class CharReader {
    constructor(src) {
        this.index = 0
        this.src = src
    }

    next() {
        return this.src.charAt(this.index++)
    }

    back() {
        this.index--
        if (this.index < 0) {
            throw Error("Reader can't go back out of string")
        }
    }

    hasNext() {
        return this.index < this.src.length
    }

    ensureNext() {
        if (!this.hasNext()) {
            throw Error("Unexpected end of source")
        }
    }

    position(offset = 0) {
        let real = this.index + offset
        if (real < 0) {
            return 0
        } else if (real >= this.src.length) {
            return this.src.length - 1
        }
        return real
    }

    range(start, end) {
        //end is exclusive
        return this.src.substring(start, end + 1)
    }
}

class MarkupTag{
    constructor(renderer,container="div") {
        this._stack = [document.createElement(container)]
        this._renderer = renderer
    }

    warn(message){
        this._renderer.warn(message)
    }

    //opens a new html tag and sets it to the html tag we are currently working on
    openHTMLTag(name){
        let top = this._stackTop()
        let element = document.createElement(name)
        top.appendChild(element)
        this._stack.push(element)
    }

    // adds another markup tag instance. Intended for use in the onChild function
    addMarkupTag(tag){
        let top = this._stackTop()
        top.appendChild(tag.build())
    }

    //private function, please ignore
    _stackTop(){
        return this._stack[this._stack.length-1]
    }

    //adds a text node to the element we are currently working on
    addTextNode(text){
        let top = this._stackTop()
        top.appendChild(document.createTextNode(text))
    }

    setAttribute(name, value){
        let top = this._stackTop()
        top.setAttribute(name,value)
    }

    addClass(name){
        let top = this._stackTop()
        top.classList.add(name)
    }

    //closes the element we are currently working on and go back to the last one we were working on
    closeHTMLTag(){
        if (this._stack-length>1) {
            this._stack.pop()
        } else {
            this.warn(`There is a a relatively unimportant bug in the code for the tags ${tagNames}, the editor should continue working fine. Error: HTML element stack underflow.`)
        }
    }

    //returns this tag as a html dom element
    build(){
        return this._stack[0]
    }

    //handles standard attributes like id and adds it to the current element, or wrapper, if none was created
    handleStandardAttributes(attributes){
        if(attributes.id){
            console.log("hi")
            this.setAttribute("id",attributes.id)
        }
    }

    allowsContent(){
        return true
    }

    // called when the tag is opened
    onOpen(attributes){
        this.handleStandardAttributes(attributes)
    }

    //called when the tag is closed
    onClose(){
        //nothing
    }

    //called when the tag contains text
    onTextContent(text){
        this.addTextNode(text)
    }

    //called when a tag contains another tag
    onChild(child){
        this.addMarkupTag(child)
    }
}

class MarkupRootElement extends MarkupTag{
    constructor(renderer,target) {
        super(renderer);
        //hacky hacky
        this._stack = [target]
    }

    onOpen(attributes) {
        //do nothing
    }
}

function process_seat_url(url) {
    let result = url.match(/^seatinfo:resource\/([0-9]+)$/)
    if (result) {
        const id = result[1]
        return `/info/resource/${id}`
    }

    result = url.match(/^seatinfo:article\/([0-9]+)/)
    if (result) {
        const id = result[1]
        return `/info/article/view/${id}`
    }

    return url
}

class MarkupRenderer{
    constructor(tag_registry) {
        this.tag_registry = tag_registry
        this.markup_tag_stack = []
        this.tag_name_stack = []
        this.textNodeStart = null
        this.src_reader = null

        this.warnings = []
    }

    warn(message){
        this.warnings.push(message)
    }

    read_text_node(){
        //if the node hasn't started, start it now
        if (this.textNodeStart == null) {
            this.textNodeStart = this.src_reader.position(-1)
        }
    }

    read_tag(){
        //check if it is opening or closing
        let isClosingTag
        this.src_reader.ensureNext()
        if (this.src_reader.next() === "/") {
            //closing tag
            isClosingTag = true
        } else {
            // opening tag, skip back for reading the tag name
            this.src_reader.back()
            isClosingTag = false
        }

        //read tag name
        let tagNameStart = this.src_reader.position()
        while (true) {
            this.src_reader.ensureNext()
            let n = this.src_reader.next()
            //tag end or argument start
            if (n === ">" || n === " ") {
                this.src_reader.back() // not part of the name anymore
                break
            }
        }
        let tagNameEnd = this.src_reader.position(-1) // we need to go one back, because the ending character shouldn't be counted
        let tagName = this.src_reader.range(tagNameStart, tagNameEnd)

        //read arguments
        let attributes = {}
        while (true) {
            // read spaces util the argument starts
            this.src_reader.ensureNext()
            let n = this.src_reader.next()

            // tag end
            if (n === ">") {
                break
            }

            // check for a space between arguments
            if (n !== " ") {
                throw Error("Expected a space")
            }

            let argNameStart = this.src_reader.position()
            while (true) {
                this.src_reader.ensureNext()
                let a = this.src_reader.next()
                // check if the arg name is finished. Cases: without parameter + more arguments follow, with parameter, without parameter+tag end
                if (a === " " || a === "=" || a === ">") {
                    this.src_reader.back()
                    break
                }
            }
            let argNameEnd = this.src_reader.position(-1)
            let argName = this.src_reader.range(argNameStart, argNameEnd)

            this.src_reader.ensureNext()
            let t = this.src_reader.next()
            if (t === " ") {
                attributes[argName] = true
                this.src_reader.back() // in order to continue, we need to go back, as we check for a single space at the beginning of the loop
            } else if (t === ">") {
                attributes[argName] = true
                break // end of argument list
            } else if (t === "=") {
                this.src_reader.ensureNext()
                let stringChar = this.src_reader.next()
                if (!(stringChar === '"' || stringChar === "'")) {
                    throw Error('expected character " after argument with parameter')
                    //TODO error handling
                }
                let parameterStart = this.src_reader.position()
                while (true) {
                    this.src_reader.ensureNext()
                    let p = this.src_reader.next()
                    if (p === stringChar) {
                        this.src_reader.back()
                        break
                    }
                }
                let parameterEnd = this.src_reader.position(-1)
                attributes[argName] = this.src_reader.range(parameterStart, parameterEnd)

                // check how to continue after argument
                this.src_reader.ensureNext()
                let t = this.src_reader.next()
                if (t === " ") {
                    this.src_reader.back() // in order to continue, we need to go back, as we check for a single space at the beginning of the loop
                } else if (t === ">") {
                    break // end of argument list
                }
            }
        }

        return {
            name: tagName,
            attributes: attributes,
            opening: !isClosingTag,
            closing: isClosingTag
        }
    }

    finish_text_node(end) {
        let start = this.textNodeStart

        if (start == null) {
            return;
        }
        let text = this.src_reader.range(start, end)
        if (text.length === 0) {
            return
        }

        let markupTagStackTop = this.markup_tag_stack[this.markup_tag_stack.length-1]
        markupTagStackTop.onTextContent(text)
    }

    build_tag(name,attributes,opening,closing) {
        if (opening) {
            // handle opening tag
            let tagType = this.tag_registry[name]
            if (tagType) {
                let tag = new tagType(this)
                tag.onOpen(attributes)
                if (tag.allowsContent()) {
                    this.markup_tag_stack.push(tag)
                    this.tag_name_stack.push(name)
                } else {
                    //no content allowed, close the tag now
                    tag.onClose()
                    this.markup_tag_stack[this.markup_tag_stack.length - 1].onChild(tag)
                }
            } else {
                this.warn(`Unknown tag: '${name}', ignoring it`)
            }
        }

        if (closing) {
            if (this.tag_name_stack.length>0){
                if (!this.tag_name_stack.includes(name)){
                    //ignore it
                    this.warn(`You are trying to close a '${name}' tag that wasn't opened, ignoring it.`)
                    return
                } else {
                    while (true) {
                        //safe, as we only execute this when the name is in the stack
                        let expected_name = this.tag_name_stack.pop()
                        if (expected_name !== name) {
                            this.warn(`You are trying to close an '${name}' tag at an hierarchy level where it wasn't expected, but the tag was found on lower levels, closing all tags until the tag at the lower level.`)

                            //close tag
                            let toClose = this.markup_tag_stack.pop()
                            toClose.onClose()
                            this.markup_tag_stack[this.markup_tag_stack.length - 1].onChild(toClose)
                        } else {
                            // we reached our target tag
                            break
                        }
                    }
                }

                //close requested tag
                let toClose = this.markup_tag_stack.pop()
                toClose.onClose()
                this.markup_tag_stack[this.markup_tag_stack.length - 1].onChild(toClose)
            }
        }
    }


    render(src, target){
        this.src_reader = new CharReader(src)
        this.textNodeStart = null
        this.markup_tag_stack = [new MarkupRootElement(this,target)]

        //main parsing loop
        while (this.src_reader.hasNext()) {
            // read first character to determine the type of token
            let c = this.src_reader.next()

            //parse text
            if (c !== "<"){
                this.read_text_node()
            }
            //parse tags
            else {
                this.finish_text_node(this.src_reader.position(-2))// we read the starting tag and afterwards it advanced the index by one, so we have to revert that
                let tag_data = this.read_tag()
                this.build_tag(tag_data.name,tag_data.attributes,tag_data.opening,tag_data.closing)
                this.textNodeStart = null // after tag, start a new text node
            }
        }

        //finish text token that was started
        this.finish_text_node(this.src_reader.position())

        if(this.markup_tag_stack.length>1){
            this.warn("There are unclosed tags at the end of the article!")
        }

        //close all unclose tags
        while(this.markup_tag_stack.length>1){ // do not remove the root element
            let toClose = this.markup_tag_stack.pop()
            toClose.onClose()
            this.markup_tag_stack[this.markup_tag_stack.length-1].onChild(toClose)
        }
    }
}

function render_article(src, target, error_cb) {
    let renderer = new MarkupRenderer(MARKUP_TAG_REGISTRY)
    try {
        renderer.render(src, target)
    } catch (e) {
        error_cb({
            error: e,
            warnings: renderer.warnings
        })
        return
    }
    error_cb({
        warnings: renderer.warnings
    })
}

const MARKUP_TAG_REGISTRY = {

}
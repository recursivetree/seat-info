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
    constructor(container="div") {
        this._stack = [document.createElement(container)]
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
            //TODO error handling
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
    constructor(target) {
        super();
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
        return `/info/view/${id}`
    }

    return url
}


function render_article(src, target, error_cb) {
    try {
        let tag_handlers = MARKUP_TAG_REGISTRY

        let reader = new CharReader(src)
        let textNodeStart = null

        let markupTagStack = [new MarkupRootElement(target)]

        function finish_text_node(start, end) {
            if (start == null) {
                return;
            }
            let text = reader.range(start, end)
            if (text.length === 0) {
                return
            }

            let markupTagStackTop = markupTagStack[markupTagStack.length-1]
            markupTagStackTop.onTextContent(text)
        }

        //main parsing loop
        while (reader.hasNext()) {
            // read first character to determine the type of token
            let c = reader.next()

            //parse text
            if (c !== "<"){
                //if the node hasn't started, start it now
                if (textNodeStart == null) {
                    textNodeStart = reader.position(-1)
                }
            }
            //parse tags
            else {
                // if there is a text node that hasn't finished, finish it now
                finish_text_node(textNodeStart, reader.position(-2)) // next advances by one and we want to go back by one, so -1+-1=2
                

                //check if it is opening or closing
                let isClosingTag
                reader.ensureNext()
                if (reader.next() === "/") {
                    //closing tag
                    isClosingTag = true
                } else {
                    // opening tag, skip back for reading the tag name
                    reader.back()
                    isClosingTag = false
                }

                //read tag name
                let tagNameStart = reader.position()
                while (true) {
                    reader.ensureNext()
                    let n = reader.next()
                    //tag end or argument start
                    if (n === ">" || n === " ") {
                        reader.back() // not part of the name anymore
                        break
                    }
                }
                let tagNameEnd = reader.position(-1) // we need to go one back, because the ending character shouldn't be counted
                let tagName = reader.range(tagNameStart, tagNameEnd)

                //read arguments
                let arguments = {}
                while (true) {
                    // read spaces util the argument starts
                    reader.ensureNext()
                    let n = reader.next()

                    // tag end
                    if (n === ">") {
                        break
                    }

                    // check for a space between arguments
                    if (n !== " ") {
                        throw Error("Expected a space")
                    }

                    let argNameStart = reader.position()
                    while (true) {
                        reader.ensureNext()
                        let a = reader.next()
                        // check if the arg name is finished. Cases: without parameter + more arguments follow, with parameter, without parameter+tag end
                        if (a === " " || a === "=" || a === ">") {
                            reader.back()
                            break
                        }
                    }
                    let argNameEnd = reader.position(-1)
                    let argName = reader.range(argNameStart, argNameEnd)

                    reader.ensureNext()
                    let t = reader.next()
                    if (t === " ") {
                        arguments[argName] = true
                        reader.back() // in order to continue, we need to go back, as we check for a single space at the beginning of the loop
                    } else if (t === ">") {
                        arguments[argName] = true
                        break // end of argument list
                    } else if (t === "=") {
                        reader.ensureNext()
                        let stringChar = reader.next()
                        if (!(stringChar === '"' || stringChar === "'")) {
                            throw Error('expected character " after argument with parameter')
                            //TODO error handling
                        }
                        let parameterStart = reader.position()
                        while (true) {
                            reader.ensureNext()
                            let p = reader.next()
                            if (p === stringChar) {
                                reader.back()
                                break
                            }
                        }
                        let parameterEnd = reader.position(-1)
                        arguments[argName] = reader.range(parameterStart, parameterEnd)

                        // check how to continue after argument
                        reader.ensureNext()
                        let t = reader.next()
                        if (t === " ") {
                            reader.back() // in order to continue, we need to go back, as we check for a single space at the beginning of the loop
                        } else if (t === ">") {
                            break // end of argument list
                        }
                    }
                }

                // build DOM
                if (isClosingTag) {
                    // handle closing tag
                    if (markupTagStack.length>1){ // never remove the last element, as it is the container
                        let toClose = markupTagStack.pop()
                        toClose.onClose()
                        markupTagStack[markupTagStack.length-1].onChild(toClose)
                    }

                } else {
                    // handle opening tag
                    let tagType = tag_handlers[tagName]
                    if (tagType) {
                        let tag = new tagType()
                        tag.onOpen(arguments)
                        if (tag.allowsContent()) {
                            markupTagStack.push(tag)
                        } else {
                            //no content allowed, close the tag now
                            tag.onClose()
                            markupTagStack[markupTagStack.length-1].onChild(tag)
                        }
                    } else {
                        throw Error("unknown tag: " + tagName)
                        //TODO error handling
                    }
                }

                textNodeStart = null
            }
        }

        while(markupTagStack.length>1){ // do not remove the root element
            let toClose = markupTagStack.pop()
            toClose.onClose()
            markupTagStack[markupTagStack.length-1].onChild(toClose)
        }

        finish_text_node(textNodeStart, reader.position())

        //target.textContent = src
    } catch (e) {
        error_cb(e)
    }
}

const MARKUP_TAG_REGISTRY = {

}
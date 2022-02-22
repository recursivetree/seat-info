class MarkupTag{
    constructor(renderer,container="div") {
        this._stack = [document.createElement(container)]
        this._renderer = renderer
    }

    warn(message){
        this._renderer.warnings.push(new MarkupWarning([],message))
    }

    //opens a new html tag and sets it to the html tag we are currently working on
    openHTMLTag(name){
        let top = this._stackTop()
        let element = document.createElement(name)
        top.appendChild(element)
        this._stack.push(element)
    }

    addEventListener(name, cb){
        this._stackTop().addEventListener(name, cb)
    }

    // adds another markup tag instance. Intended for use in the onChild function
    addMarkupTag(tag){
        let top = this._stackTop()
        top.appendChild(tag.build())
    }

    //private function, please ignore
    _stackTop(){
        //there can't be an stack underflow, as the pop function blocks that
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

    setStyle(property,value){
        let top = this._stackTop()
        top.style.setProperty(property, value)
    }

    addClass(name){
        let top = this._stackTop()
        top.classList.add(name)
    }

    //closes the element we are currently working on and go back to the last one we were working on
    closeHTMLTag(){
        if (this._stack.length>1) {
            this._stack.pop()
        } else {
            this.warn(`There is a a relatively unimportant bug in the code for the tags, the editor should continue working fine. Error: HTML element stack underflow.`)
        }
    }

    //returns this tag as a html dom element
    build(){
        return this._stack[0]
    }

    //handles standard attributes like id and adds it to the current element, or wrapper, if none was created
    handleStandardAttributes(attributes){
        if(attributes["id"]){
            this.setAttribute("id",attributes["id"])
        }
        if(attributes["text-align"]){
            let value = attributes["text-align"]
            if (value === "right"){
                this.setStyle("text-align","right")
            } else if (value === "left"){
                this.setStyle("text-align","left")
            } else if (value === "center"){
                this.setStyle("text-align","center")
            } else {
                this.warn(`Unsupported value '${value.substring(0,20)}' for attribute 'text-align'!`)
            }
        }
    }

    allowChildren(){
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

class MarkupFakeWrapper extends MarkupTag {
    constructor(renderer){
        super(renderer,"span")
    }
}

function process_seat_url(url) {
    let result = url.match(/^seatinfo:resource\/([0-9]+)$/)
    if (result) {
        const id = result[1]
        return `/info/resource/${id}`
    }

    result = url.match(/^seatinfo:article\/([0-9]+)(?:#([^ ]*))?$/)
    if (result) {
        const id = result[1]
        if (result[2]) {
            return `/info/article/view/${id}#${result[2]}`
        } else {
            return `/info/article/view/${id}`
        }
    }

    return url
}

class MarkupRenderer{
    constructor(tag_registry) {
        this.tag_registry = tag_registry
        this.textNodeStart = null

        this.warnings = []
    }

    render(lines, target, elementClickCallback=null){
        const ast = parse(lines)

        const warnings = ast.warnings

        let root = new MarkupRootElement(this,target)

        let elementStack = new Stack()

        elementStack.push(root)

        const recursiveBuildMarkupElements = (markupElement, content) => {
            for (const contentAst of content) {
                if(contentAst instanceof ASTText){
                    markupElement.onTextContent(contentAst.text)
                } else if (contentAst instanceof ASTTag){
                    if(markupElement.allowChildren()){

                        //MarkupTag class used for the element
                        let elementClass

                        if(!this.tag_registry[contentAst.tagName]){
                            //tag not found, treating it like a <span>
                            warnings.push(new MarkupWarning(contentAst.tokens,`Unknown tag of type '${contentAst.tagName}'`))
                            elementClass = MarkupFakeWrapper
                        } else {
                            //get markup tag class
                            elementClass = this.tag_registry[contentAst.tagName]
                        }

                        const child = new elementClass(this)

                        if(elementClickCallback){
                            child.addEventListener("click",function (e) {
                                e.stopPropagation()
                                e.preventDefault()
                                elementClickCallback(contentAst)
                            }, true)
                        }

                        child.onOpen(contentAst.properties)

                        if(child.allowChildren()) {
                            recursiveBuildMarkupElements(child, contentAst.content)
                        }

                        child.onClose()

                        //append child to parent
                        markupElement.onChild(child)

                        //e.g. when you have <br>text</br>, treat it as <br>
                        if(!child.allowChildren() && contentAst.content.length > 0){
                            warnings.push(new MarkupWarning(contentAst.tokens,`<${contentAst.tagName}> elements don't allow children. This can mean that you are using the old syntax <${contentAst.tagName}> instead of the new one with a closing slash: <${contentAst.tagName}/>`))


                            recursiveBuildMarkupElements(markupElement,contentAst.content)
                        }

                    } else {
                        warnings.push(new MarkupWarning(contentAst.tokens,"Parent element doesn't allow children"))
                    }
                }
            }
        }

        recursiveBuildMarkupElements(root,ast.ast.content)

        this.warnings = this.warnings.concat(warnings)

        return ast
    }
}

function render_article(lines, target, done_cb,elementClickCallback=null) {
    let renderer = new MarkupRenderer(MARKUP_TAG_REGISTRY)
    let ast
    try {
        ast = renderer.render(lines, target, elementClickCallback)
    } catch (e) {
        console.log(e)
        done_cb({
            error: e,
            warnings: renderer.warnings
        })
        return
    }
    done_cb({
        warnings: renderer.warnings,
        renderData: {ast: ast}
    })
}

const MARKUP_TAG_REGISTRY = {

}
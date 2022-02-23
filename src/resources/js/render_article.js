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

class SeatInfoDomElementBuilder {
    static create(name){
        return new SeatInfoDomElementBuilder(document.createElement(name))
    }

    static from(domElement){
        return new SeatInfoDomElementBuilder(domElement)
    }

    constructor(domElement) {
        this.domElement = domElement
    }

    class(...classes){
        for (const clazz of classes) {
            this.domElement.classList.add(clazz)
        }

        return this
    }

    removeClass(...classes){
        for (const clazz of classes) {
            this.domElement.classList.remove(clazz)
        }

        return this
    }

    style(property,value){
        this.domElement.style.setProperty(property,value)

        return this
    }

    attribute(name,value){
        this.domElement.setAttribute(name,value)

        return this
    }

    content(...children){
        for (const child of children) {
            if(child instanceof SeatInfoDomElementBuilder){
                this.domElement.appendChild(child.domElement)
            } else if (child instanceof Node){
                this.domElement.appendChild(child)
            } else if(child instanceof Array){
                this.content(...child)
            } else if(typeof child === "string" ) {
                this.domElement.appendChild(document.createTextNode(child))
            } else {
                //Seat info element node
                this.domElement.appendChild(child.dom)
            }
        }

        return this
    }

    clearContent(...newChildren){
        while (this.domElement.firstChild){
            this.domElement.removeChild(this.domElement.firstChild)
        }

        this.content(...newChildren)
    }

    event(name, cb){
        this.domElement.addEventListener(name,cb)

        return this
    }
}

class SeatInfoMarkupRenderer {

    static ELEMENT_REGISTRY = {}
    static LINK_PREPROCESSORS = {}

    static registerElement(name, element){
        SeatInfoMarkupRenderer.ELEMENT_REGISTRY[name] = element
    }

    static registerLinkPreProcessor(scope,preprocessor){
        SeatInfoMarkupRenderer.LINK_PREPROCESSORS[scope] = preprocessor
    }

    constructor() {
        this.textNodeStart = null

        this.warnings = []
    }

    warn(warning){
        this.warnings.push(warning)
    }

    render(lines, targetContainer, astNodeClickCallback=null){
        const ast = parse(lines)
        this.warnings = this.warnings.concat(ast.warnings)

        const buildContentRecursive = (astContent) => {
            const content = []

            for (const astNode of astContent) {

                if(astNode instanceof ASTText){
                    const textNode = document.createElement("span") // we can't use a text node, because they don't fire events, which is required for the astNodeClickCallback
                    textNode.textContent = astNode.text

                    if(astNodeClickCallback){
                        textNode.addEventListener("click",(e)=>{
                            e.stopPropagation()
                            astNodeClickCallback(astNode)
                        },false)
                    }

                    content.push({
                        node: astNode,
                        type:"text",
                        dom:textNode
                    })
                } else if (astNode instanceof ASTTag){
                    const elementImplementation = SeatInfoMarkupRenderer.ELEMENT_REGISTRY[astNode.tagName]

                    if(!elementImplementation){
                        this.warn(new MarkupWarning(astNode.tokens,`Unknown tag type <${astNode.tagName}>!`))

                        //still build children
                        content.push(...buildContentRecursive(astNode.content))

                    } else {

                        const elementInfo = {
                            node: astNode,
                            properties: astNode.properties,
                            content: buildContentRecursive(astNode.content),
                            renderer: this
                        }

                        const data = elementImplementation(elementInfo, SeatInfoDomElementBuilder.create)

                        let domElementBuilder

                        if(data.dom instanceof SeatInfoDomElementBuilder){
                            domElementBuilder = data.dom
                        } else if(data.dom instanceof Element){
                            domElementBuilder = SeatInfoDomElementBuilder.from(data.dom)
                        } else {
                            this.warn(astNode.tokens,`Internal error: Renderer implementation for <${astNode.tagName}> didn't return a DOMElement-like object! Please report this bug!`)
                        }

                        //apply common properties
                        this.commonProperties(elementInfo,data.disabledCommonProperties || [],domElementBuilder)

                        //extract dom node
                        data.dom = domElementBuilder.domElement

                        if(astNodeClickCallback){
                            data.dom.addEventListener("click",(e)=>{
                                e.stopPropagation()
                                astNodeClickCallback(astNode)
                            },false)
                        }

                        data.type = "element"
                        data.tagName = astNode.tagName

                        content.push(data)
                    }

                } else {
                    this.warn(new MarkupWarning([],"Internal errors: AST tree doesn't contain ASTNodes! Please report this bug."))
                }

            }

            return content
        }

        const rootContent = buildContentRecursive(ast.rootNode.content)

        for (const rootContentNode of rootContent) {
            targetContainer.appendChild(rootContentNode.dom)
        }

        return {
            ast: ast
        }
    }

    //shared properties like id and text-align
    commonProperties(elementInfo, disabledCommonProperties, elementBuilder){
        const properties = elementInfo.properties

        if(properties["id"] && !disabledCommonProperties.includes("id")){
            elementBuilder.attribute("id",properties["id"].value)
        }
        if(properties["text-align"] && !disabledCommonProperties.includes("text-align")){
            let value = properties["text-align"].value
            if (value === "right"){
                elementBuilder.style("text-align","right")
            } else if (value === "left"){
                elementBuilder.style("text-align","left")
            } else if (value === "center"){
                elementBuilder.style("text-align","center")
            } else {
                if(value===true){
                    this.warn(new MarkupWarning(properties["text-align"].tokens, `Attribute 'text-align' requires a value like text-align="right"!`))
                } else {
                    this.warn(new MarkupWarning(properties["text-align"].tokens, `Unsupported value '${value.substring(0, 20)}' for attribute 'text-align'!`))
                }
            }
        }
    }
}

function render_article(lines, target, done_cb,elementClickCallback=null) {
    let renderer = new SeatInfoMarkupRenderer()
    let renderData
    try {
        renderData = renderer.render(lines, target, elementClickCallback)
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
        renderData: {ast: renderData.ast}
    })
}
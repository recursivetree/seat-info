class ReturnStatus {
    static new(){
        return new ReturnStatus()
    }

    ok(data){
        this.data = data
        return this
    }

    warning(message){
        this.warningMsgs.push(message)
        return this
    }

    constructor() {
        this.data = null
        this.warningMsgs = []
    }

    getValue(warningHandler){
        for (const warningMsg of this.warningMsgs) {
            warningHandler(warningMsg)
        }
        return this.data
    }
}

class SeatInfoDomElementBuilder {
    static create(name) {
        return new SeatInfoDomElementBuilder(document.createElement(name))
    }

    static from(domElement) {
        return new SeatInfoDomElementBuilder(domElement)
    }

    constructor(domElement) {
        this.domElement = domElement
    }

    class(...classes) {
        for (const clazz of classes) {
            this.domElement.classList.add(clazz)
        }

        return this
    }

    removeClass(...classes) {
        for (const clazz of classes) {
            this.domElement.classList.remove(clazz)
        }

        return this
    }

    style(property, value) {
        this.domElement.style.setProperty(property, value)

        return this
    }

    attribute(name, value) {
        if (value) {
            this.domElement.setAttribute(name, value)
        }

        return this
    }

    content(...children) {
        for (const child of children) {
            if (child instanceof SeatInfoDomElementBuilder) {
                this.domElement.appendChild(child.domElement)
            } else if (child instanceof Node) {
                this.domElement.appendChild(child)
            } else if (child instanceof Array) {
                this.content(...child)
            } else if (typeof child === "string") {
                this.domElement.appendChild(document.createTextNode(child))
            } else {
                //Seat info element node
                this.domElement.appendChild(child.dom)
            }
        }

        return this
    }

    clearContent(...newChildren) {
        while (this.domElement.firstChild) {
            this.domElement.removeChild(this.domElement.firstChild)
        }

        this.content(...newChildren)
    }

    event(name, cb) {
        this.domElement.addEventListener(name, cb)

        return this
    }
}

class SeatInfoMarkupRenderer {

    static ELEMENT_REGISTRY = {}
    static LINK_PREPROCESSORS_REGISTRY = {}
    static COMMON_PROPERTY_REGISTRY = {}

    static registerElement(name, isSelfClosing, element) {
        SeatInfoMarkupRenderer.ELEMENT_REGISTRY[name] = {
            selfClosing: isSelfClosing,
            builder: element
        }
    }

    static registerLinkPreProcessor(scope, preprocessor) {
        SeatInfoMarkupRenderer.LINK_PREPROCESSORS_REGISTRY[scope] = preprocessor
    }

    static registerCommonProperty(name, handler) {
        SeatInfoMarkupRenderer.COMMON_PROPERTY_REGISTRY[name] = handler
    }

    preprocessLink(link) {
        if (!link) {
            return ReturnStatus.new().warning("No url specified!")
        }

        if (link instanceof ASTTagProperty) {
            link = link.value
        }

        const data = /^(?<resource>.+?):(?<data>.*)$/gm.exec(link)

        if (data) {
            const scope = data.groups.resource
            const handler = SeatInfoMarkupRenderer.LINK_PREPROCESSORS_REGISTRY[scope]

            if (!handler) {
                return ReturnStatus.new()
                    .warning("Could not find a matching URL transformer. If you want to use a relative url, use 'relative:your/url/path', for absolute urls use 'url:https://example.com' or read the documentation to see all url transformers.")
                    .ok(link)
            }

            return handler(data.groups.data)
        } else {
            return ReturnStatus.new()
                .ok(data)
                .warning("Could not find a matching URL transformer. If you want to use a relative url, use 'relative:your/url/path', for absolute urls use 'url:https://example.com' or read the documentation to see all url transformers.")
        }
    }

    constructor() {
        this.textNodeStart = null

        this.warnings = []
    }

    warn(warning) {
        this.warnings.push(warning)
    }

    render(lines, targetContainer, astNodeClickCallback = null) {
        const ast = parse(lines)
        this.warnings = this.warnings.concat(ast.warnings)

        const buildContentRecursive = (astContent) => {
            const content = []

            for (const astNode of astContent) {

                if (astNode instanceof ASTText) {
                    const textNode = document.createElement("span") // we can't use a text node, because they don't fire events, which is required for the astNodeClickCallback
                    textNode.textContent = astNode.text

                    if (astNodeClickCallback) {
                        textNode.addEventListener("click", (e) => {
                            e.stopPropagation()
                            astNodeClickCallback(astNode)
                        }, false)
                    }

                    content.push({
                        node: astNode,
                        type: "text",
                        dom: textNode
                    })
                } else if (astNode instanceof ASTTag) {
                    const elementImplementation = SeatInfoMarkupRenderer.ELEMENT_REGISTRY[astNode.tagName]

                    if (!elementImplementation) {
                        this.warn(new MarkupWarning(astNode.tokens, `Unknown tag type <${astNode.tagName}>!`))

                        //still build children
                        content.push(...buildContentRecursive(astNode.content))

                    } else {

                        const elementInfo = {
                            node: astNode,
                            properties: astNode.properties,
                            content: buildContentRecursive(astNode.content),
                            renderer: this
                        }

                        const elementData = elementImplementation.builder(elementInfo, SeatInfoDomElementBuilder.create)

                        //extract or create a DomElementBuilder
                        let domElementBuilder
                        if (elementData.dom instanceof SeatInfoDomElementBuilder) {
                            domElementBuilder = elementData.dom
                        } else if (elementData.dom instanceof Element) {
                            domElementBuilder = SeatInfoDomElementBuilder.from(elementData.dom)
                        } else {
                            this.warn(astNode.tokens, `Internal error: Renderer implementation for <${astNode.tagName}> didn't return a DOMElement-like object! Please report this bug!`)
                        }

                        //apply properties common across different elements
                        const disableCommonProperties = new Set(elementData.disabledCommonProperties || [])
                        this.commonProperties({
                            elementInfo,
                            elementData,
                            htmlBuilder: domElementBuilder,
                            renderer: this
                        }, elementInfo.properties, disableCommonProperties)

                        //check for unsupported properties
                        const supportedElementProperties = new Set([
                                ...(elementData.supportedElementProperties || []),
                                ...Object.keys(SeatInfoMarkupRenderer.COMMON_PROPERTY_REGISTRY)
                            ].filter((e) => !disableCommonProperties.has(e))
                        )
                        for (const property of Object.values(elementInfo.properties)) {
                            if (!supportedElementProperties.has(property.name)) {
                                this.warn(new MarkupWarning(property.tokens, `<${astNode.tagName}> elements don't support the property "${property.name}"!`))
                            }
                        }

                        //add click callback
                        if (astNodeClickCallback) {
                            domElementBuilder.event("click", (e) => {
                                e.stopPropagation()
                                astNodeClickCallback(astNode)
                            }, false)
                        }

                        //add this element to element list
                        content.push({
                            dom: domElementBuilder.domElement,
                            type: "element",
                            tagName: astNode.tagName,
                            node: astNode
                        })

                        //if an element doesn't allow content, add the content afterwards
                        if (elementData.noContent && elementInfo.content.length > 0) {
                            if (elementImplementation.selfClosing) {
                                this.warn(new MarkupWarning(astNode.tokens, `<${astNode.tagName}> does not allow any content, as it is a self-closing tag. Consider upgrading it to <${astNode.tagName} />`))
                            } else {
                                this.warn(new MarkupWarning(astNode.tokens, `<${astNode.tagName}> does not allow any content!`))
                            }
                            content.push(...elementInfo.content)
                        }
                    }

                } else {
                    this.warn(new MarkupWarning([], "Internal errors: AST tree doesn't contain ASTNodes! Please report this bug."))
                }

            }

            return content
        }

        const rootContent = buildContentRecursive(ast.rootNode.content)

        for (const rootContentNode of rootContent) {
            targetContainer.appendChild(rootContentNode.dom)
        }

        return {
            ast: ast,
        }
    }

    //shared properties like id and text-align
    commonProperties(elementData, properties, disabledProperties) {
        const propertyHandlers = Object.entries(SeatInfoMarkupRenderer.COMMON_PROPERTY_REGISTRY)
            .filter((e) => properties[e[0]])
            .filter((e) => !disabledProperties.has(e[0]))

        for (const propertyHandlerInfo of propertyHandlers) {
            const [propertyName, propertyHandler] = propertyHandlerInfo
            propertyHandler(properties[propertyName], elementData)
        }
    }
}

function render_article(lines, target, done_cb, elementClickCallback = null) {
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
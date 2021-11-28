// const tag_handlers = {
//     "br": function (builder, arguments) {
//         let brNode = document.createElement("br")
//         builder.addNode(brNode)
//     },
//     "hr": function (builder, arguments) {
//         let brNode = document.createElement("hr")
//         builder.addNode(brNode)
//     },
//     "b": function (builder, arguments) {
//         let bNode = document.createElement("b")
//         builder.pushNode(bNode)
//     },
//     "a": function (builder, arguments) {
//         let bNode = document.createElement("a")
//
//         if (arguments.href) {
//             bNode.setAttribute("href", redirect_resource_url(arguments.href, true))
//         }
//
//         if (arguments.newtab) {
//             bNode.setAttribute("target", "_blank")
//         }
//
//         if (arguments.download) {
//             bNode.setAttribute("download", "")
//         }
//
//         builder.pushNode(bNode)
//     },
//     "h1": function (builder, arguments) {
//         let bNode = document.createElement("h1")
//         builder.pushNode(bNode)
//     },
//     "h2": function (builder, arguments) {
//         let bNode = document.createElement("h2")
//         builder.pushNode(bNode)
//     },
//     "h3": function (builder, arguments) {
//         let bNode = document.createElement("h3")
//         builder.pushNode(bNode)
//     },
//     "h4": function (builder, arguments) {
//         let bNode = document.createElement("h4")
//         builder.pushNode(bNode)
//     },
//     "h5": function (builder, arguments) {
//         let bNode = document.createElement("h5")
//         builder.pushNode(bNode)
//     },
//     "h6": function (builder, arguments) {
//         let bNode = document.createElement("h6")
//         builder.pushNode(bNode)
//     },
//     "ul": function (builder, arguments) {
//         let bNode = document.createElement("ul")
//         builder.pushNode(bNode)
//     },
//     "ol": function (builder, arguments) {
//         let bNode = document.createElement("ol")
//         builder.pushNode(bNode)
//     },
//     "li": function (builder, arguments) {
//         let bNode = document.createElement("li")
//         builder.pushNode(bNode)
//     },
//     "p": function (builder, arguments) {
//         let bNode = document.createElement("p")
//         builder.pushNode(bNode)
//     },
//     "i": function (builder, arguments) {
//         let bNode = document.createElement("i")
//         builder.pushNode(bNode)
//     },
//     "s": function (builder, arguments) {
//         let bNode = document.createElement("s")
//         builder.pushNode(bNode)
//     },
//     "img": function (builder, arguments) {
//         let bNode = document.createElement("img")
//         if (arguments.src) {
//             bNode.setAttribute("src", redirect_resource_url(arguments.src))
//         }
//         if (arguments.alt) {
//             bNode.setAttribute("alt", arguments.alt)
//         }
//         bNode.classList.add("mw-100")
//         builder.addNode(bNode)
//     },
//     "table": function (builder, arguments) {
//         let bNode = document.createElement("table")
//         bNode.classList.add("table")
//         if (arguments.stripes) {
//             bNode.classList.add("table-striped")
//         }
//         if (arguments.border) {
//             bNode.classList.add("table-bordered")
//         }
//         builder.pushNode(bNode)
//     },
//     "tr": function (builder, arguments) {
//         let bNode = document.createElement("tr")
//         builder.pushNode(bNode)
//     },
//     "th": function (builder, arguments) {
//         let bNode = document.createElement("th")
//         builder.pushNode(bNode)
//     },
//     "td": function (builder, arguments) {
//         let bNode = document.createElement("td")
//         builder.pushNode(bNode)
//     },
//     "thead": function (builder, arguments) {
//         let bNode = document.createElement("thead")
//         builder.pushNode(bNode)
//     },
//     "tbody": function (builder, arguments) {
//         let bNode = document.createElement("tbody")
//         builder.pushNode(bNode)
//     },
//     "pagelink": function (builder, arguments) {
//         if (arguments.id) {
//             let bNode = document.createElement("div")
//             bNode.setAttribute("id", arguments.id);
//             builder.addNode(bNode)
//         }
//     },
// }

function registerSimpleTextContainingMarkupTag(tagName){
    class TempTag extends MarkupTag{
        constructor() {
            super(tagName);
        }
    }
    MARKUP_TAG_REGISTRY[tagName] = TempTag
    return TempTag
}

function registerSimpleNoContentMarkupTag(tagName){
    class TempTag extends MarkupTag{
        constructor() {
            super(tagName);
        }
        onChild(child) {
            //do nothing
            //allowsContent should not even allow this to be called, but you never know
            //TODO warning
        }
        onTextContent(text) {
            //do nothing
            //allowsContent should not even allow this to be called, but you never know
            //TODO warning
        }
        allowsContent(){
            return false
        }
    }
    MARKUP_TAG_REGISTRY[tagName] = TempTag
    return TempTag
}

function registerRestrainedChildrenMarkupTag(tagName,allowed_children, allow_text=false){
    class TempTag extends MarkupTag{
        constructor() {
            super(tagName);
        }
        onChild(child) {
            for(const allowed of allowed_children){
                if (child instanceof allowed){
                    super.addMarkupTag(child)
                    return
                }
            }
        }
        onTextContent(text) {
            if(allow_text){
                super.addTextNode(text)
            }
        }
    }
    MARKUP_TAG_REGISTRY[tagName] = TempTag
    return TempTag
}

function registerMarkupTag(tagName,clazz){
    MARKUP_TAG_REGISTRY[tagName] = clazz
}

registerSimpleTextContainingMarkupTag("p")
registerSimpleTextContainingMarkupTag("b")
registerSimpleTextContainingMarkupTag("i")
registerSimpleTextContainingMarkupTag("s")
registerSimpleTextContainingMarkupTag("h1")
registerSimpleTextContainingMarkupTag("h2")
registerSimpleTextContainingMarkupTag("h3")
registerSimpleTextContainingMarkupTag("h4")
registerSimpleTextContainingMarkupTag("h5")
registerSimpleTextContainingMarkupTag("h6")

registerSimpleNoContentMarkupTag("br")
registerSimpleNoContentMarkupTag("hr")

class LinkMarkupTag extends MarkupTag{
    constructor() {
        super("a");
    }

    onOpen(attributes) {
        super.onOpen(attributes);

        let href = ""
        if(attributes.href){
            href = process_seat_url(attributes.href)
        }
        super.setAttribute("href", href)

        if(attributes.newtab){
            super.setAttribute("target", "_blank")
        }

        if(attributes.download){
            super.setAttribute("download", "")
        }
    }
}
registerMarkupTag("a",LinkMarkupTag)

registerSimpleNoContentMarkupTag("pagelink")

const liTag = registerSimpleTextContainingMarkupTag("li")
registerRestrainedChildrenMarkupTag("ul",[liTag])
registerRestrainedChildrenMarkupTag("ol",[liTag])

const thTag = registerSimpleTextContainingMarkupTag("th")
const tdTag = registerSimpleTextContainingMarkupTag("td")
const trTag = registerRestrainedChildrenMarkupTag("tr",[thTag,tdTag])
const tBodyTag = registerRestrainedChildrenMarkupTag("tbody",[trTag])
const tHeadTag = registerRestrainedChildrenMarkupTag("thead",[trTag])

class TableMarkupTag extends MarkupTag{
    constructor() {
        super("table");
    }
    onOpen(attributes) {
        super.onOpen(attributes)

        super.addClass("table")
        if (attributes.stripes) {
            super.addClass("table-striped")
        }
        if (attributes.border) {
            super.addClass("table-bordered")
        }
    }
    onChild(child) {
        for(const allowed of [tBodyTag,tHeadTag]){
            if (child instanceof allowed){
                super.addMarkupTag(child)
                return
            }
        }
    }
}
registerMarkupTag("table",TableMarkupTag)

class ImgMarkupTag extends MarkupTag{
    constructor() {
        super("img");
    }

    onOpen(attributes) {
        super.onOpen(attributes);
        let bNode = document.createElement("img")
        if (attributes.src) {
            super.setAttribute("src", process_seat_url(attributes.src))
        }
        if (attributes.alt) {
            bNode.setAttribute("alt", attributes.alt)
        }
        super.addClass("mw-100")
    }

    allowsContent(){
        return false
    }

    onChild(child) {
        //do nothing
        //allowsContent should not even allow this to be called, but you never know
        //TODO warning
    }
    onTextContent(text) {
        //do nothing
        //allowsContent should not even allow this to be called, but you never know
        //TODO warning
    }
}
registerMarkupTag("img",ImgMarkupTag)



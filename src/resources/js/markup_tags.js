function registerSimpleTextContainingMarkupTag(tagName){
    class TempTag extends MarkupTag{
        constructor(renderer) {
            super(renderer,tagName);
        }
    }
    MARKUP_TAG_REGISTRY[tagName] = TempTag
    return TempTag
}

function registerSimpleNoContentMarkupTag(tagName){
    class TempTag extends MarkupTag{
        constructor(renderer) {
            super(renderer,tagName);
        }
        onChild(child) {
            //do nothing
            //allowsContent should not even allow this to be called, but you never know
            super.warn(`${tagName} tags don't allow children elements.`)
        }
        onTextContent(text) {
            //do nothing
            //allowsContent should not even allow this to be called, but you never know
            super.warn(`${tagName} tags don't allow text content.`)
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
        constructor(renderer) {
            super(renderer,tagName);
        }
        onChild(child) {
            for(const allowed of allowed_children){
                if (child instanceof allowed){
                    super.addMarkupTag(child)
                    return
                }
            }
            super.warn(`Illegal children types in elements of type ${tagName}.`)
        }
        onTextContent(text) {
            if(allow_text){
                super.addTextNode(text)
            }
            //check if it is just whitespace
            else if (!/^\s*$/.test(text)) {
                super.warn(`${tagName} tags don't allow text content.`)
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
    constructor(renderer) {
        super(renderer,"a");
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

function registerTableCellMarkupTag(tagName){
    class TempTag extends MarkupTag{
        constructor(renderer) {
            super(renderer,tagName);
        }

        onOpen(attributes) {
            super.onOpen(attributes);

            if(attributes["colspan"]) {
                let value
                try{
                    value = parseInt(attributes["colspan"])
                    super.setAttribute("colspan",value)
                } catch (e) {
                    warn(`'${tagName}' elements with attribute 'colspan' is not an integer!"`)
                }
            }
        }
    }

    MARKUP_TAG_REGISTRY[tagName] = TempTag
    return TempTag
}

const thTag = registerSimpleTextContainingMarkupTag("th")
const tdTag = registerTableCellMarkupTag("td")
const trTag = registerRestrainedChildrenMarkupTag("tr",[thTag,tdTag])
const tBodyTag = registerRestrainedChildrenMarkupTag("tbody",[trTag])
const tHeadTag = registerRestrainedChildrenMarkupTag("thead",[trTag])

class RootTableMarkupTag extends MarkupTag{
    constructor(renderer) {
        super(renderer,"table");
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
registerMarkupTag("table",RootTableMarkupTag)

class ImgMarkupTag extends MarkupTag{
    constructor(renderer,container="p") {
        super(renderer,container);
    }

    onOpen(attributes) {
        super.onOpen(attributes);

        super.openHTMLTag("img")
        if (attributes.src) {
            super.setAttribute("src", process_seat_url(attributes.src))
        }
        if (attributes.alt) {
            super.setAttribute("alt", attributes.alt)
        }
        super.addClass("mw-100")
        super.closeHTMLTag()
    }

    allowsContent(){
        return false
    }

    onChild(child) {
        //do nothing
        //allowsContent should not even allow this to be called, but you never know
        super.warn("Image tags don't allow children elements.")
    }
    onTextContent(text) {
        //do nothing
        //allowsContent should not even allow this to be called, but you never know
        super.warn("Image tags don't allow text content.")
    }
}
registerMarkupTag("img",ImgMarkupTag)

class IconMarkupTag extends ImgMarkupTag{
    constructor(renderer) {
        super(renderer,"span");
    }
}
registerMarkupTag("icon",IconMarkupTag)

class ColorTag extends MarkupTag{
    constructor(renderer) {
        super(renderer,"span");
    }

    onOpen(attributes) {
        super.onOpen(attributes);

        if(attributes["color"]){
            super.setStyle("color",attributes["color"])
        }

        if(attributes["colour"]){
            super.setStyle("color",attributes["colour"])
        }
    }
}
registerMarkupTag("color",ColorTag)
registerMarkupTag("colour",ColorTag)



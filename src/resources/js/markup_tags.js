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
            super.warn(`${tagName} tags don't allow children elements. Make sure the correct syntax  <${tagName} /> instead of just <${tagName}> is used`)
        }
        onTextContent(text) {
            //do nothing
            //allowsContent should not even allow this to be called, but you never know
            super.warn(`${tagName} tags don't allow text content. Make sure the correct syntax the correct syntax <${tagName} /> instead of just <${tagName}> is used`)
        }
        allowChildren(){
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

        const container = super._stackTop() //hacky, but I plan to replace the component system to allow actually interactive components soon

        super.openHTMLTag("img")

        super.addEventListener("error",function () {
            while (container.firstChild){
                container.removeChild(container.firstChild)
            }
            const div = document.createElement("div")
            div.classList.add("alert")
            div.classList.add("alert-warning")

            const icon = document.createElement("i")
            icon.classList.add("fas","fa-exclamation-triangle")

            div.appendChild(icon)
            div.appendChild(document.createTextNode(`Could not load image! Description: ${attributes.alt || "no description provided"}`))

            container.appendChild(div)
        })

        if (attributes.src) {
            super.setAttribute("src", process_seat_url(attributes.src))
        }
        if (attributes.alt) {
            super.setAttribute("alt", attributes.alt)
        }
        super.addClass("mw-100")
        super.closeHTMLTag()
    }

    allowChildren(){
        return false
    }

    onChild(child) {
        //do nothing
        //allowsContent should not even allow this to be called, but you never know
        super.warn("Image tags don't allow children elements. Make sure the correct syntax <tagname /> instead of just <tagname> is used")
    }
    onTextContent(text) {
        //do nothing
        //allowsContent should not even allow this to be called, but you never know
        super.warn("Image tags don't allow text content. Make sure the correct syntax <tagname /> instead of just <tagname> is used")
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

class AudioTag extends MarkupTag {
    constructor(renderer) {
        super(renderer,"p");
    }

    formatTimeStamp(position, length){
        return `${this.formatSeconds(position)} / ${this.formatSeconds(length)}`
    }

    formatSeconds(duration){
        const seconds = duration % 60
        const minutes = Math.floor(duration / 60) % 60
        const hours = Math.floor(duration / 3600)

        const options = {
            maximumFractionDigits: 0,
            minimumIntegerDigits: 2,
        }

        if (hours>0){
            return `${hours.toLocaleString(undefined, options)}:${minutes.toLocaleString(undefined, options)}:${seconds.toLocaleString(undefined, options)}`
        }

        return `${minutes.toLocaleString(undefined, options)}:${seconds.toLocaleString(undefined, options)}`
    }

    onOpen(attributes) {
        super.onOpen(attributes);

        if(attributes["src"]){
            let audio = new Audio(process_seat_url(attributes["src"]))

            this.openHTMLTag("div")
            this.addClass("d-flex")
            this.addClass("align-items-center")

            this.addClass("p-2")
            this.setStyle("background-color","#BBBBBB")
            this.setStyle("border-radius","5px")

            //button
            this.openHTMLTag("button")
            this.addClass("btn")
            this.addClass("btn-primary")

            //icon
            this.openHTMLTag("i")
            this.addClass("fas")
            this.addClass("fa-play")
            let btnIcon = this._stackTop()
            this.closeHTMLTag()

            this.addEventListener("click",function () {
                if(audio.paused) {
                    audio.play();
                } else {
                    audio.pause()
                }
            })
            this.closeHTMLTag()

            this.openHTMLTag("span")
            this.setStyle("white-space","nowrap")
            this.addClass("m-1")
            let label = this._stackTop()
            this.closeHTMLTag()

            //bootstrap: outer progress bar
            this.openHTMLTag("div")
            this.addClass("progress")
            this.addClass("m-1") //margin
            this.addClass("w-100") // use full width
            this.setStyle("min-width","50px")

            this.openHTMLTag("div")
            this.addClass("progress-bar")

            //TODO remove hack fix
            let innerElement = this._stackTop()

            //inner progress bar div
            this.closeHTMLTag()

            //TODO remove hack fix
            let outerElement = this._stackTop()

            this.addEventListener("click",function (e) {
                let progress = e.offsetX / outerElement.offsetWidth
                audio.currentTime = progress * audio.duration
            })

            //outer progress bar div
            this.closeHTMLTag()

            //outer tag
            this.closeHTMLTag()

            audio.addEventListener("play",function (){
                btnIcon.classList.remove("fa-play")
                btnIcon.classList.add("fa-pause")
            })
            audio.addEventListener("ended",function (){
                btnIcon.classList.remove("fa-pause")
                btnIcon.classList.add("fa-play")
            })
            audio.addEventListener("pause",function (){
                btnIcon.classList.remove("fa-pause")
                btnIcon.classList.add("fa-play")
            })
            audio.addEventListener("durationchange",() => {
                let progress = audio.currentTime / audio.duration
                innerElement.style.setProperty("width",`${progress*100}%`)
                label.textContent = this.formatTimeStamp(audio.currentTime,audio.duration)
            })
            audio.addEventListener("timeupdate",() => {
                let progress = audio.currentTime / audio.duration
                innerElement.style.setProperty("width",`${progress*100}%`)
                label.textContent = this.formatTimeStamp(audio.currentTime,audio.duration)
            })

        } else {
            this.warn("Audio element contains no audio source!")
        }
    }

    allowChildren() {
        return false;
    }
}
registerMarkupTag("audio",AudioTag)



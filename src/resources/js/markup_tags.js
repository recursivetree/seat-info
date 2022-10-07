SeatInfoMarkupRenderer.registerLinkPreProcessor("seatinfo", (link, emitWarning) => {

    //note for future myself: always prefer id, as the name can be cut off to fit the length of text fields
    const articleLink = /^article\/(?<article_id>\d+)\/?(?:.+?)?(?:#(?<hash>.*))?$/gm.exec(link)
    if (articleLink) {
        if (articleLink.groups.hash) {
            return ReturnStatus.new().ok(`/info/article/view/${articleLink.groups.article_id}#${articleLink.groups.hash}`)
        } else {
            return ReturnStatus.new().ok(`/info/article/view/${articleLink.groups.article_id}`)
        }
    }

    const resourceLink = /^resource\/(?<resource_id>\d+)\/?(?:.+?)?$/gm.exec(link)
    if (resourceLink) {
        return ReturnStatus.new().ok(`/info/resource/${resourceLink.groups.resource_id}`)
    }

    //at this point, we don't know what it is
    return ReturnStatus.new().warning("Unknown url schema!")
})

SeatInfoMarkupRenderer.registerLinkPreProcessor("url", (link) => {
    try {
        new URL(link)
    } catch (e) {
        return ReturnStatus.new.warning("Invalid URL format!")
    }
    return ReturnStatus.new().ok(link)
})

SeatInfoMarkupRenderer.registerLinkPreProcessor("relative", (link) => {
    //TODO validate relative link
    return ReturnStatus.new().ok(link)
})

SeatInfoMarkupRenderer.registerLinkPreProcessor("id", (link) => {
    //TODO validate relative link
    return ReturnStatus.new().ok(`#${link}`)
})

SeatInfoMarkupRenderer.registerLinkPreProcessor("seatfitting", (link) => {
    const fitLink = /^fitting\/(?<fitting_name>[\S ]+)$/gm.exec(link)
    if (fitLink) {
        return ReturnStatus.new().ok(`/info/integration/seat-fitting/fit?name=${encodeURIComponent(fitLink.groups.fitting_name)}`)
    }
    return ReturnStatus.new().warning("Invalid fitting link!")
})

SeatInfoMarkupRenderer.registerCommonProperty("id", (value, elementData) => {
    elementData.htmlBuilder.attribute("id", value.value)
})

SeatInfoMarkupRenderer.registerCommonProperty("text-align", (property, elementData) => {
    const value = property.value
    if (value === "right") {
        elementData.htmlBuilder.style("text-align", "right")
    } else if (value === "left") {
        elementData.htmlBuilder.style("text-align", "left")
    } else if (value === "center") {
        elementData.htmlBuilder.style("text-align", "center")
    } else {
        if (value === true) {
            elementData.renderer.warn(new MarkupWarning(property.tokens, `Attribute 'text-align' requires a value like text-align="right"!`))
        } else {
            elementData.renderer.warn(new MarkupWarning(property.tokens, `Unsupported value '${value.substring(0, 20)}' for attribute 'text-align'!`))
        }
    }
})


class SeatInfoMarkupElementHelper {
    static simpleElement(markupName, htmlName) {
        SeatInfoMarkupRenderer.registerElement(markupName, false, function (elementInfo, htmlElement) {
            return {
                dom: htmlElement(htmlName).content(elementInfo.content)
            }
        })
    }

    static simpleSelfClosingElement(markupName, htmlName) {
        SeatInfoMarkupRenderer.registerElement(markupName, true, function (elementInfo, htmlElement) {
            return {
                dom: htmlElement("span").content(htmlElement(htmlName)),
                noContent: true,
                disabledCommonProperties: ["text-align"]
            }
        })
    }

    static simpleLimitedContentElement(markupName, htmlName, allowedChildren = [], allowText = false) {
        SeatInfoMarkupRenderer.registerElement(markupName, false, function (elementInfo, htmlElement) {
            return {
                dom: htmlElement(htmlName).content(elementInfo.content.filter((e) => {
                    if (!allowText && e.type === "text") {
                        elementInfo.renderer.warn(new MarkupWarning(e.node.tokens, `<${markupName}> tags don't allow text content!`))
                        return false
                    }
                    if (e.type === "element" && !allowedChildren.includes(e.tagName)) {
                        elementInfo.renderer.warn(new MarkupWarning(e.node.tokens, `<${markupName}> tags don't allow <${e.tagName}> elements in them!`))
                        return false
                    }
                    return true
                }))
            }
        })
    }
}

SeatInfoMarkupElementHelper.simpleElement("p", "p")
SeatInfoMarkupElementHelper.simpleElement("b", "b")
SeatInfoMarkupElementHelper.simpleElement("i", "i")
SeatInfoMarkupElementHelper.simpleElement("s", "s")
SeatInfoMarkupElementHelper.simpleElement("h1", "h1")
SeatInfoMarkupElementHelper.simpleElement("h2", "h2")
SeatInfoMarkupElementHelper.simpleElement("h3", "h3")
SeatInfoMarkupElementHelper.simpleElement("h4", "h4")
SeatInfoMarkupElementHelper.simpleElement("h5", "h5")
SeatInfoMarkupElementHelper.simpleElement("h6", "h6")

SeatInfoMarkupElementHelper.simpleSelfClosingElement("br", "br")
SeatInfoMarkupElementHelper.simpleSelfClosingElement("hr", "hr")

//links
function linkElementBuilder(elementInfo, htmlElement) {
    const a = htmlElement("a").content(elementInfo.content)

    const url = elementInfo.renderer.preprocessLink(elementInfo.properties["href"])
        .getValue((msg) => {
            if (elementInfo.properties["src"]) {
                elementInfo.renderer.warn(new MarkupWarning(elementInfo.properties["src"].tokens, msg))
            } else {
                elementInfo.renderer.warn(new MarkupWarning(elementInfo.node.tokens, msg))
            }
        })

    if (url) {
        a.attribute("href", url)
    } else {
        if (!elementInfo.properties["href"]) {
            elementInfo.renderer.warn(new MarkupWarning(elementInfo.node.tokens, `<a> elements need a src property to link to another page!`))
        }
    }

    if (elementInfo.properties["newtab"]) {
        a.attribute("target", "_blank")
    }

    if (elementInfo.properties["download"]) {
        a.attribute("download", "")
    }

    return {
        dom: a,
        supportedElementProperties: ["href", "newtab", "download"]
    }
}

SeatInfoMarkupRenderer.registerElement("a", false, linkElementBuilder)
SeatInfoMarkupElementHelper.simpleSelfClosingElement("pagelink", "span") // deprecated legacy element

//lists
SeatInfoMarkupElementHelper.simpleElement("li", "li")
SeatInfoMarkupElementHelper.simpleLimitedContentElement("ul", "ul", ["li"], false)
SeatInfoMarkupElementHelper.simpleLimitedContentElement("ol", "ol", ["li"], false)

//tables
SeatInfoMarkupRenderer.registerElement("table", false, function (elementInfo, htmlElement) {
    const table = htmlElement("table")

    table.class("table")
    if (elementInfo.properties["stripes"]) {
        table.class("table-striped")
    }
    if (elementInfo.properties["border"]) {
        table.class("table-bordered")
    }

    table.content(elementInfo.content.filter((e) => {
        if (e.type === "text") {
            elementInfo.renderer.warn(new MarkupWarning(e.node.tokens, `<table> tags don't allow text content in them and text won't be rendered!`))
            return false
        }
        if (e.type === "element" && !(e.tagName === "thead" || e.tagName === "tbody")) {
            elementInfo.renderer.warn(new MarkupWarning(e.node.tokens, `<table> tags don't allow <${e.tagName}> elements in them and they won't be rendered!`))
            return false
        }
        return true
    }))

    return {
        dom: table,
        supportedElementProperties: ["stripes", "border"]
    }
})

function tableCellElementBuilder(type, elementInfo, htmlElement) {
    const cell = htmlElement(type).content(elementInfo.content)

    if (elementInfo.properties["colspan"]) {
        let value
        try {
            value = parseInt(elementInfo.properties["colspan"].value)
            cell.attribute("colspan", value)
        } catch (e) {
            elementInfo.renderer.warn(new MarkupWarning(elementInfo.node.tokens, `<${type}> element with attribute 'colspan' is not an integer!"`))
        }
    }

    cell.content(elementInfo.content.filter((e) => e.type === "element" && (e.tagName === "thead" || e.tagName === "tbody")))

    return {
        dom: cell,
        supportedElementProperties: ["colspan"]
    }
}

SeatInfoMarkupRenderer.registerElement("td", false, function (elementInfo, htmlElement) {
    return tableCellElementBuilder("td", elementInfo, htmlElement)
})
SeatInfoMarkupRenderer.registerElement("th", false, function (elementInfo, htmlElement) {
    return tableCellElementBuilder("th", elementInfo, htmlElement)
})
SeatInfoMarkupElementHelper.simpleLimitedContentElement("tr", "tr", ["td", "th"], false)
SeatInfoMarkupElementHelper.simpleLimitedContentElement("tbody", "tbody", ["tr"], false)
SeatInfoMarkupElementHelper.simpleLimitedContentElement("thead", "thead", ["tr"], false)

//images
function imageElementBuilder(elementInfo, htmlElement) {
    const img = htmlElement("img")

    //TODO img error handling

    const url = elementInfo.renderer.preprocessLink(elementInfo.properties["src"])
        .getValue((msg) => {
            if (elementInfo.properties["src"]) {
                elementInfo.renderer.warn(new MarkupWarning(elementInfo.properties["src"].tokens, msg))
            } else {
                elementInfo.renderer.warn(new MarkupWarning(elementInfo.node.tokens, msg))
            }
        })

    if (url) {
        img.attribute("src", url)
    } else {
        //TODO img has no url
    }

    const alt = elementInfo.properties["alt"] ? elementInfo.properties["alt"].value : "No alternative text was specified for this image"

    img.attribute("alt", alt)

    img.class("mw-100")

    img.event("error", () => {
        img.replaceWith(htmlElement("div")
            .class("alert", "alert-warning", "mb-0")
            .content(
                htmlElement("i").class("fas", "fa-ban"),
                " Image was not found! ",
                alt
            ))
    })

    return img
}

SeatInfoMarkupRenderer.registerElement("img", true, function (elementInfo, htmlElement) {
    return {
        dom: htmlElement("p").content(imageElementBuilder(elementInfo, htmlElement)),
        noContent: true,
        supportedElementProperties: ["src", "alt"],
    }
})
SeatInfoMarkupRenderer.registerElement("icon", true, function (elementInfo, htmlElement) {
    return {
        dom: htmlElement("span").content(imageElementBuilder(elementInfo, htmlElement)),
        noContent: true,
        supportedElementProperties: ["src", "alt"],
        disabledCommonProperties: ["text-align"]
    }
})

function colorElementBuilder(elementInfo, htmlElement) {
    const color = htmlElement("span").content(elementInfo.content)

    if (elementInfo.properties["color"]) {
        color.style("color", elementInfo.properties["color"].value)
    }
    if (elementInfo.properties["colour"]) {
        color.style("color", elementInfo.properties["colour"].value)
    }

    return {
        dom: color,
        supportedElementProperties: ["color", "colour"]
    }
}

SeatInfoMarkupRenderer.registerElement("color", false, colorElementBuilder)
SeatInfoMarkupRenderer.registerElement("colour", false, colorElementBuilder)

//multimedia helper functions
const formatSeconds = (duration) => {
    const seconds = duration % 60
    const minutes = Math.floor(duration / 60) % 60
    const hours = Math.floor(duration / 3600)

    const options = {
        maximumFractionDigits: 0,
        minimumIntegerDigits: 2,
    }

    if (hours > 0) {
        return `${hours.toLocaleString(undefined, options)}:${minutes.toLocaleString(undefined, options)}:${seconds.toLocaleString(undefined, options)}`
    }

    return `${minutes.toLocaleString(undefined, options)}:${seconds.toLocaleString(undefined, options)}`
}

const formatTimeStamp = (position, length) => {
    return `${formatSeconds(position)} / ${formatSeconds(length)}`
}

//audio
SeatInfoMarkupRenderer.registerElement("audio", true, function (elementInfo, htmlElement) {

    const url = elementInfo.renderer.preprocessLink(elementInfo.properties["src"])
        .getValue((msg) => {
            if (elementInfo.properties["src"]) {
                elementInfo.renderer.warn(new MarkupWarning(elementInfo.properties["src"].tokens, msg))
            } else {
                elementInfo.renderer.warn(new MarkupWarning(elementInfo.node.tokens, msg))
            }
        })

    if (url) {
        const audio = new Audio(url)

        const container = htmlElement("div")
            .class("d-flex")
            .class("align-items-center")
            .class("p-2", "my-1")
            .style("background-color", "#BBBBBB")
            .style("border-radius", "5px")

        const buttonIcon = htmlElement("i")
            .class("fas", "fa-play")

        const button = htmlElement("button")
            .class("btn", "btn-primary")
            .content(buttonIcon)
            .event("click", () => {
                if (audio.paused) {
                    audio.play();
                } else {
                    audio.pause()
                }
            })

        const label = htmlElement("span")
            .style("white-space", "nowrap")
            .class("m-1")
            .content("00:00 / 00:00")

        const progressBar = htmlElement("div")
            .class("progress-bar")

        const progress = htmlElement("div")
            .class("progress", "m-1", "w-100")
            .style("min-width", "150px")
            .content(progressBar)
            .event("click", function (e) {
                let progress = e.offsetX / e.currentTarget.offsetWidth
                audio.currentTime = progress * audio.duration
            })

        container.content(button, label, progress)

        audio.addEventListener("play", function () {
            buttonIcon.removeClass("fa-play")
            buttonIcon.class("fa-pause")
        })
        audio.addEventListener("ended", function () {
            buttonIcon.removeClass("fa-pause")
            buttonIcon.class("fa-play")
        })
        audio.addEventListener("pause", function () {
            buttonIcon.removeClass("fa-pause")
            buttonIcon.class("fa-play")
        })
        audio.addEventListener("durationchange", () => {
            let progress = audio.currentTime / audio.duration
            progressBar.style("width", `${progress * 100}%`)
            label.clearContent(formatTimeStamp(audio.currentTime, audio.duration))
        })
        audio.addEventListener("timeupdate", () => {
            let progress = audio.currentTime / audio.duration
            progressBar.style("width", `${progress * 100}%`)
            label.clearContent(formatTimeStamp(audio.currentTime, audio.duration))
        })

        return {
            dom: htmlElement("div").content(container),
            noContent: true,
            supportedElementProperties: ["src"],
            disabledCommonProperties: ["text-align"]
        }
    } else {
        elementInfo.renderer.warn(new MarkupWarning(elementInfo.node.tokens, `<audio /> element doesn't contain a valid audio source.`))
        return {
            dom: htmlElement("div"),
            noContent: true,
            supportedElementProperties: ["src"],
            disabledCommonProperties: ["text-align"]
        }
    }
})

SeatInfoMarkupRenderer.registerElement("fit", false, (elementInfo, htmlElement) => {
    const container = htmlElement("div")
        .class("p-2", "my-1")
        .style("background-color", "#BBBBBB")
        .style("border-radius", "5px")
        .style("text-align", "center")

    function renderFit(fit) {
        fit = fit.replace(/^(\r?\n)*/g, "")
        const matches = /^\[[\S ]+,\s*(?<name>[\S ]+?)\s*]$/gm.exec(fit)
        const shipName = matches.groups.name

        const title = htmlElement("h4").content(shipName)

        const contentRow = htmlElement("div")
            .class("d-flex", "flex-wrap", "justify-content-around")

        const fitElement = htmlElement("ship-fit")
            .content(fit)
            .style("width", "100%")
            .style("max-width", "30vw")
            .attribute("hide-copy", true)

        const textArea = htmlElement("textarea")
            .attribute("readonly", true)
            .class("form-control", "flex-grow-1")
            .style("resize", "none")
            .content(fit)

        const copy = htmlElement("button")
            .class("btn", "btn-primary", "btn-block", "mt-2")
            .content("Copy Fit")
            .attribute("type", "button")
            .event("click", (e) => {
                const button = e.currentTarget
                navigator.clipboard.writeText(fit)
                    .then(() => {
                        button.textContent = "Copied"
                        setTimeout(() => {
                            button.textContent = "Copy Fit"
                        }, 1000)
                    })
            })

        const textAreaRow = htmlElement("div")
            .class("flex-grow-1", "d-flex", "flex-column")
            .style("min-height", "20vh")
            .style("max-width", "30vw")
            .content(textArea)
            .content(copy)

        contentRow.content(fitElement)
        contentRow.content(textAreaRow)

        container.content(title)
        container.content(contentRow)
    }

    let isSelfClosingVariant
    if (elementInfo.properties["from"]) {
        isSelfClosingVariant = true
        let url = elementInfo.renderer.preprocessLink(elementInfo.properties["from"]).getValue((msg) => {
            elementInfo.renderer.warn(new MarkupWarning(elementInfo.properties["from"].tokens, msg))
        })

        try {
            url = new URL(url)
        } catch (e) {
            url = new URL(url,window.location.origin)
        }
        url.searchParams.append("api","true")
        url = url.toString()

        fetch(url)
            .then(res=>res.json())
            .then(fit=>{
                if(fit.ok){
                    renderFit(fit.fit)
                } else {
                    container.content(`Fit could not be loaded: ${fit.message}`)
                }
            })

    } else {
        isSelfClosingVariant = false
        let fit = ""
        for (const child of elementInfo.content) {
            if (child.node instanceof ASTText) {
                fit += child.node.text
            }
        }
        renderFit(fit)
    }

    return {
        dom: container,
        noContent: isSelfClosingVariant,
        supportedElementProperties: ["from"],
        disabledCommonProperties: ["text-align"]
    }
})

//video
SeatInfoMarkupRenderer.registerElement("video", true, (elementInfo, htmlElement) => {
    const url = elementInfo.renderer.preprocessLink(elementInfo.properties["src"])
        .getValue((msg) => {
            if (elementInfo.properties["src"]) {
                elementInfo.renderer.warn(new MarkupWarning(elementInfo.properties["src"].tokens, msg))
            } else {
                elementInfo.renderer.warn(new MarkupWarning(elementInfo.node.tokens, msg))
            }
        })

    if (!url) {
        elementInfo.renderer.warn(new MarkupWarning(elementInfo.node.tokens, `<video /> element doesn't contain a valid video source.`))
        return {
            dom: htmlElement("div"),
            noContent: true,
            supportedElementProperties: ["src"],
            disabledCommonProperties: ["text-align"]
        }
    } else {
        //we have a video

        const container = htmlElement("div")
            .class("p-2", "my-1")
            .class("d-flex")
            .class("flex-column")
            .style("background-color", "#BBBBBB")
            .style("border-radius", "5px")

        const videoElement = htmlElement("video")
            .attribute("src", url)

        const video = videoElement.getDOMElement()

        const controlsContainer = htmlElement("div")
            .class("d-flex", "mt-2")
            .class("align-items-center")

        const buttonIcon = htmlElement("i")
            .class("fas", "fa-play")

        const button = htmlElement("button")
            .class("btn", "btn-primary")
            .content(buttonIcon)
            .event("click", () => {
                if (video.paused) {
                    video.play();
                } else {
                    video.pause()
                }
            })

        const label = htmlElement("span")
            .style("white-space", "nowrap")
            .class("m-1")
            .content("00:00 / 00:00")

        const progressBar = htmlElement("div")
            .class("progress-bar")

        const progress = htmlElement("div")
            .class("progress", "m-1", "w-100")
            .style("min-width", "150px")
            .content(progressBar)
            .event("click", function (e) {
                let progress = e.offsetX / e.currentTarget.offsetWidth
                video.currentTime = progress * video.duration
            })

        controlsContainer.content(button, label, progress)
        container.content(videoElement, controlsContainer)

        video.addEventListener("play", function () {
            buttonIcon.removeClass("fa-play")
            buttonIcon.class("fa-pause")
        })
        video.addEventListener("ended", function () {
            buttonIcon.removeClass("fa-pause")
            buttonIcon.class("fa-play")
        })
        video.addEventListener("pause", function () {
            buttonIcon.removeClass("fa-pause")
            buttonIcon.class("fa-play")
        })
        video.addEventListener("durationchange", () => {
            let progress = video.currentTime / video.duration
            progressBar.style("width", `${progress * 100}%`)
            label.clearContent(formatTimeStamp(video.currentTime, video.duration))
        })
        video.addEventListener("timeupdate", () => {
            let progress = video.currentTime / video.duration
            progressBar.style("width", `${progress * 100}%`)
            label.clearContent(formatTimeStamp(video.currentTime, video.duration))
        })

        return {
            dom: htmlElement("div").content(container),
            noContent: true,
            supportedElementProperties: ["src"],
            disabledCommonProperties: ["text-align"]
        }
    }
})


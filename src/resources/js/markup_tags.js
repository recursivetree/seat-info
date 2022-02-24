class SeatInfoMarkupElementHelper {
    static simpleElement(markupName, htmlName) {
        SeatInfoMarkupRenderer.registerElement(markupName, false, function (elementInfo, htmlElement) {
            return {
                dom: htmlElement(htmlName).content(elementInfo.content)
            }
        })
    }

    static simpleSelfClosingElement(markupName, htmlName) {
        SeatInfoMarkupRenderer.registerElement(markupName,true, function (elementInfo, htmlElement) {
            return {
                dom: htmlElement("span").content(htmlElement(htmlName)),
                noContent: true
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

    if (elementInfo.properties["href"]) {
        a.attribute("href", process_seat_url(elementInfo.properties["href"].value))
    }

    if (elementInfo.properties["newtab"]) {
        a.attribute("target", "_blank")
    }

    if (elementInfo.properties["download"]) {
        a.attribute("download", "")
    }

    return {
        dom: a
    }
}

SeatInfoMarkupRenderer.registerElement("a",false, linkElementBuilder)
SeatInfoMarkupRenderer.registerElement("pagelink", false, linkElementBuilder) // deprecated legacy function

//lists
SeatInfoMarkupElementHelper.simpleElement("li", "li")
SeatInfoMarkupElementHelper.simpleLimitedContentElement("ul", "ul", ["li"], false)
SeatInfoMarkupElementHelper.simpleLimitedContentElement("ol", "ol", ["li"], false)

//tables
SeatInfoMarkupRenderer.registerElement("table",false, function (elementInfo, htmlElement) {
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
        if (e.type === "element" && !(e.tagName==="thead" || e.tagName==="tbody")) {
            elementInfo.renderer.warn(new MarkupWarning(e.node.tokens, `<table> tags don't allow <${e.tagName}> elements in them and they won't be rendered!`))
            return false
        }
        return true
    }))

    return {
        dom: table
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
        dom: cell
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

    if (elementInfo.properties["src"]) {
        img.attribute("src", process_seat_url(elementInfo.properties["src"].value))
    }
    if (elementInfo.properties["alt"]) {
        img.attribute("alt", elementInfo.properties["alt"].value)
    }
    img.class("mw-100")

    return img
}
SeatInfoMarkupRenderer.registerElement("img", true,function (elementInfo, htmlElement) {
    return {
        dom: htmlElement("p").content(imageElementBuilder(elementInfo, htmlElement)),
        noContent: true
    }
})
SeatInfoMarkupRenderer.registerElement("icon",true, function (elementInfo, htmlElement) {
    return {
        dom: htmlElement("span").content(imageElementBuilder(elementInfo, htmlElement)),
        noContent: true
    }
})

function colorElementBuilder (elementInfo, htmlElement) {
    const color = htmlElement("span").content(elementInfo.content)

    if (elementInfo.properties["color"]) {
        color.style("color", elementInfo.properties["color"].value)
    }
    if (elementInfo.properties["colour"]) {
        color.style("color", elementInfo.properties["colour"].value)
    }

    return {
        dom: color
    }
}
SeatInfoMarkupRenderer.registerElement("color",false, colorElementBuilder)
SeatInfoMarkupRenderer.registerElement("colour",false, colorElementBuilder)

//audio
SeatInfoMarkupRenderer.registerElement("audio",true, function (elementInfo, htmlElement) {
    if (elementInfo.properties["src"]) {

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

        const audio = new Audio(process_seat_url(elementInfo.properties["src"].value))

        const container = htmlElement("div")
            .class("d-flex")
            .class("align-items-center")
            .class("p-2")
            .style("background-color", "#BBBBBB")
            .style("border-radius", "5px")

        const buttonIcon = htmlElement("i")
            .class("fas", "fa-play")

        const button = htmlElement("button")
            .class("btn", "btn-primary")
            .content(buttonIcon)
            .event("click",() => {
                if (audio.paused) {
                    audio.play();
                } else {
                    audio.pause()
                }
            })

        const label = htmlElement("span")
            .style("white-space","nowrap")
            .class("m-1")
            .content("00:00 / 00:00")

        const progressBar = htmlElement("div")
            .class("progress-bar")

        const progress = htmlElement("div")
            .class("progress","m-1","w-100")
            .style("min-width","150px")
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
            noContent: true
        }
    } else {
        elementInfo.renderer.warn(new MarkupWarning(elementInfo.node.tokens, `<audio /> element doesn't contain a source. Specify one with <audio src="seatinfo:resource/..." />`))
        return {
            dom: htmlElement("div"),
            noContent: true
        }
    }
})


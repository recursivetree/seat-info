function editor(container) {
    const textArea = document.createElement("textarea")
    const previewArea = document.createElement("div")

    textArea.style.setProperty("position","relative")
    previewArea.style.setProperty("position","relative")

    container.appendChild(textArea)
    container.appendChild(previewArea)
}
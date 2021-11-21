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

class DOMBuilder {
    constructor(target) {
        this.stack = [target]
    }

    addNode(node) {
        let element = this.stack[this.stack.length - 1]
        element.appendChild(node)
    }

    pushNode(node) {
        this.addNode(node)
        this.stack.push(node)
    }

    dropNode() {
        this.stack.pop()
    }
}

function render_article(src, target) {
    function redirect_resource_url(base) {
        
    }

    const tag_handlers = {
        "br": function(builder, arguments) {
            let brNode = document.createElement("br")
            builder.addNode(brNode)
        },
        "b": function (builder, arguments) {
            let bNode = document.createElement("b")
            builder.pushNode(bNode)
        },
        "a": function (builder, arguments) {
            let bNode = document.createElement("a")

            if (arguments.href){
                bNode.setAttribute("href", arguments.href)
            }

            if (arguments.newtab){
                bNode.setAttribute("target", "_blank")
            }

            builder.pushNode(bNode)
        },
        "download": function (builder, arguments) {
            let bNode = document.createElement("a")

            if (arguments.href){
                bNode.setAttribute("href", arguments.href)
            }

            bNode.setAttribute("download", "")

            builder.pushNode(bNode)
        }
    }

    let reader = new CharReader(src)
    let textNodeStart = reader.position()

    let builder = new DOMBuilder(target)

    function finish_text_node(start, end) {
        //return nothing if both are equal
        if (start===end){
            return
        }
        let text = reader.range(start, end)
        if (text.length === 0) {
            return
        }
        let node = document.createTextNode(text)
        builder.addNode(node)
    }

    while (reader.hasNext()) {
        let c = reader.next()

        //newline
        if (c === "\r" && reader.hasNext() && reader.next() === "\n") {
            finish_text_node(textNodeStart, reader.position(-2))

            let brNode = document.createElement("br")
            builder.addNode(brNode)

            textNodeStart = reader.position()
        }
        //tags
        else if (c === "<") {
            finish_text_node(textNodeStart, reader.position(-2)) // next advances by one and we want to go back by one, so -1+-1=2

            let closing

            //check if it is opening or closing
            reader.ensureNext()
            if (reader.next() === "/") {
                //closing tag
                closing = true
            } else {
                // opening tag, skip back for reading the tag name
                reader.back()
                closing = false
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
            while(true) {
                // read spaces util the argument starts
                reader.ensureNext()
                let n = reader.next()

                // tag end
                if (n===">"){
                    break
                }

                // check for a space between arguments
                if (n!==" "){
                    throw Error("Expected a space")
                }

                let argNameStart = reader.position()
                while (true){
                    reader.ensureNext()
                    let a = reader.next()
                    // check if the arg name is finished. Cases: without parameter + more arguments follow, with parameter, without parameter+tag end
                    if (a===" "||a==="="||a===">"){
                        reader.back()
                        break
                    }
                }
                let argNameEnd = reader.position(-1)
                let argName = reader.range(argNameStart, argNameEnd)

                reader.ensureNext()
                let t = reader.next()
                if (t===" "){
                    arguments[argName] = true
                    reader.back() // in order to continue, we need to go back, as we check for a single space at the beginning of the loop
                } else if (t===">"){
                    arguments[argName] = true
                    break // end of argument list
                } else if (t==="="){
                    reader.ensureNext()
                    let stringChar = reader.next()
                    if (!(stringChar==='"' || stringChar==="'")){
                        throw Error('expected character " after argument with parameter')
                    }
                    let parameterStart = reader.position()
                    while (true){
                        reader.ensureNext()
                        let p = reader.next()
                        if (p===stringChar){
                            reader.back()
                            break
                        }
                    }
                    let parameterEnd = reader.position(-1)
                    arguments[argName] = reader.range(parameterStart, parameterEnd)

                    // check how to continue after argument
                    reader.ensureNext()
                    let t = reader.next()
                    if (t===" "){
                        reader.back() // in order to continue, we need to go back, as we check for a single space at the beginning of the loop
                    } else if (t===">"){
                        break // end of argument list
                    }
                }
            }

            // build DOM
            if (closing) {
                // handle closing tag
                builder.dropNode()
            } else {
                // handle opening tag
                let handler = tag_handlers[tagName]
                if (handler) {
                    handler(builder, arguments)
                } else {
                    throw Error("unknown tag")
                }
            }

            textNodeStart = reader.position()
        }
    }

    finish_text_node(textNodeStart, reader.position())

    //target.textContent = src
}
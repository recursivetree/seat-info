class CharReader {
    constructor(lines) {
        this.lineIndex = 0
        this.colIndex = 0
        this.lines = lines
        this.end_reached = false
    }

    move(offset){
        let lineIndex = this.lineIndex
        let colIndex = this.colIndex

        if(0<offset){
            for (let i = 0; i < offset; i++) {
                colIndex += 1
                if (colIndex < this.lines[lineIndex].length){
                    continue
                }
                while (true){
                    lineIndex += 1
                    if (!(lineIndex < this.lines.length)) return null
                    if (this.lines[lineIndex].length>0) {
                        colIndex = 0
                        break
                    }
                }
            }
            return {
                lineIndex,
                colIndex
            }
        } else {
            offset = -offset
            for (let i = 0; i < offset; i++) {
                colIndex -= 1
                if (0 <= colIndex){
                    continue
                }
                while (true){
                    lineIndex -= 1
                    if(lineIndex < 0){
                        return null
                    }
                    if(this.lines[lineIndex].length > 0){
                        colIndex = this.lines[lineIndex].length - 1
                        break
                    }
                }
            }
            return {
                lineIndex,
                colIndex
            }
        }
    }

    next() {
        if(this.end_reached){
            return null
        }

        const c = this.lines[this.lineIndex].charAt(this.colIndex)

        const newPos = this.move(1)
        if(newPos) {
            this.lineIndex = newPos.lineIndex
            this.colIndex = newPos.colIndex
        } else {
            this.end_reached = true
            this.colIndex += 1 //in order for position() to work correctly
        }

        return c
    }

    back() {
        const newPos = this.move(-1)
        this.lineIndex = newPos.lineIndex
        this.colIndex = newPos.colIndex
    }

    position(offset = 0) {
        return this.move(offset-1)
    }

    range(start, stop) {
        if(start.lineIndex===stop.lineIndex){
            const text = this.lines[start.lineIndex]
            return text.substring(start.colIndex,stop.colIndex+1)
        } else {
            let text = this.lines[start.lineIndex].substring(start.colIndex)

            for (let i = start.lineIndex+1; i < stop.lineIndex; i++) {
                text += this.lines[i]
            }

            text += this.lines[stop.lineIndex].substring(0,stop.colIndex+1)
            return text
        }
    }
}

class Token {
    static TokenType = {
        OPEN_TAG: 1,
        CLOSE_TAG: 2,
        EQUALS: 3,
        TEXT: 4,
        QUOTES: 5,
        SLASH: 6,
        WHITESPACE: 7,
        ESCAPE_SEQUENCE: 8,
    }

    constructor(type, src, start, end) {
        this.type = type
        this.src = src
        this.start = start
        this.end = end
    }

    static getRange(tokens){
        let start = {
            lineIndex: Number.MAX_SAFE_INTEGER,
            colIndex: Number.MAX_SAFE_INTEGER
        }
        let end = {
            lineIndex: Number.MIN_SAFE_INTEGER,
            colIndex: Number.MIN_SAFE_INTEGER
        }

        for (const token of tokens) {
            if(token.start.lineIndex <= start.lineIndex){
                start.lineIndex = token.start.lineIndex
                if(token.start.colIndex <= start.colIndex){
                    start.colIndex = token.start.colIndex
                }
            }
            if(token.end.lineIndex >= end.lineIndex){
                end.lineIndex = token.end.lineIndex
                if(token.end.colIndex >= end.colIndex){
                    end.colIndex = token.end.colIndex
                }
            }
        }

        return {
            start,
            end
        }
    }
}

class TokenReader {
    constructor(tokens) {
        this.tokens = tokens
        this.index = 0
    }

    hasNext() {
        return this.index < this.tokens.length
    }

    next() {
        return this.tokens[this.index++]
    }

    back() {
        this.index--
    }

    skipWhiteSpace() {
        while (this.hasNext()) {
            const token = this.next()
            if (token.type !== Token.TokenType.WHITESPACE) {
                this.index--
                return
            }
        }
    }

    tokenRange(start,end){
        return this.tokens.slice(start,end)
    }

    position(offset=0){
        return this.index+offset
    }
}

class Stack {
    constructor(base = []) {
        this.stack = base
    }

    push(e) {
        this.stack.push(e)
    }

    pop() {
        return this.stack.pop()
    }

    peek() {
        return this.stack[this.stack.length - 1]
    }

    size() {
        return this.stack.length
    }

    get(i){
        return this.stack[i]
    }
}

class MarkupWarning {
    constructor(tokens, message) {
        this.tokens = tokens
        this.message = message
    }
}

class ASTBase {
    constructor(tokens) {
        this.tokens = tokens
        this.range = Token.getRange(tokens)
    }

    isIn(start, end){
        const inStart = (this.range.start.lineIndex < start.lineIndex) || (this.range.start.lineIndex === start.lineIndex && this.range.start.colIndex <= start.colIndex)
        const inEnd = (this.range.end.lineIndex > end.lineIndex) || (this.range.end.lineIndex === end.lineIndex && this.range.end.colIndex+1 >= end.colIndex)

        return inStart && inEnd
    }
}

class ASTTag extends ASTBase {
    constructor(tokens, tagName, properties, parent) {
        super(tokens);
        this.tagName = tagName
        this.content = []
        this.properties = {}
        for (const property of properties) {
            if(this.properties[property.name]){
                //TODO emit warning
            }
            this.properties[property.name] = property
        }
        this.parent = parent
    }

    appendNode(node) {
        this.content.push(node)
    }

    appendTokens(tokens){
        this.tokens.push(...tokens)
        this.range = Token.getRange(this.tokens)
    }
}

class ASTText extends ASTBase {
    constructor(tokens,parent) {
        super(tokens);
        this.text = tokens.reduce((string,token)=>string+token.src,"")
        this.parent = parent
    }
}

class ASTTagProperty extends ASTBase{
    constructor(tokens,name,value) {
        super(tokens);
        this.name = name
        this.value = value
    }
}

const parse = (lines) => {
    const warnings = []

    const reader = new CharReader(lines)
    const tokens = []

    const singleCharacterMap = {
        "<": Token.TokenType.OPEN_TAG,
        ">": Token.TokenType.CLOSE_TAG,
        "=": Token.TokenType.EQUALS,
        "'": Token.TokenType.QUOTES,
        "\"": Token.TokenType.QUOTES,
        "/": Token.TokenType.SLASH,
        " ": Token.TokenType.WHITESPACE,
        "\n": Token.TokenType.WHITESPACE,
        "\t": Token.TokenType.WHITESPACE,
    }

    let textTokenStart = null

    while (true) {
        const c = reader.next()
        if(c==null){
            break
        }

        let isEscaped = false

        if(c === "\\"){
            const n = reader.next()
            if(!n){
                warnings.push(new MarkupWarning([new Token(Token.TokenType.ESCAPE_SEQUENCE,c,reader.position(),reader.position())],
                    "Expected another character following the escape character '\\', reading the '\\' as literal. For literal backslashes, please escape it as '\\\\'."))
                continue
            }

            if(singleCharacterMap[n]){
                const type = singleCharacterMap[n]
                if(type === Token.TokenType.WHITESPACE){
                    warnings.push(new MarkupWarning([new Token(Token.TokenType.ESCAPE_SEQUENCE,c+n,reader.position(-1),reader.position())],
                        "Whitespace shouldn't be escaped, reading the '\\' as literal. For literal backslashes, please escape it as '\\\\'."
                    ))
                    reader.back() // go back so the following character after the \ is handled normally
                } else {
                    isEscaped = true
                }
            } else if(n==="\\") {
                isEscaped = true // escaping a \. \ is not in singleCharacterMap, but still escapable
            } else {
                warnings.push(new MarkupWarning([new Token(Token.TokenType.ESCAPE_SEQUENCE,c+n,reader.position(-1),reader.position())],
                    `You don't need to escape the character '${n}', reading the '\\' as literal. For literal backslashes, please escape it as '\\\\'.`
                ))
                reader.back() // go back so the following character after the \ is handled normally
            }
        }

        if (!isEscaped && singleCharacterMap[c]) {
            if (textTokenStart !== null) {
                const end = reader.position(-1)
                const content = reader.range(textTokenStart, end)
                tokens.push(new Token(Token.TokenType.TEXT, content,textTokenStart,end))
                textTokenStart = null
            }

            tokens.push(new Token(singleCharacterMap[c], c,reader.position(),reader.position()))
        } else if (textTokenStart === null) {
            textTokenStart = reader.position()
        }
    }

    if (textTokenStart !== null) {
        const content = reader.range(textTokenStart, reader.position())
        tokens.push(new Token(Token.TokenType.TEXT, content,textTokenStart,reader.position()))
    }

    let textNodeTokens = []
    const tokenReader = new TokenReader(tokens)

    const elementStack = new Stack([new ASTTag(tokens, null, [],null)])

    let token

    mainParserLoop: while (tokenReader.hasNext()) {
        token = tokenReader.next()

        if (token.type !== Token.TokenType.OPEN_TAG) {
            textNodeTokens.push(token)
        } else {
            //tag starts

            let tagStartTokenIndex = tokenReader.position(-1)

            //finish previous text nodes
            if (textNodeTokens.length > 0) {

                //check if text is only whitespace
                if (textNodeTokens.reduce((previousValue, currentValue) => previousValue || currentValue.type !== Token.TokenType.WHITESPACE ,false)) {
                    //it isn't only whitespace, so generate a text node
                    const astText = new ASTText(textNodeTokens,elementStack.peek())
                    elementStack.peek().appendNode(astText)
                    textNodeTokens = []
                }
            }

            tokenReader.skipWhiteSpace()
            if (!tokenReader.hasNext()) {
                warnings.push(new MarkupWarning([token], "Expected the tag name or a closing slash after an opening tag!"))
                continue mainParserLoop
            }

            token = tokenReader.next()
            if (token.type === Token.TokenType.TEXT) {
                //it's a opening tag

                //get tag name
                const tagName = token.src

                const properties = []
                let selfClosingTag = false

                propertyLoop: while (true) {
                    //skip whitespace until we reach the tag name
                    tokenReader.skipWhiteSpace()

                    if (!tokenReader.hasNext()) {
                        warnings.push(new MarkupWarning(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), "Expected a property name, self-closing slash or '>' after the tag name!"))
                        continue mainParserLoop
                    }
                    token = tokenReader.next()

                    if (token.type === Token.TokenType.CLOSE_TAG) {
                        break // tag ends
                    } else if(token.type === Token.TokenType.SLASH){
                        //self closing tag
                        selfClosingTag = true

                        tokenReader.skipWhiteSpace()

                        if (!tokenReader.hasNext()) {
                            warnings.push(new MarkupWarning(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), "Expected a '>' after a closing slash!"))
                            break
                        }
                        token = tokenReader.next()

                        if(token.type !== Token.TokenType.CLOSE_TAG){
                            warnings.push(new MarkupWarning(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), "Expected a '>' after the closing slash of a self-closing element!"))
                            tokenReader.back()
                        }

                        break

                    } else if (token.type === Token.TokenType.TEXT) {
                        const propertyStart = tokenReader.position(-1)
                        const propertyName = token.src

                        tokenReader.skipWhiteSpace()

                        if (!tokenReader.hasNext()) {
                            warnings.push(new MarkupWarning(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), "Expected the next property name, a closing '>' or the equals sign if the property has a value!"))
                            continue mainParserLoop
                        }
                        token = tokenReader.next()

                        if (token.type === Token.TokenType.TEXT || token.type === Token.TokenType.CLOSE_TAG || token.type === Token.TokenType.SLASH) {
                            //property without value
                            tokenReader.back()

                            //token range is non-inclusive
                            const property = new ASTTagProperty(tokenReader.tokenRange(propertyStart,propertyStart+1),propertyName,true)
                            properties.push(property)

                            continue propertyLoop
                        } else if (token.type === Token.TokenType.EQUALS) {
                            tokenReader.skipWhiteSpace()

                            //search opening quote
                            if (!tokenReader.hasNext()) {
                                warnings.push(new MarkupWarning(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), "Expected a \" or ' to define a property with value!"))
                                continue mainParserLoop
                            }

                            token = tokenReader.next()

                            if (token.type !== Token.TokenType.QUOTES) {
                                warnings.push(new MarkupWarning(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), "Expected a \" or ' to define a property with value!"))
                                continue mainParserLoop
                            }

                            let argString = ""
                            while (true) {
                                if (!tokenReader.hasNext()) {
                                    warnings.push(new MarkupWarning(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), "Property value is not terminated with \" or '!"))
                                    continue mainParserLoop
                                }
                                token = tokenReader.next()

                                if (token.type === Token.TokenType.QUOTES) {
                                    break
                                }

                                argString += token.src
                            }

                            const property = new ASTTagProperty(tokenReader.tokenRange(propertyStart,tokenReader.position()),propertyName,argString)
                            properties.push(property)

                            continue propertyLoop
                        } else {
                            warnings.push(new MarkupWarning([token], "Expected the next property name, a closing '>' or the equals sign if the property has a value!"))
                            continue propertyLoop
                        }

                    } else {
                        warnings.push(new MarkupWarning([token], "Expected a property name, self-closing slash or closing '>'!"))
                        continue mainParserLoop
                    }
                }

                const stackTop = elementStack.peek()

                const tag = new ASTTag(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), tagName, properties, stackTop)

                stackTop.appendNode(tag)

                if(!selfClosingTag) {
                    elementStack.push(tag)
                }

            } else if (token.type === Token.TokenType.SLASH) {
                //it's a closing tag

                //skip whitespace until we reach the tag name
                tokenReader.skipWhiteSpace()

                //check for tag name
                if (!tokenReader.hasNext()) {
                    warnings.push(new MarkupWarning(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), "Expected the tag name after the slash of a closing tag!"))
                    continue mainParserLoop
                }
                token = tokenReader.next()
                if (token.type !== Token.TokenType.TEXT) {
                    warnings.push(new MarkupWarning(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), "Tag names should only consist out of alphabetic characters!"))
                    continue mainParserLoop
                }
                let tagName = token.src

                tokenReader.skipWhiteSpace()

                //check for closing >
                if (!tokenReader.hasNext()) {
                    warnings.push(new MarkupWarning(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), "Expected a '>' to end the tag!"))
                    continue mainParserLoop
                }
                token = tokenReader.next()
                if (token.type !== Token.TokenType.CLOSE_TAG) {
                    warnings.push(new MarkupWarning(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), "Expected a '>' to end the tag!"))
                    continue mainParserLoop
                }

                let stackTop = elementStack.peek()
                if (stackTop.tagName !== tagName){
                    warnings.push(new MarkupWarning(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), "Closing tag doesn't match last opened tag!"))
                } else {
                    //actually close the tag
                    const tokens = tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position())

                    const element = elementStack.pop()
                    element.appendTokens(tokens)
                }

            } else {
                warnings.push(new MarkupWarning(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), "Expected a tag name or slash after an opening '<'!"))
                continue mainParserLoop
            }
        }
    }

    //finish previous text nodes
    if (textNodeTokens.length > 0) {
        const astText = new ASTText(textNodeTokens,elementStack.peek())
        elementStack.peek().appendNode(astText)
    }

    if(elementStack.size()>1){
        warnings.push(new MarkupWarning([token], `Unclosed tags at the end! You might be able to fix this by adding '${elementStack.stack.slice(1).reduceRight((p,t)=>p+`</${t.tagName}>`,"")}'`))
    }


    return {
        warnings,
        rootNode: elementStack.get(0)
    }
}

// console.time('doSomething')
// const r = parse([""," <a>"])
// console.timeEnd('doSomething')
// console.log(r)

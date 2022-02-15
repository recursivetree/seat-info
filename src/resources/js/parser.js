class CharReader {
    constructor(src) {
        this.index = -1
        this.src = src
    }

    next() {
        return this.src.charAt(++this.index)
    }

    back() {
        this.index--
        if (this.index < -1) {
            throw Error("Reader can't go back out of string")
        }
    }

    hasNext() {
        return this.index < this.src.length - 1
    }

    position(offset = 0) {
        if (this.index < 0) {
            return offset
        }
        return this.index + offset
    }

    range(start, stop) {
        return this.src.substring(start, stop + 1)
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
        WHITESPACE: 7
    }

    constructor(type, src, start, end) {
        this.type = type
        this.src = src
        this.start = start
        this.end = end
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
    }
}

class ASTTag extends ASTBase {
    constructor(tokens, tagName, properties) {
        super(tokens);
        this.tagName = tagName
        this.content = []
        this.properties = properties
    }

    appendNode(node) {
        this.content.push(node)
    }
}

class ASTText extends ASTBase {
    constructor(tokens) {
        super(tokens);
        this.text = tokens.reduce((string,token)=>string+token.src,"")
    }
}

const parse = (text) => {
    const reader = new CharReader(text)
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

    while (reader.hasNext()) {
        const c = reader.next()

        if (singleCharacterMap[c]) {
            if (textTokenStart !== null) {
                const content = reader.range(textTokenStart, reader.position(-1))
                tokens.push(new Token(Token.TokenType.TEXT, content,textTokenStart,reader.position(-1)))
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

    const elementStack = new Stack([new ASTTag(null, null, {})])

    const warnings = []

    var token

    mainParserLoop: while (tokenReader.hasNext()) {
        token = tokenReader.next()

        if (token.type !== Token.TokenType.OPEN_TAG) {
            textNodeTokens.push(token)
        } else {
            //tag starts

            let tagStartTokenIndex = tokenReader.position(-1)

            //finish previous text nodes
            if (textNodeTokens.length > 0) {
                const astText = new ASTText(textNodeTokens)
                elementStack.peek().appendNode(astText)
                textNodeTokens = []
            }

            tokenReader.skipWhiteSpace()
            if (!tokenReader.hasNext()) {
                warnings.push(new MarkupWarning([token], "Unexpected end of source!"))
                continue mainParserLoop
            }

            token = tokenReader.next()
            if (token.type === Token.TokenType.TEXT) {
                //it's a opening tag

                //get tag name
                const tagName = token.src

                const properties = {}
                let selfClosingTag = false

                propertyLoop: while (true) {
                    //skip whitespace until we reach the tag name
                    tokenReader.skipWhiteSpace()

                    if (!tokenReader.hasNext()) {
                        warnings.push(new MarkupWarning([token], "Expected a property name or closing '>' and not the source end!"))
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
                            warnings.push(new MarkupWarning([token], "Unclosed tag at the source end"))
                            break
                        }
                        token = tokenReader.next()

                        if(token.type !== Token.TokenType.CLOSE_TAG){
                            warnings.push(new MarkupWarning([token], "In a self-closing tag, after the '/' a '>' must follow immediately"))
                            tokenReader.back()
                        }

                        break

                    } else if (token.type === Token.TokenType.TEXT) {
                        const propertyName = token.src

                        tokenReader.skipWhiteSpace()

                        if (!tokenReader.hasNext()) {
                            warnings.push(new MarkupWarning([token], "Unexpected end of source"))
                            continue mainParserLoop
                        }
                        token = tokenReader.next()

                        if (token.type === Token.TokenType.TEXT || token.type === Token.TokenType.CLOSE_TAG) {
                            //property without value
                            tokenReader.back()
                            properties[propertyName] = true
                            continue propertyLoop
                        } else if (token.type === Token.TokenType.EQUALS) {
                            tokenReader.skipWhiteSpace()

                            //search opening quote
                            if (!tokenReader.hasNext()) {
                                warnings.push(new MarkupWarning([token], "Expected a \" instead of source end!"))
                                continue mainParserLoop
                            }

                            token = tokenReader.next()

                            if (token.type !== Token.TokenType.QUOTES) {
                                warnings.push(new MarkupWarning([token], "Expected a \"!"))
                                continue mainParserLoop
                            }

                            let argString = ""
                            while (true) {
                                if (!tokenReader.hasNext()) {
                                    warnings.push(new MarkupWarning([token], "Property value is not encapsulated with a \"!"))
                                    continue mainParserLoop
                                }
                                token = tokenReader.next()

                                if (token.type === Token.TokenType.QUOTES) {
                                    break
                                }

                                argString += token.src
                            }

                            properties[propertyName] = argString

                            continue propertyLoop
                        } else {
                            warnings.push(new MarkupWarning([token], "Unexpected token!"))
                            continue propertyLoop
                        }

                    } else {
                        warnings.push(new MarkupWarning([token], "Expected a property name or closing '>'!"))
                        continue mainParserLoop
                    }
                }

                const tokens = tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position())

                const tag = new ASTTag(tokens, tagName, properties)

                const stackTop = elementStack.peek()
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
                    warnings.push(new MarkupWarning([token], "Expected the tag name instead of source end!"))
                    continue mainParserLoop
                }
                token = tokenReader.next()
                if (token.type !== Token.TokenType.TEXT) {
                    warnings.push(new MarkupWarning([token], "Expected a text token!"))
                    continue mainParserLoop
                }
                let tagName = token.src

                tokenReader.skipWhiteSpace()

                //check for closing >
                if (!tokenReader.hasNext()) {
                    warnings.push(new MarkupWarning([token], "Expected a '>' to close the tag instead of the source end"))
                    continue mainParserLoop
                }
                token = tokenReader.next()
                if (token.type !== Token.TokenType.CLOSE_TAG) {
                    warnings.push(new MarkupWarning([token], "Expected a '>' to close the tag!"))
                    continue mainParserLoop
                }

                let stackTop = elementStack.peek()
                while (stackTop.tagName !== tagName) {//nn
                    warnings.push(new MarkupWarning([token], "Unclosed tags!"))

                    if(elementStack.size()<=1){
                        continue mainParserLoop
                    }

                    elementStack.pop()
                    stackTop = elementStack.peek()
                }

                //actually close the tag
                const tokens = tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position())

                const element = elementStack.pop()
                element.tokens = element.tokens.concat(tokens)

            } else {
                warnings.push(new MarkupWarning([token], "Unexpected token, expected text or '/'"))
                continue mainParserLoop
            }
        }
    }

    //finish previous text nodes
    if (textNodeTokens.length > 0) {
        const astText = new ASTText(textNodeTokens)
        elementStack.peek().appendNode(astText)
    }

    if(elementStack.size()>1){
        warnings.push(new MarkupWarning([token], `Unclosed tags at the end! You might be able to fix this by adding '${elementStack.stack.slice(1).reduceRight((p,t)=>p+`</${t.tagName}>`,"")}'`))
    }


    return {
        warnings,
        ast: elementStack.get(0)
    }
}

// console.time('doSomething')
// const r = parse("<h1>asd</h1>dff/<p class='as'>This is a lot of    text</p>")
// console.timeEnd('doSomething')
// console.log(r)

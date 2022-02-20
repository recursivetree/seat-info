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
        WHITESPACE: 7,
        ESCAPE_SEQUENCE: 8,
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
    const warnings = []

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

        let isEscaped = false

        if(c === "\\"){
            if(!reader.hasNext()){
                warnings.push(new MarkupWarning([new Token(Token.TokenType.ESCAPE_SEQUENCE,c,reader.position(),reader.position())],
                    "Expected another character following the escape character '\\', reading the '\\' as literal. For literal backslashes, please escape it as '\\\\'."))
                continue
            }

            const n = reader.next()
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
                warnings.push(new MarkupWarning([token], "Expected the tag name or a closing slash after an opening tag!"))
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
                            properties[propertyName] = true
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

                            properties[propertyName] = argString

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

                const tag = new ASTTag(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), tagName, properties)

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
                    element.tokens = element.tokens.concat(tokens)
                }

            } else {
                warnings.push(new MarkupWarning(tokenReader.tokenRange(tagStartTokenIndex,tokenReader.position()), "Expected a tag name or slash after an opening '<'!"))
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

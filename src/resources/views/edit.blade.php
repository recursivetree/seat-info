@extends('web::layouts.grids.12')

@section('title', trans('info::info.module_title'))
@section('page_header', trans('info::info.module_title'))

@push('head')
    <style>
        .monospace-font{
            font-family: monospace;
        }
    </style>
@endpush

@section('full')

    <!-- Instructions -->
    <div class="row w-100">
        <div class="col">
            <div class="card-column">

                @if (session()->has('message'))
                    @if(session()->get('message')["type"]=="success")
                        <div class="alert alert-success">
                            <p class="card-text">{{ session()->get('message')['message'] }}</p>
                        </div>
                    @elseif(session()->get('message')["type"]=="warning")
                        <div class="alert alert-warning">
                            <p class="card-text">{{ session()->get('message')['message'] }}</p>
                        </div>
                    @elseif(session()->get('message')["type"]=="error")
                        <div class="alert alert-danger">
                            <p class="card-text">{{ session()->get('message')['message'] }}</p>
                        </div>
                    @endif
                @endif

                <div class="card">
                    <div class="card-header">Editor</div>
                    <div class="card-body">

                        <form method="post" action="{{ route('info.save') }}">
                            @csrf

                            <input type="hidden" name="id" value="{{ $article->id }}">

                            <div class="form-group">
                                <label for="name">{{ trans('info::info.article_name') }}</label>
                                <input type="text" name="name" class="form-control" id="name"
                                       placeholder="{{ trans('info::info.article_name') }}" required
                                       value="{{ $article->name }}">
                            </div>

                            <div class="d-flex w-100 form-group flex-column align-items-start">
                                <div>
                                    <label for="text">{{ trans('info::info.article_content') }}</label>
                                </div>

                                <div class="btn-group mb-1 btn-group-sm">
                                    <button type="button" class="btn btn-secondary" id="button-insert-heading"><i class="fas fa-heading"></i></button>
                                    <button type="button" class="btn btn-secondary" id="button-insert-paragraph"><i class="fas fa-paragraph"></i></button>
                                    <button type="button" class="btn btn-secondary" id="button-insert-bold"><i class="fas fa-bold"></i></button>
                                    <button type="button" class="btn btn-secondary" id="button-insert-italic"><i class="fas fa-italic"></i></button>
                                    <button type="button" class="btn btn-secondary" id="button-insert-link"><i class="fas fa-link"></i></button>
                                    <button type="button" class="btn btn-secondary" id="button-insert-image"><i class="far fa-image"></i></button>
                                    <button type="button" class="btn btn-secondary" id="button-insert-list"><i class="fas fa-list"></i></button>
                                    <button type="button" class="btn btn-secondary" id="button-insert-list-ol"><i class="fas fa-list-ol"></i></button>
                                    <button type="button" class="btn btn-secondary" id="button-insert-color"><i class="fas fa-palette"></i></button>
                                </div>

                                <div class="d-flex w-100" style="max-height: 60vh; min-height: 20vh;">
                                    <div class="w-50">
                                        <textarea name="text" style="resize: none;" class="form-control monospace-font text-sm h-100" id="text" placeholder="{{ trans('info::info.article_content_placeholder') }}" required>{{ $article->text }}</textarea>
                                    </div>
                                    <div class="w-50 ml-1">
                                        <div class="form-control h-100 overflow-auto" id="editor-preview-target">

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <p>{{ trans('info::info.editor_syntax_documentation_link') }} <a href="https://github.com/recursivetree/seat-info/blob/master/documentation.md" target="_blank">{{ trans('info::info.link') }}</a></p>
                            </div>

                            <div class="form-group" id="editor-preview-status">
                                <ul id="editor-preview-warnings" class="list-group">
                                </ul>
                            </div>

                            <div class="form-check form-group">
                                @if($article->public)
                                    <input type="checkbox" class="form-check-input" id="public" name="public" checked>
                                @else
                                    <input type="checkbox" class="form-check-input" id="public" name="public">
                                @endif
                                <label class="form-check-label" for="public">{{ trans('info::info.editor_public_checkbox') }}</label>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" id="save">{{ trans('info::info.editor_save_button') }}</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@push('javascript')
    <script src="@infoVersionedAsset('info/js/parser.js')"></script>
    <script src="@infoVersionedAsset('info/js/render_article.js')"></script>
    <script src="@infoVersionedAsset('info/js/markup_tags.js')"></script>
    <script>
        window.addEventListener('load', (event) => {
            const textarea = document.getElementById("text")

            function selectArea(start, end) {
                textarea.selectionStart = start
                textarea.selectionEnd = end
                textarea.focus()
            }

            function selectAreaFromTokenList(tokens) {
                let start = Number.MAX_SAFE_INTEGER
                let end = Number.MIN_SAFE_INTEGER

                for (const token of tokens) {
                    if(token.start < start){
                        start = token.start
                    }
                    if(token.end > end){
                        end = token.end
                    }
                }

                selectArea(start,end+1)
            }

            function render_preview() {
                const content = textarea.value
                //console.log(content)

                let preview_target = document.getElementById("editor-preview-target")
                preview_target.textContent=""// lazy thing to clear the dom

                if (content.length == 0){
                    preview_target.classList.add("text-muted")
                    preview_target.textContent= {!! json_encode(trans('info::info.editor_preview_empty_article')) !!}
                } else {
                    preview_target.classList.remove("text-muted")
                }

                render_article(content, document.getElementById("editor-preview-target"), function (e) {
                    let status_container = document.getElementById("editor-preview-status")
                    if(e.error || e.warnings.length>0){
                        status_container.style.display="block"
                    } else {
                        status_container.style.display="none"
                    }

                    let warnings_list = document.getElementById("editor-preview-warnings")
                    warnings_list.textContent="" // clear all entries

                    if (e.error){
                        console.error(e)

                        let liElement = document.createElement("li")
                        liElement.textContent = `{!! trans('info::info.editor_preview_error') !!} ${e.error.message}`
                        liElement.classList.add("list-group-item-danger")
                        liElement.classList.add("list-group-item")
                        warnings_list.appendChild(liElement)
                    }

                    for(const warning of e.warnings){
                        let liElement = document.createElement("li")
                        liElement.textContent = warning.message

                        liElement.addEventListener("click",function () {
                            selectAreaFromTokenList(warning.tokens)
                        })

                        liElement.classList.add("list-group-item-warning")
                        liElement.classList.add("list-group-item-action")
                        liElement.classList.add("list-group-item")
                        warnings_list.appendChild(liElement)
                    }
                }, function (elementAstRepresentation) {
                    selectAreaFromTokenList(elementAstRepresentation.tokens)
                })
            }

            textarea.addEventListener("input",render_preview)
            render_preview()

            function update_textarea(selectionStart,selectionEnd,replace){
                const startPos = textarea.selectionStart
                const endPos = textarea.selectionEnd
                const scrollPosition = textarea.scrollTop
                const oldText = textarea.value

                const startText = selectionStart ? selectionStart : ""
                const centerText = replace ? replace: oldText.substring(startPos,endPos)
                const endText = selectionEnd ? selectionEnd : ""

                const newText = oldText.substring(0,startPos) + startText + centerText + endText + oldText.substring(endPos)

                textarea.value = newText

                textarea.focus()
                textarea.selectionStart = startPos
                textarea.selectionEnd = startPos + startText.length + centerText.length + endText.length

                textarea.scrollTop = scrollPosition

                render_preview()
            }

            document.getElementById("button-insert-heading").addEventListener("click",function (){
                update_textarea("<h1>","</h1>",null)
            })
            document.getElementById("button-insert-paragraph").addEventListener("click",function (){
                update_textarea("<p>","</p>",null)
            })
            document.getElementById("button-insert-bold").addEventListener("click",function (){
                update_textarea("<b>","</b>",null)
            })
            document.getElementById("button-insert-italic").addEventListener("click",function (){
                update_textarea("<i>","</i>",null)
            })
            document.getElementById("button-insert-link").addEventListener("click",function (){
                update_textarea("<a href=\"seatinfo:article/\">","</a>",null)
            })
            document.getElementById("button-insert-image").addEventListener("click",function (){
                update_textarea(null,null,"<img src=\"seatinfo:resource/\" alt=\"description of the image\">")
            })
            document.getElementById("button-insert-list").addEventListener("click",function (){
                update_textarea(null,null,"<ul>\n    <li>\n    </li>\n    <li>\n    </li>\n    <li>\n    </li>\n</ul>")
            })
            document.getElementById("button-insert-list-ol").addEventListener("click",function (){
                update_textarea(null,null,"<ol>\n    <li>\n    </li>\n    <li>\n    </li>\n    <li>\n    </li>\n</ol>")
            })
            document.getElementById("button-insert-color").addEventListener("click",function (){
                update_textarea("<color color=\"red\">","</color>",null)
            })
        });
    </script>
@endpush
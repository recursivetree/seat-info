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
                    <div class="card">
                        <div class="card-header">
                            <span>{{ session()->get('message')['title'] }}</span>
                        </div>
                        <div class="card-body">
                            <p class="card-text">{{ session()->get('message')['message'] }}</p>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">Editor</div>
                    <div class="card-body">

                        <form method="post" action="{{ route('info.save') }}">
                            @csrf

                            <input type="hidden" name="id" value="{{ $id }}">

                            <div class="form-group">
                                <label for="name">{{ trans('info::info.article_name') }}</label>
                                <input type="text" name="name" class="form-control" id="name"
                                       placeholder="{{ trans('info::info.article_name') }}" required
                                       value="{{ $name }}">
                            </div>

                            <div class="form-group">
                                <label for="text">{{ trans('info::info.article_content') }}</label>
                                <textarea name="text" class="form-control monospace-font text-sm" id="text" placeholder="{{ trans('info::info.article_content_placeholder') }}" rows="15" required>{{ $text }}</textarea>
                            </div>

                            <div class="form-group">
                                <p>For the documentation on the styling syntax, take a look <a href="https://github.com/recursivetree/seat-info/blob/master/documentation.md">here</a>.</p>
                            </div>

                            <div class="form-check form-group">
                                <input type="checkbox" class="form-check-input" id="public" name="public">
                                <label class="form-check-label" for="public">Make this article public</label>
                            </div>

                            <div class="form-group">
                                <input type="submit" class="btn btn-primary" id="save" value="Save"/>
                            </div>

                        </form>

                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Preview</div>
                    <div class="card-body">

                        <div id="editor-preview-status">
                            <h2>Warnings</h2>
                            <ul id="editor-preview-warnings" class="pl-0">

                            </ul>
                        </div>

                        <div class="border rounded p-4" id="editor-preview-target">

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@stop

@push('javascript')
    <script src="{{ asset('info/js/render_article.js') }}"></script>
    <script src="{{ asset('info/js/markup_tags.js') }}"></script>
    <script>
        window.addEventListener('load', (event) => {
            const textarea = document.getElementById("text")

            function render_preview() {
                const content = textarea.value
                //console.log(content)

                let preview_target = document.getElementById("editor-preview-target")
                preview_target.textContent=""// lazy thing to clear the dom

                if (content.length == 0){
                    preview_target.textContent="This is an empty article, nothing to show"
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
                        //document.getElementById("editor-preview-error").textContent = "The renderer couldn't render the article: "+e

                        let liElement = document.createElement("li")
                        liElement.textContent = `Could not render the whole page: Error: ${e.error.message}`
                        liElement.classList.add("list-group-item-danger")
                        liElement.classList.add("list-group-item")
                        warnings_list.appendChild(liElement)
                    }

                    for(const warning of e.warnings){
                        let liElement = document.createElement("li")
                        liElement.textContent = warning
                        liElement.classList.add("list-group-item-warning")
                        liElement.classList.add("list-group-item")
                        warnings_list.appendChild(liElement)
                    }
                });
            }

            textarea.addEventListener("input",render_preview)
            render_preview()
        });
    </script>
@endpush
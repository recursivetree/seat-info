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

                            <div class="form-group">
                                <label for="text">{{ trans('info::info.article_content') }}</label>
                                <textarea name="text" class="form-control monospace-font text-sm" id="text" placeholder="{{ trans('info::info.article_content_placeholder') }}" rows="15" required>{{ $article->text }}</textarea>
                            </div>

                            <div class="form-group">
                                <p>{{ trans('info::info.editor_syntax_documentation_link') }} <a href="https://github.com/recursivetree/seat-info/blob/master/documentation.md" target="_blank">{{ trans('info::info.link') }}</a></p>
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

                <div class="card">
                    <div class="card-header">{{ trans('info::info.editor_preview_title') }}</div>
                    <div class="card-body">

                        <div id="editor-preview-status">
                            <h2>{{ trans('info::info.editor_preview_warnings_title') }}</h2>
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
    <script src="@versionedAsset('info/js/render_article.js')"></script>
    <script src="@versionedAsset('info/js/markup_tags.js')"></script>
    <script>
        window.addEventListener('load', (event) => {
            const textarea = document.getElementById("text")

            function render_preview() {
                const content = textarea.value
                //console.log(content)

                let preview_target = document.getElementById("editor-preview-target")
                preview_target.textContent=""// lazy thing to clear the dom

                if (content.length == 0){
                    preview_target.textContent= {!! json_encode(trans('info::info.editor_preview_empty_article')) !!}
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
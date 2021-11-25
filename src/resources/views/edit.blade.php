@extends('web::layouts.grids.12')

@section('title', trans('info::info.module_title'))
@section('page_header', trans('info::info.module_title'))

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
                                <label for="name">{{ trans('info::info.article_content') }}</label>
                                <textarea name="text" class="form-control" id="text" placeholder="{{ trans('info::info.article_content_placeholder') }}" rows="15" required>{{ $text }}</textarea>
                            </div>

                            <div class="form-group">
                                <p>For the documentation on the styling syntax, take a look <a href="https://github.com/recursivetree/seat-info/blob/master/documentation.md">here</a>.</p>
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
                        <p id="editor-preview-target">

                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

@stop

@push('javascript')
    <script src="{{ asset('info/js/render_article.js') }}"></script>
    <script>
        window.addEventListener('load', (event) => {
            setInterval(function (){

                const content = document.getElementById("text").value
                //console.log(content)

                let preview_target = document.getElementById("editor-preview-target")
                preview_target.textContent=""// lazy thing to clear the dom

                render_article(content, document.getElementById("editor-preview-target"), function (e) {
                    //console.log(e)
                    let preview_target = document.getElementById("editor-preview-target")
                    preview_target.textContent = "There are syntax errors in your article! "+e
                });

            }, 1000)
        });
    </script>
@endpush
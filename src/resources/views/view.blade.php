@extends('web::layouts.grids.12')

@section('title', trans('info::info.module_title'))
@section('page_header', trans('info::info.module_title'))

@section('full')

    <!-- Instructions -->
    <div class="row w-100">
        <div class="col">
            <div class="card-column">

                @isset($article)
                    <div class="card">
                        <div class="card-header">
                            <b>{{$article->name}}</b>
                            <div class="btn-group float-right" role="group">
                                @can("info.article.edit", $article->id)
                                    <a href="{{ route("info.edit_article", $article->id) }}" class="float-right btn btn-secondary">{{ trans("info::info.view_article_edit") }}</a>
                                @endcan
                                <a href="{{ route("info.manage") }}" class="float-right btn btn-secondary">{{ trans("info::info.view_article_manage") }}</a>
                                <a class="btn btn-primary" href="{{ url()->previous() }}">{{ trans("info::info.view_back_button") }}</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger" style="display: none;" id="rendering-error">
                                <span>{{ trans("info::info.view_render_errors_message") }}</span>
                            </div>
                            <p class="card-text" id="info-content-target">Loading...</p>
                        </div>
                    </div>
                @endisset

                @can("info.edit_permalinks")
                    <div class="card">
                        <div class="card-header">
                            {{ trans("info::info.permalinks_title") }}
                        </div>
                        <div class="card-body">
                            <form action="{{ route("info.create_permalink") }}" method="POST">
                                @csrf
                                <input type="hidden" name="article" value="{{ $article->id }}">
                                <div class="input-group mb-3">
                                    <input type="text" name="name" placeholder="{{ trans("info::info.add_permalink_name_placeholder") }}" class="form-control">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">{{trans("info::info.add_button")}}</button>
                                    </div>
                                </div>
                            </form>

                            <ul class="list-group">
                                @foreach($article->permaLinks as $permaLink)
                                    <li class="list-group-item d-flex flex-row align-items-baseline">
                                        <code>
                                            {{ route("info.permalink", $permaLink->permalink) }}
                                        </code>

                                        <form action="{{ route("info.delete_permalink") }}" method="POST" class="ml-auto">
                                            @csrf
                                            <input type="hidden" name="name" value="{{ $permaLink->permalink }}">
                                            <button class="btn btn-danger confirmdelete" data-seat-entity="{{trans("info::info.permalink_delete_modal")}}">{{ trans("info::info.delete_button") }}</button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>

@stop

@isset($article)
    @push('javascript')

        <script src="@infoVersionedAsset('info/js/parser.js')"></script>
        <script src="@infoVersionedAsset('info/js/render_article.js')"></script>
        <script src="@infoVersionedAsset('info/js/markup_tags.js')"></script>

        <script>
            window.addEventListener('load', (event) => {
                const target = document.getElementById("info-content-target")
                target.textContent = null
                render_article({!! json_encode( $article->text) !!}, target, function (e) {
                    if (e.error || e.warnings.length > 0) {
                        console.log(e)
                        document.getElementById("rendering-error").style.display = "block"
                    }
                });
            });
        </script>
    @endpush
@endisset
@extends('web::layouts.grids.12')

@section('title', trans('info::info.module_title'))
@section('page_header', trans('info::info.module_title'))

@section('full')

    <!-- Instructions -->
    <div class="row w-100">
        <div class="col">
            <div class="card-column">

                <div class="card">
                    <div class="card-header">
                        Articles
                        <div class="btn-group float-right" role="group">
                            @can("info.create_article")
                                <a href="{{ route("info.create") }}" class="float-right btn btn-secondary">{{ trans("info::info.list_article_new") }}</a>
                                <a href="{{ route("info.manage") }}" class="float-right btn btn-secondary">{{ trans("info::info.list_article_manage") }}</a>
                            @endcan
                            <a class="btn btn-primary" href="{{ url()->previous() }}">{{ trans("info::info.view_back_button") }}</a>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="list-group">
                            @foreach ($articles as $article)

                                <div class="list-group-item list-group-item-action d-flex flex-row align-items-baseline">
                                    <a href="{{ route("info.view", $article->id) }}" class="mr-auto">{{ $article->name }}</a>

                                    <div class="mx-3">
                                        @if($article->pinned)
                                            <span class="badge badge-primary">
                                                <i class="fas fa-map-pin"></i>
                                                {{ trans('info::info.list_pinned_article') }}
                                            </span>
                                        @endif

                                        @if(!$article->public)
                                            <span class="badge badge-secondary">{{ trans('info::info.list_private_article') }}</span>
                                        @endif
                                    </div>
                                </div>

                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop
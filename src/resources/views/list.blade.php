@extends('web::layouts.grids.12')

@section('title', trans('info::info.module_title'))
@section('page_header', trans('info::info.module_title'))

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
                    <div class="card-header">
                        Articles
                        <div class="btn-group float-right" role="group">
                            @if($can_edit)
                                <a href="{{ route("info.create") }}" class="float-right btn btn-secondary">{{ trans("info::info.list_article_new") }}</a>
                                <a href="{{ route("info.manage") }}" class="float-right btn btn-secondary">{{ trans("info::info.list_article_manage") }}</a>
                            @endif
                            <a class="btn btn-primary" href="{{ url()->previous() }}">{{ trans("info::info.view_back_button") }}</a>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="list-group">
                            @foreach ($articles as $article)
                                @if($article->public || $can_edit)
                                    <div class="list-group-item list-group-item-action">
                                        <a href="{{ route("info.view", $article->id) }}">{{ $article->name }}</a>
                                        @if(!$article->public)
                                            <span class="badge badge-secondary">{{ trans('info::info.list_private_article') }}</span>
                                        @endif
                                        @if($article->home_entry)
                                            <span class="badge badge-primary">{{ trans('info::info.list_home_article') }}</span>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop
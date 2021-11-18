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
                    <div class="card-header">Articles</div>
                    <div class="card-body">

                        <div class="list-group">
                            @foreach ($articles as $article)
                                <a class="list-group-item list-group-item-action"
                                   href="{{ route("info.view", $article->id) }}">{{ $article->name }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@stop
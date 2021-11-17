@extends('web::layouts.grids.12')

@section('title', trans('info::info.module_title'))
@section('page_header', trans('info::info.module_title'))

@section('full')

    <!-- Instructions -->
    <div class="row w-100">
        <div class="col">
            <div class="card-column">

                @isset($article_saved)
                    <div class="card">
                        <div class="card-header">
                            <span>Success</span>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                The article '{{ $article_saved['name'] }}' has been saved.
                            </p>
                        </div>
                    </div>
                @endisset

                @isset($article_deleted)
                    <div class="card">
                        <div class="card-header">
                            <span>Success</span>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                The article '{{ $article_deleted['name'] }}' has been deleted.
                            </p>
                        </div>
                    </div>
                @endisset

                @isset($error_message)
                    <div class="card">
                        <div class="card-header">
                            <span>Error</span>
                        </div>
                        <div class="card-body">
                            <p class="card-text">{{ $error_message}}</p>
                        </div>
                    </div>
                @endisset

                <div class="card">
                    <div class="card-header">
                        <span>Manage</span>
                        <a href="{{ route('info.create') }}">
                            <button class="btn btn-primary float-right">New</button>
                        </a>
                    </div>
                    <div class="card-body">

                        <table id="pages" class="table table table-striped">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Text</th>
                                <th><span class="float-right">Actions</span></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($articles as $article)
                                <tr>
                                    <td><a href="{{ route("info.view", $article->id) }}">{{ $article->name }}</a></td>
                                    <td>{{ substr(preg_replace( "/\r|\n/", "", $article->text), 0, 60) }}</td>
                                    <td>
                                        <div class="float-right row">
                                            <form method="post" action="{{ route("info.edit_article") }}">
                                                @csrf
                                                <button class="btn btn-secondary">Edit</button>
                                                <input type="hidden" value="{{ $article->id }}" name="id">
                                            </form>
                                            <form method="post" action="{{ route("info.delete_article") }}">
                                                @csrf
                                                <button class="btn btn-danger">Delete</button>
                                                <input type="hidden" value="{{ $article->id }}" name="id">
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>

            </div>
        </div>
    </div>

@stop
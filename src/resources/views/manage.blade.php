@extends('web::layouts.grids.12')

@section('title', trans('info::info.module_title'))
@section('page_header', trans('info::info.module_title'))

@section('full')

<!-- Instructions -->
<div class="row w-100">
  <div class="col">
    <div class="card-deck">

      @isset($article_saved)
        <div class="card">
          <div class="card-header" >
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
            <div class="card-header" >
              <span>Success</span>
            </div>
            <div class="card-body">
              <p class="card-text">
                The article '{{ $article_deleted['name'] }}' has been deleted.
              </p>
            </div>
          </div>
        @endisset

      <div class="card">
        <div class="card-header" >
          <span>Manage</span>
          <a href="{{ route('info.create') }}"><button class="btn">New</button></a>
        </div>
        <div class="card-body">

          <table id="pages" class="table table table-bordered table-striped">
            <thead>
            <tr>
              <th>Name</th>
              <th>Text</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($articles as $article)
              <tr>
                <td>{{ $article->name }}</td>
                <td>{{ substr(preg_replace( "/\r|\n/", "", $article->text), 0, 60) }}</td>
                <td>
                  <form method="post" action="{{ route("info.delete_article") }}">
                    @csrf
                    <button class="btn">Delete</button>
                    <input type="hidden" value="{{ $article->id }}" name="id">
                  </form>
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
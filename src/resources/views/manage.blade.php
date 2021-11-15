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
              The article '{{ $article_saved['name'] }}' has been saved
            </p>
          </div>
        </div>
      @endisset

      <div class="card">
        <div class="card-header" >
          <span>Manage</span>
          <a href="{{ route('info.create') }}"><button class="btn"></button></a>
        </div>
        <div class="card-body">

          <p class="card-text">
            manage
          </p>

        </div>
      </div>

    </div>
  </div>
</div>

@stop
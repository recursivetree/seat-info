@extends('web::layouts.grids.12')

@section('title', trans('info::info.module_title'))
@section('page_header', trans('info::info.module_title'))

@section('full')

<!-- Instructions -->
<div class="row w-100">
  <div class="col">
    <div class="card-deck">

      <div class="card">
        <div class="card-header" >Articles</div>
        <div class="card-body">

          <table id="pages" class="table table table-bordered table-striped">
            <thead>
            <tr>
              <th>Name</th>
              <th>Text</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($articles as $article)
              <tr>
                <td>{{ $article->name }}</td>
                <td>{{ substr(preg_replace( "/\r|\n/", "", $article->text), 0, 60) }}</td>
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
@extends('web::layouts.grids.12')

@section('title', trans('info::info.module_title'))
@section('page_header', trans('info::info.module_title'))

@section('full')

    <!-- Instructions -->
    <div class="row w-100">
        <div class="col">
            <div class="card-column">

                <div class="card">
                    <div class="card-header">Editor</div>
                    <div class="card-body">

                        <form method="post" action="{{ route('info.save') }}">
                            @csrf

                            <input type="hidden" name="id" value="{{ $id }}">

                            <div class="form-group col">
                                <label for="name">{{ trans('info::info.article_name') }}</label>
                                <input type="text" name="name" class="form-control" id="name" placeholder="{{ trans('info::info.article_name') }}" required value="{{ $name }}">
                                <div class="valid-feedback">Looks Good!</div>
                                <div class="invalid-feedback">You need to specify a name</div>
                            </div>

                            <div class="form-group col">
                                <label for="name">{{ trans('info::info.article_content') }}</label>
                                <textarea name="text" class="form-control" id="text"
                                          placeholder="{{ trans('info::info.article_content_placeholder') }}" rows="15"
                                          required>{{ $text }}</textarea>
                            </div>

                            <div class="form-group col">
                                <input type="submit" class="btn btn-primary" id="save" value="Save"/>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

@stop
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

                @isset($title, $content)
                    <div class="card">
                        <div class="card-header"><b>{{$title}}</b><span><a class="btn btn-secondary float-right" href="{{ route("info.list") }}">Back</a></span></div>
                        <div class="card-body">

                            <p class="card-text">
                                {{ $content }}
                            </p>

                        </div>
                    </div>
                @endisset

            </div>
        </div>
    </div>

@stop
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
                        About
                    </div>
                    <div class="card-body">
                        <p>
                            {{ trans("info::info.about_ace_text_editor") }}
                            <a href="https://ace.c9.io/">{{ trans("info::info.about_website_link") }}</a>
                            <a href="https://github.com/ajaxorg/ace">{{ trans("info::info.about_github_link") }}</a>
                        </p>
                        <p>{{ trans("info::info.about_donation_info") }}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop
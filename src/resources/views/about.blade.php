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
                        About
                    </div>
                    <div class="card-body">
                        <p>
                            {{ trans("info::info.about_ace_text_editor") }}
                            <a href="https://ace.c9.io/">{{ trans("info::info.about_website_link") }}</a>
                            <a href="https://github.com/ajaxorg/ace">{{ trans("info::info.about_github_link") }}</a>
                        </p>
                        <p>{{ trans("info::info.about_donation_info") }}</p>

                        <img src="{{ asset('info/img/PartnerImage.jpg') }}" style="max-height: 30vh;margin-left: auto;margin-right: auto;display:block;">
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop
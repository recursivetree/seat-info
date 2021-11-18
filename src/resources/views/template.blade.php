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
                    <div class="card-header">Step 1</div>
                    <div class="card-body">

                        <p class="card-text">
                            Click on the <span class="fa fa-plus-square"></span> on the top right of the text table in
                            order to create a new text. This will open a modal where you can enter the required data.
                        </p>

                    </div>
                </div>

            </div>
        </div>
    </div>

@stop
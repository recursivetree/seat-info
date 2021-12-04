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

                <div class="card" style="display: none;" id="rendering-error">
                    <div class="card-header">
                        <b>Error</b>
                    </div>
                    <div class="card-body">
                        <p>The article contains errors and might not be rendered correct! Consider contact your administrator about this.</p>
                    </div>
                </div>

                @isset($title, $content)
                    <div class="card">
                        <div class="card-header"><b>{{$title}}</b><span><a class="btn btn-secondary float-right" href="{{ route("info.list") }}">Back</a></span>
                        </div>
                        <div class="card-body">
                            <p class="card-text" id="info-content-target"></p>
                        </div>
                    </div>
                @endisset

            </div>
        </div>
    </div>

@stop

@isset($title, $content)
    @push('javascript')
        <script src="{{ asset('info/js/render_article.js') }}"></script>
        <script src="{{ asset('info/js/markup_tags.js') }}"></script>
        <script>
            window.addEventListener('load', (event) => {
                render_article({!! json_encode( $content) !!}, document.getElementById("info-content-target"), function (e) {
                    if(e.error) {
                        console.log(e)
                        document.getElementById("rendering-error").style.display = "block"
                    }
                });
            });
        </script>
    @endpush
@endisset
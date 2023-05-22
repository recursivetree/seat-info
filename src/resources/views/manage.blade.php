@extends('web::layouts.grids.12')

@section('title', trans('info::info.module_title'))
@section('page_header', trans('info::info.module_title'))

@section('full')
    <!-- Main -->
    <div class="row w-100">
        <div class="col">
            <div class="card-column">

                <div class="card">
                    <div class="card-header">
                        {{ trans("info::info.manage_article_title") }}

                        @can("info.create_article")
                            <a href="{{ route("info.create") }}"
                               class="float-right btn btn-primary">{{ trans("info::info.manage_article_new") }}</a>
                        @endcan
                    </div>
                    <div class="card-body">

                        @if($articles->count()>=10)
                            <div class="alert alert-info">
                                {{ trans("info::info.manage_donation_info") }}
                            </div>
                        @endif

                        <table id="pages" class="table table table-striped">
                            <thead>
                            <tr>
                                <th>{{ trans("info::info.manage_article_table_name") }}</th>
                                <th>{{ trans("info::info.manage_article_table_idlink") }}</th>
                                <th>{{ trans("info::info.manage_article_table_labels") }}</th>
                                <th>{{ trans("info::info.manage_article_table_owner") }}</th>
                                <th>
                                    <span class="float-right">{{ trans("info::info.manage_article_table_actions") }}</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($articles as $article)
                                <tr>
                                    <td>
                                        <a href="{{ route("info.view", $article->id) }}">{{ $article->name }}</a>
                                    </td>
                                    <td>
                                        <code>
                                            seatinfo:article/{{ $article->id }}/{{ $article->name }}
                                        </code>
                                    </td>
                                    <td>
                                        @if($article->public)
                                            <span class="badge badge-success">{{ trans("info::info.manage_article_public") }}</span>
                                        @else
                                            <span class="badge badge-warning">{{ trans("info::info.manage_article_private") }}</span>
                                        @endif

                                        @if($article->pinned)
                                            <span class="badge badge-primary">
                                                <i class="fas fa-map-pin"></i>
                                                {{ trans('info::info.list_pinned_article') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $article->owner_user->name ?? trans('info::info.manage_unknown_user') }}
                                    </td>
                                    <td>
                                        <div class="float-right d-flex flex-row">

                                            <a href="{{ route("info.edit_article", $article->id) }}" class="btn btn-primary ml-1" style="min-width: 6rem">
                                                <i class="fas fa-pen"></i>
                                                {{ trans("info::info.manage_article_edit") }}
                                            </a>

                                            @can("info.make_public")
                                                @if(!$article->public)
                                                    <form action="{{ route("info.set_article_public") }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="data" value="{{ $article->id }}">
                                                        <button type="submit" class="btn btn-warning ml-1 confirmform" data-seat-action="{{ trans("info::info.manage_article_set_public_confirmation") }}" style="min-width: 6rem">
                                                            <i class="fas fa-eye"></i>
                                                            {{ trans("info::info.manage_article_set_public") }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route("info.set_article_private") }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="data" value="{{ $article->id }}">
                                                        <button type="submit" class="btn btn-warning ml-1 confirmform" data-seat-action="{{ trans("info::info.manage_article_set_private_confirmation") }}" style="min-width: 6rem">
                                                            <i class="fas fa-eye-slash"></i>
                                                            {{ trans("info::info.manage_article_set_private") }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan

                                            @can("info.pin_article")
                                                @if($article->pinned)
                                                    <form action="{{ route("info.set_article_unpinned") }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="data" value="{{ $article->id }}">
                                                        <button class="btn btn-warning ml-1 confirmform" style="min-width: 6rem" data-seat-action="{{ trans("info::info.manage_article_unpin_article_confirmation") }}">
                                                            <i class="fas fa-map-pin"></i>
                                                            {{ trans('info::info.unpin_article') }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route("info.set_article_pinned") }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="data" value="{{ $article->id }}">
                                                        <button class="btn btn-warning ml-1 confirmform" style="min-width: 6rem" data-seat-action="{{ trans("info::info.manage_article_pin_article_confirmation") }}">
                                                            <i class="fas fa-map-pin"></i>
                                                            {{ trans('info::info.pin_article') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan

                                            <form action="{{ route("info.delete_article") }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="data" value="{{ $article->id }}">
                                                <button type="submit" class="btn btn-danger ml-1 confirmdelete" data-seat-entity="{{ trans("info::info.article") }}" style="min-width: 6rem">
                                                    {{ trans("info::info.manage_article_delete") }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        @if($articles->isEmpty())
                            <p class="text-center">{{ trans("info::info.manage_article_no_articles") }}</p>
                        @endif

                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span>{{ trans("info::info.manage_resources_title") }}</span>
                    </div>
                    <div class="card-body">

                        @can("info.upload_resource")
                            <div class="border rounded p-4">
                                <form action="{{ route("info.upload_resource") }}" method="POST"
                                      enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <label for="resourceFileUpload"> {{ trans("info::info.manage_resources_upload_label",['max'=>ini_get("upload_max_filesize")]) }}</label>
                                        <div class="custom-file">
                                            <input type="file" name="file" class="custom-file-input"
                                                   id="resourceFileUpload">
                                            <label class="custom-file-label"
                                                   for="resourceFileUpload">{{ trans("info::info.manage_resources_upload_choose") }}</label>
                                        </div>
                                    </div>
                                    <div class="form-check form-group">
                                        <input type="checkbox" class="form-check-input" id="mime-src"
                                               name="mime_src_client">
                                        <label class="form-check-label"
                                               for="mime-src">{{ trans("info::info.manage_resources_mime_client_label") }}</label>
                                    </div>
                                    <div class="form-group mb-0">
                                        <button class="btn btn-primary"
                                                type="submit">{{ trans("info::info.manage_resources_upload") }}</button>
                                    </div>
                                </form>
                            </div>
                        @endcan

                        <div class="alert alert-warning my-2">
                            If your resource files aren't showing up correctly after upgrading to seat 5, ask an administrator to follow the <a href="https://github.com/recursivetree/seat-info/tree/5.0.x#upgrading">seat-info upgrade guide</a>.
                        </div>

                        <table id="pages" class="table table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ trans("info::info.manage_resources_table_name") }}</th>
                                    <th>{{ trans("info::info.manage_resources_table_idlink") }}</th>
                                    <th>{{ trans("info::info.manage_resources_table_owner") }}</th>
                                    <th>{{ trans("info::info.manage_resources_table_type") }}</th>
                                    <th>
                                        <span class="float-right">{{ trans("info::info.manage_resources_table_actions") }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($resources as $resource)
                                <tr>
                                    <td>
                                        <a href="{{ route("info.view_resource",$resource->id) }}">{{ $resource->name }}</a>
                                    </td>
                                    <td>
                                        <code>
                                            seatinfo:resource/{{$resource->id}}/{{$resource->name}}
                                        </code>
                                    </td>
                                    <td>
                                        {{ $resource->owner_user->name ?? trans('info::info.manage_unknown_user') }}
                                    </td>
                                    <td>
                                        {{ $resource->mime }}
                                    </td>
                                    <td>
                                        <div class="d-flex flex-row justify-content-end">
                                            @can("info.resource.edit",$resource->id)
                                                <a class="btn btn-secondary" href="{{ route("info.configure_resource",$resource->id) }}">{{ trans("info::info.manage_resources_table_configure") }}</a>
                                                <form action="{{ route("info.delete_resource") }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="data" value="{{ $resource->id }}">
                                                    <button type="submit" class="btn btn-danger ml-1 confirmdelete" data-seat-entity="{{ trans("info::info.manage_resources_table_delete_confirm") }}">{{ trans("info::info.manage_resources_table_delete") }}</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        @if($resources->isEmpty())
                            <p class="text-center">{{ trans("info::info.manage_resources_no_resources") }}</p>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

@stop

@push('javascript')
    <script>
        // Add the following code if you want the name of the file appear on select
        $(".custom-file-input").on("change", function () {
            let fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    </script>
@endpush
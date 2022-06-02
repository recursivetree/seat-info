@extends('web::layouts.grids.12')

@section('title', trans('info::info.module_title'))
@section('page_header', trans('info::info.module_title'))

@section('full')
    <!-- Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">{{ trans("info::info.manage_confirm_title") }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="confirmModalWarningText"></div>
                <div class="modal-footer">
                    <form method="POST" action="" id="confirmModalForm">
                        @csrf
                        <button type="submit" class="btn btn-danger"
                                id="confirmModalConfirm">{{ trans("info::info.manage_confirm_confirm") }}</button>
                        <input type="hidden" id="confirmModalData" name="data">
                    </form>
                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ trans("info::info.manage_confirm_cancel") }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main -->
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
                        {{ trans("info::info.manage_article_title") }}

                        @can("info.create_article")
                            <a href="{{ route("info.create") }}"
                               class="float-right btn btn-primary">{{ trans("info::info.manage_article_new") }}</a>
                        @endcan
                    </div>
                    <div class="card-body">

                        @can("info.configure_home_article")
                            @if($noHomeArticle)
                                <div class="alert alert-warning" role="alert">
                                    {{ trans("info::info.manage_article_no_home_article") }}
                                </div>
                            @endif
                        @endcan

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
                                <th>
                                    <span class="float-right">{{ trans("info::info.manage_article_table_actions") }}</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($articles as $article)
                                <tr>
                                    <td>
                                        @can("info.article.view",$article->id)
                                            <a href="{{ route("info.view", $article->id) }}">{{ $article->name }}</a>
                                        @else
                                            {{ $article->name }}
                                        @endcan
                                    </td>
                                    <td>
                                        {{ "seatinfo:article/{$article->id}" }}
                                    </td>
                                    <td>
                                        @if($article->public)
                                            <span class="badge badge-success">{{ trans("info::info.manage_article_public") }}</span>
                                        @else
                                            <span class="badge badge-warning">{{ trans("info::info.manage_article_private") }}</span>
                                        @endif
                                        @if($article->home_entry)
                                            <span class="badge badge-info">{{ trans("info::info.manage_article_home_article") }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group float-right" role="group">

                                            @can("info.article.edit",$article->id)
                                                <a href="{{ route("info.edit_article", $article->id) }}"
                                                   class="btn btn-primary mr-auto">{{ trans("info::info.manage_article_edit") }}</a>
                                            @endcan

                                            <button id="btnGroupDropArticles{{ $article->id }}" type="button"
                                                    class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false">
                                                {{ trans("info::info.manage_article_options") }}
                                            </button>
                                            <div class="dropdown-menu p-0"
                                                 aria-labelledby="btnGroupDropArticles{{ $article->id }}">
                                                <div class="btn-group-vertical dropdown-item p-0">

                                                    @can("info.configure_home_article")
                                                        @if($article->home_entry)
                                                            <button
                                                                    class="btn btn-warning confirm-action"
                                                                    data-confirm-warning="{{ trans("info::info.manage_article_unset_home_article_confirmation") }}"
                                                                    data-url="{{ route("info.unset_home_article") }}"
                                                            >{{ trans("info::info.manage_article_unset_home_article") }}</button>
                                                        @else
                                                            <button
                                                                    class="btn btn-warning confirm-action"
                                                                    data-confirm-warning="{{ trans("info::info.manage_article_set_home_article_confirmation") }}"
                                                                    data-url="{{ route("info.set_home_article") }}"
                                                                    data-data="{{ $article->id }}"
                                                            >{{ trans("info::info.manage_article_set_home_article") }}</button>
                                                        @endif
                                                    @endcan

                                                    @can("info.article.edit",$article->id)
                                                        @if(!$article->public)
                                                            <button
                                                                    class="btn btn-warning confirm-action"
                                                                    data-confirm-warning="{{ trans("info::info.manage_article_set_public_confirmation") }}"
                                                                    data-url="{{ route("info.set_article_public") }}"
                                                                    data-data="{{ $article->id }}"
                                                            >{{ trans("info::info.manage_article_set_public") }}</button>
                                                        @else
                                                            <button
                                                                    class="btn btn-warning confirm-action"
                                                                    data-confirm-warning="{{ trans("info::info.manage_article_set_private_confirmation") }}"
                                                                    data-url="{{ route("info.set_article_private") }}"
                                                                    data-data="{{ $article->id }}"
                                                            >{{ trans("info::info.manage_article_set_private") }}</button>
                                                        @endif
                                                    @endcan

                                                    @can("info.article.edit",$article->id)
                                                        <button
                                                                class="btn btn-danger confirm-action"
                                                                data-confirm-warning="{{ trans("info::info.manage_article_delete_confirmation") }}"
                                                                data-url="{{ route("info.delete_article") }}"
                                                                data-data="{{ $article->id }}"
                                                        >{{ trans("info::info.manage_article_delete") }}</button>
                                                    @endcan
                                                </div>
                                            </div>
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

                        @can("info.edit_resource")
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

                        <table id="pages" class="table table table-striped">
                            <thead>
                            <tr>
                                <th>{{ trans("info::info.manage_resources_table_name") }}</th>
                                <th>{{ trans("info::info.manage_resources_table_idlink") }}</th>
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
                                        {{ "seatinfo:resource/{$resource->id}"}}
                                    </td>
                                    <td>
                                        {{ $resource->mime }}
                                    </td>
                                    <td>
                                        @can("info.delete_resource")
                                        <div class="float-right row">
                                            <button id="btnGroupDropResources{{ $resource->id }}" type="button"
                                                    class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false"
                                            >{{ trans("info::info.manage_resources_table_options") }}</button>
                                            <div class="dropdown-menu p-0"
                                                 aria-labelledby="btnGroupDropArticles{{ $resource->id }}">
                                                <div class="btn-group-vertical dropdown-item p-0">
                                                    <button
                                                            class="btn btn-danger confirm-action"
                                                            data-confirm-warning="{{ trans("info::info.manage_resources_table_delete_confirm") }}"
                                                            data-url="{{ route("info.delete_resource") }}"
                                                            data-data="{{ $resource->id }}"
                                                    >{{ trans("info::info.manage_resources_table_delete") }}</button>
                                                </div>
                                            </div>
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


        // Bind click to OK button within popup
        $('.confirm-action').click(function (e) {
            const toggleBtn = $(e.target)
            const warningText = toggleBtn.data("confirm-warning")

            $("#confirmModalWarningText").text(warningText)

            console.log(toggleBtn.data("url"))

            $("#confirmModalForm").attr("action", toggleBtn.data("url"))
            $("#confirmModalData").attr("value", toggleBtn.data("data"))

            $('#confirmModal').modal()
        });
    </script>
@endpush
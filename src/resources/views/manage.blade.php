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
                    <h5 class="modal-title" id="confirmModalLabel">Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="confirmModalWarningText">
                    Do you really want to perform this action?
                </div>
                <div class="modal-footer">
                    <form method="POST" action="" id="confirmModalForm">
                        @csrf
                        <button type="submit" class="btn btn-danger" id="confirmModalConfirm">Confirm</button>
                        <input type="hidden" id="confirmModalData" name="data">
                    </form>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main -->
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
                    <div class="card-header">
                        <b>Manage Articles</b>
                        <a href="{{ route("info.create") }}" class="float-right btn btn-primary">New</a>
                    </div>
                    <div class="card-body">

                        @if($noHomeArticle)
                            <div class="alert alert-warning" role="alert">
                                You don't have any home article! Set one by selecting an article and Options->Set Home Article. The home article appears in the start section of the info module.
                            </div>
                        @endif
                        <table id="pages" class="table table table-striped">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>ID-Link</th>
                                <th>Labels</th>
                                <th><span class="float-right">Actions</span></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($articles as $article)
                                <tr>
                                    <td>
                                        <a href="{{ route("info.view", $article->id) }}">{{ $article->name }}</a>
                                    </td>
                                    <td>
                                        {{ "seatinfo:article/{$article->id}" }}
                                    </td>
                                    <td>
                                        @if($article->public)
                                            <span class="badge badge-success">Public</span>
                                        @else
                                            <span class="badge badge-warning">Private</span>
                                        @endif
                                        @if($article->home_entry)
                                            <span class="badge badge-info">Home Article</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group float-right" role="group">
                                            <a href="{{ route("info.edit_article", $article->id) }}"
                                               class="btn btn-primary mr-auto">Edit</a>
                                            <button id="btnGroupDropArticles{{ $article->id }}" type="button"
                                                    class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false">Options
                                            </button>
                                            <div class="dropdown-menu p-0"
                                                 aria-labelledby="btnGroupDropArticles{{ $article->id }}">
                                                <div class="btn-group-vertical dropdown-item p-0">
                                                    @if($article->home_entry)
                                                        <button
                                                                class="btn btn-warning confirm-action"
                                                                data-confirm-warning="Do you really want to unset your home article?"
                                                                data-url="{{ route("info.unset_home_article") }}"
                                                        >Unset Home Article
                                                        </button>
                                                    @else
                                                        <button
                                                                class="btn btn-warning confirm-action"
                                                                data-confirm-warning="Do you really want to change your home article?"
                                                                data-url="{{ route("info.set_home_article") }}"
                                                                data-data="{{ $article->id }}"
                                                        >Set Home Article
                                                        </button>
                                                    @endif

                                                    @if(!$article->public)
                                                        <button
                                                                class="btn btn-warning confirm-action"
                                                                data-confirm-warning="Do you really want to make this article public?"
                                                                data-url="{{ route("info.set_article_public") }}"
                                                                data-data="{{ $article->id }}"
                                                        >Make Public
                                                        </button>
                                                    @else
                                                        <button
                                                                class="btn btn-warning confirm-action"
                                                                data-confirm-warning="Do you really want to make this article private?"
                                                                data-url="{{ route("info.set_article_private") }}"
                                                                data-data="{{ $article->id }}"
                                                        >Make Private
                                                        </button>
                                                    @endif
                                                    <button
                                                            class="btn btn-danger confirm-action"
                                                            data-confirm-warning="Do you really want to delete this article?"
                                                            data-url="{{ route("info.delete_article") }}"
                                                            data-data="{{ $article->id }}"
                                                    >Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        @if($articles->isEmpty())
                            <p class="text-center">There are no articles</p>
                        @endif

                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span>Resources</span>
                    </div>
                    <div class="card-body">
                        <div>
                            <form action="{{ route("info.upload_resource") }}" method="POST"
                                  enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="resourceFileUpload">File Upload</label>
                                    <div class="custom-file">
                                        <input type="file" name="file" class="custom-file-input"
                                               id="resourceFileUpload">
                                        <label class="custom-file-label" for="resourceFileUpload">Choose file</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-primary" type="submit">Upload</button>
                                </div>
                            </form>
                        </div>

                        <table id="pages" class="table table table-striped">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>ID-Link</th>
                                <th>Type</th>
                                <th><span class="float-right">Actions</span></th>
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
                                        <div class="float-right row">
                                            <form method="post" action="{{ route("info.delete_resource") }}">
                                                @csrf
                                                <button class="btn btn-danger" style="margin:0.1rem">Delete</button>
                                                <input type="hidden" value="{{ $resource->id }}" name="id">
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        @if($resources->isEmpty())
                            <p class="text-center">There are no resources</p>
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
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
                        {{ $resource->name }}
                    </div>
                    <div class="card-body">

                        <form action="{{route("info.configure_resource_save", $resource->id)}}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="resource-name-input">{{ trans("info::info.configure_resource_resource_name") }}</label>
                                <input class="form-control" id="resource-name-input" type="text" name="name" value="{{ $resource->name }}" placeholder="{{ trans("info::info.configure_resource_resource_name_placeholder") }}">
                            </div>

                            <div class="form-group">
                                <label for="name">{{ trans('info::info.access_management_label') }}</label>
                                <ul class="list-group" id="aclConfigurationList">
                                    @foreach($roles as $role)
                                        <li class="list-group-item d-flex flex-row">
                                                <span class="mr-auto">
                                                    {{ $role->roleModel->title }}
                                                </span>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="aclAccessType[{{ $role->role }}]" value="nothing"
                                                       @if(!$role->allows_edit && !$role->allows_view)
                                                       checked
                                                        @endif>
                                                <label class="form-check-label">Nothing</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="aclAccessType[{{ $role->role }}]" value="view"
                                                       @if($role->allows_view)
                                                       checked
                                                        @endif>
                                                <label class="form-check-label">Allow Viewing</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="aclAccessType[{{ $role->role }}]" value="edit"
                                                       @if($role->allows_edit)
                                                       checked
                                                        @endif>
                                                <label class="form-check-label">Allow Editing</label>
                                            </div>
                                        </li>
                                    @endforeach
                                    @if($roles->isEmpty())
                                        <li class="list-group-item d-flex flex-row">
                                            {{ trans("info::info.configure_resource_acl_help") }}
                                        </li>
                                    @endif

                                </ul>
                            </div>

                            <div class="form-group">
                                <label for="resourceFileUpload"> {{ trans("info::info.configure_resources_reupload_label",['max'=>ini_get("upload_max_filesize")]) }}</label>
                                <div class="custom-file mb-2">
                                    <input type="file" name="file" class="custom-file-input"
                                           id="resourceFileUpload">
                                    <label class="custom-file-label"
                                           for="resourceFileUpload">{{ trans("info::info.configure_resources_upload_choose") }}</label>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="mime-src"
                                           name="mime_src_client">
                                    <label class="form-check-label"
                                           for="mime-src">{{ trans("info::info.manage_resources_mime_client_label") }}</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary confirmform">{{ trans('info::info.configure_resource_resource_save') }}</button>

                            <a href="{{ route("info.manage") }}" class="btn btn-secondary">{{ trans('info::info.configure_resource_personal_article_link') }}</a>

                        </form>
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
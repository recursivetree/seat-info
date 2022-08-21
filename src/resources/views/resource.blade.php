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
                        {{ $resource->name }}
                    </div>
                    <div class="card-body">

                        <form action="{{route("info.configure_resource_save", $resource->id)}}" method="POST">
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
                                            Seat-Info manages access over seat roles. To configure access for this article head over to access management under the settings section. In there, you can create roles and add members. To automatically manage members, take a look at squads.
                                        </li>
                                    @endif

                                </ul>
                            </div>

                            <button type="submit" class="btn btn-primary confirmform">{{ trans('info::info.configure_resource_resource_safe') }}</button>

                            <a href="{{ route("info.manage") }}" class="btn btn-secondary">{{ trans('info::info.configure_resource_personal_article_link') }}</a>

                        </form>
                    </div>
                </div>


            </div>
        </div>
    </div>

@stop
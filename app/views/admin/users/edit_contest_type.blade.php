@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Contest Type</h1>
</div>


<form class="form-horizontal" role="form" method="post" action="{{ route('admin/users/contest/type/edit', $contestType->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-contesttype-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">{{ $contestType->id }}</p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-contesttype-id" class="col-sm-2 control-label">User</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                @if(is_null($contestType->user))
                <em>Unknown</em>
                @else
                <a href="{{ route('admin/users/user/edit', $contestType->user->id) }}">{{{ $contestType->user->nameplate() }}}</a>
                @endif
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-contesttype-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-contesttype-name" value="{{{ Input::old('name', $contestType->name) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-contesttype-description" class="col-sm-2 control-label">Description</label>
        <div class="col-sm-10">
            <input type="text" name="description" class="form-control" id="cp-contesttype-description" value="{{{ Input::old('description', $contestType->description) }}}" />
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/users/user/delete', $contestType->id) }}" name="delete_contest_type" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this contest type?');">Delete</a>
            <button type="submit" name="edit_contest_type" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

@stop
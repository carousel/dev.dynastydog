@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Contest</h1>
</div>


<form class="form-horizontal" role="form" method="post" action="{{ route('admin/users/contest/edit', $contest->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-contest-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">{{ $contest->id }}</p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-contest-id" class="col-sm-2 control-label">User</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                @if(is_null($contest->user))
                <em>Unknown</em>
                @else
                <a href="{{ route('admin/users/user/edit', $contest->user->id) }}">{{{ $contest->user->nameplate() }}}</a>
                @endif
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-contest-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-contest-name" value="{{{ Input::old('name', $contest->name) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-contest-typename" class="col-sm-2 control-label">Type Name</label>
        <div class="col-sm-10">
            <input type="text" name="type_name" class="form-control" id="cp-contest-typename" value="{{{ Input::old('type_name', $contest->type_name) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-contest-typedescription" class="col-sm-2 control-label">Type Description</label>
        <div class="col-sm-10">
            <input type="text" name="type_description" class="form-control" id="cp-contest-typedescription" value="{{{ Input::old('type_description', $contest->type_description) }}}" />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-contest-runon" class="col-sm-2 control-label">Run On</label>
        <div class="col-sm-10">
            <p class="form-control-static">{{ $contest->run_on->format('F j, Y') }}</p>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/users/user/delete', $contest->id) }}" name="delete_contest" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this contest?');">Delete</a>
            <button type="submit" name="edit_contest" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

@stop

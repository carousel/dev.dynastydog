@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Alpha Code</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/alpha/code/edit', $alphaCode->code) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-alphacode-title" class="col-sm-2 control-label">Code</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $alphaCode->code }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-alphacode-capacity" class="col-sm-2 control-label">Capacity</label>
        <div class="col-sm-10">
            <input type="number" name="capacity" class="form-control" id="cp-alphacode-capacity" value="{{{ Input::old('capacity', $alphaCode->capacity) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-alphacode-title" class="col-sm-2 control-label">Population</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $alphaCode->population }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/alpha/code/delete', $alphaCode->code) }}" name="delete_alpha_code" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this alpha code?');">Delete</a>
            <button type="submit" name="edit_alpha_code" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

<h2>Users</h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Display Name</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td><a href="{{ route('user/profile', $user->id) }}">{{ $user->id }}</a></td>
            <td><a href="{{ route('user/profile', $user->id) }}">{{{ $user->display_name }}}</a></td>
            <td>{{ $user->created_at->format('F j, Y g:i A') }}</td>
        </tr>
        @endforeach

        @if($users->isEmpty())
        <tr>
            <td colspan="5">No users to display</td>
        </tr>
        @endif
    </tbody>
</table>

@stop

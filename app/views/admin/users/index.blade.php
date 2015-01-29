@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Users</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-users">
            <div class="form-group">
                <label for="search-users-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-users-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-users-displayname" class="col-sm-2 control-label">Display Name</label>
                <div class="col-sm-10">
                    <input type="text" name="display_name" class="form-control" id="search-users-displayname" value="{{{ Input::get('display_name') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-users-status" class="col-sm-2 control-label">Status</label>
                <div class="col-sm-10">
                    <div class="radio">
                        <label for="search-users-status-all">
                            <input type="radio" name="status" id="search-users-status-all" value="all" {{ (Input::get('status', 'all') == 'all') ? 'checked' : '' }} /> All
                        </label>
                    </div>
                    <div class="radio">
                        <label for="search-users-status-trashed">
                            <input type="radio" name="status" id="search-users-status-trashed" value="trashed" {{ (Input::get('status') == 'trashed') ? 'checked' : '' }} /> Trashed Only
                        </label>
                    </div>
                    <div class="radio">
                        <label for="search-users-status-untrashed">
                            <input type="radio" name="status" id="search-users-status-untrashed" value="untrashed" {{ (Input::get('status') == 'untrashed') ? 'checked' : '' }} /> Untrashed Only
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="users" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $users->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Display Name</th>
            <th>Trashed?</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            @if($user->trashed())
            <td>{{ $user->id }}</td>
            <td>{{{ $user->display_name }}}</td>
            @else
            <td><a href="{{ route('admin/users/user/edit', $user->id) }}">{{ $user->id }}</a></td>
            <td><a href="{{ route('admin/users/user/edit', $user->id) }}">{{{ $user->display_name }}}</a></td>
            @endif
            <td>
                @if($user->trashed())
                <a class="btn btn-success btn-xs" href="{{ route('admin/users/user/restore', $user->id) }}">Restore</a>
                <a class="btn btn-danger btn-xs" href="{{ route('admin/users/user/delete/permanent', $user->id) }}" onclick="return confirm('Are you sure you want to permanently delete that user?');">Delete Permanently</a>
                @else
                <em>No</em>
                @endif
            </td>
        </tr>
        @endforeach()

        @if($users->isEmpty())
        <tr>
            <td colspan="3">No users to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $users->appends(array_except(Input::all(), 'page'))->links() }}

@stop

@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Contest Types</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-contesttypes">
            <div class="form-group">
                <label for="search-contesttypes-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-contesttypes-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-contesttypes-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" id="search-contesttypes-name" value="{{{ Input::get('name') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="contest_types" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $contestTypes->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>User</th>
        </tr>
    </thead>
    <tbody>
        @foreach($contestTypes as $contestType)
        <tr>
            <td><a href="{{ route('admin/users/contest/type/edit', $contestType->id) }}">{{ $contestType->id }}</a></td>
            <td><a href="{{ route('admin/users/contest/type/edit', $contestType->id) }}">{{{ $contestType->name }}}</a></td>
            <td>{{{ Str::words($contestType->description, 10) }}}</td>
            <td>
                @if(is_null($contestType->user))
                <em>Unknown</em>
                @else
                <a href="{{ route('admin/users/user/edit', $contestType->user->id) }}">{{{ $contestType->user->nameplate() }}}</a>
                @endif
            </td>
        </tr>
        @endforeach()

        @if($contestTypes->isEmpty())
        <tr>
            <td colspan="4">No contest types to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $contestTypes->appends(array_except(Input::all(), 'page'))->links() }}

@stop

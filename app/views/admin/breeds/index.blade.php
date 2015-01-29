@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Breeds</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-breeds">
            <div class="form-group">
                <label for="search-breeds-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-breeds-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-breeds-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" id="search-breeds-name" value="{{{ Input::get('name') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="breeds" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $breeds->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Active?</th>
        </tr>
    </thead>
    <tbody>
        @foreach($breeds as $breed)
        <tr>
            <td><a href="{{ route('admin/breeds/breed/edit', $breed->id) }}">{{ $breed->id }}</a></td>
            <td><a href="{{ route('admin/breeds/breed/edit', $breed->id) }}">{{ $breed->name }}</a></td>
            <td><big>{{ $breed->isActive() ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>' }}</big></td>
        </tr>
        @endforeach()

        @if($breeds->isEmpty())
        <tr>
            <td colspan="3">No breeds to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $breeds->appends(array_except(Input::all(), 'page'))->links() }}

@stop

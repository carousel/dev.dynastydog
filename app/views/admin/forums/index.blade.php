@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Forums</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-alpha-codes">
            <div class="form-group">
                <label for="search-forums-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-forums-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-forums-title" class="col-sm-2 control-label">Title</label>
                <div class="col-sm-10">
                    <input type="text" name="title" class="form-control" id="search-forums-title" value="{{{ Input::get('title') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="forums" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $forums->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
        </tr>
    </thead>
    <tbody>
        @foreach($forums as $forum)
        <tr>
            <td><a href="{{ route('admin/forums/forum/edit', $forum->id) }}">{{ $forum->id }}</a></td>
            <td><a href="{{ route('admin/forums/forum/edit', $forum->id) }}">{{ $forum->title }}</a></td>
        </tr>
        @endforeach()

        @if($forums->isEmpty())
        <tr>
            <td colspan="3">No forums to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $forums->appends(array_except(Input::all(), 'page'))->links() }}

@stop

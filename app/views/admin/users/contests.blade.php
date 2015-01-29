@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Contests</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-contests">
            <div class="form-group">
                <label for="search-contests-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-contests-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-contests-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" id="search-contests-name" value="{{{ Input::get('name') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="contests" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $contests->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>User</th>
            <th>Type</th>
            <th>Run Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($contests as $contest)
        <tr>
            <td><a href="{{ route('admin/users/contest/edit', $contest->id) }}">{{ $contest->id }}</a></td>
            <td><a href="{{ route('admin/users/contest/edit', $contest->id) }}">{{{ $contest->name }}}</a></td>
            <td>
                @if(is_null($contest->user))
                <em>Unknown</em>
                @else
                <a href="{{ route('admin/users/user/edit', $contest->user->id) }}">{{{ $contest->user->nameplate() }}}</a>
                @endif
            </td>
            <td>
                {{{ $contest->type_name }}}
                <a data-toggle="tooltip" data-html="true" data-placement="right" title="{{{ $contest->type_description }}}"><i class="fa fa-question-circle"></i></a>
            </td>
            <td>
                @if($contest->hasRun())
                <span class="text-muted">{{ $contest->run_on->format('F j, Y') }}</span>
                @else
                {{ $contest->run_on->format('F j, Y') }}
                @endif
            </td>
        </tr>
        @endforeach()

        @if($contests->isEmpty())
        <tr>
            <td colspan="5">No contests to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $contests->appends(array_except(Input::all(), 'page'))->links() }}

@stop

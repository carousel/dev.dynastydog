@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Help Pages</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-helpapges">
            <div class="form-group">
                <label for="search-helpapges-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-helpapges-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-helppages-title" class="col-sm-2 control-label">Title</label>
                <div class="col-sm-10">
                    <input type="text" name="title" class="form-control" id="search-helpapges-title" value="{{{ Input::get('title') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="help_pages" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $helpPages->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
        </tr>
    </thead>
    <tbody>
        @foreach($helpPages as $helpPage)
        <tr>
            <td><a href="{{ route('admin/help/help/page/edit', $helpPage->id) }}">{{ $helpPage->id }}</a></td>
            <td><a href="{{ route('admin/help/help/page/edit', $helpPage->id) }}">{{ $helpPage->title }}</a></td>
        </tr>
        @endforeach()

        @if($helpPages->isEmpty())
        <tr>
            <td colspan="2">No help pages to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $helpPages->appends(array_except(Input::all(), 'page'))->links() }}

@stop

@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Help Categories</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-helpcategories">
            <div class="form-group">
                <label for="search-helpcategories-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-helpcategories-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-helpcategories-title" class="col-sm-2 control-label">Title</label>
                <div class="col-sm-10">
                    <input type="text" name="title" class="form-control" id="search-helpcategories-title" value="{{{ Input::get('title') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="help_categories" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $helpCategories->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Parent</th>
        </tr>
    </thead>
    <tbody>
        @foreach($helpCategories as $helpCategory)
        <tr>
            <td><a href="{{ route('admin/help/help/category/edit', $helpCategory->id) }}">{{ $helpCategory->id }}</a></td>
            <td><a href="{{ route('admin/help/help/category/edit', $helpCategory->id) }}">{{ $helpCategory->title }}</a></td>
            <td>
                @if(is_null($helpCategory->parent))
                <em>None</em>
                @else
                <a href="{{ route('admin/help/help/category/edit', $helpCategory->parent->id) }}">{{ $helpCategory->parent->title }}</a>
                @endif
            </td>
        </tr>
        @endforeach()

        @if($helpCategories->isEmpty())
        <tr>
            <td colspan="3">No help categories to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $helpCategories->appends(array_except(Input::all(), 'page'))->links() }}

@stop

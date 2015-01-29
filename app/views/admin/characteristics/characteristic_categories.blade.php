@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Characteristic Categories</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-characteristiccategories">
            <div class="form-group">
                <label for="search-characteristiccategories-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-characteristiccategories-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-characteristiccategories-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" id="search-characteristiccategories-name" value="{{{ Input::get('name') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="characteristic_categories" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $characteristicCategories->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Parent Category</th>
            <th># Children</th>
            <th># Characteristics</th>
        </tr>
    </thead>
    <tbody>
        @foreach($characteristicCategories as $characteristicCategory)
        <tr>
            <td><a href="{{ route('admin/characteristics/category/edit', $characteristicCategory->id) }}">{{ $characteristicCategory->id }}</a></td>
            <td><a href="{{ route('admin/characteristics/category/edit', $characteristicCategory->id) }}">{{ $characteristicCategory->name }}</a></td>
            <td>
                @if( ! is_null($characteristicCategory->parent))
                <a href="{{ route('admin/characteristics/category/edit', $characteristicCategory->parent->id) }}">{{ $characteristicCategory->parent->name }}</a>
                @else
                <em>None</em>
                @endif
            </td>
            <td>{{ $characteristicCategory->children()->count() }}</td>
            <td>{{ $characteristicCategory->characteristics()->count() }}</td>
        </tr>
        @endforeach()

        @if($characteristicCategories->isEmpty())
        <tr>
            <td colspan="3">No characteristic categories to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $characteristicCategories->appends(array_except(Input::all(), 'page'))->links() }}

@stop

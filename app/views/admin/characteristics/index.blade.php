@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Characteristics</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-characteristics">
            <div class="form-group">
                <label for="search-characteristics-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-characteristics-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-characteristics-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" id="search-characteristics-name" value="{{{ Input::get('name') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="characteristics" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $characteristics->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Percentage of Breeds</th>
            <th>Category</th>
            <th>Active?</th>
        </tr>
    </thead>
    <tbody>
        @foreach($characteristics as $characteristic)
        <tr>
            <td><a href="{{ route('admin/characteristics/characteristic/edit', $characteristic->id) }}">{{ $characteristic->id }}</a></td>
            <td>
                <a href="{{ route('admin/characteristics/characteristic/edit', $characteristic->id) }}">{{ $characteristic->name }}</a>
                @if( ! $characteristic->isType(Characteristic::TYPE_NORMAL))
                <span class="label label-info">{{ $characteristic->getType() }}</span>
                @endif
            </td>
            <td>{{ round(($characteristic->breedCharacteristics()->count() / DB::table('breeds')->count()) * 100, 2) }}%</td>
            <td>
                @if( ! is_null($characteristic->category))
                    <a href="{{ route('admin/characteristics/category/edit', $characteristic->category->id) }}">{{ $characteristic->category->name }}</a>
                    @if( ! is_null($characteristic->category->parent))
                    <i class="fa fa-long-arrow-right"></i>
                    <a href="{{ route('admin/characteristics/category/edit', $characteristic->category->parent->id) }}">{{ $characteristic->category->parent->name }}</a>
                    @endif
                @else
                    <em>None</em>
                @endif
            </td>
            <td>{{ $characteristic->isActive() ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>' }}</td>
        </tr>
        @endforeach()

        @if($characteristics->isEmpty())
        <tr>
            <td colspan="3">No characteristics to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $characteristics->appends(array_except(Input::all(), 'page'))->links() }}

@stop

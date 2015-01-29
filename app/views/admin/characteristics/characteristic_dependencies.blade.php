@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Characteristic Dependencies</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-characteristicdependencies">
            <div class="form-group">
                <label for="search-characteristicdependencies-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-characteristicdependencies-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-characteristicdependencies-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" id="search-characteristicdependencies-name" value="{{{ Input::get('name') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="characteristic_dependencies" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $characteristicDependencies->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Dependent Characteristic</th>
            <th>Type</th>
            <th>Independent Characteristics</th>
            <th>Active?</th>
        </tr>
    </thead>
    <tbody>
        @foreach($characteristicDependencies as $characteristicDependency)
        <tr>
            <td><a href="{{ route('admin/characteristics/dependency/edit', $characteristicDependency->id) }}">{{ $characteristicDependency->id }}</a></td>
            <td>
                <a href="{{ route('admin/characteristics/dependency/edit', $characteristicDependency->id) }}">{{ $characteristicDependency->characteristic->name }}</a>
                <a href="{{ route('admin/characteristics/characteristic/edit', $characteristicDependency->characteristic->id) }}"><i class="fa fa-external-link"></i></a>
            </td>
            <td>{{ $characteristicDependency->getType() }}</td>
            <td>
                <ul>
                    @foreach($characteristicDependency->independentCharacteristics as $independentCharacteristic)
                    <li><a href="{{ route('admin/characteristics/characteristic/edit', $independentCharacteristic->characteristic->id) }}">{{ $independentCharacteristic->characteristic->name }}</a></li>
                    @endforeach

                    @if($characteristicDependency->independentCharacteristics->isEmpty())
                    <li><em>None</em></li>
                    @endif
                </ul>
            </td>
            <td>{{ $characteristicDependency->isActive() ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>' }}</td>
        </tr>
        @endforeach()

        @if($characteristicDependencies->isEmpty())
        <tr>
            <td colspan="3">No characteristic dependencies to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $characteristicDependencies->appends(array_except(Input::all(), 'page'))->links() }}

@stop

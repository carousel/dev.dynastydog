@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Characteristic Category</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/category/edit', $characteristicCategory->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-characteristiccategory-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $characteristicCategory->id }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristiccategory-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-characteristiccategory-name" value="{{{ Input::old('name', $characteristicCategory->name) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristiccategory-parent" class="col-sm-2 control-label">Parent</label>
        <div class="col-sm-10">
            <select name="parent" class="form-control" id="cp-characteristiccategory-parent">
                <option value="">None</option>
                @foreach($parentCharacteristicCategories as $parentCharacteristicCategory)
                <option value="{{ $parentCharacteristicCategory->id }}" {{ (Input::old('parent', $characteristicCategory->parent_category_id) == $parentCharacteristicCategory->id) ? 'selected' : '' }}>
                    {{ $parentCharacteristicCategory->name }}
                </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/characteristics/category/delete', $characteristicCategory->id) }}" name="delete_category" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
            <button type="submit" name="edit_category" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

<h2>Children</h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Total Characteristics</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($childCharacteristicCategories as $childCharacteristicCategory)
        <tr>
            <td><a href="{{ route('admin/characteristics/category/edit', $childCharacteristicCategory->id) }}">{{ $childCharacteristicCategory->id }}</a></td>
            <td><a href="{{ route('admin/characteristics/category/edit', $childCharacteristicCategory->id) }}">{{ $childCharacteristicCategory->name }}</a></td>
            <td>{{ $childCharacteristicCategory->characteristics()->count() }}</td>
            <td class="text-right">
                <a class="btn btn-danger btn-xs" href="{{ route('admin/characteristics/category/remove_child_category', ['characteristicCategory' => $characteristicCategory->id, 'childCharacteristicCategory' => $childCharacteristicCategory->id]) }}">Remove</a>
            </td>
        </tr>
        @endforeach

        @if($childCharacteristicCategories->isEmpty())
        <tr>
            <td colspan="4">No child categories to display</td>
        </tr>
        @endif
    </tbody>
</table>

<h2>Characteristics</h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Active</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($characteristics as $characteristic)
            <td><a href="{{ route('admin/characteristics/characteristic/edit', $characteristic->id) }}">{{ $characteristic->id }}</a></td>
            <td><a href="{{ route('admin/characteristics/characteristic/edit', $characteristic->id) }}">{{ $characteristic->name }}</a></td>
            <td><big>{{ $characteristic->isActive() ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>' }}</big></td>
            <td class="text-right">
                <a class="btn btn-danger btn-xs" href="{{ route('admin/characteristics/category/remove_characteristic', ['characteristicCategory' => $characteristicCategory->id, 'characteristic' => $characteristic->id]) }}">Remove</a>
            </td>
        </tr>
        @endforeach

        @if($characteristics->isEmpty())
        <tr>
            <td colspan="4">No characteristics to display</td>
        </tr>
        @endif
    </tbody>
</table>

@stop

@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New Characteristic Category</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/category/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-characteristiccategory-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-characteristiccategory-name" value="{{{ Input::old('name') }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristiccategory-parent" class="col-sm-2 control-label">Parent</label>
        <div class="col-sm-10">
            <select name="parent" class="form-control" id="cp-characteristiccategory-parent">
                <option value="">None</option>
                @foreach($parentCharacteristicCategories as $parentCharacteristicCategory)
                <option value="{{ $parentCharacteristicCategory->id }}" {{ (Input::old('parent') == $parentCharacteristicCategory->id) ? 'selected' : '' }}>
                    {{ $parentCharacteristicCategory->name }}
                </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_characteristic_category" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop

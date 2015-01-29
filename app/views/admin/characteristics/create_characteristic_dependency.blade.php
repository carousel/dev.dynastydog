@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New Characteristic Dependency</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/dependency/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-characteristicdependency-characteristic" class="col-sm-2 control-label">Dependent Characteristic</label>
        <div class="col-sm-10">
            <select name="characteristic" class="form-control" id="cp-characteristicdependency-characteristic">
                @if( ! $uncategorizedCharacteristics->isEmpty())
                <optgroup label="Uncategorized">
                    @foreach($uncategorizedCharacteristics as $characteristic)
                    <option value="{{ $characteristic->id }}" {{ (Input::old('characteristic') == $characteristic->id) ? 'selected' : '' }}>{{ $characteristic->name }}</option>
                    @endforeach
                </optgroup>
                @endif

                @foreach($characteristicCategories as $category)
                <optgroup label="{{ $category->parent->name }}: {{ $category->name }}">
                    @foreach($category->characteristics as $characteristic)
                    <option value="{{ $characteristic->id }}" {{ (Input::old('characteristic') == $characteristic->id) ? 'selected' : '' }}>{{ $characteristic->name }}</option>
                    @endforeach
                </optgroup>
                @endforeach

                @if($uncategorizedCharacteristics->isEmpty() and $characteristicCategories->isEmpty())
                <option value="">No characteristics available</option>
                @endif
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristicdependency-type" class="col-sm-2 control-label">Type</label>
        <div class="col-sm-10">
            <select name="type" class="form-control" id="cp-characteristicdependency-type">
                @foreach(CharacteristicDependency::types() as $typeId => $type)
                <option value="{{ $typeId }}" {{ (Input::old('type') == $typeId) ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristicdependency-active" class="col-sm-2 control-label">Active?</label>
        <div class="col-sm-7">
            <div class="checkbox">
                <label for="cp-characteristicdependency-active">
                    <input type="checkbox" name="active" value="yes" id="cp-characteristicdependency-active" {{ (Input::old('active') == 'yes') ? 'checked' : '' }} />
                    Yes
                </label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_characteristic_dependency" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop

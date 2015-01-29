@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New Characteristic</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/characteristic/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-characteristic-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-characteristic-name" value="{{{ Input::old('name') }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristic-description" class="col-sm-2 control-label">Description</label>
        <div class="col-sm-10">
            <textarea name="description" class="form-control" id="cp-characteristic-description" rows="3" required>{{{ Input::old('description') }}}</textarea>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristic-helppage" class="col-sm-2 control-label">Help Page</label>
        <div class="col-sm-10">
            <select name="help_page" class="form-control">
                <option value="">None</option>
                @foreach($helpPages as $helpPage)
                    <option value="{{ $helpPage->id }}" {{ (Input::old('help_page') == $helpPage->id) ? 'selected' : '' }}>{{ $helpPage->title }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristic-category" class="col-sm-2 control-label">Category</label>
        <div class="col-sm-10">
            <select name="category" class="form-control" id="cp-characteristic-category">
                <option value="">None</option>
                @foreach($parentCharacteristicCategories as $parentCharacteristicCategory)
                <optgroup label="{{ $parentCharacteristicCategory->name }}">
                    @foreach($parentCharacteristicCategory->children as $characteristicCategory)
                    <option value="{{ $characteristicCategory->id }}" {{ (Input::old('category') == $characteristicCategory->id) ? 'selected' : '' }}>
                        {{ $characteristicCategory->name }}
                    </option>
                    @endforeach
                </optgroup>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cp-characteristic-active" class="col-sm-4 control-label">Active?</label>
                <div class="col-sm-8">
                    <div class="checkbox">
                        <label for="cp-characteristic-active">
                            <input type="checkbox" name="active" value="yes" id="cp-characteristic-active" {{ (Input::old('active') == 'yes') ? 'checked' : '' }}/> Yes
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cp-characteristic-hide" class="col-sm-4 control-label">Hide?</label>
                <div class="col-sm-8">
                    <div class="checkbox">
                        <label for="cp-characteristic-hide">
                            <input type="checkbox" name="hide" value="yes" id="cp-characteristic-hide" {{ (Input::old('hide') == 'yes') ? 'checked' : '' }}/> Yes
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cp-characteristic-ignorable" class="col-sm-4 control-label">Ignorable?</label>
                <div class="col-sm-8">
                    <div class="checkbox">
                        <label for="cp-characteristic-ignorable">
                            <input type="checkbox" name="ignorable" value="yes" id="cp-characteristic-ignorable" {{ (Input::old('ignorable') == 'yes') ? 'checked' : '' }}/> Yes
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cp-characteristic-hidegenotypes" class="col-sm-4 control-label">Hide Genotypes?</label>
                <div class="col-sm-8">
                    <div class="checkbox">
                        <label for="cp-characteristic-hidegenotypes">
                            <input type="checkbox" name="hide_genotypes" value="yes" id="cp-characteristic-hidegenotypes"{{ (Input::old('hide_genotypes') == 'yes') ? 'checked' : '' }} /> Yes
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_characteristic" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop

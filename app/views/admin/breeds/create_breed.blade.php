@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New Breed</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/breeds/breed/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-breed-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-breed-name" value="{{{ Input::old('name') }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-breed-description" class="col-sm-2 control-label">Description</label>
        <div class="col-sm-10">
            <textarea name="description" class="form-control" id="cp-breed-description" rows="3" placeholder="Optional">{{{ Input::old('description') }}}</textarea>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-breed-imageurl" class="col-sm-2 control-label">Image Filename</label>
        <div class="col-sm-10">
            <input type="text" name="image_filename" class="form-control" id="cp-breed-imageurl" value="{{{ Input::old('image_filename') }}}" maxlength="255" placeholder="eg. german_shepherd_dog" required />
            <span class="help-block well well-sm alert-info">
                <strong>Full URL:</strong> <em>{{ asset((new Breed())->fill(['image_url' => 'FILENAME_GOES_HERE'])->getImageUrl()) }}</em>
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cp-breed-userid" class="col-sm-4 control-label">User ID</label>
                <div class="col-sm-8">
                    <div class="input-group">
                        <span class="input-group-addon">#</span>
                        <input type="text" name="user_id" class="form-control" id="cp-breed-userid" value="{{{ Input::get('user_id') }}}" maxlength="32" placeholder="Optional" />
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cp-breed-dogid" class="col-sm-4 control-label">Dog ID</label>
                <div class="col-sm-8">
                    <div class="input-group">
                        <span class="input-group-addon">#</span>
                        <input type="text" name="dog_id" class="form-control" id="cp-breed-dogid" value="{{{ Input::get('dog_id') }}}" maxlength="10" placeholder="Optional" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cp-breed-active" class="col-sm-4 control-label">Active?</label>
                <div class="col-sm-8">
                    <div class="checkbox">
                        <label for="cp-breed-active">
                            <input type="checkbox" name="active" value="yes" id="cp-breed-active" {{ (Input::old('active') == 'yes') ? 'checked' : '' }}/> Yes
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cp-breed-importable" class="col-sm-4 control-label">Importable?</label>
                <div class="col-sm-8">
                    <div class="checkbox">
                        <label for="cp-breed-importable">
                            <input type="checkbox" name="importable" value="yes" id="cp-breed-importable" {{ (Input::old('importable') == 'yes') ? 'checked' : '' }}/> Yes
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cp-breed-extinctable" class="col-sm-4 control-label">Extinctable?</label>
                <div class="col-sm-8">
                    <div class="checkbox">
                        <label for="cp-breed-extinctable">
                            <input type="checkbox" name="extinctable" value="yes" id="cp-breed-extinctable"{{ (Input::old('extinctable') == 'yes') ? 'checked' : '' }} /> Yes
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_breed" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop

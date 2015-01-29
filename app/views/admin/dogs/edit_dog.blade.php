@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Dog</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/dogs/dog/edit', $dog->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-dog-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">{{ $dog->id }}</p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-dog-owner" class="col-sm-2 control-label">Owner</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                @if(is_null($dog->owner))
                Pet Homed
                @else
                <a href="{{ route('admin/users/user/edit', $dog->owner->id) }}">{{{ $dog->owner->nameplate() }}}</a>
                @endif
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-dog-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-dog-name" value="{{{ Input::old('name', $dog->name) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-dog-kennel_prefix" class="col-sm-2 control-label">Kennel Prefix</label>
        <div class="col-sm-10">
            <input type="text" name="kennel_prefix" class="form-control" id="cp-dog-kennel_prefix" value="{{{ Input::old('kennel_prefix', $dog->kennel_prefix) }}}" />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-dog-image_url" class="col-sm-2 control-label">Image URL</label>
        <div class="col-sm-10">
            <input type="text" name="image_url" class="form-control" id="cp-dog-image_url" value="{{{ Input::old('image_url', $dog->image_url) }}}" />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-dogs-dog-notes" class="col-sm-2 control-label">Notes</label>
        <div class="col-sm-10">
            <textarea name="notes" class="form-control" id="cp-dogs-dog-notes" rows="3">{{{ Input::old('notes', $dog->notes) }}}</textarea>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/dogs/dog/delete', $dog->id) }}" name="delete_dog" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this dog?');">Delete</a>
            @if($dog->isComplete())
            <a href="{{ route('admin/dogs/dog/recomplete', $dog->id) }}" name="delete_dog" class="btn btn-warning" onclick="return confirm('Are you sure you want to recomplete this dog?');">Recomplete</a>
            @else
            <a href="{{ route('admin/dogs/dog/refresh_phenotypes', $dog->id) }}" name="delete_dog" class="btn btn-warning" onclick="return confirm('Are you sure you want to complete this dog?');">Complete</a>
            @endif
            <a href="{{ route('admin/dogs/dog/refresh_phenotypes', $dog->id) }}" name="refresh_dog_phenotypes" class="btn btn-warning" onclick="return confirm('Are you sure you want to refresh the phenotypes of this dog?');">Refresh Phenotypes</a>
            <button type="submit" name="edit_dog" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

@stop

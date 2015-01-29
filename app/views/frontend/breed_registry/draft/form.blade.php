@extends($layout)

{{-- Page content --}}
@section('content')

@if($breedDraft->hasReasonsForRejection())
<div class="alert alert-danger">
    <p><strong>Rejection Reasons</strong></p>
    <p>{{{ $breedDraft->rejection_reasons }}}</p>
</div>
@endif

<div class="page-header">
    <div class="text-right pull-right">
        <a href="{{ route('breed_registry/manage') }}" class="btn btn-primary">Manage Your Breeds</a>
    </div>

    <h1>Breed Submission Form</h1>
</div>

<h2>General Information</h2>

<form class="form-horizontal" role="form" method="post" enctype="multipart/form-data" action="{{ route('breed_registry/draft/form', $breedDraft->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <div class="form-group">
        <label for="draft-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="draft-name" value="{{{ Input::old('name', $breedDraft->name) }}}" placeholder="32 character limit" maxlength="32" />
        </div>
    </div>

    <div class="form-group">
        <label for="draft-description" class="col-sm-2 control-label">Description</label>
        <div class="col-sm-10">
            <textarea name="description" class="form-control" id="draft-description" rows="3">{{{ Input::old('description', $breedDraft->description) }}}</textarea>
        </div>
    </div>

    @if($breedDraft->isOfficial())
    <div class="form-group">
        <label for="draft-health-disorders" class="col-sm-2 control-label">Breed Health Disorders</label>
        <div class="col-sm-10">
            <textarea name="health_disorders" class="form-control" id="draft-health-disorders" rows="5" placeholder="Please list the major genetic health disorders that exist in the breed, regardless of whether they currently exist on Dynasty or not. This will be a guideline for admin.">{{{ Input::old('description', $breedDraft->health_disorders) }}}</textarea>
        </div>
    </div>
    @else
    <div class="form-group">
        <label for="draft-dog" class="col-sm-2 control-label">Dog</label>
        <div class="col-sm-10">
            <select name="dog" class="form-control" id="draft-dog">
                <option value=""></option>
                @foreach($kennelGroups as $kennelGroup)
                <optgroup label="{{{ $kennelGroup->name }}}">
                    @foreach($kennelGroup->dogs as $dog)
                    <option value="{{ $dog->id }}" {{ ($dog->id == Input::old('dog', $breedDraft->dog_id)) ? 'selected' : '' }}>{{{ $dog->nameplate() }}}</option>
                    @endforeach
                </optgroup>
                @endforeach

                @if ($kennelGroups->isEmpty())
                <option value="">No dogs available</option>
                @endif
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="draft-image" class="col-sm-2 control-label">Breed Image</label>
        <div class="col-sm-10">
            @if($breedDraft->hasImage())
            <img src="{{{ asset($breedDraft->getImageUrl()) }}}?{{ $breedDraft->updated_at }}" class="img-responsive center-block" alt="Breed Image" title="Breed Image" />
            @endif

            <input type="file" name="image" id="draft-image" />
            <p class="help-block">Image must be of type PNG, no wider than 700px and no taller than 500px</p>
        </div>
    </div>
    @endif

    <p class="text-right">
        <button type="submit" name="save_draft" class="btn btn-primary" data-loading-text="<i class='fa fa-cog fa-spin'></i> Saving...">Save Draft</button>
        <a class="btn btn-danger" href="{{ route('breed_registry/draft/delete', $breedDraft->id) }}" onclick="return confirm('Are you sure you want to delete this draft?');" data-loading-text="<i class='fa fa-cog fa-spin'></i> Deleting...">Delete Draft</a>
    </p>
</form>

<h2>Characteristics</h2>

@if( ! $breedDraft->isOfficial())
<form class="form-horizontal" role="form" method="post" action="{{ route('breed_registry/draft/form/characteristic/add', $breedDraft->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <div class="form-group">
        <label for="draft-charactereistic" class="col-sm-2 control-label">Characteristic</label>
        <div class="col-sm-10">
            <select name="characteristics[]" class="form-control" id="draft-characteristic" size="5" multiple>
                @foreach($characteristicCategories as $category)
                <optgroup label="{{ $category->parent->name }}: {{ $category->name }}">
                    @foreach($category->characteristics as $characteristic)
                    <option value="{{ $characteristic->id }}">{{ $characteristic->name }}</option>
                    @endforeach
                </optgroup>
                @endforeach

                @if($characteristicCategories->isEmpty())
                <option value="">No characteristics available</option>
                @endif
            </select>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="add_characteristic" class="btn btn-primary" data-loading-text="<i class='fa fa-cog fa-spin'></i> Adding...">Add</button>
        </div>
    </div>
</form>
@endif

<div class="alert alert-warning text-center">
    <strong>Phenotypes and genotypes for a characteristic must match!</strong>
</div>

<div class="well well-sm text-justify">
    <a id="chlist"><!-- Empty --></a>
    @foreach($breedDraftCharacteristics as $draftCharacteristic)
    <a class="btn btn-sm {{ $draftCharacteristic->wasSaved() ? 'btn-default' : 'btn-info' }}" style="margin-bottom: 0.5em;" href="{{ route('breed_registry/draft/form/characteristic', $draftCharacteristic->id) }}">{{ $draftCharacteristic->characteristic->name }}</a>
    @endforeach

    @if($breedDraftCharacteristics->isEmpty())
    <p class="text-center no-margin">No characteristics added</p>
    @endif
</div>

<p class="text-center">
    @if($breedDraft->isOfficial())
    <a class="btn btn-block btn-lg btn-success" href="{{ route('breed_registry/draft/form/submit', $breedDraft->id) }}" onclick="return confirm('Are you sure you filled out every single characteristic to be a realistic representation of this breed? The less accurate this is, the longer it will take for the breed to be approved.');" data-loading-text="<i class='fa fa-cog fa-spin'></i> Submitting...">Submit Breed</a>
    @else
    <a class="btn btn-block btn-lg btn-success" href="{{ route('breed_registry/draft/form/submit', $breedDraft->id) }}" onclick="return confirm('Are you sure you are finished with this entry?');" data-loading-text="<i class='fa fa-cog fa-spin'></i> Submitting...">Submit Breed</a>
    @endif
</p>

@stop

{{-- JS assets --}}
@section('js_assets')
@parent
<script type="text/javascript" src="{{ asset('assets/js/breed_registry.js') }}"></script>
@stop

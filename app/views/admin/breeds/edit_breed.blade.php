@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Breed</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/breeds/breed/edit', $breed->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-breed-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $breed->id }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-breed-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-breed-name" value="{{{ Input::old('name', $breed->name) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-breed-description" class="col-sm-2 control-label">Description</label>
        <div class="col-sm-10">
            <textarea name="description" class="form-control" id="cp-breed-description" rows="3" placeholder="Optional">{{{ Input::old('description', $breed->description) }}}</textarea>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-breed-imageurl" class="col-sm-2 control-label">Image Filename</label>
        <div class="col-sm-10">
            <input type="text" name="image_filename" class="form-control" id="cp-breed-imageurl" value="{{{ Input::old('image_filename', $breed->image_url) }}}" maxlength="255" placeholder="eg. german_shepherd_dog" required />
            <span class="help-block well well-sm alert-info">
                <strong>Full URL:</strong> <em>{{ asset($breed->getImageUrl()) }}</em>
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
                        <input type="text" name="user_id" class="form-control" id="cp-breed-userid" value="{{{ Input::get('user_id', $breed->creator_id) }}}" maxlength="32" placeholder="Optional" />
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
                        <input type="text" name="dog_id" class="form-control" id="cp-breed-dogid" value="{{{ Input::get('dog_id', $breed->originator_id) }}}" maxlength="10" placeholder="Optional" />
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
                            <input type="checkbox" name="active" value="yes" id="cp-breed-active" {{ (Input::old('active', ($breed->active ? 'yes' : 'no')) == 'yes') ? 'checked' : '' }}/> Yes
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
                            <input type="checkbox" name="importable" value="yes" id="cp-breed-importable" {{ (Input::old('importable', ($breed->importable ? 'yes' : 'no')) == 'yes') ? 'checked' : '' }}/> Yes
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
                            <input type="checkbox" name="extinctable" value="yes" id="cp-breed-extinctable"{{ (Input::old('extinctable', ($breed->extinctable ? 'yes' : 'no')) == 'yes') ? 'checked' : '' }} /> Yes
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/breeds/breed/delete', $breed->id) }}" name="delete_breed" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this breed?');">Delete</a>
            <a href="{{ route('admin/breeds/breed/clone', $breed->id) }}" name="clone_breed" class="btn btn-default" onclick="return confirm('Are you sure you want to clone this breed?');">
                <i class="fa fa-fw fa-code-fork"></i> Clone
            </a>
            <button type="submit" name="edit_breed" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

<hr />

<h2>
    @if($breed->hasGenotypes())
    <i class="fa fa-check text-success"></i>
    @else
    <i class="fa fa-times text-danger"></i>
    @endif

    Genotypes

    <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#breed_genotypes">
        <i class="fa fa-plus"></i>
    </button>
</h2>

<div id="breed_genotypes" class="collapse {{ Input::old('edit_breed_genotypes') ? 'in' : '' }}">
    <form class="form-horizontal" role="form" method="post" action="{{ route('admin/breeds/breed/genotypes/update', $breed->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <div class="row">
            @foreach($loci as $locus)
            <div class="form-group col-sm-6">
                <div class="container-fluid">
                    <div class="row">
                        @if($breed->genotypes()->where('genotypes.locus_id', $locus->id)->wherePivot('frequency', '>', 0)->count() > 0)
                        <label for="breedGenotypes{{ $locus->id }}" class="col-sm-4 control-label text-success">
                            <i class="fa fa-check"></i> {{ $locus->name }}
                        </label>
                        @else
                        <label for="breedGenotypes{{ $locus->id }}" class="col-sm-4 control-label text-danger">
                            <i class="fa fa-times"></i> {{ $locus->name }}
                        </label>
                        @endif

                        <div class="col-sm-8">
                            <div class="row">
                                @foreach($breed->genotypes()->where('genotypes.locus_id', $locus->id)->orderByAlleles()->get() as $genotype)
                                <div class="col-sm-4">
                                    <p class="form-control-static">
                                        <a href="{{ route('admin/genetics/genotype/edit', $genotype->id) }}">{{ $genotype->toSymbol() }}</a>
                                    </p>
                                </div>
                                <div class="col-sm-8">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon" title="Frequency"><i class="fa fa-bar-chart-o"></i></span>
                                        <input type="text" name="frequency[{{ $genotype->id }}]" class="form-control" id="breedGenotypeFrequency{{ $genotype->id }}" value="{{ $genotype->pivot->frequency }}" maxlength="3">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <hr />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2 text-right">
                <button type="submit" name="edit_breed_genotypes" value="edit_breed_genotypes" class="btn btn-primary">Save</button>
            </div>
        </div>
    </form>
</div>

<hr />

<h2>
    @if($breed->hasCharacteristics())
    <i class="fa fa-check text-success"></i>
    @else
    <i class="fa fa-times text-danger"></i>
    @endif

    Characteristics

    <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#breed_characteristics">
        <i class="fa fa-plus"></i>
    </button>
</h2>

<div id="breed_characteristics" class="collapse {{ (Input::old('create_breed_characteristic') or Input::old('edit_breed_characteristic')) ? 'in' : '' }}">
    <form class="form-horizontal" role="form" method="post" action="{{ route('admin/breeds/breed/characteristic/create', $breed->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />

        <div class="form-group">
            <label for="breedCharacteristicId" class="col-sm-2 control-label">Characteristic</label>
            <div class="col-sm-10">
                <select name="characteristics[]" class="form-control" id="breedCharacteristicId" size="8" multiple required>
                    @foreach($characteristicCategories as $category)
                    <optgroup label="{{ $category->parent->name }}: {{ $category->name }}">
                        @foreach($category->characteristics as $characteristic)
                        <option value="{{ $characteristic->id }}" {{ in_array($characteristic->id, (array)Input::old('characteristics')) ? 'selected' : '' }}>{{ $characteristic->name }}</option>
                        @endforeach
                    </optgroup>
                    @endforeach

                    @if($characteristicCategories->isEmpty())
                    <option value="">No characteritics available</option>
                    @endif
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="breedCharacteristicActive" class="col-sm-4 control-label">Active?</label>
                    <div class="col-sm-8">
                        <div class="checkbox">
                            <label for="breedCharacteristicActive">
                                <input type="checkbox" name="active_characteristic" value="yes" id="breedCharacteristicActive" {{ (Input::old('active_characteristic') == 'yes') ? 'checked' : '' }}/> Yes
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="breedCharacteristicHide" class="col-sm-4 control-label">Hide?</label>
                    <div class="col-sm-8">
                        <div class="checkbox">
                            <label for="breedCharacteristicHide">
                                <input type="checkbox" name="hide_characteristic" value="yes" id="breedCharacteristicHide" {{ (Input::old('hide_characteristic') == 'yes') ? 'checked' : '' }}/> Yes
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2 text-right">
                <button type="submit" name="create_breed_characteristic" value="create_breed_characteristic" class="btn btn-primary">Create</button>
            </div>
        </div>
    </form>

    <div class="well well-sm">
        <a id="chlist"><!-- Empty --></a>
        @foreach($breedCharacteristics as $breedCharacteristic)
        <a class="btn btn-sm btn-info" href="#ch{{ $breedCharacteristic->id }}">{{ $breedCharacteristic->characteristic->name }}</a>
        @endforeach
    </div>

    @foreach($breedCharacteristics as $breedCharacteristic)
    <div class="well well-sm">
        <a id="ch{{ $breedCharacteristic->id }}"><!-- Empty --></a>
        <h3>
            <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#breed_characteristic{{$breedCharacteristic->id }}">
                <i class="fa fa-plus"></i>
            </button>
            {{ $breedCharacteristic->characteristic->name }}
            <a class="btn btn-xs" href="{{ route('admin/characteristics/characteristic/edit', $breedCharacteristic->characteristic->id) }}"><i class="fa fa-external-link"></i></a>
            <a class="pull-right text-muted" href="#chlist"><i class="fa fa-arrow-up"></i></a>
        </h3>
        <div id="breed_characteristic{{ $breedCharacteristic->id }}" class="collapse">
            <form class="form-horizontal" role="form" method="post" action="{{ route('admin/breeds/breed/characteristic/update', $breedCharacteristic->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="breedCharacteristic{{ $breedCharacteristic->id }}Active" class="col-sm-4 control-label">Active?</label>
                            <div class="col-sm-8">
                                <div class="checkbox">
                                    <label for="breedCharacteristic{{ $breedCharacteristic->id }}Active">
                                        <input type="checkbox" name="existing_active_characteristic" value="yes" id="breedCharacteristic{{ $breedCharacteristic->id }}Active" {{ (($breedCharacteristic->active ? 'yes' : 'no') == 'yes') ? 'checked' : '' }}/> Yes
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="breedCharacteristic{{ $breedCharacteristic->id }}Hide" class="col-sm-4 control-label">Hide?</label>
                            <div class="col-sm-8">
                                <div class="checkbox">
                                    <label for="breedCharacteristic{{ $breedCharacteristic->id }}Hide">
                                        <input type="checkbox" name="existing_hide_characteristic" value="yes" id="breedCharacteristic{{ $breedCharacteristic->id }}Hide" {{ (($breedCharacteristic->hide ? 'yes' : 'no') == 'yes') ? 'checked' : '' }}/> Yes
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($breedCharacteristic->isGenetic() and ($breedCharacteristic->characteristic->genotypesCanBeRevealed() or $breedCharacteristic->characteristic->phenotypesCanBeRevealed()))
                <hr />

                <div class="form-group">
                    <div class="col-sm-12">
                        <h4>Genetic Profile</h4>
                    </div>
                </div>

                @if($breedCharacteristic->characteristic->phenotypesCanBeRevealed())
                <div class="form-group">
                    <label for="breedCharacteristic{{ $breedCharacteristic->id }}GeneticPhenotypesCanBeKnown" class="col-sm-2 control-label">Age to Reveale Phenotypes</label>
                    <div class="col-sm-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Minimum</span>
                            <input type="text" name="minimum_age_to_reveal_phenotypes" class="form-control" value="{{{ $breedCharacteristic->min_age_to_reveal_phenotypes }}}" maxlength="5" />
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Maximum</span>
                            <input type="text" name="maximum_age_to_reveal_phenotypes" class="form-control" value="{{{ $breedCharacteristic->max_age_to_reveal_phenotypes }}}" maxlength="5" />
                        </div>
                    </div>
                </div>
                @endif

                @if($breedCharacteristic->characteristic->genotypesCanBeRevealed())
                <div class="form-group">
                    <label for="breedCharacteristic{{ $breedCharacteristic->id }}GeneticGenotypesCanBeKnown" class="col-sm-2 control-label">Age to Reveal Genotypes</label>
                    <div class="col-sm-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Minimum</span>
                            <input type="text" name="minimum_age_to_reveal_genotypes" class="form-control" value="{{{ $breedCharacteristic->min_age_to_reveal_genotypes }}}" maxlength="5" />
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Maximum</span>
                            <input type="text" name="maximum_age_to_reveal_genotypes" class="form-control" value="{{{ $breedCharacteristic->max_age_to_reveal_genotypes }}}" maxlength="5" />
                        </div>
                    </div>
                </div>
                @endif
                @endif

                @if($breedCharacteristic->characteristic->isRanged())
                <hr />

                <div class="form-group">
                    <div class="col-sm-12">
                        <h4>Range Profile</h4>
                    </div>
                </div>
                <div class="form-group">
                    <label for="breedCharacteristic{{ $breedCharacteristic->id }}RangeValue" class="col-sm-2 control-label">Value</label>
                    <div class="col-sm-2 text-center">
                        <p class="form-control-static"><big><span class="label label-danger">Female</span></big></p>
                    </div>
                    <div class="col-sm-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Minimum</span>
                            <input type="text" name="minimum_female_ranged_value" class="form-control" id="breedCharacteristic{{ $breedCharacteristic->id }}RangeValue" value="{{{ $breedCharacteristic->min_female_ranged_value }}}" maxlength="10" required />
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Maximum</span>
                            <input type="text" name="maximum_female_ranged_value" class="form-control" value="{{{ $breedCharacteristic->max_female_ranged_value }}}" maxlength="10" required />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-2 col-sm-offset-2 text-center">
                        <p class="form-control-static"><big><span class="label label-info">Male</span></big></p>
                    </div>
                    <div class="col-sm-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Minimum</span>
                            <input type="text" name="minimum_male_ranged_value" class="form-control" id="breedCharacteristic{{ $breedCharacteristic->id }}RangeValue" value="{{{ $breedCharacteristic->min_male_ranged_value }}}" maxlength="10" required />
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Maximum</span>
                            <input type="text" name="maximum_male_ranged_value" class="form-control" value="{{{ $breedCharacteristic->max_male_ranged_value }}}" maxlength="10" required />
                        </div>
                    </div>
                </div>

                @if($breedCharacteristic->characteristic->rangedValueCanBeRevealed())
                <div class="form-group">
                    <label for="breedCharacteristic{{ $breedCharacteristic->id }}RangeValueCanBeKnown" class="col-sm-2 control-label">Age to Reveal Ranged Value</label>
                    <div class="col-sm-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Minimum</span>
                            <input type="text" name="minimum_age_to_reveal_ranged_value" class="form-control" value="{{{ $breedCharacteristic->min_age_to_reveal_ranged_value }}}" maxlength="5" />
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Maximum</span>
                            <input type="text" name="maximum_age_to_reveal_ranged_value" class="form-control" value="{{{ $breedCharacteristic->max_age_to_reveal_ranged_value }}}" maxlength="5" />
                        </div>
                    </div>
                </div>
                @endif

                @if($breedCharacteristic->characteristic->hasRangedGrowth())
                <div class="form-group">
                    <label for="breedCharacteristic{{ $breedCharacteristic->id }}RangeGrowth" class="col-sm-2 control-label">Age for Ranged Value to Grow</label>
                    <div class="col-sm-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Minimum</span>
                            <input type="text" name="minimum_age_to_stop_growing" class="form-control" value="{{{ $breedCharacteristic->min_age_to_stop_growing }}}" maxlength="5" />
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Maximum</span>
                            <input type="text" name="maximum_age_to_stop_growing" class="form-control" value="{{{ $breedCharacteristic->max_age_to_stop_growing }}}" maxlength="5" />
                        </div>
                    </div>
                </div>
                @endif
                @endif

                @if($breedCharacteristic->hasSeverities())
                <hr />

                <div class="form-group">
                    <div class="col-sm-12">
                        <h4>Severities</h4>
                    </div>
                </div>

                @foreach($breedCharacteristic->severities as $breedCharacteristicSeverity)
                <div class="panel panel-info">
                    <div class="panel-heading clearfix">
                        <h5 class="panel-title">{{ $breedCharacteristicSeverity->characteristicSeverity->min_value }} - {{ $breedCharacteristicSeverity->characteristicSeverity->max_value }}</h5>
                    </div>

                    <div class="panel-body">
                        @if($breedCharacteristicSeverity->characteristicSeverity->canBeExpressed())
                        <div class="form-group">
                            <label for="breedCharacteristicHealthSeverity{{ $breedCharacteristicSeverity->id }}OnsetAge" class="col-sm-2 control-label">Age to Express</label>
                            <div class="col-sm-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon">Minimum</span>
                                    <input type="text" name="breed_characteristic_severity[{{ $breedCharacteristicSeverity->id }}][minimum_age_to_express]" class="form-control" id="breedCharacteristicHealthSeverity{{ $breedCharacteristicSeverity->id }}OnsetAge" value="{{{ $breedCharacteristicSeverity->min_age_to_express }}}" maxlength="5" required />
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon">Maximum</span>
                                    <input type="text" name="breed_characteristic_severity[{{ $breedCharacteristicSeverity->id }}][maximum_age_to_express]" class="form-control" value="{{{ $breedCharacteristicSeverity->max_age_to_express }}}" maxlength="5" required />
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($breedCharacteristicSeverity->characteristicSeverity->valueCanBeRevealed())
                        <div class="form-group">
                            <label for="breedCharacteristicHealthSeverity{{ $breedCharacteristicSeverity->id }}ValueCanBeKnown" class="col-sm-2 control-label">Age to Reveal Severity Value</label>
                            <div class="col-sm-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon">Minimum</span>
                                    <input type="text" name="breed_characteristic_severity[{{ $breedCharacteristicSeverity->id }}][minimum_age_to_reveal_value]" class="form-control" value="{{{ $breedCharacteristicSeverity->min_age_to_reveal_value }}}" maxlength="5" />
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon">Maximum</span>
                                    <input type="text" name="breed_characteristic_severity[{{ $breedCharacteristicSeverity->id }}][maximum_age_to_reveal_value]" class="form-control" value="{{{ $breedCharacteristicSeverity->max_age_to_reveal_value }}}" maxlength="5" />
                                </div>
                            </div>
                        </div>
                        @endif

                        @foreach($breedCharacteristicSeverity->symptoms() as $breedCharacteristicSeveritySymptom)
                        <div class="well well-sm">
                            <h5>
                                <strong>{{ $breedCharacteristicSeveritySymptom->name }}</strong>

                                <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#breed_characteristic_health_severity_health_symptom_{{ $breedCharacteristicSeveritySymptom->id }}">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </h5>

                            <section id="breed_characteristic_health_severity_health_symptom_{{ $breedCharacteristicSeveritySymptom->id }}" class="collapse">
                                <div class="form-group">
                                    <label for="characteristicHealthSeverityHealthSymptom{{ $breedCharacteristicSeveritySymptom->id }}OffsetOnsetAge" class="col-sm-2 control-label">Offset Age to Express</label>
                                    <div class="col-sm-5">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-addon">Minimum</span>
                                            <input type="text" name="breed_characteristic_severity_symptom[{{ $breedCharacteristicSeveritySymptom->id }}][minimum_offset_age_to_express]" class="form-control" id="characteristicHealthSeverityHealthSymptom{{ $breedCharacteristicSeveritySymptom->id }}OffsetOnsetAge" value="{{{ $breedCharacteristicSeveritySymptom->min_offset_age_to_express }}}" maxlength="5" required />
                                        </div>
                                    </div>

                                    <div class="col-sm-5">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-addon">Maximum</span>
                                            <input type="text" name="breed_characteristic_severity_symptom[{{ $breedCharacteristicSeveritySymptom->id }}][maximum_offset_age_to_express]" class="form-control" value="{{{ $breedCharacteristicSeveritySymptom->max_offset_age_to_express }}}" maxlength="5" required />
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="characteristicHealthSeverityHealthSymptom{{ $breedCharacteristicSeveritySymptom->id }}Lethal" class="col-sm-2 control-label">Lethal?</label>
                                    <div class="col-sm-7">
                                        <div class="checkbox">
                                            <label for="characteristicHealthSeverityHealthSymptom{{ $breedCharacteristicSeveritySymptom->id }}Lethal">
                                                <input type="checkbox" name="breed_characteristic_severity_symptom[{{ $breedCharacteristicSeveritySymptom->id }}][lethal]" value="yes" id="characteristicHealthSeverityHealthSymptom{{ $breedCharacteristicSeveritySymptom->id }}Lethal" {{ $breedCharacteristicSeveritySymptom->lethal ? 'checked' : '' }} />
                                                Yes
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
                @endif

                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2 text-right">
                        <a href="{{ route('admin/breeds/breed/characteristic/delete', $breedCharacteristic->id) }}" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this breed characteristi?');">Delete</a>
                        <button type="submit" name="edit_breed_characteristic" value="edit_breed_characteristic" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>

@stop

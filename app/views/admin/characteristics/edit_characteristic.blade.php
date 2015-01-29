@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Characteristic</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/characteristic/edit', $characteristic->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-characteristic-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $characteristic->id }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristic-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-characteristic-name" value="{{{ Input::old('name', $characteristic->name) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristic-description" class="col-sm-2 control-label">Description</label>
        <div class="col-sm-10">
            <textarea name="description" class="form-control" id="cp-characteristic-description" rows="3" required>{{{ Input::old('description', $characteristic->description) }}}</textarea>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristic-helppage" class="col-sm-2 control-label">Help Page</label>
        <div class="col-sm-10">
            <select name="help_page" class="form-control">
                <option value="">None</option>
                @foreach($helpPages as $helpPage)
                    <option value="{{ $helpPage->id }}" {{ (Input::old('help_page', $characteristic->help_page_id) == $helpPage->id) ? 'selected' : '' }}>{{ $helpPage->title }}</option>
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
                    <option value="{{ $characteristicCategory->id }}" {{ (Input::old('category', $characteristic->category_id) == $characteristicCategory->id) ? 'selected' : '' }}>
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
                            <input type="checkbox" name="active" value="yes" id="cp-characteristic-active" {{ (Input::old('active', ($characteristic->active ? 'yes' : '')) == 'yes') ? 'checked' : '' }}/> Yes
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
                            <input type="checkbox" name="hide" value="yes" id="cp-characteristic-hide" {{ (Input::old('hide', ($characteristic->hide ? 'yes' : '')) == 'yes') ? 'checked' : '' }}/> Yes
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
                            <input type="checkbox" name="ignorable" value="yes" id="cp-characteristic-ignorable" {{ (Input::old('ignorable', ($characteristic->ignorable ? 'yes' : '')) == 'yes') ? 'checked' : '' }}/> Yes
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
                            <input type="checkbox" name="hide_genotypes" value="yes" id="cp-characteristic-hidegenotypes"{{ (Input::old('hide_genotypes', ($characteristic->hide_genotypes ? 'yes' : '')) == 'yes') ? 'checked' : '' }} /> Yes
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/characteristics/characteristic/delete', $characteristic->id) }}" name="delete_characteristic" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this characteristic?');">Delete</a>
            <button type="submit" name="edit_characteristic" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

<hr />

<h2>
    @if($characteristic->isRanged())
    <i class="fa fa-check text-success"></i>
    @else
    <i class="fa fa-times text-danger"></i>
    @endif

    Range Profile

    <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#characteristic_range">
        <i class="fa fa-plus"></i>
    </button>
</h2>

<div id="characteristic_range" class="collapse {{ (Input::old('edit_characteristic_range') or Input::old('create_characteristic_range_label')) ? 'in' : '' }}">
    <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/characteristic/range/update', $characteristic->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <div class="form-group">
            <label for="characteristicRangeValue" class="col-sm-2 control-label">Value</label>
            <div class="col-sm-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Minimum</span>
                    <input type="text" name="minimum_ranged_value" class="form-control" id="characteristicRangeValue" value="{{{ Input::old('minimum_ranged_value', $characteristic->min_ranged_value) }}}" maxlength="10" placeholder="None" required />
                </div>
            </div>
            <div class="col-sm-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Maximum</span>
                    <input type="text" name="maximum_ranged_value" class="form-control" value="{{{ Input::old('maximum_ranged_value', $characteristic->max_ranged_value) }}}" maxlength="10" placeholder="None" required />
                </div>
            </div>
            <div class="col-sm-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Precision</span>
                    <input type="text" name="ranged_value_precision" class="form-control" value="{{{ Input::old('ranged_value_precision', $characteristic->ranged_value_precision) }}}" placeholder="None" maxlength="1" placeholder="0-2" required />
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="characteristicRangeValueCanBeKnown" class="col-sm-2 control-label">Value Can Be Revealed?</label>
            <div class="col-sm-2">
                <div class="checkbox">
                    <label for="characteristicRangeValueCanBeKnown">
                        <input type="checkbox" name="ranged_value_can_be_revealed" value="yes" id="characteristicRangeValueCanBeKnown" {{ (Input::old('ranged_value_can_be_revealed', ($characteristic->ranged_value_can_be_revealed ? 'yes' : 'no')) == 'yes' ? 'checked' : '') }}/>
                        Yes
                    </label>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Minimum Age</span>
                    <input type="text" name="minimum_age_to_reveal_ranged_value" class="form-control" value="{{{ Input::old('minimum_age_to_reveal_ranged_value', $characteristic->min_age_to_reveal_ranged_value) }}}" maxlength="5" placeholder="None" />
                </div>
            </div>
            <div class="col-sm-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Maximum Age</span>
                    <input type="text" name="maximum_age_to_reveal_ranged_value" class="form-control" value="{{{ Input::old('minimum_age_to_reveal_ranged_value', $characteristic->max_age_to_reveal_ranged_value) }}}" maxlength="5" placeholder="None" />
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="characteristicRangeBoundaryLabel" class="col-sm-2 control-label">Boundary Labels</label>
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Lower</span>
                    <input type="text" name="ranged_lower_boundary_label" class="form-control" id="characteristicRangeBoundaryLabel" value="{{{ Input::old('ranged_lower_boundary_label', $characteristic->ranged_lower_boundary_label) }}}" maxlength="32" placeholder="None" />
                </div>
            </div>
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Upper</span>
                    <input type="text" name="ranged_upper_boundary_label" class="form-control" value="{{{ Input::old('ranged_upper_boundary_label', $characteristic->ranged_upper_boundary_label) }}}" maxlength="32" placeholder="None" />
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="characteristicRangeUnits" class="col-sm-2 control-label">Units</label>
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Prefix</span>
                    <input type="text" name="ranged_prefix_units" class="form-control" id="characteristicRangeUnits" value="{{{ Input::old('ranged_prefix_units', $characteristic->ranged_prefix_units) }}}" maxlength="16" placeholder="None" />
                </div>
            </div>
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Suffix</span>
                    <input type="text" name="ranged_suffix_units" class="form-control" value="{{{ Input::old('ranged_suffix_units', $characteristic->ranged_suffix_units) }}}" maxlength="16" placeholder="None" />
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="characteristicRangeGrowth" class="col-sm-2 control-label">Growth?</label>
            <div class="col-sm-2">
                <div class="checkbox">
                    <label for="characteristicRangeGrowth">
                        <input type="checkbox" name="growth" value="yes" id="characteristicRangeGrowth" {{ (Input::old('growth', ($characteristic->ranged_value_can_grow ? 'yes' : 'no')) == 'yes' ? 'checked' : '') }}/>
                        Yes
                    </label>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Minimum Age</span>
                    <input type="text" name="minimum_age_to_stop_growing" class="form-control" value="{{{ Input::old('minimum_age_to_stop_growing', $characteristic->min_age_to_stop_growing) }}}" maxlength="5" placeholder="None" />
                </div>
            </div>
            <div class="col-sm-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Maximum Age</span>
                    <input type="text" name="maximum_age_to_stop_growing" class="form-control" value="{{{ Input::old('maximum_age_to_stop_growing', $characteristic->max_age_to_stop_growing) }}}" maxlength="5" placeholder="None" />
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2 text-right">
                @if($characteristic->isRanged())
                <a class="btn btn-danger" href="{{ route('admin/characteristics/characteristic/range/remove', $characteristic->id) }}" onclick="return confirm('Are you sure you want to remove the range settings?');">Remove</a>
                @endif
                <button type="submit" name="edit_characteristic_range" value="edit_characteristic_range" class="btn btn-primary">Save</button>
            </div>
        </div>
    </form>

    <h3>Labels</h3>

    <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/characteristic/label/add', $characteristic->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <div class="form-group">
            <label for="characteristicRangeLabelName" class="col-sm-2 control-label">Name</label>
            <div class="col-sm-10">
                <input type="text" name="label_name" class="form-control" id="characteristicRangeLabelName" value="{{{ Input::old('label_name') }}}" maxlength="32" required />
            </div>
        </div>
        <div class="form-group">
            <label for="characteristicRangeLabelValue" class="col-sm-2 control-label">Value</label>
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Minimum</span>
                    <input type="text" name="minimum_ranged_label_value" class="form-control" id="characteristicRangeLabelValue" value="{{{ Input::old('minimum_ranged_label_value') }}}" maxlength="10" required />
                </div>
            </div>
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Maximum</span>
                    <input type="text" name="maximum_ranged_label_value" class="form-control" value="{{{ Input::old('maximum_ranged_label_value') }}}" maxlength="10" required />
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2 text-right">
                <button type="submit" name="create_characteristic_range_label" value="create_characteristic_range_label" class="btn btn-primary">Create</button>
            </div>
        </div>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Min Value</th>
                <th>Max Value</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($labels as $label)
            <tr>
                <td>{{ $label->name }}</td>
                <td>{{ $label->min_ranged_value }}</td>
                <td>{{ $label->max_ranged_value }}</td>
                <td>
                    <a class="btn btn-danger btn-xs" href="{{ route('admin/characteristics/characteristic/label/delete', $label->id) }}" onclick="return confirm('Are you sure you want to delete this characteristic range label?');">Delete</a>
                </td>
            </tr>
            @endforeach

            @if($labels->isEmpty())
            <tr>
                <td colspan="4">No labels to display.</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

<hr />

<h2>
    @if($characteristic->isGenetic())
    <i class="fa fa-check text-success"></i>
    @else
    <i class="fa fa-times text-danger"></i>
    @endif

    Genetic Profile

    <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#characteristic_genetic">
        <i class="fa fa-plus"></i>
    </button>
</h2>

<div id="characteristic_genetic" class="collapse {{ Input::old('edit_characteristic_genetic') ? 'in' : '' }}">
    <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/characteristic/genetics/update', $characteristic->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <div class="form-group">
            <label for="characteristicGeneticPhenotypesCanBeKnown" class="col-sm-2 control-label">Phenotypes Can Be Revealed?</label>
            <div class="col-sm-2">
                <div class="checkbox">
                    <label for="characteristicGeneticPhenotypesCanBeKnown">
                        <input type="checkbox" name="phenotypes_can_be_revealed" value="yes" id="characteristicGeneticPhenotypesCanBeKnown" {{ Input::old('phenotypes_can_be_revealed', ($characteristic->phenotypes_can_be_revealed ? 'yes' : 'no')) == 'yes' ? 'checked' : '' }}/>
                        Yes
                    </label>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Minimum Age</span>
                    <input type="text" name="minimum_age_to_reveal_phenotypes" class="form-control" value="{{{ Input::old('minimum_age_to_reveal_phenotypes', $characteristic->min_age_to_reveal_phenotypes) }}}" maxlength="5" placeholder="None" />
                </div>
            </div>
            <div class="col-sm-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Maximum Age</span>
                    <input type="text" name="maximum_age_to_reveal_phenotypes" class="form-control" value="{{{ Input::old('maximum_age_to_reveal_phenotypes', $characteristic->max_age_to_reveal_phenotypes) }}}" maxlength="5" placeholder="None" />
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="characteristicGeneticGenotypesCanBeKnown" class="col-sm-2 control-label">Genotypes Can Be Revealed?</label>
            <div class="col-sm-2">
                <div class="checkbox">
                    <label for="characteristicGeneticGenotypesCanBeKnown">
                        <input type="checkbox" name="genotypes_can_be_revealed" value="yes" id="characteristicGeneticGenotypesCanBeKnown" {{ Input::old('genotypes_can_be_revealed', ($characteristic->genotypes_can_be_revealed ? 'yes' : 'no')) == 'yes' ? 'checked' : '' }}/>
                        Yes
                    </label>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Minimum Age</span>
                    <input type="text" name="minimum_age_to_reveal_genotypes" class="form-control" value="{{{ Input::old('minimum_age_to_reveal_genotypes', $characteristic->min_age_to_reveal_genotypes) }}}" maxlength="5" placeholder="None" />
                </div>
            </div>
            <div class="col-sm-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Maximum Age</span>
                    <input type="text" name="maximum_age_to_reveal_genotypes" class="form-control" value="{{{ Input::old('maximum_age_to_reveal_genotypes', $characteristic->max_age_to_reveal_genotypes) }}}" maxlength="5" placeholder="None" />
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="characteristicGeneticLoci" class="col-sm-2 control-label">Loci</label>
            <div class="col-sm-10">
                <div class="row">
                    @foreach($loci as $locus)
                    <div class="col-sm-6">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="loci[]" value="{{ $locus->id }}" {{ in_array($locus->id, (array) Input::old('loci', $characteristic->loci->lists('id'))) ? 'checked' : '' }}> {{ $locus->name }}
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2 text-right">
                <a class="btn btn-danger" href="{{ route('admin/characteristics/characteristic/genetics/remove', $characteristic->id) }}" onclick="return confirm('Are you sure you want to remove the genetics settings?');">Remove</a>
                <button type="submit" name="edit_characteristic_genetic" value="edit_characteristic_genetic" class="btn btn-primary">Save</button>
            </div>
        </div>
    </form>
</div>

<hr />

<h2>
    @if($characteristic->hasHealthGenotypes() or $characteristic->hasSeverities())
    <i class="fa fa-check text-success"></i>
    @else
    <i class="fa fa-times text-danger"></i>
    @endif

    Health Profile

    <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#characteristic_health">
        <i class="fa fa-plus"></i>
    </button>
</h2>

<div id="characteristic_health" class="collapse {{ (Input::old('edit_characteristic_health') or Input::old('create_characteristic_health_severity')) ? 'in' : '' }}">
    <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/characteristic/health/update', $characteristic->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <div class="form-group">
            <label for="characteristicHealthGenotypes" class="col-sm-2 control-label">Genotypes</label>
            <div class="col-sm-10">
                <div class="row">
                    @foreach($loci as $locus)
                        <div class="col-sm-4">
                            <h5><strong>{{ $locus->name }}</strong></h5>
                            @foreach($locus->genotypes as $genotype)
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="health_genotypes[]" value="{{ $genotype->id }}" {{ in_array($genotype->id, (array)Input::old('health_genotypes', $characteristic->genotypes->lists('id'))) ? 'checked' : '' }} /> {{ $genotype->toSymbol() }}
                                </label>
                            </div>
                            @endforeach
                            <hr />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2 text-right">
                <a class="btn btn-danger" href="{{ route('admin/characteristics/characteristic/health/remove', $characteristic->id) }}" onclick="return confirm('Are you sure you want to remove the health settings?');">Remove</a>
                <button type="submit" name="edit_characteristic_health" value="edit_characteristic_health" class="btn btn-primary">Save</button>
            </div>
        </div>
    </form>

    <h3>
        @if($characteristic->hasSeverities())
        <i class="fa fa-check text-success"></i>
        @else
        <i class="fa fa-times text-danger"></i>
        @endif

        Severities
    </h3>

    <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/characteristic/severity/add', $characteristic->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />

        <div class="form-group">
            <label for="characteristicHealthSeverityValue" class="col-sm-2 control-label">Value</label>
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Minimum</span>
                    <input type="text" name="minimum_severity_value" class="form-control" id="characteristicHealthSeverityValue" value="{{{ Input::old('minimum_severity_value') }}}" maxlength="5" required />
                </div>
            </div>
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Maximum</span>
                    <input type="text" name="maximum_severity_value" class="form-control" value="{{{ Input::old('maximum_severity_value') }}}" maxlength="5" required />
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="characteristicHealthSeverityCanBeExpressed" class="col-sm-2 control-label">Can Be Expressed?</label>
            <div class="col-sm-2">
                <div class="checkbox">
                    <label for="characteristicHealthSeverityCanBeExpressed">
                        <input type="checkbox" name="severity_can_be_expressed" value="yes" id="characteristicHealthSeverityCanBeExpressed" {{ Input::old('severity_value_can_be_revealed') == 'yes' ? 'checked' : '' }}/>
                        Yes
                    </label>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Minimum Age</span>
                    <input type="text" name="minimum_age_to_express" class="form-control" value="{{{ Input::old('minimum_age_to_express') }}}" maxlength="5" placeholder="None" />
                </div>
            </div>
            <div class="col-sm-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Maximum Age</span>
                    <input type="text" name="maximum_age_to_express" class="form-control" value="{{{ Input::old('maximum_age_to_express') }}}" maxlength="5" placeholder="None" />
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="characteristicHealthSeverityValueCanBeKnown" class="col-sm-2 control-label">Value Can Be Revealed?</label>
            <div class="col-sm-2">
                <div class="checkbox">
                    <label for="characteristicHealthSeverityValueCanBeKnown">
                        <input type="checkbox" name="severity_value_can_be_revealed" value="yes" id="characteristicHealthSeverityValueCanBeKnown" {{ Input::old('severity_value_can_be_revealed') == 'yes' ? 'checked' : '' }}/>
                        Yes
                    </label>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Minimum Age</span>
                    <input type="text" name="minimum_age_to_reveal_severity_value" class="form-control" value="{{{ Input::old('minimum_age_to_reveal_severity_value') }}}" maxlength="5" placeholder="None" />
                </div>
            </div>
            <div class="col-sm-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Maximum Age</span>
                    <input type="text" name="maximum_age_to_reveal_severity_value" class="form-control" value="{{{ Input::old('maximum_age_to_reveal_severity_value') }}}" maxlength="5" placeholder="None" />
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="characteristicHealthSeverityUnits" class="col-sm-2 control-label">Units</label>
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Prefix</span>
                    <input type="text" name="severity_prefix_units" class="form-control" id="characteristicHealthSeverityUnits" value="{{{ Input::old('severity_prefix_units') }}}" maxlength="16" placeholder="None" />
                </div>
            </div>
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Suffix</span>
                    <input type="text" name="severity_suffix_units" class="form-control" value="{{{ Input::old('severity_suffix_units') }}}" maxlength="16" placeholder="None" />
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2 text-right">
                <button type="submit" name="create_characteristic_health_severity" value="create_characteristic_health_severity" class="btn btn-primary">Create</button>
            </div>
        </div>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Min Value</th>
                <th>Max Value</th>
                <th>Health Symptoms</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($characteristicSeverities as $characteristicSeverity)
            <tr>
                <td>{{ $characteristicSeverity->min_value }}</td>
                <td>{{ $characteristicSeverity->max_value }}</td>
                <td>
                    @foreach($characteristicSeverity->symptoms as $characteristicSeveritySymptom)
                    <big><a class="label label-{{ $characteristicSeveritySymptom->isLethal() ? 'danger' : 'warning' }} btn-xs" href="{{ route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id) }}">{{ $characteristicSeveritySymptom->symptom->name }}</a></big>
                    @endforeach

                    @if($characteristicSeverity->symptoms->isEmpty())
                    <em>None</em>
                    @endif
                </td>
                <td>
                    <a class="btn btn-default btn-xs" href="{{ route('admin/characteristics/characteristic/severity/edit', $characteristicSeverity->id) }}">Edit</a>
                </td>
            </tr>
            @endforeach

            @if($characteristicSeverities->isEmpty())
            <tr>
                <td colspan="4">No severities to display.</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

@stop

@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Characteristic Dependency</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/dependency/edit', $characteristicDependency->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-characteristicdependency-characteristic" class="col-sm-2 control-label">Dependent Characteristic</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                <a href="{{ route('admin/characteristics/characteristic/edit', $characteristicDependency->characteristic->id) }}">
                    {{ $characteristicDependency->characteristic->name }}
                </a>
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristicdependency-type" class="col-sm-2 control-label">Type</label>
        <div class="col-sm-10">
            <p class="form-control-static">{{ $characteristicDependency->getType() }}</p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristicdependency-active" class="col-sm-2 control-label">Active?</label>
        <div class="col-sm-7">
            <div class="checkbox">
                <label for="cp-characteristicdependency-active">
                    <input type="checkbox" name="active" value="yes" id="cp-characteristicdependency-active" {{ (Input::old('active', ($characteristicDependency->active ? 'yes' : 'no')) == 'yes') ? 'checked' : '' }} />
                    Yes
                </label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a class="btn btn-danger" href="{{ route('admin/characteristics/dependency/delete', $characteristicDependency->id) }}" onclick="return confirm('Are you sure you want to delete this characteristic dependency?');">Delete</a>
            <button type="submit" name="edit_characteristic_dependency" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

<hr />

<h2>
    @if($characteristicDependency->hasIndependentCharacteristics())
    <i class="fa fa-check text-success"></i>
    @else
    <i class="fa fa-times text-danger"></i>
    @endif

    Independent Characteristics
    
    <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#characteristic_dependency_independent_characteristic_characteristics">
        <i class="fa fa-plus"></i>
    </button>
</h2>

<div id="characteristic_dependency_independent_characteristic_characteristics" class="collapse {{ $characteristicDependency->hasIndependentCharacteristics() ? 'in' : '' }}">
    <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/dependendency/add_independents', $characteristicDependency->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />

        <div class="form-group">
            <label for="charDependencyIndependentCharacteristicIDs" class="col-sm-2 control-label">Independent Characteristics</label>

            <div class="col-sm-10">
                <select name="independent_characteristics[]" class="form-control" id="charDependencyIndependentCharacteristicIDs" size="10" multiple>
                    @if( ! $uncategorizedIndependentCharacteristics->isEmpty())
                    <optgroup label="Uncategorized">
                        @foreach($uncategorizedIndependentCharacteristics as $characteristic)
                        <option value="{{ $characteristic->id }}" {{ (in_array($characteristic->id, (array)Input::old('independent_characteristics'))) ? 'selected' : '' }}>{{ $characteristic->name }}</option>
                        @endforeach
                    </optgroup>
                    @endif

                    @foreach($independentCharacteristicCategories as $category)
                    <optgroup label="{{ $category->parent->name }}: {{ $category->name }}">
                        @foreach($category->characteristics as $characteristic)
                        <option value="{{ $characteristic->id }}" {{ (in_array($characteristic->id, (array)Input::old('independent_characteristics'))) ? 'selected' : '' }}>{{ $characteristic->name }}</option>
                        @endforeach
                    </optgroup>
                    @endforeach

                    @if($uncategorizedIndependentCharacteristics->isEmpty() and $independentCharacteristicCategories->isEmpty())
                    <option value="">No characteristics available</option>
                    @endif
                </select>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2 text-right">
                <button type="submit" name="add_independent_characteristic" class="btn btn-primary">Add</button>
            </div>
        </div>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($characteristicDependencyIndependentCharacteristics as $independentCharacteristic)
            <tr>
                <td><a href="{{ route('admin/characteristics/characteristic/edit', $independentCharacteristic->characteristic->id) }}">{{ $independentCharacteristic->characteristic->id }}</a></td>
                <td><a href="{{ route('admin/characteristics/characteristic/edit', $independentCharacteristic->characteristic->id) }}">{{ $independentCharacteristic->characteristic->name }}</a></td>
                <td class="text-right">
                    <a class="btn btn-danger btn-xs" href="{{ route('admin/characteristics/dependency/independent/remove', $independentCharacteristic->id) }}" onclick="return confirm('Are you sure you want to delete this independent characteristic?');">Remove</a>
                </td>
            </tr>
            @endforeach

            @if($characteristicDependencyIndependentCharacteristics->isEmpty())
            <tr>
                <td colspan="4">No independent characteristics to display.</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

<hr />

@if($characteristicDependency->isR2R())
    <h2>Range to Range</h2>

    @if($characteristicDependency->needsRangedPercents())
        <h3>Percentage of Range</h3>

        @foreach($characteristicDependencyIndependentCharacteristics as $independentCharacteristic)
        <h4>{{ $independentCharacteristic->characteristic->name }}</h4>

        <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/dependency/independent/update_percents', $independentCharacteristic->id) }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />

            <div class="form-group">
                <label for="r2rMinPercent" class="col-sm-2 control-label">Range</label>
                <div class="col-sm-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon">Minimum Percent</span>
                        <input type="text" name="minimum_percent" class="form-control" value="{{ $independentCharacteristic->min_percent }}" maxlength="5" placeholder="000.00" />
                        <span class="input-group-addon">%</span>
                    </div>
                </div>

                <div class="col-sm-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon">Maximum Percent</span>
                        <input type="text" name="maximum_percent" class="form-control" value="{{ $independentCharacteristic->max_percent }}" maxlength="5" placeholder="000.00" />
                        <span class="input-group-addon">%</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="edit_characteristic_dependency_r2r_per" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
        @endforeach
    @endif
@endif

@if($characteristicDependency->isR2G())
    <h2>Range to Gene</h2>

    <h3>Groups</h3>

    <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/dependency/group/x2g/create', $characteristicDependency->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />

        <div class="form-group">
            <label for="r2gGroupIdentifier" class="col-sm-2 control-label">Identifier</label>
            <div class="col-sm-10">
                <input type="text" name="identifier" class="form-control" id="r2gGroupIdentifier" value="{{{ Input::old('identifier') }}}" maxlength="32" required />
            </div>
        </div>

        <div class="form-group">
            <label for="x2gGroupGenotypes" class="col-sm-2 control-label">Genotypes</label>
            <div class="col-sm-10">
                <div class="row">
                    @foreach($dependentCharacteristicLoci as $locus)
                        <div class="col-sm-4">
                            <h5><strong>{{ $locus->name }}</strong></h5>
                            @foreach($locus->genotypes as $genotype)
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="genotypes[]" value="{{ $genotype->id }}" {{ in_array($genotype->id, (array)Input::old('genotypes')) ? 'selected' : '' }}>
                                    {{ $genotype->toSymbol() }}
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
                <button type="submit" name="create_characteristic_dependency_r2g_group" class="btn btn-primary">Create</button>
            </div>
        </div>
    </form>

    @foreach($characteristicDependencyGroups as $characteristicDependencyGroup)
    <div class="well well-sm">
        <h4>
            {{{ $characteristicDependencyGroup->identifier }}}
            <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#characteristic_dependency_group{{ $characteristicDependencyGroup->id }}">
                <i class="fa fa-plus"></i>
            </button>
        </h4>

        <div id="characteristic_dependency_group{{ $characteristicDependencyGroup->id }}" class="collapse">
            <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/dependency/group/x2g/update', $characteristicDependencyGroup->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                <div class="form-group">
                    <label for="g2rGroup{{ $characteristicDependencyGroup->id }}Identifier" class="col-sm-2 control-label">Identifier</label>
                    <div class="col-sm-10">
                        <input type="text" name="identifier" class="form-control" id="g2rIdentifier" value="{{{ $characteristicDependencyGroup->identifier }}}" maxlength="32" required />
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2 text-right">
                        <a class="btn btn-danger" href="{{ route('admin/characteristics/dependency/group/delete', $characteristicDependencyGroup->id) }}" onclick="return confirm('Are you sure you want to delete this characteristic dependency group?');">Delete</a>
                        <button type="submit" name="edit_characteristic_dependency_x2r_group" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>

            <hr />

            @if($characteristicDependency->hasIndependentCharacteristics())
            <h4>Ranges</h4>

            <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/dependency/group/independent/range/add', $characteristicDependencyGroup->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                <div class="form-group">
                    <label for="r2gGroup{{ $characteristicDependencyGroup->id }}IndependentCharacteristicID" class="col-sm-2 control-label">Independent Characteristics</label>
                    <div class="col-sm-10">
                        <select name="independent_characteristic" class="form-control" id="r2gGroup{{ $characteristicDependencyGroup->id }}IndependentCharacteristicID">
                            @foreach($characteristicDependencyIndependentCharacteristics as $independentCharacteristic)
                            <option value="{{ $independentCharacteristic->id }}">{{ $independentCharacteristic->characteristic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="r2gGroup{{ $characteristicDependencyGroup->id }}MinValue" class="col-sm-2 control-label">Range</label>
                    <div class="col-sm-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Minimum Value</span>
                            <input type="text" name="minimum_value" class="form-control" maxlength="10" placeholder="0.00" />
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Maximum Value</span>
                            <input type="text" name="maximum_value" class="form-control" maxlength="10" placeholder="0.00" />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2 text-right">
                        <button type="submit" name="create_characteristic_dependency_r2g_group_indep_range" class="btn btn-primary">Add</button>
                    </div>
                </div>
            </form>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Independent Characteristic</th>
                        <th>Minimum Value</th>
                        <th>Maximum Value</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($characteristicDependencyGroup->independentCharacteristicRanges as $independentCharacteristicRange)
                    <tr>
                        <td><a href="{{ route('admin/characteristics/characteristic/edit', $independentCharacteristicRange->characteristicDependencyIndependentCharacteristic->characteristic->id) }}">{{ $independentCharacteristicRange->characteristicDependencyIndependentCharacteristic->characteristic->name }}</a></td>
                        <td>{{ $independentCharacteristicRange->min_value }}</td>
                        <td>{{ $independentCharacteristicRange->max_value }}</td>
                        <td class="text-right">
                            <a class="btn btn-danger btn-xs" href="{{ route('admin/characteristics/dependency/group/independent/range/remove', $independentCharacteristicRange->id) }}" onclick="return confirm('Are you sure you want to remove this range from this group?');">Remove</a>
                        </td>
                    </tr>
                    @endforeach

                    @if($characteristicDependencyGroup->independentCharacteristicRanges->isEmpty())
                    <tr>
                        <td colspan="4">No ranges to display.</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <hr />
            @endif

            <h4>Results</h4>

            <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/dependency/group/genotypes/add', $characteristicDependencyGroup->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                <div class="form-group">
                    <label for="x2gGroup{{ $characteristicDependencyGroup->id }}Genotypes" class="col-sm-2 control-label">Genotypes</label>
                    <div class="col-sm-10">
                        <div class="row">
                            @foreach($dependentCharacteristicLoci as $locus)
                                <div class="col-sm-4">
                                    <h5><strong>{{ $locus->name }}</strong></h5>
                                    @foreach($locus->genotypes as $genotype)
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="genotypes[]" value="{{ $genotype->id }}" {{ in_array($genotype->id, $characteristicDependencyGroup->genotypes()->lists('id')) ? 'checked' : '' }}> {{ $genotype->toSymbol() }}
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
                        <button type="submit" name="edit_characteristic_dependency_x2g_group_genotypes" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endforeach
@endif

@if($characteristicDependency->isG2G())
    <h2>Gene to Gene</h2>

    <h3>Groups</h3>

    <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/dependency/group/x2g/create', $characteristicDependency->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />

        <div class="form-group">
            <label for="x2gGroupIdentifier" class="col-sm-2 control-label">Identifier</label>
            <div class="col-sm-10">
                <input type="text" name="identifier" class="form-control" id="x2gGroupIdentifier" value="{{{ Input::old('identifier') }}}" maxlength="32" required />
            </div>
        </div>

        <div class="form-group">
            <label for="x2gGroupGenotypes" class="col-sm-2 control-label">Genotypes</label>
            <div class="col-sm-10">
                <div class="row">
                    @foreach($dependentCharacteristicLoci as $locus)
                        <div class="col-sm-4">
                            <h5><strong>{{ $locus->name }}</strong></h5>
                            @foreach($locus->genotypes as $genotype)
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="genotypes[]" value="{{ $genotype->id }}">
                                    {{ $genotype->toSymbol() }}
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
                <button type="submit" name="create_characteristic_dependency_x2g_group" class="btn btn-primary">Create</button>
            </div>
        </div>
    </form>

    @foreach($characteristicDependencyGroups as $characteristicDependencyGroup)
    <div class="well well-sm">
        <h4>
            {{ $characteristicDependencyGroup->identifier }}
            <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#characteristic_dependency_group{{ $characteristicDependencyGroup->id }}">
                <i class="fa fa-plus"></i>
            </button>
        </h4>

        <div id="characteristic_dependency_group{{ $characteristicDependencyGroup->id }}" class="collapse">
            <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/dependency/group/g2x/update', $characteristicDependencyGroup->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                <div class="form-group">
                    <label for="g2gGroup{{ $characteristicDependencyGroup->id }}Identifier" class="col-sm-2 control-label">Identifier</label>
                    <div class="col-sm-10">
                        <input type="text" name="identifier" class="form-control" id="g2gGroup{{ $characteristicDependencyGroup->id }}Identifier" value="{{ $characteristicDependencyGroup->identifier }}" maxlength="32" required />
                    </div>
                </div>

                @if($characteristicDependency->hasIndependentCharacteristics())
                <div class="form-group">
                    <label for="g2gGroup{{ $characteristicDependencyGroup->id }}Genotypes" class="col-sm-2 control-label">Genotypes</label>
                    <div class="col-sm-10">
                        <div class="row">
                            @foreach($independentCharacteristicLoci as $locus)
                                <div class="col-sm-4">
                                    <h5><strong>{{ $locus->name }}</strong></h5>
                                    @foreach($locus->genotypes as $genotype)
                                    <div class="checkbox">
                                        <label>

                                            <input type="checkbox" name="genotypes[]" value="{{ $genotype->id }}" {{ in_array($genotype->id, $characteristicDependencyGroup->independentCharacteristicGenotypes()->lists('genotype_id')) ? 'checked' : '' }}> {{ $genotype->toSymbol() }}
                                        </label>
                                    </div>
                                    @endforeach
                                    <hr />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2 text-right">
                        <a class="btn btn-danger" href="{{ route('admin/characteristics/dependency/group/delete', $characteristicDependencyGroup->id) }}" onclick="return confirm('Are you sure you want to delete this characteristic dependency group?');">Delete</a>
                        <button type="submit" name="edit_characteristic_dependency_g2x_group" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>

            <h4>Results</h4>

            <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/dependency/group/x2g/update') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                <div class="form-group">
                    <label for="x2gGroup{{ $characteristicDependencyGroup->id }}Genotypes" class="col-sm-2 control-label">Genotypes</label>
                    <div class="col-sm-10">
                        <div class="row">
                            @foreach($dependentCharacteristicLoci as $locus)
                                <div class="col-sm-4">
                                    <h5><strong>{{ $locus->name }}</strong></h5>
                                    @foreach($locus->genotypes as $genotype)
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="genotypes[]" value="{{ $genotype->id }}" {{ in_array($genotype->id, $characteristicDependencyGroup->genotypes()->lists('id')) ? 'checked' : '' }}> {{ $genotype->toSymbol() }}
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
                        <button type="submit" name="edit_characteristic_dependency_x2g_group_genotypes" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endforeach
@endif

@if($characteristicDependency->isG2R())
    <h2>Gene to Range</h2>

    <h3>Groups</h3>

    <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/dependency/group/g2r/create', $characteristicDependency->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />

        <div class="form-group">
            <label for="g2rGroupIdentifier" class="col-sm-2 control-label">Identifier</label>
            <div class="col-sm-10">
                <input type="text" name="identifier" class="form-control" id="g2rGroupIdentifier" value="{{{ Input::old('identifier') }}}" maxlength="32" required />
            </div>
        </div>

        <div class="form-group">
            <label for="g2rGroupMinValue" class="col-sm-2 control-label">Range</label>
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Minimum Value</span>
                    <input type="text" name="minimum_value" class="form-control" value="{{{ Input::old('minimum_value') }}}" maxlength="10" placeholder="0.00" />
                </div>
            </div>
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Maximum Value</span>
                    <input type="text" name="maximum_value" class="form-control" value="{{{ Input::old('maximum_value') }}}" maxlength="10" placeholder="0.00" />
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2 text-right">
                <button type="submit" name="create_characteristic_dependency_g2r_group" class="btn btn-primary">Create</button>
            </div>
        </div>
    </form>

    @foreach($characteristicDependencyGroups as $characteristicDependencyGroup)
    <div class="well well-sm">
        <h4>
            {{ $characteristicDependencyGroup->identifier }}
            <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#characteristic_dependency_group{{ $characteristicDependencyGroup->id }}">
                <i class="fa fa-plus"></i>
            </button>
        </h4>

        <div id="characteristic_dependency_group{{ $characteristicDependencyGroup->id }}" class="collapse">
            <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/dependency/group/g2x/update', $characteristicDependencyGroup->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                <div class="form-group">
                    <label for="g2rGroup{{ $characteristicDependencyGroup->id }}Identifier" class="col-sm-2 control-label">Identifier</label>
                    <div class="col-sm-10">
                        <input type="text" name="identifier" class="form-control" id="g2rIdentifier" value="{{{ $characteristicDependencyGroup->identifier }}}" maxlength="32" required />
                    </div>
                </div>

                @if($characteristicDependency->hasIndependentCharacteristics())
                <div class="form-group">
                    <label for="g2rGroup{{ $characteristicDependencyGroup->id }}Genotypes" class="col-sm-2 control-label">Genotypes</label>
                    <div class="col-sm-10">
                        <div class="row">
                            @foreach($independentCharacteristicLoci as $locus)
                                <div class="col-sm-4">
                                    <h5><strong>{{ $locus->name }}</strong></h5>
                                    @foreach($locus->genotypes as $genotype)
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="genotypes[]" value="{{ $genotype->id }}" {{ in_array($genotype->id, $characteristicDependencyGroup->independentCharacteristicGenotypes()->lists('genotype_id')) ? 'checked' : '' }}> {{ $genotype->toSymbol() }}
                                        </label>
                                    </div>
                                    @endforeach
                                    <hr />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2 text-right">
                        <a class="btn btn-danger" href="{{ route('admin/characteristics/dependency/group/delete', $characteristicDependencyGroup->id) }}" onclick="return confirm('Are you sure you want to delete this characteristic dependency group?');">Delete</a>
                        <button type="submit" name="edit_characteristic_dependency_g2x_group" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>

            <hr />

            <h4>Results</h4>

            <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/dependency/group/range/add', $characteristicDependencyGroup->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <div class="form-group">
                    <label for="g2rGroup{{ $characteristicDependencyGroup->id }}MinValue" class="col-sm-2 control-label">Range</label>
                    <div class="col-sm-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Minimum Value</span>
                            <input type="text" name="minimum_value" class="form-control" maxlength="10" placeholder="0.00" />
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Maximum Value</span>
                            <input type="text" name="maximum_value" class="form-control" maxlength="10" placeholder="0.00" />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2 text-right">
                        <button type="submit" name="create_characteristic_dependency_g2r_group_range" class="btn btn-primary">Add</button>
                    </div>
                </div>
            </form>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Minimum Value</th>
                        <th>Maximum Value</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($characteristicDependencyGroup->ranges as $characteristicDependencyGroupRange)
                    <tr>
                        <td>{{ $characteristicDependencyGroupRange->min_value }}</td>
                        <td>{{ $characteristicDependencyGroupRange->max_value }}</td>
                        <td class="text-right">
                            <a class="btn btn-danger btn-xs" href="{{ route('admin/characteristics/dependency/group/range/remove', $characteristicDependencyGroupRange->id) }}" onclick="return confirm(''Are you sure you want to remove this range from this group?');">Remove</a>
                        </td>
                    </tr>
                    @endforeach

                    @if($characteristicDependencyGroup->ranges->isEmpty())
                    <tr>
                        <td colspan="3">No ranges to display.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
@endif




@stop

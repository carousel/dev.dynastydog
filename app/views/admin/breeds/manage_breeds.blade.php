@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Manage Breeds</h1>
</div>

<h2>Add Characteristics</h2>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/breeds/manage/add_characteristics') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="breedBreedId" class="col-sm-2 control-label">Breeds</label>
        <div class="col-sm-10">
            <select name="breeds[]" class="form-control" id="breedBreedId" size="8" multiple required>
                @foreach($breeds as $breed)
                <option value="{{ $breed->id }}" {{ in_array($breed->id, (array)Input::old('breeds')) ? 'selected' : '' }}>{{ $breed->name }}</option>
                @endforeach

                @if($breeds->isEmpty())
                <option value="">No breeds available</option>
                @endif
            </select>
        </div>
    </div>

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
            <button type="submit" name="add_characteristics_to_breeds" value="add_characteristics_to_breeds" class="btn btn-primary">Add Characteristics</button>
        </div>
    </div>
</form>

<h2>Update Genotypes</h2>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/breeds/manage/add_genotypes') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="breedBreedId" class="col-sm-2 control-label">Breeds</label>
        <div class="col-sm-10">
            <select name="breeds[]" class="form-control" id="breedBreedId" size="8" multiple required>
                @foreach($breeds as $breed)
                <option value="{{ $breed->id }}" {{ in_array($breed->id, (array)Input::old('breeds')) ? 'selected' : '' }}>{{ $breed->name }}</option>
                @endforeach

                @if($breeds->isEmpty())
                <option value="">No breeds available</option>
                @endif
            </select>
        </div>
    </div>

    <div class="row">
        @foreach($loci as $locus)
        <div class="form-group col-sm-6">
            <div class="container-fluid">
                <div class="row">
                    <label for="breedGenotypes{{ $locus->id }}" class="col-sm-4 control-label">
                        {{ $locus->name }}
                    </label>

                    <div class="col-sm-8">
                        <div class="row">
                            @foreach($locus->genotypes as $genotype)
                            <div class="col-sm-4">
                                <p class="form-control-static">
                                    <a href="{{ route('admin/genetics/genotype/edit', $genotype->id) }}">{{ $genotype->toSymbol() }}</a>
                                </p>
                            </div>
                            <div class="col-sm-8">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon" title="Frequency"><i class="fa fa-bar-chart-o"></i></span>
                                    <input type="text" name="genotypes[{{ $genotype->id }}][frequency]" class="form-control" id="breedGenotypeFrequency{{ $genotype->id }}" value="0" maxlength="3">
                                    <span class="input-group-addon" title="Ignore">
                                        <small>
                                            <input type="checkbox" name="genotypes[{{ $genotype->id }}][ignore]" value="yes" id="cp-breeds-breed-manage-genotypes-ignore-{{ $genotype->id }}" checked>
                                            <strong>Ignore</strong>
                                        </small>
                                    </span>
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
            <button type="submit" name="add_genotypes_to_breeds" value="add_genotypes_to_breeds" class="btn btn-primary">Update Genotypes</button>
        </div>
    </div>
</form>

@stop

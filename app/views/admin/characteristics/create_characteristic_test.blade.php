@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New Characteristic Test</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/test/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-characteristictest-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-characteristictest-name" value="{{{ Input::old('name') }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristictest-characteristic" class="col-sm-2 control-label">Characteristic</label>
        <div class="col-sm-10">
            <select name="characteristic" class="form-control" id="cp-characteristictest-characteristic">
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
        <label for="cp-characteristictest-type" class="col-sm-2 control-label">Type</label>
        <div class="col-sm-10">
            <select name="type" class="form-control" id="cp-characteristictest-type">
                @foreach(CharacteristicTest::types() as $typeId => $type)
                <option value="{{ $typeId }}" {{ (Input::old('type') == $typeId) ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristictest-minage" class="col-sm-2 control-label">Availability</label>
        <div class="col-sm-5">
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Minimum Age</span>
                <input type="text" name="min_age" class="form-control" id="cp-characteristictest-minage" value="{{{ Input::old('min_age') }}}" maxlength="5" placeholder="None" />
            </div>
        </div>
        <div class="col-sm-5">
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Maximum Age</span>
                <input type="text" name="max_age" class="form-control" value="{{{ Input::old('max_age') }}}" maxlength="5" placeholder="None" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-characteristictest-active" class="col-sm-2 control-label">Active?</label>
        <div class="col-sm-7">
            <div class="checkbox">
                <label for="cp-characteristictest-active">
                    <input type="checkbox" name="active" value="yes" id="cp-characteristictest-active" {{ (Input::old('active') == 'yes') ? 'checked' : '' }} />
                    Yes
                </label>
            </div>
        </div>
    </div>

    <h4>Profiles</h4>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="cp-characteristictest-revealgenotypes" class="col-sm-7 control-label">Reveal Genotypes?</label>
                    <div class="col-sm-5">
                        <div class="checkbox">
                            <label for="cp-characteristictest-revealgenotypes">
                                <input type="checkbox" name="reveal_genotypes" value="yes" id="cp-characteristictest-revealgenotypes" {{ (Input::old('reveal_genotypes') == 'yes') ? 'checked' : '' }}/> Yes
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="cp-characteristictest-revealrangedvalue" class="col-sm-7 control-label">Reveal Ranged Value?</label>
                    <div class="col-sm-5">
                        <div class="checkbox">
                            <label for="cp-characteristictest-revealrangedvalue">
                                <input type="checkbox" name="reveal_ranged_value" value="yes" id="cp-characteristictest-revealrangedvalue" {{ (Input::old('reveal_ranged_value') == 'yes') ? 'checked' : '' }}/> Yes
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="cp-characteristictest-revealphenotypes" class="col-sm-7 control-label">Reveal Phenotypes?</label>
                    <div class="col-sm-5">
                        <div class="checkbox">
                            <label for="cp-characteristictest-revealphenotypes">
                                <input type="checkbox" name="reveal_phenotypes" value="yes" id="cp-characteristictest-revealphenotypes" {{ (Input::old('reveal_phenotypes') == 'yes') ? 'checked' : '' }}/> Yes
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="cp-characteristictest-revealseverityvalue" class="col-sm-7 control-label">Reveal Severity Value?</label>
                    <div class="col-sm-5">
                        <div class="checkbox">
                            <label for="cp-characteristictest-revealseverityvalue">
                                <input type="checkbox" name="reveal_severity_value" value="yes" id="cp-characteristictest-revealseverityvalue"{{ (Input::old('reveal_severity_value') == 'yes') ? 'checked' : '' }} /> Yes
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_characteristic_test" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop

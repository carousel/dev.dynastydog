@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Characteristic Severity</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/characteristic/severity/update', $characteristicSeverity->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="characteristicHealthSeverityValue" class="col-sm-2 control-label">Value</label>
        <div class="col-sm-5">
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Minimum</span>
                <input type="text" name="minimum_severity_value" class="form-control" id="characteristicHealthSeverityValue" value="{{{ Input::old('minimum_severity_value', $characteristicSeverity->min_value) }}}" maxlength="5" required />
            </div>
        </div>
        <div class="col-sm-5">
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Maximum</span>
                <input type="text" name="maximum_severity_value" class="form-control" value="{{{ Input::old('maximum_severity_value', $characteristicSeverity->max_value) }}}" maxlength="5" required />
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="characteristicHealthSeverityCanBeExpressed" class="col-sm-2 control-label">Can Be Expressed?</label>
        <div class="col-sm-2">
            <div class="checkbox">
                <label for="characteristicHealthSeverityCanBeExpressed">
                    <input type="checkbox" name="severity_can_be_expressed" value="yes" id="characteristicHealthSeverityCanBeExpressed" {{ (Input::old('severity_value_can_be_revealed', ($characteristicSeverity->can_be_expressed ? 'yes' : 'no')) == 'yes') ? 'checked' : '' }}/>
                    Yes
                </label>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Minimum Age</span>
                <input type="text" name="minimum_age_to_express" class="form-control" value="{{{ Input::old('minimum_age_to_express', $characteristicSeverity->min_age_to_express) }}}" maxlength="5" placeholder="None" />
            </div>
        </div>
        <div class="col-sm-4">
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Maximum Age</span>
                <input type="text" name="maximum_age_to_express" class="form-control" value="{{{ Input::old('maximum_age_to_express', $characteristicSeverity->max_age_to_express) }}}" maxlength="5" placeholder="None" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="characteristicHealthSeverityValueCanBeKnown" class="col-sm-2 control-label">Value Can Be Revealed?</label>
        <div class="col-sm-2">
            <div class="checkbox">
                <label for="characteristicHealthSeverityValueCanBeKnown">
                    <input type="checkbox" name="severity_value_can_be_revealed" value="yes" id="characteristicHealthSeverityValueCanBeKnown" {{ (Input::old('severity_value_can_be_revealed', ($characteristicSeverity->value_can_be_revealed ? 'yes' : 'no')) == 'yes') ? 'checked' : '' }}/>
                    Yes
                </label>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Minimum Age</span>
                <input type="text" name="minimum_age_to_reveal_severity_value" class="form-control" value="{{{ Input::old('minimum_age_to_reveal_severity_value', $characteristicSeverity->min_age_to_reveal_value) }}}" maxlength="5" placeholder="None" />
            </div>
        </div>
        <div class="col-sm-4">
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Maximum Age</span>
                <input type="text" name="maximum_age_to_reveal_severity_value" class="form-control" value="{{{ Input::old('maximum_age_to_reveal_severity_value', $characteristicSeverity->max_age_to_reveal_value) }}}" maxlength="5" placeholder="None" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="characteristicHealthSeverityUnits" class="col-sm-2 control-label">Units</label>
        <div class="col-sm-5">
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Prefix</span>
                <input type="text" name="severity_prefix_units" class="form-control" id="characteristicHealthSeverityUnits" value="{{{ Input::old('severity_prefix_units', $characteristicSeverity->prefix_units) }}}" maxlength="16" placeholder="None" />
            </div>
        </div>
        <div class="col-sm-5">
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Suffix</span>
                <input type="text" name="severity_suffix_units" class="form-control" value="{{{ Input::old('severity_suffix_units', $characteristicSeverity->suffix_units) }}}" maxlength="16" placeholder="None" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a class="btn btn-danger" href="{{ route('admin/characteristics/characteristic/severity/delete', $characteristicSeverity->id) }}" onclick="return confirm('Are you sure you want to delete this characteristic severity?');">Delete</a>
            <button type="submit" name="update_characteristic_health_severity" value="update_characteristic_health_severity" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

<hr />

<h2>
    @if($characteristicSeverity->hasSymptoms())
    <i class="fa fa-check text-success"></i>
    @else
    <i class="fa fa-times text-danger"></i>
    @endif
    Health Symptoms
    <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#characteristic_health_severity_health_symptoms">
        <i class="fa fa-plus"></i>
    </button>
</h2>
<div id="characteristic_health_severity_health_symptoms" class="collapse {{ Input::old('create_characteristic_health_severity_health_symptom') ? 'in' : '' }}">
    <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/characteristic/severity/symptom/add', $characteristicSeverity->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <div class="form-group">
            <label for="characteristicHealthSeverityHealthSymptomHealthSymptomId" class="col-sm-2 control-label">Health Symptom</label>
            <div class="col-sm-10">
                <select name="symptom" class="form-control" id="characteristicHealthSeverityHealthSymptomHealthSymptomId" required>
                    @foreach($symptoms as $symptom)
                    <option value="{{ $symptom->id }}" {{ (Input::old('symptom') == $symptom->id) ? 'selected' : '' }}>{{ $symptom->name }}</option>
                    @endforeach

                    @if($symptoms->isEmpty())
                    <option value="">No symptoms available</option>
                    @endif
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="characteristicHealthSeverityHealthSymptomOffsetOnsetAge" class="col-sm-2 control-label">Offset Age to Express</label>
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Minimum</span>
                    <input type="text" name="minimum_offset_age_to_express" class="form-control" id="characteristicHealthSeverityHealthSymptomOffsetOnsetAge" value="{{{ Input::old('minimum_offset_age_to_express') }}}" maxlength="5" required />
                </div>
            </div>
            <div class="col-sm-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">Maximum</span>
                    <input type="text" name="maximum_offset_age_to_express" class="form-control" value="{{{ Input::old('maximum_offset_age_to_express') }}}" maxlength="5" required />
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="characteristicHealthSeverityHealthSymptomLethal" class="col-sm-2 control-label">Lethal?</label>
            <div class="col-sm-7">
                <div class="checkbox">
                    <label for="characteristicHealthSeverityHealthSymptomLethal">
                        <input type="checkbox" name="lethal" value="yes" id="characteristicHealthSeverityHealthSymptomLethal" {{ (Input::old('lethal') == 'yes') ? 'checked' : '' }} /> Yes
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2 text-right">
                <button type="submit" name="create_characteristic_health_severity_health_symptom" value="create_characteristic_health_severity_health_symptom" class="btn btn-primary">Add</button>
            </div>
        </div>
    </form>

    @foreach($characteristicSeveritySymptoms as $characteristicSeveritySymptom)
    <div class="well well-sm">
        <h3>{{ $characteristicSeveritySymptom->symptom->name }}</h3>
        <form class="form-horizontal" role="form" method="post" action="{{ route('admin/characteristics/characteristic/severity/symptom/update', $characteristicSeveritySymptom->id) }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <div class="form-group">
                <label for="characteristicHealthSeverityHealthSymptom{{ $characteristicSeveritySymptom->id }}OffsetOnsetAge" class="col-sm-2 control-label">Offset Onset Age</label>
                <div class="col-sm-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon">Minimum</span>
                        <input type="text" name="existing_minimum_offset_age_to_express" class="form-control" id="characteristicHealthSeverityHealthSymptom{{ $characteristicSeveritySymptom->id }}OffsetOnsetAge" value="{{ $characteristicSeveritySymptom->min_offset_age_to_express }}" maxlength="5" required />
                    </div>
                </div>
                <div class="col-sm-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon">Maximum</span>
                        <input type="text" name="existing_maximum_offset_age_to_express" class="form-control" value="{{ $characteristicSeveritySymptom->max_offset_age_to_express }}" maxlength="5" required />
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="characteristicHealthSeverityHealthSymptom{{ $characteristicSeveritySymptom->id }}Lethal" class="col-sm-2 control-label">Lethal?</label>
                <div class="col-sm-7">
                    <div class="checkbox">
                        <label for="characteristicHealthSeverityHealthSymptom{{ $characteristicSeveritySymptom->id }}Lethal">
                            <input type="checkbox" name="lethal" value="yes" id="characteristicHealthSeverityHealthSymptom{{ $characteristicSeveritySymptom->id }}Lethal" {{ (($characteristicSeveritySymptom->lethal ? 'yes' : 'no') == 'yes') ? 'checked' : '' }} />
                            Yes
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <a class="btn btn-danger" href="{{ route('admin/characteristics/characteristic/severity/symptom/delete', $characteristicSeveritySymptom->id) }}" onclick="return confirm('Are you sure you want to delete this characteristic severity symptom?');">Delete</a>
                    <button type="submit" name="edit_characteristic_health_severity_health_symptom" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
    @endforeach
</div>

@stop

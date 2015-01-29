@if(isset($message) and strlen($message))
<span class="btn btn-block btn-xs btn-primary disabled">{{{ $message }}}</span>
@endif

@if($dogCharacteristic->phenotypesAreRevealed() and ($phenotypes = $dogCharacteristic->phenotypes()->orderBy('priority', 'asc')->get()) and ! $phenotypes->isEmpty())
<span class="center-block label label-primary label-lg label-wrap unbolded">
	@foreach($phenotypes as $phenotype)
    {{ $phenotype->name }}
    @endforeach
</span>
@endif()

@if( $dogCharacteristic->hasSeverity() and $dogCharacteristic->severityValueIsRevealed())
<span class="center-block label label-primary label-lg label-wrap unbolded">
    {{ $dogCharacteristic->formatSeverityValue() }}
</span>
@endif

@if($dogCharacteristic->genotypesAreRevealed() and ! $dogCharacteristic->genotypes->isEmpty())
<span class="center-block label label-primary label-lg label-wrap unbolded">
	@foreach($dogCharacteristic->genotypes as $genotype)
    {{ $genotype->toSymbol() }}
    @endforeach
</span>
@endif

@if($dogCharacteristic->rangedValueIsRevealed())
<div class="progress-group" id="dog-characteristic-range-{{ $dogCharacteristic->id }}">
    <span class="progress-group-addon">
        <a class="range-bounds" data-toggle="tooltip" data-placement="top" title="{{ $dogCharacteristic->characteristic->ranged_lower_boundary_label }}">
            <i class="fa fa-step-backward"></i>
        </a>
    </span>
    <div class="progress">
        <div class="progress-bar" role="progressbar" aria-valuetransitiongoal="{{ $dogCharacteristic->current_ranged_value}}" aria-valuemin="{{ $dogCharacteristic->characteristic->min_ranged_value }}" aria-valuemax="{{ $dogCharacteristic->characteristic->max_ranged_value }}" data-label="{{ $dogCharacteristic->formatRangedValue() }}"></div>
    </div>
    <span class="progress-group-addon">
        <a class="range-bounds" data-toggle="tooltip" data-placement="top" title="{{ $dogCharacteristic->characteristic->ranged_upper_boundary_label }}">
            <i class="fa fa-step-forward"></i>
        </a>
    </span>
</div>
<script type="text/javascript">
$(function() {
    $("#dog-characteristic-range-{{ $dogCharacteristic->id }} .progress .progress-bar").each(function(){
    	var progress=$(this);
        var label=progress.attr("data-label");
        progress.progressbar({
            display_text:"center",
            use_percentage:false,
            amount_format:function(e,i){
                return label;
            }
        });
    });
});
</script>
@endif

{{-- Get all active tests that this dog has not been tested for --}}
@if($showTests and $currentUser->ownsDog($dogCharacteristic->dog) and ($testableTests = $dogCharacteristic->untestedTests()->whereInTestableAgeRange($dogCharacteristic->dog->age)->orderBy('name', 'asc')->get()) and ! $testableTests->isEmpty())
    @foreach($testableTests as $test)
    <button type="submit" name="test_dog" class="btn btn-success btn-xs btn-block" data-dog="{{ $dogCharacteristic->dog->id }}" data-test="{{ $test->id }}" data-loading-text="Testing..." {{ $dogCharacteristic->dog->isWorked() ? 'disabled' : '' }}>
        {{ $test->name }}
    </button>
    @endforeach

    @if($dogCharacteristic->hasBeenTested())
    <p class="text-center"><em><small>Last tested at {{ Dog::formatAgeInYearsAndMonths($dogCharacteristic->last_tested_at_months, true) }}</small></em></p>
    @endif
@endif

@if( ! $dogCharacteristic->phenotypesAreRevealed() and ! $dogCharacteristic->genotypesAreRevealed() and ! $dogCharacteristic->rangedValueIsRevealed() and ! $dogCharacteristic->severityValueIsRevealed() and ( ! $dogCharacteristic->hasUntestedTestableTest() or ! $currentUser->ownsDog($dogCharacteristic->dog)))
<em>Unknown</em>
@endif
@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <div class="text-right pull-right">
        <a href="{{ route('breed_registry/draft/form', $breedDraft->id) }}" class="btn btn-primary">Back to Form</a>
    </div>

    <h1>Viewing Characteristic: {{ $characteristic->name }}</h1>
</div>

<h2>Settings</h2>

@if($characteristic->isGenetic())
<div class="callout callout-info">
    <p class="text-center">You do not have to fill out both phenotypes and genotypes, although you can. One or the other is fine. Once you save a characteristic, you will see the results of what you've selected show up on the page. Hold <kbd>CTRL</kbd> or <kbd>CMD</kbd> to select more than one, or to deselect options.</p>
</div>
@endif

<form class="form-horizontal" role="form" method="post" action="{{ route('breed_registry/draft/form/characteristic/save', $breedDraftCharacteristic->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    @if($breedDraft->isOfficial() and $characteristic->isIgnorable())
    <div class="form-group">
        <div class="col-xs-10 col-xs-offset-2">
            <label>
                <input type="checkbox" value="yes" name="ignore" {{ Input::old('ignore', ($breedDraftCharacteristic->isIgnored() ? 'yes' : 'no') == 'yes' ? 'checked' : '') }} />
                I'm not sure what to put here, so let admins fill this out for me.
            </label>
        </div>
    </div>
    @endif

    @if($characteristic->hasPhenotypes())
    <div class="form-group">
        <label for="draft-characteristic-{{ $breedDraftCharacteristic->id }}-phenotypes" class="col-xs-2 control-label">
            Phenotypes
        </label>
        <div class="col-xs-10">
            <select name="phenotypes[]" class="form-control input-xs" size="2" multiple>
                @foreach($phenotypes as $phenotype)
                <option value="{{ $phenotype->id }}" {{ in_array($phenotype->id, $savedPhenotypeIds) ? 'selected' : '' }}>
                    {{ $phenotype->name }}
                </option>
                @endforeach
            </select>
        </div>
    </div>
    @endif

    @if($characteristic->hasGenotypes() and ! $characteristic->hideGenotypes())
    <div class="form-group">
        <label for="draft-characteristic-{{ $breedDraftCharacteristic->id }}-genotypes" class="col-xs-2 control-label">
            Genotypes
        </label>
        <div class="col-xs-10">
            <div class="row">
                @foreach($loci as $locus)
                <div class="col-xs-2 text-center">
                    <strong>{{ $locus->name }}</strong>
                    <select name="genotypes[{{ $locus->id }}][]" class="form-control input-xs" size="2" multiple>
                        @foreach($locus->genotypes as $genotype)
                        <option value="{{ $genotype->id }}" {{ in_array($genotype->id, $savedGenotypeIds) ? 'selected' : '' }}>
                            {{ $genotype->toSymbol() }}
                        </option>
                        @endforeach
                    </select><br />
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($characteristic->isRanged())
    <div class="form-group">
        <label for="draft-characteristic-{{ $breedDraftCharacteristic->id }}-range" class="col-xs-2 control-label">
            Range
        </label>
        <div class="col-xs-10">
            <div class="row">
                <div class="col-xs-2 text-right">
                    <p class="form-control-static"><strong>Bitch:</strong></p>
                </div>
                <div class="col-xs-10">
                    <p class="form-control-static">
                        <a class="slider-bounds" data-toggle="tooltip" data-placement="top" title="{{ $characteristic->ranged_lower_boundary_label }}">
                            <i class="fa fa-step-backward"></i>
                        </a> 

                        <input name="range_female" id="draft-characteristic-range-female" type="text" value="{{ $breedDraftCharacteristic->min_female_ranged_value.','.$breedDraftCharacteristic->max_female_ranged_value }}" data-slider-min="{{ ceil($characteristic->min_ranged_value) }}" data-slider-max="{{ floor($characteristic->max_ranged_value) }}"/>

                        <a class="slider-bounds" data-toggle="tooltip" data-placement="top" title="{{ $characteristic->ranged_upper_boundary_label }}">
                            <i class="fa fa-step-forward"></i>
                        </a>
                    </p>
                </div>
            </div><br />
            <div class="row">
                <div class="col-xs-2 text-right">
                    <p class="form-control-static"><strong>Dog:</strong></p>
                </div>
                <div class="col-xs-10">
                    <p class="form-control-static">
                        <a class="slider-bounds" data-toggle="tooltip" data-placement="top" title="{{ $characteristic->ranged_lower_boundary_label }}">
                            <i class="fa fa-step-backward"></i>
                        </a> 

                        <input name="range_male" id="draft-characteristic-range-male" type="text" value="{{ $breedDraftCharacteristic->min_male_ranged_value.','.$breedDraftCharacteristic->max_male_ranged_value }}" data-slider-min="{{ ceil($characteristic->min_ranged_value) }}" data-slider-max="{{ floor($characteristic->max_ranged_value) }}"/>

                        <a class="slider-bounds" data-toggle="tooltip" data-placement="top" title="{{ $characteristic->ranged_upper_boundary_label }}">
                            <i class="fa fa-step-forward"></i>
                        </a>
                    </p>
                </div>
            </div>

            <script type="text/javascript">
            $(document).ready(function(){
                var female = $("#draft-characteristic-range-female");
                var male = $("#draft-characteristic-range-male");

                if ( ! female.parent().hasClass('slider'))
                {
                    female.slider({
                        step:1,
                        value:[{{ $breedDraftCharacteristic->min_female_ranged_value.','.$breedDraftCharacteristic->max_female_ranged_value }}],
                        formater:function(value){
                            {{ $characteristic->jsFormatRangedSlider() }}
                        }
                    });

                    male.slider({
                        step:1,
                        value:[{{ $breedDraftCharacteristic->min_male_ranged_value.','.$breedDraftCharacteristic->max_male_ranged_value }}],
                        formater:function(value){
                            {{ $characteristic->jsFormatRangedSlider() }}
                        }
                    });
                }
            });
            </script>
        </div>
    </div>
    @endif

    <div class="form-group">
        <div class="col-xs-10 col-xs-offset-2 text-right">
            <button type="submit" name="save_draft_characteristic" class="btn btn-success" data-loading-text="<i class='fa fa-cog fa-spin'></i> Saving...">Save</button>

            @if( ! $breedDraft->isOfficial())
            <a class="btn btn-danger" href="{{ route('breed_registry/draft/form/characteristic/remove', $breedDraftCharacteristic->id) }}" onclick="return confirm('Are you sure you want to remove this characteristic?');" data-loading-text="<i class='fa fa-cog fa-spin'></i> Removing...">Remove</a>
            @endif
        </div>
    </div>
</form>

<hr />

<h2>Results</h2>

@if($characteristic->isType(Characteristic::TYPE_COLOUR))
<div class="callout callout-info">
    <p class="text-center">Markings, like Spotting, Ticking, Roaning, and Urajiro, are allowed by default. If you do not want white markings allowed in this breed, select SS for Spotting, tt for Ticking, and XX for Urajiro. For more specific information, please visit the help pages of those specific genes</p>
</div>
@endif

@if($characteristic->isGenetic())
    @if($characteristic->hasPhenotypes())
    <div class="row">
        <div class="col-xs-2 text-right">
            <strong>Phenotypes</strong>
        </div>
        <div class="col-xs-10">
            <ul>
                @foreach($resultingPhenotypes as $phenotype)
                <li>{{ $phenotype->name }}</li>
                @endforeach
            
                @if($resultingPhenotypes->isEmpty())
                <li><em>None</em></li>
                @endif
            </ul>
        </div>
    </div>
    @endif

    @if( ! $characteristic->hideGenotypes())
    <div class="row">
        <div class="col-xs-2 text-right">
            <strong>Genotypes</strong>
        </div>
        <div class="col-xs-10">
            <ul>
                @foreach($resultingLoci as $locus)
                <li>
                    <strong>{{ $locus->name }}:</strong>
                    @foreach($locus->genotypes as $genotype)
                    {{ $genotype->toSymbol() }}
                    @endforeach

                    @if($locus->genotypes->isEmpty())
                    <em>None</em>
                    @endif
                </li>
                @endforeach
            
                @if($resultingLoci->isEmpty())
                <li><em>None</em></li>
                @endif
            </ul>
        </div>
    </div>
    @endif
@endif

@if($characteristic->isRanged())
<div class="row">
    <div class="col-xs-2 text-right">
        <strong>Range</strong>
    </div>
    <div class="col-xs-10">
        <ul>
            @if(Floats::compare($breedDraftCharacteristic->min_female_ranged_value, $breedDraftCharacteristic->min_male_ranged_value, '=') and Floats::compare($breedDraftCharacteristic->max_female_ranged_value, $breedDraftCharacteristic->max_male_ranged_value, '='))
                @foreach(($labels = $breedDraftCharacteristic->characteristic->labels()
                    ->where('min_ranged_value', '<=', $breedDraftCharacteristic->max_female_ranged_value)
                    ->where('max_ranged_value', '>=', $breedDraftCharacteristic->min_female_ranged_value)
                    ->orderBy('min_ranged_value', 'asc')
                    ->orderBy('max_ranged_value', 'asc')
                    ->get()
                ) as $label)
                <li>{{ $label->name }}</li>
                @endforeach

                @if($labels->isEmpty())
                <li>{{ $breedDraftCharacteristic->formatRangedValue($breedDraftCharacteristic->min_female_ranged_value, false) }} - {{ $breedDraftCharacteristic->formatRangedValue($breedDraftCharacteristic->max_female_ranged_value, false) }}</li>
                @endif
            @else
                <li>
                    Bitches
                    <ul>
                        @foreach(($labels = $breedDraftCharacteristic->characteristic->labels()
                            ->where('min_ranged_value', '<=', $breedDraftCharacteristic->max_female_ranged_value)
                            ->where('max_ranged_value', '>=', $breedDraftCharacteristic->min_female_ranged_value)
                            ->orderBy('min_ranged_value', 'asc')
                            ->orderBy('max_ranged_value', 'asc')
                            ->get()
                        ) as $label)
                        <li>{{ $label->name }}</li>
                        @endforeach

                        @if($labels->isEmpty())
                        <li>{{ $breedDraftCharacteristic->formatRangedValue($breedDraftCharacteristic->min_female_ranged_value, false) }} - {{ $breedDraftCharacteristic->formatRangedValue($breedDraftCharacteristic->max_female_ranged_value, false) }}</li>
                        @endif
                    </ul>
                </li>
                <li>
                    Dogs
                    <ul>
                        @foreach(($labels = $breedDraftCharacteristic->characteristic->labels()
                            ->where('min_ranged_value', '<=', $breedDraftCharacteristic->max_male_ranged_value)
                            ->where('max_ranged_value', '>=', $breedDraftCharacteristic->min_male_ranged_value)
                            ->orderBy('min_ranged_value', 'asc')
                            ->orderBy('max_ranged_value', 'asc')
                            ->get()
                        ) as $label)
                        <li>{{ $label->name }}</li>
                        @endforeach

                        @if($labels->isEmpty())
                        <li>{{ $breedDraftCharacteristic->formatRangedValue($breedDraftCharacteristic->min_male_ranged_value, false) }} - {{ $breedDraftCharacteristic->formatRangedValue($breedDraftCharacteristic->max_male_ranged_value, false) }}</li>
                        @endif
                    </ul>
                </li>
            @endif
        </ul>
    </div>
</div>
@endif

@stop

{{-- JS assets --}}
@section('js_assets')
@parent
<script type="text/javascript" src="{{ asset('assets/js/breed_registry.js') }}"></script>
@stop

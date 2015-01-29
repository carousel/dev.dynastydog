@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <div class="text-right pull-right">
        <a href="{{ route('breed_registry/draft/submitted', $breedDraft->id) }}" class="btn btn-primary">Back to Entry</a>
    </div>

    <h1>Viewing Characteristic: {{ $characteristic->name }}</h1>
</div>

<h2>Results</h2>

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

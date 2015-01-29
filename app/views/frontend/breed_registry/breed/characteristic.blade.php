@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <div class="text-right pull-right">
        <a href="{{ route('breed_registry/breed', $breedCharacteristic->breed_id) }}" class="btn btn-primary">
            Back to {{{ $breedCharacteristic->breed->name }}}
        </a>
    </div>

    <h1>Viewing Characteristic: {{ $breedCharacteristic->characteristic->name }}</h1>
</div>

<h2>Results</h2>

<div class="row">
    @if($breedCharacteristic->isRanged())
    <div class="col-xs-2 text-right">
        <strong>Range</strong>
    </div>
    <div class="col-xs-10">
        <ul>
            @if(Floats::compare($breedCharacteristic->min_female_ranged_value, $breedCharacteristic->min_male_ranged_value, '=') and Floats::compare($breedCharacteristic->max_female_ranged_value, $breedCharacteristic->max_male_ranged_value, '='))
                @foreach(($labels = $breedCharacteristic->characteristic->labels()
                    ->where('min_ranged_value', '<=', $breedCharacteristic->max_female_ranged_value)
                    ->where('max_ranged_value', '>=', $breedCharacteristic->min_female_ranged_value)
                    ->orderBy('min_ranged_value', 'asc')
                    ->orderBy('max_ranged_value', 'asc')
                    ->get()
                ) as $label)
                <li>{{ $label->name }}</li>
                @endforeach

                @if($labels->isEmpty())
                <li>{{ $breedCharacteristic->formatRangedValue($breedCharacteristic->min_female_ranged_value, false) }} - {{ $breedCharacteristic->formatRangedValue($breedCharacteristic->max_female_ranged_value, false) }}</li>
                @endif
            @else
                <li>
                    Bitches
                    <ul>
                        @foreach(($labels = $breedCharacteristic->characteristic->labels()
                            ->where('min_ranged_value', '<=', $breedCharacteristic->max_female_ranged_value)
                            ->where('max_ranged_value', '>=', $breedCharacteristic->min_female_ranged_value)
                            ->orderBy('min_ranged_value', 'asc')
                            ->orderBy('max_ranged_value', 'asc')
                            ->get()
                        ) as $label)
                        <li>{{ $label->name }}</li>
                        @endforeach

                        @if($labels->isEmpty())
                        <li>{{ $breedCharacteristic->formatRangedValue($breedCharacteristic->min_female_ranged_value, false) }} - {{ $breedCharacteristic->formatRangedValue($breedCharacteristic->max_female_ranged_value, false) }}</li>
                        @endif
                    </ul>
                </li>
                <li>
                    Dogs
                    <ul>
                        @foreach(($labels = $breedCharacteristic->characteristic->labels()
                            ->where('min_ranged_value', '<=', $breedCharacteristic->max_male_ranged_value)
                            ->where('max_ranged_value', '>=', $breedCharacteristic->min_male_ranged_value)
                            ->orderBy('min_ranged_value', 'asc')
                            ->orderBy('max_ranged_value', 'asc')
                            ->get()
                        ) as $label)
                        <li>{{ $label->name }}</li>
                        @endforeach

                        @if($labels->isEmpty())
                        <li>{{ $breedCharacteristic->formatRangedValue($breedCharacteristic->min_male_ranged_value, false) }} - {{ $breedCharacteristic->formatRangedValue($breedCharacteristic->max_male_ranged_value, false) }}</li>
                        @endif
                    </ul>
                </li>
            @endif
        </ul>
    </div>
    @endif

    @if($breedCharacteristic->hasPhenotypes())
    <div class="col-xs-2 text-right">
        <strong>Phenotypes</strong>
    </div>
    <div class="col-xs-10">
        <ul>
            @foreach($breedCharacteristic->queryPhenotypes()->orderBy('name', 'asc')->get() as $phenotype)
            <li>{{ $phenotype->name }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if( ! $breedCharacteristic->characteristic->hideGenotypes() and $breedCharacteristic->hasGenotypes())
    <div class="col-xs-2 text-right">
        <strong>Genotypes</strong>
    </div>
    <div class="col-xs-10">
        <ul>
            @foreach($breedCharacteristic->characteristic->loci as $locus)
            <li>
                <strong>{{ $locus->name }}:</strong>
                @foreach($breedCharacteristic->breed->genotypes()->where('genotypes.locus_id', $locus->id)->wherePivot('frequency', '>', 0)->orderByAlleles()->get() as $genotype)
                {{ $genotype->toSymbol() }}
                @endforeach
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

@stop

@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <div class="text-right pull-right">
        <a href="{{ route('breed_registry/manage') }}" class="btn btn-primary">Manage Your Breeds</a>
    </div>

    <h1>Breed Registry</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            Search Breeds
        </h3>
    </div>
    <div class="panel-body">
        <form class="form" role="form" method="get" id="search-breed-registry">
            <fieldset id="search-breed-registry">
                <div class="form-group">
                    <label for="breed-registry-name">Name:</label>
                    <input type="text" name="name" class="form-control" id="breed-registry-name" value="{{{ Input::get('name') }}}" />
                </div>

                @foreach($searchedCharacteristics as $index => $searchedCharacteristic)
                <div class="characteristic-wrapper clearfix">
                    @include('characteristics/_dropdown', array(
                        'characteristicCategories' => $characteristicCategories, 
                        'selectedCharacteristic' => $searchedCharacteristic['characteristic'], 
                        'counter' => $counter, 
                    ))

                    @include('characteristics/_profiles', array(
                        'characteristic' => $searchedCharacteristic['characteristic'], 
                        'searchedCharacteristic' => $searchedCharacteristic, 
                        'counter' => $counter++, 
                    ))
                </div>
                @endforeach

                @include('characteristics/_add')

                <button type="submit" name="search" value="breeds" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-success btn-block btn-loading">Search</button>
            </fieldset>
        </form>
    </div>
</div>

<h3>Browse Breeds - Alphabetical Order</h3>

{{ $results->appends(array_except(Input::all(), 'page'))->links() }}

@if($showCharacteristics)
<table class="table table-striped table-responsive">
    <thead>
        <tr>
            <th>Breed</th>
            <th class="col-xs-8 text-center">Characteristics</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results as $breed)
        <tr>
            <td>
                <a href="{{ route('breed_registry/breed', $breed->id) }}" class="go-to-breed">
                    {{{ $breed->name }}} <i class='loading fa fa-cog fa-spin hidden'></i>
                </a>
            </td>
            <td>
                @foreach($breed->characteristics as $breedCharacteristic)
                <div class="row">
                    <div class="col-sm-4 text-right">
                        <strong>{{ $breedCharacteristic->characteristic->name }}:</strong>
                    </div>
                    <div class="col-sm-8">
                        <div class="row">
                            @if($breedCharacteristic->isRanged())
                            <div class="col-sm-4 text-right">
                                <strong>Range</strong>
                            </div>
                            <div class="col-sm-8">
                                <div style="max-height: 95px; overflow: auto;">
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
                            </div>
                            @endif

                            @if(($phenotypes = $breedCharacteristic->queryPhenotypes()->orderBy('name', 'asc')->get()) and ! $phenotypes->isEmpty())
                            <div class="col-sm-4 text-right">
                                <strong>Phenotypes</strong>
                            </div>
                            <div class="col-sm-8">
                                <div style="max-height: 95px; overflow: auto;">
                                    <ul>
                                        @foreach($phenotypes as $phenotype)
                                        <li>{{ $phenotype->name }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @endif

                            @if( ! $breedCharacteristic->characteristic->hideGenotypes() and $breedCharacteristic->hasGenotypes())
                            <div class="col-sm-4 text-right">
                                <strong>Genotypes</strong>
                            </div>
                            <div class="col-sm-8">
                                <div style="max-height: 95px; overflow: auto;">
                                    <ul>
                                        @foreach($breedCharacteristic->characteristic->loci as $locus)
                                        <li>
                                            <strong>{{ $locus->name }}:</strong>
                                            @foreach($breed->genotypes()->where('genotypes.locus_id', $locus->id)->wherePivot('frequency', '>', 0)->orderByAlleles()->get() as $genotype)
                                            {{ $genotype->toSymbol() }}
                                            @endforeach
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </td>
        </tr>
        @endforeach

        @if($results->isEmpty())
        <tr>
            <td colspan="2">No breeds were found with your specified settings.</td>
        </tr>
        @endif
    </tbody>
</table>
@else
<div class="row">
    @foreach($results as $breed)
    <div class="col-xs-6 col-md-3 go-to-breed">
        <div class="panel panel-default">
            @if($breed->hasImage())
            <div class="panel-body">
                <a href="{{ route('breed_registry/breed', $breed->id) }}">
                    <img src="{{ asset($breed->getImageUrl()) }}" alt="Breed Image" title="Breed Image" class="img-responsive center-block" />
                </a>
            </div>
            @endif

            <div class="panel-footer panel-nav">
                <ul class="nav nav-pills bordered nav-justified">
                    <li>
                        <a href="{{ route('breed_registry/breed', $breed->id) }}">
                            {{{ $breed->name }}} <i class='loading fa fa-cog fa-spin hidden'></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endforeach

    @if($results->isEmpty())
    <div class="col-md-12">
        <p>No breeds were found with your specified settings.</p>
    </div>
    @endif
</div>
@endif

{{ $results->appends(array_except(Input::all(), 'page'))->links() }}

@stop

{{-- JS assets --}}
@section('js_assets')
@parent
<script type="text/javascript" src="{{ asset('assets/js/breed_registry.js') }}"></script>
<script type="text/javascript">
$(function() {
    dogGame.characteristic_search.init({
        counter: {{ $counter }}
    });
});
</script>
@stop

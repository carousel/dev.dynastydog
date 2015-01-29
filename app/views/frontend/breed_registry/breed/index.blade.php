@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>{{{ $breed->name }}}</h1>
</div>

<h2>General Information</h2>

<div class="row">
    @if($breed->hasImage())
    <div class="col-xs-5">
        <img src="{{ asset($breed->getImageUrl()) }}" alt="Breed Image" title="Breed Image" />
    </div>
    @endif

    <div class="col-xs-{{ $breed->hasImage() ? 7 : 12 }}">
        <div class="row">
            <div class="col-xs-4 text-right">
                <strong>Name:</strong>
            </div>
            <div class="col-xs-8">
                {{{ $breed->name }}}
            </div>
        </div>

        @if($breed->hasDescription())
        <div class="row">
            <div class="col-xs-4 text-right">
                <strong>Description:</strong>
            </div>
            <div class="col-xs-8">
                {{{ $breed->description }}}
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-xs-4 text-right">
                <strong>Total Dogs Alive:</strong>
            </div>
            <div class="col-xs-8">
                {{ number_format($totalAliveDogs) }} {{ Str::plural('Dog', $totalAliveDogs) }}
            </div>
        </div>

        @if($breed->hasCreator())
        <div class="row">
            <div class="col-xs-4 text-right">
                <strong>Creator:</strong>
            </div>
            <div class="col-xs-8">
                {{ $breed->creator->linkedNameplate() }}
            </div>
        </div>
        @endif

        @if($breed->hasOriginator())
        <div class="row">
            <div class="col-xs-4 text-right">
                <strong>Originator:</strong>
            </div>
            <div class="col-xs-8">
                {{ $breed->originator->linkedNameplate() }}
            </div>
        </div>
        @endif

        @if($breed->isExtinctable())
        <div class="row">
            <div class="col-xs-4 text-right">
                <strong>Total Active Breed Members:</strong>
            </div>
            <div class="col-xs-8">
                {{ number_format($totalActiveBreedMembers) }} {{ Str::plural('Dog', $totalActiveBreedMembers) }}
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-md-12"><br />
                @if(Input::get('view') == 'health')
                <a class="btn btn-primary btn-sm btn-block" href="{{ route('breed_registry/breed', ['breed' => $breed->id, 'view' => 'health']) }}">
                    Refresh Health Disorders in {{{ $breed->name }}}
                </a>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Disorder</th>
                            <th>Dogs Affected</th>
                            <th class="text-right">% of Breed Population</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($healthStatistics as $healthStatistic)
                        <tr>
                            <td><strong>{{ $healthStatistic['characteristic']->name }}</strong></td>
                            <td>{{ number_format($healthStatistic['total_dogs']) }} {{ Str::plural('Dog', $healthStatistic['total_dogs']) }}</td>
                            <td class="text-right">{{ round(($healthStatistic['total_dogs'] / $totalAliveDogs) * 100, 2) }}%</td>
                        </tr>
                        @endforeach

                        @if(empty($healthStatistics))
                        <tr>
                            <td colspan="3">No disorders found in breed population</td>
                        </tr>
                        @else
                        {{-- <tr class="info">
                            <td><strong>TOTAL</strong></td>
                            <td>{{ number_format($totalAffectedDogs) }} {{ Str::plural('Dog', $totalAffectedDogs) }}</td>
                            <td></td>
                        </tr> --}}
                        @endif
                    </tbody>
                </table>
                @else
                <a class="btn btn-primary btn-sm btn-block" href="{{ route('breed_registry/breed', ['breed' => $breed->id, 'view' => 'health']) }}">
                    View Health Disorders in {{{ $breed->name }}}
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<h2>Characteristics</h2>

<div class="well well-sm text-justify">
    <a id="chlist"><!-- Empty --></a>
    @foreach($breedCharacteristics as $breedCharacteristic)
    <a class="btn btn-sm btn-info" style="margin-bottom: 0.5em;" href="{{ route('breed_registry/breed/characteristic', $breedCharacteristic->id) }}">
        {{ $breedCharacteristic->characteristic->name }}
    </a>
    @endforeach
</div>

@stop

@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Viewing Draft: {{{ $breedDraft->name }}}</h1>
</div>

<form role="form" method="post" action="{{ route('admin/breeds/breed/draft/reject', $breedDraft->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <div class="form-group">
        <textarea name="rejection_reasons" class="form-control" placeholder="Reasons for rejection"></textarea>
    </div>
    <div class="row">
        <div class="col-xs-6">
            <a href="{{ route('admin/breeds/breed/draft/approve', $breedDraft->id) }}" class="btn btn-block btn-lg btn-success" onclick="return confirm('Are you sure you want to APPROVE this breed?');">Approve Breed</a>
        </div>
        <div class="col-xs-6">
            <button type="submit" name="reject_draft" class="btn btn-block btn-lg btn-danger" onclick="return confirm('Are you sure you want to REJECT this breed?');">Reject Breed</button>
        </div>
    </div>
</form>

<h2>General Information</h2>

<div class="row">
    @if($breedDraft->hasImage())
    <div class="col-md-5">
        <img src="{{{ asset($breedDraft->getImageUrl()) }}}?{{ $breedDraft->updated_at }}" alt="Breed Image" title="Breed Image" />
    </div>
    <div class="col-md-7">
    @else
    <div class="col-md-12">
    @endif
        <div class="row">
            <div class="col-md-3 text-right">
                <strong>Name:</strong>
            </div>
            <div class="col-md-9">
                {{{ $breedDraft->name }}}
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 text-right">
                <strong>Description:</strong>
            </div>
            <div class="col-md-9">
                @if($breedDraft->hasDescription())
                {{{ $breedDraft->description }}}
                @else
                <em>No description</em>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 text-right">
                <strong>Creator:</strong>
            </div>
            <div class="col-md-9">
                @if(is_null($breedDraft->user))
                <em>Unknown</em>
                @else
                <a href="{{ route('admin/users/user/edit', $breedDraft->user->id) }}">{{{ $breedDraft->user->nameplate() }}}</a>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 text-right">
                <strong>Real?</strong>
            </div>
            <div class="col-md-9">
                {{ $breedDraft->isOfficial() ? 'Yes' : 'No' }}
            </div>
        </div>

        @if($breedDraft->isOfficial())
        <div class="row">
            <div class="col-md-3 text-right">
                <strong>Breed Health Disorders</strong>
            </div>
            <div class="col-md-9">
                @if($breedDraft->hasHealthDisorders())
                {{{ $breedDraft->health_disorders }}}
                @else
                <em>No health disorders</em>
                @endif
            </div>
        </div>
        @else
        <div class="row">
            <div class="col-md-3 text-right">
                <strong>Originator:</strong>
            </div>
            <div class="col-md-9">
                @if(is_null($breedDraft->dog))
                <em>Unknown</em>
                @else
                <a href="{{ route('admin/dogs/dog/edit', $breedDraft->dog->id) }}">{{{ $breedDraft->dog->nameplate() }}}</a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<h2>Characteristics</h2>

<div class="well well-sm text-justify">
    <a id="chlist"><!-- Empty --></a>
    @foreach($breedDraftCharacteristics as $breedDraftCharacteristic)
    <a class="btn btn-sm btn-info" href="#ch{{ $breedDraftCharacteristic->id }}" style="margin-bottom: 0.5em">{{ $breedDraftCharacteristic->characteristic->name }}</a>
    @endforeach
</div>

@foreach($breedDraftCharacteristics as $breedDraftCharacteristic)
<div class="well well-sm">
    <a id="ch{{ $breedDraftCharacteristic->id }}"><!-- Empty --></a>
    <h3>
        {{ $breedDraftCharacteristic->characteristic->name }}
        <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#draft_characteristic{{ $breedDraftCharacteristic->id }}">
            <i class="fa fa-plus"></i>
        </button>
        <a class="pull-right text-muted" href="#chlist"><i class="fa fa-arrow-up"></i></a>
    </h3>
    <div id="draft_characteristic{{ $breedDraftCharacteristic->id }}" class="collapse">
        @if($breedDraftCharacteristic->characteristic->isGenetic())
            @if($breedDraftCharacteristic->characteristic->hasPhenotypes())
            <div class="row">
                <div class="col-xs-2 text-right">
                    <strong>Phenotypes</strong>
                </div>
                <div class="col-xs-10">
                    <ul>
                        @foreach(($resultingPhenotypes = $breedDraftCharacteristic->possiblePhenotypes()->orderBy('name', 'asc')->get()) as $phenotype)
                        <li>{{ $phenotype->name }}</li>
                        @endforeach
                    
                        @if($resultingPhenotypes->isEmpty())
                        <li><em>None</em></li>
                        @endif
                    </ul>
                </div>
            </div>
            @endif

            @if( ! $breedDraftCharacteristic->characteristic->hideGenotypes())
            <div class="row">
                <div class="col-xs-2 text-right">
                    <strong>Genotypes</strong>
                </div>
                <div class="col-xs-10">
                    <ul>
                        @foreach(($resultingLoci = $breedDraftCharacteristic->possibleLociWithGenotypes()->orderBy('name', 'asc')->get()) as $locus)
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

        @if($breedDraftCharacteristic->characteristic->isRanged())
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
    </div>
</div>
@endforeach

@stop

@extends($layout)

{{-- Page content --}}
@section('content')

@if($breedDraft->isPending())
<div class="alert alert-info text-center">
    <strong>{{ strtoupper($breedDraft->getStatus()) }}</strong>
</div>
@elseif($breedDraft->isAccepted())
<div class="alert alert-success text-center">
    <strong>{{ strtoupper($breedDraft->getStatus()) }}</strong>
</div>
@elseif($breedDraft->isRejected() or $breedDraft->isExtinct())
<div class="alert alert-danger text-center">
    <strong>{{ strtoupper($breedDraft->getStatus()) }}</strong>
    @if($breedDraft->hasReasonsForRejection())
    <p><strong>Reasons:</strong> {{{ $breedDraft->rejection_reasons }}}</p>
    @endif
</div>
@endif

@if($breedDraft->isExtinct())
<form class="form-horizontal" role="form" method="post" action="{{ route('breed_registry/draft/submitted/resubmit', $breedDraft->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <div class="form-group">
        <label for="draft-dog" class="col-sm-2 control-label">New Originator</label>
        <div class="col-sm-10">
            <select name="dog" class="form-control" id="draft-dog" required>
                <option value=""></option>
                @foreach($kennelGroups as $kennelGroup)
                <optgroup label="{{{ $kennelGroup->name }}}">
                    @foreach($kennelGroup->dogs as $dog)
                    <option value="{{ $dog->id }}" {{ ($dog->id == Input::old('dog', $breedDraft->dog_id)) ? 'selected' : '' }}>{{{ $dog->nameplate() }}}</option>
                    @endforeach
                </optgroup>
                @endforeach

                @if ($kennelGroups->isEmpty())
                <option value="">No dogs available</option>
                @endif
            </select>
        </div>
    </div>
    <p class="text-right">
        <button type="submit" name="reactivate" class="btn btn-primary">Resubmit</button>
    </p>
</form>
@endif

<div class="page-header">
    <div class="text-right pull-right">
        <a href="{{ route('breed_registry/manage') }}" class="btn btn-primary">Manage Your Breeds</a>
    </div>

    <h1>{{{ $breedDraft->name }}}</h1>
</div>

<h2>General Information</h2>

<div class="row">
    @if($breedDraft->hasImage())
    <div class="col-xs-5">
        <img src="{{{ asset($breedDraft->getImageUrl()) }}}?{{ $breedDraft->updated_at }}" class="img-responsive center-block" alt="Breed Image" title="Breed Image" />
    </div>
    <div class="col-xs-7">
    @else
    <div class="col-xs-12">
    @endif
        <div class="row">
            <div class="col-xs-3 text-right">
                <strong>Name:</strong>
            </div>
            <div class="col-xs-9">
                {{{ $breedDraft->name }}}
            </div>
        </div>

        @if($breedDraft->hasDescription())
        <div class="row">
            <div class="col-xs-3 text-right">
                <strong>Description:</strong>
            </div>
            <div class="col-xs-9">
                {{{ $breedDraft->description }}}
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-xs-3 text-right">
                <strong>Creator:</strong>
            </div>
            <div class="col-xs-9">
                {{ is_null($breedDraft->user) ? '<em>Unknown</em>' : $breedDraft->user->linkedNameplate() }}
            </div>
        </div>

        @if( ! $breedDraft->isOfficial())
        <div class="row">
            <div class="col-xs-3 text-right">
                <strong>Originator:</strong>
            </div>
            <div class="col-xs-9">
                {{ is_null($breedDraft->dog) ? '<em>Unknown</em>' : $breedDraft->dog->linkedNameplate() }}
            </div>
        </div>
        @endif
    </div>
</div>

<h2>Characteristics</h2>

<div class="well well-sm text-justify">
    <a id="chlist"><!-- Empty --></a>
    @foreach($breedDraftCharacteristics as $draftCharacteristic)
    <a class="btn btn-sm {{ $draftCharacteristic->wasSaved() ? 'btn-default' : 'btn-info' }}" style="margin-bottom: 0.5em;" href="{{ route('breed_registry/draft/submitted/characteristic', $draftCharacteristic->id) }}">{{ $draftCharacteristic->characteristic->name }}</a>
    @endforeach

    @if($breedDraftCharacteristics->isEmpty())
    <p class="text-center no-margin">No characteristics added</p>
    @endif
</div>

@stop

{{-- JS assets --}}
@section('js_assets')
@parent
<script type="text/javascript" src="{{ asset('assets/js/breed_registry.js') }}"></script>
@stop

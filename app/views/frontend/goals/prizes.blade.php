@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Claim Your Prizes</h1>
</div>

<p class="text-center">Congratulations! When you win a Community Challenge you can choose one of two prizes - {{ Dynasty::credits($creditPrize) }} added to your account, or access to the Breeder's Prize for 30 days. The Breeder's Prize is the ability to view dogs' pedigrees to 10 generations instead of the usual 4</p>

@foreach($communityChallenges as $communityChallenge)
<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            Community Challenge #{{ $communityChallenge->id }}
        </h3>
    </div>
    <div class="panel-body">
        <div class="form-group">
            <label>Winners:</label>
            <p class="form-control-static">
                <ul class="list-unstyled">
                    @foreach($communityChallenge->entries as $entry)
                    <li>{{ $entry->dog->linkedNameplate() }} with {{ number_format($entry->num_breeders) }} {{ Str::plural('breeder', number_format($entry->num_breeders)) }}</li>
                    @endforeach
                </ul>
            </p>
        </div>

        <div class="row">
            <div class="col-md-5">
                <a href="{{ route('goals/community/claim/breeders', $communityChallenge->id) }}" class="btn btn-success btn-block">
                    <i class="fa fa-check-square-o"></i>
                    Claim Breeder's Prize
                </a>
            </div>
            <div class="col-md-2 text-center">
                <p class="btn btn-link disabled"><strong>OR</strong></p>
            </div>
            <div class="col-md-5">
                <a href="{{ route('goals/community/claim/credits', $communityChallenge->id) }}" class="btn btn-success btn-block">
                    <i class="fa fa-check-square-o"></i>
                    Claim {{ Dynasty::credits($creditPrize) }}
                </a>
            </div>
        </div>
    </div>
</div>
@endforeach

@if($communityChallenges->isEmpty())
<p class="well text-center">You do not have any unclaimed Community Challenge prizes.</p>
@endif

@stop

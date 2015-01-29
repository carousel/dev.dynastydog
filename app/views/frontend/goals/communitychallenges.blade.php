@if($currentUser->hasBreedersPrize())
<div class="alert alert-info text-center">
    <strong>Your Breeder's Prize expires in {{ strtolower(carbon_intervalforhumans($currentUser->breeders_prize_until)) }}.</strong>
</div>
@endif

<div class="row">
    <div class="col-md-5">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Community Challenge {{ is_null($communityChallenge) ? '' : '#'.$communityChallenge->id }}
                </h3>
            </div>
            <div class="panel-body">
                @if(is_null($communityChallenge))
                <p class="text-center">There is no Community Challenge running at this time. Please come back later.</p>
                @else
                <p>Obtain a dog with the following characteristics:</p>

                <div class="callout callout-info">
                    <ul class="list-unstyled no-margin">
                        @foreach($communityChallenge->characteristics as $communityChallengeCharacteristic)
                        <li>
                            <strong>{{ $communityChallengeCharacteristic->characteristic->name}}:</strong>
                            {{ $communityChallengeCharacteristic->getGoalString() }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                <form role="form" method="post" action="{{ route('goals/community/enter', $communityChallenge->id) }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <label>Dog Must be Healthy?</label>
                        <p class="form-control-static">
                            {{ $communityChallenge->onlyAllowHealthyDogs() ? 'Yes' : 'No' }}
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Select Dog:</label>
                        <select name="dog" class="form-control">
                            @foreach($kennelGroups as $kennelGroup)
                            <optgroup label="{{{ $kennelGroup->name }}}">
                                @foreach($kennelGroup->dogs as $dog)
                                <option value="{{ $dog->id }}">{{{ $dog->nameplate() }}}</option>
                                @endforeach
                            </optgroup>
                            @endforeach

                            @if ( ! count($kennelGroups))
                            <option value="">No dogs available</option>
                            @endif
                        </select>
                    </div>

                    <button type="submit" name="enter_community_challenge" class="btn btn-success btn-block">
                        Submit for Judging
                    </button>
                </form><br />

                <p class="text-center">Challenge ends {{ $communityChallenge->end_date->format('F j, Y') }} at 11:59 PM. You must submit your entries before that time.</p>
                @endif
            </div>
        </div>

        @if( ! is_null($communityChallenge))
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Current Entries
                </h3>
            </div>
            <div class="panel-body">
                @if($communityChallenge->entries->count() > 0)
                <ol>
                    @foreach($communityChallenge->entries as $entry)
                    <li>{{ is_null($entry->dog) ? '<em>Unknown</em>' : $entry->dog->linkedNameplate() }}</li>
                    @endforeach
                </ol>
                @else
                <p><em>No entries</em></p>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-7">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Challenge Rules and Rewards
                </h3>
            </div>
            <div class="panel-body">
                <ol>
                    <li>Dogs are judged based on the number of different players listed as "Breeder" in the last 10 generations of the dog's pedigree. The more players participate in the breeding of a dog that fulfills the current challenge requirements, the higher your chance of winning as a team.</li>
                    <li>Every player that is listed as a "Breeder" in the winning dog's pedigree (up to 10 generations only) will get to select either the Breeder's Prize or 20 Credits. Even if you have bred multiple dogs in the winner's pedigree, you only receive one prize. The Breeder's Prize is the ability to view dogs' pedigrees to 10 generations instead of the usual 4. This ability lasts for 30 days.</li>
                    <li>You can submit as many dogs as you like into each challenge, provided they all meet the challenge requirements, but you can only win once per challenge. Once a dog is submitted, they can still win even if they are pet homed or die or are given away before the end of the challenge.</li>
                    <li>Dogs must have all the required characteristics revealed and must match all of them to be accepted as an entry.</li>
                    <li>If the "Dog must be healthy" status is set to Yes, that means that dogs must have no Current Health Problems at the time of submission.  If it is set to No, then a dog's health doesn't matter in this challenge.</li>
                    <li>Good luck, cooperate, and have fun!</li>
                </ol>

                @if($currentUser->hasUnclaimedCommunityChallengePrize())
                <p class="text-center">
                    <a href="{{ route('goals/community/prizes') }}" class="btn btn-primary">Claim Challenge Prize</a>
                </p>
                @endif
            </div>
        </div>
    </div>
</div>
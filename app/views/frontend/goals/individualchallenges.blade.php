<div class="row">
    <div class="col-md-12">
    <p class="text-center"><strong>Current Level:</strong> {{ $currentUser->challengeLevel->name }}</p>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <strong>Challenges to Next Level:</strong>
    </div>
    <div class="col-md-9">
        <div id="indiv-ch-next-level" class="progress">
            <div class="progress-bar" role="progressbar" aria-valuetransitiongoal="{{ $currentUser->challengeLevelCompletionPercent() }}" aria-valuemin="0" aria-valuemax="100" data-label="{{ $currentUser->challengeLevelProgress() }}"></div>
        </div>
    </div>
</div>

<p>Complete individual challenges to earn credits! When you own a dog that fulfills all the criteria of a challenge and tested all the required characteristics to make sure this is true, submit him or her for evaluation to complete the challenge! As you complete challenges, they will increase in difficulty, and the payout will increase accordingly. Good luck!</p>

<div class="row">
    @foreach($displayedChallenges as $challenge)
    <div class="col-md-4">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Challenge #{{ ++$counter }} (ID: {{ $challenge->id }})
                </h3>
            </div>
            <div class="panel-body">
                <p>Obtain a dog with the following characteristics:</p>

                <div class="callout callout-info">
                    <ul class="list-unstyled no-margin">
                        @foreach($challenge->characteristics as $challengeCharacteristic)
                        <li>
                            <strong>{{ $challengeCharacteristic->characteristic->name }}:</strong>
                            {{ $challengeCharacteristic->getGoalString() }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                <form role="form" method="post" action="{{ route('goals/challenge/complete', [$challenge->id]) }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="challenge" value="{{ $challenge->id }}">
                    <div class="row">
                        <div class="col-md-4 text-right">
                            <label>Level:</label>
                        </div>
                        <div class="col-md-8">
                            <p class="form-control-static">{{ $challenge->level->name }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-right">
                            <label>Prize:</label>
                        </div>
                        <div class="col-md-8">
                            <p class="form-control-static">{{ Dynasty::credits($challenge->level->credit_prize) }}</p>
                        </div>
                    </div>

                    @if($challenge->isComplete())
                    <div class="form-group">
                        <label>Dog:</label>
                        <div>
                            <p class="form-control-static">
                                {{ is_null($challenge->dog) ? '<em>Unknown</em>' : $challenge->dog->linkedNameplate() }}
                            </p>
                        </div>
                    </div>
                    <span class="btn btn-default btn-block disabled">Completed!</span>
                    <a href="{{ route('goals/challenge/roll') }}" class="btn btn-primary btn-block">Roll New Challenge</a>
                    @else
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
                            <option value="">You do not own any eligible dogs</option>
                            @endif
                        </select>
                    </div>
                    <button type="submit" name="complete_challenge" value="complete_challenge" class="btn btn-success btn-block">Complete Challenge</button>
                    @if($challenge->canBeRerolled())
                    <a href="{{ route('goals/challenge/reroll', $challenge->id) }}" class="btn btn-link btn-block btn-xs" onclick="return confirm('Are you sure you want to reroll this challenge for {{ Dynasty::credits(Config::get('game.challenge.reroll_cost')) }}?');">Reroll Challenge? ({{ Dynasty::credits(Config::get('game.challenge.reroll_cost')) }})</a>
                    @endif
                    @endif
                </form>
            </div>
        </div>
    </div>
    @endforeach

    @for($i = 0; $i < $totalEmptySlots; ++$i)
    <div class="col-md-4">
        <a href="{{ route('goals/challenge/roll') }}" class="btn btn-primary btn-block">Start Challenge</a>
    </div>
    @endfor
</div>

<h2>History</h2>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Requirements</th>
            <th>Prize</th>
            <th>Completed With</th>
        </tr>
    </thead>
    <tbody>
        @foreach($completedChallenges as $challenge)
        <tr>
            <td>
                <ul class="list-unstyled">
                    @foreach($challenge->characteristics as $challengeCharacteristic)
                    <li>
                        <strong>{{ $challengeCharacteristic->characteristic->name }}:</strong>
                        {{ $challengeCharacteristic->getGoalString() }}
                    </li>
                    @endforeach
                </ul>
            </td>
            <td>{{ Dynasty::credits($challenge->credit_payout) }}</td>
            <td>{{ is_null($challenge->dog) ? '<em>Unknown</em>' : $challenge->dog->linkedNameplate() }}</td>
        </tr>
        @endforeach
        
        @if( ! count($completedChallenges))
        <tr>
            <td colspan="3">Nothing to report</td>
        </tr>
        @endif
    </tbody>
</table>

@if ($challengeJustCompleted = Session::get('challengeJustCompleted'))
<div class="modal fade" id="goals-ic-completed-modal" tabindex="-1" role="dialog" aria-labelledby="goals-ic-completed-modal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="goals-ic-completed-modal-label">Challenge Completed</h4>
            </div>
            <div class="modal-body">
                <p>Congratulations! You successfully completed this challenge with {{{ $challengeJustCompleted->dog->nameplate() }}}, and have been awarded {{ Dynasty::credits($challengeJustCompleted->credit_payout) }}!</p>

                @if ($newChallengeLevel = Session::get('newChallengeLevel'))
                <p><strong>Level Up!</strong> Congratulations, you are now level {{ $newChallengeLevel->name }}!</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">$(document).ready(function() {$('#goals-ic-completed-modal').modal('show');});</script>
@endif

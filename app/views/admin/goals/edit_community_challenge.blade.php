@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Community Challenge</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/goals/community/challenge/edit', $communityChallenge->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-communitychallenge-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $communityChallenge->id }}
            </p>
    </div>
        </div>

    <div class="form-group">
        <label for="cp-communitychallenge-name" class="col-sm-2 control-label">Start Date</label>
        <div class="col-sm-10">
            <p class="form-control-static">{{ $communityChallenge->start_date->format('m/d/Y') }}</p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-communitychallenge-end_date" class="col-sm-2 control-label">End Date</label>
        <div class="col-sm-10">
            <div class="input-group date">
                <input type="text" name="end_date" class="form-control" id="cp-communitychallenge-end_date" value="{{{ Input::old('end_date', $communityChallenge->end_date->format('m/d/Y')) }}}" required/>
                <span class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                </span>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function () {
            $("#cp-communitychallenge-end_date").datetimepicker({
                pickTime: false
            });
        });
    </script>

    <div class="form-group">
        <label for="cp-communitychallenge-healthy" class="col-sm-2 control-label">Healthy?</label>
        <div class="col-sm-7">
            <div class="checkbox">
                <label for="cp-communitychallenge-healthy">
                    <input type="checkbox" name="healthy" value="yes" id="cp-communitychallenge-healthy" {{ (Input::old('healthy', ($communityChallenge->healthy ? 'yes' : 'no')) == 'yes') ? 'checked' : '' }} />
                    Yes
                </label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="edit_community_challenge" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

<h2>Characteristics</h2>

<div class="callout callout-info">
    <ul class="list-unstyled no-margin">
        @foreach($communityChallengeCharacteristics as $communityChallengeCharacteristic)
        <li>
            <strong>{{ $communityChallengeCharacteristic->characteristic->name}}:</strong>
            {{ $communityChallengeCharacteristic->getGoalString() }}
        </li>
        @endforeach

        @if($communityChallengeCharacteristics->isEmpty())
        <li><em>None</em></li>
        @endif
    </ul>
</div>

@stop

@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Community Challenges</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-communitychallenges">
            <div class="form-group">
                <label for="search-communitychallenges-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-communitychallenges-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="community_challenges" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $communityChallenges->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th># Characteristics</th>
            <th>Healthy?</th>
        </tr>
    </thead>
    <tbody>
        @foreach($communityChallenges as $communityChallenge)
        <tr>
            <td><a href="{{ route('admin/goals/community/challenge/edit', $communityChallenge->id) }}">{{ $communityChallenge->id }}</a></td>
            <td>{{ $communityChallenge->start_date->format("M. j, 'y g:i A") }}</td>
            <td>{{ $communityChallenge->end_date->format("M. j, 'y g:i A") }}</td>
            <td>{{ number_format($communityChallenge->num_characteristics) }}</td>
            <td>{{ $communityChallenge->isHealthy() ? 'Yes' : 'No' }}</td>
        </tr>
        @endforeach()

        @if($communityChallenges->isEmpty())
        <tr>
            <td colspan="5">No community challenges to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $communityChallenges->appends(array_except(Input::all(), 'page'))->links() }}

@stop

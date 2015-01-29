@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Control Panel</h1>
</div>

<h2>New Users</h2>

<div class="row">
        
    <div class="col-md-4 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    New Registrations
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $newRegistrations }}
                    </h1>
                    <p class="avg no-margin">Users</p>
                </div>
            </div>
            <div class="panel-footer clearfix">
                New accounts created last 7 days
            </div>
        </div>
    </div>
        
    <div class="col-md-4 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    New User Retention
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $newUserRetention['percent'] }}%
                    </h1>
                    <p class="avg no-margin">{{ $newUserRetention['total'] }} Active Newbies</p>
                </div>
            </div>
            <div class="panel-footer clearfix">
                Percentage of users who joined last week that are still around this week.
            </div>
        </div>
    </div>
        
    <div class="col-md-4 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Average Newbie Drop Off Time
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <div class="row">
                        <div class="col-xs-4">
                            <h1 class="number primary no-margin">
                                {{ $averageNewbieDropOffTime['days'] }}
                            </h1>
                            <p class="avg no-margin">Days</p>
                        </div>
                        <div class="col-xs-4">
                            <h1 class="number primary no-margin">
                                {{ $averageNewbieDropOffTime['hours'] }}
                            </h1>
                            <p class="avg no-margin">Hours</p>
                        </div>
                        <div class="col-xs-4">
                            <h1 class="number primary no-margin">
                                {{ $averageNewbieDropOffTime['minutes'] }}
                            </h1>
                            <p class="avg no-margin">Minutes</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer clearfix">
                The average length of time it takes for a user to lose interest, given they lost interest in the first week of playing, and it has been at least two weeks since they registered.
            </div>
        </div>
    </div>

</div>

<div class="row">
        
    <div class="col-md-4 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Most Abandoned Page
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $mostAbandonedNewbiePage }}
                    </h1>
                    <p class="avg no-margin">Page</p>
                </div>
            </div>
            <div class="panel-footer clearfix">
                The page that newbies leave from, and never return.
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Never Logged In
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $neverLoggedIn['percent'] }}%
                    </h1>
                    <p class="avg no-margin">{{ $neverLoggedIn['total'] }} Users</p>
                </div>
            </div>
            <div class="panel-footer clearfix">
                Accounts that were never logged into over the last 30 days
            </div>
        </div>
    </div>

</div>

<h3>Tutorial Drop Off</h3>

<div style="max-height: 300px; overflow-y:auto; overflow-x:hidden;">
    <table class="table table-striped table-responsive">
        <thead>
            <tr>
                <th colspan="3" class="text-center">
                    <big>{{ $totalCompletedTutorials }} Completed</big>
                </th>
            </tr>
            <tr>
                <th class="text-right">Step</th>
                <th>Slug</th>
                <th>Users on Step</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tutorialStages as $tutorialStage)
            <tr>
                <td class="text-right">{{ $tutorialStage->number }}</td>
                <td>{{ $tutorialStage->slug }}</td>
                <td>{{ $tutorialStage->users->count() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<hr />

<h2>
    Revenue
    <small><small><span class="label label-info">{{ $paymentWeeks }} WEEKS</span></small></small>
</h2>

<h3>Report on Credits Bought</h3>

<div class="well well-sm">
    <p class="text-center no-margin"># of each credit package bought (this week & weekly average) order by DESC (by this week)</p>
</div>

<div class="row">
    <div class="col-md-2 text-right">
        <strong>Package</strong>
    </div>
    <div class="col-md-5 text-center">
        <strong>Past Seven Days</strong>
    </div>
    <div class="col-md-5 text-center">
        <strong>Weekly Average</strong>
    </div>
</div>

<div style="max-height: 300px; overflow-y:auto; overflow-x:hidden;">
    @foreach($creditPackagesBought as $creditPackageBought)
    <div class="row">
        <div class="col-md-2 text-right">
            <strong>{{ $creditPackageBought['package'] }}</strong>
        </div>
        <div class="col-md-1 text-right">
            {{ $creditPackageBought['weekly']['amount'] }}
        </div>
        <div class="col-md-4">
            <div class="progress">
                @if($creditPackageBought['weekly']['high'])
                <div class="progress-bar progress-bar-success" style="width: {{ $creditPackageBought['weekly']['percent'] }}%">
                    {{ $creditPackageBought['weekly']['amount'] }}
                </div>
                @elseif($creditPackageBought['weekly']['mid'])
                <div class="progress-bar progress-bar-warning" style="width: {{ $creditPackageBought['weekly']['percent'] }}%">
                    {{ $creditPackageBought['weekly']['amount'] }}
                </div>
                @else
                <div class="progress-bar progress-bar-danger" style="width: {{ $creditPackageBought['weekly']['percent'] }}%">
                    {{ $creditPackageBought['weekly']['amount'] }}
                </div>
                @endif
            </div>
        </div>

        <div class="col-md-1 text-right">
            {{ $creditPackageBought['weekly_avg']['amount'] }}
        </div>
        <div class="col-md-4">
            <div class="progress">
                @if($creditPackageBought['weekly_avg']['high'])
                <div class="progress-bar progress-bar-success" style="width: {{ $creditPackageBought['weekly_avg']['percent'] }}%">
                    {{ $creditPackageBought['weekly_avg']['amount'] }}
                </div>
                @elseif($creditPackageBought['weekly_avg']['mid'])
                <div class="progress-bar progress-bar-warning" style="width: {{ $creditPackageBought['weekly_avg']['percent'] }}%">
                    {{ $creditPackageBought['weekly_avg']['amount'] }}
                </div>
                @else
                <div class="progress-bar progress-bar-danger" style="width: {{ $creditPackageBought['weekly_avg']['percent'] }}%">
                    {{ $creditPackageBought['weekly_avg']['amount'] }}
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<br />

<div class="row">
    
    <div class="col-md-4 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Lifetime Value
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number no-margin">
                        ${{ $lifetimeValue }}
                    </h1>
                </div>
            </div>
            <div class="panel-footer clearfix">
                How much on average a user spends. AKA how much a single user is worth.
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Lifetime Network Value
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number no-margin">
                        ${{ $lifetimeNetworkValue }}
                    </h1>
                </div>
            </div>
            <div class="panel-footer clearfix">
                How much a single user is worth, given they attract other users.
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Weekly Revenue
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number no-margin">
                        ${{ $weeklyRevenue }}
                    </h1>
                    <ul class="min-max">
                        <li class="pull-right">
                            <h4 class="num"><small>Avg.</small>${{ $averageWeeklyRevenue }}</h4>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel-footer clearfix">
                sum Gross if payment_date < 7 days
            </div>
        </div>
    </div>

</div>

<h3>Revenue by Source</h3>

<div class="well well-sm">
    <p class="text-center no-margin">Sum weekly & monthly revenue by campaign code</p>
</div>

<div class="row">
    <div class="col-md-3 text-right">
        <strong>Campaign Code</strong>
    </div>
    <div class="col-md-3 text-center">
        <strong>Past Seven Days</strong>
    </div>
    <div class="col-md-3 text-center">
        <strong>Past Thirty Days</strong>
    </div>
    <div class="col-md-3 text-center">
        <strong>Past 180 Days</strong>
    </div>
</div>

<div style="max-height: 300px; overflow-y:auto; overflow-x:hidden;">
    @foreach($revenueBySource as $source)
    <div class="row">
        <div class="col-md-3 text-right">
            <strong>{{ $source['campaign_code'] }}</strong>
        </div>
        <div class="col-md-1 text-right">
            ${{ $source['weekly']['amount'] }}
        </div>
        <div class="col-md-2">
            <div class="progress">
                @if($source['weekly']['high'])
                <div class="progress-bar progress-bar-success" style="width: {{ $source['weekly']['percent'] }}%">
                    ${{ $source['weekly']['amount'] }}
                </div>
                @elseif($source['weekly']['mid'])
                <div class="progress-bar progress-bar-warning" style="width: {{ $source['weekly']['percent'] }}%">
                    ${{ $source['weekly']['amount'] }}
                </div>
                @else
                <div class="progress-bar progress-bar-danger" style="width: {{ $source['weekly']['percent'] }}%">
                    ${{ $source['weekly']['amount'] }}
                </div>
                @endif
            </div>
        </div>

        <div class="col-md-1 text-right">
            ${{ $source['monthly']['amount'] }}
        </div>
        <div class="col-md-2">
            <div class="progress">
                @if($source['monthly']['high'])
                <div class="progress-bar progress-bar-success" style="width: {{ $source['monthly']['percent'] }}%">
                    ${{ $source['monthly']['amount'] }}
                </div>
                @elseif($source['monthly']['mid'])
                <div class="progress-bar progress-bar-warning" style="width: {{ $source['monthly']['percent'] }}%">
                    ${{ $source['monthly']['amount'] }}
                </div>
                @else
                <div class="progress-bar progress-bar-danger" style="width: {{ $source['monthly']['percent'] }}%">
                    ${{ $source['monthly']['amount'] }}
                </div>
                @endif
            </div>
        </div>

        <div class="col-md-1 text-right">
            ${{ $source['halfly']['amount'] }}
        </div>
        <div class="col-md-2">
            <div class="progress">
                @if($source['halfly']['high'])
                <div class="progress-bar progress-bar-success" style="width: {{ $source['halfly']['percent'] }}%">
                    ${{ $source['halfly']['amount'] }}
                </div>
                @elseif($source['halfly']['mid'])
                <div class="progress-bar progress-bar-warning" style="width: {{ $source['halfly']['percent'] }}%">
                    ${{ $source['halfly']['amount'] }}
                </div>
                @else
                <div class="progress-bar progress-bar-danger" style="width: {{ $source['halfly']['percent'] }}%">
                    ${{ $source['halfly']['amount'] }}
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<hr />

<h2>Perks</h2>

<h3>Report on Perks Bought</h3>

<div class="well well-sm">
    <p class="text-center no-margin">Basically the user_credit_transactions table, but organized.</p>
</div>

<div class="row">
    <div class="col-md-2 text-right">
        <strong>Perk</strong>
    </div>
    <div class="col-md-5 text-center">
        <strong>Past Seven Days</strong>
    </div>
    <div class="col-md-5 text-center">
        <strong>Weekly Average</strong>
    </div>
</div>

<div style="max-height: 300px; overflow-y:auto; overflow-x:hidden;">
    @foreach($perksBought as $creditTransaction)
    <div class="row">
        <div class="col-md-2 text-right">
            <strong>{{ $creditTransaction['credit_transaction_type'] }}</strong>
        </div>
        <div class="col-md-1 text-right">
            {{ $creditTransaction['weekly']['amount'] }}
        </div>
        <div class="col-md-4">
            <div class="progress">
                @if($creditTransaction['weekly']['high'])
                <div class="progress-bar progress-bar-success" style="width: {{ $creditTransaction['weekly']['percent'] }}%">
                    {{ $creditTransaction['weekly']['amount'] }}
                </div>
                @elseif($creditTransaction['weekly']['mid'])
                <div class="progress-bar progress-bar-warning" style="width: {{ $creditTransaction['weekly']['percent'] }}%">
                    {{ $creditTransaction['weekly']['amount'] }}
                </div>
                @else
                <div class="progress-bar progress-bar-danger" style="width: {{ $creditTransaction['weekly']['percent'] }}%">
                    {{ $creditTransaction['weekly']['amount'] }}
                </div>
                @endif
            </div>
        </div>

        <div class="col-md-1 text-right">
            {{ $creditTransaction['weekly_avg']['amount'] }}
        </div>
        <div class="col-md-4">
            <div class="progress">
                @if($creditTransaction['weekly_avg']['high'])
                <div class="progress-bar progress-bar-success" style="width: {{ $creditTransaction['weekly_avg']['percent'] }}%">
                    {{ $creditTransaction['weekly_avg']['amount'] }}
                </div>
                @elseif($creditTransaction['weekly_avg']['mid'])
                <div class="progress-bar progress-bar-warning" style="width: {{ $creditTransaction['weekly_avg']['percent'] }}%">
                    {{ $creditTransaction['weekly_avg']['amount'] }}
                </div>
                @else
                <div class="progress-bar progress-bar-danger" style="width: {{ $creditTransaction['weekly_avg']['percent'] }}%">
                    {{ $creditTransaction['weekly_avg']['amount'] }}
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<br />

<div class="row">
    
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Weekly Credits Gifted
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $weeklyCreditsGifted }}
                    </h1>
                    <p class="avg no-margin">Credits</p>
                </div>
            </div>
            <div class="panel-footer clearfix"></div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Weekly # Turns Thrown
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $weeklyTurnsThrown }}
                    </h1>
                    <p class="avg no-margin">Turns</p>
                </div>
            </div>
            <div class="panel-footer clearfix"></div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Weekly # Turn Throwing Instances
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $weeklyTurnThrowingInstances }}
                    </h1>
                    <p class="avg no-margin">Instances</p>
                </div>
            </div>
            <div class="panel-footer clearfix"></div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Upgraded Users
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $upgradedUsers }}
                    </h1>
                    <p class="avg">Users</p>
                    <ul class="min-max">
                        <li class="pull-right">
                            <h4 class="num"><small>Active</small>{{ $activeUpgradedPopulation }}%</h4>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel-footer clearfix">
                Upgraded Accounts / Active Players
            </div>
        </div>
    </div>

</div>

<hr />

<h2>Long Term Activity</h2>

<div class="row">

    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Long Gone Rate
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $longGoneRate['percent'] }}%
                    </h1>
                    <p class="avg no-margin">{{ $longGoneRate['total'] }} Users</p>
                </div>
            </div>
            <div class="panel-footer clearfix">
                Percentage of total user base that hasn't been online for over a month.
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Average Lifetime
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <div class="row">
                        <div class="col-xs-4">
                            <h1 class="number primary no-margin">
                                {{ $averageLifetime['days'] }}
                            </h1>
                            <p class="avg no-margin">Days</p>
                        </div>
                        <div class="col-xs-4">
                            <h1 class="number primary no-margin">
                                {{ $averageLifetime['hours'] }}
                            </h1>
                            <p class="avg no-margin">Hours</p>
                        </div>
                        <div class="col-xs-4">
                            <h1 class="number primary no-margin">
                                {{ $averageLifetime['minutes'] }}
                            </h1>
                            <p class="avg no-margin">Minutes</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer clearfix">
                AVG(Last seen - date registered)
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Average Lifetime excl. Newbies
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <div class="row">
                        <div class="col-xs-4">
                            <h1 class="number primary no-margin">
                                {{ $averageLifetimeNewbless['days'] }}
                            </h1>
                            <p class="avg no-margin">Days</p>
                        </div>
                        <div class="col-xs-4">
                            <h1 class="number primary no-margin">
                                {{ $averageLifetimeNewbless['hours'] }}
                            </h1>
                            <p class="avg no-margin">Hours</p>
                        </div>
                        <div class="col-xs-4">
                            <h1 class="number primary no-margin">
                                {{ $averageLifetimeNewbless['minutes'] }}
                            </h1>
                            <p class="avg no-margin">Minutes</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer clearfix">
                Basically how long does a user stay once they get into the game.
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Most Abandoned Page
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $mostAbandonedPage }}
                    </h1>
                    <p class="avg no-margin">Page</p>
                </div>
            </div>
            <div class="panel-footer clearfix">
                The page that players leave on if they do not return after a month (data from 30-60 days ago)
            </div>
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Total Accounts
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $totalAccounts }}
                    </h1>
                    <p class="avg no-margin">Users</p>
                </div>
            </div>
            <div class="panel-footer clearfix">
                COUNT Users_id
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Average # of Referrals
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $averageReferrals }}
                    </h1>
                    <p class="avg no-margin">Referrals</p>
                </div>
            </div>
            <div class="panel-footer clearfix"></div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Total Number of Animals
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $totalDogs }}
                    </h1>
                    <p class="avg no-margin">Dogs</p>
                </div>
            </div>
            <div class="panel-footer clearfix"></div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Online Users
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $onlineUsers['avg'] }}
                    </h1>
                    <p class="avg">Avg. Users</p>
                    <ul class="min-max">
                        <li>
                            <h4 class="num"><small>Lowest</small>{{ $onlineUsers['low'] }}</h4>
                        </li>
                        <li>
                            <h4 class="num"><small>Highest</small>{{ $onlineUsers['high'] }}</h4>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel-footer clearfix"></div>
        </div>
    </div>

</div>

<h3>Community Challenges</h3>

<table class="table table-striped table-responsive">
    <thead>
        <tr>
            <th class="text-right">ID</th>
            <th class="text-center">Start Date</th>
            <th class="text-center">End Date</th>
            <th class="text-right">Winning Users</th>
            <th class="text-right">Credits Paid Out</th>
            <th class="text-right">Breeder's Prizes Paid Out</th>
        </tr>
    </thead>
    <tbody>
        @foreach($communityChallenges as $communityChallenge)
        <tr>
            <td class="text-right">{{ $communityChallenge->id }}</td>
            <td class="text-center">{{ $communityChallenge->start_date->format('Y-m-d H:i:s') }}</td>
            <td class="text-center">{{ $communityChallenge->end_date->format('Y-m-d H:i:s')}}</td>
            <td class="text-right">{{ $communityChallenge->winners }}</td>
            <td class="text-right">{{ $communityChallenge->credit_payout }}</td>
            <td class="text-right">{{ $communityChallenge->breeders_prize_payout }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<hr />

<h2>Short Term Activity</h2>

<div class="row">
    
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Active Users
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $activeUsers }}
                    </h1>
                    <p class="avg no-margin">Users</p>
                </div>
            </div>
            <div class="panel-footer clearfix">
                Users seen in past 7 days
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Drop Off Rate
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $dropOffRate['percent'] }}%
                    </h1>
                    <p class="avg no-margin">{{ $dropOffRate['total'] }} Users</p>
                </div>
            </div>
            <div class="panel-footer clearfix">
                % of total user base that hasn't been online for over a week.
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Average Play Session Length
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <div class="row">
                        <div class="col-xs-4">
                            <h1 class="number primary no-margin">
                                {{ $averagePlaySession['days'] }}
                            </h1>
                            <p class="avg no-margin">Days</p>
                        </div>
                        <div class="col-xs-4">
                            <h1 class="number primary no-margin">
                                {{ $averagePlaySession['hours'] }}
                            </h1>
                            <p class="avg no-margin">Hours</p>
                        </div>
                        <div class="col-xs-4">
                            <h1 class="number primary no-margin">
                                {{ $averagePlaySession['minutes'] }}
                            </h1>
                            <p class="avg no-margin">Minutes</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer clearfix">
                if (active_player = yes) {average(last_seen_time - last_login)}
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Weekly Forum Posts
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $weeklyForumPosts }}
                    </h1>
                    <p class="avg no-margin">Posts</p>
                </div>
            </div>
            <div class="panel-footer clearfix"></div>
        </div>
    </div>

</div>

<div class="row">
    
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Current Animals Worked
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $weeklyAnimalsWorked }}
                    </h1>
                    <p class="avg">Dogs</p>
                </div>
            </div>
            <div class="panel-footer clearfix">
                Healthy if number is constantly changing, but trending upwards
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Weekly Animals Bred
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $weeklyAnimalsBred['total'] }}
                    </h1>
                    <p class="avg">Dogs</p>
                    <ul class="min-max">
                        <li>
                            <h4 class="num"><small>Avg.</small>{{ $weeklyAnimalsBred['avg'] }}</h4>
                        </li>
                        <li>
                            <h4 class="num"><small>Median</small>{{ $weeklyAnimalsBred['median'] }}</h4>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel-footer clearfix"></div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Weekly New Dogs Imported
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $weeklyDogsImported['total'] }}
                    </h1>
                    <p class="avg">Dogs</p>
                    <ul class="min-max">
                        <li>
                            <h4 class="num"><small>Avg.</small>{{ $weeklyDogsImported['avg'] }}</h4>
                        </li>
                        <li>
                            <h4 class="num"><small>Median</small>{{ $weeklyDogsImported['median'] }}</h4>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel-footer clearfix"></div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Weekly New Dogs Custom Imported
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $weeklyDogsCustomImported['total'] }}
                    </h1>
                    <p class="avg">Dogs</p>
                    <ul class="min-max">
                        <li>
                            <h4 class="num"><small>Avg.</small>{{ $weeklyDogsCustomImported['avg'] }}</h4>
                        </li>
                        <li>
                            <h4 class="num"><small>Median</small>{{ $weeklyDogsCustomImported['median'] }}</h4>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel-footer clearfix"></div>
        </div>
    </div>

</div>

<div class="row">
    
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Weekly Online Users
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $weeklyOnlineUsers['avg'] }}
                    </h1>
                    <p class="avg">Users</p>
                    <ul class="min-max">
                        <li>
                            <h4 class="num"><small>Lowest</small>{{ $weeklyOnlineUsers['low'] }}</h4>
                        </li>
                        <li>
                            <h4 class="num"><small>Highest</small>{{ $weeklyOnlineUsers['high'] }}</h4>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel-footer clearfix"></div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Weekly Contests Created
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $weeklyContestsCreated['total'] }}
                    </h1>
                    <p class="avg">Contests</p>
                    <ul class="min-max">
                        <li>
                            <h4 class="num"><small>Avg.</small>{{ $weeklyContestsCreated['avg'] }}</h4>
                        </li>
                        <li>
                            <h4 class="num"><small>Median</small>{{ $weeklyContestsCreated['median'] }}</h4>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel-footer clearfix">
                Average & median data goes back 30 days
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Average Contests Created per Active User
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $averageContestsPerActiveUser }}
                    </h1>
                    <p class="avg no-margin">Contests per User</p>
                </div>
            </div>
            <div class="panel-footer clearfix">
                total contests created (where created date < 7 days ago) divided by total active players
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Most Often Abandoned Page
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $mostOftenAbandonedPage }}
                    </h1>
                    <p class="avg no-margin">Page</p>
                </div>
            </div>
            <div class="panel-footer clearfix">
                ORDERBY  (last_seen_page) && last_seen_time < 15 mins ago && last_seen_time > 7 days ago
            </div>
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-3 col-sm-6">
        <div class="panel panel-grey">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Credits from Challenges
                </h3>
            </div>
            <div class="panel-body">
                <div class="daily-stats">
                    <h1 class="number primary no-margin">
                        {{ $creditsFromChallenges['total'] }}
                    </h1>
                    <p class="avg">Credits</p>
                    <ul class="min-max">
                        <li class="pull-right">
                            <h4 class="num"><small>Per Active User</small>{{ $creditsFromChallenges['per_active_user'] }}</h4>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel-footer clearfix">
                Amount of credits earned from individual challenges in the past 7 days
            </div>
        </div>
    </div>   

</div>

@stop

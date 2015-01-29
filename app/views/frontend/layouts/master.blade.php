@include('frontend/layouts/_header')

<div class="main row hidden-md hidden-lg">
    <div class="content col-sm-12 col-sx-12">
        <div class="panel panel-default">
            <div class="panel-body">

                <!-- Notifications -->
                @include('frontend/notifications/basic')

                @if( ! is_null($currentUser))
                    <a class="advance-turn-button btn btn-success btn-lg btn-block" href="{{ route('user/advance_turn') }}" data-loading-text="<i class='fa fa-cog fa-spin'></i> Advancing..." onclick="return confirm('Are you sure you want to advance your turn?');">
                        <i class="fa fa-level-up"></i> Next Turn
                    </a>

                    <p class="text-center">
                        {{ ($currentUsersTotalWorkedDogs = $currentUser->dogs()->whereWorked()->whereAlive()->count()) }}/{{ Config::get('game.dog.advanced_turn_worked_limit') }} Dogs Worked <a data-toggle="tooltip" data-placement="top" title="" data-original-title="One turn will go through up to {{ Config::get('game.dog.advanced_turn_worked_limit') }} worked {{ Str::plural('dog', Config::get('game.dog.advanced_turn_worked_limit')) }} at once."><i class="fa fa-question-circle"></i></a>
                    </p>

                    @if($currentUser->inTutorial())
                        <button class="btn btn-danger btn-lg btn-block" data-toggle="modal" data-target="#tutorial-current-stage">
                            View Current Tutorial Step
                        </button>
                    @endif

                    <br />
                @endif
            </div>
        </div>
    </div>
</div>

<div class="main row">
    <div class="content col-md-9 col-sm-12 col-sx-12" id="content">
        @include('frontend/layouts/_content')
    </div>

    @section('sidebar')
    <div class="sidebar col-md-3 col-sm-12 col-sx-12" id="main-sidebar">
        @include('frontend/layouts/_sidebar')
    </div>
    @show
</div>

@include('frontend/layouts/_footer')

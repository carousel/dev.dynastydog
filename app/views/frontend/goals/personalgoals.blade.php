<form class="form-horizontal" role="form" method="post" action="{{ route('goals/personal/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="form-group">
        <label for="personal-goal" class="col-xs-2 control-label">New Goal:</label>
        <div class="col-xs-8">
            <input type="text" name="new_personal_goal" class="form-control input-md" id="personal-goal" placeholder="Enter goal" maxlength="1024" required />
        </div>
        <div class="col-xs-2">
            <button type="submit" name="add_personal_goal" class="btn btn-success btn-block btn-md">Add Goal</button>
        </div>
    </div>
</form>

<div class="panel panel-grey">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            Current Goals
        </h3>
    </div>
    <div class="panel-body">
        @foreach($incompletePersonalGoals as $personalGoal)
        <div class="row">
            <form role="form" method="post" action="{{ route('goals/personal/update', $personalGoal->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="col-xs-2">
                    <a class="btn btn-success btn-block btn-xs" href="{{ route('goals/personal/complete', $personalGoal->id) }}">Complete</a>
                </div>
                <div class="col-xs-10">
                    <span id="personal-goal-body-text-{{ $personalGoal->id }}">{{{ $personalGoal->body }}}</span>
                    <input type="text" name="personal_goal_body" class="form-control input-sm hide" value="{{{ $personalGoal->body }}}" maxlength="1024" required id="personal-goal-body-input-{{ $personalGoal->id }}" />
                    <button type="button" name="edit_personal_goal" class="btn btn-link btn-xs">
                        <big><i class="fa fa-pencil-square-o"></i></big>
                    </button>
                    <button type="submit" name="save_personal_goal" class="btn btn-success btn-xs hide">
                        Save
                    </button>
                    <button type="button" name="cancel_edit_personal_goal" class="btn btn-link btn-xs hide">
                        <big><i class="fa fa-ban"></i></big>
                    </button>
                    <a class="btn btn-link btn-xs" href="{{ route('goals/personal/delete', $personalGoal->id) }}">
                        <big><i class="fa fa-trash-o"></i></big>
                    </a>
                </div>
            </form>
        </div>
        @endforeach
    </div>
</div>

<div class="panel panel-grey">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            Completed Goals
        </h3>
    </div>
    <div class="panel-body">
        @foreach($completedPersonalGoals as $personalGoal)
        <div class="row">
            <div class="col-xs-2">
                <span class="btn btn-default btn-block disabled btn-xs">Completed!</span>
            </div>
            <div class="col-xs-9">
                {{{ $personalGoal->body }}}
                <a class="btn btn-link btn-xs" href="{{ route('goals/personal/delete', $personalGoal->id) }}">
                    <big><i class="fa fa-trash-o"></i></big>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
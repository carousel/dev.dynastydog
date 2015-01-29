@extends($layout)

{{-- Page content --}}
@section('content')

<div class="panel panel-default">
    <div class="panel-heading panel-nav clearfix">
        <ul class="nav nav-pills">
            <li {{ (Input::get('tab', 'individual') == 'individual') ? 'class="active"' : '' }}><a href="#individual" data-toggle="tab">Individual Challenges</a></li>
            <li {{ (Input::get('tab') == 'community') ? 'class="active"' : '' }}><a href="#community" data-toggle="tab">Community Challenges</a></li>
            <li {{ (Input::get('tab') == 'personal') ? 'class="active"' : '' }}><a href="#personal" data-toggle="tab">Personal Goals</a></li>
        </ul>
    </div>

    <!-- Tab panes -->
    <div class="tab-content panel-body">
        <div class="tab-pane {{ (Input::get('tab', 'individual') == 'individual') ? 'active' : '' }}" id="individual">
            @include('frontend/goals/individualchallenges')
        </div>

        <div class="tab-pane {{ (Input::get('tab') == 'community') ? 'active' : '' }}" id="community">
            @include('frontend/goals/communitychallenges')
        </div>

        <div class="tab-pane {{ (Input::get('tab') == 'personal') ? 'active' : '' }}" id="personal">
            @include('frontend/goals/personalgoals')
        </div>
    </div>
</div>

@stop

{{-- JS assets --}}
@section('js_assets')
@parent
<script type="text/javascript" src="{{ asset('assets/js/goals.js') }}"></script>
@stop


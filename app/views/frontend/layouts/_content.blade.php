<div class="panel panel-default">
    <div class="panel-body">
        <!-- Breadcrumbs -->
        {{ Breadcrumbs::renderIfExists() }}

        <div class="hidden-sm hidden-xs">
            <!-- Notifications -->
            @include('frontend/notifications/basic')

            @if( ! is_null($currentUser))
                @if($currentUser->inTutorial())
                <p class="clearfix">
                    <button class="btn btn-danger pull-right" data-toggle="modal" data-target="#tutorial-current-stage">
                        View Current Tutorial Step
                    </button>
                </p>
                @endif
            @endif
        </div>

        <!-- Content -->
        @yield('content')

    </div>
</div>

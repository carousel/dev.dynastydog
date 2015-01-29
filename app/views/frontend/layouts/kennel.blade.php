{{-- CSS assets --}}
@section('css_assets')
@parent
<style type="text/css">
#kennel-sidebar, .kennel-show-sidebar, #content {
    z-index: 900;
}

.navbar-collapse.bs-navbar-collapse.in {
    z-index: 999;
}
</style>
@stop

@include('frontend/layouts/_header')

<div class="main row hidden-md hidden-lg">
    <div class="content col-sm-12 col-sx-12">
        <div class="panel panel-default no-margin">
            <div class="panel-body">
                <!-- Notifications -->
                @include('frontend/notifications/basic')

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
            </div>
        </div>

        <div class="panel panel-default kennel-show-sidebar collapse">
            <div class="panel-body">
                <button type="button" class="btn btn-primary btn-block btn-sm">
                    Open Sidebar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="main row">
    @if($currentUser->id == $kennel->id)
    <div class="sidebar col-md-3 collapse width in" id="kennel-sidebar">
        <!--<div id="kennel-sidebar-affix" data-spy="affix">-->
        <div class="panel panel-default">
            <div class="panel-body">
                <button type="button" class="btn btn-primary btn-block btn-sm" data-toggle="collapse" id="kennel-sidebar-collapse" data-target="#kennel-sidebar">
                    Collapse
                </button>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    <a data-toggle="collapse" href="#kennel-sidebar-mass-test">
                        <i class="fa fa-chevron-circle-down"></i>
                        Mass Test Dogs
                    </a>
                </h3>
            </div>

            <div id="kennel-sidebar-mass-test" class="panel-collapse collapse">
                <div class="panel-body">
                    <form class="form" role="form" method="post" action="{{ route('user/kennel/dogs/test') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="dogs" value="" class="selected-dog-ids" />
                        <div class="form-group">
                            <label for="mass-text-dogs-test-id">Select Test:</label>
                            <select name="test" class="form-control" id="mass-text-dogs-test-id" required>
                                @foreach($characteristicTests as $test)
                                <option value="{{ $test->id }}">
                                {{ $test->characteristic->name }}: {{ $test->name }} 
                                @if($test->hasAgeRequirement())
                                    @if($test->min_age == $test->max_age or ! $test->hasMaximumAgeRequirement())
                                    ({{ number_format($test->min_age).' '.Str::plural('Month', $test->min_age) }})
                                    @elseif( ! $test->hasMinimumAgeRequirement())
                                    ({{ number_format($test->max_age).' '.Str::plural('Month', $test->max_age) }})
                                    @else
                                    ({{ $test->hasMinimumAgeRequirement() ? number_format($test->min_age).' '.Str::plural('Month', $test->min_age) : '' }} - {{ $test->hasMaximumAgeRequirement() ? number_format($test->max_age).' '.Str::plural('Month', $test->max_age) : '' }})
                                    @endif
                                @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-9">
                                @if($kennel->isUpgraded())
                                <button type="submit" name="test_dogs" class="btn btn-success btn-block">Test Selected Dogs</button>
                                @else
                                <a href="{{ route('cash_shop') }}" class="btn btn-info btn-block btn-sm">Upgrade to Mass Test Dogs</a>
                                @endif
                            </div>
                            <div class="col-md-3 text-right">
                                <a data-toggle="tooltip" data-placement="top" title="Please select checkboxes corresponding to the dogs you want to apply this test to. Dogs that have already been tested for this test will not lose their turn.">
                                    <i class="fa fa-question-circle fa-3x"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    <a data-toggle="collapse" href="#kennel-sidebar-compare">
                        <i class="fa fa-chevron-circle-down"></i>
                        Compare Dogs
                    </a>
                </h3>
            </div>

            <div id="kennel-sidebar-compare" class="panel-collapse collapse">
                <div class="panel-body">
                    <form class="form" role="form" method="post" action="{{ route('user/kennel/dogs/compare') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="dogs" value="" class="selected-dog-ids" />

                        <div class="form-group">
                            <label for="compare-dogs-characteristic-id">Select Characteristic:</label>
                            <select name="characteristic" class="form-control" id="compare-dogs-characteristic-id" required>
                                @foreach($characteristics as $characteristic)
                                <option value="{{ $characteristic->id }}">{{ $characteristic->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-9">
                                <button type="submit" name="compare_dogs" class="btn btn-success btn-block">Compare Selected Dogs</button>
                            </div>
                            <div class="col-md-3 text-right">
                                <a data-toggle="tooltip" data-placement="top" title="Please select checkboxes corresponding to the dogs you want to compare.">
                                    <i class="fa fa-question-circle fa-3x"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
           </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    <a data-toggle="collapse" href="#kennel-sidebar-breed-dogs">
                        <i class="fa fa-chevron-circle-down"></i>
                        Breed Dogs
                    </a>
                </h3>
            </div>

            <div id="kennel-sidebar-breed-dogs" class="panel-collapse collapse">
                <div class="panel-body">
                    <form class="form" role="form" method="post" action="{{ route('user/kennel/dogs/breed/request') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="form-group">
                            <label for="breed-dogs-dog-id">Select Stud:</label>
                            <select name="dog" class="form-control" id="breed-dogs-dog-id" required>
                                @foreach($breedableDogs as $dog)
                                <option value="{{ $dog->id }}">{{ $dog->nameplate() }}</option>
                                @endforeach
                                
                                @if($breedableDogs->isEmpty())
                                <option value="">No eligible dogs</option>
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="breed-dogs-bitch-id">Select Bitch:</label>
                            <select name="bitch" class="form-control" id="breed-dogs-bitch-id" required>
                                @foreach($breedableBitches as $dog)
                                <option value="{{ $dog->id }}">{{ $dog->nameplate() }}</option>
                                @endforeach

                                @if($breedableBitches->isEmpty())
                                	<option value="">No eligible bitches</option>
                                @endif
                            </select>
                        </div>
                        <button type="submit" name="request_breeding" class="btn btn-success btn-block">Breed Dogs</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    <a data-toggle="collapse" href="#kennel-sidebar-stud-requests">
                        <i class="fa fa-chevron-circle-down"></i>
                        Stud Requests
                    </a>
                </h3>
            </div>

            <div id="kennel-sidebar-stud-requests" class="panel-collapse collapse">
                <div class="panel-body">
                    <form class="form" role="form" method="post" action="{{ route('user/kennel/stud_request/manage') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        @foreach($receivedStudRequests as $studRequest)
                        <div class="radio">
                            <input type="radio" name="stud_request" value="{{ $studRequest->id }}" />
                            
                            @if($studRequest->isInHeat())
                            <a data-toggle="tooltip" data-placement="top" title="In Heat">
                                <i class="fa fa-heart-o"></i>
                            </a>
                            @endif

                            Requesting {{ is_null($studRequest->bitch) ? '<em>Unknown</em>' : $studRequest->bitch->linkedNameplate() }} to {{ is_null($studRequest->stud) ? '<em>Unknown</em>' : $studRequest->stud->linkedNameplate() }}
                        </div>
                        @endforeach

                        @if($receivedStudRequests->isEmpty())
                        <p>You do not have any pending stud requests.</p>
                        @endif

                        <div class="btn-group btn-group-justified">
                            <div class="btn-group">
                                <button type="submit" name="manage" value="reject" class="btn btn-danger btn-block">Reject Request</button>
                            </div>
                            <div class="btn-group">
                                <button type="submit" name="manage" value="accept" class="btn btn-success btn-block">Accept Request</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    <a data-toggle="collapse" href="#kennel-sidebar-your-stud-requests">
                        <i class="fa fa-chevron-circle-down"></i>
                        Your Stud Requests
                    </a>
                </h3>
            </div>

            <div id="kennel-sidebar-your-stud-requests" class="panel-collapse collapse">
                <div class="panel-body">
                    <form class="form" role="form" method="post" action="{{ route('user/kennel/stud_request/manage') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        @foreach($sentStudRequests as $studRequest)
                        <div class="row">
                            <div class="col-xs-9">
                                <div class="radio">
                                    <input type="radio" name="stud_request" value="{{ $studRequest->id }}" />

                                    @if($studRequest->isInHeat())
                                    <a data-toggle="tooltip" data-placement="top" title="In Heat">
                                        <i class="fa fa-heart-o"></i>
                                    </a>
                                    @endif

                                    Your {{ is_null($studRequest->bitch) ? '<em>Unknown</em>' : $studRequest->bitch->linkedNameplate() }} to {{ is_null($studRequest->stud) ? '<em>Unknown</em>' : $studRequest->stud->linkedNameplate() }}
                                </div>
                            </div>
                            <div class="col-xs-3">
                                <div class="radio">
                                    @if($studRequest->isAccepted())
                                    <span class="text-success"><big><strong><abbr title="Accepted">A</abbr></strong></big></span>
                                    @endif

                                    @if($studRequest->isWaiting())
                                    <span class="text-danger"><big><strong><abbr title="Waiting">W</abbr></strong></big></span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach

                        @if($sentStudRequests->isEmpty())
                        <p>You do not have any pending stud requests.</p>
                        @endif

                        <div class="btn-group btn-group-justified">
                            <div class="btn-group">
                                <button type="submit" name="manage" value="remove" class="btn btn-danger btn-block">Remove Request</button>
                            </div>
                            <div class="btn-group">
                                <button type="submit" name="manage" value="breed" class="btn btn-success btn-block">Breed Dogs</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    <a data-toggle="collapse" href="#kennel-sidebar-actions">
                        <i class="fa fa-chevron-circle-down"></i>
                        Actions
                    </a>
                </h3>
            </div>

            <div id="kennel-sidebar-actions" class="panel-collapse collapse">
                <div class="panel-body">
                    <p>Please use the checkboxes on the right to select dogs to perform actions with.</p>

                    <form class="form" role="form" method="post" action="{{ route('user/kennel/dogs/move') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="dogs" value="" class="selected-dog-ids" />

                        <button type="button" class="btn btn-primary btn-block btn-sm" data-toggle="collapse" data-target="#kennel-actions-move">
                            Move to Tab
                        </button>

                        <div id="kennel-actions-move" class="collapse">
                            <div class="well well-sm">
                                <div class="form-group">
                                    <label for="kennel-actions-move-tab-id">Select Tab:</label>
                                    <select name="tab" class="form-control" id="kennel-actions-move-tab-id" required>
                                        @foreach($actionKennelGroups as $kennelGroup)
                                        <option value="{{ $kennelGroup->id }}">{{{ $kennelGroup->name }}}</option>
                                        @endforeach
                                        
                                        @if($actionKennelGroups->isEmpty())
                                        <option value="">No tabs</option>
                                        @endif
                                    </select>
                                </div>

                                <button type="submit" name="move_dog_to_tab" class="btn btn-success btn-block">
                                    Move All Selected Dogs
                                </button>
                            </div>    
                        </div>
                    </form>

                    <form class="form" role="form" method="post" action="{{ route('user/kennel/dogs/stud') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="dogs" value="" class="selected-dog-ids" />

                        <button type="button" class="btn btn-primary btn-block btn-sm" data-toggle="collapse" data-target="#kennel-actions-studding">
                            Manage Studding
                        </button>

                        <div id="kennel-actions-studding" class="collapse">
                            <div class="well well-sm">
                                <div class="form-group">
                                    <label for="kennel-actions-move-tab-id">Select Type:</label>
                                    @foreach(($studdingOptions = Dog::studdingOptions()) as $id => $text)
                                    <div class="radio">
                                        <label class="radio-inline">
                                            <input type="radio" name="stud_type" value="{{ $id }}" {{ ($id == $studdingOptions[0]) ? 'checked' : ''}} />
                                            {{ $text }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>

                                <button type="submit" name="manage_studding" class="btn btn-success btn-block">
                                    Apply to All Selected Dogs
                                </button>
                            </div>    
                        </div>
                    </form>

                    @if($kennel->isUpgraded())
                    <form class="form" role="form" method="post" action="{{ route('user/kennel/dogs/summary') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="dogs" value="" class="selected-dog-ids" />

                        <button type="button" class="btn btn-primary btn-block btn-sm" data-toggle="collapse" data-target="#kennel-actions-copy-summary">
                            Copy Summary
                        </button>

                        <div id="kennel-actions-copy-summary" class="collapse">
                            <div class="well well-sm">
                                <p class="text-center">You can copy this source dog's summary to any of the other dogs in your kennel. Please use checkboxes to select the destination dogs to copy to. <span class="text-warning"><strong>WARNING:</strong> Current summaries of destination dogs will be overwritten.</span></p>

                                <div class="form-group">
                                    <label for="kennel-actions-copy-summary-dog-id">Select Dog:</label>
                                    <select name="dog" class="form-control" id="kennel-actions-copy-summary-dog-id" required>
                                        @foreach($actionKennelGroupsWithDogs as $kennelGroup)
                                        <optgroup label="{{{ $kennelGroup->name }}}">
                                            @foreach($kennelGroup->dogs as $dog)
                                            <option value="{{ $dog->id }}">{{{ $dog->nameplate() }}}</option>
                                            @endforeach
                                        </optgroup>
                                        @endforeach

                                        @if($actionKennelGroupsWithDogs->isEmpty())
                                        <option value="">No dogs available</option>
                                        @endif
                                    </select>
                                </div>

                                <button type="submit" name="copy_dog_summary" class="btn btn-success btn-block" onclick="return confirm('Are you sure? Current summaries of all destination dogs will be overwritten.');">
                                    Copy to All Selected Dogs
                                </button>
                            </div>    
                        </div>
                    </form>
                    @endif

                    <form class="form" role="form" method="post" action="{{ route('user/kennel/dogs/pethome') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="dogs" value="" class="selected-dog-ids" />

                        <button type="button" class="btn btn-primary btn-block btn-sm" data-toggle="collapse" data-target="#kennel-actions-pethome">
                            Mass Pet Home
                        </button>

                        <div id="kennel-actions-pethome" class="collapse">
                            <div class="well well-sm">
                                <p class="text-center"><span class="text-warning"><strong>WARNING:</strong> This removes the dogs from your kennel and the game, although their information is retained in pedigrees. You will not be able to get them back. Dogs without traces in the game (i.e. no offspring, no contests, etc) will be deleted permanently.</span></p>

                                @if($currentUser->hasCompletedTutorialStage('first-breeding'))
                                <button type="submit" name="pet_home_dogs" class="btn btn-success btn-block" onclick="return confirm('Are you absolutely sure you want to send these dogs to a pet home? This removes the dogs from your kennel and the game, although its information is retained in pedigrees. You will not be able to get it back!');">
                                    Pet Home All Selected Dogs
                                </button>
                                @else
                                <span class="btn btn-success btn-block disabled">Please Complete Tutorial First</span>
                                @endif
                            </div>    
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    <a data-toggle="collapse" href="#kennel-sidebar-tab-settings">
                        <i class="fa fa-chevron-circle-down"></i>
                        Tab Settings
                    </a>
                </h3>
            </div>

            <div id="kennel-sidebar-tab-settings" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group">
                        <label for="kennel-tab-settings-tab-id">Select Tab:</label>
                        <select name="user_kennel_group_id" class="form-control" id="kennel-tab-settings-tab-id" required>
                            @foreach($kennelGroups as $kennelGroup)
                            <option value="{{ $kennelGroup->id }}">{{{ $kennelGroup->name }}}</option>
                            @endforeach

                            @if($kennelGroups->isEmpty())
                            <option value="">No tabs</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="button" name="edit_tab" class="btn btn-success btn-block">Edit</button>
                    </div>

                    @foreach($kennelGroups as $kennelGroup)
                    <div id="kennel-tab-settings-tab-{{ $kennelGroup->id }}-info" class="kennel-tab-settings-tab hide well well-sm">

                        <form class="form" role="form" method="post" action="{{ route('user/kennel/group/update', $kennelGroup->id) }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="form-group">
                                <label for="kennel-tab-settings-tab-{{ $kennelGroup->id }}-info-name">Name:</label>
                                @if($kennelGroup->canBeEdited())
                                <input type="text" name="name" class="form-control" id="kennel-tab-settings-tab-{{ $kennelGroup->id }}-info-name" value="{{{ $kennelGroup->name }}}" />
                                @else
                                <p class="form-control-static">{{{ $kennelGroup->name }}}</p>
                                @endif
                            </div>

                            @if($kennelGroup->canBeEdited())
                            <div class="form-group">
                                <label for="kennel-tab-settings-tab-{{ $kennelGroup->id }}-info-description">Description:</label>
                                <textarea name="description" class="form-control" id="kennel-tab-settings-tab-{{ $kennelGroup->id }}-info-description">{{{ $kennelGroup->description }}}</textarea>
                            </div>
                            @endif

                            <div class="form-group">
                                <label for="kennel-tab-settings-tab-{{ $kennelGroup->id }}-info-order-by">Order By:</label>
                                <select name="dog_order" class="form-control" id="kennel-tab-settings-tab-{{ $kennelGroup->id }}-info-order-by" required>
                                    @foreach(KennelGroup::getDogOrders() as $index => $name)
                                    <option value="{{ $index }}" {{ $index == $kennelGroup->dog_order_id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group text-right">
                                @if($kennelGroup->canBeDeleted())
                                <a href="{{ route('user/kennel/group/delete', $kennelGroup->id) }}" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete that tab?');">Delete</a>
                                @endif
                                <button type="submit" name="edit_tab" class="btn btn-success">Save</button>
                            </div>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar col-md-1 collapse hidden-sm hidden-xs kennel-show-sidebar kennel-show-sidebar-bar">
        <div class="kennel-show-sidebar-text">
            Open Sidebar
        </div>
    </div>
    @endif

    <div class="content col-md-{{ ($currentUser->id == $kennel->id) ? 6 : 9 }}" id="content">
        <div class="panel panel-default">
            <div class="panel-body">

                <div class="hidden-sm hidden-xs">
                    <!-- Notifications -->
                    @include('frontend/notifications/basic')

                    @if($currentUser->inTutorial())
                    <p class="clearfix">
                        <button class="btn btn-danger pull-right" data-toggle="modal" data-target="#tutorial-current-stage">
                            View Current Tutorial Step
                        </button>
                    </p>
                    @endif
                </div>

                <!-- Content -->
                @yield('content')
            </div>
        </div>
    </div>

    <div class="sidebar col-md-3 col-sm-12 col-sx-12" id="main-sidebar">
        @include('frontend/layouts/_sidebar')
    </div>
</div>

{{-- JS assets --}}
@section('js_assets')
@parent
<script type="text/javascript" src="{{ asset('assets/js/kennel.js') }}"></script>
@stop

{{-- Modals --}}
@section('modals')
@parent
<!-- Beginners luck modal -->
@include('frontend/modals/beginners_luck')

<!-- Mass test results modal -->
@include('frontend/modals/mass_test_results')

<!-- Dog comparison modal -->
@include('frontend/modals/compare_dogs')

<div class="modal fade" id="new-group-cash-shop" tabindex="-1" role="dialog" aria-labelledby="new-group-cash-shop-labal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p>Sorry, you need to have an upgraded account in order to create unlimited tabs. Please visit the cash shop to upgrade your account.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <a href="{{ route('cash_shop') }}" class="btn btn-primary">Go to the Cash Shop</a>
            </div>
        </div>
    </div>
</div>
@stop

@include('frontend/layouts/_footer')

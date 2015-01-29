@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>{{{ $dog->fullName() }}} (#{{ $dog->id}})</h1>
</div>

@if($dog->isPetHomed())
<div class="alert alert-purple text-center">
    <big><strong>LIVES IN A PET HOME</strong></big>
</div>
@endif

@if($dog->isDeceased())
<div class="alert alert-danger text-center">
    <big><strong>DECEASED</strong></big>
</div>
@endif

@if($dog->isForStud())
<div class="alert alert-danger text-center">
    <big><strong>FOR {{ $dog->isForImmediateStud() ? 'IMMEDIATE' : 'REQUEST' }} STUD</strong></big>
</div>
@endif

@if($dog->isPregnant())
<div class="alert alert-warning text-center">
    <big><strong>PREGNANT</strong></big>
</div>
@elseif($dog->isInHeat())
<div class="alert alert-warning text-center">
    <big><strong>IN HEAT</strong></big>
</div>
@endif

@if($dog->isFemale() and $dog->hasBeginnersLuckRequests())
    <div class="alert alert-info text-center">
        <big><strong>BEGINNER'S LUCK REQUESTS</strong></big>
        <ul class="list-unstyled">
            @foreach($dog->beginnersLuckRequests as $request)
            <li>
                Beginner's Luck request to 
                {{ (is_null($request->beginner) ? '<em>Unkown</em>' : $request->beginner->linkedNameplate()) }} 
                for breeding with 
                {{ (is_null($request->dog) ? '<em>Unkown</em>' : $request->dog->linkedNameplate()) }}. 

                @if($currentUser->ownsDog($dog))
                <a href="{{ route('dog/blr/revoke', $request->id) }}">Revoke?</a>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
@endif


<div class="container-fluid">
    <div class="row">
        @if( ! is_null($image = $dog->getImageUrl()))
        <div class="col-md-5">
            <a href="{{{ $image }}}">
                <img src="{{{ $image }}}" class="img-responsive center-block" alt="Dog Image" title="Dog Image" />
            </a>
        </div>
        @endif
        <div class="col-md-{{ is_null($image) ? 12 : 7  }}">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-5">
                            <h3 class="panel-title">
                                General Info
                            </h3>
                        </div>
                        <div class="col-xs-4">
                            @if( ! is_null($previous))
                            <a href="{{ route('dog/profile', $previous->id) }}">
                                <i class="fa fa-backward pull-none"></i>
                                Previous Dog
                            </a>
                            @endif
                        </div>
                        <div class="col-xs-3 text-right">
                            @if( ! is_null($next))
                            <a href="{{ route('dog/profile', $next->id) }}">
                                Next Dog
                                <i class="fa fa-forward pull-none"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">


                            <div class="condensed form-horizontal">
                                <div class="form-group">
                                    <label class="col-sm-4 col-xs-3 control-label">Name:</label>
                                    <div class="col-sm-8 col-xs-9">
                                        @if($currentUser->ownsDog($dog) and $dog->isAlive())
                                        <button class="btn btn-primary btn-xs" data-toggle="modal" data-target="#change-name">{{{ $dog->fullName() }}}</button>
                                        @else
                                        <p class="form-control-static">{{{ $dog->fullName() }}}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 col-xs-3 control-label">Owner:</label>
                                    <div class="col-sm-8 col-xs-9">
                                        <p class="form-control-static">{{ $dog->isPetHomed() ? 'Pet Home' : $dog->owner->linkedNameplate() }}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 col-xs-3 control-label">Breeder:</label>
                                    <div class="col-sm-8 col-xs-9">
                                        @if($dog->isImported())
                                        <p class="form-control-static">Imported</p>
                                        @elseif($dog->isBred() and $dog->hasBreeder())
                                        <p class="form-control-static">{{ $dog->breeder->linkedNameplate() }}</p>
                                        @else
                                        <p class="form-control-static"><em>Unknown</em></p>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 col-xs-3 control-label">Age:</label>
                                    <div class="col-sm-8 col-xs-9">
                                        <p class="form-control-static">{{ $dog->getAgeInYearsAndMonths() }}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 col-xs-3 control-label">Sex:</label>
                                    <div class="col-sm-8 col-xs-9">
                                        <p class="form-control-static">{{ $dog->sex->name }}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 col-xs-3 control-label">Breed:</label>
                                    <div class="col-sm-8 col-xs-9">
                                        @if($dog->isRegistered())
                                            {{ $dog->breed->name }}
                                            @if($currentUser->ownsDog($dog))
                                            <button class="btn btn-primary btn-xs" data-toggle="modal" data-target="#register-breed">Change Registration</button>
                                            @endif
                                        @elseif($currentUser->ownsDog($dog) and $dog->isAlive())
                                        <button class="btn btn-primary btn-xs" data-toggle="modal" data-target="#register-breed">Register Dog</button>
                                        @else
                                        <p class="form-control-static">Unregistered</p>
                                        @endif
                                    </div>
                                </div>
                                @if( ! is_null($dog->breed) and $dog->breed->isExtinctable())
                                <div class="form-group">
                                    <label class="col-sm-4 col-xs-3 control-label">Active Breed Member:</label>
                                    <div class="col-sm-8 col-xs-9">
                                        <p class="form-control-static">{{ $dog->isActiveBreedMember() ? 'Yes' : 'No' }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($currentUser->ownsDog($dog) and $dog->isAlive())
                            <form role="form" method="post" action="{{ route('dog/profile/save_notes', $dog->id) }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <label class="sr-only">Notes</label>
                                    <textarea name="notes" class="form-control" rows="3">{{{ $dog->notes }}}</textarea>
                                </div>
                                <button name="save_notes" type="submit" class="btn btn-primary btn-xs pull-right">Save Notes</button>
                            </form>
                            @else
                            {{{ $dog->notes }}}
                            @endif
                        </div>
                    </div>

                    <br />

                    @if($dog->isAlive())
                        @if($dog->isMale() and ($dog->isForStud() or $currentUser->ownsDog($dog)))
                            <div class="row">
                            <div class="col-md-12">
                                <form class="form-inline" role="form" method="post" action="{{ route('dog/breed/request', $dog->id) }}">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <div class="form-group">
                                        <label class="sr-only">Breed With:</label>
                                        <select name="bitch_to_breed_with" class="form-control input-xs" required>
                                            @foreach($breedableBitches as $bitch)
                                            <option value="{{ $bitch->id }}" {{ Input::old('bitch_to_breed_with') == $bitch->id ? 'selected' : '' }}>
                                                {{{ $bitch->nameplate() }}}
                                            </option>
                                            @endforeach

                                            @if( ! count($breedableBitches))
                                            <option value="">No bitches available to breed</option>
                                            @endif
                                        </select>
                                    </div>
                                    <button type="submit" name="request_breeding" class="btn btn-primary btn-xs">Breed With</button>
                                </form>
                            </div>
                        </div>

                        <br />
                        @endif

                        @if($currentUser->ownsDog($dog))
                        <div class="row">
                            <div class="col-md-12">
                                <div class="btn-group btn-group-justified">
                                    <div class="btn-group">
                                        <button class="btn btn-primary btn-xs" data-toggle="modal" data-target="#change-image">Change Image</button>
                                    </div>

                                    @if( ! $dog->hasKennelPrefix())
                                    <div class="btn-group">
                                        <a href="{{ route('dog/profile/add_prefix', $dog->id) }}" class="btn btn-primary btn-xs" onclick="return confirm('It will cost {{ Dynasty::credits(Config::get('game.dog.prefix_cost')) }} to prefix this dog. Are you sure you want to add a prefix?');">Add Prefix</a>
                                    </div>
                                    @endif

                                    @if($currentUser->hasCompletedTutorialStage('first-breeding'))
                                    <div class="btn-group">
                                        @if($dog->isPendingOwnership())
                                            @if($dog->isLentOut())
                                            <a class="btn btn-primary btn-xs" href="{{ route('dog/lend/return', $dog->lendRequest->id) }}">Send Back</a>
                                            @else
                                            <span class="btn btn-primary btn-xs disabled">Dog Sent (Pending)</span>
                                            @endif
                                        @else
                                        <button class="btn btn-primary btn-xs" data-toggle="modal" data-target="#send-to-player">Send to Player</button>
                                        @endif
                                    </div>

                                    <div class="btn-group">
                                        <a href="{{ route('dog/pet_home', $dog->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('Are you absolutely sure you want to send the dog to a pet home? This removes the dog from your kennel and the game, although its information is retained in pedigrees. You will not be able to get it back.');" data-toggle="tooltip" data-placement="top" title="Permanently remove this dog from your kennel; will not affect pedigrees.">Pet Home <i class="fa fa-question-circle"></i></a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<hr />

@if($dog->isIncomplete() and $dog->isAlive() and $currentUser->ownsDog($dog))
<div class="alert alert-warning text-center">
    <big><strong>INCOMPLETE</strong></big>
</div>

<div class="well well-sm">
    <p class="text-center text-warning">
        You need to complete this dog to reveal all of its characteristics. This will not use any turns.
    </p>

    <p class="text-center">
        <a class="btn btn-default btn-lg btn-block btn-warning" href="{{ route('dog/profile/complete', $dog->id) }}" data-loading-text="<i class='fa fa-cog fa-spin'></i> Completing...">
            <i class="fa fa-check"></i> Complete Dog
        </a>
    </p>
</div>
@elseif($dog->isComplete())
<div class="panel panel-default">
    <div class="panel-heading panel-nav clearfix">
        <ul class="nav nav-pills">
            @if($dog->hasOwner() and $dog->owner->isUpgraded())
            <li {{ Input::get('view', ($dog->owner->isUpgraded() ? 'summary' : null)) == 'summary' ? 'class="active"' : '' }}><a href="#summary" data-toggle="tab">Summary</a></li>
            @endif

            @if($currentUser->ownsDog($dog) and $dog->isMale() and $dog->isBreedable())
            <li class="pull-right {{ $activeTab == 'studding' ? 'active' : '' }}"><a href="#studding" data-toggle="tab">Studding</a></li>
            @endif

            <li class="pull-right {{ ($activeTab == 'offspring') ? 'active' : '' }}"><a href="#offspring" data-toggle="tab">Offspring</a></li>

            @if($dog->hasPedigree())
            <li class="pull-right {{ ($activeTab == 'pedigree') ? 'active' : '' }}"><a href="#pedigree" data-toggle="tab">Pedigree</a></li>
            @endif

            <li class="pull-right {{ ($activeTab == 'contests') ? 'active' : '' }}"><a href="#contests" data-toggle="tab">Contest Results</a></li>

            @foreach($characteristicCategories as $category)
            <li class="pull-right {{ ($activeTab == 'charcat'.$category['id']) ? 'active' : '' }}"><a href="#tabs_charcat_{{ $category['id']}}" data-toggle="tab">{{ $category['name'] }}</a></li>
            @endforeach
        </ul>
    </div>

    <!-- Tab panes -->
    <div class="tab-content panel-body">
        @if($dog->hasOwner() and $dog->owner->isUpgraded())
        <div class="tab-pane {{ $activeTab == 'summary' ? 'active' : '' }}" id="summary">

            <div class="row">
                @foreach($summarizedCategories as $category)
                <div class="col-md-{{ $category['column'] }}" data-dog-char-parent="summary">
                    <div class="panel panel-grey">
                        <div class="panel-heading clearfix">
                            <h3 class="panel-title">
                                {{ $category['parent_name'] }}: {{ $category['name'] }}
                            </h3>
                        </div>
                        <div class="panel-body">
                            <div class="container-fluid">
                                @foreach($category['characteristics'] as $dogCharacteristic)
                                <div class="row">
                                    <div class="col-md-5">
                                        @if($currentUser->ownsDog($dog) and $dog->isAlive())
                                            <a class="btn btn-link btn-xs text-danger" href="{{ route('dog/profile/summary/remove', $dogCharacteristic->id) }}">
                                                <i class="fa fa-times text-danger pull-left"></i>
                                            </a>
                                        @endif

                                        @if($dogCharacteristic->characteristic->hasHelpPage())
                                        <a href="{{ route('help/page', $dogCharacteristic->characteristic->helpPage->id) }}">
                                            <strong>{{ $dogCharacteristic->characteristic->name }}</strong>
                                        </a>
                                        @else
                                        <strong>{{ $dogCharacteristic->characteristic->name }}</strong>
                                        @endif

                                        <a data-toggle="tooltip" data-placement="top" title="{{{ $dogCharacteristic->characteristic->description }}}">
                                            <i class="fa fa-question-circle"></i>
                                        </a>
                                    </div>
                                    <div class="col-md-7">
                                        @include('frontend/dog/_characteristic', ['dogCharacteristic' => $dogCharacteristic, 'showTests' => true, 'message' => ''])
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                @if( ! count($summarizedCategories))
                <div class="col-md-12">
                    <p>No characteristics have been added to this Summary.</p>
                </div>
                @endif
            </div>

            @if($currentUser->ownsDog($dog))
            <div class="callout callout-info no-margin">
                Don't want to build this Summary from scratch? Use the <b>Copy Summary</b> function in the <b>Actions</b> tab on your <a href="{{ route('user/kennel') }}">kennel page</a> to copy another dog's Summary to this dog.
            </div>

            <hr />

            <form class="form-horizontal" role="form" method="post" action="{{ route('dog/profile/summary/add', $dog->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <div class="form-group">
                    <label for="summary-characteristics" class="col-sm-2 control-label">Characteristic</label>
                    <div class="col-sm-10">
                        <select name="characteristics_to_summarize[]" class="form-control" id="summary-characteristics" size="5" multiple>
                            @foreach($summaryCharacteristicCategories as $characteristicCategory)
                            <optgroup label="{{ $characteristicCategory->parent->name }}: {{ $characteristicCategory->name }}">
                                @foreach($characteristicCategory->characteristics as $characteristic)
                                <option value="{{ $characteristic->id }}">{{ $characteristic->name }}</option>
                                @endforeach
                            </optgroup>
                            @endforeach

                            @if($summaryCharacteristicCategories->isEmpty())
                            <option value="">No characteristics available</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2 text-right">
                        <button type="submit" name="add_to_summary" class="btn btn-primary" data-loading-text="<i class='fa fa-cog fa-spin'></i> Adding...">Add to Summary</button>
                    </div>
                </div>
            </form>

            @endif
        </div>
        @endif

        @if($dog->isMale() and $currentUser->ownsDog($dog) and $dog->isBreedable())
        <div class="tab-pane {{ $activeTab == 'studding' ? 'active' : '' }}" id="studding">
            <form class="form-horizontal" role="form" method="post" action="{{ route('dog/profile/manage_studding', $dog->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="form-group">
                    <label class="col-sm-2 control-label">Put Up for Stud?</label>
                    <div class="col-sm-10">
                        <div class="radio">
                            <label class="radio-inline">
                                <input type="radio" name="up_for_stud" value="no" {{ Input::old('up_for_stud', ($dog->isForStud() ? 'yes' : 'no')) == 'no' ? 'checked' : '' }} />
                                No
                            </label>
                        </div>

                        <div class="radio">
                            <label class="radio-inline">
                                <input type="radio" name="up_for_stud" value="yes" {{ Input::old('up_for_stud', ($dog->isForStud() ? 'yes' : 'no')) == 'yes' ? 'checked' : '' }} />
                                Yes
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Type</label>
                    <div class="col-sm-10">
                        <div class="radio">
                            <label class="radio-inline">
                                <input type="radio" name="stud_type" value="immediate" {{ Input::old('stud_type', ($dog->isForImmediateStud() ? 'immediate' : null)) == 'immediate' ? 'checked' : '' }} />
                                Immediate
                            </label>
                        </div>

                        <div class="radio">
                            <label class="radio-inline">
                                <input type="radio" name="stud_type" value="request" {{ Input::old('stud_type', ($dog->isForRequestStud() ? 'request' : null)) == 'request' ? 'checked' : '' }} />
                                Request
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" name="save_studding" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
        @endif

        <div class="tab-pane {{ $activeTab == 'offspring' ? 'active' : '' }}" id="offspring">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Parent</th>
                        <th>Gender</th>
                        <th>Breeder</th>
                        <th>Age</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($offspring as $puppy)
                    <tr>
                        <td>{{ $puppy->linkedNameplate() }}</td>
                        <td>
                            @if($puppy->litter->dam_id == $dog->id)
                            {{ ! is_null($puppy->litter->sire) ? $puppy->litter->sire->linkedNameplate() : '<em>Unknown</em>' }}
                            @else
                            {{ ! is_null($puppy->litter->dam) ? $puppy->litter->dam->linkedNameplate() : '<em>Unknown</em>' }}
                            @endif
                        </td>
                        <td>{{ $puppy->sex->name }}</td>
                        <td>
                            {{ $puppy->hasBreeder() ? $puppy->breeder->linkedNameplate() : '<em>Unknown</em>' }}
                        </td>
                        <td>{{ $puppy->getAgeInYearsAndMonths() }}</td>
                        <td>
                            @if($puppy->isDeceased())
                            Passed Away
                            @elseif($puppy->isPetHomed())
                            Pet Homed
                            @else
                            Alive
                            @endif
                        </td>
                    </tr>
                    @endforeach

                    @if( ! count($offspring))
                    <tr>
                        <td colspan="6">No offspring to show</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if($dog->hasPedigree())
        <div class="tab-pane {{ $activeTab == 'pedigree' ? 'active' : '' }}" id="pedigree">
            <div class="row">
                <div class="col-md-3">
                    <strong>Coefficient of Inbreeding:</strong>
                </div>
                <div class="col-md-9">
                    {{ $dog->coi }}
                </div>
            </div>
            <table class="table table-condensed table-responsive">
                <tbody>
                @foreach($pedigreeSlots as $slot)
                    <tr class="{{ ($slot['type'] == Pedigree::DAM) ? 'dog-pedigree-dam' : 'dog-pedigree-sire info' }}">
                        <td rowspan="{{ $slot['rowspan'] }}">
                            @if( ! is_null($slot['ancestor']))
                                <p>{{ $slot['ancestor']->linkedNameplate() }}</p>
                                <p>{{ $slot['ancestor']->hasBreed() ? $slot['ancestor']->breed->name : '<em>Unregistered</em>' }}</p>
                                @if($slot['ancestor']->hasBreeder())
                                <p><small>Breeder: {{ $slot['ancestor']->breeder->linkedNameplate() }}</small></p>
                                @endif
                            @else
                                <em>Unknown</em>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="tab-pane {{ $activeTab == 'contests' ? 'active' : '' }}" id="contests">
            <div class="row">
                <div class="col-xs-4">
                    <div class="well well-sm well-info">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-5 text-right">
                                    <h2 class="no-margin">{{ number_format($dog->small_contest_wins) }}</h2>
                                </div>
                                <div class="col-xs-6 no-padding">
                                    Total {{ Str::plural('Win', $dog->small_contest_wins) }} in Small Contests
                                </div>
                                <div class="col-xs-1 text-right no-padding">
                                    <a data-toggle="tooltip" data-html="true" data-placement="right" title="{{ Config::get('game.contest.size_min_small') }}-{{ Config::get('game.contest.size_min_medium') - 1 }}">
                                        <i class="fa fa-question-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-4">
                    <div class="well well-sm well-success">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-5 text-right">
                                    <h2 class="no-margin">{{ number_format($dog->medium_contest_wins) }}</h2>
                                </div>
                                <div class="col-xs-6 no-padding">
                                    Total {{ Str::plural('Win', $dog->medium_contest_wins) }} in Medium Contests
                                </div>
                                <div class="col-xs-1 text-right no-padding">
                                    <a data-toggle="tooltip" data-html="true" data-placement="right" title="{{ Config::get('game.contest.size_min_medium') }}-{{ Config::get('game.contest.size_min_large') - 1 }}">
                                        <i class="fa fa-question-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-4">
                    <div class="well well-sm well-warning">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-5 text-right">
                                    <h2 class="no-margin">{{ number_format($dog->large_contest_wins) }}</h2>
                                </div>
                                <div class="col-xs-6 no-padding">
                                    Total {{ Str::plural('Win', $dog->large_contest_wins) }} in Large Contests
                                </div>
                                <div class="col-xs-1 text-right no-padding">
                                    <a data-toggle="tooltip" data-html="true" data-placement="right" title="{{ Config::get('game.contest.size_min_large') }}+">
                                        <i class="fa fa-question-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <h4>Recent Contests - Last 30 Days</h4>

            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Name</th>
                        <th>Run Date</th>
                        <th colspan="3">Judging Requirements</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentContestEntries as $entry)
                        @if($entry->hasWon())
                        <tr {{ $entry->contest->isSmall() ? 'info' : ( $entry->contest->isMedium() ? 'success' : 'warning' ) }}>
                        @else
                        <tr>
                        @endif
                        <td>
                            {{ $entry->rank ? $entry->rank : 'N/A' }}
                        </td>
                        <td>
                            {{{ $entry->contest->name }}}

                            @if($entry->contest->isSmall())
                            <strong>(S)</strong>
                            @elseif($entry->contest->isMedium())
                            <strong>(M)</strong>
                            @elseif($entry->contest->isLarge())
                            <strong>(L)</strong>
                            @endif

                            <br />

                            <small><em>Type: {{{ $entry->contest->type_name }}}</em></small>
                        </td>
                        <td>{{ $entry->contest->run_on->format('M. j') }}</td>

                        @foreach($entry->contest->requirements as $requirement)
                        <td>
                            {{ $requirement->characteristic->name }}: {{ $requirement->getType() }}
                        </td>
                        @endforeach

                        <td>
                            <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#contest-entry-{{ $entry->id }}-entries">
                                <i class="fa fa-chevron-down"></i>
                            </button>
                        </td>
                    </tr>
                    <tr class="collapse" id="contest-entry-{{ $entry->id }}-entries">
                        <td colspan="7">
                            <table class="table table-condensed table-hover">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Dog</th>
                                        @foreach($entry->contest->requirements as $requirement)
                                        <th>{{ $requirement->characteristic->name }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($entry->contest->entries()->orderBy('rank', 'asc')->get() as $otherEntry)
                                    <tr {{ $otherEntry->dog_id == $dog->id ? 'class="active"'  : '' }}>
                                        <td>
                                            {{ $otherEntry->rank ? $otherEntry->rank : 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $otherEntry->dog->linkedNameplate() }}
                                        </td>
                                        @foreach($entry->contest->requirements as $requirement)
                                        <td>{{ ! is_null($dogCharacteristic = $otherEntry->dog->characteristics()->whereCharacteristic($requirement->characteristic->id)->first()) ? $dogCharacteristic->formatRangedValue() : '<em>Unknown</em>' }}</td>
                                        @endforeach
                                    </tr>
                                    @endforeach

                                    @if( ! count($entry->contest->entries))
                                    <tr>
                                        <td colspan="100%">
                                            No dogs have entered
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    @endforeach

                    @if( ! count($recentContestEntries))
                    <tr>
                        <td colspan="7">No recent contest results to display</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @foreach($characteristicCategories as $category)
        <div class="tab-pane {{ ($activeTab == 'charcat'.$category['id']) ? 'active' : '' }}" id="tabs_charcat_{{ $category['id'] }}">
            <div class="container-fluid">
                @if($category['is_health'])
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-grey no-margin">
                            <div class="panel-heading clearfix">
                                <h3 class="panel-title">
                                    Current Health Problems
                                </h3>
                            </div>
                            <div class="panel-body">
                                <div class="container-fluid">
                                    <ul>
                                        @foreach($symptoms as $symptom)
                                        <li>{{ $symptom->characteristicSeveritySymptom->symptom->name }}</li>
                                        @endforeach

                                        @if( ! count($symptoms))
                                        <li>None</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="callout callout-warning">
                    <p class="text-center">The list of disorders below is specific to the things your dog may have or carry. Different dogs have differing disorder lists.</p>
                </div>
                @endif
                
                <div class="row">
                    @foreach($category['subcategories'] as $subcategory)
                    <div class="col-md-{{ $category['column'] }}" data-dog-char-parent="{{ $category['id'] }}">
                        <div class="panel panel-grey">
                            <div class="panel-heading clearfix">
                                <h3 class="panel-title">
                                    {{ $subcategory['name'] }}
                                </h3>
                            </div>
                            <div class="panel-body">
                                <div class="container-fluid">
                                    @foreach($subcategory['characteristics'] as $dogCharacteristic)
                                    <div class="row">
                                        <div class="col-md-5">
                                            @if($dogCharacteristic->characteristic->hasHelpPage())
                                            <a href="{{ route('help/page', $dogCharacteristic->characteristic->helpPage->id) }}">
                                                <strong>{{ $dogCharacteristic->characteristic->name }}</strong>
                                            </a>
                                            @else
                                            <strong>{{ $dogCharacteristic->characteristic->name }}</strong>
                                            @endif

                                            <a data-toggle="tooltip" data-placement="top" title="{{{ $dogCharacteristic->characteristic->description }}}">
                                                <i class="fa fa-question-circle"></i>
                                            </a>
                                        </div>

                                        <div class="col-md-7">
                                            @include('frontend/dog/_characteristic', ['dogCharacteristic' => $dogCharacteristic, 'showTests' => true, 'message' => ''])
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="modal fade" id="change-name" tabindex="-1" role="dialog" aria-labelledby="change-name-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post" action="{{ route('dog/profile/change_name', $dog->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="changeNameName">Name</label>
                        @if($dog->hasKennelPrefix())
                        <div class="input-group input-group-lg">
                            <span class="input-group-addon">{{{ $dog->kennel_prefix }}}</span>
                            <input type="text" name="new_name" class="form-control input-lg" id="changeNameName" placeholder="Enter name" value="{{{ Input::old('new_name', $dog->name) }}}" maxlength="32" required />
                        </div>
                        @else
                        <input type="text" name="new_name" class="form-control input-lg" id="changeNameName" placeholder="Enter name" value="{{{ Input::old('new_name', $dog->name) }}}" maxlength="32" required />
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" name="change_name">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="change-image" tabindex="-1" role="dialog" aria-labelledby="change-image-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post" action="{{ route('dog/profile/change_image', $dog->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="changeImageImageURL">Display options</label>
                        @foreach($displayImageOptions as $id => $option)
                        <div class="radio">
                            <label class="radio-inline">
                                <input type="radio" name="display_image_option" value="{{ $id }}" {{ Input::old('display_image_option', $dog->display_image) == $id ? 'checked' : '' }}/> 
                                {{ $option }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                    <div class="form-group">
                        <label for="changeImageImageURL">Image URL</label>
                        <input type="text" name="image_url" class="form-control input-lg" id="changeImageImageURL" placeholder="Enter URL" value="{{{ $dog->image_url }}}" maxlength="255" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" name="change_image">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="register-breed" tabindex="-1" role="dialog" aria-labelledby="register-breed-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post" action="{{ route('dog/profile/change_breed', $dog->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-body">
                    <p> Please select a breed to register your dog as. Your dog must fit the breed standard for that breed in order to be eligible for registration.</p>

                    <p>If your dog is Unregistered, you can register it for free. Every registered dog can also change its breed registration once, for free. Every breed registration change after that costs {{ Dynasty::credits(Config::get('game.dog.change_breed_cost')) }}.</p>

                    <p class="text-center">
                        <strong>Next Registration Change:</strong>
                        @if($dog->hasHadBreedChanged())
                        {{ Dynasty::credits(Config::get('game.dog.change_breed_cost')) }}
                        @else
                        Free
                        @endif
                    </p>
                    
                    <div class="form-group">
                        <label for="changeImageImageURL">Choose a breed</label>
                        <select name="new_breed" class="form-control" id="registerBreedBreedID" required>
                            @foreach($changeableBreeds as $breed)
                            <option value="{{ $breed->id }}" {{ Input::old('new_breed') == $breed->id ? 'selected' : '' }}>
                                {{{ $breed->name }}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" name="register_breed">Register</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="send-to-player" tabindex="-1" role="dialog" aria-labelledby="send-to-player-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post" action="{{ route('dog/lend', $dog->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="sendToPlayerUserId">Send to</label>

                        <div class="input-group input-group-lg">
                            <span class="input-group-addon">#</span>
                            <input type="text" name="user" class="form-control" id="sendToPlayerUserId" value="" placeholder="Player ID" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="sendToPlayerLendType">For how long?</label>

                        <div class="input-group input-group-lg">
                            <span class="input-group-addon">#</span>
                            <select name="length_of_lending_period" class="form-control" id="sendToPlayerLendType" required>
                                @foreach($lendingOptions as $id => $option)
                                <option value="{{ $id }}" {{ Input::old('length_of_lending_period') ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" name="send_to_user">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

{{-- Modals --}}
@section('modals')
@parent
<!-- Beginners luck modal -->
@include('frontend/modals/beginners_luck')
@stop

{{-- JS assets --}}
@section('js_assets')
@parent
<script type="text/javascript" src="{{ asset('assets/js/dog.js') }}"></script>
@stop

@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>{{{ $kennel->kennel_name }}}</h1>
</div>

@if(strlen($kennel->kennel_description))
<div class="well">
    {{ $kennel->kennel_description }}
</div>
@endif

<div class="panel panel-default">
    <div class="panel-heading panel-nav clearfix">
        <ul class="nav nav-pills nav-horizontal-scroll">
            @foreach($kennelGroups as $kennelGroup)
            <li {{ $kennelGroup->id == $kennelGroups->first()->id ? 'class="active"' : '' }}><a href="#kennel-tab-{{ $kennelGroup->id }}" data-toggle="tab">{{{ $kennelGroup->name }}}</a></li>
            @endforeach

            @if($kennel->id == $currentUser->id)
            <li>
                @if( ! $kennel->canAddNewKennelGroup())
                <a href="#" data-toggle="modal" data-target="#new-group-cash-shop">
                    <i class="fa fa-plus"></i>
                </a>
                @else
                <a href="{{ route('user/kennel/group/add') }}">
                    <i class="fa fa-plus"></i>
                </a>
                @endif
            </li>
            @endif
        </ul>
    </div>

    <!-- Tab panes -->
    <div class="tab-content panel-body">
        @foreach($kennelGroups as $kennelGroup)
        <div class="tab-pane {{ $kennelGroup->id == $kennelGroups->first()->id ? 'active' : '' }}" id="kennel-tab-{{ $kennelGroup->id }}">
            @if($kennel->id == $currentUser->id)
            <p><input type="checkbox" name="check-all-ids[]" onclick="check_all(this, '[name$=&quot;ids[]&quot;]', false);"/> <strong>Toggle Selected Dogs in All Tabs</strong></p>
            @endif

            @if(strlen($kennelGroup->description))
            <div class="well">
                {{ $kennelGroup->description }}
            </div>
            @endif

            <h3>Dogs</h3>
            <div class="table-responsive">
                <table class="table table-condensed table-striped">
                    <thead>
                        <tr>
                            @if($kennel->id == $currentUser->id)
                            <th class="col-lg-1"><input type="checkbox" name="check-all-dog-ids[]" onclick="check_all(this, '[name=&quot;kennel-tab-{{ $kennelGroup->id }}-dog-ids[]&quot;]', false);"/></th>
                            @endif
                            <th>Name</th>
                            <th class="col-lg-3">Breed</th>
                            <th class="col-lg-2">Age</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($dogs = $kennelGroup->dogs()->whereMale()->whereAdult()->orderByKennelGroup($kennelGroup)->get()) as $dog)
                        <tr>
                            @if($kennel->id == $currentUser->id)
                            <td><input type="checkbox" name="kennel-tab-{{ $kennelGroup->id }}-dog-ids[]" class="kennel_dog_checkbox" value="{{ $dog->id }}" /></td>
                            @endif
                            <td>
                                <a href="{{ route('dog/profile', $dog->id) }}">
                                    <span {{ $dog->isWorked() ? 'class="text-muted"' : '' }}>{{{ $dog->fullName() }}} (#{{ $dog->id }})</span>
                                </a>

                                 @if( ! $kennelGroup->isCemetery())
                                    @if($dog->isComplete())
                                        @if( ! $dog->isSexuallyMature())
                                        <a data-toggle="tooltip" data-placement="top" title="Not Sexually Mature"><i class="fa fa-leaf"></i></a>
                                        @endif

                                        @if($dog->isForStud())
                                        <a data-toggle="tooltip" data-placement="top" title="For Stud"><i class="fa fa-fire"></i></a>
                                        @endif

                                        @if($dog->isInfertile())
                                        <a data-toggle="tooltip" data-placement="top" title="Infertile"><i class="fa fa-stethoscope"></i></a>
                                        @endif

                                        @if($dog->isUnhealthy())
                                        <a data-toggle="tooltip" data-placement="top" title="Unhealthy"><i class="fa fa-bug"></i></a>
                                        @endif
                                    @else
                                    <a data-toggle="tooltip" data-placement="top" title="Incomplete"><i class="fa fa-circle-o-notch"></i></a>
                                    @endif
                                @endif

                                @if($dog->hasLitter())
                                <br />
                                <small><em>{{ is_null($dog->litter->sire) ? 'Unknown' : $dog->litter->sire->linkedNameplate() }} x {{ is_null($dog->litter->dam) ? 'Unknown' : $dog->litter->dam->linkedNameplate() }}</em></small>
                                @endif
                            </td>
                            <td>
                                @if($dog->hasBreed())
                                {{{ $dog->breed->name }}}
                                @else
                                <em>Unregistered</em>
                                @endif
                            </td>
                            <td>{{ $dog->getAgeInYearsAndMonths(true) }}</td>
                        </tr>
                        @endforeach

                        @if($dogs->isEmpty())
                        <tr>
                            <td colspan="100%">No dogs to display</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <h3>Bitches</h3>
            <div class="table-responsive">
                <table class="table table-condensed table-striped">
                    <thead>
                        <tr>
                            @if($kennel->id == $currentUser->id)
                            <th class="col-lg-1"><input type="checkbox" name="check-all-bitch-ids[]" onclick="check_all(this, '[name=&quot;kennel-tab-{{ $kennelGroup->id }}-bitch-ids[]&quot;]', false);" /></th>
                            @endif
                            <th >Name</th>
                            <th class="col-lg-3">Breed</th>
                            <th class="col-lg-2">Age</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($bitches = $kennelGroup->dogs()->whereFemale()->whereAdult()->orderByKennelGroup($kennelGroup)->get()) as $dog)
                        <tr>
                            @if($kennel->id == $currentUser->id)
                            <td><input type="checkbox" name="kennel-tab-{{ $kennelGroup->id }}-bitch-ids[]" class="kennel_dog_checkbox" value="{{ $dog->id }}"/></td>
                            @endif
                            <td>
                                <a href="{{ route('dog/profile', $dog->id) }}">
                                    <span {{ $dog->isWorked() ? 'class="text-muted"' : '' }}>{{{ $dog->fullName() }}} (#{{ $dog->id }})</span>
                                </a>

                                 @if( ! $kennelGroup->isCemetery())
                                    @if($dog->isComplete())
                                        @if( ! $dog->isSexuallyMature())
                                        <a data-toggle="tooltip" data-placement="top" title="Not Sexually Mature"><i class="fa fa-leaf"></i></a>
                                        @endif

                                        @if($dog->isExpecting())
                                        <a data-toggle="tooltip" data-placement="top" title="Pregnant"><i class="fa fa-heart"></i></a>
                                        @elseif($dog->isInHeat())
                                        <a data-toggle="tooltip" data-placement="top" title="In Heat"><i class="fa fa-heart-o"></i></a>
                                        @endif

                                        @if($dog->isInfertile())
                                        <a data-toggle="tooltip" data-placement="top" title="Infertile"><i class="fa fa-stethoscope"></i></a>
                                        @endif

                                        @if($dog->isUnhealthy())
                                        <a data-toggle="tooltip" data-placement="top" title="Unhealthy"><i class="fa fa-bug"></i></a>
                                        @endif
                                    @else
                                    <a data-toggle="tooltip" data-placement="top" title="Incomplete"><i class="fa fa-circle-o-notch"></i></a>
                                    @endif
                                @endif

                                @if($dog->hasLitter())
                                <br />
                                <small><em>{{ is_null($dog->litter->sire) ? 'Unknown' : $dog->litter->sire->linkedNameplate() }} x {{ is_null($dog->litter->dam) ? 'Unknown' : $dog->litter->dam->linkedNameplate() }}</em></small>
                                @endif
                            </td>
                            <td>
                                @if($dog->hasBreed())
                                {{{ $dog->breed->name }}}
                                @else
                                <em>Unregistered</em>
                                @endif
                            </td>
                            <td>{{ $dog->getAgeInYearsAndMonths(true) }}</td>
                        </tr>
                        @endforeach

                        @if($bitches->isEmpty())
                        <tr>
                            <td colspan="100%">No bitches to display</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <h3>Puppies</h3>
            <div class="table-responsive">
                <table class="table table-condensed table-striped">
                    <thead>
                        <tr>
                            @if($kennel->id == $currentUser->id)
                            <th class="col-lg-1"><input type="checkbox" name="check-all-puppy-ids[]" onclick="check_all(this, '[name=&quot;kennel-tab-{{ $kennelGroup->id }}-puppy-ids[]&quot;]', false);" /></th>
                            @endif
                            <th >Name</th>
                            <th class="col-lg-3">Breed</th>
                            <th class="col-lg-2">Age</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($puppies = $kennelGroup->dogs()->wherePuppy()->orderByKennelGroup($kennelGroup)->get()) as $dog)
                        <tr>
                            @if($kennel->id == $currentUser->id)
                            <td><input type="checkbox" name="kennel-tab-{{ $kennelGroup->id }}-puppy-ids[]" class="kennel_dog_checkbox" value="{{ $dog->id }}"/></td>
                            @endif
                            <td>
                                <a href="{{ route('dog/profile', $dog->id) }}">
                                    <span {{ $dog->isWorked() ? 'class="text-muted"' : '' }}>{{{ $dog->fullName() }}} (#{{ $dog->id }})</span>
                                </a>

                                @if($dog->isMale())
                                <a data-toggle="tooltip" data-placement="top" title="Male"><span class="text-male"><i class="fa fa-male"></i></span></a>
                                @endif

                                @if($dog->isFemale())
                                <a data-toggle="tooltip" data-placement="top" title="Female"><span class="text-female"><i class="fa fa-female"></i></span></a>
                                @endif

                                 @if( ! $kennelGroup->isCemetery())
                                    @if($dog->isComplete())
                                        @if( ! $dog->isSexuallyMature())
                                        <a data-toggle="tooltip" data-placement="top" title="Not Sexually Mature"><i class="fa fa-leaf"></i></a>
                                        @endif

                                        @if($dog->isForStud())
                                        <a data-toggle="tooltip" data-placement="top" title="For Stud"><i class="fa fa-fire"></i></a>
                                        @endif

                                        @if($dog->isExpecting())
                                        <a data-toggle="tooltip" data-placement="top" title="Pregnant"><i class="fa fa-heart"></i></a>
                                        @elseif($dog->isInHeat())
                                        <a data-toggle="tooltip" data-placement="top" title="In Heat"><i class="fa fa-heart-o"></i></a>
                                        @endif

                                        @if($dog->isInfertile())
                                        <a data-toggle="tooltip" data-placement="top" title="Infertile"><i class="fa fa-stethoscope"></i></a>
                                        @endif

                                        @if($dog->isUnhealthy())
                                        <a data-toggle="tooltip" data-placement="top" title="Unhealthy"><i class="fa fa-bug"></i></a>
                                        @endif
                                    @else
                                    <a data-toggle="tooltip" data-placement="top" title="Incomplete"><i class="fa fa-circle-o-notch"></i></a>
                                    @endif
                                @endif

                                @if($dog->hasLitter())
                                <br />
                                <small><em>{{ is_null($dog->litter->sire) ? 'Unknown' : $dog->litter->sire->linkedNameplate() }} x {{ is_null($dog->litter->dam) ? 'Unknown' : $dog->litter->dam->linkedNameplate() }}</em></small>
                                @endif
                            </td>
                            <td>
                                @if($dog->hasBreed())
                                {{{ $dog->breed->name }}}
                                @else
                                <em>Unregistered</em>
                                @endif
                            </td>
                            <td>{{ $dog->getAgeInYearsAndMonths(true) }}</td>
                        </tr>
                        @endforeach

                        @if($puppies->isEmpty())
                        <tr>
                            <td colspan="100%">No puppies to display</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>

@stop

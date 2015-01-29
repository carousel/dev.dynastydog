@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Contests</h1>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Search Contests
                </h3>
            </div>

            <div class="panel-body">
                <form class="form-horizontal" role="form" method="get">
                    <div class="form-group">
                        <label for="contests-dog" class="col-sm-3 control-label">Select Dog:</label>
                        <div class="col-sm-9">
                            <select name="dog" class="form-control" id="contests-dog" required>
                                @foreach($kennelGroups as $kennelGroup)
                                <optgroup label="{{{ $kennelGroup->name }}}">
                                    @foreach($kennelGroup->dogs as $dog)
                                    <option value="{{ $dog->id }}">{{{ $dog->nameplate() }}}</option>
                                    @endforeach
                                </optgroup>
                                @endforeach

                                @if ( ! count($kennelGroups))
                                <option value="">No dogs eligible to compete</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-12 text-center">
                            <button type="submit" name="search" value="contests" class="btn btn-block btn-success">
                                Search Contests
                            </button>
                        </div>
                    </div>
                </form>

                <p>This will show a list of all contests that the selected dog is eligible for. This means that only those contests where the dog has unlocked and meets all the prerequisites will be listed. The dog also must have unlocked all the characteristics that are Judging Requirements to be eligible for the contest. Contests run at 11:59pm EST. Good luck!</p>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Create Contests
                </h3>
            </div>
            <div class="panel-body">
                <p>Haven’t found a contest you like? Need to test your dogs in a very specialized kind of contest? You can design your own contests here.</p>

                <p class="text-center">
                    <a href="{{ route('contests/manage') }}" class="btn btn-block btn-success">
                        Create Contests
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

@if( ! is_null($results))
@foreach($results as $contest)
<table class="table table-condensed">
    <thead>
        <tr class="info">
            <th>Name</th>
            <th class="col-xs-3">Type</th>
            <th class="col-xs-2">Run Date</th>
            <th class="col-xs-3">Judging Requirements</th>
        </tr>
    </thead>
    <tbody>
        <tr class="active">
            <td>{{{ $contest->name }}} (#{{{ $contest->id }}})</td>
            <td>
                {{{ $contest->type_name }}}
                <a data-toggle="tooltip" data-html="true" data-placement="right" title="{{{ $contest->type_description }}}"><i class="fa fa-question-circle"></i></a>
            </td>
            <td>{{ $contest->run_on->format('M. jS') }}</td>
            <td>
                <ul class="list-unstyled">
                    @foreach($contest->requirements as $requirement)
                    <li>{{ $requirement->characteristic->name }}: {{ $requirement->getType() }}</li>
                    @endforeach
                </ul>
            </td>
        </tr>
        <tr class="active">
            <td colspan="3">
                <strong>Current Entries:</strong>

                <ul class="list-unstyled">
                    @foreach($contest->entries as $entry)
                    <li>{{ is_null($entry->dog) ? '<em>Unknown</em>' : $entry->dog->linkedNameplate() }}</li>
                    @endforeach

                    @if( ! count($contest->entries))
                    <li><em>None</em></li>
                    @endif
                </ul>
            </td>
            <td>
                @if($contest->dogHasBeenEntered($searchedDog))
                <span class="btn btn-block btn-default disabled">Entered</span>
                @elseif( ! $contest->dogHasUnlockedRequirements($searchedDog))
                <span class="btn btn-block btn-default disabled">Unlock all JRs to Enter</span>
                @elseif($searchedDog->isWorked())
                <span class="btn btn-block btn-default disabled">Advance Turn to Enter</span>
                @else
                <a class="btn btn-block btn-success" href="{{ route('contests/enter', ['dog' => $searchedDog->id, 'contest' => $contest->id]) }}">Enter Dog</a>
                <p class="text-center"><small>* Entering a class takes up the dog's turn</small></p>
                @endif
            </td>
        </tr>
    </tbody>
</table>
@endforeach

@if( ! count($results))
<p>No contests were found for the selected dog.</p>
@endif

@endif

@stop

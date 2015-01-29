@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Manage Contests</h1>
</div>

<h2>Contests</h2>

<div class="callout callout-info">
    <p>You must first create a contest type (e.g. Herding, High Jump, Agility) below before creating a contest.</p>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            Create Contest
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="post" action="{{ route('contests/create') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group">
                <label for="contest-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="contest_name" class="form-control" id="contest-name" value="{{{ Input::old('contest_name') }}}" required/>
                </div>
            </div>

            <div class="form-group">
                <label for="contest-run-date" class="col-sm-2 control-label">Run Date</label>
                <div class="col-sm-10">
                    <div class="input-group date">
                        <input type="text" name="run_date" class="form-control" id="contest-run-date" value="{{{ Input::old('run_date') }}}" required/>
                        <span class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </span>
                    </div>

                    <script type="text/javascript">
                        $(function () {
                            $("#contest-run-date").datetimepicker({
                                pickTime: false, 
                                minDate: "{{ Contest::minRunDate()->format('m/d/Y') }}",
                                maxDate: "{{ Contest::maxRunDate()->format('m/d/Y') }}"
                            });
                        });
                    </script>
                </div>
            </div>

            <div class="form-group">
                <label for="contest-type" class="col-sm-2 control-label">Type</label>
                <div class="col-sm-10">
                    <select name="contest_type" class="form-control" id="contest-type" required>
                        @foreach($contestTypes as $contestType)
                        <option value="{{ $contestType->id }}" {{ Input::old('contest_type') == $contestType->id ? 'selected' : '' }}>
                            {{{ $contestType->name }}}
                        </option>
                        @endforeach

                        @if( ! count($contestTypes))
                        <option value="">No contest types created</option>
                        @endif
                    </select>
                </div>
            </div>
            <p class="text-right">
                <button type="submit" name="create_contest" class="btn btn-success">Create Contest</button>
            </p>
        </form>
    </div>
</div>

<h3>Your Contests</h3>

{{ $contests->links() }}

<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Run Date</th>
            <th>Type</th>
            <th>Prerequisites</th>
            <th>Judging Requirements</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($contests as $contest)
        <tr>
            <td>{{{ $contest->name }}} (#{{ $contest->id }})</td>
            <td>{{ $contest->run_on->format('m/d/Y') }}</td>
            <td>{{{ $contest->type_name }}}</td>
            <td>
                <ul>
                    @foreach($contest->prerequisites as $prerequisite)
                    <li>{{ $prerequisite->characteristic->name }}</li>
                    @endforeach
                </ul>
            </td>
            <td>
                <ul>
                    @foreach($contest->requirements as $requirement)
                    <li>{{ $requirement->characteristic->name }}</li>
                    @endforeach
                </ul>
            </td>
            <td class="text-right">
                <button type="button" class="btn btn-info btn-xs" data-toggle="collapse" data-target="#contest-{{ $contest->id }}-entries">
                    View Entries ({{ $contest->total_entries }})
                </button>
            </td>
        </tr>
        <tr class="collapse" id="contest-{{ $contest->id }}-entries">
            <td colspan="6">
                <table class="table table-striped table-hover modal-form">
                    <thead>
                        <tr>
                            <th>Dog</th>
                            @foreach($contest->requirements as $requirement)
                            <th>{{ $requirement->characteristic->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contest->entries as $entry)
                        <tr>
                            <td>{{ is_null($entry->dog) ? '<em>Unknown</em>' : $entry->dog->linkedNameplate() }}</td>
                            @foreach($contest->requirements as $requirement)
                            <td>{{ ! is_null($dogCharacteristic = $entry->dog->characteristics()->whereCharacteristic($requirement->characteristic->id)->first()) ? $dogCharacteristic->formatRangedValue() : '<em>Unknown</em>' }}</td>
                            @endforeach
                        </tr>
                        @endforeach

                        @if( ! $contest->entries()->count())
                        <tr><td colspan="100%">No dogs have entered</td></tr>
                        @endif
                    </tbody>
                </table>
            </td>
        </tr>
        @endforeach

        @if( ! count($contests))
        <tr><td colspan="6">You have not created any contests</td></tr>
        @endif
    </tbody>
</table>

{{ $contests->links() }}

<h2>Contest Types</h2>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            Create Contest Type
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="post" action="{{ route('contests/type/create') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group">
                <label for="contest-type-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="contest_type_name" class="form-control" id="contest-type-name" value="{{{ Input::old('contest_type_name') }}}" required/>
                </div>
            </div>

            <div class="form-group">
                <label for="contest-type-description" class="col-sm-2 control-label">Description</label>
                <div class="col-sm-10">
                    <input type="text" name="description" class="form-control" id="contest-type-description" value="{{{ Input::old('description') }}}"/>
                </div>
            </div>

            <p class="text-right">
                <button type="submit" name="create_contest_type" class="btn btn-success">Create Contest Type</button>
            </p>
        </form>
    </div>
</div>

<h3>Your Contest Types</h3>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Prerequisites</th>
            <th>Judging Requirements</th>
        </tr>
    </thead>
    <tbody>
        @foreach($contestTypes as $contestType)
        <tr>
            <td><a href="{{ route('contests/type', $contestType->id) }}">{{{ $contestType->name }}}</a></td>
            <td>{{{ $contestType->description }}}</td>
            <td>
                <ul>
                    @foreach($contestType->prerequisites as $prerequisite)
                    <li>{{ $prerequisite->characteristic->name }}</li>
                    @endforeach
                </ul>
            </td>
            <td>
                <ul>
                    @foreach($contestType->requirements as $requirement)
                    <li>{{ $requirement->characteristic->name }}</li>
                    @endforeach
                </ul>
            </td>
        </tr>
        @endforeach

        @if( ! count($contestTypes))
        <tr><td colspan="4">You have not created any contest types</td></tr>
        @endif
    </tbody>
</table>

@stop

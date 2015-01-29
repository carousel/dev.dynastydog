@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Manage Users</h1>
</div>
<h2>Find</h2>

<form class="form" role="form" method="post" action="{{ route('admin/users/user/find') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="row">
        <div class="col-xs-9">
            <div class="input-group">
                <span class="input-group-addon">#</span>
                <input type="number" min="1" name="user" class="form-control" id="cp-users-user-manage-goto" placeholder="User ID" required />
            </div>
        </div>
        <div class="col-xs-3">
            <button type="submit" name="go_to_user" class="btn btn-primary btn-block">Go To</button>
        </div>
    </div>
</form>

<br /><br />

<h2>Give</h2>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/users/manage/give_currency') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <div class="col-sm-6">
            <div class="input-group">
                <input type="number" min="0" name="credits" class="form-control" id="cp-users-user-manage-credits" value="" placeholder="0" />
                <span class="input-group-addon">Credits</span>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="input-group">
                <input type="number" min="0" name="turns" class="form-control" id="cp-users-user-manage-turns" value="" placeholder="0" />
                <span class="input-group-addon">Turns</span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-6">
            <button type="submit" name="give_currency" value="all" class="btn btn-primary btn-block">Give to <big><strong>ALL</strong></big> Users</button>
        </div>
        <div class="col-sm-6">
            <button type="submit" name="give_currency" value="active" class="btn btn-info btn-block">Give to <big><strong>ACTIVE</strong></big> Users</button>
        </div>
    </div>
</form>

<br /><br />

<h2>Access</h2>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/users/manage/ban_ip') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-access-ip" class="col-sm-2 control-label">Ban IP Address</label>
        <div class="col-sm-4">
            <input type="text" name="ip" class="form-control" id="cp-access-ip" placeholder="000.000.000.000" />
        </div>
        <div class="col-sm-2">
            <p class="form-control-static text-center">
                <big><big><strong>/</strong></big></big>
            </p>
        </div>
        <div class="col-sm-4">
            <div class="input-group">
                <span class="input-group-addon">#</span>
                <input type="text" name="user" class="form-control" id="cp-access-ip" placeholder="User ID" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-9">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="unsocialize" id="cp-access-unsocialize" value="yes" /> Remove social presence?
                </label>
            </div>
        </div>
    </div>

    <button type="submit" name="ban_ip" class="btn btn-danger btn-block">Ban</button>
</form>

<table class="table table-striped table-hover table-responsive">
    <thead>
        <tr>
            <th>IP Address</th>
            <th>Banned On</th>
            <th>Associated Users</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bannedIps as $bannedIp)
        <tr>
            <td>
                <a class="btn btn-warning btn-xs" onclick="return confirm('Are you sure you want to unban this IP address?');" href="{{ route('admin/users/manage/unban_ip', $bannedIp->ip) }}">Unban</a>
                {{ $bannedIp->ip }}
            </td>
            <td>{{ $bannedIp->created_at->format('F j, Y g:i A') }}</td>
            <td>
                <ul>
                    @foreach(($users = $bannedIp->users()->orderBy('id', 'asc')->get()) as $user)
                    <li><a href="{{ route('admin/users/user/edit', $user->id) }}">{{{ $user->nameplate() }}}</a></li>
                    @endforeach

                    @if($users->isEmpty())
                    <li><em>None</em></li>
                    @endif
                </ul>
            </td>
        </tr>
        @endforeach

        @if($bannedIps->isEmpty())
        <tr>
            <td colspan="3">No IP addresses are banned.</td>
        </tr>
        @endif
    </tbody>
</table>

@stop

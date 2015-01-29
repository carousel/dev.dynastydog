@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit User</h1>
</div>

<h2>Access</h2>

<div class="form-group">
    <label for="cp-users-user-access-date" class="col-sm-2 control-label">IP Banned?</label>
    <div class="col-sm-10">
        <p class="form-control-static">
            @if($user->isIpBanned())
            <strong>Yes</strong>
            @else
            <em>No</em>
            @endif
        </p>

        <span class="help-block">
            Most recent IP is 
            @if( ! is_null($user->last_login_ip))
            {{ $user->last_login_ip }}
            @elseif( ! is_null($user->created_ip))
            {{ $user->last_login_ip }}
            @else
            <em>Unknown</em>
            @endif
        </span>
    </div>
</div>

@if($user->isBanned())
<div class="form-group">
    <label for="cp-users-user-access-date" class="col-sm-2 control-label">Current Ban</label>
    <div class="col-sm-10">
        <p class="form-control-static">
            {{ $user->banned_until->format('F j, Y g:i A') }}

            @if($user->id != $currentUser->id)
            <a class="btn btn-warning btn-xs" href="{{ route('admin/users/user/unban', $user->id) }}" onclick="return confirm('Are you sure you want to remove the ban from this user?');">Unban</a>
            @endif
        </p>
        <span class="help-block">{{{ $user->ban_reason }}}</span>
    </div>
</div>
@endif

@if($user->isBannedFromChat())
<div class="form-group">
    <label for="cp-users-user-access-date" class="col-sm-2 control-label">Current Chat Ban</label>
    <div class="col-sm-10">
        <p class="form-control-static">
            {{ $user->chat_banned_until->format('F j, Y g:i A') }}

            @if($user->id != $currentUser->id)
            <a class="btn btn-warning btn-xs" href="{{ route('admin/users/user/unban_chat', $user->id) }}" onclick="return confirm('Are you sure you want to remove the chat ban from this user?');">Unban</a>
            @endif
        </p>
        <span class="help-block">{{{ $user->chat_ban_reason }}}</span>
    </div>
</div>
@endif

@if($user->id != $currentUser->id)
<form class="form-horizontal" role="form" method="post" action="{{ route('admin/users/user/ban', $user->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-users-user-access-ban_until" class="col-sm-2 control-label">Ban Until</label>
        <div class="col-sm-10">
            <div class="input-group date">
                <input type="text" name="ban_until" class="form-control" id="cp-users-user-access-ban_until" value="{{{ Input::old('ban_until') }}}" required/>
                <span class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                </span>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function () {
            $("#cp-users-user-access-ban_until").datetimepicker();
        });
    </script>

    <div class="form-group">
        <label for="cp-users-user-access-reason" class="col-sm-2 control-label">Reason</label>
        <div class="col-sm-10">
            <input type="text" name="ban_reason" class="form-control" id="cp-users-user-access-reason" value="{{{ Input::old('ban_reason') }}}" maxlength="255" required />
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-9">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="chat_ban" id="cp-users-user-access-chatban" value="yes" {{ (Input::old('chat_ban') == 'yes') ? 'checked' : '' }} /> Chat ban only?
                </label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-9">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="unsocialize" id="cp-users-user-access-unsocialize" value="yes"  {{ (Input::old('unsocialize') == 'yes') ? 'checked' : '' }} /> Remove social presence?
                </label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="ban_user" class="btn btn-danger">Ban</button>
        </div>
    </div>
</form>
@endif

<hr />

<h2>General Information</h2>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/users/user/edit', $user->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-user-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $user->id }} <a href="{{ route('user/profile', $user->id) }}">(Go to Profile)</a>
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-user-displayname" class="col-sm-2 control-label">Display Name</label>
        <div class="col-sm-10">
            <input type="text" name="display_name" class="form-control" id="cp-user-displayname" value="{{{ Input::old('display_name', $user->display_name) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-users-user-avatar" class="col-sm-2 control-label">Avatar</label>
        <div class="col-sm-10">
            <input type="text" name="avatar" class="form-control" id="cp-users-user-avatar" value="{{{ Input::old('avatar', $user->avatar) }}}" maxlength="255" />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-users-user-kennel_name" class="col-sm-2 control-label">Kennel Name</label>
        <div class="col-sm-10">
            <input type="text" name="kennel_name" class="form-control" id="cp-users-user-kennel_name" value="{{{ Input::old('kennel_name', $user->kennel_name) }}}" maxlength="50" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-users-user-kennel_prefix" class="col-sm-2 control-label">Kennel Prefix</label>
        <div class="col-sm-10">
            <input type="text" name="kennel_prefix" class="form-control" id="cp-users-user-kennel_prefix" value="{{{ Input::old('kennel_prefix', $user->kennel_prefix) }}}" maxlength="5" />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-users-user-kennel_description" class="col-sm-2 control-label">Kennel Description</label>
        <div class="col-sm-10">
            <textarea name="kennel_description" class="form-control" id="cp-users-user-kennel_description" rows="10">{{{ Input::old('kennel_description', $user->kennel_description) }}}</textarea>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            @if($user->id != $currentUser->id)
            <a href="{{ route('admin/users/user/delete', $user->id) }}" name="delete_user" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
            @endif
            <button type="submit" name="edit_user" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

<hr />

<h2>Kennel Groups</h2>

@foreach($kennelGroups as $kennelGroup)
<div class="well well-sm">
    <form class="form-horizontal" role="form" method="post" action="{{ route('admin/users/kennel_group/update', $kennelGroup->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />

        <div class="form-group">
            <label for="cp-users-user-kg{{ $kennelGroup->id }}-id" class="col-sm-2 control-label">ID</label>
            <div class="col-sm-10">
                <p class="form-control-static">{{ $kennelGroup->id }}</p>
            </div>
        </div>

        <div class="form-group">
            <label for="cp-users-user-kg{{ $kennelGroup->id }}-name" class="col-sm-2 control-label">Name</label>
            <div class="col-sm-10">
                @if($kennelGroup->canBeEdited())
                <input type="text" name="name" class="form-control" id="cp-users-user-kg{{ $kennelGroup->id }}-name" value="{{{  $kennelGroup->name  }}}" />
                @else
                <p class="form-control-static">{{{ $kennelGroup->name }}}</p>
                @endif
            </div>
        </div>

        <div class="form-group">
            <label for="cp-users-user-kg{{ $kennelGroup->id }}-description" class="col-sm-2 control-label">Description</label>
            <div class="col-sm-10">
                @if($kennelGroup->canBeEdited())
                <textarea name="description" class="form-control" id="cp-users-user-kg{{ $kennelGroup->id }}-description">{{{ $kennelGroup->description }}}</textarea>
                @else
                <textarea name="description" class="form-control" id="cp-users-user-kg{{ $kennelGroup->id }}-description" disabled>{{{ $kennelGroup->description }}}</textarea>
                @endif
            </div>
        </div>

        <div class="form-group">
            <label for="cp-users-user-kg{{ $kennelGroup->id }}-order" class="col-sm-2 control-label">Order By</label>
            <div class="col-sm-10">
                <select name="dog_order" class="form-control" id="cp-users-user-kg{{ $kennelGroup->id }}-order" required>
                    @foreach(KennelGroup::getDogOrders() as $orderId => $orderName)
                    <option value="{{ $orderId }}" {{ ($orderId == $kennelGroup->dog_order_id) ? 'selected' : '' }}>{{ $orderName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2 text-right">
                <button type="submit" name="edit_kennel_group" class="btn btn-primary">Save</button>
            </div>
        </div>
    </form>
</div>
@endforeach

@if($kennelGroups->isEmpty())
<div class="well well-sm text-center">
    <em>No kennel groups to display</em>
</div>
@endif

@stop

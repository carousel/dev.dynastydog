@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Account Settings</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Basic Settings
                </h3>
            </div>

            <div class="panel-body">
                <form class="form-horizontal" role="form" method="post" action="{{ route('user/settings/update_basic') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                    <div class="form-group">
                        <label for="basicDisplayName" class="col-sm-4 control-label">Display Name</label>
                        <div class="col-sm-8">
                            <input type="text" name="display_name" class="form-control" id="basicDisplayName" value="{{{ Input::old('display_name', $currentUser->display_name) }}}" maxlength="50" required />
                            {{ $errors->first('display_name', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    @if($currentUser->isUpgraded())
                        <div class="form-group">
                            <label for="basicAvatar" class="col-sm-4 control-label">Avatar</label>
                            <div class="col-sm-8">
                                <input type="text" name="avatar" class="form-control" id="basicAvatar" value="{{{ Input::old('avatar', $currentUser->avatar) }}}" maxlength="255" />
                                {{ $errors->first('avatar', '<span class="help-block">:message</span>') }}
                            </div>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="basicKennelName" class="col-sm-4 control-label">Kennel Name</label>
                        <div class="col-sm-8">
                            <input type="text" name="kennel_name" class="form-control" id="basicKennelName" value="{{{ Input::old('kennel_name', $currentUser->kennel_name) }}}" maxlength="50" required />
                            {{ $errors->first('kennel_name', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="basicKennelPrefix" class="col-sm-4 control-label">Kennel Prefix</label>
                        <div class="col-sm-8">
                            <input type="text" name="kennel_prefix" class="form-control" id="basicKennelPrefix" value="{{{ Input::old('kennel_prefix', $currentUser->kennel_prefix) }}}" maxlength="5" />
                            {{ $errors->first('kennel_prefix', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="basicShowGifterLevel" class="col-sm-4 control-label">Show Gifter Level?</label>
                        <div class="col-sm-7">
                            <div class="checkbox">
                                <label for="basicShowGifterLevel">
                                    <input type="checkbox" name="show_gifter_level" value="yes" id="basicShowGifterLevel" {{ Input::old('show_gifter_level', ($currentUser->showGifterLevel() ? 'yes' : 'no')) == 'yes' ? 'checked' : '' }} />
                                    Yes
                                </label>
                            </div>
                            {{ $errors->first('show_gifter_level', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-4 text-right">
                            <button type="submit" name="update_basic" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Change Password
                </h3>
            </div>

            <div class="panel-body">
                <form class="form-horizontal" role="form" method="post" action="{{ route('user/settings/change_password') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                    <div class="form-group">
                        <label for="chpwOld" class="col-sm-4 control-label">Current Password</label>
                        <div class="col-sm-8">
                            <input type="password" name="current_password" class="form-control" id="chpwOld" required />
                            {{ $errors->first('current_password', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="chpwNew" class="col-sm-4 control-label">New Password</label>
                        <div class="col-sm-8">
                            <input type="password" name="new_password" class="form-control" id="chpwNew" required />
                            {{ $errors->first('new_password', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="chpwConfirm" class="col-sm-4 control-label">Confirm New Password</label>
                        <div class="col-sm-8">
                            <input type="password" name="new_password_confirmation" class="form-control" id="chpwConfirm" required />
                            {{ $errors->first('new_password_confirmation', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-4 text-right">
                            <button type="submit" name="change_password" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Block Players
                </h3>
            </div>

            <div class="panel-body">
                <form class="form-horizontal" role="form" method="post" action="{{ route('user/settings/block') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                    <div class="form-group">
                        <label for="blockID" class="col-sm-3 control-label">Player ID</label>
                        <div class="col-sm-6">
                            <div class="input-group">
                                <span class="input-group-addon">#</span>
                                <input type="text" name="user_id_to_block" class="form-control" id="blockID" value="{{{ Input::old('user_id_to_block') }}}" required />
                            </div>
                            {{ $errors->first('user_id_to_block', '<span class="help-block">:message</span>') }}
                        </div>

                        <div class="col-sm-3 text-right">
                            <button type="submit" name="block_user" class="btn btn-danger">Block</button>
                        </div>
                    </div>

                </form>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th colspan="2">Display Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($blockedUsers = $currentUser->blocked()->orderBy('id', 'asc')->get()) as $blockedUser)
                            <tr>
                                <td>{{{ $blockedUser->id }}}</td>
                                <td>{{{ $blockedUser->display_name }}}</td>
                                <td>
                                    <form class="form-inline text-right" role="form" method="post" action="{{ route('user/settings/unblock') }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                        <input type="hidden" name="user_id_to_unblock" value="{{{ $blockedUser->id }}}" />
                                        <button type="submit" name="unblock_user" class="btn btn-danger btn-xs">Unblock</button>
                                        {{ $errors->first('user_id_to_unblock', '<span class="help-block">:message</span>') }}
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        @if (count($blockedUsers) < 1)
                            <tr>
                                <td colspan="3">No blocked players to display</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Change Email
                </h3>
            </div>

            <div class="panel-body">
                <form class="form-horizontal" role="form" method="post" action="{{ route('user/settings/change_email') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                    <div class="form-group">
                        <label for="chemailEmail" class="col-sm-4 control-label">Email</label>
                        <div class="col-sm-8">
                            <input type="email" name="email" class="form-control" id="chemailEmail" value="{{{ Input::old('email', $currentUser->email) }}}" maxlength="255" required />
                            {{ $errors->first('email', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="chemailConfirm" class="col-sm-4 control-label">Confirm Password</label>
                        <div class="col-sm-8">
                            <input type="password" name="confirm_change_email_password" class="form-control" id="chemailConfirm" required />
                            {{ $errors->first('confirm_change_email_password', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-4 text-right">
                            <button type="submit" name="change_email" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Kennel Description
                </h3>
            </div>

            <div class="panel-body">
                <form class="form-horizontal" role="form" method="post" action="{{ route('user/settings/update_kennel_description') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                    <div class="form-group">
                        <div class="col-sm-12">
                            <textarea name="kennel_description" class="form-control" rows="10">{{{ Input::old('kennel_description', $currentUser->kennel_description) }}}</textarea>
                            {{ $errors->first('kennel_description', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-12 text-right">
                            <button type="submit" name="update_kennel_description" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@stop

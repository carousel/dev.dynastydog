@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Register</h1>
</div>
<form class="form-horizontal" role="form" action="{{ route('auth/register') }}" method="post">
    <!-- CSRF Token -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <input type="hidden" name="cc" value="{{{ Input::old('cc', Input::get('cc')) }}}" />
    <fieldset>
        <legend>Your First Dog</legend>
        <div class="form-group">
            <label for="registerBreed" class="col-sm-2 control-label">Breed</label>
            <div class="col-sm-10">
                <select name="register_breed" class="form-control" id="registerBreed" required>
                    @foreach($breeds as $breed)
                        <option value="{{ $breed->id }}" {{ $breed->id == Input::old('register_breed') ? 'selected' : '' }}>
                            {{ $breed->name }}
                        </option>
                    @endforeach
                </select>
                {{ $errors->first('register_breed', '<span class="help-block">:message</span>') }}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Sex</label>
            <div class="col-sm-10">
                @foreach($sexes as $sex)
                    <label class="radio-inline" for="registerSex{{ $sex->name }}">
                        <input type="radio" name="register_sex" id="registerSex{{ $sex->name }}" value="{{ $sex->id }}" {{ Input::old('register_sex', 1) == $sex->id ? 'checked' : '' }}/>
                        {{ $sex->name }}
                    </label>
                @endforeach
                {{ $errors->first('register_sex', '<span class="help-block">:message</span>') }}
            </div>
        </div>
        <div class="form-group">
            <label for="registerDogName" class="col-sm-2 control-label">Name</label>
            <div class="col-sm-10">
                <input type="text" name="register_dog_name" class="form-control" id="registerDogName" value="{{{ Input::old('register_dog_name') }}}" placeholder="Name your new dog" maxlength="32" required />
                {{ $errors->first('register_dog_name', '<span class="help-block">:message</span>') }}
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>Account Information</legend>
        <div class="form-group">
            <label for="registerDisplayName" class="col-sm-2 control-label">Display Name</label>
            <div class="col-sm-10">
                <input type="text" name="display_name" class="form-control" id="registerDisplayName" value="{{{ Input::old('display_name') }}}" maxlength="32" required />
                <span class="help-block"><small>Name other users know you by. Changeable.</small></span>
                {{ $errors->first('display_name', '<span class="help-block">:message</span>') }}
            </div>
        </div>
        <div class="form-group">
            <label for="registerUsername" class="col-sm-2 control-label">Username</label>
            <div class="col-sm-10">
                <input type="text" name="username" class="form-control" id="registerUsername" value="{{{ Input::old('username') }}}" maxlength="32" pattern="^[A-Za-z0-9_]{1,}$" required />
                <span class="help-block"><small>Name you use to log in. Permanent. Letters, numbers, dashes, and underscores only.</small></span>
                {{ $errors->first('username', '<span class="help-block">:message</span>') }}
            </div>
        </div>
        <div class="form-group">
            <label for="registerPassword" class="col-sm-2 control-label">Password</label>
            <div class="col-sm-10">
                <input type="password" name="password" class="form-control" id="registerPassword" value="" required />
                {{ $errors->first('password', '<span class="help-block">:message</span>') }}
            </div>
        </div>
        <div class="form-group">
            <label for="registerPasswordConfirm" class="col-sm-2 control-label">Password Again</label>
            <div class="col-sm-10">
                <input type="password" name="password_confirmation" class="form-control" id="registerPasswordConfirm" value="" required />
                {{ $errors->first('password_confirmation', '<span class="help-block">:message</span>') }}
            </div>
        </div>
        <div class="form-group">
            <label for="registerEmail" class="col-sm-2 control-label">Email</label>
            <div class="col-sm-10">
                <input type="email" name="email" class="form-control" id="registerEmail" value="{{{ Input::old('email') }}}" maxlength="255" required />
                {{ $errors->first('email', '<span class="help-block">:message</span>') }}
            </div>
        </div>
        <div class="form-group">
            <label for="registerReferredBy" class="col-sm-2 control-label">Referred By</label>
            <div class="col-sm-10">
                <input type="number" min="1" step="1" name="referred_by_id" class="form-control" id="registerReferredBy" value="{{{ Input::old('referred_by_id', Input::get('refid')) }}}" />
                <span class="help-block"><small>Optional</small></span>
                {{ $errors->first('referred_by_id', '<span class="help-block">:message</span>') }}
            </div>
        </div>

        @if(Config::get('game.require_alpha_code'))
            <div class="form-group">
                <label for="registerAlphaCode" class="col-sm-2 control-label">Alpha Code</label>
                <div class="col-sm-10">
                    <input type="text" name="alpha_code" class="form-control" id="registerAlphaCode" value="" maxlength="255" required />
                    {{ $errors->first('alpha_code', '<span class="help-block">:message</span>') }}
                </div>
            </div>
        @endif

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                    <label for="registerTOS">
                        <input type="checkbox" name="tos" id="registerTOS" value="yes" required />
                        I confirm that I have read and agree to all of {{ Config::get('game.name') }}'s <a href="{{ route('tos') }}" target="_blank" title="">Terms of Service</a> and am exactly or over 13 years old. I understand that I am only allowed one account.
                    </label>
                </div>
                {{ $errors->first('tos', '<span class="help-block">:message</span>') }}
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                    <label for="registerMarketing">
                        <input type="checkbox" name="allow_marketing_emails" id="registerMarketing" value="yes" />
                        Yes, I would like to receive periodic updates, coupons and marketing communications about Dynasty via email.
                    </label>
                </div>
                {{ $errors->first('allow_marketing_emails', '<span class="help-block">:message</span>') }}
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2">
                <button type="submit" name="register" value="register" class="btn btn-success" title="">Register</button>
            </div>
        </div>
    </fieldset>
</form>

@stop

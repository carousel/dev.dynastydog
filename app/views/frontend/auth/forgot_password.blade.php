@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Lost Password</h1>
</div>

<p>Enter an email address below to have a new password emailed to you.</p>

<form method="post" action="" class="form-horizontal">
    <!-- CSRF Token -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <!-- Email -->
    <div class="form-group">
        <label for="email" class="col-sm-2 control-label">Email address</label>
        <div class="col-sm-10">
            <input type="email" name="email" class="form-control" id="email" value="{{{ Input::old('email') }}}" />
            {{ $errors->first('email', '<span class="help-block">:message</span>') }}
        </div>
    </div>

    <!-- Form actions -->
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" name="resend_new_password" class="btn btn-default">Send</button>
        </div>
    </div>
</form>

@stop

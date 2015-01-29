@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Change Your Password</h1>
</div>


<form method="post" action="" class="form-horizontal">
    <!-- CSRF Token -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <!-- New Password -->
    <div class="form-group">
        <label for="password" class="col-sm-2 control-label">New Password</label>
        <div class="col-sm-10">
            <input type="password" name="password" class="form-control" id="password" />
            {{ $errors->first('password', '<span class="help-block">:message</span>') }}
        </div>
    </div>

    <!-- Password Confirm -->
    <div class="form-group">
        <label for="password_confirmation" class="col-sm-2 control-label">Confirm Password</label>
        <div class="col-sm-10">
            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" />
            {{ $errors->first('password_confirmation', '<span class="help-block">:message</span>') }}
        </div>
    </div>

    <!-- Form actions -->
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-default">Change</button>
        </div>
    </div>
</form>

@stop

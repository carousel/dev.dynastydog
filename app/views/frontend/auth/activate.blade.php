@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Activate Account</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('auth/activate') }}">
    <!-- CSRF Token -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="activationCode" class="col-sm-2 control-label">Activation Code</label>
        <div class="col-sm-10">
            <input type="text" name="activation_code" class="form-control" id="activationCode" value="{{{ Input::old('activation_code', Input::get('code')) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="activationEmail" class="col-sm-2 control-label">Email</label>
        <div class="col-sm-10">
            <input type="email" name="email" class="form-control" id="activationEmail" value="{{{ Input::old('email', Input::get('email')) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2">
            <button type="submit" name="activate" class="btn btn-success">Activate</button>
        </div>
    </div>
</form>

@stop

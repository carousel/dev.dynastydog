@extends($layout)



{{-- Page content --}}

@section('content')



<div class="page-header">

    <h1>Credits & Contact Staff</h1>

</div>

<h4>
	Credits
</h4>

<p>
	<a href="http://dynastydog.com/user/profile/1">Baus</a> - Owner & Game Designer
</p>
<p>
	<a href="http://taywhited.com">Taylor Whited</a> - Programmer
</p>
<p>
	<a href="http://maranez.deviantart.com/">Martina (Maranez)</a> - Banner Artist
</p>
<p>
	<a href="http://dynastydog.com/user/profile/136">Eispiritu</a> - Most Dog Breed Silhouettes
</p>

<hr>

<h4>
	Contact Staff
</h4>

<form role="form" method="post" action="{{ route('staff') }}">

    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div class="form-group">

        <label for="from-name">Your Name</label>

        <input type="text" name="from" class="form-control" id="from-name" value="{{{ Input::old('from') }}}" placeholder="Enter your name" required>

        {{ $errors->first('from', '<span class="help-block">:message</span>') }}

    </div>

    <div class="form-group">

        <label for="email-address">Your Email Address</label>

        <input type="email" name="email" class="form-control" id="email-address" value="{{{ Input::old('email') }}}" placeholder="Enter email" required>

        {{ $errors->first('email', '<span class="help-block">:message</span>') }}

    </div>

    <div class="form-group">

        <label for="body">Message</label>

        <textarea name="body" class="form-control" rows="10">{{{ Input::old('body') }}}</textarea>

        {{ $errors->first('body', '<span class="help-block">:message</span>') }}

    </div>



    {{ $errors->first('recaptcha_response_field', '<span class="help-block text-center">:message</span>') }}

    {{ Form::captcha() }}



    <br />



    <button type="submit" name="contact_staff" class="btn btn-primary btn-block">Contact Us!</button>

</form>



@stop


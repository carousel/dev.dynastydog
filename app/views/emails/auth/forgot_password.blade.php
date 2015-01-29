@extends('emails/layouts/default')

@section('content')
<p>Hello {{ $user->display_name }},</p>

<p>Please click on the following link to updated your password:</p>

<p><a href="{{ route('auth/forgot_password_confirm', $recoveryCode) }}">{{ route('auth/forgot_password_confirm', $recoveryCode) }}</a></p>

<p>Best regards,</p>

<p>{{ Config::get('game.name') }} Team</p>
@stop

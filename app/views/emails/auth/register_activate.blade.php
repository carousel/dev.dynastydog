@extends('emails/layouts/default')

@section('content')
<p>Hello {{ $user->display_name }},</p>

<p>Welcome to {{ Config::get('game.name') }}! Please click on the following link to confirm your {{ Config::get('game.name') }} account:</p>

<p><a href="{{ route('auth/activate', ['email' => $user->email, 'code' => $activationCode]) }}">{{ route('auth/activate', ['email' => $user->email, 'code' => $activationCode]) }}</a></p>

<p>Best regards,</p>

<p>{{ Config::get('game.name') }} Team</p>
@stop

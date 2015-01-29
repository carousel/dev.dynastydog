@extends($layout)

{{-- Page content --}}
@section('content')

<h1 class="text-center">{{ Config::get('game.maintenance_mode_message') }}</h1>

@stop

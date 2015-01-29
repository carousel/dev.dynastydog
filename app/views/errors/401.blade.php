@extends($layout)

{{-- Page content --}}
@section('content')

<h1 class="text-center">{{ $exception->getMessage() }}</h1>

@stop

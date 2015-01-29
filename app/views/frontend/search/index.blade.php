@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Search</h1>
</div>

<ul class="nav nav-pills nav-justified">
    <li><a href="{{ route('search/users') }}">Search Players</a></li>
    <li><a href="{{ route('search/dogs') }}">Search Dogs</a></li>
    <li><a href="{{ route('search/forums') }}">Search Forums</a></li>
</ul>

@stop

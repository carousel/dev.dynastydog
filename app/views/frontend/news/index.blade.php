@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>News</h1>
</div>

{{ $newsPosts->links() }}

@foreach($newsPosts as $newsPost)
@include('frontend/news/_post', ['newsPost' => $newsPost, 'lead' => true])
@endforeach

{{ $newsPosts->links() }}

@stop

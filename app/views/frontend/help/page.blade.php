@extends($layout)

{{-- Breadcrumbs --}}
{{ Breadcrumbs::setCurrentRoute('help/page', $page) }}

{{-- Page content --}}
@section('content')

<h1>{{{ $page->title }}}</h1>

{{ $page->content }}

@if ($page->categories()->count())
<hr />

<p>
    <strong>Categories:</strong> 
    @foreach($page->categories()->orderBy('title', 'asc')->get() as $category)
    <span class="label label-default">{{{ $category->title }}}</span>
    @endforeach
</p>
@endif

@if($currentUser->hasAnyAccess(['admin']))
<p class="text-right"><a href="{{ route('admin/help/help/page/edit', $page->id) }}" class="btn btn-primary">Edit</a></p>
@endif

@stop

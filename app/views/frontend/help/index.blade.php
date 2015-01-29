@extends($layout)

{{-- Breadcrumbs --}}
{{ Breadcrumbs::setCurrentRoute('help') }}

{{-- Page content --}}
@section('content')

<div class="page-header">
	<h1>Help</h1>
</div>

<h2>All Categories</h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Category</th>
            @if($currentUser->hasAnyAccess(['admin']))
            <th></th>
            @endif
        </tr>
    </thead>
    <tbody>
    	@foreach($categories as $category)
        <tr>
            <td><a href="{{ route('help/category', $category->id) }}">{{{ $category->title }}}</a></td>
            @if($currentUser->hasAnyAccess(['admin']))
            <td class="text-right">
                <a href="{{ route('admin/help/help/category/edit', $category->id) }}" class="btn btn-primary btn-xs">Edit</a>
            </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

<h2>All Pages</h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Page</th>
            @if($currentUser->hasAnyAccess(['admin']))
            <th></th>
            @endif
        </tr>
    </thead>
    <tbody>
    	@foreach($pages as $page)
        <tr>
            <td><a href="{{ route('help/page', $page->id) }}">{{{ $page->title }}}</a></td>
            @if($currentUser->hasAnyAccess(['admin']))
            <td class="text-right">
                <a href="{{ route('admin/help/help/page/edit', $page->id) }}" class="btn btn-primary btn-xs">Edit</a>
            </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

@stop

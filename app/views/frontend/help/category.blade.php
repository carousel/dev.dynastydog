@extends($layout)

{{-- Breadcrumbs --}}
{{ Breadcrumbs::setCurrentRoute('help/category', $category) }}

{{-- Page content --}}
@section('content')

<h1>{{{ $category->title }}}</h1>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Sub-category</th>
            @if($currentUser->hasAnyAccess(['admin']))
            <th></th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($subCategories as $subCategory)
        <tr>
            <td><a href="{{ route('help/category', $subCategory->id) }}">{{{ $subCategory->title }}}</a></td>
            @if($currentUser->hasAnyAccess(['admin']))
            <td class="text-right">
                <a href="{{ route('admin/help/help/category/edit', $subCategory->id) }}" class="btn btn-primary btn-xs">Edit</a>
            </td>
            @endif
        </tr>
        @endforeach

        @if( ! count($subCategories))
        <tr>
            <td colspan="2">No categories to display</td>
        </tr>
        @endif
    </tbody>
</table>

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

        @if( ! count($pages))
        <tr>
            <td colspan="2">No pages to display</td>
        </tr>
        @endif
    </tbody>
</table>

@stop

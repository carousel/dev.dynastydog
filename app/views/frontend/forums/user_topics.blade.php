@extends($layout)

{{-- Page content --}}
@section('content')

@include('frontend/forums/_navigation', ['newTopicForum' => null])

<div class="page-header">
    <h1>Your Topics</h1>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Topic</th>
            <th>Forum</th>
            <th>Date</th>
            <th>Last Activity</th>
        </tr>
    </thead>
    <tbody>
        @foreach($topics as $topic)
        <tr>
            <td><strong><a href="{{ route('forums/topic', $topic->id) }}">{{{ $topic->title }}}</a></strong></td>
            <td><a href="{{ route('forums/forum', $topic->forum->id) }}">{{ $topic->forum->title }}</a></td>
            <td>{{ $topic->created_at->format('F jS, Y g:i A') }}</td>
            <td>
                @if ( ! is_null($lastPost = $topic->lastPost()))
                {{ $lastPost->created_at->format('F jS, Y g:i A') }}
                <br />
                Last post by 
                @if( ! is_null($lastPost->author))
                <a href="{{ route('user/profile', $lastPost->author->id) }}">
                    @if($lastPost->author->isAdministrator())
                    <span class="text-success">{{{ $lastPost->author->display_name }}} (#{{ $lastPost->author->id }})</span>
                    @else
                    {{{ $lastPost->author->display_name }}} (#{{ $lastPost->author->id }})
                    @endif
                </a>
                @else
                <em>Deleted</em>
                @endif
                @else
                No Recent Activity
                @endif
                </a>
            </td>
        </tr>
        @endforeach

        @if( ! count($topics))
        <tr>
            <td colspan="4">You have not created any topics.</td>
        </tr>
        @endif
    </tbody>
</table>

@stop

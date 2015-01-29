@extends($layout)

{{-- Page content --}}
@section('content')

@include('frontend/forums/_navigation', ['newTopicForum' => $forum])

<div class="page-header">
    <h1>{{ $forum->title }}</h1>
</div>

{{ $topics->links() }}

<form class="form" role="form" method="post" action="{{ route('admin/forums/forum/topics/delete', $forum->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <table class="table table-striped">
        <thead>
            <tr>
                @if($currentUser->hasAnyAccess(['admin']))
                <th><input type="checkbox" onclick="check_all(this, '[name$=&quot;forum_topics[]&quot;]', false);" /></th>
                @endif
                <th>Title</th>
                <th colspan="2">Latest Activity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topics as $topic)
            <tr>
                @if($currentUser->hasAnyAccess(['admin']))
                <td><input type="checkbox" name="forum_topics[]" value="{{ $topic->id }}" /></td>
                @endif
                <td>
                    <strong>
                        <a href="{{ route('forums/topic', $topic->id) }}">{{{ $topic->title }}}</a>
                        @if($topic->isStickied())
                        <i class="fa fa-thumb-tack"></i>
                        @endif
                        @if($topic->isLocked())
                        <i class="fa fa-lock"></i>
                        @endif
                    </strong>

                    </strong>
                    <br />
                    By 
                    @if( ! is_null($topic->author))
                    <a href="{{ route('user/profile', $topic->author->id) }}">
                        @if($topic->author->isAdministrator())
                        <span class="text-success">{{{ $topic->author->display_name }}} (#{{ $topic->author->id }})</span>
                        @else
                        {{{ $topic->author->display_name }}} (#{{ $topic->author->id }})
                        @endif
                    </a>
                    @else
                    <em>Deleted</em>
                    @endif
                    on {{ $topic->created_at->format('F jS, Y g:i A') }}
                </td>
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
                </td>
                <td>
                    {{ $topic->views }} {{ Str::plural('View', $topic->views) }}<br />
                    {{ $topic->replies }} {{ Str::plural('Replies', $topic->replies) }}
                </td>
            </tr>
            @endforeach

            @if( ! count($topics))
            <tr>
                <td colspan="100%">
                    No topics to display.
                </td>
            </tr>
            @endif
        </tbody>
    </table>
    @if($currentUser->hasAnyAccess(['admin']))
    <p class="text-center">
        <button type="submit" name="delete_topics" class="btn btn-danger">Delete</button>
    </p>
    @endif
</form>

{{ $topics->links() }}

@stop

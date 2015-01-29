@extends($layout)

{{-- Page content --}}
@section('content')

@include('frontend/forums/_navigation', ['newTopicForum' => null])

<div class="page-header">
    <h1>Forums</h1>
</div>

<table class="table table-striped">
    <tbody>
        @foreach($forums as $forum)
        <tr>
            <td>
                <h4><a href="{{ route('forums/forum', $forum->id) }}">{{{ $forum->title }}}</a></h4>
                <p><i>{{{ $forum->description }}}</i></p>
            </td>
            <td style="vertical-align: middle;">
                @if( ! is_null($lastTopic = $forum->lastTopic()))
                    <a href="{{ route('forums/topic', $lastTopic->id) }}">{{{ $lastTopic->title }}}</a> by 
                    
                    @if ( ! is_null($lastPost = $lastTopic->lastPost()) and ! is_null($lastPost->author))
                    <a href="{{ route('user/profile', $lastPost->author->id) }}">
                        @if($lastPost->author->isAdministrator())
                        <span class="text-success">{{{ $lastPost->author->display_name }}} (#{{ $lastPost->author->id }})</span>
                        @else
                        {{{ $lastPost->author->display_name }}} (#{{ $lastPost->author->id }})
                        @endif
                    </a>
                    @elseif( ! is_null($lastTopic->author))
                    <a href="{{ route('user/profile', $lastTopic->author->id) }}">
                        @if($lastTopic->author->isAdministrator())
                        <span class="text-success">{{{ $lastTopic->author->display_name }}} (#{{ $lastTopic->author->id }})</span>
                        @else
                        {{{ $lastTopic->author->display_name }}} (#{{ $lastTopic->author->id }})
                        @endif
                    </a>
                    @else
                    <em>Deleted</em>
                    @endif

                    <br />

                    on {{ $lastTopic->last_activity_at->format('F jS, Y g:i A') }}
                @else
                No Recent Activity
                @endif
            </td>
        </tr>
        @endforeach

        @if( ! count($forums))
        <tr>
            <td>No forums to display</td>
        </tr>
        @endif
    </tbody>
</table>

@stop

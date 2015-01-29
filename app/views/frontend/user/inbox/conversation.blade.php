@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Conversation</h1>
</div>

<h2>{{{ $conversation->subject }}}</h2>

<ul class="chats">
    @foreach($messages as $message)
    <li class="{{ $currentUser->id == $message->user_id ? 'out' : 'in' }}">
        <div class="message">
            <span class="date-time">{{ $message->created_at->format('F jS, Y g:i A') }}</span><br />
            @if( ! is_null($message->user))
            <a href="{{ route('user/profile', $message->user->id) }}">
                {{{ $message->user->display_name }}} (#{{ $message->user->id }})
            </a>
            @else
            <em>Deleted</em>
            @endif
            <span class="body body-grey">{{ nl2br($message->body) }}</span>
        </div>
    </li>
    @endforeach
</ul>

{{ $messages->links() }}

<br />

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Reply</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="post" action="{{ route('user/inbox/conversation/reply', $conversation->id) }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />

            <div class="form-group">
                <div class="col-sm-12">
                    <textarea name="body" class="form-control" rows="10">{{{ Input::old('body') }}}</textarea>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-12 text-right">
                    <button type="submit" name="reply" class="btn btn-primary">Send Reply</button>
                </div>
            </div>
        </form>
    </div>
</div>

@stop

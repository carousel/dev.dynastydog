@foreach(($chatMessages = ChatMessage::with('author')->orderBy('created_at', 'desc')->orderBy('id', 'desc')->take(50)->get()) as $message)
<li class="chat_message {{ $message->id % 2 ? 'out' : 'in' }}">
    <div class="message">
        <span class="date-time">{{ $message->created_at->format('M jS, Y g:i A') }}</span>

        @if( ! is_null($currentUser) and $currentUser->hasAnyAccess(['admin']))
            <button type="button" class="close" data-id="{{ $message->id }}" data-dismiss="chat_message" onclick="return confirm('Are you sure you want to permanently delete this chat message?');">×</button>
        @endif
        <br />
        @if(is_null($message->author))
        <em>Deleted</em>
        @else
        <a href="{{ route('user/profile', $message->author->id) }}" class="name" data-original-title="" title="">
            @if($message->author->isAdministrator())
                <span class="text-success"><strong>{{{ $message->author->display_name }}} (#{{ $message->author->id }})</strong></span>
            @else
                {{ $message->author->isUpgraded() ? '<strong>' : '' }}
                {{{ $message->author->display_name }}} (#{{ $message->author->id }})
                {{ $message->author->isUpgraded() ? '</strong>' : '' }}
            @endif
        </a>
        @endif
        <span class="breakable body body-grey" style="color: #{{{ $message->hex }}};">{{ $message->body }}</span>
    </div>
</li>
@endforeach

@if ( ! $chatMessages)
<li class="in">
    <div class="message">
        <span class="body-grey">No chat messages to display</span>
    </div>
</li>
@endif

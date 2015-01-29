@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Inbox</h1>
</div>

<form role="form" method="post" action="{{ route('user/inbox/delete_conversations') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Conversation With</th>
                <th>Replies</th>
                <th>Received</th>
                <th class="text-right"><input type="checkbox" onclick="check_all(this, '[name$=&quot;ids[]&quot;]');" /></th>
            </tr>
        </thead>
        <tbody>
            @foreach($conversations as $conversation)
            <tr>
                <td><a href="{{ route('user/inbox/conversation', $conversation->id) }}">{{{ $conversation->subject }}}</a></td>
                <td>
                    @if($currentUser->id == $conversation->sender_id and ! is_null($conversation->receiver))
                    <a href="{{ route('user/profile', $conversation->receiver->id) }}">
                        {{{ $conversation->receiver->display_name }}} (#{{ $conversation->receiver->id }})
                    </a>
                    @elseif($currentUser->id == $conversation->receiver_id and  ! is_null($conversation->sender))
                    <a href="{{ route('user/profile', $conversation->sender->id) }}">
                        {{{ $conversation->sender->display_name }}} (#{{ $conversation->sender->id }})
                    </a>
                    @else
                    <em>Deleted</em>
                    @endif
                </td>
                <td>{{ $conversation->replies }}</td>
                <td>{{ $conversation->updated_at->format('F jS, Y g:i A') }}</td>
                <td class="text-right">
                    <input type="checkbox" name="ids[]" value="{{ $conversation->id }}" />
                </td>
            </tr>
            @endforeach

            @if( ! count($conversations))
            <tr>
                <td colspan="5">No conversations to display</td>
            </tr>
            @endif
        </tbody>
    </table>
    <section class="text-right">
        <button type="submit" name="delete_conversations" class="btn btn-danger">Delete</button>
    </section>
</form>

{{ $conversations->links() }}

<br />

<a name="compose"><!-- Empty --></a>
<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Compose Message</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="post" action="{{ route('user/inbox/conversation/create') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <div class="form-group">
                <label for="sendTo" class="col-sm-2 control-label">Send To</label>
                <div class="col-sm-10">
                    <div class="input-group">
                    <span class="input-group-addon">#</span>
                    <input type="text" name="receiver_id" class="form-control" id="sendTo" value="{{{ Input::old('receiver_id', Input::get('compose', null)) }}}" placeholder="Player ID" required />
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="subject" class="col-sm-2 control-label">Subject</label>
                <div class="col-sm-10">
                    <input type="text" name="subject" class="form-control" id="subject" value="{{{ Input::old('subject') }}}" maxlength="255" required />
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2">
                    <textarea name="body" class="form-control" rows="10" required>{{{ Input::old('body') }}}</textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="start_conversation" class="btn btn-primary">Send Message</button>
                </div>
            </div>
        </form>
    </div>
</div>

@stop

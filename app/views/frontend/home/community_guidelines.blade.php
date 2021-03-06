@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Community Rules</h1>
</div>

<ol>
    <li>Be nice to each other. Don’t do anything you wouldn’t do when interacting with people face to face. Just because you can’t see faces on the other side of the computer screen, doesn’t mean they are not there.</li>

    <li>Hard swearing is not allowed. You are welcome to use replacement words like “fudge” and “shoot”. Softer swear words, like “hell”, are allowed in reasonable quantities.</li>

    <li>Do not post anything that could be deemed offensive - including, but not limited to, pornography, graphic images, torture, gore. Don’t forget that Dynasty is officially 13+.</li>

    <li>Keep caps and chat-speak to a minimum. A word is fine. A sentence is not.</li>

    <li>Do not flood chat with the same post over and over. Along the same lines, do not make the same forum thread/post continuously. This is considered spamming.</li>

    <li>Please foster an atmosphere of sharing. Giving away or trading breedings and dogs is encouraged over charging credits for them.</li>

    <li>You are encouraged to ask other players for breedings over private messages, however please keep in mind they are not obligated to agree. If you cannot come to an agreement, do not continue asking as that constitutes harassment. </li>

    <li>Have fun!</li>
</ol>

@if( ! is_null($currentUser))
	@if ($currentUser->agreedToCommunityGuidelines())
		<span class="btn btn-block btn-primary disabled">I have agreed to follow these rules.</button>
	@else
		<form method="post" class="form" role="form" action="{{ route('forums/agree_to_community_guidelines') }}">
		    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
		    <button type="submit" name="agree" class="btn btn-block btn-primary">I agree to follow these rules. Take me to the forums.</button>
		</form>
	@endif
@endif

@stop

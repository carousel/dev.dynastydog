@if($lead)
<div class="row">
    <div class="col-xs-12 lead">
        <a href="{{ route('news/post', $newsPost->id) }}">{{{ $newsPost->title }}}</a>
    </div>
</div>
@endif

<div class="row">
    <div class="col-xs-12">{{ nl2br($newsPost->body) }}</div>
</div>

@foreach($newsPost->polls as $poll)
@include('frontend/news/_poll', ['poll' => $poll])
@endforeach

<div class="row">
    <div class="col-md-4">
        <a href="{{ route('news/post', $newsPost->id) }}">Comments ({{ $newsPost->comments()->count() }})</a>
    </div>
    <div class="col-md-8 text-md-right">
        <em>Post added on {{ $newsPost->created_at->format('F jS, Y g:i A') }}</em>
        <div class="btn-group">
            @if($currentUser->hasAnyAccess(['admin']))
            <a class="btn btn-primary btn-xs" href="{{ route('admin/news/post/edit', $newsPost->id) }}">Edit</a>
            @endif
        </div>
    </div>
</div>

<hr />
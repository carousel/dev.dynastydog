@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>{{{ $newsPost->title }}}</h1>
</div>

@include('frontend/news/_post', ['newsPost' => $newsPost, 'lead' => false])

<h2>Comments</h2>

{{ $comments->links() }}

@foreach($comments as $comment)
<div class="row">
    <div class="col-sm-4 text-center">
        <section class="well well-sm">
        	@if( ! is_null($comment->author))
            <a href="{{ route('user/profile', $comment->author->id) }}" title="">
            	{{{ $comment->author->display_name }}} ({{ $comment->author->id }})
            </a>
            @if($comment->author->hasAvatar())
            <br />
            <img src="{{{ $comment->author->avatar }}}" class="img-responsive center-block" alt="" />
            @endif
            @else
            <em>Deleted</em>
            @endif
            <br />
            <em>{{ $comment->created_at->format('F jS, Y g:i A') }}</em>
        </section>
    </div>

    <div class="col-sm-8">
        {{ nl2br($comment->body) }}
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="btn-group">
        	@if($currentUser->hasAnyAccess(['admin']))
            <a href="{{ route('admin/news/post/comment/edit', $comment->id) }}" class="btn btn-primary btn-xs">Edit</a>
            <a href="{{ route('admin/news/post/comment/delete', $comment->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this comment?');">Delete</a>
            @endif
        </div>
    </div>
</div>

<hr />
@endforeach

{{ $comments->links() }}

@if ( ! count($comments))
<p class="well well-sm text-center">No comments to display</p>
@endif

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Leave a Comment</big>
        </h3>
    </div>

    <div class="panel-body">
        <form class="form-horizontal" role="form" method="post" action="{{ route('news/post/comment', $newsPost->id) }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />

            <div class="form-group">
                <div class="col-sm-12">
                    <textarea name="body" class="form-control" rows="10" required>{{{ Input::old('body') }}}</textarea>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-12 text-right">
                    <button type="submit" name="comment" class="btn btn-primary">Comment</button>
                </div>
            </div>
        </form>
    </div>
</div>

@stop

@extends($layout)

{{-- Page content --}}
@section('content')

@include('frontend/forums/_navigation', ['newTopicForum' => $topic->forum])

<div class="page-header">
    @if($currentUser->hasAnyAccess(['admin']))
    <form class="inline-form" role="form" method="post" action="{{ route('admin/forums/forum/topic/move', $topic->id) }}">
        <div class="btn-group btn-group-sm">
            <a class="btn btn-danger" href="{{ route('admin/forums/forum/topic/delete', $topic->id) }}" onclick="return confirm('Are you sure you want to delete this topic?');">
                Delete
            </a>

            <a class="btn btn-default" href="{{ route('admin/forums/forum/topic/edit', $topic->id) }}">
                Edit
            </a>

            <a class="btn btn-default" href="{{ route('admin/forums/forum/topic/sticky', $topic->id) }}">
                {{ $topic->isStickied() ? 'Unsticky' : 'Sticky' }}
            </a>

            <a class="btn btn-default" href="{{ route('admin/forums/forum/topic/lock', $topic->id) }}">
                {{ $topic->isLocked() ? 'Unlock' : 'Lock' }}
            </a>

            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <select name="forum" class="btn btn-default">
                @foreach($forums as $forum)
                <option value="{{ $forum->id }}">{{ $forum->title }}</option>
                @endforeach
            </select>
            <button type="submit" name="move_topic" class="btn btn-default">Move</button>
        </div>
    </form>
    @endif

    <h1>
        {{{ $topic->title }}}
        @if($topic->isStickied())
        <i class="fa fa-thumb-tack"></i>
        @endif
        @if($topic->isLocked())
        <i class="fa fa-lock"></i>
        @endif
    </h1>
</div>

{{ $posts->links() }}

@foreach($posts as $post)
@include('frontend/forums/_post', ['post' => $post, 'author' => $post->author, 'editor' => $post->editor])
<div class="row">
    <div class="col-xs-12">
        <div class="btn-group">
            @if($currentUser->hasAnyAccess(['admin']))
            <a href="{{ route('admin/forums/forum/post/edit', $post->id) }}" class="btn btn-default btn-xs">
                Edit
            </a>
            <a href="{{ route('admin/forums/forum/post/delete', $post->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this post?');">
                Delete
            </a>
            @endif
        </div>
    </div>
</div>
<hr />
@endforeach()

@if( ! count($posts))
<p class="well well-sm text-center">No posts to display</p>
@endif

{{ $posts->links() }}

@if( ! $topic->isLocked())
@if(Input::old('preview'))
<h2>Preview</h2>
@include('frontend/forums/_post', ['post' => Input::old('preview'), 'author' => $currentUser, 'editor' => null])
@endif

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Reply</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="post" action="{{ route('forums/topic', $topic->id) }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <div class="form-group">
                <div class="col-sm-12">
                    <textarea name="body" class="form-control" rows="10">{{{ Input::old('body') }}}</textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-12 text-center">
                    <a class="btn btn-primary" href="{{ route('forums/topic/bump', $topic->id) }}" onclick="return confirm('Are you sure you want to bump this topic?');">Bump</a>
                    <button type="submit" name="preview" value="preview" class="btn btn-default">Preview</button>
                    <button type="submit" name="reply" value="reply" class="btn btn-success">Post</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif()

@stop

@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit News Post Comment</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/news/post/comment/edit', $newsPostComment->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-newspostcomment-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $newsPostComment->id }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-newspostcomment-title" class="col-sm-2 control-label">News Post</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                <a href="{{ route('admin/news/post/edit', $newsPostComment->post->id) }}">{{ $newsPostComment->post->title }}</a>
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-newspostcomment-body" class="col-sm-2 control-label">Body</label>
        <div class="col-sm-10">
            <textarea rows="10" name="body" class="form-control" id="cp-newspostcomment-body" required>{{{ Input::old('body', $newsPostComment->body) }}}</textarea>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-newspostcomment-created" class="col-sm-2 control-label">Created</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $newsPostComment->created_at->format('F j, Y g:i A') }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/news/post/comment/delete', $newsPostComment->id) }}" name="delete_news_post_comment" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this news post?');">Delete</a>
            <button type="submit" name="edit_news_post_comment" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

@stop

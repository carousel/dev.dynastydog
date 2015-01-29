@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Forum Post</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/forums/forum/post/update', $forumPost->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-forumpost-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $forumPost->id }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forumpost-id" class="col-sm-2 control-label">Topic</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                @if(is_null($forumPost->topic))
                <em>Unknown</em>
                @else
                <a href="{{ route('admin/forums/forum/topic/edit', $forumPost->topic->id) }}">{{{ $forumPost->topic->title }}}</a>
                @endif
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forumpost-created" class="col-sm-2 control-label">Author</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                @if(is_null($forumPost->author))
                <em>Unknown</em>
                @else
                {{ $forumPost->author->linkedNameplate() }}
                @endif
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forumpost-title" class="col-sm-2 control-label">Body</label>
        <div class="col-sm-10">
            <textarea id="cp-forumpost-title" class="form-control" name="body" rows="10" required>{{{ Input::old('body', $forumPost->body) }}}</textarea>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forumpost-created" class="col-sm-2 control-label">Created</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $forumPost->created_at->format('F j, Y g:i A') }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forumpost-editor" class="col-sm-2 control-label">Editor</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                @if(is_null($forumPost->editor))
                <em>No one</em>
                @else
                {{ $forumPost->editor->linkedNameplate() }}
                @endif
            </p>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/forums/forum/post/delete', $forumPost->id) }}" name="delete_forum_post" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this forum post?');">Delete</a>
            <button type="submit" name="edit_forum_post" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

@stop

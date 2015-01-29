@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Forum Topic</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/forums/forum/topic/update', $forumTopic->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-forumtopic-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $forumTopic->id }} <a href="{{ route('forums/topic', $forumTopic->id) }}">(Go to in Forums)</a>
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forumtopic-author" class="col-sm-2 control-label">Author</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                @if(is_null($forumTopic->author))
                <em>Unknown</em>
                @else
                {{ $forumTopic->author->linkedNameplate() }}
                @endif
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forumtopic-forum" class="col-sm-2 control-label">Forum</label>
        <div class="col-sm-10">
            <select id="cp-forumtopic-forum" class="form-control" name="forum">
                @foreach($forums as $forum)
                <option value="{{ $forum->id }}" {{ (Input::old('forum', $forumTopic->forum_id) == $forum->id) ? 'selected' : '' }}>{{ $forum->title }}</option>
                @endforeach

                @if($forums->isEmpty())
                <option value="">No forums available</option>
                @endif
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forumtopic-title" class="col-sm-2 control-label">Title</label>
        <div class="col-sm-10">
            <input type="text" name="title" class="form-control" id="cp-forumtopic-title" value="{{{ Input::old('title', $forumTopic->title) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forumtopic-created" class="col-sm-2 control-label">Created</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $forumTopic->created_at->format('F j, Y g:i A') }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forumtopic-views" class="col-sm-2 control-label">Views</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $forumTopic->views }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forumtopic-replies" class="col-sm-2 control-label">Replies</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $forumTopic->replies }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forumtopic-editor" class="col-sm-2 control-label">Editor</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                @if(is_null($forumTopic->editor))
                <em>No one</em>
                @else
                {{ $forumTopic->editor->linkedNameplate() }}
                @endif
            </p>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/forums/forum/topic/delete', $forumTopic->id) }}" name="delete_forum_topic" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this forum topic?');">Delete</a>
            <button type="submit" name="edit_forum_topic" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

@stop

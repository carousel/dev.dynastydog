@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Forum</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/forums/forum/edit', $forum->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-forum-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $forum->id }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forum-title" class="col-sm-2 control-label">Title</label>
        <div class="col-sm-10">
            <input type="text" name="title" class="form-control" id="cp-forum-title" value="{{{ Input::old('title', $forum->title) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forum-description" class="col-sm-2 control-label">Description</label>
        <div class="col-sm-10">
            <textarea name="description" class="form-control" id="cp-forum-description" rows="3">{{{ Input::old('description', $forum->description) }}}</textarea>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/forums/forum/delete', $forum->id) }}" name="delete_forum" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this forum?');">Delete</a>
            <button type="submit" name="edit_forum" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

@stop

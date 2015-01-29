@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit News Post</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/news/post/edit', $newsPost->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-newspost-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $newsPost->id }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-newspost-title" class="col-sm-2 control-label">Title</label>
        <div class="col-sm-10">
            <input type="text" name="title" class="form-control" id="cp-newspost-title" value="{{{ Input::old('title', $newsPost->title) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-newspost-body" class="col-sm-2 control-label">Body</label>
        <div class="col-sm-10">
            <textarea rows="10" name="body" class="form-control" id="cp-newspost-body" required>{{{ Input::old('body', $newsPost->body) }}}</textarea>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-newspost-created" class="col-sm-2 control-label">Created</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $newsPost->created_at->format('F j, Y g:i A') }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/news/post/delete', $newsPost->id) }}" name="delete_news_post" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this news post?');">Delete</a>
            <button type="submit" name="edit_news_post" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

<h2>Polls</h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Question</th>
            <th>Votes</th>
            <th>Created</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($newsPolls as $newsPoll)
        <tr>
            <td><a href="{{ route('admin/news/poll/edit', $newsPoll->id) }}">{{ $newsPoll->id }}</a></td>
            <td><a href="{{ route('admin/news/poll/edit', $newsPoll->id) }}">{{ $newsPoll->question }}</a></td>
            <td>{{ $newsPoll->votes()->count() }}</td>
            <td>{{ $newsPoll->created_at->format('F j, Y g:i A') }}</td>
            <td class="text-right">
                @if(in_array($newsPoll->id, $attachedNewsPollIds))
                <a class="btn btn-danger btn-xs" href="{{ route('admin/news/post/poll/remove', [$newsPost->id, $newsPoll->id]) }}">Remove</a>
                @else
                <a class="btn btn-primary btn-xs" href="{{ route('admin/news/post/poll/add', [$newsPost->id, $newsPoll->id]) }}">Add</a>
                @endif
            </td>
        </tr>
        @endforeach

        @if($newsPolls->isEmpty())
        <tr>
            <td colspan="5">No polls to display</td>
        </tr>
        @endif
    </tbody>
</table>

<h2>Comments</h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Body</th>
            <th>Author</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
        @foreach($newsPostComments as $newsPostComment)
        <tr>
            <td><a href="{{ route('admin/news/post/comment/edit', $newsPostComment->id) }}">{{ $newsPostComment->id }}</a></td>
            <td>{{{ $newsPostComment->body }}}</td>
            <td>
                @if(is_null($newsPostComment->author))
                <em>Unknown</em>
                @else
                {{ $newsPostComment->author->linkedNameplate() }}
                @endif
            </td>
            <td>{{ $newsPostComment->created_at->format('F j, Y g:i A') }}</td>
        </tr>
        @endforeach

        @if($newsPostComments->isEmpty())
        <tr>
            <td colspan="5">No comments to display</td>
        </tr>
        @endif
    </tbody>
</table>

@stop

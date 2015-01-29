@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Forum Posts</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-forum-posts">
            <div class="form-group">
                <label for="search-forumposts-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-forumposts-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-forumposts-body" class="col-sm-2 control-label">Body</label>
                <div class="col-sm-10">
                    <input type="text" name="body" class="form-control" id="search-forumposts-body" value="{{{ Input::get('body') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-forumposts-status" class="col-sm-2 control-label">Status</label>
                <div class="col-sm-10">
                    <div class="radio">
                        <label for="search-forumposts-status-all">
                            <input type="radio" name="status" id="search-forumposts-status-all" value="all" {{ (Input::get('status', 'all') == 'all') ? 'checked' : '' }} /> All
                        </label>
                    </div>
                    <div class="radio">
                        <label for="search-forumposts-status-trashed">
                            <input type="radio" name="status" id="search-forumposts-status-trashed" value="trashed" {{ (Input::get('status') == 'trashed') ? 'checked' : '' }} /> Trashed Only
                        </label>
                    </div>
                    <div class="radio">
                        <label for="search-forumposts-status-untrashed">
                            <input type="radio" name="status" id="search-forumposts-status-untrashed" value="untrashed" {{ (Input::get('status') == 'untrashed') ? 'checked' : '' }} /> Untrashed Only
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="forum_posts" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $forumPosts->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Body</th>
            <th>Topic</th>
            <th>Trashed?</th>
        </tr>
    </thead>
    <tbody>
        @foreach($forumPosts as $forumPost)
        <tr>
            @if($forumPost->trashed())
            <td>{{ $forumPost->id }}</td>
            <td>{{{ Str::words($forumPost->body, 50) }}}</td>
            @else
            <td><a href="{{ route('admin/forums/forum/post/edit', $forumPost->id) }}">{{ $forumPost->id }}</a></td>
            <td><a href="{{ route('admin/forums/forum/post/edit', $forumPost->id) }}">{{{ Str::words($forumPost->body, 50) }}}</a></td>
            @endif
            <td>
                @if(is_null($forumPost->topic))
                <em>Unknown</em>
                @else
                <a href="{{ route('admin/forums/forum/topic/edit', $forumPost->topic->id) }}">{{ $forumPost->topic->title }}</a>
                @endif
            </td>
            <td>
                @if($forumPost->trashed())
                <a class="btn btn-danger btn-xs" href="{{ route('admin/forums/forum/post/delete/permanent', $forumPost->id) }}" onclick="return confirm('Are you sure you want to permanently delete that post?');">Delete Permanently</a>
                <a class="btn btn-success btn-xs" href="{{ route('admin/forums/forum/post/restore', $forumPost->id) }}">Restore</a>
                @else
                <em>No</em>
                @endif
            </td>
        </tr>
        @endforeach()

        @if($forumPosts->isEmpty())
        <tr>
            <td colspan="4">No forum posts to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $forumPosts->appends(array_except(Input::all(), 'page'))->links() }}

@stop

@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing News Post Comments</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-news-post-comments">
            <div class="form-group">
                <label for="search-news-post-comments-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-news-post-comments-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-news-post-comments-body" class="col-sm-2 control-label">Body</label>
                <div class="col-sm-10">
                    <input type="text" name="body" class="form-control" id="search-news-post-comments-body" value="{{{ Input::get('body') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="news_post_comments" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $newsPostComments->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Body</th>
            <th>News Post</th>
            <th>Author</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
        @foreach($newsPostComments as $newsPostComment)
        <tr>
            <td><a href="{{ route('admin/news/post/comment/edit', $newsPostComment->id) }}">{{ $newsPostComment->id }}</a></td>
            <td><a href="{{ route('admin/news/post/comment/edit', $newsPostComment->id) }}">{{{ $newsPostComment->body }}}</a></td>
            <td><a href="{{ route('admin/news/post/edit', $newsPostComment->post->id) }}">{{ $newsPostComment->post->title }}</a></td>
            <td>
                @if(is_null($newsPostComment->author))
                <em>Unknown</em>
                @else
                {{ $newsPostComment->author->linkedNameplate() }}
                @endif
            </td>
            <td>{{ $newsPostComment->created_at->format('F j, Y g:i A') }}</td>
        </tr>
        @endforeach()

        @if($newsPostComments->isEmpty())
        <tr>
            <td colspan="3">No news post comments to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $newsPostComments->appends(array_except(Input::all(), 'page'))->links() }}

@stop

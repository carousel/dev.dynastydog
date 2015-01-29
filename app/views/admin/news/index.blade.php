@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing News Posts</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-news-posts">
            <div class="form-group">
                <label for="search-news-posts-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-news-posts-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-news-posts-title" class="col-sm-2 control-label">Title</label>
                <div class="col-sm-10">
                    <input type="text" name="title" class="form-control" id="search-news-posts-title" value="{{{ Input::get('title') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="news_posts" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $newsPosts->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
        @foreach($newsPosts as $newsPost)
        <tr>
            <td><a href="{{ route('admin/news/post/edit', $newsPost->id) }}">{{ $newsPost->id }}</a></td>
            <td><a href="{{ route('admin/news/post/edit', $newsPost->id) }}">{{ $newsPost->title }}</a></td>
            <td>{{ $newsPost->created_at->format('F j, Y g:i A') }}</td>
        </tr>
        @endforeach()

        @if($newsPosts->isEmpty())
        <tr>
            <td colspan="3">No news posts to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $newsPosts->appends(array_except(Input::all(), 'page'))->links() }}

@stop

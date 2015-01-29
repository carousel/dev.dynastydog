@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing News Polls</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-news-polls">
            <div class="form-group">
                <label for="search-news-polls-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-news-polls-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-news-polls-question" class="col-sm-2 control-label">Question</label>
                <div class="col-sm-10">
                    <input type="text" name="question" class="form-control" id="search-news-polls-question" value="{{{ Input::get('question') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="news_polls" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $newsPolls->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Question</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
        @foreach($newsPolls as $newsPoll)
        <tr>
            <td><a href="{{ route('admin/news/poll/edit', $newsPoll->id) }}">{{ $newsPoll->id }}</a></td>
            <td><a href="{{ route('admin/news/poll/edit', $newsPoll->id) }}">{{{ $newsPoll->question }}}</a></td>
            <td>{{ $newsPoll->created_at->format('F j, Y g:i A') }}</td>
        </tr>
        @endforeach()

        @if($newsPolls->isEmpty())
        <tr>
            <td colspan="3">No news polls to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $newsPolls->appends(array_except(Input::all(), 'page'))->links() }}

@stop

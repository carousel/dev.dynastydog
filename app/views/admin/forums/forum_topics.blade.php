@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Forum Topics</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-forum-topics">
            <div class="form-group">
                <label for="search-forumtopics-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-forumtopics-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-forumtopics-title" class="col-sm-2 control-label">Title</label>
                <div class="col-sm-10">
                    <input type="text" name="title" class="form-control" id="search-forumtopics-title" value="{{{ Input::get('title') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-forumtopics-status" class="col-sm-2 control-label">Status</label>
                <div class="col-sm-10">
                    <div class="radio">
                        <label for="search-forumtopics-status-all">
                            <input type="radio" name="status" id="search-forumtopics-status-all" value="all" {{ (Input::get('status', 'all') == 'all') ? 'checked' : '' }} /> All
                        </label>
                    </div>
                    <div class="radio">
                        <label for="search-forumtopics-status-trashed">
                            <input type="radio" name="status" id="search-forumtopics-status-trashed" value="trashed" {{ (Input::get('status') == 'trashed') ? 'checked' : '' }} /> Trashed Only
                        </label>
                    </div>
                    <div class="radio">
                        <label for="search-forumtopics-status-untrashed">
                            <input type="radio" name="status" id="search-forumtopics-status-untrashed" value="untrashed" {{ (Input::get('status') == 'untrashed') ? 'checked' : '' }} /> Untrashed Only
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="forum_topics" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $forumTopics->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Trashed?</th>
        </tr>
    </thead>
    <tbody>
        @foreach($forumTopics as $forumTopic)
        <tr>
            @if($forumTopic->trashed())
            <td>{{ $forumTopic->id }}</td>
            <td>{{{ $forumTopic->title }}}</td>
            @else
            <td><a href="{{ route('admin/forums/forum/topic/edit', $forumTopic->id) }}">{{ $forumTopic->id }}</a></td>
            <td><a href="{{ route('admin/forums/forum/topic/edit', $forumTopic->id) }}">{{{ $forumTopic->title }}}</a></td>
            @endif
            <td>
                @if($forumTopic->trashed())
                <a class="btn btn-success btn-xs" href="{{ route('admin/forums/forum/topic/restore', $forumTopic->id) }}">Restore</a>
                <a class="btn btn-danger btn-xs" href="{{ route('admin/forums/forum/topic/delete/permanent', $forumTopic->id) }}" onclick="return confirm('Are you sure you want to permanently delete that topic?');">Delete Permanently</a>
                @else
                <em>No</em>
                @endif
            </td>
        </tr>
        @endforeach()

        @if($forumTopics->isEmpty())
        <tr>
            <td colspan="3">No forum topics to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $forumTopics->appends(array_except(Input::all(), 'page'))->links() }}

@stop

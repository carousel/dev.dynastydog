@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Search Forums</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Options</big>
        </h3>
    </div>

    <div class="panel-body">
        <form method="get" action="{{ route('search/forums') }}" class="form-horizontal">
            <div class="form-group">
                <label for="searchTerms" class="col-sm-2 control-label">Search Terms</label>
                <div class="col-sm-10">
                    <input type="text" name="terms" class="form-control" id="searchTerms" value="{{{ Input::get('terms') }}}" />
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="forums" class="col-sm-4 control-label">Forums</label>
                        <div class="col-sm-8">
                            <select name="forums[]" class="form-control" id="forums" multiple>
                                @foreach($forums as $forum)
                                <option value="{{ $forum->id }}" {{ in_array($forum->id, Input::get('forums', [])) ? 'selected' : '' }}>
                                    {{ $forum->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="typeTopics" class="col-sm-4 control-label">Type</label>
                        <div class="col-sm-8">
                            <label class="checkbox-inline">
                                <input type="radio" name="type" id="typeTopics" value="topics" {{ Input::get('type', 'topics') == 'topics' ? 'checked' : '' }} /> 
                                Topics
                            </label>
                            <label class="checkbox-inline">
                                <input type="radio" name="type" id="typePosts" value="posts" {{ Input::get('type', 'topics') == 'posts' ? 'checked' : '' }} /> 
                                Posts
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="authorId" class="col-sm-4 control-label">Author ID</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <span class="input-group-addon">#</span>
                                <input type="text" name="author" class="form-control" id="authorId" value="{{{ Input::old('author') }}}" placeholder="Player ID" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="forums" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

@if( ! is_null($results))
@if(Input::get('type') === 'topics')
<table class="table table-striped">
    <thead>
        <tr>
            <th>Topic</th>
            <th>Forum</th>
            <th colspan="2">Latest Activity</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results as $topic)
        <tr>
            <td>
                <strong><a href="{{ route('forums/topic', $topic->id) }}">{{{ $topic->title }}}</a></strong>
                <br />
                By 
                @if( ! is_null($topic->author))
                @if($topic->author->isAdministrator())
                <span class="text-success">{{{ $topic->author->display_name }}} (#{{ $topic->author->id }})</span>
                @else
                {{{ $topic->author->display_name }}} (#{{ $topic->author->id }})
                @endif
                @else
                <em>Deleted</em>
                @endif
                on {{ $topic->created_at->format('F jS, Y g:i A') }}
            </td>
            <td>
                @if( ! is_null($topic->forum))
                <a href="{{ route('forums/forum', $topic->forum->id) }}">{{ $topic->forum->title }}</a>
                @else
                <em>Unknown</em>
                @endif
            </td>
            <td>
                @if ( ! is_null($lastPost = $topic->lastPost()))
                {{ $lastPost->created_at->format('F jS, Y g:i A') }}
                <br />
                Last post by 
                @if( ! is_null($lastPost->author))
                <a href="{{ route('user/profile', $lastPost->author->id) }}">
                    @if($lastPost->author->isAdministrator())
                    <span class="text-success">{{{ $lastPost->author->display_name }}} (#{{ $lastPost->author->id }})</span>
                    @else
                    {{{ $lastPost->author->display_name }}} (#{{ $lastPost->author->id }})
                    @endif
                </a>
                @else
                <em>Deleted</em>
                @endif
                @else
                No Recent Activity
                @endif
            </td>
            <td>
                {{ $topic->views }} {{ Str::plural('View', $topic->views) }}<br />
                {{ $topic->replies }} {{ Str::plural('Replies', $topic->replies) }}
            </td>
        </tr>
        @endforeach

        @if( ! count($results))
        <tr>
            <td colspan="6">
                No results found
            </td>
        </tr>
        @endif
    </tbody>
</table>
@else
<table class="table table-striped">
    <thead>
        <tr>
            <th class="col-xs-4">Body</th>
            <th class="col-xs-3">Topic</th>
            <th class="col-xs-3">Author</th>
            <th class="col-xs-2">Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results as $post)
        <tr>
            <td>{{{ str_limit($post->getOriginal('body'), 75) }}}</td>
            <td>
                <a href="{{ route('forums/topic', $post->topic->id) }}">{{{ $post->topic->title }}}</a>
                (<a href="{{ route('forums/forum', $post->topic->forum->id) }}">{{{ $post->topic->forum->title }}}</a>)
            </td>
            <td>
                @if( ! is_null($post->author))
                @if($post->author->isAdministrator())
                <span class="text-success">{{{ $post->author->display_name }}} (#{{ $post->author->id }})</span>
                @else
                {{{ $post->author->display_name }}} (#{{ $post->author->id }})
                @endif
                @else
                <em>Deleted</em>
                @endif
            </td>
            <td>{{ $post->created_at->format('F jS Y, g:i A') }}</td>
        </tr>
        @endforeach

        @if( ! count($results))
        <tr>
            <td colspan="4">
                No results found
            </td>
        </tr>
        @endif
    </tbody>
</table>
@endif

{{ $results->appends(array_except(Input::all(), 'page'))->links() }}
@endif

@stop

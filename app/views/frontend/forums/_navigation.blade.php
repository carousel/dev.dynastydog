<div class="panel panel-default">
    <div class="panel-footer panel-nav">
        <ul class="nav nav-pills bordered nav-justified">
            <li><a href="{{ route('forums/topics/active') }}">Active Topics</a></li>
            <li><a href="{{ route('forums/topic/create', (is_null($newTopicForum) ? null : ['fid' => $newTopicForum->id])) }}">Create New Topic</a></li>
            <li><a href="{{ route('search/forums') }}">Search Forums</a></li>
            <li><a href="{{ route('forums/topics/user') }}">Your Topics</a></li>
        </ul>
    </div>
</div>
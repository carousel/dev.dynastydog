@extends($layout)

{{-- Page content --}}
@section('content')

@include('frontend/forums/_navigation', ['newTopicForum' => null])

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>New Topic</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="post" action="{{ route('forums/topic/create') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <div class="form-group">
                <label for="forum" class="col-sm-2 control-label">Forum</label>
                <div class="col-sm-10">
                    <select name="forum" class="form-control" id="forum" required>
                        @foreach($forums as $forum)
                        <option value="{{ $forum->id }}" {{ Input::old('forum', Input::get('fid')) == $forum->id ? 'selected' : '' }}>
                            {{ $forum->title }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="title" class="col-sm-2 control-label">Title</label>
                <div class="col-sm-10">
                    <input type="text" name="title" class="form-control" id="title" value="{{{ Input::old('title') }}}" maxlength="255" required />
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2">
                    <textarea name="body" class="form-control" rows="10" required>{{{ Input::old('body') }}}</textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="create_topic" class="btn btn-primary">Create Topic</button>
                </div>
            </div>
        </form>
    </div>
</div>

@stop

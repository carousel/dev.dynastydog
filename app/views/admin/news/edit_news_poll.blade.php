@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit News Poll</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/news/poll/edit', $newsPoll->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-newspoll-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $newsPoll->id }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-newspoll-question" class="col-sm-2 control-label">Title</label>
        <div class="col-sm-10">
            <input type="text" name="question" class="form-control" id="cp-newspoll-question" value="{{{ Input::old('question', $newsPoll->question) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-newspoll-reward" class="col-sm-2 control-label">Reward</label>
        <div class="col-sm-10">
            <input type="text" name="reward" class="form-control" id="cp-newspoll-reward" value="{{{ Input::old('reward', $newsPoll->reward) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-newspoll-created" class="col-sm-2 control-label">Created</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $newsPoll->created_at->format('F j, Y g:i A') }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/news/poll/delete', $newsPoll->id) }}" name="delete_news_poll" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this news poll?');">Delete</a>
            <button type="submit" name="edit_news_pll" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

<h2>Add Answer</h2>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/news/poll/answer/create', $newsPoll->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <div class="form-group">
        <label for="answer_body" class="col-sm-2 control-label">Body</label>
        <div class="col-sm-10">
            <input type="text" name="answer_body" class="form-control" id="answer_body" maxlength="255" required />
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="add_answer" class="btn btn-primary">Add</button>
        </div>
    </div>
</form>

<h2>Current Answers</h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Body</th>
            <th>Votes</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($newsPollAnswers as $newsPollAnswer)
        <tr>
            <td>
                <a></a>
                <form class="form" role="form" method="post" action="{{ route('admin/news/poll/answer/edit', $newsPollAnswer->id) }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <div class="row">
                        <div class="col-md-10 col-xs-8">
                            <label class="sr-only" for="answerBody{{ $newsPollAnswer->id }}">Body</label>
                            <input type="text" name="body" class="form-control input-sm" id="answerBody{{ $newsPollAnswer->id }}" value="{{ $newsPollAnswer->body }}" placeholder="Body" required />
                        </div>
                        <div class="col-md-2 col-xs-4">
                            <button type="submit" name="edit_answer" class="btn btn-primary btn-sm">Edit</button>
                        </div>
                    </div>
                </form>
            </td>
            <td>{{ $newsPollAnswer->votes()->count() }}</td>
            <td class="text-right">
                <a class="btn btn-danger btn-sm" href="{{ route('admin/news/poll/answer/delete', $newsPollAnswer->id) }}">Delete</a>
            </td>
        </tr>
        @endforeach

        @if($newsPollAnswers->isEmpty())
        <tr>
            <td colspan="3">No answers to display</td>
        </tr>
        @endif
    </tbody>
</table>

<h2>Attached to Posts</h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Post</th>
            <th>Created</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($newsPosts as $newsPost)
        <tr>
            <td><a href="{{ route('admin/news/post/edit', $newsPost->id) }}">{{ $newsPost->title }}</a></td>
            <td>{{ $newsPost->created_at->format('F j, Y g:i A') }}</td>
            <td class="text-right">
                <a class="btn btn-danger btn-xs" href="{{ route('admin/news/post/poll/remove', [$newsPost->id, $newsPoll->id]) }}">Remove</a>
            </td>
        </tr>
        @endforeach

        @if($newsPosts->isEmpty())
        <tr>
            <td colspan="3">No posts to display</td>
        </tr>
        @endif
    </tbody>
</table>

@stop

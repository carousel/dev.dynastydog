@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New News Poll</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/news/poll/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-newspoll-question" class="col-sm-2 control-label">Question</label>
        <div class="col-sm-10">
            <input type="text" name="question" class="form-control" id="cp-newspoll-question" value="{{{ Input::old('question') }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-newspoll-reward" class="col-sm-2 control-label">Reward</label>
        <div class="col-sm-10">
            <input type="text" name="reward" class="form-control" id="cp-newspoll-reward" value="{{{ Input::old('reward') }}}" />
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_news_poll" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop

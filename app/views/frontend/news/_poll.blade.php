<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                @if($currentUser->hasAnyAccess(['admin']))
                <div class="btn-group pull-right">
                    <a class="btn btn-primary btn-xs" href="{{ route('admin/news/poll/edit', $poll->id) }}">Edit</a>
                </div>
                @endif

                <h3 class="panel-title">
                    <big>{{{ $poll->question }}}</big>
                </h3>
            </div>

            <div class="panel-body">
                <form class="form-horizontal" role="form" action="{{ route('news/poll/vote', $poll->id) }}" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    @foreach($poll->answers()->orderBy('body', 'asc')->get() as $answer)
                    <div class="radio">
                        <label>
                            <input type="radio" name="answer" value="{{ $answer->id }}" {{ $poll->votedOnBy($currentUser) ? ($answer->votedOnBy($currentUser) ? 'disabled checked' : 'disabled') : '' }} />
                            {{{ $answer->body }}}
                        </label>
                    </div>
                    @endforeach

                    <div class="form-group">
                        <div class="col-sm-12 text-right">
                            <button type="submit" class="btn btn-success" name="vote" {{ $poll->votedOnBy($currentUser) ? 'disabled' : '' }}>Vote</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
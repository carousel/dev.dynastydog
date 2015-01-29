@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Request some Beginner's Luck</h1>
</div>

<div class="well well-sm">
    <div class="row">
        <div class="col-md-5 text-center">
            {{ $dog->linkedNameplate() }}
        </div>
        <div class="col-md-2 text-center">
            <strong>x</strong>
        </div>
        <div class="col-md-5 text-center">
            {{ $bitch->linkedNameplate() }}
        </div>
    </div>
</div>

<div class="row">
    @foreach($onlineBeginners as $user)
    <div class="col-xs-6 col-sm-4 col-md-3">
        <div class="well well-small text-center">
            <a href="{{ route('user/profile', $user->id) }}">
                @if($user->isAdministrator())
                <strong>{{{ $user->nameplate() }}}</strong>
                @else
                {{{ $user->nameplate() }}}
                @endif
            </a>
            <br />

            {{ $user->last_action_at->diffForHumans() }}

            @if($user->hasAvatar())
            <br />
            <img src="{{{ $user->avatar }}}" class="img-responsive center-block" alt="[avatar]" title="User Avatar" />
            @endif

            <a class="btn btn-success btn-xs btn-block wrap" href="{{ route('dog/blr/request', [$dog->id, $bitch->id, $user->id]) }}">
                Request Beginner’s Luck
            </a>
        </div>
    </div>
    @endforeach

    @if($onlineBeginners->isEmpty())
    <div class="well well-small text-center">No online newbies to display.</div>
    @endif
</div>

<p class="text-center">
    <a class="btn btn-primary btn-lg" href="{{ route('dog/breed', [$dog->id, $bitch->id]) }}">Breed Dogs without Beginner's Luck</a>
</p>

@stop

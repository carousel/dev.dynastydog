@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Who's Online?</h1>
</div>

<table class="table table-striped table-responsive">
    <thead>
        <tr>
            <th class="text-center">Player</th>
            <th class="col-xs-4 text-center">Last Action</th>
            @if ($currentUser->hasAnyAccess(['admin']))
                <th class="col-xs-4 text-center">Last Page</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach(User::whereOnline()->orderBy('last_action_at', 'desc')->orderBy('id', 'asc')->get() as $user)
            <tr>
                <td class="text-center">
                    <a href="{{ route('user/profile', $user->id) }}">
                        @if($user->hasAvatar())
                            <img src="{{{ $user->avatar }}}" class="img-responsive center-block" alt="[avatar]" title="User Avatar" /><br />
                        @endif

                        @if($user->isAdministrator())
                            <span class="text-success"><strong>{{{ $user->display_name }}} (#{{ $user->id }})</strong></span>
                        @else
                            {{ $user->isUpgraded() ? '<strong>' : '' }}
                            {{{ $user->display_name }}} (#{{ $user->id }})
                            {{ $user->isUpgraded() ? '</strong>' : '' }}
                        @endif
                    </a>
                </td>
                <td class="text-center">
                    {{ $user->last_action_at->diffForHumans() }}
                </td>
                @if ($currentUser->hasAnyAccess(['admin']))
                    <td class="breakable text-center">{{{ $user->last_uri }}}</td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>

@stop

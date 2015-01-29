@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Notification Center</h1>
</div>

<table class="table table-striped table-responsive">
    <thead>
        <tr>
            <th class="col-xs-1 text-right">ID</th>
            <th>Message</th>
            <th class="col-xs-3 text-center">Date</th>
            <th class="col-xs-2 text-center">
                <form class="form-inline" role="form" method="post" action="{{ route('user/notifications/read_all') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <button type="submit" name="mark_all_as_read" class="btn btn-primary btn-xs">
                        Mark <big><strong>ALL</strong></big> As Read
                    </button>
                </form>
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach($notifications as $notification)
        <tr>
            <td class="text-right">{{ $notification->id }}</td>
            <td>{{ $notification->body }}</td>
            <td class="text-center">{{ $notification->created_at->format('F jS, Y g:i A') }}</td>
            <td class="text-center">
                <form class="form-inline" role="form" method="post" action="{{ route('user/notifications/read') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <input type="hidden" name="id" value="{{ $notification->id }}" />
                    @if($notification->isRead())
                    <span class="btn btn-default btn-xs disabled">Read</span>
                    @else
                    <button type="submit" name="mark_as_read" class="btn btn-primary btn-xs">Mark As Read</button>
                    @endif
                </form>
            </td>
        </tr>
        @endforeach

        @if( ! count($notifications))
        <tr>
            <td colspan="4">No notifications to display</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $notifications->links() }}

@stop

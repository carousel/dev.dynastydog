@if( ! $growlNotifications->isEmpty())
  <script type="text/javascript">
    @foreach($growlNotifications as $growlNotification)
      $.bootstrapGrowl({{ json_encode($growlNotification->body) }}, {
        type: "{{{ $growlNotification->type }}} hidden-xs",
        @if($growlNotification->isPersistent())
        delay: 0,
        allow_dismiss: false, 
        @else
        delay: 10000,
        @endif
      });
    @endforeach
  </script>
@endif

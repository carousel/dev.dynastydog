<div class="panel panel-default">
    <div class="panel-body">
        @if( ! is_null($currentUser))
            @if($currentUser->hasAvatar())
                <div class="row">
                    <div class="col-md-12 text-center">
                        <img src="{{{ $currentUser->avatar }}}"  alt="" />
                    </div>
                </div>
            @endif

            <ul class="list-unstyled text-center">
                <li>
                    <a href="{{ route('user/profile') }}">
                        <strong>{{{ $currentUser->display_name }}} (#{{{ $currentUser->id }}})</strong>
                    </a>
                </li>
                <li>
                    <a href="{{ route('user/kennel') }}">
                       {{{ $currentUser->kennel_name }}}
                    </a>
                </li>
                <li>
                    <a href="{{ route('cash_shop') }}">
                        {{ Dynasty::credits($currentUser->credits) }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('user/notifications') }}" {{ ($totalUnreadNotifications = $currentUser->notifications()->unread()->count()) ? 'class="text-warning"' : '' }}>
                        <span id="sidebar-unread-notification-count">{{ $totalUnreadNotifications }}</span>
                        {{ Str::plural('Notification', $totalUnreadNotifications) }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('cash_shop') }}">
                        <span id="sidebar-turns-left">{{ $currentUser->turns }}</span> {{ Str::plural('Turn', $currentUser->turns) }} Left
                    </a>
                </li>
                <li>
                    @if($nextTurnIn = $currentUser->nextTurnIn())
                    <div id="sidebar-next-turn-in-wrapper">
                        <small>+1 turn in <span id="sidebar-next-turn-in">{{ $nextTurnIn }}</span></small>
                    </div>
                    @endif
                    <a class="btn btn-success btn-xxs advance-turn-button" href="{{ route('user/advance_turn') }}" data-loading-text="<i class='fa fa-cog fa-spin'></i> Advancing..." onclick="return confirm('Are you sure you want to advance your turn?');">
                        <i class="fa fa-level-up"></i> Next Turn
                    </a>
                    <p class="text-center">
                        <small>{{ ($currentUsersTotalWorkedDogs = $currentUser->dogs()->whereWorked()->whereAlive()->count()) }}/{{ Config::get('game.dog.advanced_turn_worked_limit') }} Dogs Worked</small> <a data-toggle="tooltip" data-placement="top" title="" data-original-title="One turn will go through up to {{ Config::get('game.dog.advanced_turn_worked_limit') }} worked {{ Str::plural('dog', Config::get('game.dog.advanced_turn_worked_limit')) }} at once."><i class="fa fa-question-circle"></i></a>
                    </p>
                </li>
            </ul>
        @else
            <div class="page-header">
                <h4>Login</h4>
            </div>
            <form class="form-horizontal" role="form" action="{{ route('auth/login') }}" method="post">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <div class="form-group">
                    <label for="loginUsername" class="col-sm-3 control-label">Username</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control input-sm" id="loginUsername" name="username" placeholder="Username">
                    </div>
                </div>
                <div class="form-group">
                    <label for="loginPassword" class="col-sm-3 control-label">Password</label>
                    <div class="col-sm-9">
                        <input type="password" class="form-control input-sm" id="loginPassword" name="password" placeholder="Password">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-9 text-right">
                        <button type="submit" name="login" class="btn btn-success">Sign in</button><br />
                        <a href="{{ route('auth/forgot_password') }}">Lost password?</a><br />
                        <a href="{{ route('auth/activate') }}">Activate Account</a>
                    </div>
                </div>
            </form>
        @endif
    </div>
    @if( ! is_null($currentUser))
        <div class="panel-footer panel-nav">
            <ul class="nav nav-pills bordered nav-justified">
                <li><a href="{{ route('user/inbox') }}">Inbox<br /></a></li>
                <li><a href="{{ route('search') }}">Search<br /></a></li>
                <li><a href="{{ route('user/settings') }}">Settings<br /></a></li>
                <li><a href="{{ route('user/referrals') }}">Referrals<br /></a></li>
            </ul>
        </div>
    @else
        <div class="panel-footer">
            <h4>Log In</h4>
        </div>
    @endif
</div>

<div class="panel panel-default">
    <div class="panel-body">
        @if( ! is_null($currentUser))
            <form class="form-inline" role="form">
                <div class="chat-control row">
                    <div class="col-md-12">
                        @if( ! $currentUser->isBannedFromChat())
                            <div class="input-group">
                                <span class="input-group-addon input-group-spectrum">
                                    <input type="hidden" id="spectrum-sidebar-chat" name="chat_color" value="000000" />
                                </span>
                                <input type="text" id="sidebar-chat-message" class="form-control input-sm" name="chat_message" style="color: #000000;" />
                                <span class="input-group-btn">
                                    <button type="button" name="chat_submit" class="btn btn-primary btn-sm"><i class="fa fa-bullhorn"></i></button>
                                </span>
                            </div>
                        @else
                            <p class="form-control-static">You are banned from chat until <strong>{{ $currentUser->chat_banned_until->format('F jS, Y g:i A') }}</strong> for the following reason: <em>{{ $currentUser->chat_ban_reason }}</em></p>
                        @endif
                    </div>
                </div>
            </form>

            <button class="btn btn-success btn-xs btn-block" data-toggle="modal" data-target="#model-chat-turns">
                Make It Rain Turns
            </button>
        @endif
        <ul id="sidebar-chat" class="chats">
            <li class="in">
                <div class="message">
                    <span class="body-grey"><em>Loading chat messages...</em></span>
                </div>
            </li>
        </ul>
    </div>
</div>

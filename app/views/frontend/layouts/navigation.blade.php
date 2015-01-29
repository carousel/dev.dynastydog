<div class="row">
    <div class="col-md-12 no-padding">
        <header class="navbar" role="navigation">
            <div class="navbar-header">
                <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand hidden-lg hidden-md hidden-sm" href="{{ route('home') }}">{{ Config::get('game.name') }}</a>
            </div>
            <nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
                <ul class="nav navbar-nav">
                	@if (is_null($currentUser))
                    <li><a href="{{ route('home') }}">Home</a></li>
                    @endif

                    <li><a href="{{ route('user/kennel') }}">Kennel</a></li>
                    <li><a href="{{ route('goals') }}">Goals</a></li>
                    <li><a href="{{ route('imports') }}">Import Dogs</a></li>
                    <li><a href="{{ route('contests') }}">Contests</a></li>
                    <li><a href="{{ route('forums') }}">Forums</a></li>
                    <li><a href="{{ route('breed_registry') }}">Breed Registry</a></li>
                    <li><a href="{{ route('cash_shop') }}">Cash Shop</a></li>
                    <li><a href="{{ route('news') }}">News</a></li>
                    <li><a href="{{ route('help') }}">Help</a></li>
                    <li class="navbar-text navbar-right hidden-sm hidden-md">{{ Carbon::now()->format('g:i A') }}</li>
                    @if ( ! is_null($currentUser))
                    <li class="navbar-right hidden-sm hidden-md">
                        <a href="{{ route('online') }}">
                            <span class="badge">{{ $totalOnline = User::whereOnline()->count() }}</span>
                            {{ Str::plural('Player', $totalOnline) }}
                            Online
                        </a>
                    </li>
                    @endif
                </ul>
            </nav>
        </header>
    </div>
</div>

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
            </div>
            <nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
                <ul class="nav navbar-nav">
                    <li><a href="{{ route('admin') }}">Home</a></li>
                    <li><a href="{{ route('admin/alpha') }}">Alpha</a></li>
                    <li><a href="{{ route('admin/breeds') }}">Breeds</a></li>
                    <li><a href="{{ route('admin/characteristics') }}">Characteristics</a></li>
                    <li><a href="{{ route('admin/dogs') }}">Dogs</a></li>
                    <li><a href="{{ route('admin/forums') }}">Forums</a></li>
                    <li><a href="{{ route('admin/genetics') }}">Genetics</a></li>
                    <li><a href="{{ route('admin/goals') }}">Goals</a></li>
                    <li><a href="{{ route('admin/health') }}">Health</a></li>
                    <li><a href="{{ route('admin/help') }}">Help</a></li>
                    <li><a href="{{ route('admin/news') }}">News</a></li>
                    <li><a href="{{ route('admin/users') }}">Users</a></li>
                    <li class="navbar-text navbar-right">{{ Carbon::now()->format('g:i A') }}</li>
                    <li class="navbar-right">
                        <a href="{{ route('online') }}">
                            <span class="badge">{{ $totalOnline = User::whereOnline()->count() }}</span>
                            {{ Str::plural('Player', $totalOnline) }}
                            Online
                        </a>
                    </li>
                </ul>
            </nav>
        </header>
    </div>
</div>
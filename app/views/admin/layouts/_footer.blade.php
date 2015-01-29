            <footer class="row">
                <div class="col-xs-8">
                    @if( ! is_null($currentUser))
                        <a href="{{ route('auth/logout') }}">Logout</a> | 
                    @endif
                    <a href="{{ route('tos') }}">Terms of Service</a> | 
                    <a href="{{ route('privacy') }}">Privacy Policy</a> | 
                    <a href="{{ route('community_guidelines') }}">Community Rules</a> | 
                    <a href="{{ route('staff') }}">Contact Staff</a>
                    <span class="hidden-xs hidden-lg">| <a href="{{ route('online') }}">
                        {{ $totalOnline = User::whereOnline()->count() }}
                        {{ Str::plural('Player', $totalOnline) }}
                        Online
                    </a></span>
                    <span class="hidden-xs hidden-lg">| {{ Carbon::now()->format('g:i A') }}</span>
                </div>
                <div class="col-xs-4 text-right">
                    &copy; Bausman {{ ($curYear = Carbon::now()->format('Y')) == 2014 ? $curYear : '2014 - '.$curYear }}
                </div>
            </footer>
        </div>

        <!-- JS -->
        @section('js_assets')
            <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
            <script type="text/javascript" src="{{ asset('assets/vendor/themes/cloud_admin/js/bootstrap.min.js') }}"></script>
            <script type="text/javascript" src="{{ asset('assets/vendor/themes/cloud_admin/js/sparkline.js') }}"></script>
            <script type="text/javascript" src="{{ asset('assets/vendor/themes/cloud_admin/js/tiny-scrollbar.js') }}"></script>
            <script type="text/javascript" src="{{ asset('assets/vendor/bgrins-spectrum/spectrum.js') }}"></script>
            <script type="text/javascript" src="{{ asset('assets/vendor/bootstrap-growl/jquery.bootstrap-growl.min.js') }}"></script>
            <script type="text/javascript" src="{{ asset('assets/vendor/bootstrap-progressbar/bootstrap-progressbar.min.js') }}"></script>
            <script type="text/javascript" src="{{ asset('assets/vendor/bootstrap-slider/js/bootstrap-slider.js') }}"></script>
            <script type="text/javascript" src="{{ asset('assets/vendor/momentjs/js/moment.js') }}"></script>
            <script type="text/javascript" src="{{ asset('assets/vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}"></script>
            <script type="text/javascript" src="{{ asset('assets/js/site.js') }}?20141212"></script>
        @show

        <!-- Growl Notifications -->
        @include('frontend/notifications/growl')

    </body>
</html>
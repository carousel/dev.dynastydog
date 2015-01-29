@include('frontend/layouts/_header')

<div class="main row hidden-md hidden-lg">
    <div class="content col-sm-12 col-sx-12">
        <div class="panel panel-default no-margin">
            <div class="panel-body">
                <!-- Notifications -->
                @include('frontend/notifications/basic')

                <br />
            </div>
        </div>
    </div>
</div>

<div class="main row">
    <div class="content col-md-12" id="content">
        <div class="panel panel-default">
            <div class="panel-body">

                <div class="hidden-sm hidden-xs">
                    <!-- Notifications -->
                    @include('frontend/notifications/basic')
                </div>

                <!-- Content -->
                @yield('content')
            </div>
        </div>
    </div>
</div>

@include('frontend/layouts/_footer')

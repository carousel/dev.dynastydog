<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>
            @section('title')
            {{ Config::get('game.name') }} | Dog Breeding Game
            @show
        </title>
        @section('css_assets')
            <link type="text/css" href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet" media="screen" />
            <link type="text/css" href="{{ asset('assets/vendor/themes/cloud_admin/css/main.css') }}" rel="stylesheet" media="screen" />
            <link type="text/css" href="http://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.css" rel="stylesheet" />
            <script type="text/javascript" src="{{ asset('assets/vendor/themes/cloud_admin/js/respond.min.js') }}"></script><![endif]-->
            <link type="text/css" href="{{ asset('assets/vendor/bgrins-spectrum/spectrum.css') }}" rel="stylesheet" />
            <link type="text/css" href="{{ asset('assets/vendor/bootstrap-progressbar/css/bootstrap-progressbar-3.1.1.css') }}" rel="stylesheet" />
            <link type="text/css" href="{{ asset('assets/vendor/bootstrap-slider/css/bootstrap-slider.css') }}" rel="stylesheet" />
            <link type="text/css" href="{{ asset('assets/vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
            <link type="text/css" href="{{ asset('assets/css/layout.min.css') }}" rel="stylesheet" />
        @show
        <script type="text/javascript" src="{{ asset('assets/vendor/themes/cloud_admin/js/jquery.js') }}"></script>
        <script type="text/javascript">
        var Globals = {
            root  : '{{{ Request::root() }}}/',
        };
        </script>
    </head>
    <body>
        <div class="container">
            <div class="row collapse" id="masthead-wrapper">
                <div class="masthead col-md-12 hidden-xs">
                    <a class="logo" href="{{{ is_null($currentUser) ? route('home') : route('user/kennel') }}}" data-content="{{ Config::get('game.name') }}">
                        <img src="{{ asset('assets/img/layout/logo.png') }}" class="img-responsive" alt="" />
                    </a>
                    <span id="toggle-masthead" class="toggle-masthead btn btn-default" data-toggle="collapse" data-target="#masthead-wrapper">
                        <i class="fa fa-angle-double-down"></i>
                    </span>
                </div>
            </div>

            <!-- Navigation -->
            @include('admin/layouts/_navigation')
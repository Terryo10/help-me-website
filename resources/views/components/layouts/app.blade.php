<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-app-env="{{ env('APP_ENV') }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" type="image/x-icon" href="/logo.png">
    <title>HelpMe Donation Site</title>

    {{-- @viteReactRefresh --}}

    <link href="{{asset('aidus-live/assets/css/themify-icons.css') }}" rel="stylesheet">
    <link href="{{asset('aidus-live/assets/css/flaticon_aidus.css') }}" rel="stylesheet">
    <link href="{{asset('aidus-live/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{asset('aidus-live/assets/css/animate.css') }}" rel="stylesheet">
    <link href="{{asset('aidus-live/assets/css/owl.carousel.css') }}" rel="stylesheet">
    <link href="{{asset('aidus-live/assets/css/owl.theme.css') }}" rel="stylesheet">
    <link href="{{asset('aidus-live/assets/css/slick.css') }}" rel="stylesheet">
    <link href="{{asset('aidus-live/assets/css/slick-theme.css') }}" rel="stylesheet">
    <link href="{{asset('aidus-live/assets/css/swiper.min.css') }}" rel="stylesheet">
    <link href="{{asset('aidus-live/assets/css/owl.transitions.css') }}" rel="stylesheet">
    <link href="{{asset('aidus-live/assets/css/jquery.fancybox.css') }}" rel="stylesheet">
    <link href="{{asset('aidus-live/assets/css/odometer-theme-default.css') }}" rel="stylesheet">
    <link href="{{asset('aidus-live/assets/sass/style.css') }}" rel="stylesheet">

    @livewireStyles

    {{-- @vite("resources/app/index.tsx") --}}

    <!-- All CSS Files -->

</head>

<body>
     <div class="page-wrapper">

        <!-- start preloader -->
        <div class="preloader">
            <div class="vertical-centered-box">
                <div class="content">
                    <div class="loader-circle"></div>
                    <div class="loader-line-mask">
                        <div class="loader-line"></div>
                    </div>
                    <img src="{{asset('aidus-live/assets/images/preloader.png') }}" alt="">
                </div>
            </div>
        </div>

        @yield('content')

        @livewireScripts

        {{-- <div id="root"></div> --}}
    </div>

    <script src="{{asset('aidus-live/assets/js/jquery.min.js') }}"></script>
    <script src="{{asset('aidus-live/assets/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Plugins for this template -->
    <script src="{{asset('aidus-live/assets/js/modernizr.custom.js') }}"></script>
    <script src="{{asset('aidus-live/assets/js/jquery.dlmenu.js') }}"></script>
    <script src="{{asset('aidus-live/assets/js/jquery-plugin-collection.js') }}"></script>
    <!-- Custom script for this template -->
    <script src="{{asset('aidus-live/assets/js/script.js') }}"></script>

</body>

</html>

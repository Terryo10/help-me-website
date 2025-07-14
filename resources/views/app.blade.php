<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('template/assets/images/favicon.png') }}" type="image/x-icon">

    <title>{{ isset($title) ? $title . ' - ' : '' }}HelpMe.co.zw - Zimbabwe's Premier Fundraising Platform</title>

    <!-- SEO Meta Tags -->
    <meta name="keywords"
        content="Zimbabwe fundraising, donate Zimbabwe, crowdfunding Zimbabwe, help Zimbabwe, charity Zimbabwe">
    <meta name="description"
        content="HelpMe.co.zw - Zimbabwe's trusted fundraising platform. Create campaigns, donate to causes, and make a difference in communities across Zimbabwe.">

    <!-- CSS Dependencies -->
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('template/assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Nice Select CSS -->
    <link rel="stylesheet" href="{{ asset('template/assets/vendor/nice-select/css/nice-select.css') }}">
    <!-- Magnific Popup CSS -->
    <link rel="stylesheet" href="{{ asset('template/assets/vendor/magnific-popup/css/magnific-popup.css') }}">
    <!-- Slick CSS -->
    <link rel="stylesheet" href="{{ asset('template/assets/vendor/slick/css/slick.css') }}">
    <!-- Odometer CSS -->
    <link rel="stylesheet" href="{{ asset('template/assets/vendor/odometer/css/odometer.css') }}">
    <!-- Animate CSS -->
    <link rel="stylesheet" href="{{ asset('template/assets/vendor/animate/animate.css') }}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('template/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/notifier.css') }}">

    <!-- Livewire Styles -->
    @livewireStyles
</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <span class="loader"></span>
    </div>
    <main>
        @yield('content')
        <!-- Livewire Scripts -->
        @livewireScripts
    </main>

    <!-- Scroll to Top -->
    <a href="#" class="scrollToTop"><i class="bi bi-chevron-double-up"></i></a>

    <!-- JavaScript Dependencies -->
    <!-- jQuery -->
    <script src="{{ asset('template/assets/vendor/jquery/jquery-3.6.3.min.js') }}"></script>
    <!-- Bootstrap JS -->
    <script src="{{ asset('template/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Nice Select JS -->
    <script src="{{ asset('template/assets/vendor/nice-select/js/jquery.nice-select.min.js') }}"></script>
    <!-- Magnific Popup JS -->
    <script src="{{ asset('template/assets/vendor/magnific-popup/js/jquery.magnific-popup.min.js') }}"></script>
    <!-- Circular Progress Bar -->
    <script src="https://cdn.jsdelivr.net/gh/tomik23/circular-progress-bar@latest/docs/circularProgressBar.min.js">
    </script>
    <!-- Slick JS -->
    <script src="{{ asset('template/assets/vendor/slick/js/slick.min.js') }}"></script>
    <!-- Odometer JS -->
    <script src="{{ asset('template/assets/vendor/odometer/js/odometer.min.js') }}"></script>
    <!-- Viewport JS -->
    <script src="{{ asset('template/assets/vendor/viewport/viewport.jquery.js') }}"></script>
    <!-- jQuery UI JS -->
    <script src="{{ asset('template/assets/vendor/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- WOW JS -->
    <script src="{{ asset('template/assets/vendor/wow/wow.min.js') }}"></script>
    <!-- jQuery Validate -->
    <script src="{{ asset('template/assets/vendor/jquery-validate/jquery.validate.min.js') }}"></script>

    <!-- Plugins JS -->
    <script src="{{ asset('template/assets/js/plugins.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/notifier.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('template/assets/js/main.js') }}"></script>

    <script>
        @if (session('failed'))
        notifier.show('Failed!', '{{ session('failed') }}', 'danger',
            '{{ asset('assets/images/notification/high_priority-48.png') }}', 4000);
    @endif
    @if ($errors = session('errors'))
        @if (is_object($errors))
            @foreach ($errors->all() as $error)
                notifier.show('Error!', '{{ $error }}', 'danger',
                    '{{ asset('assets/images/notification/high_priority-48.png') }}', 4000);
            @endforeach
        @else
            notifier.show('Error!', '{{ session('errors') }}', 'danger',
                '{{ asset('assets/images/notification/high_priority-48.png') }}', 4000);
        @endif
    @endif
    @if (session('successful'))
        notifier.show('Successfully!', '{{ session('successful') }}', 'success',
            '{{ asset('assets/images/notification/ok-48.png') }}', 4000);
    @endif
    @if (session('success'))
        notifier.show('Success!', '{{ session('success') }}', 'success',
            '{{ asset('assets/images/notification/ok-48.png') }}', 4000);
    @endif
    @if (session('warning'))
        notifier.show('Warning!', '{{ session('warning') }}', 'warning',
            '{{ asset('assets/images/notification/medium_priority-48.png') }}', 4000);
    @endif

    @if (session('status'))
        notifier.show('Great!', '{{ session('status') }}', 'info',
            '{{ asset('assets/images/notification/survey-48.png') }}', 4000);
    @endif
    </script>

</body>

</html>

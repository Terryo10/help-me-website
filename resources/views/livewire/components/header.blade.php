 <div>
 <header class="header-section index">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav class="navbar navbar-expand-xl nav-shadow" id="#navbar">
                        <a   class="navbar-brand" href="{{ route('home') }}">
                            <img src="{{ asset('template/assets/images/logo.png') }}" class="logo" alt="HelpMe.co.zw Logo" style="height: 60px;">
                        </a>
                        <a class="navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                            <i class="bi bi-list"></i>
                        </a>

                        <div class="collapse navbar-collapse ms-auto" id="navbar-content">
                            <div class="main-menu index-page">
                                <ul class="navbar-nav mb-lg-0 mx-auto">
                                    <li class="nav-item">
                                        <a   class="nav-link" href="{{ route('home') }}">Home</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Campaigns</a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="/campaigns">Browse Campaigns</a></li>
                                            <li><a class="dropdown-item" href="/campaigns?category=medical">Medical Campaigns</a></li>
                                            <li><a class="dropdown-item" href="/campaigns?category=education">Education Campaigns</a></li>
                                            <li><a class="dropdown-item" href="/campaigns?category=emergency">Emergency Campaigns</a></li>
                                        </ul>
                                    </li>

                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">About</a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="/about-us">About Us</a></li>
                                            <li><a class="dropdown-item" href="/faq">FAQs</a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/contact-us">Contact</a>
                                    </li>
                                </ul>
                                <div class="nav-right d-none d-xl-block">
                                    <div class="nav-right__search">
                                        <a href="javascript:void(0)" class="nav-right__search-icon btn_theme icon_box btn_bg_white">
                                            <i class="bi bi-search"></i>
                                            <span></span>
                                        </a>
                                        @auth
                                            <div class="dropdown">
                                                <a class="btn_theme dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    {{ Auth::user()->name }}
                                                    <span></span>
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a></li>
                                                    <li><a class="dropdown-item" href="#">My Campaigns</a></li>
                                                    <li><a class="dropdown-item" href="#">My Donations</a></li>
                                                    <li><a class="dropdown-item" href="{{ route('settings.profile') }}">Settings</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="{{ route('logout') }}">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">Logout</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        @else
                                            <a   href="{{ route('login') }}" class="btn_theme btn_theme_active">Sign In <i class="bi bi-arrow-up-right"></i><span></span></a>
                                            <a   href="#" class="btn_theme">Start Campaign <i class="bi bi-arrow-up-right"></i><span></span></a>
                                        @endauth
                                    </div>
                                    <div class="nav-right__search-inner">
                                        <div class="nav-search-inner__form">
                                            <form method="GET" id="search" class="inner__form">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" name="q" placeholder="Search campaigns..." required>
                                                    <button type="submit" class="search_icon"><i class="bi bi-search"></i></button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Offcanvas Mobile Menu -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight">
        <div class="offcanvas-body custom-nevbar">
            <div class="row">
                <div class="col-md-7 col-xl-8">
                    <div class="custom-nevbar__left">
                        <button type="button" class="close-icon d-md-none ms-auto" data-bs-dismiss="offcanvas" aria-label="Close">
                            <i class="bi bi-x"></i>
                        </button>
                        <ul class="custom-nevbar__nav mb-lg-0">
                            <li class="menu_item">
                                <a class="menu_link" href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="menu_item dropdown">
                                <a class="menu_link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Campaigns</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">Browse Campaigns</a></li>
                                    <li><a class="dropdown-item" href="#">Medical</a></li>
                                    <li><a class="dropdown-item" href="#">Education</a></li>
                                    <li><a class="dropdown-item" href="#">Emergency</a></li>
                                    <li><a class="dropdown-item" href="#">Community</a></li>
                                </ul>
                            </li>
                            <li class="menu_item">
                                <a class="menu_link" href="#">About Us</a>
                            </li>
                            <li class="menu_item">
                                <a class="menu_link" href="#">Contact</a>
                            </li>
                            @auth
                                <li class="menu_item">
                                    <a   class="menu_link" href="{{ route('dashboard') }}">Dashboard</a>
                                </li>
                                <li class="menu_item">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="menu_link border-0 bg-transparent">Logout</button>
                                    </form>
                                </li>
                            @else
                                <li class="menu_item">
                                    <a   class="menu_link" href="{{ route('login') }}">Sign In</a>
                                </li>
                                <li class="menu_item">
                                    <a class="menu_link" href="#">Start Campaign</a>
                                </li>
                            @endauth
                        </ul>
                    </div>
                </div>
                <div class="col-md-5 col-xl-4">
                    <div class="custom-nevbar__right">
                        <div class="custom-nevbar__top d-none d-md-block">
                            <button type="button" class="close-icon ms-auto" data-bs-dismiss="offcanvas" aria-label="Close">
                                <i class="bi bi-x"></i>
                            </button>
                            <div class="custom-nevbar__right-thumb mb-auto">
                                <img src="{{ asset('template/assets/images/logo.png') }}" alt="HelpMe.co.zw">
                            </div>
                        </div>
                        <ul class="custom-nevbar__right-location">
                            <li>
                                <p class="mb-2">Phone:</p>
                                <a href="tel:+263712345678" class="fs-4 contact">+263 71 234 5678</a>
                            </li>
                            <li class="location">
                                <p class="mb-2">Email:</p>
                                <a href="mailto:hello@helpme.co.zw" class="fs-4 contact">hello@helpme.co.zw</a>
                            </li>
                            <li class="location">
                                <p class="mb-2">Location:</p>
                                <p class="fs-4 contact">Harare, Zimbabwe</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

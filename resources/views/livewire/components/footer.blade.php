<footer class="footer">
        <div class="container">
            <div class="row section gy-5 gy-xl-0">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="about-company wow fadeInLeft" data-wow-duration="0.8s">
                        <div class="footer__logo mb-4">
                            <a   href="{{ route('home') }}">
                                <img src="{{ asset('template/assets/images/logo.png') }}" alt="HelpMe.co.zw">
                            </a>
                        </div>
                        <p>HelpMe.co.zw is Zimbabwe's premier fundraising platform, connecting generous hearts with meaningful causes across our beautiful nation.</p>
                        <div class="social mt_32">
                            <a href="#" class="btn_theme social_box"><i class="bi bi-facebook"></i><span></span></a>
                            <a href="#" class="btn_theme social_box"><i class="bi bi-twitter"></i><span></span></a>
                            <a href="#" class="btn_theme social_box"><i class="bi bi-instagram"></i><span></span></a>
                            <a href="#" class="btn_theme social_box"><i class="bi bi-linkedin"></i><span></span></a>
                            <a href="#" class="btn_theme social_box"><i class="bi bi-whatsapp"></i><span></span></a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="footer__contact ms-sm-4 ms-xl-0 wow fadeInUp" data-wow-duration="0.8s">
                        <h4 class="footer__title mb-4">Contact</h4>
                        <div class="footer__content">
                            <a href="tel:+263712345678">
                                <span class="btn_theme social_box"><i class="bi bi-telephone-plus"></i></span>
                                +263 71 234 5678
                                <span></span>
                            </a>
                            <a href="mailto:hello@helpme.co.zw">
                                <span class="btn_theme social_box"><i class="bi bi-envelope-open"></i></span>
                                hello@helpme.co.zw
                                <span></span>
                            </a>
                            <a href="#">
                                <span class="btn_theme social_box"><i class="bi bi-geo-alt"></i></span>
                                Harare, Zimbabwe
                                <span></span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="newsletter wow fadeInDown" data-wow-duration="0.8s">
                        <h4 class="footer__title mb-4">Newsletter</h4>
                        <p class="mb_32">Subscribe to our newsletter to get updates on new campaigns and success stories</p>
                        <form method="POST" autocomplete="off" class="newsletter__content-form" action="{{ route('news-letter') }}">
                            @csrf
                            <div class="input-group">
                                <input type="email" class="form-control" id="sMail" name="email" placeholder="Email Address" required>
                                <button type="submit" class="emailSubscribe btn_theme btn_theme_active" name="emailSubscribe">
                                    <i class="bi bi-cursor"></i>
                                    <span></span>
                                </button>
                            </div>
                            <span id="subscribeMsg"></span>
                        </form>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="quick-link ms-sm-4 ms-xl-0 wow fadeInRight" data-wow-duration="0.8s">
                        <h4 class="footer__title mb-4">Quick Links</h4>
                        <ul>
                            <li><a href="/about-us">About Us</a></li>
                            <li><a href="/campaigns">Browse Campaigns</a></li>
                            <li><a href="/faq">FAQs</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="footer__copyright">
                        <p class="copyright text-center">
                            Copyright © <span id="copyYear">{{ date('Y') }}</span>
                            <a href="#" class="secondary_color">HelpMe.co.zw</a>.
                            All rights reserved.
                        </p>
                        <ul class="footer__copyright-conditions">
                            <li><a href="/privacy-policy">Privacy Policy</a></li>
                            <li><a href="/terms-condition">Terms & Conditions</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

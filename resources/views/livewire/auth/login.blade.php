<div>
    <!-- Login Section Start -->
    <section class="sign-up section">
        <div class="container">
            <div class="row gy-5 gy-xl-0 justify-content-center justify-content-lg-between align-items-center">
                <div class="col-12 col-lg-7 col-xxl-6">
                    <form wire:submit="login" class="sign-up__form me-lg-4 me-xxl-0 wow fadeInUp" data-wow-duration="0.8s">
                        <h3 class="sign-up__title wow fadeInUp" data-wow-duration="0.8s">Welcome Back!</h3>
                        <p class="sign-up__sub-title mb_40">Sign in to continue your fundraising journey</p>

                        <!-- Session Status -->
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="sign-up__form-part">
                            <div class="input-single">
                                <label class="label" for="email">Email Address</label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       wire:model="email"
                                       id="email"
                                       placeholder="Enter your email address..."
                                       required
                                       autofocus
                                       autocomplete="username">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="input-single">
                                <label class="label" for="password">Password</label>
                                <div class="input-pass">
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           wire:model="password"
                                           id="password"
                                           placeholder="Enter your password..."
                                           required
                                           autocomplete="current-password">
                                    <span class="password-eye-icon"></span>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row align-items-center">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               wire:model="remember"
                                               id="remember">
                                        <label class="form-check-label" for="remember">
                                            Remember me
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6 text-end">
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}"
                                           class="signin"
                                            >
                                            Forgot password?
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <p class="have_account mt_24">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="signin"  >Sign Up</a>
                        </p>

                        <button type="submit"
                                class="btn_theme btn_theme_active mt_32"
                                wire:loading.attr="disabled">
                            <div wire:loading.remove>Sign In</div>
                            <div wire:loading>Signing In...</div>
                            <i class="bi bi-arrow-up-right"></i>
                            <div></div>
                        </button>
                    </form>
                </div>
                <div class="col-12 col-sm-7 col-lg-5 col-xxl-5">
                    <div class="sign-up__thumb previewShapeY unset-xxl wow fadeInDown" data-wow-duration="0.8s">
                        <img src="{{ asset('template/assets/images/sign_up.png') }}" alt="Welcome to HelpMe.co.zw">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Login Section End -->
</div>

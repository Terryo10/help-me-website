<div>
        <!-- Registration Section Start -->

    <section class="sign-up section">
        <div class="container">
            <div class="row gy-5 gy-xl-0 justify-content-center justify-content-lg-between align-items-center">
                <div class="col-12 col-lg-7 col-xxl-6">
                    <form wire:submit="register" class="sign-up__form me-lg-4 me-xxl-0 wow fadeInUp" data-wow-duration="0.8s">
                        <h3 class="sign-up__title wow fadeInUp" data-wow-duration="0.8s">Join HelpMe.co.zw!</h3>
                        <p class="sign-up__sub-title mb_40">Create your account and start making a difference in Zimbabwe</p>
                        
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="sign-up__form-part">
                            <div class="input-single">
                                <label class="label" for="name">Full Name</label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       wire:model="name" 
                                       id="name" 
                                       placeholder="Enter your full name..." 
                                       required 
                                       autofocus 
                                       autocomplete="name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="input-single">
                                <label class="label" for="email">Email Address</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       wire:model="email" 
                                       id="email" 
                                       placeholder="Enter your email address..." 
                                       required 
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
                                           placeholder="Create a secure password..." 
                                           required 
                                           autocomplete="new-password">
                                    <span class="password-eye-icon"></span>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="input-single">
                                <label class="label" for="password_confirmation">Confirm Password</label>
                                <div class="input-pass">
                                    <input type="password" 
                                           class="form-control @error('password_confirmation') is-invalid @enderror" 
                                           wire:model="password_confirmation" 
                                           id="password_confirmation" 
                                           placeholder="Confirm your password..." 
                                           required 
                                           autocomplete="new-password">
                                    <span class="password-eye-icon"></span>
                                </div>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Privacy Policy</a>
                                </label>
                            </div>
                        </div>

                        <p class="have_account mt_24">
                            Already have an account? 
                            <a href="{{ route('login') }}" class="signin" wire:navigate>Sign In</a>
                        </p>

                        <button type="submit" 
                                class="btn_theme btn_theme_active mt_32" 
                                wire:loading.attr="disabled">
                            <span wire:loading.remove>Create Account</span>
                            <span wire:loading>Creating Account...</span>
                            <i class="bi bi-arrow-up-right"></i>
                            <span></span>
                        </button> 
                    </form>
                </div>
                <div class="col-12 col-sm-7 col-lg-5 col-xxl-5">
                    <div class="sign-up__thumb previewShapeY unset-xxl wow fadeInDown" data-wow-duration="0.8s">
                        <img src="{{ asset('template/assets/images/sign_up.png') }}" alt="Join HelpMe.co.zw">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Registration Section End -->
</div>
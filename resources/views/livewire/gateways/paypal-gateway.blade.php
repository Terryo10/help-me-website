<div>
    <livewire:header />
    <section class="sign-up section">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="sign-up__form wow fadeInUp" data-wow-duration="0.8s">
                        <h3 class="sign-up__title text-center mb-2">PayPal Payment</h3>
                        <p class="sign-up__sub-title text-center mb_40">Complete your payment securely with PayPal</p>

                        @if (session()->has('message'))
                            <p class="alert alert-success mb-3" style="text-decoration: none !mportant;" role="alert">
                                {{ session('message') }}
                            </p>
                        @endif
                        @if (session()->has('error'))
                            <p class="alert alert-danger mb-3" role="alert">
                                {{ session('error') }}
                            </p>
                        @endif

                        <div class="sign-up__form-part mb-4">
                            <div class="input-single mb-3">
                                <label class="label">Donation ID</label>
                                <input type="text" class="form-control" value="#{{ $donation_id }}" readonly />
                            </div>
                            <div class="input-single mb-3">
                                <label class="label">Amount</label>
                                <input type="text" class="form-control" value="${{ number_format($amount, 2) }}"
                                    readonly />
                            </div>
                        </div>

                        @if ($paymentSent === 'false')
                        <form wire:submit.prevent="createPayment">
                            <div class="sign-up__form-part">
                                <div class="input-single mb-3">
                                    <label class="label" for="email">Email Address</label>
                                    <input type="email" id="email" wire:model="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        placeholder="Enter your email address" required />
                                    @error('email')
                                    <p class="invalid-feedback">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <button type="submit" class="btn_theme btn_theme_active w-100 mt-3"
                                wire:loading.attr="disabled" wire:target="createPayment">
                                <div wire:loading.remove wire:target="createPayment">Create PayPal Payment</div>
                                <div wire:loading wire:target="createPayment">Creating Payment...</div>
                            </button>
                        </form>
                        @endif

                        @if ($paymentSent === 'true')
                        <div class="text-center">
                            <p class="alert alert-info mb-4">
                                <strong>Payment Ready!</strong><br>
                                Click the button below to complete your payment on PayPal
                            </p>
                            <button wire:click="redirectToPayPal" class="btn_theme btn_theme_active w-100 mb-3">
                                Continue to PayPal
                            </button>
                            <div class="text-center">
                                <p class="text-gray-600 text-sm mb-3">Already completed payment on PayPal?</p>
                                <button wire:click="checkPayment" class="btn btn-secondary w-100"
                                    wire:loading.attr="disabled" wire:target="checkPayment">
                                    <div wire:loading.remove wire:target="checkPayment">Check Payment Status</div>
                                    <div wire:loading wire:target="checkPayment">Checking...</div>
                                </button>
                            </div>
                        </div>
                        @endif

                        <div class="mt-4 pt-3 border-top text-center text-muted small">
                            <svg class="w-4 h-4 mr-1 d-inline-block" fill="currentColor" viewBox="0 0 20 20"
                                style="vertical-align:middle;width:1em;height:1em;">
                                <path fill-rule="evenodd"
                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Secured by PayPal
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <livewire:footer />
</div>
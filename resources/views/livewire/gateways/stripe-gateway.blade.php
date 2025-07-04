<div>
    <livewire:header />
    <section class="sign-up section">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="sign-up__form wow fadeInUp" data-wow-duration="0.8s">
                        <h3 class="sign-up__title text-center mb-2">Stripe Payment</h3>
                        <p class="sign-up__sub-title text-center mb_40">Complete your payment securely with Stripe</p>
                        @if (session()->has('message'))
                            <div class="alert alert-success mb-3" role="alert">
                                {{ session('message') }}
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger mb-3" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif
                        <div class="sign-up__form-part mb-4">
                            <div class="input-single mb-3">
                                <label class="label">Donation ID</label>
                                <input type="text" class="form-control" value="#{{ $donation_id }}" readonly />
                            </div>
                            <div class="input-single mb-3">
                                <label class="label">Amount</label>
                                <input type="text" class="form-control" value="${{ number_format($amount, 2) }}" readonly />
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
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-single mb-3">
                                    <label class="label" for="cardholderName">Cardholder Name</label>
                                    <input type="text" id="cardholderName" wire:model="cardholderName"
                                        class="form-control @error('cardholderName') is-invalid @enderror"
                                        placeholder="Full name on card" required />
                                    @error('cardholderName')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-single mb-3">
                                    <label class="label" for="cardNumber">Card Number</label>
                                    <input type="text" id="cardNumber" wire:model="cardNumber"
                                        class="form-control @error('cardNumber') is-invalid @enderror"
                                        placeholder="1234 5678 9012 3456" maxlength="19" required />
                                    @error('cardNumber')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <div class="input-single mb-3">
                                            <label class="label" for="expiryMonth">Month</label>
                                            <select id="expiryMonth" wire:model="expiryMonth"
                                                class="form-control @error('expiryMonth') is-invalid @enderror" required>
                                                <option value="">MM</option>
                                                @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                                                @endfor
                                            </select>
                                            @error('expiryMonth')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-single mb-3">
                                            <label class="label" for="expiryYear">Year</label>
                                            <select id="expiryYear" wire:model="expiryYear"
                                                class="form-control @error('expiryYear') is-invalid @enderror" required>
                                                <option value="">YYYY</option>
                                                @for($i = date('Y'); $i <= date('Y') + 10; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                            @error('expiryYear')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-single mb-3">
                                            <label class="label" for="cvc">CVC</label>
                                            <input type="text" id="cvc" wire:model="cvc"
                                                class="form-control @error('cvc') is-invalid @enderror"
                                                placeholder="123" maxlength="4" required />
                                            @error('cvc')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn_theme btn_theme_active w-100 mt-3"
                                wire:loading.attr="disabled" wire:target="createPayment">
                                <div wire:loading.remove wire:target="createPayment">Pay with Stripe</div>
                                <div wire:loading wire:target="createPayment">Processing Payment...</div>
                            </button>
                        </form>
                        @endif
                        @if ($paymentSent === 'true')
                        <div class="text-center">
                            <div class="alert alert-info mb-4">
                                <strong>Authentication Required!</strong><br>
                                Your payment requires additional verification. Please complete the authentication process.
                            </div>
                            <div class="text-center">
                                <p class="text-gray-600 text-sm mb-3">Completed authentication?</p>
                                <button wire:click="checkPayment" class="btn btn-secondary w-100"
                                    wire:loading.attr="disabled" wire:target="checkPayment">
                                    <div wire:loading.remove wire:target="checkPayment">Check Payment Status</div>
                                    <div wire:loading wire:target="checkPayment">Checking...</div>
                                </button>
                            </div>
                        </div>
                        @endif
                        <div class="mt-4 pt-3 border-top text-center text-muted small">
                            <svg class="w-4 h-4 mr-1 d-inline-block" fill="currentColor" viewBox="0 0 20 20" style="vertical-align:middle;width:1em;height:1em;">
                                <path fill-rule="evenodd"
                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Secured by Stripe
                            <div class="flex justify-center mt-2 space-x-2">
                                <div class="text-xs text-gray-400">We accept:</div>
                                <div class="flex space-x-1">
                                    <span class="text-xs bg-gray-100 px-2 py-1 rounded">Visa</span>
                                    <span class="text-xs bg-gray-100 px-2 py-1 rounded">MC</span>
                                    <span class="text-xs bg-gray-100 px-2 py-1 rounded">Amex</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <livewire:footer />
</div>
<script>
    document.addEventListener('livewire:load', function () {
    // Format card number input
    document.getElementById('cardNumber').addEventListener('input', function (e) {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let matches = value.match(/\d{4,16}/g);
        let match = matches && matches[0] || '';
        let parts = [];
        for (let i = 0, len = match.length; i < len; i += 4) {
            parts.push(match.substring(i, i + 4));
        }
        if (parts.length) {
            e.target.value = parts.join(' ');
        } else {
            e.target.value = value;
        }
    });

    // Restrict CVC to numbers only
    document.getElementById('cvc').addEventListener('input', function (e) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });
});
</script>

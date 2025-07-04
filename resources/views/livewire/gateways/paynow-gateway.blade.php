<div>
    <livewire:header />
    <section class="sign-up section">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="sign-up__form wow fadeInUp mt-5 mb-5" data-wow-duration="0.8s">
                        <h3 class="sign-up__title text-center mb-2">Paynow Gateway</h3>
                        <p class="sign-up__sub-title text-center mb_40">Pay using Ecocash for the sum of ${{ $amount ??
                            '0' }}</p>
                        @if (session()->has('error'))
                        <p class="alert alert-danger mb-3" role="alert">
                            {{ session('error') }}
                        </p>
                        @endif
                        @if (session()->has('message'))
                        <p class="alert alert-success mb-3" role="alert">
                            {{ session('message') }}
                        </p>
                        @endif
                        <form wire:submit.prevent="createPayment">
                            <div class="sign-up__form-part">
                                <div class="input-single mb-3" style="display: none;">
                                    <label class="label" for="order_id">Donation ID</label>
                                    <input type="text" id="order_id" class="form-control" value="{{ $donation_id }}"
                                        readonly>
                                </div>
                                <div class="input-single mb-3">
                                    <label class="label" for="phone">Phone Number</label>
                                    <input type="text" id="phone"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        wire:model.defer="phone" placeholder="Enter your phone number">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <button type="submit" class="btn_theme btn_theme_active w-100 mt-3"
                                    wire:loading.attr="disabled">
                                    <div wire:loading.remove>Pay Now Using Ecocash</div>
                                    <div wire:loading>Please wait...</div>
                                </button>
                            </div>
                        </form>
                        @if ($paymentSent === "true")
                        <button class="btn btn-warning w-100 mt-3" wire:click="checkPayment"
                            wire:loading.attr="disabled">
                            <div wire:loading.remove>Check your initiated payment</div>
                            <div wire:loading>Checking...</div>
                        </button>
                        @endif
                        @if ($submitting === "true")
                        <div class="mt-3 alert alert-info">
                            Processing your payment. Please wait...
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <livewire:footer />
</div>
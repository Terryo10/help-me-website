<div>
    <livewire:header />


    <div class="container section">
        <div class="row">
            <div class="col-md-12">
                <h1>Paynow Gateway</h1>
            </div>
        </div>
    </div>

    <div class="container row justify-content-center align-items-center">
        <div class="col-md-6 card mt-4 p-0 m-0">
            <div class="card-header">
                <h4>Pay using ecocash for the sum of ${{$amount ?? "0"}}</h4>
            </div>
            <div class="card-body">
                @if (session()->has('error'))
                <div class="alert alert-danger mb-2" style="margin-top: 80px;">
                    {{ session('error') }}
                </div>
                @endif
                @if (session()->has('message'))
                <div class="alert alert-success mt-3">
                    {{ session('message') }}
                </div>
                @endif
                <form wire:submit.prevent="createPayment">
                    <div class="form-group" style="display: none;">
                        <label for="order_id">Donation ID</label>
                        <input type="text" id="order_id" class="form-control" value="{{ $donation_id }}" readonly>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" class="form-control mb-2" wire:model.defer="phone"
                            placeholder="Enter your phone number">
                        @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary mb-3 form-control" wire:loading.attr="disabled">
                        <span wire:loading.remove>Pay Now Using Ecocash</span>
                        <span wire:loading>Please wait...</span>
                    </button>

                </form>

                @if ($paymentSent === "true")
                <button class="btn btn-warning form-control" wire:click="checkPayment" wire:loading.attr="disabled">
                    <span wire:loading.remove>Check your initiated payment</span>
                    <span wire:loading>Checking...</span>
                </button>
                @endif

                @if ($submitting === "true")
                <div>
                    <div class="mt-3 alert alert-info">
                        Processing your payment. Please wait...
                    </div>
                </div>

                @endif

            </div>
        </div>
    </div>

    <livewire:footer />
</div>

<div>
    @if($show)
        <div class="donation-wizard-modal">
            <div class="donation-wizard-content">
                <!-- Stepper -->
                <div class="wizard-steps mb-4">
                    <span class="step {{ $step === 1 ? 'active' : '' }}">1. Donation</span>
                    <span class="step {{ $step === 2 ? 'active' : '' }}">2. Details</span>
                    <span class="step {{ $step === 3 ? 'active' : '' }}">3. Payment</span>
                    <span class="step {{ $step === 4 ? 'active' : '' }}">4. Confirm</span>
                </div>

                @if($step === 1)
                    <!-- Step 1: Amount and Donor Info -->
                    <h4>Donation Amount</h4>
                    <div class="mb-3">
                        <input type="number" wire:model="donationAmount" class="form-control" min="{{ $campaign->minimum_donation }}" placeholder="Amount">
                        @error('donationAmount') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    @unless(Auth::check())
                    <div class="mb-3">
                        <input type="text" wire:model="donorName" class="form-control mb-2" placeholder="Your name">
                        @error('donorName') <div class="text-danger">{{ $message }}</div> @enderror
                        <input type="email" wire:model="donorEmail" class="form-control" placeholder="Your email">
                        @error('donorEmail') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    @endunless
                    <button class="btn btn-primary w-100" wire:click="nextStep">Next</button>
                @elseif($step === 2)
                    <!-- Step 2: Comment/Review -->
                    <h4>Additional Details</h4>
                    <div class="mb-3">
                        <textarea wire:model="comment" class="form-control" placeholder="Leave a comment (optional)" rows="3"></textarea>
                        @error('comment') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" wire:model="isAnonymous" id="anonymous">
                        <label class="form-check-label" for="anonymous">Donate anonymously</label>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-secondary" wire:click="prevStep">Back</button>
                        <button class="btn btn-primary" wire:click="nextStep">Next</button>
                    </div>
                @elseif($step === 3)
                    <!-- Step 3: Payment Gateway Selection -->
                    <h4>Select Payment Gateway</h4>
                    <div class="gateway-list mb-3">
                        @foreach($gateways as $gateway)
                            <button type="button" class="btn btn-outline-primary w-100 mb-2 {{ $selectedGateway == $gateway->id ? 'active' : '' }}" wire:click="selectGateway({{ $gateway->id }})">
                                {{ $gateway->name }}
                            </button>
                        @endforeach
                        @error('selectedGateway') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <button class="btn btn-secondary w-100" wire:click="prevStep">Back</button>
                @elseif($step === 4)
                    <!-- Step 4: Confirmation -->
                    <h4>Confirm Donation</h4>
                    <ul class="list-group mb-3">
                        <li class="list-group-item">Amount: <strong>${{ $donationAmount }}</strong></li>
                        <li class="list-group-item">Name: <strong>{{ $isAnonymous ? 'Anonymous' : $donorName }}</strong></li>
                        <li class="list-group-item">Email: <strong>{{ $isAnonymous ? 'Hidden' : $donorEmail }}</strong></li>
                        <li class="list-group-item">Gateway: <strong>{{ optional($gateways->firstWhere('id', $selectedGateway))->name }}</strong></li>
                        @if($comment)
                        <li class="list-group-item">Comment: <em>{{ $comment }}</em></li>
                        @endif
                    </ul>
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-secondary" wire:click="prevStep">Back</button>
                        <button class="btn btn-success" wire:click="submitDonation">Donate</button>
                    </div>
                @endif
                <button class="btn btn-link mt-3 w-100" wire:click="hideWizard">Cancel</button>
            </div>
        </div>
    @endif
    <style>
    .donation-wizard-modal {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .donation-wizard-content {
        background: #fff;
        border-radius: 12px;
        padding: 2rem;
        min-width: 350px;
        max-width: 95vw;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    }
    .wizard-steps {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        justify-content: center;
    }
    .wizard-steps .step {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        background: #eee;
        color: #888;
        font-weight: 500;
    }
    .wizard-steps .step.active {
        background: #f74f22;
        color: #fff;
    }
    </style>
</div>

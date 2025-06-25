<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Stripe Payment</h2>
        <p class="text-gray-600 mt-2">Complete your payment securely with Stripe</p>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6">
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Order ID:</span>
                <span class="font-semibold">#{{ $orderId }}</span>
            </div>
            <div class="flex justify-between items-center mt-2">
                <span class="text-gray-600">Amount:</span>
                <span class="font-bold text-xl text-green-600">${{ number_format($amount, 2) }}</span>
            </div>
        </div>
    </div>

    @if ($paymentSent === 'false')
        <form wire:submit.prevent="createPayment">
            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <input
                        type="email"
                        id="email"
                        wire:model="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter your email address"
                        required
                    />
                    @error('email')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="cardholderName" class="block text-sm font-medium text-gray-700 mb-2">
                        Cardholder Name
                    </label>
                    <input
                        type="text"
                        id="cardholderName"
                        wire:model="cardholderName"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Full name on card"
                        required
                    />
                    @error('cardholderName')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="cardNumber" class="block text-sm font-medium text-gray-700 mb-2">
                        Card Number
                    </label>
                    <input
                        type="text"
                        id="cardNumber"
                        wire:model="cardNumber"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="1234 5678 9012 3456"
                        maxlength="19"
                        required
                    />
                    @error('cardNumber')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="expiryMonth" class="block text-sm font-medium text-gray-700 mb-2">
                            Month
                        </label>
                        <select
                            id="expiryMonth"
                            wire:model="expiryMonth"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required
                        >
                            <option value="">MM</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                        @error('expiryMonth')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="expiryYear" class="block text-sm font-medium text-gray-700 mb-2">
                            Year
                        </label>
                        <select
                            id="expiryYear"
                            wire:model="expiryYear"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required
                        >
                            <option value="">YYYY</option>
                            @for($i = date('Y'); $i <= date('Y') + 10; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                        @error('expiryYear')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="cvc" class="block text-sm font-medium text-gray-700 mb-2">
                            CVC
                        </label>
                        <input
                            type="text"
                            id="cvc"
                            wire:model="cvc"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="123"
                            maxlength="4"
                            required
                        />
                        @error('cvc')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <button
                type="submit"
                class="w-full mt-6 bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 ease-in-out transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
                wire:loading.attr="disabled"
                wire:target="createPayment"
            >
                <div wire:loading.remove wire:target="createPayment" class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 3a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm0 3a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1z"/>
                    </svg>
                    Pay with Stripe
                </div>
                <div wire:loading wire:target="createPayment" class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing Payment...
                </div>
            </button>
        </form>
    @endif

    @if ($paymentSent === 'true')
        <div class="text-center">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <div class="flex items-center justify-center mb-2">
                    <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-blue-800 font-medium">Authentication Required!</p>
                <p class="text-blue-600 text-sm mt-1">Your payment requires additional verification. Please complete the authentication process.</p>
            </div>

            <div class="text-center">
                <p class="text-gray-600 text-sm mb-3">Completed authentication?</p>
                <button
                    wire:click="checkPayment"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                    wire:target="checkPayment"
                >
                    <div wire:loading.remove wire:target="checkPayment" class="flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                        </svg>
                        Check Payment Status
                    </div>
                    <div wire:loading wire:target="checkPayment" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Checking...
                    </div>
                </button>
            </div>
        </div>
    @endif

    <div class="mt-6 pt-4 border-t border-gray-200">
        <div class="flex items-center justify-center text-sm text-gray-500">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            Secured by Stripe
        </div>
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

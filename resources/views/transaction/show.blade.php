@extends('app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Transaction Details</h1>
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

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-700 mb-2">Transaction ID</h3>
                        <p class="text-gray-900">{{ $transaction->transaction_id ?? $transaction->id }}</p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-700 mb-2">Status</h3>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @if($transaction->status === 'completed' || $transaction->isPaid === 'true')
                                bg-green-100 text-green-800
                            @elseif($transaction->status === 'processing')
                                bg-yellow-100 text-yellow-800
                            @elseif($transaction->status === 'failed')
                                bg-red-100 text-red-800
                            @else
                                bg-gray-100 text-gray-800
                            @endif
                        ">
                            @if($transaction->isPaid === 'true')
                                Paid
                            @else
                                {{ ucfirst($transaction->status) }}
                            @endif
                        </span>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-700 mb-2">Amount</h3>
                        <p class="text-2xl font-bold text-green-600">
                            @if($transaction->currency)
                                {{ $transaction->currency }} {{ number_format($transaction->amount ?? $transaction->total, 2) }}
                            @else
                                ${{ number_format($transaction->amount ?? $transaction->total, 2) }}
                            @endif
                        </p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-700 mb-2">Payment Method</h3>
                        <p class="text-gray-900">{{ ucfirst($transaction->type) }}</p>
                    </div>

                    @if($transaction->gateway_transaction_id)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-700 mb-2">Gateway Transaction ID</h3>
                        <p class="text-gray-900 break-all">{{ $transaction->gateway_transaction_id }}</p>
                    </div>
                    @endif

                    @if($transaction->processed_at)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-700 mb-2">Processed At</h3>
                        <p class="text-gray-900">{{ $transaction->processed_at->format('M d, Y H:i:s') }}</p>
                    </div>
                    @endif
                </div>

                @if($transaction->description)
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-gray-700 mb-2">Description</h3>
                    <p class="text-gray-900">{{ $transaction->description }}</p>
                </div>
                @endif

                <div class="text-center mt-8">
                    <a href="{{ route('dashboard') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

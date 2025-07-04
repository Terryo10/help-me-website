@extends('app')

@section('content')
<livewire:header />
<div class="sign-up section" style="background: #f8fafc; min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="sign-up__form wow fadeInUp mt-5 mb-5" data-wow-duration="0.8s">
                    <h3 class="sign-up__title text-center mb-2">Transaction Details</h3>
                    <p class="sign-up__sub-title text-center mb_40">Below are the details of your transaction</p>
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
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="input-single mb-3">
                                    <label class="label">Transaction ID</label>
                                    <input type="text" class="form-control" value="{{ $transaction->transaction_id ?? $transaction->id }}" readonly />
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="input-single mb-3">
                                    <label class="label">Status</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($transaction->status ?? 'failed') }}" readonly />
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="input-single mb-3">
                                    <label class="label">Amount</label>
                                    <input type="text" class="form-control" value="@if($transaction->currency){{ $transaction->currency }} {{ number_format($transaction->amount ?? $transaction->total, 2) }}@else${{ number_format($transaction->amount ?? $transaction->total, 2) }}@endif" readonly />
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="input-single mb-3">
                                    <label class="label">Payment Method</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($transaction->type) }}" readonly />
                                </div>
                            </div>
                            @if($transaction->gateway_transaction_id)
                            <div class="col-12">
                                <div class="input-single mb-3">
                                    <label class="label">Gateway Transaction ID</label>
                                    <input type="text" class="form-control" value="{{ $transaction->gateway_transaction_id }}" readonly />
                                </div>
                            </div>
                            @endif
                            @if($transaction->processed_at)
                            <div class="col-12 col-md-6">
                                <div class="input-single mb-3">
                                    <label class="label">Processed At</label>
                                    <input type="text" class="form-control" value="{{ $transaction->processed_at->format('M d, Y H:i:s') }}" readonly />
                                </div>
                            </div>
                            @endif
                        </div>
                        @if($transaction->description)
                        <div class="input-single mb-3">
                            <label class="label">Description</label>
                            <textarea class="form-control" rows="2" readonly>{{ $transaction->description }}</textarea>
                        </div>
                        @endif
                        <div class="text-center mt-4">
                            <a href="{{ route('dashboard') }}" class="btn_theme btn_theme_active w-100">
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<livewire:footer />
@endsection

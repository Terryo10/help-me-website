<?php

namespace App\Livewire;

use App\Models\Campaign;
use App\Models\Donation;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\EmailNotificationService;

class Dashboard extends Component
{
    public $withdrawalAmount;
    public $phoneNumber;
    public $bankName;
    public $accountNumber;
    public $branchCode;

    public function getBalance()
    {
        $user = Auth::user();
        $totalRaised = Campaign::where('user_id', $user->id)->get()->sum(function ($campaign) {
            return $campaign->raised_amount_count();
        });

        $withdrawals = \App\Models\Withdrawal::where('user_id', $user->id)
            ->where('status', 'approved')
            ->sum('amount');
        return ($totalRaised - $withdrawals) > 0 ? ($totalRaised - $withdrawals) : 0;
    }

    public function requestWithdrawal()
    {
        $this->validate([
            'withdrawalAmount' => 'required|numeric|min:0.01|max:' . $this->getBalance(),
            'phoneNumber' => 'required|string',
            'bankName' => 'required|string',
            'accountNumber' => 'required|string',
            'branchCode' => 'required|string',
        ]);

        // Create withdrawal record
        $withdrawal = new \App\Models\Withdrawal();
        $withdrawal->user_id = Auth::id();
        $withdrawal->amount = $this->withdrawalAmount;
        $withdrawal->phone_number = $this->phoneNumber;
        $withdrawal->bank_name = $this->bankName;
        $withdrawal->account_number = $this->accountNumber;
        $withdrawal->branch_code = $this->branchCode;
        $withdrawal->status = 'pending';
        $withdrawal->save();

        $user_id = Auth::id();
        // Optionally, send notification to admin
        $notificaionService = new EmailNotificationService();
        $notificaionService->sendEmail("Someone is requesting for withdrawals", "User requesting for withdrawal ID {$user_id} with the sum of $ {$this->withdrawalAmount}", env('ADMIN_EMAIL'));



        // Reset form fields
        $this->reset(['withdrawalAmount', 'phoneNumber', 'bankName', 'accountNumber', 'branchCode']);

        session()->flash('message', 'Withdrawal request submitted successfully!');
        //notify user via email with days for waiting period
        return redirect()->to('/dashboard')->with('message', 'Withdrawal request submitted successfully!');
    }

    public function render()
    {
        $user = Auth::user();

        // Get user stats
        $userCampaigns = Campaign::where('user_id', $user->id)->get();
        $totalRaised = $userCampaigns->sum(function ($campaign) {
            return $campaign->raised_amount_count();
        });
        $activeCampaigns = $userCampaigns->where('status', 'active')->count();
        $totalDonations = Donation::where('user_id', $user->id)->where('status', 'completed')->count();

        // Recent campaigns
        $recentCampaigns = Campaign::where('user_id', $user->id)
            ->latest()
            ->take(3)
            ->get();

        // Recent donations made by user
        $recentDonations = Donation::where('user_id', $user->id)
            ->with('campaign')
            ->latest()
            ->take(5)
            ->get();

        // Get user withdrawals
        $withdrawals = \App\Models\Withdrawal::where('user_id', $user->id)->latest()->get();

        return view('livewire.dashboard', [
            'totalRaised' => $totalRaised,
            'balance' => $this->getBalance(),
            'activeCampaigns' => $activeCampaigns,
            'totalDonations' => $totalDonations,
            'recentCampaigns' => $recentCampaigns,
            'recentDonations' => $recentDonations,
            'withdrawals' => $withdrawals,
        ])->extends('app');
    }
}

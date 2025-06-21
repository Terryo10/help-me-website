<?php

namespace App\Livewire;

use App\Models\Campaign;
use App\Models\Donation;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        
        // Get user stats
        $userCampaigns = Campaign::where('user_id', $user->id)->get();
        $totalRaised = $userCampaigns->sum('raised_amount');
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

        return view('livewire.dashboard', [
            'totalRaised' => $totalRaised,
            'activeCampaigns' => $activeCampaigns,
            'totalDonations' => $totalDonations,
            'recentCampaigns' => $recentCampaigns,
            'recentDonations' => $recentDonations
        ])->extends('app');
    }
}
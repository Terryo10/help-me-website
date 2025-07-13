<div>
    <livewire:header />

    <section class="dashboard-section section">

        <div class="container">
            <!-- Header -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="dashboard-header">
                        <h2 class="wow fadeInUp">Welcome back, {{ Auth::user()->name }}!</h2>
                        <p class="wow fadeInDown">Here's what's happening with your campaigns and donations</p>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-5">
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card wow fadeInUp" data-wow-delay="0.1s">
                        <div class="stats-icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="stats-content">
                            <h3>${{ number_format($totalRaised, 2) }}</h3>
                            <p>Total Raised</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card wow fadeInUp" data-wow-delay="0.1s">
                        <div class="stats-icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="stats-content">
                            <h3>${{ number_format($balance, 2) }}</h3>
                            <p>Your Balance Left</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stats-card wow fadeInUp" data-wow-delay="0.2s">
                        <div class="stats-icon">
                            <i class="bi bi-megaphone"></i>
                        </div>
                        <div class="stats-content">
                            <h3>{{ $activeCampaigns }}</h3>
                            <p>Active Campaigns</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card wow fadeInUp" data-wow-delay="0.3s">
                        <div class="stats-icon">
                            <i class="bi bi-heart"></i>
                        </div>
                        <div class="stats-content">
                            <h3>{{ $totalDonations }}</h3>
                            <p>Donations Made</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card wow fadeInUp" data-wow-delay="0.4s">
                        <div class="stats-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stats-content">
                            <h3>{{ $recentCampaigns->sum('donation_count') }}</h3>
                            <p>Total Supporters</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="quick-actions wow fadeInUp">
                        <h4 class="mb-4">Quick Actions</h4>
                        <div class="btn-group gap-3">
                            <a href="{{ route('campaigns.create') }}" class="btn_theme btn_theme_active">
                                <i class="bi bi-plus-circle"></i> Create Campaign
                            </a>
                            <a href="{{ route('campaigns.index') }}" class="btn_theme">
                                <i class="bi bi-list-ul"></i> View All Campaigns
                            </a>
                            <a href="#" class="btn_theme">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                            <button type="button" class="btn_theme" data-bs-toggle="modal"
                                data-bs-target="#withdrawalModal">
                                <i class="bi bi-cash-coin"></i> Withdraw
                            </button>
                            <button type="button" class="btn_theme" data-bs-toggle="modal"
                                data-bs-target="#withdrawalsModal">
                                <i class="bi bi-history"></i> Withdrawals
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Withdrawal Modal -->
            <div class="modal fade" id="withdrawalModal" tabindex="-1" aria-labelledby="withdrawalModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="withdrawalModalLabel">Withdraw Funds</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form wire:submit.prevent="requestWithdrawal">
                                <div class="mb-3">
                                    <label for="withdrawalAmount" class="form-label">Amount</label>
                                    <input type="number" class="form-control" id="withdrawalAmount"
                                        wire:model="withdrawalAmount" max="{{ $this->getBalance() }}" required>
                                    @error('withdrawalAmount') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="phoneNumber" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="phoneNumber" wire:model="phoneNumber"
                                        required>
                                    @error('phoneNumber') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="bankName" class="form-label">Bank Name</label>
                                    <input type="text" class="form-control" id="bankName" wire:model="bankName"
                                        required>
                                    @error('bankName') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="accountNumber" class="form-label">Account Number</label>
                                    <input type="text" class="form-control" id="accountNumber"
                                        wire:model="accountNumber" required>
                                    @error('accountNumber') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="branchCode" class="form-label">Branch Code</label>
                                    <input type="text" class="form-control" id="branchCode" wire:model="branchCode"
                                        required>
                                    @error('branchCode') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <button type="submit" class="btn_theme btn_theme_active">Request Withdrawal</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Withdrawals Modal -->
            <div class="modal fade" id="withdrawalsModal" tabindex="-1" aria-labelledby="withdrawalsModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="withdrawalsModalLabel">Your Withdrawals</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @if ($withdrawals->isEmpty())
                            <p>No withdrawals yet.</p>
                            @else
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Requested At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($withdrawals as $withdrawal)
                                    <tr>
                                        <td>${{ number_format($withdrawal->amount, 2) }}</td>
                                        <td>{{ ucfirst($withdrawal->status) }}</td>
                                        <td>{{ $withdrawal->created_at->diffForHumans() }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="dashboard-card wow fadeInLeft">
                        <div class="card-header">
                            <h4>Your Recent Campaigns</h4>
                            <a href="{{ route('campaigns.index') }}" class="view-all">View All</a>
                        </div>
                        <div class="card-body">
                            @forelse($recentCampaigns as $campaign)
                            <div class="campaign-item">
                                <div class="campaign-image">
                                    <img src="{{ $campaign->featured_image ?? asset('template/assets/images/placeholder.png') }}"
                                        alt="{{ $campaign->title }}">
                                </div>
                                <div class="campaign-content">
                                    <h5><a href="{{ route('campaigns.show', $campaign->slug) }}">{{ $campaign->title
                                            }}</a></h5>
                                    <div class="campaign-meta">
                                        <span
                                            class="badge badge-{{ $campaign->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($campaign->status) }}
                                        </span>
                                        <span class="progress-text">
                                            ${{ number_format($campaign->raised_amount_count()) }} / ${{
                                            number_format($campaign->goal_amount) }}
                                        </span>
                                    </div>
                                    <div class="progress mb-2">
                                        <div class="progress-bar"
                                            style="width: {{ min($campaign->goal_percentage(), 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="empty-state">
                                <i class="bi bi-megaphone"></i>
                                <h5>No campaigns yet</h5>
                                <p>Start your first campaign to help your community</p>
                                <a href="{{ route('campaigns.create') }}" class="btn_theme btn_theme_active">Create
                                    Campaign</a>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="dashboard-card wow fadeInRight">
                        <div class="card-header">
                            <h4>Recent Donations</h4>
                        </div>
                        <div class="card-body">
                            @forelse($recentDonations as $donation)
                            <div class="donation-item">
                                <div class="donation-content">
                                    <h6>{{ $donation->campaign->title }}</h6>
                                    <div class="donation-meta">
                                        <span class="amount">${{ number_format($donation->amount, 2) }}</span>
                                        <span class="date">{{ $donation->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="empty-state-small">
                                <i class="bi bi-heart"></i>
                                <p>No donations yet</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>


</div>
</section>
<livewire:footer />


<style>
    .dashboard-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .stats-card {
        /* background: rgb(32, 27, 27); */
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-5px);
    }

    .stats-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #f74f22, #ff6b3d);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .stats-content h3 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
        color: #333;
    }

    .stats-content p {
        margin: 0;
        color: #666;
        font-size: 0.9rem;
    }

    .dashboard-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }

    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h4 {
        margin: 0;
        color: #333;
    }

    .view-all {
        color: #f74f22;
        text-decoration: none;
        font-weight: 500;
    }

    .campaign-item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .campaign-item:last-child {
        border-bottom: none;
    }

    .campaign-image {
        width: 80px;
        height: 60px;
        border-radius: 8px;
        overflow: hidden;
    }

    .campaign-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .campaign-content {
        flex: 1;
    }

    .campaign-content h5 {
        margin: 0 0 0.5rem 0;
        font-size: 1rem;
    }

    .campaign-content h5 a {
        color: #333;
        text-decoration: none;
    }

    .campaign-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .badge {
        padding: 0.25rem 0.5rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-success {
        background: #d4edda;
        color: #155724;
    }

    .badge-secondary {
        background: #e2e3e5;
        color: #383d41;
    }

    .progress-text {
        font-size: 0.8rem;
        color: #666;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #666;
    }

    .empty-state i {
        font-size: 3rem;
        color: #ddd;
        margin-bottom: 1rem;
    }

    .donation-item {
        padding: 1rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .donation-item:last-child {
        border-bottom: none;
    }

    .donation-content h6 {
        margin: 0 0 0.5rem 0;
        font-size: 0.9rem;
    }

    .donation-meta {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
    }

    .amount {
        color: #f74f22;
        font-weight: 600;
    }

    .date {
        color: #666;
    }

    .quick-actions {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }
</style>
</div>

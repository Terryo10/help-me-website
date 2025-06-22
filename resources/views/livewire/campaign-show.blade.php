@section('content')
<livewire:header />
<div class="section campaign-detail-section section-padding">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('campaigns.index') }}">Campaigns</a></li>
                        <li class="breadcrumb-item active">{{ $campaign->title }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row gy-5">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Campaign Header -->
                <div class="campaign-header wow fadeInUp">
                    <div class="campaign-badges mb-3">
                        @foreach($campaign->categories as $category)
                        <span class="category-badge" style="background-color: {{ $category->color }}">
                            {{ $category->name }}
                        </span>
                        @endforeach
                        @if($campaign->is_urgent)
                        <span class="urgent-badge">Urgent</span>
                        @endif
                    </div>

                    <h1 class="campaign-title">{{ $campaign->title }}</h1>

                    <div class="campaign-meta">
                        <div class="organizer">
                            <img src="{{ asset('template/assets/images/avatar-placeholder.png') }}" alt="{{ $campaign->user->name }}" class="organizer-avatar">
                            <div>
                                <span class="organizer-label">Organized by</span>
                                <strong class="organizer-name">{{ $campaign->user->name }}</strong>
                            </div>
                        </div>
                        @if($campaign->location)
                        <div class="location">
                            <i class="bi bi-geo-alt"></i>
                            {{ $campaign->location }}
                        </div>
                        @endif
                        <div class="created">
                            <i class="bi bi-calendar"></i>
                            Created {{ $campaign->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>

                <!-- Campaign Image -->
                <div class="campaign-image-container wow fadeInUp">
                    <img src="{{ $campaign->featured_image ? asset('storage/' . $campaign->featured_image) : asset('template/assets/images/placeholder.png') }}"
                         alt="{{ $campaign->title }}"
                         class="campaign-featured-image">
                </div>

                <!-- Campaign Story -->
                <div class="campaign-story wow fadeInUp">
                    <h3>Story</h3>
                    <div class="story-content">
                        {!! nl2br(e($campaign->story)) !!}
                    </div>
                </div>

                <!-- Recent Donations -->
                @if($recentDonations->count() > 0)
                <div class="recent-donations wow fadeInUp">
                    <h3>Recent Donations</h3>
                    <div class="donations-list">
                        @foreach($recentDonations as $donation)
                        <div class="donation-item">
                            <div class="donor-info">
                                <strong class="donor-name">{{ $donation->donor_display_name }}</strong>
                                <span class="donation-amount">${{ number_format($donation->amount, 2) }}</span>
                            </div>
                            @if($donation->comment)
                            <p class="donation-comment">"{{ $donation->comment }}"</p>
                            @endif
                            <span class="donation-time">{{ $donation->created_at->diffForHumans() }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="campaign-sidebar">
                    <!-- Donation Progress -->
                    <div class="donation-card wow fadeInRight">
                        <div class="progress-stats">
                            <div class="raised-amount">
                                <h2>${{ number_format($campaign->raised_amount) }}</h2>
                                <p>raised of ${{ number_format($campaign->goal_amount) }} goal</p>
                            </div>
                            <div class="progress-percentage">
                                {{ number_format($campaign->progress_percentage, 1) }}%
                            </div>
                        </div>

                        <div class="progress-bar-container">
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ min($campaign->progress_percentage, 100) }}%"></div>
                            </div>
                        </div>

                        <div class="campaign-stats">
                            <div class="stat">
                                <strong>{{ $campaign->donation_count }}</strong>
                                <span>donors</span>
                            </div>
                            <div class="stat">
                                <strong>{{ $campaign->share_count }}</strong>
                                <span>shares</span>
                            </div>
                            @if($campaign->end_date)
                            <div class="stat">
                                <strong>{{ $campaign->end_date->diffInDays(now()) }}</strong>
                                <span>days left</span>
                            </div>
                            @endif
                        </div>

                        <!-- Donation Button -->
                        @if($campaign->status === 'active')
                        <button wire:click="toggleDonationForm" class="btn_theme btn_theme_active w-100 mt-3">
                            <i class="bi bi-heart"></i> Donate Now
                        </button>
                        @else
                        <div class="campaign-status-notice">
                            <i class="bi bi-info-circle"></i>
                            This campaign is {{ $campaign->status }}
                        </div>
                        @endif

                        <!-- Share Buttons -->
                        <div class="share-buttons mt-3">
                            <span class="share-label">Share:</span>
                            <a href="#" class="share-btn facebook" title="Share on Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="share-btn twitter" title="Share on Twitter">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="#" class="share-btn whatsapp" title="Share on WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="#" class="share-btn copy" title="Copy Link">
                                <i class="bi bi-link"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Donation Form Modal -->
                    @if($showDonationForm)
                    <div class="donation-form-card mt-4 wow fadeInRight">
                        <form wire:submit.prevent="donate">
                            <h4>Make a Donation</h4>

                            <div class="amount-selection mb-3">
                                <div class="quick-amounts">
                                    <button type="button" wire:click="$set('donationAmount', 10)" class="amount-btn {{ $donationAmount == 10 ? 'active' : '' }}">$10</button>
                                    <button type="button" wire:click="$set('donationAmount', 25)" class="amount-btn {{ $donationAmount == 25 ? 'active' : '' }}">$25</button>
                                    <button type="button" wire:click="$set('donationAmount', 50)" class="amount-btn {{ $donationAmount == 50 ? 'active' : '' }}">$50</button>
                                    <button type="button" wire:click="$set('donationAmount', 100)" class="amount-btn {{ $donationAmount == 100 ? 'active' : '' }}">$100</button>
                                </div>
                                <div class="custom-amount mt-2">
                                    <input type="number"
                                           wire:model="donationAmount"
                                           class="form-control"
                                           placeholder="Custom amount"
                                           min="{{ $campaign->minimum_donation }}">
                                </div>
                                @error('donationAmount') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>

                            @unless(auth()->check())
                            <div class="donor-info mb-3">
                                <input type="text"
                                       wire:model="donorName"
                                       class="form-control mb-2"
                                       placeholder="Your name">
                                @error('donorName') <div class="text-danger">{{ $message }}</div> @enderror

                                <input type="email"
                                       wire:model="donorEmail"
                                       class="form-control"
                                       placeholder="Your email">
                                @error('donorEmail') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            @endunless

                            <div class="donation-comment mb-3">
                                <textarea wire:model="comment"
                                          class="form-control"
                                          placeholder="Leave a comment (optional)"
                                          rows="3"></textarea>
                                @error('comment') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input"
                                       type="checkbox"
                                       wire:model="isAnonymous"
                                       id="anonymous">
                                <label class="form-check-label" for="anonymous">
                                    Donate anonymously
                                </label>
                            </div>

                            <button type="submit" class="btn_theme btn_theme_active w-100" wire:loading.attr="disabled">
                                <span wire:loading.remove>Donate ${{ $donationAmount }}</span>
                                <span wire:loading>Processing...</span>
                            </button>

                            <button type="button" wire:click="toggleDonationForm" class="btn btn-secondary w-100 mt-2">
                                Cancel
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Related Campaigns -->
        @if($relatedCampaigns->count() > 0)
        <div class="row mt-5">
            <div class="col-12">
                <div class="related-campaigns wow fadeInUp">
                    <h3 class="text-center mb-4">Related Campaigns</h3>
                    <div class="row g-4">
                        @foreach($relatedCampaigns as $related)
                        <div class="col-md-4">
                            <div class="campaign-card-small">
                                <div class="campaign-image">
                                    <img src="{{ $related->featured_image ? asset('storage/' . $related->featured_image) : asset('template/assets/images/placeholder.png') }}"
                                         alt="{{ $related->title }}">
                                </div>
                                <div class="campaign-content">
                                    <h5><a href="{{ route('campaigns.show', $related->slug) }}">{{ Str::limit($related->title, 50) }}</a></h5>
                                    <div class="progress-info">
                                        <span>${{ number_format($related->raised_amount) }} raised</span>
                                        <span>{{ $related->progress_percentage }}%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ $related->progress_percentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
<livewire:footer />

<style>
.campaign-header {
    margin-bottom: 2rem;
}

.campaign-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.category-badge, .urgent-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    color: white;
}

.urgent-badge {
    background: #dc3545;
}

.campaign-title {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin: 1rem 0;
}

.campaign-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.organizer {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.organizer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.organizer-label {
    display: block;
    font-size: 0.8rem;
    color: #666;
}

.organizer-name {
    color: #333;
}

.location, .created {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    font-size: 0.9rem;
}

.campaign-image-container {
    margin: 2rem 0;
    border-radius: 15px;
    overflow: hidden;
}

.campaign-featured-image {
    width: 100%;
    height: 400px;
    object-fit: cover;
}

.campaign-story {
    margin: 3rem 0;
}

.campaign-story h3 {
    margin-bottom: 1.5rem;
    color: #333;
}

.story-content {
    line-height: 1.7;
    color: #555;
    font-size: 1rem;
}

.recent-donations {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 15px;
    margin-top: 3rem;
}

.donations-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.donation-item {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    border-left: 4px solid #f74f22;
}

.donor-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.donor-name {
    color: #333;
}

.donation-amount {
    color: #f74f22;
    font-weight: 600;
}

.donation-comment {
    font-style: italic;
    color: #666;
    margin: 0.5rem 0;
}

.donation-time {
    font-size: 0.8rem;
    color: #999;
}

.campaign-sidebar {
    position: sticky;
    top: 2rem;
}

.donation-card, .donation-form-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.progress-stats {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.raised-amount h2 {
    font-size: 2rem;
    font-weight: 700;
    color: #f74f22;
    margin: 0;
}

.raised-amount p {
    color: #666;
    margin: 0;
}

.progress-percentage {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
}

.progress-bar-container {
    margin-bottom: 1.5rem;
}

.progress {
    height: 12px;
    background: #f0f0f0;
    border-radius: 6px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(135deg, #f74f22, #ff6b3d);
    transition: width 0.3s ease;
}

.campaign-stats {
    display: flex;
    justify-content: space-around;
    padding: 1rem 0;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
    margin: 1rem 0;
}

.stat {
    text-align: center;
}

.stat strong {
    display: block;
    font-size: 1.2rem;
    color: #333;
}

.stat span {
    font-size: 0.8rem;
    color: #666;
}

.campaign-status-notice {
    background: #fff3cd;
    color: #856404;
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
    border: 1px solid #ffeaa7;
}

.share-buttons {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.share-label {
    font-weight: 500;
    color: #666;
}

.share-btn {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: white;
    font-size: 0.9rem;
}

.share-btn.facebook { background: #4267B2; }
.share-btn.twitter { background: #1DA1F2; }
.share-btn.whatsapp { background: #25D366; }
.share-btn.copy { background: #666; }

.quick-amounts {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
}

.amount-btn {
    padding: 0.75rem;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.amount-btn:hover, .amount-btn.active {
    border-color: #f74f22;
    background: #f74f22;
    color: white;
}

.campaign-card-small {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.campaign-card-small:hover {
    transform: translateY(-3px);
}

.campaign-card-small .campaign-image {
    height: 150px;
    overflow: hidden;
}

.campaign-card-small .campaign-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.campaign-card-small .campaign-content {
    padding: 1rem;
}

.campaign-card-small h5 {
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
}

.campaign-card-small h5 a {
    color: #333;
    text-decoration: none;
}

.campaign-card-small .progress-info {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    margin-bottom: 0.5rem;
}

.breadcrumb {
    background: none;
    padding: 0;
    margin-bottom: 1rem;
}

.breadcrumb-item a {
    color: #666;
    text-decoration: none;
}

.breadcrumb-item.active {
    color: #333;
}

@media (max-width: 768px) {
    .campaign-title {
        font-size: 1.5rem;
    }

    .campaign-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .campaign-featured-image {
        height: 250px;
    }

    .progress-stats {
        flex-direction: column;
        gap: 1rem;
    }

    .campaign-stats {
        flex-wrap: wrap;
        gap: 1rem;
    }

    .share-buttons {
        justify-content: center;
    }

    .quick-amounts {
        grid-template-columns: repeat(4, 1fr);
    }
}</style>
@endsection

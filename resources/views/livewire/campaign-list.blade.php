<div>

    <livewire:header />
    <div class="section campaigns-section section-padding">
        <div class="container">
            <!-- Header -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="section__header text-center">
                        <h2 class="wow fadeInUp">Browse Campaigns</h2>
                        <p class="wow fadeInDown">Discover meaningful causes and help make a difference in Zimbabwe</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="campaign-filters wow fadeInUp">
                        <div class="row g-3 align-items-center">
                            <div class="col-lg-4">
                                <div class="search-box">
                                    <input type="text"
                                           wire:model.live="search"
                                           class="form-control"
                                           placeholder="Search campaigns...">
                                    <i class="bi bi-search"></i>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <select wire:model.live="category" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <select wire:model.live="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <select wire:model.live="sortBy" class="form-select">
                                    <option value="latest">Latest</option>
                                    <option value="popular">Most Popular</option>
                                    <option value="ending">Ending Soon</option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <a href="{{ route('campaigns.create') }}" class="btn_theme btn_theme_active w-100">
                                    <i class="bi bi-plus-circle"></i> Create Campaign
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Campaign Grid -->
            <div class="row g-4" wire:loading.class="opacity-50">
                @forelse($campaigns as $campaign)
                <div class="col-lg-4 col-md-6">
                    <div class="campaign-card wow fadeInUp" data-wow-delay="{{ $loop->index * 0.1 }}s">
                        <div class="campaign-image">
                            <img src="{{ $campaign->featured_image ? asset('storage/' . $campaign->featured_image) : asset('template/assets/images/placeholder.png') }}"
                                 alt="{{ $campaign->title }}">
                            <div class="campaign-badges">
                                @if($campaign->is_urgent)
                                <span class="badge urgent">Urgent</span>
                                @endif
                                @if($campaign->is_featured)
                                <span class="badge featured">Featured</span>
                                @endif
                            </div>
                            <div class="campaign-category">
                                @if($campaign->categories->first())
                                <span class="category-tag" style="background-color: {{ $campaign->categories->first()->color }}">
                                    {{ $campaign->categories->first()->name }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="campaign-content">
                            <div class="campaign-meta">
                                <span class="author">
                                    <i class="bi bi-person"></i>
                                    {{ $campaign->user->name }}
                                </span>
                                @if($campaign->location)
                                <span class="location">
                                    <i class="bi bi-geo-alt"></i>
                                    {{ $campaign->location }}
                                </span>
                                @endif
                            </div>

                            <h3 class="campaign-title">
                                <a href="{{ route('campaigns.show', $campaign->slug) }}">{{ $campaign->title }}</a>
                            </h3>

                            <p class="campaign-description">{{ Str::limit($campaign->description, 120) }}</p>

                            <div class="campaign-progress">
                                <div class="progress-info">
                                    <div class="raised">
                                        <strong>${{ number_format($campaign->raised_amount_count()) }}</strong>
                                        <span>raised of ${{ number_format($campaign->goal_amount) }}</span>
                                    </div>
                                    <div class="percentage">
                                        {{ number_format($campaign->goal_percentage(), 1) }}%
                                    </div>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ min($campaign->goal_percentage(), 100) }}%"></div>
                                </div>
                                <div class="progress-stats">
                                    <span class="donors">{{ $campaign->donations()->count() }} donors</span>
                                    @if($campaign->end_date)
                                    <span class="days-left">
                                        {{ number_format($campaign->end_date->diffInDays(now()), 0) }} days left
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="campaign-actions">
                                <a href="{{ route('campaigns.show', $campaign->slug) }}" class="btn_theme btn_theme_active">
                                    Donate Now <i class="bi bi-arrow-right"></i>
                                </a>
                                <button class="btn_theme share-btn" title="Share">
                                    <i class="bi bi-share"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="empty-campaigns text-center">
                        <i class="bi bi-search"></i>
                        <h3>No campaigns found</h3>
                        <p>Try adjusting your search or filters</p>
                        <a href="{{ route('campaigns.create') }}" class="btn_theme btn_theme_active">Create First Campaign</a>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($campaigns->hasPages())
            <div class="row mt-5">
                <div class="col-12">
                    <div class="pagination-wrapper text-center">
                        {{ $campaigns->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    <livewire:footer />

    <style>
    .campaign-filters {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        padding-left: 2.5rem;
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
    }

    .campaign-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .campaign-card:hover {
        transform: translateY(-5px);
    }

    .campaign-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .campaign-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .campaign-badges {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }

    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }

    .badge.urgent {
        background: #dc3545;
        color: white;
    }

    .badge.featured {
        background: #ffc107;
        color: #000;
    }

    .campaign-category {
        position: absolute;
        bottom: 1rem;
        left: 1rem;
    }

    .category-tag {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        color: white;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .campaign-content {
        padding: 1.5rem;
    }

    .campaign-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        font-size: 0.85rem;
        color: #666;
    }

    .campaign-title {
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }

    .campaign-title a {
        color: #333;
        text-decoration: none;
    }

    .campaign-title a:hover {
        color: #f74f22;
    }

    .campaign-description {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .campaign-progress {
        margin-bottom: 1.5rem;
    }

    .progress-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .raised strong {
        color: #f74f22;
        font-size: 1.1rem;
    }

    .raised span {
        color: #666;
        font-size: 0.85rem;
    }

    .percentage {
        font-weight: 600;
        color: #333;
    }

    .progress {
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(135deg, #f74f22, #ff6b3d);
        transition: width 0.3s ease;
    }

    .progress-stats {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        color: #666;
    }

    .campaign-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .campaign-actions .btn_theme {
        flex: 1;
    }

    .share-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        color: #666;
    }

    .empty-campaigns {
        padding: 4rem 2rem;
        color: #666;
    }

    .empty-campaigns i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .campaign-filters .row > div {
            margin-bottom: 1rem;
        }

        .campaign-meta {
            flex-direction: column;
            gap: 0.5rem;
        }

        .progress-info,
        .progress-stats {
            flex-direction: column;
            gap: 0.25rem;
        }
    }
    </style>
    </div>

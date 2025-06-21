<div>
    @section('content')
    <livewire:header />
    <div class="create-campaign-section section-padding">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="campaign-form-wrapper">
                        <div class="section-header text-center mb-5">
                            <h2 class="wow fadeInUp">Create Your Campaign</h2>
                            <p class="wow fadeInDown">Share your story and start making a difference in Zimbabwe</p>
                        </div>
    
                        @if (session()->has('message'))
                            <div class="alert alert-success wow fadeInUp">
                                {{ session('message') }}
                            </div>
                        @endif
    
                        <form wire:submit.prevent="createCampaign" class="campaign-form">
                            <!-- Basic Information -->
                            <div class="form-section wow fadeInUp">
                                <h4 class="form-section-title">Basic Information</h4>
                                
                                <div class="input-single">
                                    <label class="label" for="title">Campaign Title *</label>
                                    <input type="text" 
                                           wire:model="title" 
                                           id="title" 
                                           class="form-control @error('title') is-invalid @enderror" 
                                           placeholder="Give your campaign a compelling title">
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
    
                                <div class="input-single">
                                    <label class="label" for="description">Short Description *</label>
                                    <textarea wire:model="description" 
                                              id="description" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" 
                                              placeholder="Brief description of your campaign (max 500 characters)"></textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
    
                                <div class="input-single">
                                    <label class="label" for="story">Your Story *</label>
                                    <textarea wire:model="story" 
                                              id="story" 
                                              class="form-control @error('story') is-invalid @enderror" 
                                              rows="8" 
                                              placeholder="Tell the full story of your campaign. Why is this important? How will donations help?"></textarea>
                                    @error('story') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
    
                            <!-- Campaign Details -->
                            <div class="form-section wow fadeInUp" data-wow-delay="0.2s">
                                <h4 class="form-section-title">Campaign Details</h4>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-single">
                                            <label class="label" for="goal_amount">Fundraising Goal *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" 
                                                       wire:model="goal_amount" 
                                                       id="goal_amount" 
                                                       class="form-control @error('goal_amount') is-invalid @enderror" 
                                                       placeholder="0.00" 
                                                       step="0.01">
                                            </div>
                                            @error('goal_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-single">
                                            <label class="label" for="minimum_donation">Minimum Donation</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" 
                                                       wire:model="minimum_donation" 
                                                       id="minimum_donation" 
                                                       class="form-control @error('minimum_donation') is-invalid @enderror" 
                                                       value="1" 
                                                       min="1">
                                            </div>
                                            @error('minimum_donation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
    
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-single">
                                            <label class="label" for="end_date">End Date (Optional)</label>
                                            <input type="date" 
                                                   wire:model="end_date" 
                                                   id="end_date" 
                                                   class="form-control @error('end_date') is-invalid @enderror">
                                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-single">
                                            <label class="label" for="location">Location</label>
                                            <input type="text" 
                                                   wire:model="location" 
                                                   id="location" 
                                                   class="form-control @error('location') is-invalid @enderror" 
                                                   placeholder="City, Province">
                                            @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
    
                            <!-- Categories -->
                            <div class="form-section wow fadeInUp" data-wow-delay="0.3s">
                                <h4 class="form-section-title">Categories *</h4>
                                <div class="category-grid">
                                    @foreach($categories as $category)
                                    <div class="category-item">
                                        <input type="checkbox" 
                                               wire:model="category_ids" 
                                               value="{{ $category->id }}" 
                                               id="category_{{ $category->id }}" 
                                               class="category-checkbox">
                                        <label for="category_{{ $category->id }}" class="category-label">
                                            <div class="category-icon" style="background-color: {{ $category->color }}">
                                                <i class="bi bi-{{ $category->icon ?? 'tag' }}"></i>
                                            </div>
                                            <span>{{ $category->name }}</span>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                @error('category_ids') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
    
                            <!-- Image Upload -->
                            <div class="form-section wow fadeInUp" data-wow-delay="0.4s">
                                <h4 class="form-section-title">Featured Image</h4>
                                <div class="image-upload-area">
                                    <input type="file" 
                                           wire:model="featured_image" 
                                           id="featured_image" 
                                           class="image-input"
                                           accept="image/*">
                                    <label for="featured_image" class="image-upload-label">
                                        <div class="upload-content">
                                            <i class="bi bi-cloud-upload"></i>
                                            <h5>Click to upload image</h5>
                                            <p>PNG, JPG up to 2MB</p>
                                        </div>
                                    </label>
                                    @if ($featured_image)
                                        <div class="image-preview">
                                            <img src="{{ $featured_image->temporaryUrl() }}" alt="Preview">
                                        </div>
                                    @endif
                                </div>
                                @error('featured_image') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
    
                            <!-- Settings -->
                            <div class="form-section wow fadeInUp" data-wow-delay="0.5s">
                                <h4 class="form-section-title">Privacy Settings</h4>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           wire:model="allow_anonymous_donations" 
                                           id="allow_anonymous">
                                    <label class="form-check-label" for="allow_anonymous">
                                        Allow anonymous donations
                                    </label>
                                </div>
                            </div>
    
                            <!-- Submit -->
                            <div class="form-submit text-center wow fadeInUp" data-wow-delay="0.6s">
                                <button type="submit" 
                                        class="btn_theme btn_theme_active"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove>Create Campaign</span>
                                    <span wire:loading>Creating...</span>
                                    <i class="bi bi-arrow-up-right"></i>
                                    <span></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <livewire:footer />
    
    <style>
    .campaign-form-wrapper {
        background: white;
        padding: 3rem;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .form-section {
        margin-bottom: 3rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #eee;
    }
    
    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .form-section-title {
        color: #333;
        margin-bottom: 1.5rem;
        font-size: 1.3rem;
        font-weight: 600;
    }
    
    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }
    
    .category-item {
        position: relative;
    }
    
    .category-checkbox {
        display: none;
    }
    
    .category-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1rem;
        border: 2px solid #eee;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }
    
    .category-checkbox:checked + .category-label {
        border-color: #f74f22;
        background: rgba(247, 79, 34, 0.1);
    }
    
    .category-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-bottom: 0.5rem;
        font-size: 1.2rem;
    }
    
    .image-upload-area {
        position: relative;
    }
    
    .image-input {
        display: none;
    }
    
    .image-upload-label {
        display: block;
        border: 2px dashed #ddd;
        border-radius: 10px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .image-upload-label:hover {
        border-color: #f74f22;
        background: rgba(247, 79, 34, 0.05);
    }
    
    .upload-content i {
        font-size: 2rem;
        color: #ddd;
        margin-bottom: 0.5rem;
    }
    
    .image-preview {
        margin-top: 1rem;
        text-align: center;
    }
    
    .image-preview img {
        max-width: 200px;
        border-radius: 10px;
    }
    
    .form-submit {
        margin-top: 2rem;
    }
    
    @media (max-width: 768px) {
        .campaign-form-wrapper {
            padding: 2rem 1rem;
        }
        
        .category-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    </style>
    @endsection
    </div>
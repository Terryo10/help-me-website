<div>
    @section('content')
    <livewire:header />

    <section class="create-campaign-section section section-padding">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="campaign-wizard-wrapper">
                        <!-- Header -->
                        <div class="wizard-header text-center mb-5">
                            <h2 class="wow fadeInUp">Create Your Campaign</h2>
                            <p class="wow fadeInDown">Share your story and start making a difference in Zimbabwe</p>
                        </div>

                        <!-- Progress Steps -->
                        <div class="wizard-progress mb-5">
                            <div class="progress-container">
                                @for($i = 1; $i <= $totalSteps; $i++)
                                <div class="step {{ $currentStep >= $i ? 'active' : '' }} {{ $currentStep > $i ? 'completed' : '' }}"
                                     wire:click="goToStep({{ $i }})">
                                    <div class="step-number">
                                        @if($currentStep > $i)
                                            <i class="bi bi-check"></i>
                                        @else
                                            {{ $i }}
                                        @endif
                                    </div>
                                    <div class="step-label">
                                        @switch($i)
                                            @case(1) Basic Info @break
                                            @case(2) Your Story @break
                                            @case(3) Financial @break
                                            @case(4) Media @break
                                            @case(5) Review @break
                                        @endswitch
                                    </div>
                                </div>
                                @if($i < $totalSteps)
                                <div class="step-connector {{ $currentStep > $i ? 'completed' : '' }}"></div>
                                @endif
                                @endfor
                            </div>
                        </div>

                        <!-- Messages -->
                        @if (session()->has('message'))
                            <div class="alert alert-success wow fadeInUp mb-4">
                                {{ session('message') }}
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger wow fadeInUp mb-4">
                                {{ session('error') }}
                            </div>
                        @endif

                        <!-- Validation Errors Summary -->
                        @if ($errors->any())
                            <div class="alert alert-danger wow fadeInUp mb-4">
                                <h6><i class="bi bi-exclamation-circle"></i> Please fix the following errors:</h6>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Form Content -->
                        <div class="wizard-content">
                            @if($currentStep === 1)
                                <!-- Step 1: Basic Information -->
                                <div class="step-content wow fadeInUp">
                                    <h4 class="step-title">Basic Information</h4>
                                    <p class="step-description">Let's start with the basics of your campaign</p>

                                    <div class="form-group mb-4">
                                        <label class="form-label required">Campaign Title</label>
                                        <input type="text"
                                               wire:model="title"
                                               class="form-control @error('title') is-invalid @enderror"
                                               placeholder="Give your campaign a compelling title">
                                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="form-label required">Short Description</label>
                                        <textarea wire:model="description"
                                                  class="form-control @error('description') is-invalid @enderror"
                                                  rows="3"
                                                  placeholder="Brief description of your campaign (max 500 characters)"></textarea>
                                        <div class="character-count">{{ strlen($description) }}/500</div>
                                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="form-label required">Categories</label>
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

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label class="form-label">Location</label>
                                                <input type="text"
                                                       wire:model="location"
                                                       class="form-control"
                                                       placeholder="City, Province">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label class="form-label">Campaign End Date</label>
                                                <input type="date"
                                                       wire:model="end_date"
                                                       class="form-control @error('end_date') is-invalid @enderror">
                                                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @elseif($currentStep === 2)
                                <!-- Step 2: Detailed Story -->
                                <div class="step-content wow fadeInUp">
                                    <h4 class="step-title">Tell Your Story</h4>
                                    <p class="step-description">Share the details that will inspire people to donate</p>

                                    <div class="form-group mb-4">
                                        <label class="form-label required">Your Campaign Story</label>
                                        <textarea wire:model="story"
                                                  class="form-control story-textarea @error('story') is-invalid @enderror"
                                                  rows="12"
                                                  placeholder="Tell the full story of your campaign. Why is this important? How will donations help? Be specific about how funds will be used."></textarea>
                                        <div class="character-count">{{ strlen($story) }} characters (minimum 100)</div>
                                        @error('story') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Beneficiary Information -->
                                    <div class="beneficiary-section">
                                        <h5 class="section-subtitle">Beneficiary Information (Optional)</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Beneficiary Name</label>
                                                    <input type="text"
                                                           wire:model="beneficiary_name"
                                                           class="form-control"
                                                           placeholder="Who will benefit from this campaign?">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Relationship to You</label>
                                                    <input type="text"
                                                           wire:model="beneficiary_relationship"
                                                           class="form-control"
                                                           placeholder="e.g., My daughter, Community member">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Age</label>
                                                    <input type="number"
                                                           wire:model="beneficiary_age"
                                                           class="form-control"
                                                           min="1" max="120">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Contact Information</label>
                                                    <input type="text"
                                                           wire:model="beneficiary_contact"
                                                           class="form-control"
                                                           placeholder="Phone or email (optional)">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @elseif($currentStep === 3)
                                <!-- Step 3: Financial Details -->
                                <div class="step-content wow fadeInUp">
                                    <h4 class="step-title">Financial Details</h4>
                                    <p class="step-description">Set your fundraising goal and donation settings</p>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group mb-4">
                                                <label class="form-label required">Fundraising Goal</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number"
                                                           wire:model="goal_amount"
                                                           class="form-control @error('goal_amount') is-invalid @enderror"
                                                           placeholder="0.00"
                                                           step="0.01">
                                                </div>
                                                @error('goal_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                <div class="form-text">Set a realistic goal that covers your needs plus any fees</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-4">
                                                <label class="form-label">Currency</label>
                                                <select wire:model="currency" class="form-select">
                                                    <option value="USD">USD ($)</option>
                                                    <option value="ZWL">ZWL (Z$)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label class="form-label">Minimum Donation</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number"
                                                           wire:model="minimum_donation"
                                                           class="form-control @error('minimum_donation') is-invalid @enderror"
                                                           min="1">
                                                </div>
                                                @error('minimum_donation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fund Usage Breakdown (Optional) -->
                                    <div class="fund-breakdown">
                                        <h5 class="section-subtitle">How will funds be used?</h5>
                                        <div class="breakdown-tip">
                                            <i class="bi bi-lightbulb"></i>
                                            <span>Being transparent about fund usage increases donor confidence</span>
                                        </div>
                                    </div>
                                </div>

                            @elseif($currentStep === 4)
                                <!-- Step 4: Media Upload -->
                                <div class="step-content wow fadeInUp">
                                    <h4 class="step-title">Add Images</h4>
                                    <p class="step-description">Images help tell your story and build trust with donors</p>

                                    <!-- Featured Image -->
                                    <div class="form-group mb-5">
                                        <label class="form-label {{ $status === 'pending' ? 'required' : '' }}">Featured Image</label>
                                        <div class="image-upload-area featured-upload">
                                            <input type="file"
                                                   wire:model="featured_image"
                                                   id="featured_image"
                                                   class="image-input"
                                                   accept="image/*">
                                            <label for="featured_image" class="image-upload-label">
                                                @if ($featured_image)
                                                    <div class="image-preview">
                                                        <img src="{{ $featured_image->temporaryUrl() }}" alt="Featured Image Preview">
                                                        <div class="upload-overlay">
                                                            <i class="bi bi-camera"></i>
                                                            <span>Change Image</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="upload-content">
                                                        <i class="bi bi-cloud-upload"></i>
                                                        <h5>Upload Featured Image</h5>
                                                        <p>This will be the main image for your campaign</p>
                                                        <span class="file-info">PNG, JPG up to 2MB</span>
                                                    </div>
                                                @endif
                                            </label>
                                        </div>
                                        @error('featured_image') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Gallery Images -->
                                    <div class="form-group mb-4">
                                        <label class="form-label">Additional Images (Optional)</label>
                                        <div class="gallery-upload-area">
                                            <input type="file"
                                                   wire:model="gallery"
                                                   id="gallery"
                                                   class="image-input"
                                                   accept="image/*"
                                                   multiple>
                                            <label for="gallery" class="gallery-upload-label">
                                                <div class="upload-content">
                                                    <i class="bi bi-images"></i>
                                                    <h6>Add More Images</h6>
                                                    <p>Upload up to 5 additional images</p>
                                                </div>
                                            </label>
                                        </div>

                                        <!-- Gallery Preview -->
                                        @if(count($gallery) > 0)
                                        <div class="gallery-preview mt-3">
                                            <div class="row g-3">
                                                @foreach($gallery as $index => $image)
                                                <div class="col-md-3">
                                                    <div class="gallery-item">
                                                        <img src="{{ $image->temporaryUrl() }}" alt="Gallery Image {{ $index + 1 }}">
                                                        <button type="button"
                                                                wire:click="removeGalleryImage({{ $index }})"
                                                                class="remove-btn">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                        @error('gallery.*') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                            @elseif($currentStep === 5)
                                <!-- Step 5: Review & Settings -->
                                <div class="step-content wow fadeInUp">
                                    <h4 class="step-title">Review & Final Settings</h4>
                                    <p class="step-description">Review your campaign and choose your privacy settings</p>

                                    <!-- Campaign Preview -->
                                    <div class="campaign-preview mb-5">
                                        <h5>Campaign Preview</h5>
                                        <div class="preview-card">
                                            @if($featured_image)
                                            <div class="preview-image">
                                                <img src="{{ $featured_image->temporaryUrl() }}" alt="Campaign Preview">
                                            </div>
                                            @endif
                                            <div class="preview-content">
                                                <h6>{{ $title }}</h6>
                                                <p>{{ $description }}</p>
                                                <div class="preview-meta">
                                                    <span class="goal">Goal: ${{ number_format($goal_amount) }}</span>
                                                    @if($location)
                                                    <span class="location"><i class="bi bi-geo-alt"></i> {{ $location }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Privacy Settings -->
                                    <div class="privacy-settings mb-4">
                                        <h5>Privacy Settings</h5>
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   wire:model="allow_anonymous_donations"
                                                   id="allow_anonymous">
                                            <label class="form-check-label" for="allow_anonymous">
                                                Allow anonymous donations
                                            </label>
                                            <div class="form-text">Donors can choose to hide their name and donation amount</div>
                                        </div>
                                    </div>

                                    <!-- Initial Update -->
                                    <div class="initial-update mb-4">
                                        <h5>Add an Initial Update (Optional)</h5>
                                        <div class="form-group mb-3">
                                            <label class="form-label">Update Title</label>
                                            <input type="text"
                                                   wire:model="initial_update_title"
                                                   class="form-control"
                                                   placeholder="e.g., Campaign Launch">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Update Content</label>
                                            <textarea wire:model="initial_update_content"
                                                      class="form-control"
                                                      rows="4"
                                                      placeholder="Share an update about your campaign launch"></textarea>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="wizard-navigation">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    @if($currentStep > 1)
                                    <button type="button"
                                            wire:click="previousStep"
                                            class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Previous
                                    </button>
                                    @endif
                                </div>

                                <div class="d-flex gap-3">
                                    <!-- Save Draft Button (available on all steps) -->
                                    <button type="button"
                                            wire:click="saveDraft"
                                            class="btn btn-outline-primary"
                                            wire:loading.attr="disabled">
                                        <div wire:loading.remove wire:target="saveDraft">
                                            <i class="bi bi-save"></i> Save Draft
                                        </div>
                                        <div wire:loading wire:target="saveDraft">
                                            Saving...
                                        </div>
                                    </button>

                                    @if($currentStep < $totalSteps)
                                    <button wire:click="nextStep" type="button"
                                            class="btn_theme btn_theme_active"
                                            wire:loading.attr="disabled"
                                            wire:target="nextStep">
                                        <div wire:loading.remove wire:target="nextStep">
                                            Next <i class="bi bi-arrow-right"></i>
                                        </div>
                                        <div wire:loading wire:target="nextStep">
                                            <i class="bi bi-hourglass-split"></i> Validating...
                                        </div>
                                    </button>
                                    @else
                                    <!-- Final submission buttons -->
                                    <button type="button"
                                            wire:click="submitForReview"
                                            class="btn_theme btn_theme_active"
                                            wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="submitForReview">
                                            Submit for Review <i class="bi bi-check-circle"></i>
                                        </span>
                                        <span wire:loading wire:target="submitForReview">
                                            Submitting...
                                        </span>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <livewire:footer />

    <style>
    .campaign-wizard-wrapper {
        background: white;
        padding: 3rem;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    /* Progress Steps Styling */
    .wizard-progress {
        margin-bottom: 3rem;
    }

    .progress-container {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 80px;
    }

    .step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }

    .step.active .step-number {
        background: #f74f22;
        color: white;
    }

    .step.completed .step-number {
        background: #28a745;
        color: white;
    }

    .step-label {
        font-size: 0.85rem;
        font-weight: 500;
        color: #6c757d;
        text-align: center;
    }

    .step.active .step-label {
        color: #f74f22;
    }

    .step.completed .step-label {
        color: #28a745;
    }

    .step-connector {
        width: 40px;
        height: 2px;
        background: #e9ecef;
        transition: all 0.3s ease;
    }

    .step-connector.completed {
        background: #28a745;
    }

    /* Step Content Styling */
    .step-content {
        min-height: 400px;
        padding: 2rem 0;
    }

    .step-title {
        color: #333;
        margin-bottom: 0.5rem;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .step-description {
        color: #666;
        margin-bottom: 2rem;
        font-size: 1rem;
    }

    .section-subtitle {
        color: #333;
        margin-bottom: 1rem;
        font-size: 1.2rem;
        font-weight: 600;
        border-bottom: 2px solid #f74f22;
        display: inline-block;
        padding-bottom: 0.25rem;
    }

    /* Form Styling */
    .form-label.required::after {
        content: " *";
        color: #dc3545;
    }

    .character-count {
        text-align: right;
        font-size: 0.8rem;
        color: #666;
        margin-top: 0.25rem;
    }

    .story-textarea {
        min-height: 300px;
        resize: vertical;
    }

    /* Category Grid */
    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
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
        height: 100%;
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

    /* Image Upload Styling */
    .image-upload-area {
        position: relative;
        margin-bottom: 1rem;
    }

    .image-input {
        display: none;
    }

    .image-upload-label {
        display: block;
        border: 2px dashed #ddd;
        border-radius: 15px;
        padding: 3rem 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .image-upload-label:hover {
        border-color: #f74f22;
        background: rgba(247, 79, 34, 0.05);
    }

    .upload-content i {
        font-size: 3rem;
        color: #ddd;
        margin-bottom: 1rem;
    }

    .upload-content h5 {
        color: #333;
        margin-bottom: 0.5rem;
    }

    .upload-content p {
        color: #666;
        margin-bottom: 0.5rem;
    }

    .file-info {
        font-size: 0.85rem;
        color: #999;
    }

    .featured-upload .image-upload-label {
        min-height: 200px;
    }

    .image-preview {
        position: relative;
        border-radius: 15px;
        overflow: hidden;
    }

    .image-preview img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .upload-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: white;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .image-preview:hover .upload-overlay {
        opacity: 1;
    }

    /* Gallery Styling */
    .gallery-upload-area {
        border: 2px dashed #ddd;
        border-radius: 10px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .gallery-upload-area:hover {
        border-color: #f74f22;
        background: rgba(247, 79, 34, 0.05);
    }

    .gallery-upload-label {
        cursor: pointer;
    }

    .gallery-preview {
        margin-top: 1rem;
    }

    .gallery-item {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
    }

    .gallery-item img {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }

    .remove-btn {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: rgba(220, 53, 69, 0.8);
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .remove-btn:hover {
        background: rgba(220, 53, 69, 1);
    }

    /* Beneficiary Section */
    .beneficiary-section {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 15px;
        margin-top: 2rem;
    }

    /* Fund Breakdown */
    .fund-breakdown {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 15px;
        margin-top: 2rem;
    }

    .breakdown-tip {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #666;
        font-size: 0.9rem;
        background: white;
        padding: 1rem;
        border-radius: 8px;
        border-left: 4px solid #17a2b8;
    }

    .breakdown-tip i {
        color: #17a2b8;
    }

    /* Campaign Preview */
    .campaign-preview {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 15px;
    }

    .preview-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .preview-image {
        height: 200px;
        overflow: hidden;
    }

    .preview-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .preview-content {
        padding: 1.5rem;
    }

    .preview-content h6 {
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .preview-content p {
        color: #666;
        margin-bottom: 1rem;
    }

    .preview-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.9rem;
        color: #666;
    }

    .preview-meta .goal {
        color: #f74f22;
        font-weight: 600;
    }

    /* Privacy Settings */
    .privacy-settings {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 15px;
    }

    /* Initial Update */
    .initial-update {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 15px;
    }

    /* Navigation */
    .wizard-navigation {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid #eee;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .campaign-wizard-wrapper {
            padding: 2rem 1rem;
        }

        .progress-container {
            gap: 0.5rem;
        }

        .step {
            min-width: 60px;
        }

        .step-number {
            width: 40px;
            height: 40px;
            font-size: 0.9rem;
        }

        .step-label {
            font-size: 0.75rem;
        }

        .step-connector {
            width: 20px;
        }

        .category-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .wizard-navigation .d-flex {
            flex-direction: column;
            gap: 1rem;
        }

        .wizard-navigation .d-flex > div {
            width: 100%;
        }

        .wizard-navigation .btn {
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        .progress-container {
            flex-direction: column;
            gap: 1rem;
        }

        .step-connector {
            width: 2px;
            height: 20px;
        }

        .preview-meta {
            flex-direction: column;
            gap: 0.5rem;
        }
    }
    </style>
    @endsection
</div>

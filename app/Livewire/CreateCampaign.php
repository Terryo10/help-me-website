<?php

namespace App\Livewire;

use App\Models\Campaign;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateCampaign extends Component
{
    use WithFileUploads;

    // Basic Information
    public $title = '';
    public $description = '';
    public $story = '';

    // Financial Details
    public $goal_amount = '';
    public $currency = 'USD';
    public $minimum_donation = 1;

    // Campaign Details
    public $end_date = '';
    public $location = '';
    public $category_ids = [];

    // Media
    public $featured_image;
    public $gallery = [];
    public $existing_gallery = [];

    // Beneficiary Information
    public $beneficiary_name = '';
    public $beneficiary_relationship = '';
    public $beneficiary_age = '';
    public $beneficiary_contact = '';

    // Settings
    public $allow_anonymous_donations = true;
    public $status = 'draft'; // draft, pending

    // Campaign Updates
    public $initial_update_title = '';
    public $initial_update_content = '';

    // Step management
    public $currentStep = 1;
    public $totalSteps = 5;

    protected function rules()
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'story' => 'required|string|min:100',
            'goal_amount' => 'required|numeric|min:1',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'minimum_donation' => 'required|numeric|min:1',
            'end_date' => 'nullable|date|after:today',
            'location' => 'nullable|string|max:255',
            'featured_image' => 'required|image|max:2048',
            'gallery.*' => 'required|image|max:2048',
            'beneficiary_name' => 'nullable|string|max:255',
            'beneficiary_relationship' => 'nullable|string|max:255',
            'beneficiary_age' => 'nullable|integer|min:1|max:120',
            'beneficiary_contact' => 'nullable|string|max:255',
            'allow_anonymous_donations' => 'boolean',
            'status' => 'required|in:draft,pending',
            'initial_update_title' => 'nullable|string|max:255',
            'initial_update_content' => 'nullable|string',
        ];

        // Conditional rules based on current step
        if ($this->currentStep >= 4 && $this->status === 'pending') {
            $rules['featured_image'] = 'required|image|max:2048';
        }

        return $rules;
    }

    protected $messages = [
        'title.required' => 'Campaign title is required.',
        'description.required' => 'A brief description is required.',
        'story.required' => 'Please tell your campaign story.',
        'story.min' => 'Your story should be at least 100 characters long.',
        'goal_amount.required' => 'Please set a fundraising goal.',
        'goal_amount.min' => 'Goal amount must be at least $1.',
        'category_ids.required' => 'Please select at least one category.',
        'featured_image.required' => 'A featured image is required to publish your campaign.',
    ];

    public function mount()
    {
        // Set default end date to 30 days from now
        $this->end_date = now()->addDays(30)->format('Y-m-d');
    }

    public function nextStep()
    {
        $this->validateCurrentStep();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep($step)
    {
        if ($step >= 1 && $step <= $this->totalSteps) {
            // Validate all previous steps
            for ($i = 1; $i < $step; $i++) {
                $this->validateStep($i);
            }
            $this->currentStep = $step;
        }
    }

    protected function validateCurrentStep()
    {
        $this->validateStep($this->currentStep);
    }

    protected function validateStep($step)
    {
        $rules = [];

        switch ($step) {
            case 1: // Basic Information
                $rules = [
                    'title' => 'required|string|max:255',
                    'description' => 'required|string|max:500',
                    'category_ids' => 'required|array|min:1',
                    'category_ids.*' => 'exists:categories,id',
                    'end_date' => 'nullable|date|after:today',
                    'location' => 'nullable|string|max:255',
                ];
                break;

            case 2: // Detailed Story
                $rules = [
                    'story' => 'required|string|min:10',
                    'beneficiary_name' => 'nullable|string|max:255',
                    'beneficiary_relationship' => 'nullable|string|max:255',
                    'beneficiary_age' => 'nullable|integer|min:1|max:120',
                    'beneficiary_contact' => 'nullable|string|max:255',
                ];
                break;

            case 3: // Financial Details
                $rules = [
                    'goal_amount' => 'required|numeric|min:1',
                    'minimum_donation' => 'required|numeric|min:1',
                    'currency' => 'required|in:USD,ZWL',
                ];
                break;

            case 4: // Media Upload
                $rules = [
                    'featured_image' => 'required|image|max:2048',
                    'gallery.*' => 'nullable|image|max:2048',
                ];

                if ($this->status === 'pending') {
                    $rules['featured_image'] = 'required|image|max:2048';
                }
                break;

            case 5: // Review & Settings
                $rules = [
                    'allow_anonymous_donations' => 'nullable|boolean',
                    'initial_update_title' => 'nullable|string|max:255',
                    'initial_update_content' => 'nullable|string',
                ];
                break;
        }

        if (!empty($rules)) {
            $this->validate($rules);
        }
    }

    public function removeGalleryImage($index)
    {
        if (isset($this->gallery[$index])) {
            unset($this->gallery[$index]);
            $this->gallery = array_values($this->gallery); // Re-index array
        }
    }

    public function saveDraft()
    {
        $this->status = 'draft';
        $this->createCampaign();
    }

    public function submitForReview()
    {
        $this->status = 'pending';
        $this->validate(); // Full validation for pending status
        $this->createCampaign();
    }

    public function createCampaign()
    {
        try {
            // Handle featured image upload
            $featuredImagePath = null;
            if ($this->featured_image) {
                $featuredImagePath = $this->featured_image->store('campaigns', 'public');
            }

            // Handle gallery uploads
            $galleryPaths = [];
            if ($this->gallery) {
                foreach ($this->gallery as $image) {
                    if ($image) {
                        $galleryPaths[] = $image->store('campaigns/gallery', 'public');
                    }
                }
            }

            // Create campaign
            $campaign = Campaign::create([
                'user_id' => Auth::id(),
                'title' => $this->title,
                'slug' => $this->generateUniqueSlug($this->title),
                'description' => $this->description,
                'story' => $this->story,
                'goal_amount' => $this->goal_amount,
                'currency' => $this->currency,
                'end_date' => $this->end_date,
                'location' => $this->location,
                'featured_image' => $featuredImagePath,
                'gallery' => $galleryPaths,
                'allow_anonymous_donations' => $this->allow_anonymous_donations,
                'minimum_donation' => $this->minimum_donation,
                'status' => $this->status,
                'beneficiary_info' => [
                    'name' => $this->beneficiary_name,
                    'relationship' => $this->beneficiary_relationship,
                    'age' => $this->beneficiary_age,
                    'contact' => $this->beneficiary_contact,
                ],
            ]);

            // Attach categories
            $campaign->categories()->attach($this->category_ids);

            // Create initial update if provided
            if ($this->initial_update_title && $this->initial_update_content) {
                $campaign->updates()->create([
                    'title' => $this->initial_update_title,
                    'content' => $this->initial_update_content,
                    'published_at' => $this->status === 'pending' ? now() : null,
                ]);
            }

            $message = $this->status === 'draft'
                ? 'Campaign saved as draft successfully!'
                : 'Campaign submitted for review successfully!';

            session()->flash('message', $message);

            return redirect()->route('campaigns.show', $campaign->slug);

        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while creating your campaign. Please try again.');
            logger('Campaign creation error: ' . $e->getMessage());
        }
    }

    protected function generateUniqueSlug($title)
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug . '-' . time();

        // Ensure uniqueness
        $count = 1;
        while (Campaign::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . time() . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function render()
    {
        $categories = Category::active()->ordered()->get();

        return view('livewire.create-campaign', [
            'categories' => $categories
        ])->extends('app');
    }
}

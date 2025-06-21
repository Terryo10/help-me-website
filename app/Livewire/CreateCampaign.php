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

    public $title = '';
    public $description = '';
    public $story = '';
    public $goal_amount = '';
    public $currency = 'USD';
    public $end_date = '';
    public $location = '';
    public $category_ids = [];
    public $featured_image;
    public $allow_anonymous_donations = true;
    public $minimum_donation = 1;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string|max:500',
        'story' => 'required|string',
        'goal_amount' => 'required|numeric|min:1',
        'end_date' => 'nullable|date|after:today',
        'location' => 'nullable|string|max:255',
        'category_ids' => 'required|array|min:1',
        'featured_image' => 'nullable|image|max:2048',
        'minimum_donation' => 'required|numeric|min:1',
    ];

    public function createCampaign()
    {
        $this->validate();

        $imagePath = null;
        if ($this->featured_image) {
            $imagePath = $this->featured_image->store('campaigns', 'public');
        }

        $campaign = Campaign::create([
            'user_id' => Auth::id(),
            'title' => $this->title,
            'slug' => Str::slug($this->title) . '-' . time(),
            'description' => $this->description,
            'story' => $this->story,
            'goal_amount' => $this->goal_amount,
            'currency' => $this->currency,
            'end_date' => $this->end_date,
            'location' => $this->location,
            'featured_image' => $imagePath,
            'allow_anonymous_donations' => $this->allow_anonymous_donations,
            'minimum_donation' => $this->minimum_donation,
            'status' => 'pending',
        ]);

        $campaign->categories()->attach($this->category_ids);

        session()->flash('message', 'Campaign created successfully! It will be reviewed before going live.');
        
        return redirect()->route('campaigns.show', $campaign->slug);
    }

    public function render()
    {
        $categories = Category::active()->ordered()->get();
        
        return view('livewire.create-campaign', [
            'categories' => $categories
        ])->extends('app');
    }
}
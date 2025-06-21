<?php

namespace App\Livewire;

use App\Models\Campaign;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class CampaignList extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';
    public $status = '';
    public $sortBy = 'latest';

    protected $queryString = ['search', 'category', 'status'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategory()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $campaigns = Campaign::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->category, function ($query) {
                $query->whereHas('categories', function ($q) {
                    $q->where('slug', $this->category);
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->sortBy === 'latest', function ($query) {
                $query->latest();
            })
            ->when($this->sortBy === 'popular', function ($query) {
                $query->orderBy('view_count', 'desc');
            })
            ->when($this->sortBy === 'ending', function ($query) {
                $query->whereNotNull('end_date')->orderBy('end_date', 'asc');
            })
            ->with('categories', 'user')
            ->paginate(12);

        $categories = Category::active()->ordered()->get();

        return view('livewire.campaign-list', [
            'campaigns' => $campaigns,
            'categories' => $categories
        ])->extends('app');
    }
}
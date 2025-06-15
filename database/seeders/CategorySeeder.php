<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Medical',
                'slug' => 'medical',
                'description' => 'Medical expenses, treatments, and health-related fundraising',
                'icon' => 'medical-bag',
                'color' => '#EF4444',
                'sort_order' => 1,
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'description' => 'School fees, educational programs, and learning resources',
                'icon' => 'academic-cap',
                'color' => '#3B82F6',
                'sort_order' => 2,
            ],
            [
                'name' => 'Emergency',
                'slug' => 'emergency',
                'description' => 'Natural disasters, accidents, and urgent situations',
                'icon' => 'exclamation-triangle',
                'color' => '#F59E0B',
                'sort_order' => 3,
            ],
            [
                'name' => 'Community',
                'slug' => 'community',
                'description' => 'Community development and local initiatives',
                'icon' => 'users',
                'color' => '#10B981',
                'sort_order' => 4,
            ],
            [
                'name' => 'Personal',
                'slug' => 'personal',
                'description' => 'Personal causes and individual needs',
                'icon' => 'user',
                'color' => '#8B5CF6',
                'sort_order' => 5,
            ],
            [
                'name' => 'Non-Profit',
                'slug' => 'non-profit',
                'description' => 'Registered non-profit organizations and charities',
                'icon' => 'heart',
                'color' => '#EC4899',
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}

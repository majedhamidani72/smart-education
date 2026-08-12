<?php

namespace Database\Seeders;

use App\Models\ContentType;
use Illuminate\Database\Seeder;

class ContentTypeSeeder extends Seeder
{
    public function run(): void
    {
        ContentType::insert([

            [
                'title' => 'ویدئو',
                'slug' => 'video',
                'icon' => 'video',
                'sort_order' => 1,
                'is_active' => true,
            ],

            [
                'title' => 'PDF',
                'slug' => 'pdf',
                'icon' => 'document',
                'sort_order' => 2,
                'is_active' => true,
            ],

            [
                'title' => 'گام به گام',
                'slug' => 'step_by_step',
                'icon' => 'academic-cap',
                'sort_order' => 3,
                'is_active' => true,
            ],

            [
                'title' => 'نمونه سوال',
                'slug' => 'sample_question',
                'icon' => 'question-mark',
                'sort_order' => 4,
                'is_active' => true,
            ],

        ]);
    }
}

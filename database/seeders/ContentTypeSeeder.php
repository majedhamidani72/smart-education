<?php

namespace Database\Seeders;

use App\Models\ContentType;
use Illuminate\Database\Seeder;

class ContentTypeSeeder extends Seeder
{
    public function run(): void
    {
        ContentType::upsert(

            [

                [

                    'title' => 'تدریس',

                    'slug' => 'teaching',

                    'icon' => 'heroicon-o-video-camera',

                    'sort_order' => 1,

                    'is_active' => true,

                ],

                [

                    'title' => 'گام به گام',

                    'slug' => 'step_by_step',

                    'icon' => 'heroicon-o-photo',

                    'sort_order' => 2,

                    'is_active' => true,

                ],

                [

                    'title' => 'نمونه سوالات',

                    'slug' => 'sample_questions',

                    'icon' => 'heroicon-o-document-text',

                    'sort_order' => 3,

                    'is_active' => true,

                ],

            ],

            ['slug'],

            [

                'title',

                'icon',

                'sort_order',

                'is_active',

            ]

        );
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
            [
                'meta_key' => 'title',
                'meta_value' => 'CMS',
            ],
            [
                'meta_key' => 'tagline',
                'meta_value' => 'CMS',
            ],
            [
                'meta_key' => 'separator',
                'meta_value' => '|',
            ],
            [
                'meta_key' => 'description',
                'meta_value' => 'CMS',
            ],
            [
                'meta_key' => 'focus_keyphrase',
                'meta_value' => 'CMS, Website',
            ],
            [
                'meta_key' => 'timezone',
                'meta_value' => 'UTC+7',
            ],
            [
                'meta_key' => 'date_format',
                'meta_value' => 'Y-m-d',
            ],
            [
                'meta_key' => 'time_format',
                'meta_value' => 'g:i A',
            ],
            [
                'meta_key' => 'admin_pagination_limit',
                'meta_value' => '20',
            ],
            [
                'meta_key' => 'language',
                'meta_value' => 'id_ID',
            ],
            [
                'meta_key' => 'start_of_week',
                'meta_value' => '1',
            ],
            [
                'meta_key' => 'search_engine_visibility',
                'meta_value' => '1',
            ],
            [
                'meta_key' => 'google_verification_code',
                'meta_value' => '',
            ],
            [
                'meta_key' => 'bing_verification_code',
                'meta_value' => '',
            ],
            [
                'meta_key' => 'organize_uploads',
                'meta_value' => '1',
            ],
            [
                'meta_key' => 'crop_image_to_exact_dimensions',
                'meta_value' => '1',
            ],
            [
                'meta_key' => 'thumbnail_width',
                'meta_value' => '150',
            ],
            [
                'meta_key' => 'thumbnail_height',
                'meta_value' => '150',
            ],
            [
                'meta_key' => 'medium_max_width',
                'meta_value' => '300',
            ],
            [
                'meta_key' => 'medium_max_height',
                'meta_value' => '300',
            ],
            [
                'meta_key' => 'large_max_width',
                'meta_value' => '1024',
            ],
            [
                'meta_key' => 'large_max_height',
                'meta_value' => '1024',
            ],
            [
                'meta_key' => 'multilanguage_website',
                'meta_value' => '0',
            ],
            [
                'meta_key' => 'permalink_news',
                'meta_value' => 'news',
            ],
            [
                'meta_key' => 'permalink_news_category',
                'meta_value' => 'categories',
            ],
            [
                'meta_key' => 'permalink_news_tag',
                'meta_value' => 'tags',
            ],
        ];

        foreach($datas as $data)
        {
            DB::table('settings')->insert([
                'meta_key' => $data['meta_key'],
                'meta_value' => $data['meta_value'],
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }
    }
}

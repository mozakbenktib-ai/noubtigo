<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'flag' => '🇺🇸',
                'is_rtl' => false,
                'is_active' => true,
            ],
            [
                'code' => 'fr',
                'name' => 'Français',
                'flag' => '🇫🇷',
                'is_rtl' => false,
                'is_active' => true,
            ],
            [
                'code' => 'ar',
                'name' => 'العربية',
                'flag' => '🇸🇦',
                'is_rtl' => true,
                'is_active' => true,
            ],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(['code' => $language['code']], $language);
        }
    }
}

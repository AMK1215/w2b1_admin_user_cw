<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerTextSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bannerTexts = [
            ['text' => 'မြန်မာနိုင်ငံရဲ့ အယုံကြည်ရဆုံး Slot Casino - Slot Casino Website - ကြီး', 'created_at' => now(), 'updated_at' => now()],
            ['text' => 'Play Smart, Win Big - Join Our Casino Today!', 'created_at' => now(), 'updated_at' => now()],
            ['text' => 'Welcome to the Best Online Casino Experience!', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('banner_texts')->insert($bannerTexts);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdsVedioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $videos = [
            'promo_intro.mp4',
            'bonus_offer.mp4',
            'cashback_ad.mp4',
            'spin_win.mp4',
            'weekend_fun.mp4',
        ];

        foreach ($videos as $video) {
            DB::table('ads_vedios')->insert([
                'video_ads' => $video,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

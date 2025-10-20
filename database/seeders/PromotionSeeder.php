<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promotions = [
            [
                'image' => 'promo_1.png',
                'title' => 'Welcome Bonus 100%',
                'description' => 'Get 100% bonus on your first deposit up to $500!',
            ],
            [
                'image' => 'promo_2.jfif',
                'title' => 'Daily Cashback',
                'description' => 'Receive up to 10% cashback on your losses every day.',
            ],
            [
                'image' => 'promo_3.jfif',
                'title' => 'Refer a Friend',
                'description' => 'Invite your friends and earn rewards for each successful referral.',
            ],
            [
                'image' => 'promo_4.jfif',
                'title' => 'Weekend Special',
                'description' => 'Special bonuses every weekend to boost your playtime.',
            ],
        ];

        foreach ($promotions as $promo) {
            DB::table('promotions')->insert([
                'image' => $promo['image'],
                'title' => $promo['title'],
                'description' => $promo['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

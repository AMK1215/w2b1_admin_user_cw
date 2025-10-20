<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WinnerTextSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample realistic messages
        $sampleTexts = [
            'Congratulations to our lucky winner of the week!',
            'You have just won big – enjoy your reward!',
            'Another winner joins the hall of fame!',
            'Massive win just landed – stay tuned for more!',
            'Cheers to our winner – well played!',
            'What a streak! You have hit the jackpot!',
            'Winner alert – you made it to the top!',
            'That is a wrap – another big win secured!',
            'Our latest winner is celebrating now!',
            'Big congratulations – you earned it!',
        ];

        foreach ($sampleTexts as $text) {
            DB::table('winner_texts')->insert([
                'text' => $text,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            [
                'user_id' => 2,
                'booking_id' => 6,
                'resource_id' => 1,
                'rating' => 5,
                'comment' => 'Great table and service.',
            ],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }
    }
}

<?php
namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            ['user_id' => 2, 'booking_id' => 1, 'resource_id' => 1, 'rating' => 5, 'comment' => 'Отличный столик, вид на улицу!'],
            ['user_id' => 2, 'booking_id' => 2, 'resource_id' => 2, 'rating' => 4, 'comment' => 'Просторно и уютно'],
            ['user_id' => 1, 'booking_id' => 3, 'resource_id' => 3, 'rating' => 5, 'comment' => 'VIP-кабинка — супер!'],
            ['user_id' => 2, 'booking_id' => 4, 'resource_id' => 4, 'rating' => 3, 'comment' => 'Терраса холодновата'],
        ];

        foreach ($reviews as $r) {
            Review::create($r);
        }
    }
}

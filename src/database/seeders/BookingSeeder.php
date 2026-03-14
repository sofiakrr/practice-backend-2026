<?php
namespace Database\Seeders;

use App\Models\Booking;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $futureDate = now()->addDays(2)->toDateString();
        $futureDateAdmin = now()->addDays(3)->toDateString();
        $futureDate2 = now()->addDays(4)->toDateString();
        $pastDateForReview = now()->subDays(3)->toDateString();
        $pastDateReviewed = now()->subDays(7)->toDateString();

        // Active bookings for schedule and availability checks
        Booking::create([
            'user_id'     => 2,
            'resource_id' => 1,
            'date'        => $futureDate,
            'start_time'  => '10:00',
            'end_time'    => '12:00',
            'status'      => 'active',
        ]);

        Booking::create([
            'user_id'     => 2,
            'resource_id' => 1,
            'date'        => $futureDate,
            'start_time'  => '14:00',
            'end_time'    => '16:00',
            'status'      => 'active',
        ]);

        Booking::create([
            'user_id'     => 1,
            'resource_id' => 3,
            'date'        => $futureDateAdmin,
            'start_time'  => '10:00',
            'end_time'    => '12:00',
            'status'      => 'active',
        ]);

        Booking::create([
            'user_id'     => 2,
            'resource_id' => 4,
            'date'        => $futureDate2,
            'start_time'  => '15:00',
            'end_time'    => '17:00',
            'status'      => 'active',
        ]);

        // Completed booking without review: used for demo "create review" step
        Booking::create([
            'user_id'     => 2,
            'resource_id' => 2,
            'date'        => $pastDateForReview,
            'start_time'  => '12:00',
            'end_time'    => '14:00',
            'status'      => 'active',
        ]);

        // Completed booking with existing review: used to show average rating
        Booking::create([
            'user_id'     => 2,
            'resource_id' => 1,
            'date'        => $pastDateReviewed,
            'start_time'  => '10:00',
            'end_time'    => '11:00',
            'status'      => 'active',
        ]);
    }
}

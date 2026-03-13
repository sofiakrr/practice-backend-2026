<?php
namespace Database\Seeders;

use App\Models\Booking;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // Брони user (id=2)
        Booking::create([
            'user_id'     => 2,
            'resource_id' => 1,
            'date'        => now()->addDay()->format('Y-m-d'),
            'start_time'  => '10:00',
            'end_time'    => '12:00',
            'status'      => 'active',
        ]);

        Booking::create([
            'user_id'     => 2,
            'resource_id' => 2,
            'date'        => now()->addDay()->format('Y-m-d'),
            'start_time'  => '14:00',
            'end_time'    => '16:00',
            'status'      => 'active',
        ]);

        // Бронь admin (id=1) — для теста №12
        Booking::create([
            'user_id'     => 1,
            'resource_id' => 4,
            'date'        => now()->addDays(3)->format('Y-m-d'),
            'start_time'  => '09:00',
            'end_time'    => '11:00',
            'status'      => 'active',
        ]);
    }
}

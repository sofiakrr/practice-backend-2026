<?php
namespace Database\Seeders;

use App\Models\Booking;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {

        Booking::create([
            'user_id'     => 2,
            'resource_id' => 1,
            'date'        => '2026-03-20',
            'start_time'  => '10:00',
            'end_time'    => '12:00',
            'status'      => 'active',
        ]);

        Booking::create([
            'user_id'     => 2,
            'resource_id' => 1,
            'date'        => '2026-03-20',
            'start_time'  => '14:00',
            'end_time'    => '16:00',
            'status'      => 'active',
        ]);

        Booking::create([
            'user_id'     => 1,
            'resource_id' => 3,
            'date'        => '2026-03-21',
            'start_time'  => '10:00',
            'end_time'    => '12:00',
            'status'      => 'active',
        ]);

        Booking::create([
            'user_id'     => 2,
            'resource_id' => 4,
            'date'        => '2026-03-22',
            'start_time'  => '15:00',
            'end_time'    => '17:00',
            'status'      => 'active',
        ]);
    }
}

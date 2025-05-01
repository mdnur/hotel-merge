<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Customer;
use App\Models\HotelSetting;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        User::create([
            'name' => 'Mohammad Nur',
            'email' => 'mdnur701@gmail.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Shaidul Islam Ripon',
            'email' => 'shaidul@app.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Nayemon Islam',
            'email' => 'nayemon@app.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Toaha',
            'email' => 'toaha@app.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Osman',
            'email' => 'osman@app.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Ashab',
            'email' => 'ashab@app.com',
            'password' => Hash::make('password'),
        ]);

        // User::factory(4)->create();

        PaymentType::create(['name' => 'Bkash']);
        PaymentType::create(['name' => 'Nagad']);
        PaymentType::create(['name' => 'Rocket']);

        // Customer::factory(10)->create();

        RoomType::create(['name' => 'Super Deluxe couple', 'room_rent' => 2250]);
        RoomType::create(['name' => 'Deluxe couple', 'room_rent' => 2000]);
        RoomType::create(['name' => 'Eco Deluxe couple', 'room_rent' => 1500]);
        RoomType::create(['name' => 'Deluxe Double', 'room_rent' => 3000]);
        RoomType::create(['name' => 'Family Suits', 'room_rent' => 3500]);

        HotelSetting::create([
            'name' => 'Check in time',
            'description' => '12:30 PM',
        ]);
        HotelSetting::create([
            'name' => 'Check out Time',
            'description' => '11:00 PM',
        ]);

        HotelSetting::create([
            'name' => 'Note',
            'description' => 'Please bring a photocopy of your NID card or passport at the time of arrival, sir.',
        ]);

        HotelSetting::create([
            'name' => 'cancellation_policy',
            'description' => 'If a guest informs the Hotel Authorities to Cancel the Booking 24 hours prior to the Check-in date, The advance Payment will not be refunded but if he Re-Booked the room within the next 30 Working Days if the room is empty, The Previous Advance will be an adjustment to the Current Rate. The room cannot be canceled after check-in.',
        ]);

        $now = Carbon::now();

        $settings = [
            ['key' => 'check_out_time', 'label' => 'Check Out Time', 'value' => '11:15'],
            ['key' => 'check_in_start_time', 'label' => 'Check in Start Time', 'value' => '5:00'],
            ['key' => 'check_in_end_time', 'label' => 'Check In End Time', 'value' => '4:59'],
            ['key' => 'billing_start', 'label' => 'Billing Start', 'value' => '5:00'],
            ['key' => 'billing_end', 'label' => 'Billing End', 'value' => '11:30'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'label' => $setting['label'],
                    'value' => $setting['value'],
                    'description' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        Role::create([
            'name' => 'Super Admin',
            'guard_name' => 'web',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // DB::table('model_has_roles')->inserrt

        DB::table('model_has_roles')->insert([
            'role_id' => 1,
            'model_type' => 'App\Models\User',
            'model_id' => '1',
        ]);

        // Payment::factory(10)->create();

        // Reservation::factory(10)->create();

        // Room::factory(10)->create();

    }
}

<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Room;
use App\Models\User;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\RoomType;
use App\Models\PaymentType;
use App\Models\Reservation;
use App\Models\HotelSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'password'=> Hash::make('password'),
        ]);



        User::create([
            'name' => 'Shaidul Islam Ripon',
            'email' => 'shaidul@app.com',
            'password'=> Hash::make('password'),
        ]);

        User::create([
            'name' => 'Nayemon Islam',
            'email' => 'nayemon@app.com',
            'password'=> Hash::make('password'),
        ]);



        User::create([
            'name' => 'Toaha',
            'email' => 'toaha@app.com',
            'password'=> Hash::make('password'),
        ]);


        User::create([
            'name' => 'Osman',
            'email' => 'osman@app.com',
            'password'=> Hash::make('password'),
        ]);

        User::create([
            'name' => 'Ashab',
            'email' => 'ashab@app.com',
            'password'=> Hash::make('password'),
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


        // Payment::factory(10)->create();


        // Reservation::factory(10)->create();


        // Room::factory(10)->create();


    }
}

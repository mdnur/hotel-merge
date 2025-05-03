<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Customer;
use App\Models\ExpenseType;
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

        // PaymentType::create(['name' => 'Bkash']);
        // PaymentType::create(['name' => 'Nagad']);
        // PaymentType::create(['name' => 'Rocket']);

        // Customer::factory(10)->create();

        // RoomType::create(['name' => 'Super Deluxe couple', 'room_rent' => 2250]);
        // RoomType::create(['name' => 'Deluxe couple', 'room_rent' => 2000]);
        // RoomType::create(['name' => 'Eco Deluxe couple', 'room_rent' => 1500]);
        // RoomType::create(['name' => 'Deluxe Double', 'room_rent' => 3000]);
        // RoomType::create(['name' => 'Family Suits', 'room_rent' => 3500]);

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

        RoomType::create(['name' => 'Super Deluxe couple', 'room_rent' => 2250]); // 1
        RoomType::create(['name' => 'Deluxe couple', 'room_rent' => 2000]); // 2
        RoomType::create(['name' => 'Eco Deluxe couple', 'room_rent' => 1500]); // 3
        RoomType::create(['name' => 'Deluxe Double', 'room_rent' => 3000]); // 4
        RoomType::create(['name' => 'Family Suits', 'room_rent' => 3500]); // 5
        RoomType::create(['name' => 'Premium Couple', 'room_rent' => 2800]); // 6
        RoomType::create(['name' => 'Premium Three bed', 'room_rent' => 3300]); // 7
        RoomType::create(['name' => 'Premium Double bed', 'room_rent' => 4000]); // 8
        RoomType::create(['name' => 'Royal Suits', 'room_rent' => 5000]); // 9

        Room::create(['room_no' => '101', 'room_type_id' => 2]);
        Room::create(['room_no' => '102', 'room_type_id' => 2]);
        Room::create(['room_no' => '103', 'room_type_id' => 4]);
        Room::create(['room_no' => '104', 'room_type_id' => 4]);
        Room::create(['room_no' => '105', 'room_type_id' => 2]);
        Room::create(['room_no' => '106', 'room_type_id' => 2]);
        Room::create(['room_no' => '107', 'room_type_id' => 4]);
        Room::create(['room_no' => '108', 'room_type_id' => 2]);
        Room::create(['room_no' => '109', 'room_type_id' => 5]);
        Room::create(['room_no' => '110', 'room_type_id' => 5]);
        Room::create(['room_no' => '111', 'room_type_id' => 2]);

        Room::create(['room_no' => '201', 'room_type_id' => 2]);
        Room::create(['room_no' => '202', 'room_type_id' => 3]);
        Room::create(['room_no' => '203', 'room_type_id' => 4]);
        Room::create(['room_no' => '204', 'room_type_id' => 4]);
        Room::create(['room_no' => '205', 'room_type_id' => 3]);
        Room::create(['room_no' => '206', 'room_type_id' => 2]);
        Room::create(['room_no' => '207', 'room_type_id' => 4]);
        Room::create(['room_no' => '208', 'room_type_id' => 2]);
        Room::create(['room_no' => '209', 'room_type_id' => 3]);
        Room::create(['room_no' => '210', 'room_type_id' => 4]);
        Room::create(['room_no' => '211', 'room_type_id' => 4]);
        Room::create(['room_no' => '212', 'room_type_id' => 5]);
        Room::create(['room_no' => '213', 'room_type_id' => 2]);

        Room::create(['room_no' => '301', 'room_type_id' => 1]);
        Room::create(['room_no' => '302', 'room_type_id' => 1]);
        Room::create(['room_no' => '303', 'room_type_id' => 1]);
        Room::create(['room_no' => '304', 'room_type_id' => 1]);
        Room::create(['room_no' => '305', 'room_type_id' => 1]);
        Room::create(['room_no' => '306', 'room_type_id' => 1]);
        Room::create(['room_no' => '307', 'room_type_id' => 1]);
        Room::create(['room_no' => '308', 'room_type_id' => 1]);
        Room::create(['room_no' => '309', 'room_type_id' => 1]);
        Room::create(['room_no' => '310', 'room_type_id' => 1]);
        Room::create(['room_no' => '311', 'room_type_id' => 1]);
        Room::create(['room_no' => '312', 'room_type_id' => 1]);
        Room::create(['room_no' => '313', 'room_type_id' => 1]);

        Room::create(['room_no' => '401', 'room_type_id' => 6]);
        Room::create(['room_no' => '402', 'room_type_id' => 7]);
        Room::create(['room_no' => '403', 'room_type_id' => 6]);
        Room::create(['room_no' => '404', 'room_type_id' => 6]);
        Room::create(['room_no' => '405', 'room_type_id' => 7]);
        Room::create(['room_no' => '406', 'room_type_id' => 6]);
        Room::create(['room_no' => '407', 'room_type_id' => 8]);
        Room::create(['room_no' => '408', 'room_type_id' => 6]);
        Room::create(['room_no' => '409', 'room_type_id' => 6]);
        Room::create(['room_no' => '410', 'room_type_id' => 7]);
        Room::create(['room_no' => '411', 'room_type_id' => 7]);
        Room::create(['room_no' => '412', 'room_type_id' => 6]);
        Room::create(['room_no' => '413', 'room_type_id' => 6]);

        Room::create(['room_no' => '501', 'room_type_id' => 6]);
        Room::create(['room_no' => '502', 'room_type_id' => 7]);
        Room::create(['room_no' => '503', 'room_type_id' => 6]);
        Room::create(['room_no' => '504', 'room_type_id' => 6]);
        Room::create(['room_no' => '505', 'room_type_id' => 7]);
        Room::create(['room_no' => '506', 'room_type_id' => 6]);
        Room::create(['room_no' => '507', 'room_type_id' => 8]);
        Room::create(['room_no' => '508', 'room_type_id' => 6]);
        Room::create(['room_no' => '509', 'room_type_id' => 6]);
        Room::create(['room_no' => '510', 'room_type_id' => 6]);
        Room::create(['room_no' => '511', 'room_type_id' => 7]);
        Room::create(['room_no' => '512', 'room_type_id' => 6]);
        Room::create(['room_no' => '513', 'room_type_id' => 6]);

        Room::create(['room_no' => '601', 'room_type_id' => 6]);
        Room::create(['room_no' => '602', 'room_type_id' => 9]);
        Room::create(['room_no' => '603', 'room_type_id' => 9]);
        Room::create(['room_no' => '604', 'room_type_id' => 6]);
        Room::create(['room_no' => '605', 'room_type_id' => 7]);
        Room::create(['room_no' => '606', 'room_type_id' => 6]);
        Room::create(['room_no' => '607', 'room_type_id' => 8]);
        Room::create(['room_no' => '608', 'room_type_id' => 6]);
        Room::create(['room_no' => '609', 'room_type_id' => 6]);
        Room::create(['room_no' => '610', 'room_type_id' => 6]);
        Room::create(['room_no' => '611', 'room_type_id' => 6]);

        ExpenseType::create(['name' => 'Salary Purpose']);
        ExpenseType::create(['name' => 'Staff Bonus']);
        ExpenseType::create(['name' => 'Advance Salary']);
        ExpenseType::create(['name' => 'Electric bill']);
        ExpenseType::create(['name' => 'Staff Related Bill']);
        ExpenseType::create(['name' => 'Snack Bill']);
        ExpenseType::create(['name' => 'Fruits Bill']);
        ExpenseType::create(['name' => 'Drinking Water']);
        ExpenseType::create(['name' => 'Online Marketing']);
        ExpenseType::create(['name' => 'Maintenance']);
        ExpenseType::create(['name' => 'Generator Diesel']);
        ExpenseType::create(['name' => 'Govt Bill']);
        ExpenseType::create(['name' => 'Donation']);
        ExpenseType::create(['name' => 'Staff Food Bill']);
        ExpenseType::create(['name' => 'Mobile Recharge']);
        ExpenseType::create(['name' => 'Lift Service Purpose']);
        ExpenseType::create(['name' => 'Any kind of tech bill']);
        ExpenseType::create(['name' => 'PDB Line Purpose']);
        ExpenseType::create(['name' => 'laborer Purpose']);
        ExpenseType::create(['name' => 'Amenity Purpose']);
        ExpenseType::create(['name' => 'Conveyance']);
        ExpenseType::create(['name' => 'Any Kind of Element Buy']);
        ExpenseType::create(['name' => 'Co-mission']);
        ExpenseType::create(['name' => 'Laundry Bill']);
        ExpenseType::create(['name' => 'Others']);
        ExpenseType::create(['name' => 'Hotel Owner Asso']);

        PaymentType::create(['name' => 'Cash']);
        PaymentType::create(['name' => 'Bkash']);
        PaymentType::create(['name' => 'Nagad']);
        PaymentType::create(['name' => 'Rocket']);
        PaymentType::create(['name' => 'CITY Bank']);
        PaymentType::create(['name' => 'DBBL']);
        PaymentType::create(['name' => 'BRAC Bank']);
        PaymentType::create(['name' => 'EBL Bank']);
        PaymentType::create(['name' => 'UCB Bank']);

    }
}

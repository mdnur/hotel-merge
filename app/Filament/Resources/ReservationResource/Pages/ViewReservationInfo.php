<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use Carbon\Carbon;
use App\Models\RoomType;
use App\Classes\BDBulkSms;
use App\Models\Reservation;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\ReservationResource;

class ViewReservationInfo extends ViewRecord
{
    protected static string $view = 'filament.resources.reservation.pages.view-reservation-info';
    protected static string $resource = ReservationResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
            ]);
    }

    public function openNewUserModal($type){
        $reservation = Reservation::whereId($this->record->id)->with('customer')->with('rooms')->with('user')->with('payment')->first();
        $this->sendConfigrmation($reservation, $type);
    }
    public  function sendConfigrmation($reservation, $type = "")
    {

        $message = $type . "Confirmation Message from Hotel Amin International.\n" .
            "Reservation No: " . $reservation->reservation_no . "\n" .
            "Booking Date: " . Carbon::parse($reservation->booking_date)->format('Y-m-d') . "\n" .
            "Name: " .  $reservation->customer->name . "\n" .
            "Mobile Number: " . $reservation->customer->phone . "\n" .
            "Address: " . $reservation->customer->address . "\n" .
            "Mobile Number: " . $reservation->customer->phone . "\n";
        $message .= "Room Type: ";
        $sum = 0;
        foreach ($reservation->rooms as $room) {
            $sum += $room->quantity;
            $message .= RoomType::find($room->room_type_id)->name . "(" . $room->quantity . ")";
        }
        $message .= "\nTotal room: " . $sum . "\n";
        $message .= "Check-in: " . Carbon::parse($reservation->check_in_date)->format('Y-m-d') . " (" . \App\Models\HotelSetting::find(1)->description . ")\n" .
            "Check-out: " . Carbon::parse($reservation->check_out_date)->format('Y-m-d') . " (" . \App\Models\HotelSetting::find(2)->description . ")\n";

        $rent = "";
        foreach ($reservation->rooms as $room) {
            $rent .= $room['rent'] . " / ";
        }
        $message .= "Room price: " . rtrim($rent, ' / ') . " Taka\n";
        $message .= "Total price: " .  $reservation->total_rent . " Taka\n";
        $message .= "Advance: " . $reservation->payment->advance . " Taka\n";
        $message .= "Due: " . $reservation->total_rent-$reservation->payment->advance . " Taka\n";
        $message .= "(" . \App\Models\PaymentType::find($reservation->payment->payment_type_id)->name . ")" . "Last 3 Digits:" . $reservation->payment->Last3Digit . "\n";
        $message .= "Booked By: " . $reservation->user->name . "\n";
        if($reservation->reference != null){
            $message .= "Reference by: " . $reservation->reference . "\n";
        }
        $message .= "Note: " . \App\Models\HotelSetting::find(3)->description . "\n";
        $message .= "Our Cancellation Policy:" . \App\Models\HotelSetting::find(4)->description;

        $sms = new BDBulkSms($reservation->customer->phone, $message);
        if ($sms->send()) {
            $reservation->confrim_message_sent_at = Carbon::now();
            $reservation->save();
            Notification::make()
                ->title($type . 'Confirmation Message sent successfully')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title($type . 'Confirmation Message sent failed')
                ->error()
                ->send();
        }
    }
}

<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Classes\BDBulkSms;
use App\Filament\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\RoomType;
use Carbon\Carbon;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

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

    public function openNewUserModal($type)
    {
        $reservation = Reservation::whereId($this->record->id)->with('customer')->with('reservationRooms')->with('user')->with('payments')->first();
        $this->sendConfigrmation($reservation, $type);
    }

    public function sendConfigrmation($reservation, $type = '')
    {
        $message = $type."Confirmation Message from Hotel Amin International.\n".
        'Reservation No: '.$reservation->reservation_no."\n".
        'Booking Date: '.Carbon::parse($reservation->booking_date)->format('Y-m-d')."\n".
        'Name: '.$reservation->customer->name."\n".
        'Mobile Number: '.$reservation->customer->phone."\n".
        'Address: '.$reservation->customer->address."\n";

        $message .= 'Room Type: ';
        $sum = 0;
        foreach ($reservation->reservationRooms as $room) {
            $sum += $room->quantity;
            $message .= RoomType::find($room->room_type_id)->name.'('.$room->quantity.') ';
        }
        $message .= "\nTotal room: ".$sum."\n";

        $message .= 'Check-in: '.Carbon::parse($reservation->check_in_date)->format('Y-m-d').' ('.\App\Models\HotelSetting::find(1)->description.")\n".
            'Check-out: '.Carbon::parse($reservation->check_out_date)->format('Y-m-d').' ('.\App\Models\HotelSetting::find(2)->description.")\n";

        $rent = '';
        foreach ($reservation->reservationRooms as $room) {
            $rent .= $room['rent'].' / ';
        }
        $message .= 'Room price: '.rtrim($rent, ' / ')." Taka\n";
        $message .= 'Total price: '.$reservation->total_rent." Taka\n";

        // 🔁 Loop through all payments
        $totalAdvance = 0;
        $index = 1;
        foreach ($reservation->payments as $payment) {
            $paymentType = \App\Models\PaymentType::find($payment->payment_type_id)?->name ?? 'N/A';
            if ($reservation->payments->count() > 1) {
                $message .= 'Advance '.$index++.': '.$payment->amount.' Taka by '.$paymentType;
                if ($payment->tnx) {
                    $message .= ' ('.$payment->tnx.')';
                }
                $message .= "\n";

            }
            $totalAdvance += $payment->amount;
        }
        // ;
        // dd(\App\Models\PaymentType::find($reservation->payments->first()->payment_type_id)?->name);
        if ($reservation->payments->count() > 1) {
            $message .= 'Total Advance: '.$totalAdvance." Taka\n";
        } else {
            $firstPayment = $reservation->payments->first();
            $paymentTypeName = \App\Models\PaymentType::find($firstPayment->payment_type_id)?->name ?? 'N/A';

            $message .= 'Total Advance: '.$totalAdvance.' Taka by '.$paymentTypeName;

            if (! empty($firstPayment->tnx)) {
                $message .= ' ('.$firstPayment->tnx.')';
            }

            $message .= "\n";
        }
        // $message .= ' by '.$reservation->payments()->first()->
        $message .= 'Due: '.($reservation->total_rent - $totalAdvance)."Taka\n";

        $message .= 'Booked By: '.$reservation->user->name."\n";
        if ($reservation->reference != null) {
            $message .= 'Reference by: '.$reservation->reference."\n";
        }

        $message .= 'Note: '.\App\Models\HotelSetting::find(3)->description."\n";
        $message .= 'Our Cancellation Policy: '.\App\Models\HotelSetting::find(4)->description;

        // dd($message);
        $sms = new BDBulkSms($reservation->customer->phone, $message);
        if ($sms->send()) {
            $reservation->confrim_message_sent_at = Carbon::now();
            $reservation->save();
            Notification::make()
                ->title($type.'Confirmation Message sent successfully')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title($type.'Confirmation Message sent failed')
                ->error()
                ->send();
        }
    }
}

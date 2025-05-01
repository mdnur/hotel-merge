@php use App\Models\RoomType; @endphp
<x-filament-panels::page>
    @if ($this->hasInfolist())
    {{ $this->infolist }}

    @else
    {{-- {{ dd($this->data) }} --}}

    @endif
    {{-- {{ dd($this->data) }} --}}

    Confirmation Message <br>
    from Hotel Amin International.
    <br>
    Reservation No: {{ $this->data['reservation_no'] }}
    <br>
    Booking Date: {{ Carbon\Carbon::parse($this->data['booking_date'])->format('Y-m-d') }}
    <br>
    Name: {{ $this->data['customer']['name'] }}
    <br>
    Mobile Number: {{ $this->data['customer']['phone'] }}
    <br>
    Address: {{ $this->data['customer']['address'] }}
    <br>

    @php
    $sum = 0;
    $rent ="";
    @endphp
    Room type:
    @foreach ($this->data['Rooms'] as $room)
    @php
    $sum =$sum + $room['quantity'];
    echo RoomType::find($room['room_type_id'])->name
    @endphp
    ({{ $room['quantity'] }})

    {{-- {{ \App\Models\RoomType::find($room['room_type_id'])->first()->name }}({{ $room['quantity'] }}) --}}
    @endforeach
    <br>
    Total room: {{ $sum }}
    <br>
    Check-in: {{ Carbon\Carbon::parse($this->data['check_in_date'])->format('Y-m-d') }}
    ({{ \App\Models\HotelSetting::find(1)->description }})
    <br>
    Check-out: {{ Carbon\Carbon::parse($this->data['check_out_date'])->format('Y-m-d') }}
    ({{ \App\Models\HotelSetting::find(2)->description }})
    <br>
    @foreach ($this->data['Rooms'] as $room)
    @php
    $rent .= $room['rent']." / ";
    @endphp
    @endforeach

    Room price: {{ rtrim($rent, ' / ') }} Taka
    <br>
    Total price: {{ $this->data['total_rent'] }} Taka
    <br>
    {{-- {{ collect($data['payments'])->sum('advance') }}Taka --}}
    @if(collect($data['payments'])->count() > 1)

    @php
    $index =0;
    @endphp
    @foreach ($data['payments'] as $payment)
    Advance {{ ++$index }}: {{ $payment['advance'] }} by {{ \App\Models\PaymentType::findOrFail($payment['payment_type_id'])->first()->name }} ({{ $payment['Last3Digit'] }})
    <br>
    @endforeach
    @endif

    {{-- @foreach ($data['payments'] as $payment)
    TXN({{ \App\Models\PaymentType::findOrFail($payment['payment_type_id'])->first()->name }}):{{ $payment['Last3Digit'] }}
    @endforeach
    <br> --}}
    @if(collect($data['payments'])->count() > 1)
    Total Advance : {{ collect($data['payments'])->sum('advance') }} Taka

    @else
    Total Advance : {{ collect($data['payments'])->sum('advance') }} Taka By

    {{ \App\Models\PaymentType::findOrFail(collect($data['payments'])->first()['payment_type_id'])->first()->name }} ({{ collect($data['payments'])->first()['Last3Digit'] }})
    @endif
    <br>
    Due: {{ $this->data['total_rent'] - collect($data['payments'])->sum('advance') }} Taka
    <br>
    {{-- ({{ \App\Models\PaymentType::find($this->data['payments']['payment_type_id'])->name }}) Last 3 Digits: {{
    $this->data['payments']['Last3Digit'] }} --}}
    Booked By: {{ \App\Models\User::find($this->data['user_id'])->name }}
    @if ($this->data['reference'] != null)
    <br>
    Reference: {{ $this->data['reference'] }}
    <br>
    @else
    <br>
    @endif

    Note: {{ \App\Models\HotelSetting::find(3)->description }}
    <br>Our Cancellation Policy: {{ \App\Models\HotelSetting::find(4)->description }}
    @if ($this->data['confrim_message_sent_at'] == null)
    <x-filament::button wire:click="openNewUserModal('')">
        Send this Confirmation
    </x-filament::button>
    @else
    <x-filament::button wire:click="openNewUserModal('Re-')" color="danger">
        Resend this Confirmation
    </x-filament::button>
    @endif

</x-filament-panels::page>

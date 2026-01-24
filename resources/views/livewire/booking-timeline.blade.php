<div class="overflow-x-auto text-sm">
    <table class="table-auto border-collapse w-full min-w-[1500px]">
        <thead>
            <tr>
                <th class="p-2 border">Number</th>
                <th class="p-2 border">Type</th>
                <th class="p-2 border">Status</th>
                @foreach ($days as $day)
                <th class="w-10 p-1 text-center border">
                    {{ \Carbon\Carbon::parse($day)->format('d') }}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rooms as $room)
            <tr>
                <td class="p-2 border">{{ $room['number'] }}</td>
                <td class="p-2 border">{{ $room['type'] }}</td>
                <td class="p-2 border">
                    <span class="inline-block w-2 h-2 rounded-full mr-1
                            @if($room['status'] == 'Ready') bg-green-500
                            @elseif($room['status'] == 'Clean up') bg-yellow-500
                            @elseif($room['status'] == 'Dirty') bg-red-500
                            @endif">
                    </span>
                    {{ $room['status'] }}
                </td>

                @foreach ($days as $day)
                @php
                $booking = collect($bookings)->first(function ($b) use ($room, $day) {
                return $b['room'] == $room['number'] && $day >= $b['start'] && $day <= $b['end']; }); @endphp <td class="border text-center p-1
                            @if($booking)
                                @if($booking['status'] == 'New') bg-orange-300
                                @elseif($booking['status'] == 'Confirmed') bg-green-300
                                @elseif($booking['status'] == 'Arrived') bg-blue-300
                                @endif
                            @endif">
                    @if ($booking && $day == $booking['start'])
                    <div class="text-xs font-semibold">
                        {{ $booking['code'] }}<br>
                        {{ $booking['status'] }}<br>
                        @if($booking['paid']) <span class="text-green-700">paid</span> @endif
                    </div>
                    @endif
                    </td>
                    @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

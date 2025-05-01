<div class="overflow-auto border" x-data>
    <table class="text-sm border-collapse table-fixed min-w-max">
        <thead>
            <tr class="sticky top-0 z-10 bg-gray-100">
                <th class="w-32 p-2 border">Date</th>
                @foreach($rooms as $room)
                <th class="w-20 p-2 text-center border">{{ $room['number'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($dates as $date)
            <tr>
                <td class="p-2 font-medium border bg-blue-50">{{ $date }}</td>
                @foreach($rooms as $room)
                @php
                $status = $this->getCellStatus($room['number'], $date);
                $bgColor = match($status) {
                'occupied' => 'bg-red-500 text-white',
                'checkout' => 'bg-yellow-400',
                'reservation' => 'bg-green-500 text-white',
                'out-of-order' => 'bg-blue-600 text-white',
                default => 'bg-white',
                };
                @endphp
                <td class="border text-center {{ $bgColor }}">{{ $status ? strtoupper(substr($status, 0, 3)) : '' }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

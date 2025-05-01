@php
    $items = (new App\Http\Controllers\DailyCollectionCalculator($record))->calculate();
@endphp


<div class="overflow-hidden bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="min-w-full text-center divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">Card No.</th>
                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">Room No.</th>
                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">Room Rent</th>
                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">Cash</th>
                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">Advance Adjust</th>
                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">Due Collection</th>
                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">Due</th>
                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">Advance</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if ($items != null)
                    @foreach ($items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900 whitespace-wrap">
                                {{ $item['specific_date']->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 whitespace-wrap">
                                {{ $item['card_no'] }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-900 max-w-s">
                                <div class="flex flex-wrap gap-1">
                                    @foreach (explode(',', $item['room_no']) as $room)
                                        <span class="inline-block px-2 py-1 text-xs bg-gray-100 rounded">
                                            {{ trim($room) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 whitespace-wrap">
                                {{ $item['daily_rent'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 whitespace-wrap">
                                {{ $item['cash_collected'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 whitespace-wrap">
                                {{ $item['adv_adjust'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 whitespace-wrap">
                                {{ $item['due_collection'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 whitespace-wrap">
                                {{ $item['due'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 whitespace-wrap">
                                {{ $item['advance'] }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="px-4 py-4 text-sm text-center text-gray-500">
                            No records found
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

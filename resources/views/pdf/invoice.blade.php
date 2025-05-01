<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Daily Sales Sheet</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-6 bg-gray-100">
    <div class="max-w-4xl p-6 mx-auto bg-white rounded-lg ">

        <div class="p-4 bg-white rounded-lg shadow">
            <!-- Header Section -->
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-200">
                <img src="{{ asset('img/logo.png') }}" width="120px" height="120px" alt="Hotel Logo" class="h-20">
                <div class="text-center">
                    <h1 class="text-xl font-bold text-gray-800">Hotel Amin International</h1>
                    <p class="text-gray-600">Daily Sales Sheet</p>
                </div>
                <p class="font-semibold text-gray-700">Date: {{ Carbon\Carbon::parse($date)->format('d-m-Y') }}</p>
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto text-center border border-gray-200 rounded-lg">
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-3 font-medium text-gray-700 border border-gray-300">Sl No.</th>
                            <th class="p-3 font-medium text-gray-700 border border-gray-300">Card No.</th>
                            <th class="p-3 font-medium text-gray-700 border border-gray-300">Room No.</th>
                            <th class="p-3 font-medium text-gray-700 border border-gray-300">Room Rent</th>
                            <th class="p-3 font-medium text-gray-700 border border-gray-300">Cash</th>
                            <th class="p-3 font-medium text-gray-700 border border-gray-300">Advance Adjust</th>
                            <th class="p-3 font-medium text-gray-700 border border-gray-300">Due Collection</th>
                            <th class="p-3 font-medium text-gray-700 border border-gray-300">Due</th>
                            <th class="p-3 font-medium text-gray-700 border border-gray-300">Advance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sl = 1;
                            $totalDailyRent = 0;
                            $totalCashCollected = 0;
                            $totalAdvAdjust = 0;
                            $totalDueCollection = 0;
                            $totalDue = 0;
                            $totalAdvance = 0;
                            $totalTotalDue = 0;
                            $totalRoom = 0;
                        @endphp

                        @forelse ($data as $item)
                            @php
                                $totalDailyRent += $item['daily_rent'];
                                $totalCashCollected += $item['cash_collected'];
                                $totalAdvAdjust += $item['adv_adjust'];
                                $totalDueCollection += $item['due_collection'];
                                $totalDue += $item['due'];
                                $totalAdvance += $item['advance'];
                                $totalTotalDue += $item['total_due'];
                                $totalRoom += count(explode(',', $item['room_no']));
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="p-3 border border-gray-300">{{ $sl++ }}</td>
                                <td class="p-3 border border-gray-300">{{ $item['card_no'] }}</td>
                                <td class="p-3 border border-gray-300">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach (explode(',', $item['room_no']) as $room)
                                            <span class="px-2 py-1 text-xs bg-gray-100 rounded">
                                                {{ trim($room) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="p-3 border border-gray-300">{{ $item['daily_rent'] }}</td>
                                <td class="p-3 border border-gray-300">{{ $item['cash_collected'] }}</td>
                                <td class="p-3 border border-gray-300">{{ $item['adv_adjust'] }}</td>
                                <td class="p-3 border border-gray-300">{{ $item['due_collection'] }}</td>
                                <td class="p-3 border border-gray-300">{{ $item['due'] }}</td>
                                <td class="p-3 border border-gray-300">{{ $item['advance'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-3 text-center text-gray-500 border border-gray-300">
                                    No data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold bg-gray-100">
                            <td class="p-3 border border-gray-300">Total</td>
                            <td class="p-3 border border-gray-300">{{ $sl - 1 }}</td>
                            <td class="p-3 border border-gray-300">{{ $totalRoom }}</td>
                            <td class="p-3 border border-gray-300">{{ $totalDailyRent }}</td>
                            <td class="p-3 border border-gray-300">{{ $totalCashCollected }}</td>
                            <td class="p-3 border border-gray-300">{{ $totalAdvAdjust }}</td>
                            <td class="p-3 border border-gray-300">{{ $totalDueCollection }}</td>
                            <td class="p-3 border border-gray-300">{{ $totalDue }}</td>
                            <td class="p-3 border border-gray-300">{{ $totalAdvance }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @pageBreak
        <div>
            {{-- <div class="flex items-center justify-between pb-4 border-b">
                <img src="{{ asset('img/logo.png') }}" width="120px" height="120px" alt="Hotel Logo" class="">
                <div>
                    <h1 class="text-xl font-bold">Hotel Amin International</h1>
                    <p class="text-center text-gray-600">Daily Sales Sheet</p>
                </div>
                <p class="font-semibold">Date: 17/02/25</p>
            </div> --}}
            <div class="grid grid-cols-1 gap-4 mt-5 md:grid-cols-2">
                <x-filament::card>
                    <h2 class="text-lg font-semibold">Debit/Credit</h2>
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 text-left">Description</th>
                                <th class="px-4 py-2 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-4 py-2">Total Room Rent</td>
                                <td class="px-4 py-2 text-right">৳{{ $totalDailyRent }}</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2">Cash Collection</td>
                                <td class="px-4 py-2 text-right">{{ $totalCashCollected }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2">Dues</td>
                                <td class="px-4 py-2 text-right">{{ $totalDailyRent - $totalCashCollected }}</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2">Advance Adjust</td>
                                <td class="px-4 py-2 text-right">{{ $totalAdvAdjust }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2">Today's Total Dues</td>
                                <td class="px-4 py-2 text-right">
                                    {{ $totalDailyRent - $totalCashCollected - $totalAdvAdjust }}
                                </td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2">B/F</td>
                                <td class="px-4 py-2 text-right">৳{{ $totalsRentByMonth ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2">(+) Cash Collection</td>
                                <td class="px-4 py-2 text-right">{{ $totalCashCollected }}</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2">(+) Dues Collection</td>
                                <td class="px-4 py-2 text-right">{{ $totalDueCollection }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2">(+) Advance</td>
                                <td class="px-4 py-2 text-right">{{ $totalAdvance }}</td>
                            </tr>
                            <tr class="font-semibold bg-gray-100">
                                <td class="px-4 py-2">Total Balance Until Today</td>
                                <td class="px-4 py-2 text-right">
                                    {{ $totalsRentByMonth + $totalCashCollected + $totalDueCollection + $totalAdvance }}
                                </td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2">(-) Expenditure</td>
                                <td class="px-4 py-2 text-right">{{ $totalExpenditure }}</td>
                            </tr>
                            <tr class="font-semibold bg-gray-100">
                                <td class="px-4 py-2">Total Balance</td>
                                <td class="px-4 py-2 text-right">
                                    {{ $totalsRentByMonth + $totalCashCollected + $totalDueCollection + $totalAdvance - $totalExpenditure }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4 bg-white rounded-lg ">
                        <h2 class="text-lg font-semibold mt">Dues of Previous Date (P.D)</h2>
                        <table class="w-full mt-5">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-2 text-left">Date</th>
                                    <th class="px-4 py-2 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="px-4 py-2">
                                        {{ Carbon\Carbon::parse($date)->subDay()->format('d-m-Y') ??
                                            Carbon\Carbon::parse($date)->subDay()->format('d-m-Y') }}
                                    </td>
                                    <td class="px-4 py-2 text-right">৳{{ $dueBeforeToday }}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-4 py-2">
                                        {{ Carbon\Carbon::parse($date)->format('d-m-Y') ?? now()->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-2 text-right">৳{{ $totalDue }}</td>
                                </tr>
                                <tr class="font-semibold bg-gray-100">
                                    <td class="px-4 py-2">Total Dues of Until Todays</td>
                                    <td class="px-4 py-2 text-right">৳{{ $dueBeforeToday + $totalDue }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">(-) Dues Collection</td>
                                    <td class="px-4 py-2 text-right">৳{{ $totalDueCollection }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="font-semibold bg-gray-100">
                                    <td class="px-4 py-2">Total Dues</td>
                                    <td class="px-4 py-2 text-right">
                                        ৳{{ $dueBeforeToday + $totalDue - $totalDueCollection }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>


                </x-filament::card>

                <!-- Expenditures Section -->
                <x-filament::card>
                    <h2 class="text-lg font-semibold">Expenditures</h2>
                    <table class="w-full mt-4">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 text-left">#</th>
                                <th class="px-4 py-2 text-left">Description</th>
                                <th class="px-4 py-2 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expenditures as $expenditure)
                                <tr>
                                    <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-2">{{ $expenditure['expense_type_name'] }}</td>
                                    <td class="px-4 py-2 text-right">৳{{ $expenditure['total_amount'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-2" colspan="3"> No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="">
                            <tr class="font-semibold bg-gray-100">
                                <td class="px-4 py-2"></td>
                                <td class="px-4 py-2">Total Expenditure</td>
                                <td class="px-4 py-2 text-right">৳{{ $totalExpenditure ?? 0 }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </x-filament::card>
            </div>


        </div>

    </div>
</body>

</html>

{{-- @vite('resources/css/app.css') --}}

<div class="container p-4 mx-auto">

    {{-- <div class="max-w-sm p-4 mx-auto">
        <form wire:submit="save">
            <input type="date" wire:model="date" value="">
            <button type="submit">Save</button>
        </form>
        <h1>{{ $dateIn }}</h1>
    </div> --}}
    @php
        $totalDailyRent = 0;
        $totalCashCollected = 0;
        $totalAdvAdjust = 0;
        $totalDueCollection = 0;
        $totalDue = 0;
        $totalAdvance = 0;
        $totalTotalDue = 0;
        $totalRoom = 0;
    @endphp
    <!-- Table -->
    <div class="overflow-x-auto ">
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
                        {{ $totalDailyRent - $totalCashCollected - $totalAdvAdjust }}</td>
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


    </div>




    <div class="overflow-x-auto">
        <!-- Table -->
        <div class="pt-4 ">
            <div class="container p-4 mx-auto">
                <div class="grid grid-cols-4 gap-2">
                    <!-- Debit/Credit Section -->
                    <div class="col-span-2 p-4 bg-white rounded-lg shadow-md">
                        <h2 class="mb-4 text-lg font-semibold">Debit/Credit</h2>
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
                                    <td class="px-4 py-2 text-right">$162,810.00</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-4 py-2">Cash Collection</td>
                                    <td class="px-4 py-2 text-right">$108,500.00</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Dues</td>
                                    <td class="px-4 py-2 text-right">$54,310.00</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-4 py-2">Advance Adjust</td>
                                    <td class="px-4 py-2 text-right">$4,800.00</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">Dues</td>
                                    <td class="px-4 py-2 text-right">$49,510.00</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-4 py-2">B/F</td>
                                    <td class="px-4 py-2 text-right">$1,698,510.00</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">(+) Cash Collection</td>
                                    <td class="px-4 py-2 text-right">$108,500.00</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-4 py-2">(+) Dues Collection</td>
                                    <td class="px-4 py-2 text-right">$97,900.00</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">(+) Advance</td>
                                    <td class="px-4 py-2 text-right">$0.00</td>
                                </tr>
                                <tr class="font-semibold bg-gray-100">
                                    <td class="px-4 py-2">Total</td>
                                    <td class="px-4 py-2 text-right">$1,904,910.00</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2">(-) Expenditure</td>
                                    <td class="px-4 py-2 text-right">$99,470.00</td>
                                </tr>
                                <tr class="font-semibold bg-gray-100">
                                    <td class="px-4 py-2">Total Balance</td>
                                    <td class="px-4 py-2 text-right">$1,805,440.00</td>
                                </tr>
                            </tbody>
                        </table>


                        <!-- Dues of Previous Date Section -->
                        <div class="mt-4 bg-white rounded-lg shadow-md ">
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
                                        <td class="px-4 py-2">14/02/2025</td>
                                        <td class="px-4 py-2 text-right">$123,550.00</td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="px-4 py-2">15/02/2025</td>
                                        <td class="px-4 py-2 text-right">$49,510.00</td>
                                    </tr>
                                    <tr class="font-semibold bg-gray-100">
                                        <td class="px-4 py-2">Total Dues of P.D</td>
                                        <td class="px-4 py-2 text-right">$173,060.00</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-2">(-) Dues Collection</td>
                                        <td class="px-4 py-2 text-right">$97,900.00</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="font-semibold bg-gray-100">
                                        <td class="px-4 py-2">Total Dues</td>
                                        <td class="px-4 py-2 text-right">$75,160.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>

                    <!-- Expenditures Section -->
                    <div class="col-span-2 p-4 bg-white rounded-lg shadow-md">
                        <h2 class="mb-4 text-lg font-semibold">Expenditures</h2>
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
                                        <td class="px-4 py-2" colspan="3">No data available</td>
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
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>

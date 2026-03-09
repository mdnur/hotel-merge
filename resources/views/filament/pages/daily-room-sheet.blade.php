<x-filament::page>

    <div class="container p-4 mx-auto">
        <!-- Main Table -->

        {{ $this->form }}
        <x-filament::section class="mt-5">
            <x-slot name="heading">
                Daily Room Sheet

            </x-slot>
            <x-slot name="description">
                {{ Carbon\Carbon::parse($date)->format('d-m-Y') }}
            </x-slot>
            <x-filament::button wire:click="exportToExcel" color="info" class="mb-4">
                Export to Excel
            </x-filament::button>
            <div class="overflow-x-auto ">
                <table class="w-full text-center border border-gray-200 rounded-lg">
                    <thead>
                        <tr class=>
                            <th class="px-4 py-2 text-center border border-gray-300">Sl No.</th>
                            <th class="px-4 py-2 text-center border border-gray-300">Card No.</th>
                            <th class="px-4 py-2 text-center border border-gray-300">Room No.</th>
                            <th class="px-4 py-2 text-center border border-gray-300">Room Rent</th>
                            <th class="px-4 py-2 text-center border border-gray-300">Cash</th>
                            <th class="px-4 py-2 text-center border border-gray-300">Advance Adjust</th>
                            <th class="px-4 py-2 text-center border border-gray-300">Due Collection</th>
                            <th class="px-4 py-2 text-center border border-gray-300">Due</th>
                            <th class="px-4 py-2 text-center border border-gray-300">Advance</th>
                        </tr>
                    </thead>

                    {{-- {{ $this->data }} --}}
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

                        @forelse ($this->data as $item)
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

                        <tr class="border-b ">
                            <td class="px-4 py-2 text-center border border-gray-300"> {{ $sl++ }}</td>
                            <td class="px-4 py-2 text-center border border-gray-300">
                                <x-filament::button size="xs" color="info" icon="heroicon-o-eye" outlined wire:click="openViewModal('{{ $item['card_no'] }}')">
                                    {{ $item['card_no'] }}
                                </x-filament::button>
                            </td>
                            <td style="width: 12rem; padding: 0.5rem 1rem; line-height: 1.625; text-align: center; word-break: break-all; white-space: normal;">
                                {{ $item['room_no'] }}
                            </td>

                            <td class="px-4 py-2 text-center border border-gray-300"> {{ $item['daily_rent'] }}
                            </td>
                            <td class="px-4 py-2 text-center border border-gray-300">
                                {{ $item['cash_collected'] }}
                            </td>
                            <td class="px-4 py-2 text-center border border-gray-300"> {{ $item['adv_adjust'] }}
                            </td>
                            <td class="px-4 py-2 text-center border border-gray-300">
                                {{ $item['due_collection'] }}
                            </td>
                            <td class="px-4 py-2 text-center border border-gray-300"> {{ $item['due'] }}</td>
                            <td class="px-4 py-2 text-center border border-gray-300"> {{ $item['advance'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-4 py-2 text-center border border-gray-300">No data
                                available</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="font-bold ">
                        <tr>
                            <td class="px-4 py-2 border border-gray-300">Total</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $sl - 1 }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $totalRoom }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $totalDailyRent }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $totalCashCollected }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $totalAdvAdjust }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $totalDueCollection }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $totalDue }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $totalAdvance }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </x-filament::section>




        <div class="grid grid-cols-1 gap-4 mt-5 md:grid-cols-2">
            <!-- Debit/Credit Section -->
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

                <div class="mt-4 rounded-lg shadow-md ">
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
                                    ৳{{ $dueBeforeToday + $totalDue - $totalDueCollection }}</td>
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
            </x-filament::card>
        </div>
        <!-- View Modal -->
        <x-filament::modal id="view-modal" width="5xl">
            <x-slot name="heading">
                <div class="flex items-center">
                    <x-heroicon-o-document-text class="w-5 h-5 mr-2 text-primary-500" />
                    Transaction Details for Card: {{ $selectedCardNo ?? '' }}
                </div>
            </x-slot>

            @if ($this->card)
            <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                    Date</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                    Card No.</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                    Room No.</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                    Room Rent</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                    Cash</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                    Advance Adjust</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                    Due Collection</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                    Due</th>
                                <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                    Advance</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach ($this->card as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap dark:text-gray-200">
                                    {{ $item['specific_date']->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap dark:text-gray-200">
                                    {{ $item['card_no'] }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach (explode(',', $item['room_no']) as $room)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ trim($room) }}
                                        </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 whitespace-nowrap dark:text-gray-200">
                                    ৳{{ number_format($item['daily_rent'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 whitespace-nowrap dark:text-gray-200">
                                    ৳{{ number_format($item['cash_collected'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 whitespace-nowrap dark:text-gray-200">
                                    ৳{{ number_format($item['adv_adjust'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 whitespace-nowrap dark:text-gray-200">
                                    ৳{{ number_format($item['due_collection'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 whitespace-nowrap dark:text-gray-200">
                                    ৳{{ number_format($item['due'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 whitespace-nowrap dark:text-gray-200">
                                    ৳{{ number_format($item['advance'], 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                No transaction details found
            </div>
            @endif

            <x-slot name="footer">
                <div class="flex justify-end">
                    <x-filament::button color="danger" x-on:click="$dispatch('close-modal', { id: 'view-modal' })">
                        Close
                    </x-filament::button>
                </div>
            </x-slot>
        </x-filament::modal>



    </div>
</x-filament::page>

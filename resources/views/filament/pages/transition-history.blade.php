<x-filament::page>
    {{ $this->form }}

    @php
    $total = 0;
    $totalCash = 0;
    $totalBkash =0;
    $totalNagad = 0;
    $totalCityBank =0;
    $totalDBBL =0;
    $totalEBL =0;
    $totalUCB = 0;
    $totalIslamicBank = 0;
    @endphp
    <x-filament::section heading="Card Payments">
        {{-- @foreach ($cardPayments as $payment) --}}
        <x-filament::button color="success" icon="heroicon-o-arrow-down-tray" wire:click="exportExcel">
            Export Excel
        </x-filament::button>

        <div class="py-1 border-b">
            @if($paymentTypeId)
            <x-filament::badge color="info">
                Filtering: {{ $paymentTypes->firstWhere('id', $paymentTypeId)?->name }}
            </x-filament::badge>
            @endif
            <table class="w-full text-center border border-gray-200 rounded-lg">
                @if(!empty($paymentTypeIds))
                <x-filament::badge color="info">
                    {{ implode(', ', $paymentTypes->whereIn('id', $paymentTypeIds)->pluck('name')->toArray()) }}
                </x-filament::badge>
                @endif

                <thead>
                    <tr class="font-bold ">
                        <th class="px-4 py-2 text-center border border-gray-300">Received Time</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Card no.</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Action</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Room No</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Reservation NO</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Payment Type</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Tnx</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Amount</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Received By</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($this->cardPayments as $cardPayment)

                    <tr>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $cardPayment->created_at }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300">
                            <x-filament::button size="xs" color="info" outlined icon="heroicon-o-eye" wire:click="openViewModal('{{ $cardPayment->paymentable->card_no }}')">
                                {{ $cardPayment->paymentable->card_no }}
                            </x-filament::button>
                        </td>
                        <td class="px-4 py-2 text-center border border-gray-300">
                            <x-filament::button color="info" size="xs" wire:click="openPaymentHistoryModal('{{ $cardPayment->paymentable->card_no }}')">
                                Payment History
                            </x-filament::button>
                        </td>
                        <td class="px-4 py-2 text-center border border-gray-300">
                            @forelse ($cardPayment->paymentable->cardRooms as $cardRoom)
                            <span class="inline-flex items-center px-2 py-0.5 m-0.5 text-xs font-semibold text-blue-700 bg-blue-100 rounded">
                                {{ $cardRoom->room->room_no }}
                            </span>
                            @empty
                            <span class="text-gray-400">—</span>
                            @endforelse
                        </td>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $cardPayment->paymentable->reservation_id  }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $cardPayment->paymentType->name }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $cardPayment->tnx ?? '' }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $cardPayment->amount }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $cardPayment->user->name }}</td>
                    </tr>
                    @php
                    $total += $cardPayment->amount;
                    @endphp

                    @if($cardPayment->payment_type_id == 1)

                    @php
                    $totalCash += $cardPayment->amount;
                    @endphp
                    @endif

                    @if($cardPayment->payment_type_id == 2)


                    @php
                    $totalBkash += $cardPayment->amount;
                    @endphp
                    @endif


                    @if($cardPayment->payment_type_id == 3)

                    @php
                    $totalNagad += $cardPayment->amount;
                    @endphp
                    @endif

                    @if($cardPayment->payment_type_id == 5)


                    @php
                    $totalCityBank += $cardPayment->amount;
                    @endphp
                    @endif


                    @if($cardPayment->payment_type_id == 6)

                    @php
                    $totalDBBL += $cardPayment->amount;
                    @endphp
                    @endif


                    @if($cardPayment->payment_type_id == 8)


                    @php
                    $totalEBL += $cardPayment->amount;
                    @endphp
                    @endif



                    @if($cardPayment->payment_type_id == 9)
                    @php
                    $totalUCB += $cardPayment->amount;
                    @endphp
                    @endif

                    @if($cardPayment->payment_type_id == 10)
                    @php
                    $totalIslamicBank += $cardPayment->amount;
                    @endphp
                    @endif
                    @endforeach

                </tbody>

                <tfoot class="font-bold ">
                    <tr>
                        <td class="px-4 py-2 text-center border border-gray-300">Received Time</td>
                        <td class="px-4 py-2 text-center border border-gray-300">Card no.</td>
                        <td class="px-4 py-2 text-center border border-gray-300">Reservation NO</td>
                        <td class="px-4 py-2 text-center border border-gray-300">Payment Type</td>
                        <td class="px-4 py-2 text-center border border-gray-300">Tnx</td>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $total }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300">Received By</td>


                    </tr>
                </tfoot>
            </table>
        </div>
        {{-- @endforeach --}}
    </x-filament::section>



    <x-filament::section heading="Expenses">
        @php
        $totalExpense =0;
        @endphp
        {{-- @foreach ($expenses as $expense) --}}
        <div class="py-1 border-b">
            <table class="w-full text-center border border-gray-200 rounded-lg">
                <thead>
                    <tr class="font-bold ">
                        <th class="px-4 py-2 text-center border border-gray-300">Expense Time</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Expense Date</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Expense Type</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Amount</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Description</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($this->expenses as $expense)
                    <tr>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $expense->created_at }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $expense->expense_date }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $expense->expenseType->name }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $expense->amount }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $expense->description }}</td>
                    </tr>
                    @php
                    $totalExpense +=$expense->amount;
                    @endphp
                    @endforeach

                </tbody>

                <tfoot class="font-bold ">
                    <tr>
                        <td class="px-4 py-2 text-center border border-gray-300">Expense Time</td>
                        <td class="px-4 py-2 text-center border border-gray-300">Expense Date</td>
                        <td class="px-4 py-2 text-center border border-gray-300">Payment Type</td>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $totalExpense }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300">Description</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        {{-- @endforeach --}}
    </x-filament::section>



    <x-filament::section heading="Total Payment By Type">
        {{-- @foreach ($cardPayments as $payment) --}}

        <div class="py-1 border-b">
            <table class="w-full text-center border border-gray-200 rounded-lg">

                <thead>
                    <tr class="font-bold ">
                        <th class="px-4 py-2 text-center border border-gray-300">Cash</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Bkash</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Nagad</th>
                        <th class="px-4 py-2 text-center border border-gray-300">CityBank</th>
                        <th class="px-4 py-2 text-center border border-gray-300">DBBL</th>
                        <th class="px-4 py-2 text-center border border-gray-300">EBL</th>
                        <th class="px-4 py-2 text-center border border-gray-300">UCB</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Islami Bank</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Total Collection</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Expense</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Total Cash after Expense</th>
                        <th class="px-4 py-2 text-center border border-gray-300">Remaining Balance</th>

                    </tr>
                </thead>
                <tbody class="font-bold ">
                    <tr>
                        <td class="px-4 py-2 text-center border border-gray-300"> {{ $totalCash  }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300"> {{ $totalBkash  }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300"> {{ $totalNagad  }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300"> {{ $totalCityBank  }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300"> {{ $totalDBBL  }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300"> {{ $totalEBL  }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300"> {{ $totalUCB  }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300"> {{ $totalIslamicBank  }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300"> {{ $total  }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300"> {{ $totalExpense  }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300"> {{ $totalCash - $totalExpense  }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300"> {{ $total - $totalExpense  }}</td>
                    </tr>
                </tbody>


            </table>
        </div>
        {{-- @endforeach --}}
    </x-filament::section>


    <x-filament::modal id="payment-history-modal">
        <x-slot name="title">
            Payment History for Card No: {{ $card['card_no'] ?? '' }}
        </x-slot>

        <div class="space-y-4">
            @if (!empty($payments))

            <ul class="space-y-2">
                @foreach ($payments as $payment)
                <li>
                    <div class="flex justify-between">
                        <span>{{ $payment->created_at->format('d M Y h:i:s') }}</span>
                        <span>৳ {{ number_format($payment->amount, 2) }}</span>
                        <span>{{ $payment->paymentType->name }}</span>
                        <span>{{ $payment->tnx }}</span>
                    </div>
                </li>
                @endforeach
            </ul>

            <div class="pt-2 mt-4 font-bold text-right border-t">
                Total Paid: ৳ {{ $payments->sum('amount') }}
            </div>
            @else
            <div class="text-center text-gray-500">No paymentTypes yet.</div>
            @endif
        </div>

        <x-slot name="footer">
            <x-filament::button wire:click="$dispatch('close-modal', { id: 'payment-history-modal' })">
                Close
            </x-filament::button>
        </x-slot>
    </x-filament::modal>


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
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
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


</x-filament::page>

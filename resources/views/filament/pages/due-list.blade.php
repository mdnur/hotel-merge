<x-filament::page>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col justify-between gap-4 p-4 rounded-lg shadow md:flex-row md:items-center bg-white dark:bg-gray-800">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                    Due List &mdash; {{ Carbon\Carbon::parse($this->date)->format('d/m/Y') }}
                </h2>
                @if(Carbon\Carbon::parse($this->date)->isToday())
                <p class="text-sm text-gray-500 dark:text-gray-400">As of {{ now()->format('h:i A') }}</p>
                @else
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    As of {{ Carbon\Carbon::parse($this->date)->addDay()->setHour(11)->format('d/m/Y h:i A') }}
                </p>
                @endif
            </div>

            <div class="flex flex-wrap gap-2 items-center">
                <x-filament::input.wrapper>
                    <x-filament::input type="date" wire:model.live="date" max="{{ now()->format('Y-m-d') }}" />
                </x-filament::input.wrapper>

                <x-filament::button wire:click="refreshData" color="primary">
                    Refresh
                </x-filament::button>

                <x-filament::button wire:click="exportToExcel" color="success" icon="heroicon-o-arrow-down-tray">
                    Export Excel
                </x-filament::button>
            </div>
        </div>

        {{-- Pending Dues --}}
        <x-filament::section>
            <x-slot name="heading">
                Pending Dues
                <span class="ml-2 text-sm font-normal text-red-500">
                    ({{ $this->items?->where('total_due', '>', 0)->count() ?? 0 }} records)
                </span>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Card No</th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Room No</th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">Today's Due</th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">Total Due</th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse ($this->items?->where('total_due', '>', 0) ?? [] as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-200">
                                <a href="{{ $this->getCardEditUrl($item['card_no']) }}" class="text-primary-600 hover:underline">
                                    {{ $item['card_no'] }}
                                </a>
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
                            <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">
                                ৳{{ number_format($item['due'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-right text-red-600 dark:text-red-400">
                                ৳{{ number_format($item['total_due'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center">
                                <div class="flex justify-center gap-2 flex-wrap">
                                    <x-filament::button size="xs" color="info" icon="heroicon-o-eye" wire:click="openViewModal('{{ $item['card_no'] }}')">View</x-filament::button>
                                    <x-filament::button size="xs" color="success" icon="heroicon-o-banknotes" wire:click="openPaymentModal('{{ $item['card_no'] }}', '{{ $item['total_due'] }}')">Pay</x-filament::button>
                                    <x-filament::button size="xs" color="gray" wire:click="openPaymentHistoryModal('{{ $item['card_no'] }}')">History</x-filament::button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No pending dues found</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($this->items?->where('total_due', '>', 0)->count())
                    <tfoot class="bg-gray-50 dark:bg-gray-700 font-semibold">
                        <tr>
                            <td class="px-4 py-3">Total</td>
                            <td class="px-4 py-3">
                                {{ $this->items->where('total_due', '>', 0)->sum(fn($i) => count(explode(',', $i['room_no']))) }}
                            </td>
                            <td class="px-4 py-3 text-right">৳{{ number_format($this->items->where('total_due', '>', 0)->sum('due'), 2) }}</td>
                            <td class="px-4 py-3 text-right text-red-600">৳{{ number_format($this->items->where('total_due', '>', 0)->sum('total_due'), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </x-filament::section>

        {{-- Cleared Dues --}}
        @php
        $cleared = $this->items?->filter(fn($i) => $i['due_collection'] > 0 || ($i['cash_collected'] > 0 && $i['total_due'] <= 0)) ?? collect(); @endphp <x-filament::section>
            <x-slot name="heading">
                Cleared Today
                <span class="ml-2 text-sm font-normal text-green-500">({{ $cleared->count() }} records)</span>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Card No</th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Room No</th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">Due Collection</th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">Cash Collection</th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">Total</th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse ($cleared as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-200">
                                <a href="{{ $this->getCardEditUrl($item['card_no']) }}" class="text-primary-600 hover:underline">
                                    {{ $item['card_no'] }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex flex-wrap gap-1">
                                    @foreach (explode(',', $item['room_no']) as $room)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ trim($room) }}
                                    </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-semibold">৳{{ number_format($item['due_collection'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold">৳{{ number_format($item['cash_collected'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-green-600 dark:text-green-400">
                                ৳{{ number_format($item['due_collection'] + $item['cash_collected'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center">
                                <div class="flex justify-center gap-2">
                                    <x-filament::button size="xs" color="info" icon="heroicon-o-eye" wire:click="openViewModal('{{ $item['card_no'] }}')">View</x-filament::button>
                                    <x-filament::button size="xs" color="gray" wire:click="openPaymentHistoryModal('{{ $item['card_no'] }}')">History</x-filament::button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No cleared dues found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </x-filament::section>
    </div>

    {{-- Payment Modal --}}
    <x-filament::modal id="receive-payment-modal">
        <x-slot name="heading">
            Receive Payment &mdash; Card: {{ $this->selectedCardNo ?? '' }}
        </x-slot>
        <div class="space-y-4">
            <x-filament::input.wrapper label="Amount">
                <x-filament::input type="number" wire:model="amount" placeholder="Enter Amount" />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Payment Type">
                <x-filament::input.select wire:model="payment_type_id">
                    <option value="">Select Payment Type</option>
                    @foreach ($this->paymentTypes ?? [] as $pt)
                    <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
            <x-filament::input.wrapper label="Transaction No">
                <x-filament::input type="text" wire:model="tnx" placeholder="Optional" />
            </x-filament::input.wrapper>
        </div>
        <x-slot name="footer">
            <div class="flex justify-end gap-3">
                <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'receive-payment-modal' })">Cancel</x-filament::button>
                <x-filament::button color="primary" wire:click="savePayment" wire:loading.attr="disabled">
                    <span wire:loading.remove>Confirm Payment</span>
                    <span wire:loading>Processing…</span>
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- View Transaction Modal --}}
    <x-filament::modal id="view-modal" width="5xl">
        <x-slot name="heading">
            Transaction Details &mdash; Card: {{ $this->selectedCardNo ?? '' }}
        </x-slot>
        @if ($this->card)
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        @foreach(['Date','Card No.','Room No.','Room Rent','Cash','Adv Adjust','Due Collect','Due','Advance'] as $h)
                        <th class="px-4 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @foreach ($this->card as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $item['specific_date']->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $item['card_no'] }}</td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex flex-wrap gap-1">
                                @foreach (explode(',', $item['room_no']) as $room)
                                <span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ trim($room) }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">৳{{ number_format($item['daily_rent'], 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right">৳{{ number_format($item['cash_collected'], 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right">৳{{ number_format($item['adv_adjust'], 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right">৳{{ number_format($item['due_collection'], 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right">৳{{ number_format($item['due'], 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right">৳{{ number_format($item['advance'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="p-4 text-center text-gray-500">No transaction details found.</p>
        @endif
        <x-slot name="footer">
            <x-filament::button color="danger" x-on:click="$dispatch('close-modal', { id: 'view-modal' })">Close</x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- Payment History Modal --}}
    <x-filament::modal id="payment-history-modal">
        <x-slot name="heading">
            Payment History &mdash; Card: {{ $this->selectedCardNo ?? '' }}
        </x-slot>
        @if (!empty($this->payments) && $this->payments->count())
        <ul class="space-y-2">
            @foreach ($this->payments as $payment)
            <li class="flex justify-between text-sm border-b pb-2">
                <span>{{ $payment->created_at->format('d M Y h:i A') }}</span>
                <span class="font-semibold">৳{{ number_format($payment->amount, 2) }}</span>
                <span>{{ $payment->paymentType->name }}</span>
                <span class="text-gray-500">{{ $payment->tnx }}</span>
            </li>
            @endforeach
        </ul>
        <div class="mt-4 pt-2 font-bold text-right border-t">
            Total Paid: ৳{{ number_format($this->payments->sum('amount'), 2) }}
        </div>
        @else
        <p class="text-center text-gray-500">No payments yet.</p>
        @endif
        <x-slot name="footer">
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'payment-history-modal' })">Close</x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament::page>

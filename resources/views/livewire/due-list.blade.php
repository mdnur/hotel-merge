<div class="p-4 space-y-6 bg-white rounded-lg shadow dark:bg-gray-800">
    <!-- Header Section -->
    <div class="flex flex-col justify-between gap-4 p-4 mb-4 rounded-lg shadow md:flex-row md:items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Due List - {{ Carbon\Carbon::parse($this->date)->format('d/m/Y') ?? now()->format('d/m/Y') }}
            </h2>

            {{-- {{ $this->date }} --}}
            @if (Carbon\Carbon::parse($this->date)->format('d/m/Y') == now()->format('d/m/Y'))
            <p class="text-sm text-gray-500 dark:text-gray-400">As of {{ now()->format('h:i A') }}</p>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400">As of
                {{ Carbon\Carbon::parse($this->date)->addDay(1)->addHours(11)->format('d/m/Y h:i A') }}</p>
            @endif
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-2">
            <!-- Date Input -->
            <x-filament::input.wrapper class="w-full sm:w-auto">
                <x-filament::input type="date" wire:model.lazy="date" max="{{ now()->format('Y-m-d') }}" class="w-full" />
            </x-filament::input.wrapper>

            <!-- Search Button -->
            <x-filament::button wire:click="refreshData" class="w-full sm:w-auto">
                Refresh
            </x-filament::button>

        </div>
    </div>

    <!-- Due List Section -->
    <div class="space-y-6">
        <!-- Pending Dues Card -->
        <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
            <div class="p-4 border-b bg-red-50 dark:bg-gray-700">
                <h3 class="text-lg font-semibold text-red-800 dark:text-red-200">
                    Pending Dues
                    <span class="ml-2 text-sm font-normal text-red-600 dark:text-red-300">
                        ({{ $this->items->where('total_due', '>', 0)->count() }} records)
                    </span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                Card NO
                            </th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                Room No
                            </th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                Today Due
                            </th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase dark:text-gray-400">
                                Total Due
                            </th>

                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse ($this->items->where('total_due', '>', 0) as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-200">
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
                            <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">
                                ৳{{ number_format($item['due'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-right text-red-600 dark:text-red-400">
                                ৳{{ number_format($item['total_due'], 2) }}
                            </td>

                            <td class="px-4 py-3 text-sm text-center">
                                <div class="flex justify-center space-x-2">
                                    <x-filament::button size="xs" color="info" icon="heroicon-o-eye" wire:click="openViewModal('{{ $item['card_no'] }}')">
                                        View
                                    </x-filament::button>
                                    <x-filament::button size="xs" color="success" icon="heroicon-o-currency-dollar" wire:click="openPaymentModal('{{ $item['card_no'] }}')">
                                        Pay
                                    </x-filament::button>
                                    <x-filament::button color="info" size="xs" wire:click="openPaymentHistoryModal('{{ $item['card_no'] }}')">
                                        Payment History
                                    </x-filament::button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                No pending dues found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                        <tr class="font-semibold text-gray-900 dark:text-gray-200">
                            <td class="px-4 py-3 text-left">Total</td>
                            <td class="px-4 py-3">
                                {{ $this->items->where('total_due', '>', 0)->sum(function ($item) {return count(explode(',', $item['room_no']));}) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                ৳{{ number_format($this->items->where('total_due', '>', 0)->sum('due'), 2) }}</td>
                            <td class="px-4 py-3 text-right text-red-600 dark:text-red-400">
                                ৳{{ number_format($this->items->where('total_due', '>', 0)->sum('total_due'), 2) }}
                            </td>
                            <td class="px-4 py-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- No Dues Card -->
        <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
            <div class="p-4 border-b bg-green-50 dark:bg-gray-700">
                <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">
                    Cleared Dues
                    <span class="ml-2 text-sm font-normal text-green-600 dark:text-green-300">

                        ({{ $this->items ->filter(function ($item) {
                            return  $item['total_due'] <= 0;;
                            // return $item['due_collection'] > 0 or $item['cash_collected'] > 0 and $item['total_due'] <= 0;;
                        })->count() }} records)

                    </span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                Card NO
                            </th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                Room No
                            </th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                Due Collection
                            </th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                Cash Collection
                            </th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">
                                Total Collection
                            </th>
                            </th>
                            <th class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase dark:text-gray-400">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @php
                        $items = $this->items
                        ->filter(function ($item) {
                        return $item['due_collection'] > 0 or $item['cash_collected'] > 0 and $item['total_due'] <= 0;; }) @endphp @forelse ($items as $item) {{-- @if(($item['due_collection'] + $item['cash_collected']) ==0 )
                            @continue
                            @endif --}} <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-200">
                                <a href="{{ \App\Filament\Resources\CardResource::getUrl('edit', ['record' => \App\Models\Card::where('card_no', $item['card_no'])->first()?->id]) }}">
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
                            <td class="px-4 py-3 text-sm font-semibold text-right ">
                                ৳{{ number_format($item['due_collection'], 2) }}
                            </td>

                            <td class="px-4 py-3 text-sm font-semibold text-right ">
                                ৳{{ number_format($item['cash_collected'], 2) }}
                            </td>

                            <td class="px-4 py-3 text-sm font-semibold text-right text-green-400">
                                ৳{{ number_format($item['due_collection']+$item['cash_collected'], 2) }}
                            </td>

                            <td class="px-4 py-3 text-sm text-center">
                                <x-filament::button size="xs" color="info" icon="heroicon-o-eye" wire:click="openViewModal('{{ $item['card_no'] }}')">
                                    View
                                </x-filament::button>
                                <x-filament::button color="info" size="xs" icon="heroicon-o-eye" wire:click="openPaymentHistoryModal('{{ $item['card_no'] }}')">
                                    Payment History
                                </x-filament::button>
                            </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    No cleared dues found
                                </td>
                            </tr>
                            @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                        <tr class="font-semibold text-gray-900 dark:text-gray-200">
                            <td class="px-4 py-3 text-left">Total</td>
                            <td class="px-4 py-3">
                                {{-- {{ $this->items->where('total_due', '<=', 0)->sum(function ($item) {return count(explode(',', $item['room_no']));}) }} --}}
                                {{ $this->items
                                    ->filter(function ($item) {
                                        return $item['due_collection'] > 0 or $item['cash_collected'] > 0 and $item['total_due'] <= 0;;
                                    })
                                    ->sum(function ($item) {
                                        return count(explode(',', $item['room_no']));
                                    })
                                }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                ৳{{ number_format($this->items->where('total_due', '<=', 0)->sum('due_collection'), 2) }}</td>
                            <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">
                                ৳{{ number_format($this->items->where('total_due', '<=', 0)->sum('cash_collected'), 2) }}</td>
                            <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">
                                ৳{{ number_format($this->items->where('total_due', '<=', 0)->sum('due_collection')+$this->items->where('total_due', '<=', 0)->sum('cash_collected'), 2) }}</td>
                            <td class="px-4 py-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <x-filament::modal id="receive-payment-modal">
        <x-slot name="heading">
            <div class="flex items-center">
                <x-heroicon-o-currency-dollar class="w-5 h-5 mr-2 text-primary-500" />
                Receive Payment for Card: {{ $selectedCardNo ?? '' }}
            </div>
        </x-slot>

        <div class="space-y-4">
            <x-filament::input.wrapper label="Amount">
                <x-filament::input type="number" wire:model.defer="amount" placeholder="Enter Amount" required />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper label="Payment Type">
                <x-filament::input.select wire:model.defer="payment_type_id" required>
                    <option value="">Select Payment Type</option>
                    @foreach ($paymentTypes as $payment)
                    <option value="{{ $payment->id }}">{{ $payment->name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>

            <x-filament::input.wrapper label="Transaction Number">
                <x-filament::input type="text" wire:model.defer="transactions_no" placeholder="Enter Transaction No" />
            </x-filament::input.wrapper>
        </div>

        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <x-filament::button color="secondary" x-on:click="$dispatch('close-modal', { id: 'receive-payment-modal' })">
                    Cancel
                </x-filament::button>
                <x-filament::button color="primary" wire:click="savePayment" wire:loading.attr="disabled">
                    <span wire:loading.remove>Confirm Payment</span>
                    <span wire:loading>Processing...</span>
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

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



    <!-- Payment History Modal -->

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

</div>

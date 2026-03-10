<x-filament::page>


    {{-- HEADER BANNER --}}
    <div class="due-header-card rounded-2xl p-6 mb-6 shadow-2xl">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-500/30">
                        <x-filament::icon icon="heroicon-o-banknotes" class="w-5 h-5 text-indigo-400" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-white">Due List</h1>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">
                            {{ Carbon\Carbon::parse($this->date)->format('l, d F Y') }} &bull;
                            @if(Carbon\Carbon::parse($this->date)->isToday())
                            <span class="text-emerald-400">Live &bull; {{ now()->format('h:i A') }}</span>
                            @else
                            <span class="text-amber-400">Snapshot at {{ Carbon\Carbon::parse($this->date)->addDay()->setHour(11)->format('h:i A') }}</span>
                            @endif
                        </p>
                    </div>
                </div>
                @php
                $pendingCount = $this->items?->where('total_due', '>', 0)->count() ?? 0;
                $pendingTotal = $this->items?->where('total_due', '>', 0)->sum('total_due') ?? 0;
                $clearedItems = $this->items?->filter(fn($i) => $i['due_collection'] > 0 || ($i['cash_collected'] > 0 && $i['total_due'] <= 0)) ?? collect(); $clearedTotal=$clearedItems->sum(fn($i) => $i['due_collection'] + $i['cash_collected']);
                    @endphp
                    <div class="flex flex-wrap gap-2">
                        <span class="stat-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium text-red-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400 due-badge"></span>
                            {{ $pendingCount }} Pending &bull; <span class="amount-chip">৳{{ number_format($pendingTotal) }}</span>
                        </span>
                        <span class="stat-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium text-emerald-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            {{ $clearedItems->count() }} Cleared &bull; <span class="amount-chip">৳{{ number_format($clearedTotal) }}</span>
                        </span>
                    </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/5 border border-white/10">
                    <x-filament::icon icon="heroicon-o-calendar-days" class="w-4 h-4 text-slate-400" />
                    <input type="date" wire:model.live="date" max="{{ now()->format('Y-m-d') }}" class="bg-transparent text-sm text-white border-none outline-none font-mono cursor-pointer" />
                </div>
                <button wire:click="refreshData" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-500/20 border border-indigo-500/30 text-indigo-300 text-sm font-medium hover:bg-indigo-500/30 transition-all">
                    <x-filament::icon icon="heroicon-o-arrow-path" class="w-4 h-4" wire:loading.class="animate-spin" wire:target="refreshData" />
                    Refresh
                </button>
                <button wire:click="exportToExcel" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-sm font-medium hover:bg-emerald-500/30 transition-all">
                    <x-filament::icon icon="heroicon-o-arrow-down-tray" class="w-4 h-4" />
                    Export
                </button>
            </div>
        </div>
    </div>

    {{-- PENDING DUES --}}
    <div class="rounded-2xl border border-red-500/20 bg-white dark:bg-gray-900 shadow-sm overflow-hidden mb-6">
        <div class="flex items-center justify-between px-5 py-4 border-b border-red-100 dark:border-red-900/30 bg-red-50/50 dark:bg-red-950/20">
            <div class="flex items-center gap-2.5">
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-red-100 dark:bg-red-900/40">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-4 h-4 text-red-500" />
                </span>
                <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Pending Dues</span>
                @if($pendingCount)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-500 text-white">{{ $pendingCount }}</span>
                @endif
            </div>
            @if($pendingCount)
            <span class="text-xs font-mono font-semibold text-red-600 dark:text-red-400 amount-chip">Total: ৳{{ number_format($pendingTotal, 2) }}</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider w-24">Card</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Rooms</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Today's Due</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Due</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800/60">
                    @forelse ($this->items?->where('total_due', '>', 0) ?? [] as $item)
                    <tr class="tbl-row row-enter transition-colors">
                        <td class="px-5 py-3.5">
                            <a href="{{ $this->getCardEditUrl($item['card_no']) }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition-colors font-mono">
                                #{{ $item['card_no'] }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex flex-wrap gap-1">
                                @foreach (explode(',', $item['room_no']) as $room)
                                <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">{{ trim($room) }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-right amount-chip text-gray-600 dark:text-gray-400">৳{{ number_format($item['due'], 2) }}</td>
                        <td class="px-5 py-3.5 text-right"><span class="amount-chip font-bold text-red-600 dark:text-red-400 text-base">৳{{ number_format($item['total_due'], 2) }}</span></td>
                        <td class="px-5 py-3.5">
                            <div class="flex justify-center items-center gap-1.5 flex-wrap">
                                <button wire:click="openViewModal('{{ $item['card_no'] }}')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800 transition-colors">
                                    <x-filament::icon icon="heroicon-o-eye" class="w-3.5 h-3.5" /> View
                                </button>
                                <button wire:click="openPaymentModal('{{ $item['card_no'] }}', '{{ $item['total_due'] }}')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition-colors">
                                    <x-filament::icon icon="heroicon-o-banknotes" class="w-3.5 h-3.5" /> Pay
                                </button>
                                <button wire:click="openPaymentHistoryModal('{{ $item['card_no'] }}')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-gray-50 text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700 transition-colors">
                                    <x-filament::icon icon="heroicon-o-clock" class="w-3.5 h-3.5" /> History
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <x-filament::icon icon="heroicon-o-check-circle" class="w-10 h-10 text-emerald-400/60" />
                                <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">All clear — no pending dues</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($pendingCount)
                <tfoot>
                    <tr class="border-t-2 border-red-200 dark:border-red-900/50 bg-red-50/50 dark:bg-red-950/20">
                        <td class="px-5 py-3 text-xs font-bold text-gray-600 dark:text-gray-300 uppercase">Totals</td>
                        <td class="px-5 py-3 text-xs text-gray-500">{{ $this->items->where('total_due', '>', 0)->sum(fn($i) => count(explode(',', $i['room_no']))) }} rooms</td>
                        <td class="px-5 py-3 text-right font-semibold amount-chip text-gray-700 dark:text-gray-300">৳{{ number_format($this->items->where('total_due', '>', 0)->sum('due'), 2) }}</td>
                        <td class="px-5 py-3 text-right font-bold amount-chip text-red-600 dark:text-red-400 text-base">৳{{ number_format($pendingTotal, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- CLEARED TODAY --}}
    <div class="rounded-2xl border border-emerald-500/20 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-emerald-100 dark:border-emerald-900/30 bg-emerald-50/50 dark:bg-emerald-950/20">
            <div class="flex items-center gap-2.5">
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/40">
                    <x-filament::icon icon="heroicon-o-check-badge" class="w-4 h-4 text-emerald-500" />
                </span>
                <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Cleared Today</span>
                @if($clearedItems->count())
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-500 text-white">{{ $clearedItems->count() }}</span>
                @endif
            </div>
            @if($clearedItems->count())
            <span class="text-xs font-mono font-semibold text-emerald-600 dark:text-emerald-400 amount-chip">Collected: ৳{{ number_format($clearedTotal, 2) }}</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider w-24">Card</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Rooms</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Due Collected</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Cash Collected</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800/60">
                    @forelse ($clearedItems as $item)
                    <tr class="tbl-row row-enter transition-colors">
                        <td class="px-5 py-3.5">
                            <a href="{{ $this->getCardEditUrl($item['card_no']) }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition-colors font-mono">
                                #{{ $item['card_no'] }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex flex-wrap gap-1">
                                @foreach (explode(',', $item['room_no']) as $room)
                                <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">{{ trim($room) }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-right amount-chip font-semibold text-gray-700 dark:text-gray-300">৳{{ number_format($item['due_collection'], 2) }}</td>
                        <td class="px-5 py-3.5 text-right amount-chip font-semibold text-gray-700 dark:text-gray-300">৳{{ number_format($item['cash_collected'], 2) }}</td>
                        <td class="px-5 py-3.5 text-right"><span class="amount-chip font-bold text-emerald-600 dark:text-emerald-400 text-base">৳{{ number_format($item['due_collection'] + $item['cash_collected'], 2) }}</span></td>
                        <td class="px-5 py-3.5">
                            <div class="flex justify-center items-center gap-1.5">
                                <button wire:click="openViewModal('{{ $item['card_no'] }}')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800 transition-colors">
                                    <x-filament::icon icon="heroicon-o-eye" class="w-3.5 h-3.5" /> View
                                </button>
                                <button wire:click="openPaymentHistoryModal('{{ $item['card_no'] }}')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-gray-50 text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700 transition-colors">
                                    <x-filament::icon icon="heroicon-o-clock" class="w-3.5 h-3.5" /> History
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-gray-400">
                                <x-filament::icon icon="heroicon-o-inbox" class="w-10 h-10 opacity-30" />
                                <span class="text-sm">No cleared dues for this date</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAYMENT MODAL --}}
    <x-filament::modal id="receive-payment-modal" width="md">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/40">
                    <x-filament::icon icon="heroicon-o-banknotes" class="w-4 h-4 text-emerald-600" />
                </span>
                Receive Payment
                @if($this->selectedCardNo)
                <span class="text-sm font-mono font-normal text-gray-500">— Card #{{ $this->selectedCardNo }}</span>
                @endif
            </div>
        </x-slot>
        <div class="space-y-4 py-1">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Amount (৳)</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="number" wire:model="amount" placeholder="0.00" />
                </x-filament::input.wrapper>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Payment Type</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model="payment_type_id">
                        <option value="">Select type…</option>
                        @foreach ($this->paymentTypes ?? [] as $pt)
                        <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                    Transaction Ref <span class="normal-case font-normal text-gray-400">(optional)</span>
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model="tnx" placeholder="e.g. bKash/Nagad TXN ID" />
                </x-filament::input.wrapper>
            </div>
        </div>
        <x-slot name="footer">
            <div class="flex justify-end gap-2">
                <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'receive-payment-modal' })">Cancel</x-filament::button>
                <x-filament::button color="success" wire:click="savePayment" wire:loading.attr="disabled" icon="heroicon-o-check">
                    <span wire:loading.remove wire:target="savePayment">Confirm Payment</span>
                    <span wire:loading wire:target="savePayment">Processing…</span>
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- VIEW TRANSACTION MODAL --}}
    <x-filament::modal id="view-modal" width="6xl">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/40">
                    <x-filament::icon icon="heroicon-o-table-cells" class="w-4 h-4 text-blue-600" />
                </span>
                Transaction History
                @if($this->selectedCardNo)
                <span class="text-sm font-mono font-normal text-gray-500">— Card #{{ $this->selectedCardNo }}</span>
                @endif
            </div>
        </x-slot>
        @if ($this->card)
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        @foreach(['Date','Card','Rooms','Rent','Cash','Adv.Adj','Due Coll.','Due','Advance'] as $h)
                        <th class="px-3 py-2.5 text-left font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($this->card as $item)
                    <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-950/20 transition-colors">
                        <td class="px-3 py-2.5 font-mono text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $item['specific_date']->format('d/m/y') }}</td>
                        <td class="px-3 py-2.5 font-bold font-mono text-indigo-600 dark:text-indigo-400">#{{ $item['card_no'] }}</td>
                        <td class="px-3 py-2.5">
                            <div class="flex flex-wrap gap-0.5">
                                @foreach (explode(',', $item['room_no']) as $room)
                                <span class="px-1.5 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">{{ trim($room) }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-3 py-2.5 text-right font-mono font-medium">৳{{ number_format($item['daily_rent']) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono {{ $item['cash_collected'] > 0 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-gray-400' }}">৳{{ number_format($item['cash_collected']) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono {{ $item['adv_adjust'] > 0 ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-gray-400' }}">৳{{ number_format($item['adv_adjust']) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono {{ $item['due_collection'] > 0 ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-gray-400' }}">৳{{ number_format($item['due_collection']) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono font-bold {{ $item['due'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">৳{{ number_format($item['due']) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono {{ $item['advance'] > 0 ? 'text-purple-600 dark:text-purple-400 font-semibold' : 'text-gray-400' }}">৳{{ number_format($item['advance']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 font-bold text-xs">
                        <td colspan="3" class="px-3 py-2.5 text-gray-500 uppercase">Totals</td>
                        <td class="px-3 py-2.5 text-right font-mono">৳{{ number_format(collect($this->card)->sum('daily_rent')) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-emerald-600">৳{{ number_format(collect($this->card)->sum('cash_collected')) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-blue-600">৳{{ number_format(collect($this->card)->sum('adv_adjust')) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-indigo-600">৳{{ number_format(collect($this->card)->sum('due_collection')) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-red-600">৳{{ number_format(collect($this->card)->sum('due')) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-purple-600">৳{{ number_format(collect($this->card)->sum('advance')) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="flex flex-col items-center gap-3 py-12 text-gray-400">
            <x-filament::icon icon="heroicon-o-inbox" class="w-12 h-12 opacity-30" />
            <p class="text-sm">No transaction data found</p>
        </div>
        @endif
        <x-slot name="footer">
            <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'view-modal' })">Close</x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- PAYMENT HISTORY MODAL --}}
    <x-filament::modal id="payment-history-modal" width="lg">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-violet-100 dark:bg-violet-900/40">
                    <x-filament::icon icon="heroicon-o-clock" class="w-4 h-4 text-violet-600" />
                </span>
                Payment History
                @if($this->selectedCardNo)
                <span class="text-sm font-mono font-normal text-gray-500">— Card #{{ $this->selectedCardNo }}</span>
                @endif
            </div>
        </x-slot>
        @if (!empty($this->payments) && $this->payments->count())
        <div class="space-y-2 py-1">
            @foreach ($this->payments as $payment)
            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:border-violet-200 dark:hover:border-violet-800 transition-colors">
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-gray-400 font-mono">{{ $payment->created_at->format('d M Y · h:i A') }}</span>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">{{ $payment->paymentType->name }}</span>
                        @if($payment->tnx)
                        <span class="text-xs font-mono text-gray-400">{{ $payment->tnx }}</span>
                        @endif
                    </div>
                </div>
                <span class="font-bold text-base font-mono text-emerald-600 dark:text-emerald-400 amount-chip">৳{{ number_format($payment->amount, 2) }}</span>
            </div>
            @endforeach
            <div class="flex items-center justify-between pt-3 mt-1 border-t border-gray-200 dark:border-gray-700">
                <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">Total Paid</span>
                <span class="text-lg font-bold font-mono text-emerald-600 dark:text-emerald-400 amount-chip">৳{{ number_format($this->payments->sum('amount'), 2) }}</span>
            </div>
        </div>
        @else
        <div class="flex flex-col items-center gap-3 py-10 text-gray-400">
            <x-filament::icon icon="heroicon-o-banknotes" class="w-10 h-10 opacity-30" />
            <p class="text-sm">No payments recorded yet</p>
        </div>
        @endif
        <x-slot name="footer">
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'payment-history-modal' })">Close</x-filament::button>
        </x-slot>
    </x-filament::modal>

</x-filament::page>

{{-- @vite('resources/css/app.css') --}}
<x-filament-panels::page>
    {{--
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ __('Practice') }}
    </h1>
    </x-slot>



    <x-filament::section>
        {{-- <x-slot name="heading">
            Due List {{ now()->format('d/m/Y') }}
        </x-slot>

        <x-slot name="headerEnd">
            <x-filament::input.wrapper>

                <x-filament::input type="date" wire:model="amount" placeholder="Enter Amount" class="w-full" label="Card No" />
            </x-filament::input.wrapper>
            <x-filament::button color="primary" wire:click="savePayment()">
                Search
            </x-filament::button>
        </x-slot> --}}



        {{-- </x-filament::section>  --}}
        <livewire:due-list />
        {{-- <livewire:booking-timeline /> --}}


        {{-- <livewire:room-booking-chart /> --}}

</x-filament-panels::page>

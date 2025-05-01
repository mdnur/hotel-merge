<?php

namespace App\Filament\Pages;

use App\Livewire\DailyRoomSheet;
use App\Livewire\DueList as LivewireDueList;
use Carbon\Carbon;
use Filament\Pages\Page;

class DueList extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    public $date;
    protected static string $view = 'filament.pages.due-list';

    // public $data;

    public function mount()
    {
    }

    // public function dueList()
    // {

    //     $dailyRoomSheet = new DailyRoomSheet;
    //     $this->data = $dailyRoomSheet->showDailyCollection(Carbon::now()->format('Y-m-d'));
    //     // dd($this->data);
    // }
    public function refreshData()
    {
        $livewire = new LivewireDueList();
    //    $livewire->refreshData($this->date);
       return $this->mount();
        // dd('hello world');

        // Do something here
    }
}

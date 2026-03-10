<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DailyRoomSheetExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        // Convert collection to array of arrays
        $this->data = $data instanceof Collection
            ? $data
            : collect($data); // Always keep it as collection
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Sl No.',
            'Card No.',
            'Room No.',
            'Room Rent',
            'Cash',
            'Advance Adjust',
            'Due Collection',
            'Due',
            'Advance',
        ];
    }

    public function map($row): array
    {
        static $index = 1;

        return [
            $index++, // Sl No.
            $row['card_no'],
            $row['room_no'],
            $row['daily_rent'],
            $row['cash_collected'],
            $row['adv_adjust'],
            $row['due_collection'],
            $row['due'],
            $row['advance'],
        ];
    }
}

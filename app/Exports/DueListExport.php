<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DueListExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = collect($data);
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Card No',
            'Room No',
            'Daily Rent',
            "Todays's Due",
            'Total Due',
        ];
    }

    public function map($row): array
    {
        return [
            $row['card_no'],
            $row['room_no'],
            $row['daily_rent'],
            $row['due'],
            $row['total_due'],
        ];
    }

    public function foobar()
    {
        return [
            'Card No',
            'Room No',
            'Daily Rent',
            "Todays's Due",
            'Total Due',
        ];
    }
}

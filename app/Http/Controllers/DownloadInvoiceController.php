<?php

namespace App\Http\Controllers;

use App\Filament\Pages\DailyRoomSheet;
use Illuminate\Support\Facades\Storage;

use function Spatie\LaravelPdf\Support\pdf;

class DownloadInvoiceController
{
    // public function __invoke()
    // {
    //     return pdf('pdf.invoice', [
    //         'invoiceNumber' => '1234',
    //         'customerName' => 'Grumpy Cat',
    //     ]);
    // }

    public function download($date)
    {
        // dd($date);
        // DailyRoomSheet::downloadData($date);
        $filePath = (new DailyRoomSheet)->downloadData($date);

        // return $filePath;
        if (! Storage::exists($filePath)) {
            abort(404, 'File not found.');
        }

        return response()->streamDownload(function () use ($filePath) {
            echo Storage::get($filePath);
            Storage::delete($filePath); // delete after download

        }, $filePath);
    }
}

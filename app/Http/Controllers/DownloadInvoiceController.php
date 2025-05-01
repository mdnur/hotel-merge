<?php

namespace App\Http\Controllers;

use App\Filament\Pages\DailyRoomSheet;

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
        $filePath = (new DailyRoomSheet)->downloadData($date);

        $fullPath = storage_path("app/{$filePath}");

        if (! file_exists($fullPath)) {
            abort(404, 'File not found.');
        }

        return response()->download($fullPath)->deleteFileAfterSend(true);
    }
}

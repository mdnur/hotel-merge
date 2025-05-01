<?php

use App\Http\Controllers\DownloadInvoiceController;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

use function Spatie\LaravelPdf\Support\pdf;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/post', function () {
    return pdf()
        ->view('pdf.invoice')
        ->name('invoice-2023-04-10.pdf')
        ->download();
});
Route::get('/download-invoice/{date}', [DownloadInvoiceController::class, 'download'])
    ->name('download.invoice');

Route::get('/test', function () {
    $specificDate = Carbon::parse(now());

    // code in the class
    // Define billing period

    $check_out_start_time = Setting::where('key', '=', 'check_out_time')->get()->first()->value;
    $filterCheckOutStartTime = explode(':', $check_out_start_time);

    $CheckOutStartTime = $specificDate->copy()->setTime((int) $filterCheckOutStartTime[0], (int) $filterCheckOutStartTime[1]); // Start at 5:00 AM
    dd($CheckOutStartTime);

});

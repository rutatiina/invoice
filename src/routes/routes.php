<?php
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web', 'auth', 'tenant', 'service.accounting']], function() {

	Route::prefix('invoices')->group(function () {

        Route::post('routes', 'Rutatiina\Invoice\Http\Controllers\InvoiceController@routes')->name('invoice.routes');
        //Route::get('summary', 'Rutatiina\Invoice\Http\Controllers\InvoiceController@summary');
        Route::post('export-to-excel', 'Rutatiina\Invoice\Http\Controllers\InvoiceController@exportToExcel');
        Route::post('{id}/approve', 'Rutatiina\Invoice\Http\Controllers\InvoiceController@approve');
        //Route::post('contact-invoices', 'Rutatiina\Invoice\Http\Controllers\Sales\ReceiptController@invoices');
        Route::get('{id}/copy', 'Rutatiina\Invoice\Http\Controllers\InvoiceController@copy');
        Route::patch('cancel', 'Rutatiina\Invoice\Http\Controllers\InvoiceController@cancel')->name('invoice.cancel');

    });

    Route::resource('invoices/settings', 'Rutatiina\Invoice\Http\Controllers\InvoiceSettingsController');
    Route::resource('/invoices', 'Rutatiina\Invoice\Http\Controllers\InvoiceController');

});

Route::group(['middleware' => ['web', 'auth', 'tenant', 'service.accounting']], function() {

    Route::prefix('recurring-invoices')->group(function () {

        //Route::get('summary', 'Rutatiina\Invoice\Http\Controllers\RecurringController@summary');
        Route::post('export-to-excel', 'Rutatiina\Invoice\Http\Controllers\RecurringInvoiceController@exportToExcel');
        Route::post('{id}/activate', 'Rutatiina\Invoice\Http\Controllers\RecurringInvoiceController@activate');
        Route::get('{id}/copy', 'Rutatiina\Invoice\Http\Controllers\RecurringInvoiceController@copy');

    });

    Route::resource('recurring-invoices/settings', 'Rutatiina\Invoice\Http\Controllers\RecurringInvoiceSettingController');
    Route::resource('recurring-invoices', 'Rutatiina\Invoice\Http\Controllers\RecurringInvoiceController');

});

<?php

namespace Rutatiina\Invoice\Classes\Recurring;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Rutatiina\Invoice\Models\Invoice;
use Rutatiina\Invoice\Classes\Recurring\Copy as RecurringInvoiceCopy;
use Rutatiina\Invoice\Classes\Store as InvoiceStore;

class Schedule
{
    public $task;

    function __construct($task)
    {
        $this->task = $task;
    }

    /**
     * Execute the console command.
     *
     * @return boolean
     */

    public function __invoke()
    {
        $task = $this->task;

        //get the last invoice number
        $txn = Invoice::orderBy('id', 'desc')->first();
        //$settings = InvoiceSetting::first();
        //$number = $settings->number_prefix.(str_pad((optional($txn)->number+1), $settings->minimum_number_length, "0", STR_PAD_LEFT)).$settings->number_postfix;

        $TxnCopy = new RecurringInvoiceCopy();
        $txnAttributes = $TxnCopy->run($task->recurring_invoice_id);
        $txnAttributes['number'] = (optional($txn)->number + 1);
        //Log::info('doc number #'.$txnAttributes['number']);

        $TxnStore = new InvoiceStore();
        $TxnStore->txnInsertData = $txnAttributes;
        $insert = $TxnStore->run();

        if ($insert == false)
        {
            Log::warning('Error: Recurring invoice id:: #' . $task->recurring_invoice_id . ' failed @ ' . Carbon::now().' - timezones::'.config('app.timezone').' - '.date('Y m d H:i:s'));
            Log::warning($TxnStore->errors);
        }
        else
        {
            $task->update(['last_run' => now()]);
            Log::info('Success: Recurring invoice id:: #' . $task->recurring_invoice_id . ' passed @ ' . Carbon::now().' - timezones::'.config('app.timezone').' - '.date('Y m d H:i:s'));
        }
    }
}

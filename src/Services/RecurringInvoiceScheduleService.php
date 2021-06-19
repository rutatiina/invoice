<?php

namespace Rutatiina\Invoice\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RecurringInvoiceScheduleService
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

        $txnAttributes = RecurringInvoiceService::copy($task->invoice_recurring_id);

        $insert = RecurringInvoiceService::store($txnAttributes);

        if ($insert == false)
        {
            Log::warning('Error: Recurring invoice id:: #' . $task->invoice_recurring_id . ' failed @ ' . Carbon::now().' - timezones::'.config('app.timezone').' - '.date('Y m d H:i:s'));
            Log::warning(implode("\n", RecurringInvoiceService::$errors));
        }
        else
        {
            $task->update(['last_run' => now()]);
            Log::info('Success: Recurring invoice id:: #' . $task->invoice_recurring_id . ' passed @ ' . Carbon::now().' - timezones::'.config('app.timezone').' - '.date('Y m d H:i:s'));
        }
    }
}
<?php

namespace Rutatiina\Invoice\Traits\Recurring;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Rutatiina\FinancialAccounting\Traits\Schedule as FinancialAccountingScheduleTrait;
use Rutatiina\Invoice\Models\RecurringInvoice;

trait Schedule
{
    use FinancialAccountingScheduleTrait;

    /**
     * Execute the console command.
     *
     * @param \Rutatiina\Invoice\Traits\Recurring\Schedule $schedule
     * @return boolean
     */
    public function recurringInvoiceSchedule($schedule)
    {
        //return true;

        config(['app.scheduled_process' => true]);

        //$schedule->call(function () {
        //    Log::info('recurringInvoiceSchedule via trait has been called #updated');
        //})->everyMinute()->runInBackground();

        //the script to process recurring requests

        try
        {
            DB::connection('tenant')->getDatabaseName();
            Schema::hasTable((new RecurringInvoice)->getTable());
        }
        catch (\Throwable $e)
        {
            return false;
        }

        $tasks = RecurringInvoice::withoutGlobalScopes()
            ->where('status', 'active')
            ->get();

        $this->recurringSchedule($schedule, $tasks);

        //Log::info('number of tasks: '.$tasks->count());

        return true;
    }
}

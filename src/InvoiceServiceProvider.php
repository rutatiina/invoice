<?php

namespace Rutatiina\Invoice;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Rutatiina\Invoice\Traits\Recurring\Schedule as RecurringInvoiceScheduleTrait;

class InvoiceServiceProvider extends ServiceProvider
{
    use RecurringInvoiceScheduleTrait;

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        /*
        $providers = array_values(config('app.providers'));
        $requiredProviders = [
            "Rutatiina\Contact\ContactServiceProvider",
            "Rutatiina\Tenant\TenantServiceProvider",
            "Rutatiina\Item\ItemServiceProvider"
        ];
        $arrayIntersect = array_values(array_intersect($providers, $requiredProviders));

        if ($arrayIntersect != $requiredProviders) {
            //echo 'some packages are required'; exit;
            return redirect('/?required-packages-missing')->send();
        }
        */

        include __DIR__.'/routes/routes.php';
        //include __DIR__.'/routes/api.php';

        $this->loadViewsFrom(__DIR__.'/resources/views', 'invoice');
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        //register the scheduled tasks
        $this->app->booted(function () {
            $this->recurringInvoiceSchedule(app(Schedule::class));
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Rutatiina\Invoice\Http\Controllers\InvoiceController');
    }
}

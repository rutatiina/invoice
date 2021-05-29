<?php

namespace Rutatiina\Invoice;

use Illuminate\Support\ServiceProvider;

class InvoiceServiceProvider extends ServiceProvider
{
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

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRgInvoiceRecurringsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('rg_invoice_recurrings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            //>> default columns
            $table->softDeletes();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            //<< default columns

            //>> table columns
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('app_id');
            $table->string('profile_name', 250); //name of the recurring invoice
            $table->unsignedBigInteger('contact_id');
            $table->string('contact_name', 50);
            $table->string('contact_address', 50);
            $table->string('reference', 100)->nullable();
            $table->string('base_currency', 3);
            $table->string('quote_currency', 3);
            $table->unsignedDecimal('exchange_rate', 20,10);
            $table->unsignedDecimal('taxable_amount', 20,5);
            $table->unsignedDecimal('total', 20, 5);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('salesperson_id')->nullable();
            $table->string('payment_mode', 50)->nullable();
            $table->string('payment_terms', 100)->nullable();

            //>> recurring details columns
            $table->string('status', 20)->nullable(); //active | paused | de-active
            $table->string('frequency', 50)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('cron_day_of_month', 10)->nullable();
            $table->string('cron_month', 10)->nullable();
            $table->string('cron_day_of_week', 10)->nullable();
            $table->dateTime('last_run')->nullable()->comment('date time of last run');
            $table->dateTime('next_run')->nullable()->comment('date time of next run');

            $table->string('contact_notes', 250)->nullable();
            $table->string('terms_and_conditions', 250)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->dropIfExists('rg_invoice_recurrings');
    }
}

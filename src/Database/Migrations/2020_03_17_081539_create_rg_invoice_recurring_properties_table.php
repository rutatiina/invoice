<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRgInvoiceRecurringPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('rg_invoice_recurring_properties', function (Blueprint $table) {
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
            $table->unsignedBigInteger('invoice_recurring_id');
            $table->string('status', 20); //active | paused | de-active
            $table->string('frequency', 50);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('day_of_month', 10);
            $table->string('month', 10);
            $table->string('day_of_week', 10);

            $table->dateTime('last_run')->comment('date time of last run');
            $table->dateTime('next_run')->comment('date time of next run');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->dropIfExists('rg_invoice_recurring_properties');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RgInvoiceItemsAddTaxableAmountColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('tenant')->hasColumn('rg_invoice_items', 'taxable_amount'))
        {
            //do nothing
        }
        else
        {
            Schema::connection('tenant')->table('rg_invoice_items', function (Blueprint $table) {
                $table->unsignedDecimal('taxable_amount', 20, 5)->nullable()->default(0)->after('rate');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->table('rg_invoice_items', function (Blueprint $table) {
            $table->dropColumn('taxable_amount');
        });
    }
}

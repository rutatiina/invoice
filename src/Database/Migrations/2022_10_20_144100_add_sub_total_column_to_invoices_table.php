<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubTotalColumnToInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->table('rg_invoices', function (Blueprint $table) {
            $table->unsignedDecimal('sub_total', 20, 5)->nullable()->default(0)->after('taxable_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->table('rg_invoices', function (Blueprint $table) {
            $table->dropColumn('sub_total');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameInvoicesDiscountColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('tenant')->hasColumn('rg_invoices', 'discount_amount'))
        {
            Schema::connection('tenant')->table('rg_invoices', function (Blueprint $table) {
                $table->renameColumn('discount_amount', 'discount');
            });
        }

        Schema::connection('tenant')->table('rg_invoices', function (Blueprint $table) {
            $table->unsignedDecimal('discount_percentage', 20, 5)->nullable()->default(0)->after('discount');
        });
    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::connection('tenant')->hasColumn('rg_invoices', 'discount'))
        {
            Schema::connection('tenant')->table('rg_invoices', function (Blueprint $table) {
                $table->renameColumn('discount', 'discount_amount');
            });
        }

        Schema::connection('tenant')->table('rg_invoices', function (Blueprint $table) {
            $table->dropColumn('discount_percentage');
        });
    }
}

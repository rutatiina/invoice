<?php

namespace Rutatiina\Invoice\Classes;

use Rutatiina\FinancialAccounting\Models\Txn;
use Rutatiina\FinancialAccounting\Models\TxnNumberConfig;
use Rutatiina\FinancialAccounting\Models\TxnNumber;
use Rutatiina\Invoice\Traits\Init as TxnTraitsInit;
use Rutatiina\Invoice\Traits\Entree as TxnEntree;

class Number
{
    use TxnTraitsInit;
    use TxnEntree;

    public function __construct()
    {}

    public function run($idOrSlug)
    {
        $txnEntree = $this->entree($idOrSlug);

        if ($txnEntree) {
            //do nothing
        } else {
            return false;
        }

        $txnType = $txnEntree['config']['txn_type'];

        $TxnNumberConfig = TxnNumberConfig::where('txn_type_id', $txnType['id'])->first();
        if ($TxnNumberConfig) {
            $prefix = $TxnNumberConfig->prefix;
            $postfix = $TxnNumberConfig->postfix;
            $length = $TxnNumberConfig->length;
        } else {
            $prefix = '';
            $postfix = '';
            $length = 5;
        }

        $TxnNumber = TxnNumber::where('txn_type_id', $txnType['id'])->first();

        if ($TxnNumber) {
            return str_pad($prefix.($TxnNumber->last_number+1).$postfix, $length, "0", STR_PAD_LEFT);
        } else {
            return str_pad($prefix.'1'.$postfix, $length, "0", STR_PAD_LEFT);
        }



    }

}

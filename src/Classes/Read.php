<?php

namespace Rutatiina\Invoice\Classes;

use Rutatiina\Invoice\Models\Invoice;

use Rutatiina\Invoice\Traits\Init as TxnTraitsInit;
use Rutatiina\Receipt\Models\Receipt;

class Read
{
    use TxnTraitsInit;

    public function __construct()
    {
    }

    public function run($id)
    {
        $Txn = Invoice::find($id);

        if ($Txn)
        {
            //txn has been found so continue normally
        }
        else
        {
            $this->errors[] = 'Transaction not found';
            return false;
        }

        $Txn->load('contact', 'debit_account', 'credit_account', 'items');
        $Txn->load('annexes.receipt.debit_account', 'annexes.receipt.items');
        $Txn->setAppends(['taxes']);

        foreach ($Txn->items as &$item)
        {
            if (empty($item->name))
            {
                $txnDescription[] = $item->description;
            }
            else
            {
                $txnDescription[] = (empty($item->description)) ? $item->name : $item->name . ': ' . $item->description;
            }
        }

        $Txn->description = implode(',', $txnDescription);

        return $Txn->toArray();

    }

}

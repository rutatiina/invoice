<?php

namespace Rutatiina\Invoice\Services;

use Rutatiina\Invoice\Models\InvoiceItem;
use Rutatiina\Invoice\Models\InvoiceItemTax;
use Rutatiina\Invoice\Models\InvoiceLedger;

class InvoiceLedgerService
{
    public static $errors = [];

    public function __construct()
    {
        //
    }

    public static function store($data)
    {
        //print_r($data['ledgers']); exit;

        //Save the items >> $data['items']
        foreach ($data['ledgers'] as &$ledger)
        {
            $ledger['invoice_id'] = $data['id'];
            InvoiceLedger::create($ledger);
        }
        unset($ledger);

    }

}

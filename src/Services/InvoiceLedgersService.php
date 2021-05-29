<?php

namespace Rutatiina\Invoice\Services;

use Rutatiina\Invoice\Models\InvoiceItem;
use Rutatiina\Invoice\Models\InvoiceItemTax;
use Rutatiina\Invoice\Models\InvoiceLedger;

class InvoiceLedgersService
{
    public static $errors = [];

    public function __construct()
    {
        //
    }

    public static function store($data)
    {
        foreach ($data['ledgers'] as &$ledger)
        {
            $ledger['retainer_invoice_id'] = $data['id'];
            InvoiceLedger::create($ledger);
        }
        unset($ledger);

    }

}

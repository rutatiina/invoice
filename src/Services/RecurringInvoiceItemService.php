<?php

namespace Rutatiina\Invoice\Services;

use Rutatiina\Invoice\Models\RecurringInvoiceItem;
use Rutatiina\Invoice\Models\RecurringInvoiceItemTax;

class RecurringInvoiceItemService
{
    public static $errors = [];

    public function __construct()
    {
        //
    }

    public static function store($data)
    {
        //print_r($data['items']); exit;

        //Save the items >> $data['items']
        foreach ($data['items'] as &$item)
        {
            $item['recurring_invoice_id'] = $data['id'];

            $itemTaxes = (is_array($item['taxes'])) ? $item['taxes'] : [] ;
            unset($item['taxes']);

            $itemModel = RecurringInvoiceItem::create($item);

            foreach ($itemTaxes as $tax)
            {
                //save the taxes attached to the item
                $itemTax = new RecurringInvoiceItemTax;
                $itemTax->tenant_id = $item['tenant_id'];
                $itemTax->recurring_invoice_id = $item['recurring_invoice_id'];
                $itemTax->recurring_invoice_item_id = $itemModel->id;
                $itemTax->tax_code = $tax['code'];
                $itemTax->amount = $tax['total'];
                $itemTax->inclusive = $tax['inclusive'];
                $itemTax->exclusive = $tax['exclusive'];
                $itemTax->save();
            }
            unset($tax);
        }
        unset($item);

    }

}

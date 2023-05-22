<?php

namespace Rutatiina\Invoice\Services;

use Rutatiina\Item\Models\Item;
use Rutatiina\Contact\Models\Contact;
use Illuminate\Support\Facades\Validator;
use Rutatiina\Invoice\Models\RecurringInvoiceSetting;

class RecurringInvoiceValidateService
{
    public static $errors = [];

    public static function run($requestInstance)
    {
        //$request = request(); //used for the flash when validation fails
        $user = auth()->user();


        // >> data validation >>------------------------------------------------------------

        //validate the data
        $customMessages = [
            'con_day_of_month.required_if' => "The day of month to recur is required",
            'con_month.required_if' => "The month to recur is required",
            'con_day_of_week.required_if' => "The day of week to recur is required",

            'items.*.taxes.*.code.required' => "Tax code is required",
            'items.*.taxes.*.total.required' => "Tax total is required",
            //'items.*.taxes.*.exclusive.required' => "Tax exclusive amount is required",
        ];

        $rules = [
            'profile_name' => 'required|string|max:250',
            'contact_id' => 'required|numeric',
            'base_currency' => 'required',
            'salesperson_contact_id' => 'numeric|nullable',
            'customer_notes' => 'string|nullable',

            'frequency' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'con_day_of_month' => 'required_if:frequency,custom|string',
            'con_month' => 'required_if:frequency,custom|string',
            'con_day_of_week' => 'required_if:frequency,custom|string',

            'items' => 'required|array',
            'items.*.name' => 'required_without:type_id',
            'items.*.rate' => 'required|numeric',
            'items.*.quantity' => 'required|numeric|gt:0',
            //'items.*.total' => 'required|numeric|in:' . $itemTotal, //todo custom validator to check this
            'items.*.units' => 'numeric|nullable',
            'items.*.taxes' => 'array|nullable',

            'items.*.taxes.*.code' => 'required',
            'items.*.taxes.*.total' => 'required|numeric',
        ];

        $validator = Validator::make($requestInstance->all(), $rules, $customMessages);

        if ($validator->fails())
        {
            self::$errors = $validator->errors()->all();
            return false;
        }

        // << data validation <<------------------------------------------------------------

        $contact = Contact::findOrFail($requestInstance->contact_id);

        $data['id'] = $requestInstance->input('id', null); //for updating the id will always be posted
        $data['user_id'] = $user->id;
        $data['tenant_id'] = $user->tenant->id;
        $data['created_by'] = $user->name;
        $data['app'] = 'web';
        $data['profile_name'] = $requestInstance->input('profile_name');
        $data['contact_id'] = $requestInstance->contact_id;
        $data['contact_name'] = $contact->name;
        $data['contact_address'] = trim($contact->shipping_address_street1 . ' ' . $contact->shipping_address_street2);
        $data['base_currency'] =  $requestInstance->input('base_currency');
        $data['quote_currency'] =  $requestInstance->input('quote_currency', $data['base_currency']);
        $data['exchange_rate'] = $requestInstance->input('exchange_rate', 1);
        $data['salesperson_contact_id'] = $requestInstance->input('salesperson_contact_id', null);
        $data['branch_id'] = $requestInstance->input('branch_id', null);
        $data['store_id'] = $requestInstance->input('store_id', null);
        $data['due_date'] = $requestInstance->input('due_date', null);
        $data['terms_and_conditions'] = $requestInstance->input('terms_and_conditions', null);
        $data['contact_notes'] = $requestInstance->input('contact_notes', null);

        $data['status'] = $requestInstance->input('status', null);
        $data['frequency'] = $requestInstance->input('frequency', null);
        $data['start_date'] = $requestInstance->input('start_date', null);
        $data['end_date'] = $requestInstance->input('end_date', null);
        $data['cron_day_of_month'] = $requestInstance->input('cron_day_of_month', null);
        $data['cron_month'] = $requestInstance->input('cron_month', null);
        $data['cron_day_of_week'] = $requestInstance->input('cron_day_of_week', null);

        //set the transaction total to zero
        $txnTotal = 0;
        $taxableAmount = 0;

        //Formulate the DB ready items array
        $data['items'] = [];
        foreach ($requestInstance->items as $key => $item)
        {
            $itemTaxes = $requestInstance->input('items.'.$key.'.taxes', []);

            $txnTotal           += ($item['rate']*$item['quantity']);
            $taxableAmount      += ($item['rate']*$item['quantity']);
            $itemTaxableAmount   = ($item['rate']*$item['quantity']); //calculate the item taxable amount

            foreach ($itemTaxes as $itemTax)
            {
                $txnTotal           += $itemTax['exclusive'];
                $taxableAmount      -= $itemTax['inclusive'];
                $itemTaxableAmount  -= $itemTax['inclusive']; //calculate the item taxable amount more by removing the inclusive amount
            }

            //get the item
            $itemModel = Item::find($item['item_id']);

            $data['items'][] = [
                'tenant_id' => $data['tenant_id'],
                'created_by' => $data['created_by'],
                'contact_id' => $item['contact_id'],
                'item_id' => optional($itemModel)->id, //$item['item_id'], use internal ID to verify data so that from here one the item_id value is LEGIT
                'name' => $item['name'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'total' => $item['total'],
                'taxable_amount' => $itemTaxableAmount,
                'units' => $requestInstance->input('items.'.$key.'.units', null),
                'batch' => $requestInstance->input('items.'.$key.'.batch', null),
                'expiry' => $requestInstance->input('items.'.$key.'.expiry', null),
                'taxes' => $itemTaxes,
            ];
        }

        $data['taxable_amount'] = $taxableAmount;
        $data['total'] = $txnTotal;

        $data['recurring']  = $requestInstance->input('recurring', []);

        //print_r($data); exit;

        return $data;

    }

}

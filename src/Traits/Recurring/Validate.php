<?php

namespace Rutatiina\Invoice\Traits\Recurring;

use Illuminate\Support\Facades\Validator;
use Rutatiina\Contact\Models\Contact;
use Rutatiina\Invoice\Models\RecurringInvoice;
use Rutatiina\Invoice\Models\RecurringInvoiceSetting;
use Rutatiina\Tax\Models\Tax;

trait Validate
{

    private function insertDataDefault($key, $defaultValue)
    {
        if (isset($this->txnInsertData[$key])) {
            return $this->txnInsertData[$key];
        } else {
            return $defaultValue;
        }
    }

    private function itemDataDefault($item, $key, $defaultValue)
    {
        if (isset($item[$key])) {
            return $item[$key];
        } else {
            return $defaultValue;
        }
    }

    private function validate()
    {
        //$request = request(); //used for the flash when validation fails
        $user = auth()->user();

        //print_r($request->all()); exit;

        $data = $this->txnInsertData;

        //print_r($data); exit;

        $data['user_id'] = $user->id;
        $data['tenant_id']  = $user->tenant->id;
        $data['created_by'] = $user->name;

        //set default values ********************************************
        $data['id'] = $this->insertDataDefault('id', null);
        $data['app'] = $this->insertDataDefault('app', null);
        $data['app_id'] = $this->insertDataDefault('app_id', null);
        $data['internal_ref'] = $this->insertDataDefault('internal_ref', null);
        $data['txn_entree_id'] = $this->insertDataDefault('txn_entree_id', null);

        $data['payment_mode'] = $this->insertDataDefault('payment_mode', null);
        $data['payment_terms'] = $this->insertDataDefault('payment_terms', null);
        $data['invoice_number'] = $this->insertDataDefault('invoice_number', null);
        $data['due_date'] = $this->insertDataDefault('due_date', null);
        $data['expiry_date'] = $this->insertDataDefault('expiry_date', null);

        $data['base_currency'] = $this->insertDataDefault('base_currency', null);
        $data['quote_currency'] = $this->insertDataDefault('quote_currency', $data['base_currency']);
        $data['exchange_rate'] = $this->insertDataDefault('exchange_rate', 1);

        $data['contact_id'] = $this->insertDataDefault('contact_id', null);
        $data['debit_contact_id'] = $this->insertDataDefault('debit_contact_id', $data['contact_id']);
        $data['credit_contact_id'] = $this->insertDataDefault('credit_contact_id', $data['contact_id']);
        $data['contact_name'] = $this->insertDataDefault('contact_name', null);
        $data['contact_address'] = $this->insertDataDefault('contact_address', null);

        $data['branch_id'] = $this->insertDataDefault('branch_id', null);
        $data['store_id'] = $this->insertDataDefault('store_id', null);
        $data['terms_and_conditions'] = $this->insertDataDefault('terms_and_conditions', null);
        $data['external_ref'] = $this->insertDataDefault('external_ref', null);
        $data['reference'] = $this->insertDataDefault('reference', null);
        $data['recurring'] = $this->insertDataDefault('recurring', []);

        $data['items'] = $this->insertDataDefault('items', []);

        $data['taxes'] = $this->insertDataDefault('taxes', []);

        //$data['taxable_amount'] = $this->insertDataDefault('taxable_amount', );
        $data['discount'] = $this->insertDataDefault('discount', 0);


        // >> data validation >>------------------------------------------------------------

        //validate the data
        $rules = [
            'id' => 'numeric|nullable',
            'tenant_id' => 'required|numeric',
            'user_id' => 'required|numeric',
            'contact_id' => 'required|numeric',
            'date' => 'required|date',
            'internal_ref' => 'numeric|nullable',
            'base_currency' => 'required',
            'items' => 'required|array',
            'txn_type_id' => 'numeric|nullable',
            //'contact_name' => 'required|string',
            'debit' => 'numeric|nullable',
            'credit' => 'numeric|nullable',
        ];

        if ($this->txnEntreeSlug == 'journal') {
            unset($rules['contact_id']); //remove the contact if validation rule for journal entries
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $this->errors = $validator->errors()->all();
            return false;
        }


        //validate the items
		$customMessages = [
			'total.in' => "Item total is invalid:\nItem total = item rate x item quantity",
			'type_id.required_without' => "No item is selected in row.",
		];

        foreach ($data['items'] as $key => &$item) {

        	$itemTotal = floatval($item['quantity']) * floatval($item['rate']);

            $validator = Validator::make($item, [
                'type_id'   => 'required_without:name|numeric|nullable',
                'name'      => 'required_without:type_id',
                'rate'      => 'required|numeric',
                'quantity'  => 'required|numeric|gt:0',
                'discount'  => 'numeric',
                'total'     => 'required|numeric|in:'.$itemTotal,
                'units'     => 'numeric|nullable',
            ], $customMessages);

            if ($validator->fails()) {
                $this->errors = $validator->errors()->all();
                return false;
            }

        }
        //print_r($data['items']); exit;
        unset($item);

        //validate the taxes data
        $customMessages = [
            'code.required' => "Tax code is required",
            'total.required' => "Tax total is required",
            'exclusive.required' => "Tax exclusive amount is required",
        ];

        foreach($data['taxes'] as $key => $tax) {

            $validator = Validator::make($tax, [
                'code' => 'required',
                'total' => 'required|numeric',
                'exclusive' => 'required|numeric',
            ], $customMessages);

            if ($validator->fails()) {
                $this->errors = $validator->errors()->all();
                return false;
            }
        }

        //validate the recurring details
        if ($data['isRecurring'] === true || $data['isRecurring'] == 'true') {

            $validator = Validator::make($data['recurring'], [
                //'date_range' => 'required|array',
                'frequency' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'day_of_month' => 'required|string',
                'month' => 'required|string',
                'day_of_week' => 'required|string',
            ], $customMessages);

            if ($validator->fails()) {
                $this->errors = $validator->errors()->all();
                return false;
            }

            $this->txn['isRecurring'] = true;

        } else {
            $this->txn['isRecurring'] = false;
        }

        // << data validation <<------------------------------------------------------------

        $this->settings = RecurringInvoiceSetting::with(['financial_account_to_debit', 'financial_account_to_credit'])->first();

        if (!$this->settings->financial_account_to_debit && !$this->settings->financial_account_to_credit)
        {
            $this->errors[] = 'Error: Please check Expense double entry settings.';
            return false;
        }


        $contact = Contact::find($data['contact_id']);
        if ($contact)
        {
            $data['contact_name']       = (!empty(trim($contact->name))) ? $contact->name : $contact->display_name;
            $data['contact_address']    = trim($contact->shipping_address_street1.' '.$contact->shipping_address_street2);
        }

        //set the transaction total to zero
		$txnTotal = 0;

        //Formulate the DB ready items array
        $items = [];
        foreach ($data['items'] as $item) {

            $itemData = [
                'type'          => $item['type'],
                'type_id'       => $item['type_id'],
                'contact_id'    => $item['contact_id'],
                'name'          => $item['name'],
                'description'   => $item['description'],
                'quantity'      => $item['quantity'],
                'rate'          => $item['rate'],
                'total'         => $item['total'],
                'units'         => $this->itemDataDefault($item, 'units', null),
                'batch'         => $this->itemDataDefault($item, 'batch', null),
                'expiry'        => $this->itemDataDefault($item, 'expiry', null),
                'taxes'         => (isset($item['taxes']) && is_array($item['taxes'])) ? $item['taxes'] : [],
            ];

            if ($item['type'] == 'txn') {
                $items[$item['type_id']] = $itemData;
            } else {
                $items[] = $itemData;
            }

            //generate the transaction total
			$txnTotal += $item['total'];

        }

        //Add the tax exclusive amount to the transaction total -----------------------------------------------------
        foreach($data['taxes'] as $key => $tax)
        {
            //update the total of the transaction i.e. only add the amount exclusive
            $txnTotal += $tax['exclusive'];
        }
        //end: Generate the tax items -----------------------------------------------------

        //print_r($data); exit;

        $last_txn = RecurringInvoice::latest()->first();


        // >> Generate the transaction variables
        $this->txn['id'] = $data['id'];
        $this->txn['tenant_id'] = $data['tenant_id'];
        $this->txn['user_id'] = $data['user_id'];
        $this->txn['app'] = $data['app'];
        $this->txn['app_id'] = $data['app_id'];
        $this->txn['created_by'] = $data['created_by'];
        $this->txn['internal_ref'] = $data['internal_ref'];
        $this->txn['document_name'] = $this->settings->document_name;
        $this->txn['number_prefix'] = $this->settings->number_prefix;
        $this->txn['number'] = (optional($last_txn)->number + 1);
        $this->txn['number_length'] = $this->settings->minimum_number_length;
        $this->txn['number_postfix'] = $this->settings->number_postfix;
        $this->txn['date'] = $data['date'];
        $this->txn['debit_financial_account_code'] = optional($this->settings->financial_account_to_debit)->code;
        $this->txn['credit_financial_account_code'] = optional($this->settings->financial_account_to_credit)->code;
        $this->txn['contact_name'] = $data['contact_name'];
        $this->txn['contact_address'] = $data['contact_address'];
        $this->txn['reference'] = $data['reference'];
        $this->txn['invoice_number'] = $data['invoice_number'];
        $this->txn['base_currency'] = $data['base_currency'];
        $this->txn['quote_currency'] = $data['quote_currency'];
        $this->txn['exchange_rate'] = $data['exchange_rate'];
        $this->txn['taxable_amount'] = $data['taxable_amount'];
        $this->txn['total'] = $txnTotal;
        $this->txn['balance'] = $txnTotal;
        $this->txn['branch_id'] = $data['branch_id'];
        $this->txn['store_id'] = $data['store_id'];
        $this->txn['due_date'] = $data['due_date'];
        $this->txn['expiry_date'] = $data['expiry_date'];
        $this->txn['terms_and_conditions'] = $data['terms_and_conditions'];
        $this->txn['external_ref'] = $data['external_ref'];
        $this->txn['payment_mode'] = $data['payment_mode'];
        $this->txn['payment_terms'] = $data['payment_terms'];
        $this->txn['status'] = $data['status'];

        $this->txn['debit_contact_id'] = $data['debit_contact_id'];
        $this->txn['credit_contact_id'] = $data['credit_contact_id'];
        // << Generate the transaction variables

        $this->txn['items'] = $items;

        $this->txn['taxes'] = $data['taxes'];

        $this->txn['accounts']    = [
            'debit' => $this->settings->financial_account_to_debit,
            'credit' => $this->settings->financial_account_to_credit,
        ];

        $this->txn['ledgers']     = [
            'debit' => [
                'financial_account_code'    => $this->txn['debit_financial_account_code'],
                'effect'        => 'debit',
                'total'         => $this->txn['total'],
                'contact_id'    => $this->txn['debit_contact_id']
            ],
            'credit' => [
                'financial_account_code'   => $this->txn['credit_financial_account_code'],
                'effect'       => 'credit',
                'total'        => $this->txn['total'],
                'contact_id'   => $this->txn['credit_contact_id']
            ]
        ];

        $this->txn['recurring']  = $data['recurring'];

        //print_r($this->txn); exit;
        //print_r($this->settings->document_type); exit;

        //Now add the default values to items and ledgers

        $this->txn['items_contacts_ids'] = [];

        foreach($this->txn['items'] as $item_index => &$item) {
            $item['tenant_id'] = $data['tenant_id'];

            if (isset($item['contact_id']) && !empty($item['contact_id']) && is_numeric($item['contact_id'])) {
                $this->txn['items_contacts_ids'][$item['contact_id']] = $item['contact_id'];
            }
        }
        unset($item);

        $this->txnItemsContactsIdsLedgers();
        
        foreach($this->txn['ledgers'] as $ledgers_index => &$ledger) {

            $ledger['tenant_id']        = $data['tenant_id'];
            $ledger['date']             = date('Y-m-d', strtotime($data['date']));
            $ledger['base_currency']    = $data['base_currency'];
            $ledger['quote_currency']   = $data['quote_currency'];
            $ledger['exchange_rate']    = $data['exchange_rate'];

            //Delete ledger entries to 0 or null accounts
            if ( empty($ledger['financial_account_code']) ) {
                unset($this->txn['ledgers'][$ledgers_index]);
            }
        }
        unset($ledger);


        //Return the array of txns
        //print_r($this->txn); exit;

        return true;

    }

}

<?php

namespace Rutatiina\Invoice\Classes\Recurring;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Rutatiina\Invoice\Models\RecurringInvoice;

use Rutatiina\Invoice\Models\RecurringInvoiceItem;
use Rutatiina\Invoice\Models\RecurringInvoiceLedger;
use Rutatiina\Invoice\Traits\Recurring\Init as TxnTraitsInit;
use Rutatiina\Invoice\Traits\Recurring\Inventory as TxnTraitsInventory;
use Rutatiina\Invoice\Traits\Recurring\InventoryReverse as TxnTraitsInventoryReverse;
use Rutatiina\Invoice\Traits\Recurring\TxnItemsContactsIdsLedgers as TxnTraitsTxnItemsContactsIdsLedgers;
use Rutatiina\Invoice\Traits\Recurring\TxnTypeBasedSpecifics as TxnTraitsTxnTypeBasedSpecifics;
use Rutatiina\Invoice\Traits\Recurring\Validate as TxnTraitsValidate;
use Rutatiina\Invoice\Traits\Recurring\AccountBalanceUpdate as TxnTraitsAccountBalanceUpdate;
use Rutatiina\Invoice\Traits\Recurring\ContactBalanceUpdate as TxnTraitsContactBalanceUpdate;
use Rutatiina\Invoice\Traits\Recurring\Approve as TxnTraitsApprove;

class Update
{
    use TxnTraitsInit;
    use TxnTraitsInventory;
    use TxnTraitsInventoryReverse;
    use TxnTraitsTxnItemsContactsIdsLedgers;
    use TxnTraitsTxnTypeBasedSpecifics;
    use TxnTraitsValidate;
    use TxnTraitsAccountBalanceUpdate;
    use TxnTraitsContactBalanceUpdate;
    use TxnTraitsApprove;

    public function __construct()
    {}

    public function run()
    {
        //print_r($this->txnInsertData); exit;

        $verifyWebData = $this->validate();
        if ($verifyWebData === false) return false;

        $Txn = RecurringInvoice::with('items', 'ledgers', 'debit_account', 'credit_account')->find($this->txn['id']);

        if (!$Txn) {
            $this->errors[] = 'Transaction not found';
            return false;
        }

        if ($Txn->status == 'approved') {
            $this->errors[] = 'Approved Transaction cannot be not be edited';
            return false;
        }

        $this->txn['original'] = $Txn->toArray();

        //check if inventory is affected and if its available
        $inventoryAvailability = $this->inventoryAvailability();
        if ($inventoryAvailability === false) return false;

		//Log::info($this->txn);
        //var_dump($this->txn); exit;
        //print_r($this->txn); exit;
        //echo json_encode($this->txn); exit;


        //start database transaction
        DB::connection('tenant')->beginTransaction();

        try {

            //Delete the ledger entries
            RecurringInvoiceLedger::where('recurring_invoice_id', $this->txn['original']['id'])->delete();

            //Delete the items
            RecurringInvoiceItem::where('recurring_invoice_id', $this->txn['original']['id'])->delete();

            // >> reverse all the inventory and balance effects
            //inventory checks and inventory balance update if needed
            $this->inventoryReverse();

            //Update the account balances
            $this->accountBalanceUpdate(true);

            //Update the contact balances
            $this->contactBalanceUpdate(true);
            // << reverse all the inventory and balance effects

            $txnId = $Txn->id;

            //print_r($this->txn); exit; //$this->txn, $this->txn['items'], $this->txn[number], $this->txn[ledgers], $this->txn[recurring]

            //print_r($this->txn); exit;
            $Txn->created_by = $this->txn['created_by'];
            $Txn->number = $this->txn['number'];
            $Txn->date = $this->txn['date'];
            $Txn->debit_financial_account_code = $this->txn['debit_financial_account_code'];
            $Txn->credit_financial_account_code = $this->txn['credit_financial_account_code'];
            $Txn->debit_contact_id = $this->txn['debit_contact_id'];
            $Txn->credit_contact_id = $this->txn['credit_contact_id'];
            $Txn->contact_name = $this->txn['contact_name'];
            $Txn->contact_address = $this->txn['contact_address'];
            $Txn->reference = $this->txn['reference'];
            $Txn->base_currency = $this->txn['base_currency'];
            $Txn->quote_currency = $this->txn['quote_currency'];
            $Txn->exchange_rate = $this->txn['exchange_rate'];
            $Txn->taxable_amount = $this->txn['taxable_amount'];
            $Txn->total = $this->txn['total'];
            $Txn->balance = $this->txn['balance'];
            $Txn->branch_id = $this->txn['branch_id'];
            $Txn->store_id = $this->txn['store_id'];
            $Txn->due_date = $this->txn['due_date'];
            $Txn->expiry_date = $this->txn['expiry_date'];
            $Txn->terms_and_conditions = $this->txn['terms_and_conditions'];
            $Txn->external_ref = $this->txn['external_ref'];
            $Txn->payment_mode = $this->txn['payment_mode'];
            $Txn->payment_terms = $this->txn['payment_terms'];
            $Txn->status = $this->txn['status'];
            $Txn->save();

            foreach($this->txn['items'] as &$item) {
                $item['recurring_invoice_id'] = $txnId;
            }

            unset($item);


            //Create items to be posted under the parent txn
            //update status of invoice being paid off
            foreach($this->txn['items'] as $value) {
                if ($value['type'] == 'txn') {

                    //Check if the full amount has been paid or part of it
                    $internal_ref = ($value['type'] == 'txn') ? $value['type_id'] : $this->txn['internal_ref'];

                    $txnInReference = Txn::find($internal_ref);

                    //if reference transaction has been found, then proceed
                    if ($txnInReference) {

                        //the bellow 2 line are to be removed when txn status is tracked in separate new table
                        $status = ($txnInReference->balance == $value['total']) ? 'Paid' : 'Partially Paid';
                        $txnInReference->update(['status' => $status]);

                        //If its a receipt update the status of the txn being paid off
                        //If its a payment update the status of the txn being paid off
                        if (  $TxnType->category == 'receipt' || $TxnType->category == 'payment' ) {

                            $txnInReference->decrement('balance', $value['total']);

                        }

                    }

                    //now --Create items to be posted under the parent txn
                    //the bellow code has to be at end of loop so that the correct $txnInReference is got
                    $value['recurring_invoice_id']    = $value['type_id']; //Set the txn id of the Parent transaction
                    $value['type_id']   = $txnId; //Set the Txn id of the transaction being created
                    $this->txn['items'][]     = $value;

                }
            }

            //print_r($this->txn['items']); exit;

            foreach($this->txn['ledgers'] as &$ledger) {
                $ledger['recurring_invoice_id'] = $txnId;
            }
            unset($ledger);

            //Save the items >> $this->txn['items']
            RecurringInvoiceItem::insert($this->txn['items']);

            //Save the ledgers >> $this->txn['ledgers']; and update the balances
            RecurringInvoiceLedger::insert($this->txn['ledgers']);

            $this->approve();

            DB::connection('tenant')->commit();

            return (object) [
                'id' => $txnId,
            ];

        } catch (\Exception $e) {

            DB::connection('tenant')->rollBack();
            //print_r($e); exit;
            if (App::environment('local')) {
                $this->errors[] = 'Error: Failed to save transaction to database.';
                $this->errors[] = 'File: '. $e->getFile();
                $this->errors[] = 'Line: '. $e->getLine();
                $this->errors[] = 'Message: ' . $e->getMessage();
            } else {
                $this->errors[] = 'Fatal Internal Error: Failed to save transaction to database. Please contact Admin';
			}

            return false;
        }

    }

}

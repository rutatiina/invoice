<?php

namespace Rutatiina\Invoice\Http\Controllers;

use Rutatiina\Invoice\Models\InvoiceRecurringSetting;
use URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\View;
use Rutatiina\Invoice\Models\InvoiceRecurring;
use Rutatiina\Contact\Traits\ContactTrait;
use Rutatiina\FinancialAccounting\Traits\FinancialAccountingTrait;
use Yajra\DataTables\Facades\DataTables;

use Rutatiina\Invoice\Classes\Recurring\Store as TxnStore;
use Rutatiina\Invoice\Classes\Recurring\Approve as TxnApprove;
use Rutatiina\Invoice\Classes\Recurring\Read as TxnRead;
use Rutatiina\Invoice\Classes\Recurring\Copy as TxnCopy;
use Rutatiina\Invoice\Traits\Recurring\Item as TxnItem;
use Rutatiina\Invoice\Classes\Recurring\Edit as TxnEdit;
use Rutatiina\Invoice\Classes\Recurring\Update as TxnUpdate;

class InvoiceRecurringController extends Controller
{
    use FinancialAccountingTrait;
    use ContactTrait;
    use TxnItem; // >> get the item attributes template << !!important

    public function __construct()
    {
        $this->middleware('permission:recurring-invoices.view');
		$this->middleware('permission:recurring-invoices.create', ['only' => ['create','store']]);
		$this->middleware('permission:recurring-invoices.update', ['only' => ['edit','update']]);
		$this->middleware('permission:recurring-invoices.delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson()) {
            return view('l-limitless-bs4.layout_2-ltr-default.appVue');
        }

        $query = InvoiceRecurring::query();
        $query->with('recurring');

        if ($request->contact)
        {
            $query->where(function($q) use ($request) {
                $q->where('debit_contact_id', $request->contact);
                $q->orWhere('credit_contact_id', $request->contact);
            });
        }

        $txns = $query->latest()->paginate($request->input('per_page', 20));

        return [
            'tableData' => $txns
        ];
    }

    private function nextNumber()
    {
        $txn = InvoiceRecurring::latest()->first();
        $settings = InvoiceRecurringSetting::first();

        return $settings->number_prefix.(str_pad((optional($txn)->number+1), $settings->minimum_number_length, "0", STR_PAD_LEFT)).$settings->number_postfix;
    }

    public function create()
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson()) {
            return view('l-limitless-bs4.layout_2-ltr-default.appVue');
        }

        $tenant = Auth::user()->tenant;

        $txnAttributes = (new InvoiceRecurring)->rgGetAttributes();

        $txnAttributes['number'] = $this->nextNumber();

        $txnAttributes['status'] = 'Approved';
        $txnAttributes['contact_id'] = '';
        $txnAttributes['contact'] = json_decode('{"currencies":[]}'); #required
        $txnAttributes['date'] = date('Y-m-d');
        $txnAttributes['base_currency'] = $tenant->base_currency;
        $txnAttributes['quote_currency'] = $tenant->base_currency;
        $txnAttributes['taxes'] = json_decode('{}');
        $txnAttributes['isRecurring'] = true;
        $txnAttributes['recurring'] = [
            'status' => 'active',
            'frequency' => 'monthly',
            'date_range' => [], //used by vue
            'start_date' => '',
            'end_date' => '',
            'day_of_month' => '*',
            'month' => '*',
            'day_of_week' => '*',
        ];
        $txnAttributes['contact_notes'] = null;
        $txnAttributes['terms_and_conditions'] = null;
        $txnAttributes['items'] = [$this->itemCreate()];

        unset($txnAttributes['txn_entree_id']); //!important
        unset($txnAttributes['txn_type_id']); //!important
        unset($txnAttributes['debit_contact_id']); //!important
        unset($txnAttributes['credit_contact_id']); //!important

        $data = [
            'pageTitle' => 'Create Recurring Invoice', #required
            'pageAction' => 'Create', #required
            'txnUrlStore' => '/recurring-invoices', #required
            'txnAttributes' => $txnAttributes, #required
        ];

        return $data;
    }

    public function store(Request $request)
	{
	    //return $request;

        $TxnStore = new TxnStore();
        $TxnStore->txnInsertData = $request->all();
        $insert = $TxnStore->run();

        if ($insert == false) {
            return [
                'status'    => false,
                'messages'   => $TxnStore->errors
            ];
        }

        return [
            'status'    => true,
            'messages'   => ['Recurring Invoice saved'],
            'number'    => 0,
            'callback'  => URL::route('recurring-invoices.show', [$insert->id], false)
        ];

    }

    public function show($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson()) {
            return view('l-limitless-bs4.layout_2-ltr-default.appVue');
        }

        if (FacadesRequest::wantsJson()) {
            $TxnRead = new TxnRead();
            return $TxnRead->run($id);
        }
    }

    public function edit($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson()) {
            return view('l-limitless-bs4.layout_2-ltr-default.appVue');
        }

        $TxnEdit = new TxnEdit();
        $txnAttributes = $TxnEdit->run($id);

        $data = [
            'pageTitle' => 'Edit Recurring Invoice', #required
            'pageAction' => 'Edit', #required
            'txnUrlStore' => '/recurring-invoices/'.$id, #required
            'txnAttributes' => $txnAttributes, #required
        ];

        if (FacadesRequest::wantsJson()) {
            return $data;
        }
    }

    public function update(Request $request)
    {
        //print_r($request->all()); exit;

        $TxnStore = new TxnUpdate();
        $TxnStore->txnInsertData = $request->all();
        $insert = $TxnStore->run();

        if ($insert == false) {
            return [
                'status'    => false,
                'messages'  => $TxnStore->errors
            ];
        }

        return [
            'status'    => true,
            'messages'  => ['Recurring Invoice updated'],
            'number'    => 0,
            'callback'  => URL::route('recurring-invoices.show', [$insert->id], false)
        ];
    }

    public function destroy($id)
	{
		$delete = Transaction::delete($id);

		if ($delete) {
			return [
				'status' => true,
				'message' => 'Recurring Invoice deleted',
			];
		} else {
			return [
				'status' => false,
				'message' => implode('<br>', array_values(Transaction::$rg_errors))
			];
		}
	}

	#-----------------------------------------------------------------------------------

    public function approve($id)
    {
        $TxnApprove = new TxnApprove();
        $approve = $TxnApprove->run($id);

        if ($approve == false) {
            return [
                'status'    => false,
                'messages'   => $TxnApprove->errors
            ];
        }

        return [
            'status'    => true,
            'messages'   => ['Recurring Invoice Approved'],
        ];

    }

    public function copy($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson()) {
            return view('l-limitless-bs4.layout_2-ltr-default.appVue');
        }

        $TxnCopy = new TxnCopy();
        $txnAttributes = $TxnCopy->run($id);

        $txnAttributes['number'] = $this->nextNumber();


        $data = [
            'pageTitle' => 'Copy Recurring Invoice', #required
            'pageAction' => 'Copy', #required
            'txnUrlStore' => '/accounting/sales/recurring-invoices', #required
            'txnAttributes' => $txnAttributes, #required
        ];

        if (FacadesRequest::wantsJson()) {
            return $data;
        }
    }

    public function datatables(Request $request)
	{
        $txns = Transaction::setRoute('show', route('accounting.sales.recurring-invoices.show', '_id_'))
			->setRoute('edit', route('accounting.sales.recurring-invoices.edit', '_id_'))
			->setRoute('copy', route('accounting.sales.invoices.copy', '_id_'))
			->setSortBy($request->sort_by)
			->paginate(false)
            ->returnModel(true);

        $txns->with('recurring');

        return Datatables::of($txns)->make(true);
    }

    public function exportToExcel(Request $request) {

        $txns = collect([]);

        $txns->push([
            'DATE',
            'REFERENCE',
            'CUSTOMER',
            'TOTAL',
            ' ', //Currency
        ]);

        foreach (array_reverse($request->ids) as $id) {
            $txn = Transaction::transaction($id);

            $txns->push([
                $txn->date,
                $txn->reference,
                $txn->contact_name,
                $txn->total,
                $txn->base_currency,
            ]);
        }

        $export = $txns->downloadExcel(
            'maccounts-recurring-invoices-export-'.date('Y-m-d-H-m-s').'.xlsx',
            null,
            false
        );

        //$books->load('author', 'publisher'); //of no use

        return $export;
    }

}

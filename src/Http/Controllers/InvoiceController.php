<?php

namespace Rutatiina\Invoice\Http\Controllers;

use Rutatiina\Invoice\Services\InvoiceService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Rutatiina\Invoice\Models\Invoice;
use Rutatiina\Item\Traits\ItemsSelect2DataTrait;
use Rutatiina\Contact\Traits\ContactTrait;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    use ContactTrait;
    use ItemsSelect2DataTrait;

    public function __construct()
    {
        $this->middleware('permission:invoices.view');
        $this->middleware('permission:invoices.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:invoices.update', ['only' => ['edit', 'update']]);
        $this->middleware('permission:invoices.delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        //return config('app.providers');

        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $query = Invoice::query();

        if ($request->contact)
        {
            $query->where(function ($q) use ($request)
            {
                $q->where('contact_id', $request->contact);
            });
        }

        $txns = $query->latest()->paginate($request->input('per_page', 20));

        return [
            'tableData' => $txns
        ];
    }

    public function create()
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $tenant = Auth::user()->tenant;

        $txnAttributes = (new Invoice())->rgGetAttributes();

        $txnAttributes['tenant_id'] = $tenant->id;
        $txnAttributes['created_by'] = Auth::id();
        $txnAttributes['number'] = InvoiceService::nextNumber();

        $txnAttributes['status'] = 'approved';
        $txnAttributes['contact_id'] = '';
        $txnAttributes['contact'] = json_decode('{"currencies":[]}'); #required
        $txnAttributes['date'] = date('Y-m-d');
        $txnAttributes['base_currency'] = $tenant->base_currency;
        $txnAttributes['quote_currency'] = $tenant->base_currency;
        $txnAttributes['taxes'] = json_decode('{}');
        $txnAttributes['contact_notes'] = null;
        $txnAttributes['terms_and_conditions'] = null;
        $txnAttributes['items'] = [
            [
                'selectedTaxes' => [], #required
                'selectedItem' => json_decode('{}'), #required
                'displayTotal' => 0,
                'name' => '',
                'description' => '',
                'rate' => 0,
                'quantity' => 1,
                'total' => 0,
                'taxes' => [],

                'type' => '',
                'type_id' => '',
                'contact_id' => '',
                'tax_id' => '',
                'units' => '',
                'batch' => '',
                'expiry' => ''
            ]
        ];

        return [
            'pageTitle' => 'Create Invoice', #required
            'pageAction' => 'Create', #required
            'txnUrlStore' => '/invoices', #required
            'txnAttributes' => $txnAttributes, #required
        ];
    }

    public function store(Request $request)
    {
        $storeService = InvoiceService::store($request);

        if ($storeService == false)
        {
            return [
                'status' => false,
                'messages' => InvoiceService::$errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Invoice saved'],
            'number' => 0,
            'callback' => URL::route('invoices.show', [$storeService->id], false)
        ];
    }

    public function show($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $txn = Invoice::findOrFail($id);
        $txn->load('contact', 'items.taxes', 'ledgers');
        $txn->setAppends([
            'taxes',
            'number_string',
            'total_in_words',
        ]);

        return $txn->toArray();
    }

    public function edit($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $txnAttributes = InvoiceService::edit($id);

        return [
            'pageTitle' => 'Edit Invoice', #required
            'pageAction' => 'Edit', #required
            'txnUrlStore' => '/invoices/' . $id, #required
            'txnAttributes' => $txnAttributes, #required
        ];
    }

    public function update(Request $request)
    {
        //print_r($request->all()); exit;

        $storeService = InvoiceService::update($request);

        if ($storeService == false)
        {
            return [
                'status' => false,
                'messages' => InvoiceService::$errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Invoice updated'],
            'number' => 0,
            'callback' => URL::route('invoices.show', [$storeService->id], false)
        ];
    }

    public function destroy($id)
    {
        $destroy = InvoiceService::destroy($id);

        if ($destroy)
        {
            return [
                'status' => true,
                'messages' => ['Invoice deleted'],
                'callback' => URL::route('invoices.index', [], false)
            ];
        }
        else
        {
            return [
                'status' => false,
                'messages' => InvoiceService::$errors
            ];
        }
    }

    #-----------------------------------------------------------------------------------

    public function approve($id)
    {
        $approve = InvoiceService::approve($id);

        if ($approve == false)
        {
            return [
                'status' => false,
                'messages' => InvoiceService::$errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Invoice Approved'],
        ];

    }

    public function copy($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $txnAttributes = InvoiceService::copy($id);

        return [
            'pageTitle' => 'Copy Invoices', #required
            'pageAction' => 'Copy', #required
            'txnUrlStore' => '/invoices', #required
            'txnAttributes' => $txnAttributes, #required
        ];
    }

    public function datatables(Request $request)
    {

        $txns = Transaction::setRoute('show', route('accounting.sales.invoices.show', '_id_'))
            ->setRoute('edit', route('accounting.sales.invoices.edit', '_id_'))
            ->setSortBy($request->sort_by)
            ->paginate(false);

        return Datatables::of($txns)->make(true);
    }

    public function exportToExcel(Request $request)
    {

        $txns = collect([]);

        $txns->push([
            'DATE',
            'DOCUMENT#',
            'REFERENCE',
            'CUSTOMER',
            'STATUS',
            'DUE DATE',
            'TOTAL',
            'BALANCE',
            ' ', //Currency
        ]);

        foreach (array_reverse($request->ids) as $id)
        {
            $txn = Transaction::transaction($id);

            $txns->push([
                $txn->date,
                $txn->number,
                $txn->reference,
                $txn->contact_name,
                $txn->status,
                $txn->expiry_date,
                $txn->total,
                'balance' => $txn->balance,
                'base_currency' => $txn->base_currency,
            ]);
        }

        $export = $txns->downloadExcel(
            'maccounts-invoices-export-' . date('Y-m-d-H-m-s') . '.xlsx',
            null,
            false
        );

        //$books->load('author', 'publisher'); //of no use

        return $export;
    }

}

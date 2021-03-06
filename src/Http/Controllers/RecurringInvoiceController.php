<?php

namespace Rutatiina\Invoice\Http\Controllers;

use Rutatiina\Invoice\Services\RecurringInvoiceService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Rutatiina\Invoice\Models\RecurringInvoice;

class RecurringInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:recurring-invoices.view');
        $this->middleware('permission:recurring-invoices.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:recurring-invoices.update', ['only' => ['edit', 'update']]);
        $this->middleware('permission:recurring-invoices.delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $query = RecurringInvoice::query();

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

        $txnAttributes = (new RecurringInvoice)->rgGetAttributes();

        $txnAttributes['status'] = 'active';
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
            'pageTitle' => 'Create Recurring Invoice', #required
            'pageAction' => 'Create', #required
            'txnUrlStore' => '/recurring-invoices', #required
            'txnAttributes' => $txnAttributes, #required
        ];
    }

    public function store(Request $request)
    {
        //return $request;

        $storeService = RecurringInvoiceService::store($request);

        if ($storeService == false)
        {
            return [
                'status' => false,
                'messages' => RecurringInvoiceService::$errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Recurring Invoice saved'],
            'number' => 0,
            'callback' => URL::route('recurring-invoices.show', [$storeService->id], false)
        ];

    }

    public function show($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $txn = RecurringInvoice::findOrFail($id);
        $txn->load('contact', 'items.taxes');
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

        $txnAttributes = RecurringInvoiceService::edit($id);

        return [
            'pageTitle' => 'Edit Recurring Invoice', #required
            'pageAction' => 'Edit', #required
            'txnUrlStore' => '/recurring-invoices/' . $id, #required
            'txnAttributes' => $txnAttributes, #required
        ];
    }

    public function update(Request $request)
    {
        //print_r($request->all()); exit;

        $updateService = RecurringInvoiceService::update($request);

        if ($updateService == false)
        {
            return [
                'status' => false,
                'messages' => RecurringInvoiceService::$errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Recurring Invoice updated'],
            'number' => 0,
            'callback' => URL::route('recurring-invoices.show', [$updateService->id], false)
        ];
    }

    public function destroy($id)
    {
        $destroy = RecurringInvoiceService::destroy($id);

        if ($destroy)
        {
            return [
                'status' => true,
                'messages' => 'Recurring Invoice deleted',
            ];
        }
        else
        {
            return [
                'status' => false,
                'messages' => RecurringInvoiceService::$errors
            ];
        }
    }

    #-----------------------------------------------------------------------------------

    public function activate($id)
    {
        $approve = RecurringInvoiceService::activate($id);

        if ($approve == false)
        {
            return [
                'status' => false,
                'messages' => RecurringInvoiceService::$errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Recurring invoice activated'],
        ];

    }

    public function copy($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $txnAttributes = RecurringInvoiceService::copy($id);

        return [
            'pageTitle' => 'Copy Invoices', #required
            'pageAction' => 'Copy', #required
            'txnUrlStore' => '/invoices', #required
            'txnAttributes' => $txnAttributes, #required
        ];
    }

}

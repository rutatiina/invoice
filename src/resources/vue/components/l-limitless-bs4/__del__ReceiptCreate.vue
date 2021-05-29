<template>

    <!-- Main content -->
    <div class="content-wrapper">

        <!-- Page header -->
        <div class="page-header page-header-light">
            <div class="page-header-content header-elements-md-inline">
                <div class="page-title d-flex">
                    <h4>
                        <!--<i class="icon-arrow-left52 mr-2"></i>-->
                        <span class="font-weight-semibold">Payment</span> for Invoice #{{$route.params.id}}
                    </h4>
                    <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                </div>

            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="index.html" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Accounting</a>
                        <a href="form_inputs.html" class="breadcrumb-item">Sales</a>
                        <a href="form_inputs.html" class="breadcrumb-item">Invoices</a>
                        <a href="form_inputs.html" class="breadcrumb-item">Receipt</a>
                        <span class="breadcrumb-item active">Create</span>
                    </div>

                    <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                </div>

            </div>

        </div>
        <!-- /page header -->


        <!-- Content area -->
        <div class="content max-width-960">

            <!-- Form inputs -->

            <form  @submit="formSubmit" action="#" autocomplete="off">

                <div class="card shadow-none rounded-0 border-left-2 border-left-danger border-top-0 border-right-0 border-bottom-0">
                    <div class="card-body pt-1 pb-1">

                        <fieldset class="">

                            <div class="form-group row mb-0">
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="">Customer</label>
                                            <input type="text" v-model="fields.contact_name" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="">Payment #</label>
                                            <input type="text" v-model="fields.number" class="form-control">
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </fieldset>

                    </div>
                </div>

                <div class="card shadow-none rounded-0 border-left-2 border-left-blue border-top-0 border-right-0 border-bottom-0">

                    <div class="card-body pt-1 pb-1">

                        <fieldset class="">

                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Amount received</label>
                                <div class="col-lg-8">
                                    <div class="input-group">
											<span class="input-group-prepend">
												<span class="input-group-text">{{fields.base_currency}}</span>
											</span>
                                        <input type="number"
                                               v-model.number="fields.items[0].rate"
                                               v-on:change="onItemRateChange"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Bank charges (If any)</label>
                                <div class="col-lg-8">
                                    <div class="input-group">
											<span class="input-group-prepend">
												<span class="input-group-text">{{fields.base_currency}}</span>
											</span>
                                        <input type="number"
                                               v-model.number="fields.bank_charge"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="form-group row"
                                 v-for="(tax, index)  in fields.items[0].taxes"
                                 v-if="fields.items[0].taxes.length > 0">
                                <label class="col-form-label col-lg-4">Tax: {{tax.name}}</label>
                                <div class="col-lg-5">
                                    <select v-model="fields.items[0].taxes[index].id"
                                            :data-item-tax-index="index"
                                            :data-value="tax.id"
                                            data-allow-clear="true"
                                            class="form-control select-tax text-capitalize">
                                        <option></option>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <div class="input-group">
											<span class="input-group-prepend">
												<span class="input-group-text">{{fields.base_currency}}</span>
											</span>
                                        <input type="number"
                                               v-model="fields.items[0].taxes[index].total"
                                               :data-item-tax-index="index"
                                               v-on:change="onItemTaxTotalChange(index)"
                                               class="form-control"
                                               placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-4"> </label>
                                <div class="col-lg-8">
                                    <button type="button"
                                            @click="itemTaxesAdd"
                                            class="btn btn-link">
                                        Add Tax - <small>Click to add tax and tax amount</small>
                                    </button>
                                </div>
                            </div>

                            <!-- todo feature to be added -->
                            <div class="form-group row d-none">
                                <label class="col-form-label col-lg-4">Tax deducted</label>
                                <div class="col-lg-8">

                                    <div class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="custom-stacked-radio" id="custom_radio_stacked_unchecked" checked>
                                        <label class="custom-control-label" for="custom_radio_stacked_unchecked">Custom selected</label>
                                    </div>

                                    <div class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="custom-stacked-radio" id="custom_radio_stacked_checked">
                                        <label class="custom-control-label" for="custom_radio_stacked_checked">Yes TDS <small>(Tax Deducted at Source)</small></label>
                                    </div>

                                </div>
                            </div>

                            <!-- todo feature to be added -->
                            <div class="form-group row d-none">
                                <label class="col-form-label col-lg-4">TDS Tax Account</label>
                                <div class="col-lg-8">
                                    <!--
                                    <select v-model="fields.tds_tax_account" class="form-control select-search text-capitalize">
                                        <optgroup v-for="(accounts, type) in accountsByType"
                                                  :label="type + ' account(s)'"
                                                  v-if="type=='Asset' || type=='Equity' || type=='Liability'"
                                                  class="text-capitalize">
                                            <option v-for="account in accounts"
                                                    :value="account.id">{{account.name}}</option>
                                        </optgroup>
                                    </select>
                                    -->
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <label class="col-form-label col-lg-4">Amount withheld</label>
                                <div class="col-lg-8">
                                    <div class="input-group">
											<span class="input-group-prepend">
												<span class="input-group-text">{{fields.base_currency}}</span>
											</span>
                                        <input type="number" v-model="fields.amount_withheld" class="form-control" placeholder="0.00">
                                    </div>

                                </div>
                            </div>

                        </fieldset>

                    </div>
                </div>

                <div class="card shadow-none rounded-0 border-left-2 border-left-grey-300 border-top-0 border-right-0 border-bottom-0">
                    <div class="card-body  pt-1 pb-1">

                        <fieldset class="mb-3">

                            <div class="form-group row mb-0">

                                <div class="col-md-4">
                                    <label class="">Payment Date</label>
                                    <date-picker v-model="fields.date"
                                                 valueType="format"
                                                 :lang="vue2DatePicker.lang"
                                                 class="font-weight-bold w-100"
                                                 style="height: 37px;"
                                                 placeholder="Invoice date">
                                    </date-picker>
                                </div>

                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="">Payment Mode</label>
                                            <model-select
                                                :options="txnPaymentModes"
                                                v-model="fields.payment_mode"
                                                placeholder="Select payment mode">
                                            </model-select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="">Deposit to</label>
                                            <model-list-select :list="receiptDebitAccounts"
                                                               v-model="fields.debit"
                                                               option-value="id"
                                                               option-text="name"
                                                               class=""
                                                               placeholder="select account">
                                            </model-list-select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </fieldset>

                        <fieldset class="">

                            <div class="form-group row">

                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="">Reference</label>
                                            <input type="text" v-model="fields.reference" class="form-control">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="form-group row mb-0">

                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="">Notes</label>
                                            <textarea v-model="fields.notes" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </fieldset>

                    </div>
                </div>

                <div class="card shadow-none rounded-0 border-0">
                    <div class="card-body  pt-1 pb-1">

                        <fieldset class="mb-3">

                            <div class="form-group">

                                <button type="submit" class="btn btn-danger"><i class="icon-comment-discussion mr-2"></i> Create Invoice Receipt</button>

                            </div>

                        </fieldset>

                    </div>
                </div>

            </form>

            <!-- /form inputs -->

        </div>
        <!-- /content area -->


        <!-- Footer -->
        <!-- /footer -->

    </div>
    <!-- /main content -->

</template>

<script>

    export default {
        data() {
            return {
                invoiceId: this.$route.params.id,
                invoice: {},
                bankAccounts: [],
                accountsByType: [],
                receiptDebitAccounts: [],
                taxes: [],
                fields: {
                    number: this.$route.params.id,
                    items: [
                        {
                            batch: null,
                            contact_id: null,
                            description: null,
                            name: null,
                            quantity: 0,
                            rate: 0,
                            tax_id: null,
                            taxes: [],
                            total: 0,
                            type: null,
                            type_id: null,
                            units: null
                        }
                    ]
                },
                response: {}
            }
        },
        mounted() {
            this.fetchReceiptDebitAccounts()
            this.txnFetchPaymentModes()

            //console.log('Component mounted :: axios before');

            axios.get('/invoices/'+this.invoiceId+'/receipts/create')
                .then((response) => {

                    this.fields = response.data.receiptFieldDefaults
                    this.bankAccounts = response.data.bankAccounts
                    this.accountsByType = response.data.accountsByType
                    this.taxes = response.data.taxes
                    this.invoice = response.data.invoice

                })
        },
        methods: {
            async fetchReceiptDebitAccounts() {

                try {

                    return await axios.post('/receipts/debit-accounts')
                        .then(response => {
                            this.receiptDebitAccounts = response.data
                        })
                        .catch(function (error) {
                            // handle error
                            console.log(error); //test
                        })
                        .finally(function (response) {
                            // always executed this is supposed
                        })

                } catch (e) {
                    console.log(e); //test
                }
            },
            onItemRateChange() {

                this.fields.taxable_amount = this.fields.items[0].rate

                this.updateTotal();

                /*
                this.fields.items[0].taxes = [
                    {
                        id: null,
                        name: null,
                        total: 0,
                        inclusive: 0,
                        exclusive: 0
                    }
                ]
                */

                //console.log('onItemRateChange')
            },
            updateTotal() {
                this.fields.total = this.fields.items[0].rate
                this.fields.items[0].total = this.fields.items[0].rate

                //console.log('updateTotal')
            },
            onItemTaxTotalChange(itemsTaxesIndex) {

                var taxId = this.fields.items[0].taxes[itemsTaxesIndex].id
                //console.log(taxId)

                if (taxId === null) {
                    return false;
                }

                if(this.taxes[taxId].method === 'inclusive') {
                    this.fields.items[0].taxes[itemsTaxesIndex].inclusive = this.fields.items[0].taxes[itemsTaxesIndex].total
                } else {
                    this.fields.items[0].taxes[itemsTaxesIndex].exclusive = this.fields.items[0].taxes[itemsTaxesIndex].total
                }


                console.log('onItemTaxTotalChange')
            },
            itemTaxesAdd() {
                this.fields.items[0].taxes.push({
                    id: null,
                    name: null,
                    total: 0,
                    inclusive: 0,
                    exclusive: 0
                });
            },
            formSubmit(e) {

                e.preventDefault();

                let currentObj = this;

                PNotify.removeAll();

                let PNotifySettings = {
                    title: 'Close on click',
                    text: 'Loading .....',
                    addclass: 'bg-warning-400 border-warning-400',
                    hide: false,
                    buttons: {
                        closer: false,
                        sticker: false
                    }
                };

                new PNotify(PNotifySettings);

                axios.post('/invoices/'+this.invoiceId+'/receipts', this.fields)
                    .then((response) => {

                        PNotify.removeAll();

                        PNotifySettings.text = response.data.message;

                        if(response.data.status === true) {
                            PNotifySettings.addclass = 'bg-success-400 border-success-400'

                            let notice = new PNotify(PNotifySettings);
                            notice.get().click(function() {notice.remove() })

                            this.$router.push({ path: response.data.callback })
                        } else {
                            PNotifySettings.addclass = 'bg-warning-400 border-warning-400'
                            let notice = new PNotify(PNotifySettings);
                            notice.get().click(function() {notice.remove() })
                        }
                    })
            },
            taxValue (taxRateOrValue, taxableAmount, taxMethod ) {
                //console.log(tax_value);
                //console.log('type of tax: ' + inclusive);
                if (typeof taxRateOrValue === 'undefined') return 0;

                if (taxRateOrValue.length > 0) {

                    if (taxRateOrValue.substr(-1, 1) == '%') {

                        var tax = taxRateOrValue.substr(0, taxRateOrValue.length-1);

                        if ( isNaN(tax) ) {
                            return 0;
                        }

                        if (taxMethod === 'inclusive') {
                            return (taxableAmount - (taxableAmount / (1 + (Number(tax) / 100)) ) );
                        }

                        return ( taxableAmount * (Number(tax)  / 100) );
                    }
                    else {
                        return Number(taxRateOrValue);
                    }

                }

                return 0;
            }
        }
    }
</script>

<template>

    <!-- Main content -->
    <div class="content-wrapper">

        <!-- Page header -->
        <div class="page-header page-header-light">
            <div class="page-header-content header-elements-md-inline">
                <div class="page-title d-flex">
                    <h4>
                        <i class="icon-file-plus"></i>
                        {{pageTitle}}
                    </h4>
                </div>

            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="/" class="breadcrumb-item">
                            <i class="icon-home2 mr-2"></i>
                            <span class="badge badge-primary badge-pill font-weight-bold rg-breadcrumb-item-tenant-name"> {{this.$root.tenant.name | truncate(30) }} </span>
                        </a>
                        <span class="breadcrumb-item">Accounting</span>
                        <span class="breadcrumb-item">Sales</span>
                        <span class="breadcrumb-item">Recurring Invoices</span>
                        <span class="breadcrumb-item active">{{pageAction}}</span>
                    </div>

                    <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                </div>

                <div class="header-elements">
                    <div class="breadcrumb justify-content-center">
                        <router-link :to="txnUrlStore" class=" btn btn-danger btn-sm rounded-round font-weight-bold">
                            <i class="icon-drawer3 mr-2"></i>
                            Recurring Invoices
                        </router-link>
                    </div>
                </div>

            </div>

        </div>
        <!-- /page header -->

        <!-- Content area -->
        <div class="content border-0 padding-0">

            <!-- Form horizontal -->
            <div class="card shadow-none rounded-0 border-0">

                <div class="card-body p-0">

                    <loading-animation></loading-animation>

                    <form v-if="!this.$root.loading"
                          @submit="txnFormSubmit"
                          action=""
                          method="post"
                          class="max-width-1040"
                          style="margin-bottom: 100px;"
                          autocomplete="off">


                        <input type="hidden" name="submit" value="1" />
                        <input type="hidden" name="id" :value="txnAttributes.id" />
                        <input type="hidden" name="contact_name" value="" />
                        <input type="hidden" name="internal_ref" :value="txnAttributes.internal_ref" />
                        <input type="hidden" name="quote_currency" :value="$root.tenant.base_currency" />

                        <fieldset id="fieldset_select_contact" class="select_contact_required">

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2 text-danger font-weight-bold">Customer</label>
                                <div class="col-lg-5">
                                    <model-list-select :list="txnContacts"
                                                       v-model="txnAttributes.contact"
                                                       @searchchange="txnFetchCustomers"
                                                       @input="txnContactSelect"
                                                       option-value="id"
                                                       option-text="display_name"
                                                       placeholder="select contact">
                                    </model-list-select>
                                </div>

                                <div v-show="txnAttributes.contact_id" class="col-lg-1 p-0" >
                                    <model-list-select :list="txnAttributes.contact.currencies"
                                                       v-model="txnAttributes.contact.currency"
                                                       option-value="code"
                                                       option-text="code"
                                                       placeholder="select currency">
                                    </model-list-select>
                                </div>

                                <div class="col-lg-3 pr-0"
                                     v-show="txnAttributes.contact_id && txnAttributes.base_currency != txnAttributes.quote_currency">
                                    <div class="input-group">
											<span class="input-group-prepend">
												<span class="input-group-text">Exchange rate:</span>
											</span>
                                        <input type="text"
                                               v-model="txnAttributes.exchange_rate"
                                               class="form-control text-right"
                                               placeholder="Exchange rate">
                                    </div>
                                </div>

                            </div>

                        </fieldset>

                        <fieldset class="">

                            <div class="form-group row">
                                <label class="col-lg-2 col-form-label">
                                    Ref / Order No.
                                </label>
                                <div class="col-lg-5">
                                    <input type="text" name="reference" v-model="txnAttributes.reference" class="form-control input-roundless" placeholder="Enter reference">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-2 col-form-label font-weight-bold">
                                    Payment terms:
                                </label>
                                <div class="col-lg-5">
                                    <model-select
                                        :options="txnPaymentTerms"
                                        v-model="txnAttributes.payment_terms"
                                        placeholder="Select payment terms">
                                    </model-select>
                                </div>
                            </div>

                        </fieldset>
                        <!--<div class="max-width-1040 clearfix ml-20" style="border-bottom: 1px solid #ddd;"></div>-->

                        <txn-recurring-fields></txn-recurring-fields>

                        <fieldset class="">
                            <div class="form-group row">
                                <table class="table table-bordered border-left-0 border-right-0 border-bottom-0">
                                    <thead class="thead-default bg-light">
                                        <tr>
                                            <th width="50%" class="font-weight-bold">Item / description</th>
                                            <th width="8%" class="text-right font-weight-bold">Quantity</th>
                                            <th width="11%" class="text-right font-weight-bold">Rate</th>
                                            <th width="13%" class="font-weight-bold">Tax</th>
                                            <th width="18%" class="text-right font-weight-bold p-0">
                                                <div class="input-group">
                                                    <input type="text"
                                                           value="Total"
                                                           readonly
                                                           class="rg-txn-item-row-total form-control border-0 text-right font-weight-bold bg-transparent"
                                                           placeholder="0.00">
                                                    <span class="input-group-append border-0 rounded-0">
                                                        <button type="button"
                                                                @click="txnItemsClearAll"
                                                                class="btn bg-danger bg-transparent text-danger btn-icon"
                                                                title="Clear all items">
                                                            <i class="icon-cross3"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="items_field_rows">

                                        <tr v-for="(item, index) in txnAttributes.items">
                                            <td class="td_item_selector p-0 rg_select2_border_none">

                                                <model-list-select :list="txnItems"
                                                                   v-model="item.selectedItem"
                                                                   option-value="id"
                                                                   option-text="name"
                                                                   :option-item-row="index"
                                                                   option-tag
                                                                   @input="txnItemsSelect"
                                                                   @searchchange="txnFetchItems"
                                                                   class="border-0"
                                                                   placeholder="Select item">
                                                </model-list-select>

                                                <div v-show="item.type_id || item.name" class="ml-2 mr-2">
                                                    <textarea v-model="item.description"
                                                              :data-value="item.description"
                                                              :data-row="index"
                                                              rows="1"
                                                              class="form-control mb-2"
                                                              onkeyup="rg_auto_grow(this);"
                                                              placeholder="Description"
                                                              style="min-height: 30px;overflow: hidden;"></textarea>
                                                </div>
                                            </td>
                                            <td class="p-0">
                                                <vue-numeric separator=","
                                                             :min="0"
                                                             v-model="item.quantity"
                                                             @input="txnTotal"
                                                             class="item_row_quantity form-control border-0 text-right"></vue-numeric>
                                            </td>
                                            <td class="p-0">
                                                <vue-numeric separator=","
                                                             :min="0"
                                                             v-model="item.rate"
                                                             @input="txnTotal"
                                                             class="item_row_rate form-control m-input border-0 text-right"></vue-numeric>
                                            </td>
                                            <td class="p-0">
                                                <multi-list-select
                                                    :list="txnTaxes"
                                                    option-value="code"
                                                    option-text="display_name"
                                                    :option-item-row="index"
                                                    class="border-0"
                                                    :selected-items="item.selectedTaxes"
                                                    placeholder="select tax"
                                                    show-count-of-selected-options
                                                    @select="txnItemTaxes">
                                                </multi-list-select>

                                            </td>
                                            <td class="p-0">
                                                <div style="position: relative">
                                                    <div class="input-group">
                                                        <input type="text"
                                                               :value="rgNumberFormat(item.total, 2)"
                                                               class="rg-txn-item-row-total form-control border-0 text-right bg-transparent"
                                                               readonly
                                                               placeholder="0.00">
                                                        <span class="input-group-append border-0 rounded-0">
                                                            <button type="button"
                                                                    @click="txnItemsRemove(index)"
                                                                    class="btn bg-danger bg-transparent text-danger btn-icon"
                                                                    title="Delete row">
                                                                <i class="icon-cross3"></i>
                                                            </button>
                                                        </span>
                                                    </div>

                                                </div>
                                            </td>
                                        </tr>

                                    </tbody>

                                    <tbody>
                                        <tr>
                                            <td class="border-0">
                                                <button type="button" class="btn btn-link pt-0 pb-0 font-weight-bold" @click="txnItemsCreate">
                                                    <i class="icon-plus2 mr-2"></i> Add another line
                                                </button>
                                            </td>
                                            <td class="pl-15 border-left-0 border-top-0 border-right-0 font-weight-bold" colspan="2">Sub Total</td>
                                            <td id="txn_subtotal" class="border-left-0 border-top-0 border-right-0 text-right rg-txn-total-pr" colspan="2">
                                                {{rgNumberFormat(txnAttributes.taxable_amount, 2)}}
                                            </td>
                                        </tr>
                                    </tbody>

                                    <tbody class="border-0">

                                    <tr v-for="tax in txnAttributes.taxes">
                                        <td class="p-15 border-0"></td>
                                        <td class="p-15 border-left-0 border-top-0 border-right-0 font-weight-bold" colspan="2">{{tax.name}}</td>
                                        <td class="border-left-0 border-top-0 border-right-0 text-right p-0" colspan="2">
                                            <!--{{rgNumberFormat(tax.total, 2, '.', '')}}-->
                                            <div class="input-group">
                                                <input type="text"
                                                       :value="rgNumberFormat(tax.total, 2)"
                                                       class="rg-txn-item-row-total form-control border-0 text-right bg-transparent"
                                                       readonly
                                                       placeholder="0.00">
                                                <span class="input-group-append border-0 rounded-0">
                                                    <button type="button"
                                                            @click="txnItemsTaxRemove(tax.code)"
                                                            class="btn bg-danger bg-transparent text-danger btn-icon"
                                                            title="Remove Tax">
                                                        <i class="icon-cross3"></i>
                                                    </button>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                    </tbody>

                                    <tfoot>

                                        <tr>
                                            <td class="p-15 border-0"></td>
                                            <td class="p-15 border-left-0 border-top-0 border-right-0 font-weight-bold size4of5" colspan="2">
                                                TOTAL
                                                <span v-if="txnAttributes.base_currency"
                                                      class="badge badge-primary badge-pill font-weight-bold rg-breadcrumb-item-tenant-name">
                                                    {{txnAttributes.base_currency}}
                                                </span>
                                            </td>
                                            <td id="txn_total" class="border-left-0 border-top-0 border-right-0 font-weight-bold size4of5 text-right rg-txn-total-pr" colspan="2">
                                                {{rgNumberFormat(txnAttributes.total, 2)}}
                                            </td>
                                        </tr>

                                    </tfoot>

                                </table>
                            </div>
                        </fieldset>


                        <fieldset class="">

                            <!--https://stackoverflow.com/questions/53409139/how-to-upload-multiple-images-files-with-javascript-and-axios-formdata-->
                            <!--https://laracasts.com/discuss/channels/vue/upload-multiple-files-and-relate-them-to-post-model-->
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Attach files</label>
                                <div class="col-lg-10">
                                    <input ref="filesAttached" type="file" multiple class="form-control border-0 p-1 h-auto">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-2 col-form-label">
                                    Customer notes:
                                </label>
                                <div class="col-lg-10">
                                    <textarea v-model="txnAttributes.contact_notes" class="form-control input-roundless" rows="2" placeholder="Contact notes"></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-lg-2 col-form-label">
                                    Terms and conditions:
                                </label>
                                <div class="col-lg-10">
                                    <textarea v-model="txnAttributes.terms_and_conditions" class="form-control input-roundless" rows="2" placeholder="Mention your company's Terms and Conditions."></textarea>
                                </div>
                            </div>

                        </fieldset>

                        <div class="text-left col-md-10 offset-md-2 p-0">

                            <div class="btn-group ml-1">
                                <button type="button" class="btn btn-outline bg-purple-300 border-purple-300 text-purple-800 btn-icon border-2 dropdown-toggle" data-toggle="dropdown">
                                    <i class="icon-cog"></i>
                                </button>

                                <div class="dropdown-menu dropdown-menu-left">
                                    <a href="/" class="dropdown-item" v-on:click.prevent="txnOnSave('draft', false)">
                                        <i class="icon-file-text3"></i> Save as draft
                                    </a>
                                    <a href="/" class="dropdown-item" v-on:click.prevent="txnOnSave('approved', false)">
                                        <i class="icon-file-check2"></i> Save and approve
                                    </a>
                                    <a href="/" class="dropdown-item" v-on:click.prevent="txnOnSave('approved', true)">
                                        <i class="icon-mention"></i> Save, approve and send
                                    </a>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-danger font-weight-bold">
                                <i class="icon-file-plus2 mr-1"></i> {{txnSubmitBtnText}}
                            </button>

                        </div>

                    </form>

                </div>
            </div>
            <!-- /form horizontal -->


        </div>
        <!-- /content area -->

    </div>
    <!-- /main content -->

</template>

<script>

    export default {
        name: 'AccoutingSalesInvoicesForm',
        components: {

        },
        data() {
            return {

            }
        },
        watch: {
            'txnAttributes.recurring.date_range': function () {
                let v = this.txnAttributes.recurring.date_range
                // console.log(v)

                //if (v.length > 0) {
                if (typeof v !== 'undefined') {
                    this.txnAttributes.recurring.start_date = v[0]
                    this.txnAttributes.recurring.end_date = v[1]
                }
            }
        },
        created: function () {
            this.pageTitle = 'Create Invoices'
            this.pageAction = 'Create'
        },
        mounted() {
            this.$root.appMenu('accounting')

            //console.log(this.$route.fullPath)
            this.txnCreateData()
            this.txnFetchCustomers('-initiate-')
            this.txnFetchItems('-initiate-')
            this.txnFetchTaxes()
            //this.txnFetchAccounts()
        },
        methods: {

        },
        beforeUpdate: function () {
            //
        },
        updated: function () {
            //this.txnComponentUpdates()
        },
        destroyed: function () {
            //
        }
    }
</script>

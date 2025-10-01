<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'details',width:90,align:'center', formatter:btnDetails">Detail</th>
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Purchase Invoice No</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center',formatter:statusformat,styler:statusStyle">Payment<br>Status</th>
            <th rowspan="2" data-options="field:'status_invoice',width:110,align:'center',formatter:statusformatInv,styler:statusStyleInv">Supplier<br>Invoice</th>
            <th rowspan="2" data-options="field:'gl_no',width:100,align:'center'">GL No</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'item_category_name',width:150,halign:'center'">Category</th>
            <th rowspan="2" data-options="field:'journal_type_name',width:150,halign:'center'">Journal Name</th>
            <th rowspan="2" data-options="field:'supplier_name',width:200,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'invoice_no',width:150,halign:'center'">Invoice No</th>
            <th rowspan="2" data-options="field:'taxes',width:80,halign:'center',align:'right'">Taxes %</th>
            <th rowspan="2" data-options="field:'payment_term',width:100,align:'center'">Payment Term <br>(Days)</th>
            <th rowspan="2" data-options="field:'due_date',width:100,align:'center'">Payment Due</th>
            <th rowspan="2" data-options="field:'currency',width:100,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'total_sub',width:120,halign:'center',align:'right',formatter: numberformat">Sub Total</th>
            <th rowspan="2" data-options="field:'total_vat',width:120,halign:'center',align:'right',formatter: numberformat">VAT</th>
            <th rowspan="2" data-options="field:'total_pph',width:120,halign:'center',align:'right',formatter: numberformat">PPH</th>
            <th rowspan="2" data-options="field:'total_grand',width:120,halign:'center',align:'right',formatter: numberformat">Grand Total</th>
            <th rowspan="2" data-options="field:'total_dp',width:120,halign:'center',align:'right',formatter: numberformat">Down Payment</th>
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center'">Remarks</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<style>
    /* AI Style Form Inputs when disabled to see the value clearly */
    input[disabled],
    select[disabled],
    textarea[disabled] {
        background-color: white !important;
        color: black !important;
        -webkit-text-fill-color: black !important;
        opacity: 1 !important;
        cursor: not-allowed; /* Standard cursor for disabled elements */
    }

    .textbox.textbox-disabled .textbox-text,
    .textbox.textbox-disabled
    {
        background-color: white !important;
        color: black !important;
        -webkit-text-fill-color: black !important;
        opacity: 1 !important;
        cursor: not-allowed;
    }

    .combo.combo-disabled .combo-text,
    .combo.combo-disabled 
    {
        background-color: white !important;
        color: black !important;
        -webkit-text-fill-color: black !important;
        opacity: 1 !important;
        cursor: not-allowed;
    }

    .combo.combo-disabled .combo-arrow,
    .datebox.textbox-disabled .textbox-addon {
        background-color: white !important;
        opacity: 1 !important;
    }
    
    input[type="checkbox"][disabled],
    input[type="radio"][disabled] {
        opacity: 1 !important;
        cursor: not-allowed;
    }
</style>

<!-- FORM FILTER DATAGRID -->
<div id="toolbar" style="height: 230px; padding: 10px;">
    <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 32%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type Date</span>
                <select style="width:60%;" id="filter_type" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="PID">Purchase Invoice Date</option>
                    <option value="PAY">Payment Due</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Invoice Date</span>
                <input style="width:30%;" id="filter_trans_date_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_trans_date_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment Due</span>
                <input style="width:30%;" id="filter_due_date_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_due_date_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_invoicing()"><i class="fa fa-print"></i> Print Invoicing</a>
            </div>
        </div>
        <div style="width: 32%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Category</span>
                <input style="width:60%;" name="filter_category_id" id="filter_category_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purhcase Invoice No</span>
                <input style="width:60%;" name="filter_purchase_invoice" id="filter_purchase_invoice" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier</span>
                <input style="width:60%;" name="filter_supplier" id="filter_supplier" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Invoice No</span>
                <input style="width:60%;" id="filter_invoice_no" class="easyui-combobox">
            </div>
        </div>
        <div style="width: 35%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Order</span>
                <input style="width:60%;" id="filter_purchase_order" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Order Receipt</span>
                <input style="width:60%;" id="filter_purchase_receipt" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier Status</span>
                <select style="width:60%;" id="filter_status_supplier" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="INVWDP">DOWN PAYMENT</option>
                    <option value="INVTMP">TEMPORARY</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment Status</span>
                <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="0">OPEN</option>
                    <option value="1">CLOSE</option>
                </select>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="excelDetail()"><i class="fa fa-file"></i> Export Excel Detail</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="excelJournal()"><i class="fa fa-file"></i> Export Excel Journal</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="exportAccurate()"><i class="fa fa-file"></i> Export Accurate</a>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
</div>

<div id="toolbar3">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append2()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit3()"><i class="fa fa-times"></i> Remove</a>
</div>

<div id="toolbarDetail">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" style="color:gray;"><i class="fa fa-plus"></i> Add</a>
</div>
<div id="toolbarDetailJournal">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" style="color:gray;"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" style="color:gray;"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- DETAIL -->
 <div id="dlg_detail" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 99%; height: 600px; padding:10px; top: 5px; left:10px;">
    <form id="frm_detail" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purchase Invoice Type</span>
                        <select style="width:60%;" id="d_type" name="d_type" required class="easyui-combobox" panelHeight="auto">
                            <option value="" selected disabled>Select Purchase Invoice Type</option>
                            <option value="purchase">Non Down Payment</option>
                            <option value="dp">Down Payment</option>
                            <option value="others">Others</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purchase Invoice Date</span>
                        <input style="width:60%;" id="d_trans_date" name="d_trans_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purchase Invoice No</span>
                        <input style="width:60%;" readonly id="d_number" name="d_number" class="easyui-textbox" data-options="prompt:'Automatic From Purchase Invoce Date'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product Category</span>
                        <input style="width:60%;" required="" name="d_category_id" id="d_category_id" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Supplier Name</span>
                        <input style="width:60%;" required="" id="d_supplier_name" name="d_supplier_name" class="easyui-textbox">
                    </div>
                    <div class="fitem" id="d_type_selection_purchase">
                        <span style="width:35%; display:inline-block;">Purchase Order Receipt</span>
                        <input style="width:60%;" required="" id="d_por_no" name="d_por_no" class="easyui-combobox">
                    </div>
                    <div class="fitem" id="d_type_selection_others">
                        <span style="width:35%; display:inline-block;">Purchase Order Misc</span>
                        <input style="width:60%;" required="" id="d_po_no" name="d_po_no" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Journal Type</span>
                        <input style="width:60%;" required="" name="d_journal_type_id" id="d_journal_type" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" disabled><i class="fa fa-search"></i> Preview Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Supplier Invoice</span>
                        <input style="width:60%;" required="" id="d_invoice_no" name="d_invoice_no" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">No Faktur Pajak</span>
                        <input style="width:60%;" id="d_faktur_no" name="d_faktur_no" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Taxes</span>
                        <input style="width:60%;" id="d_taxes" name="d_taxes" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment Term</span>
                        <input style="width:60%;" id="d_payment_term" name="d_payment_term" class="easyui-numberbox" data-options="buttonText:'Days',buttonAlign:'right'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment Due</span>
                        <input style="width:60%;" id="d_due_date" name="d_due_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Voucher</span>
                        <input style="width:60%;" id="d_voucher" name="d_voucher" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Remarks</span>
                        <input style="width:60%;" id="d_remarks" name="d_remarks" class="easyui-textbox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">&nbsp;</span>
                        <input style="width:60%;" id="d_company_id" name="d_company_id" class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
        </div>

        <table id="dgDetail" class="easyui-datagrid" style="width:100%;" title="Purchase Invoice Lists" data-options="singleSelect: true" toolbar="#toolbarDetail">
            <thead>
            <tr> <!--berubah -->
                    <th hidden rowspan="2" data-options="field:'id',width:150, editor: {type: 'textbox'}">ID</th>
                    <th rowspan="2" data-options="field:'por_no',width:150,editor: {type: 'textbox'}">POR. No</th>
                    <th rowspan="2" data-options="field:'po_no',width:150,editor: {type: 'textbox'}">PO. No</th>
                    <th rowspan="2" data-options="field:'item_rm_id',width:150,editor: {type: 'textbox'}" hidden>Product Id</th>
                    <th rowspan="2" data-options="field:'item_number',width:150,editor: {type: 'textbox', options: {required: true}}">Product No</th>
                    <th rowspan="2" data-options="field:'item_name',width:200,editor: {type: 'textbox', options: {required: true}}">Product Name</th>
                    <th rowspan="2" data-options="field:'supplier_product',width:200,editor: {type: 'textbox'}">Supplier Product</th>
                    <th rowspan="2" data-options="field:'uom',align:'center',width:80, editor: {type: 'textbox'}">UoM</th>
                    <th rowspan="2" data-options="field:'qty',width:80, formatter:numberformat, editor: {type: 'textbox'}">Qty</th>

                    <th colspan="3" data-options="field:'',align:'center'">Original Currency</th>
                    <th colspan="3" data-options="field:'',align:'center'">Local Currency</th>
                    <th rowspan="2" data-options="field:'account_number',width:100, halign:'center', editor: {type: 'textbox'}">Account No</th>
                    <th rowspan="2" data-options="field:'account_name',width:150, editor: {type: 'textbox', options: {readonly: true}}">Account Name</th>
                    <th rowspan="2" data-options="field:'account_type',width:100, halign:'center', editor: {type: 'textbox'}">Debit/Credit</th>
                </tr>
                <tr>
                    <th data-options="field:'currency',align:'center',width:80, editor: {type: 'textbox'}">Currency</th>
                    <th data-options="field:'price', width:80, halign:'center', align:'right', formatter:numberformat, editor: {type: 'numberbox', options: {required: true, readonly: true, precision: 4}}">Price</th>
                    <th data-options="field:'total',width:120, formatter:numberformat, halign:'center', align:'right',editor: {type: 'numberbox', options: {required: true, readonly: true, precision: 4}}">Amount</th>

                    <!-- <th data-options="field:'rate',width:80, halign:'center',align:'right', formatter:numberformat,editor: {type: 'numberbox', options: {required: true, precision: 4}}">Rate</th> -->
                    <th data-options="field:'rate',width:80, halign:'center',align:'right', formatter:numberformat, editor: {type: 'numberbox', options: {required: true, readonly: true, precision: 4}}">Rate</th>
                    <th data-options="field:'currency_local',width:80, editor: {type: 'textbox', options: {readonly: true}}">Currency</th>
                    <th data-options="field:'total_local',width:120, formatter:numberformat, halign:'center', align:'right', editor: {type: 'numberbox', options: {required: true, readonly: true, precision: 4}}">Amount</th>
                </tr>
            </thead>
        </table>

        <!-- inisiasi Tombol Add Jurnal -->
        <div style="width: 50%; float: left; margin-top:20px;">
            <a style="width: 100%;" class="easyui-linkbutton c2" disabled>Add to Journal</a>
            <br><br>
            <table id="dgDetailJournal" class="easyui-datagrid" title="Journal Lists" style="width: 100%;" data-options="singleSelect: true" toolbar="#toolbarDetailJournal"></table>

            <div class="fitem">
                <b style="width:50%; display:inline-block; padding-left: 50px;">BALANCE TOTAL</b>
                <input style="width:18%;" id="d_balance_debit" name="d_balance_debit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
                <input style="width:18%;" id="d_balance_credit" name="d_balance_credit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
            </div>
        </div>

        <div style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: right; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <div style="width: 100%; float: left;">
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">SUB TOTAL</b>
                        <input style="width:60%;" id="d_total_sub" name="d_total_sub" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">DPP</b>
                        <input style="width:60%;" id="d_total_dpp" name="d_total_dpp" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">VAT</b>
                        <input style="width:40%;" id="d_total_vat" name="d_total_vat" class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                        &nbsp; &nbsp; <input type="checkbox" class="easyui-checkbox" id="d_check_vat" data-options="onChange: d_check_vat" value="VAT">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">PPH</b>
                        <input style="width:30%;" id="d_total_pph" name="d_total_pph" class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                        <select style="width:30%;" id="d_pph" name="d_pph" class="easyui-combobox" required data-options="prompt: 'PPH'" panelHeight="auto">
                            <option value="0">NON PPH</option>
                            <option value="5">PPH 21</option>
                            <option value="2">PPH 23</option>
                            <option value="10">PPH 4(2)</option>
                            <option value="1">OTHER</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">GRAND TOTAL</b>
                        <input style="width:60%;" id="d_total_grand" name="d_total_grand" disabled required class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem" id="type_selection_dp">
                        <b style="width:35%; display:inline-block;">DOWN PAYMENT</b>
                        <input style="width:60%;" id="d_total_dp" name="d_total_dp" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                </div>
            </fieldset>
        </div>
    </form>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 99%; height: 600px; padding:10px; top: 5px; left:10px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purchase Invoice Type</span>
                        <select style="width:60%;" id="type" name="type" required class="easyui-combobox" panelHeight="auto">
                            <option value="" selected disabled>Select Purchase Invoice Type</option>
                            <option value="purchase">Non Down Payment</option>
                            <option value="dp">Down Payment</option>
                            <option value="others">Others</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purchase Invoice Date</span>
                        <input style="width:60%;" id="trans_date" name="trans_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purchase Invoice No</span>
                        <input style="width:60%;" readonly id="number" name="number" class="easyui-textbox" data-options="prompt:'Automatic From Purchase Invoce Date'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product Category</span>
                        <input style="width:60%;" required="" name="category_id" id="category_id" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Supplier Name</span>
                        <input style="width:60%;" required="" id="supplier_id" name="supplier_id" class="easyui-combogrid">
                    </div>
                    <div class="fitem" id="type_selection_purchase">
                        <span style="width:35%; display:inline-block;">Purchase Order Receipt</span>
                        <input style="width:60%;" required="" id="por_no" name="por_no" class="easyui-combogrid">
                    </div>
                    <div class="fitem" id="type_selection_others">
                        <span style="width:35%; display:inline-block;">Purchase Order Misc</span>
                        <input style="width:60%;" required="" id="po_no" name="po_no" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Journal Type</span>
                        <input style="width:60%;" required="" name="journal_type_id" id="journal_type" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="preview()" id="preview"><i class="fa fa-search"></i> Preview Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Supplier Invoice</span>
                        <input style="width:60%;" required="" id="invoice_no" name="invoice_no" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">No Faktur Pajak</span>
                        <input style="width:60%;" id="faktur_no" name="faktur_no" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Taxes</span>
                        <input style="width:60%;" id="taxes" name="taxes" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment Term</span>
                        <input style="width:60%;" required="" readonly="" id="payment_term" name="payment_term" class="easyui-numberbox" data-options="buttonText:'Days',buttonAlign:'right'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment Due</span>
                        <input style="width:60%;" id="due_date" name="due_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Voucher</span>
                        <input style="width:60%;" id="voucher" name="voucher" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Remarks</span>
                        <input style="width:60%;" id="remarks" name="remarks" class="easyui-textbox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">&nbsp;</span>
                        <input style="width:60%;" id="company_id" name="company_id" class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
        </div>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Purchase Invoice Lists" data-options="singleSelect: true" toolbar="#toolbar2">
            <thead>
            <tr> <!--berubah -->
                    <th rowspan="2" data-options="field:'action',width:120,formatter:buttonEdit">Action</th>
                    <th hidden rowspan="2" data-options="field:'id',width:150, editor: {type: 'textbox'}">ID</th>
                    <th rowspan="2" data-options="field:'por_no',width:150,editor: {type: 'textbox'}">POR. No</th>
                    <th rowspan="2" data-options="field:'po_no',width:150,editor: {type: 'textbox'}">PO. No</th>
                    <th rowspan="2" data-options="field:'item_rm_id',width:150,editor: {type: 'textbox'}" hidden>Product Id</th>
                    <th rowspan="2" data-options="field:'item_number',width:150,editor: {type: 'textbox', options: {required: true}}">Product No</th>
                    <th rowspan="2" data-options="field:'item_name',width:200,editor: {type: 'textbox', options: {required: true}}">Product Name</th>
                    <th rowspan="2" data-options="field:'supplier_product',width:200,editor: {type: 'textbox'}">Supplier Product</th>
                    <th rowspan="2" data-options="field:'uom',align:'center',width:80, editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('master/uom/reads') ?>',
                            editable:false,
                            valueField: 'name',
                            textField: 'name',
                            prompt: 'Choose Uom'
                        }}">UoM</th>

                        <th rowspan="2" data-options="field:'qty',width:80, formatter:numberformat,editor: {
                            type: 'numberbox', 
                            options: {
                                required: true,
                                onChange: function(value) {
                                    var dg = $('#dg2');
                                    var row = dg.datagrid('getSelected');
                                    var rowIndex = dg.datagrid('getRowIndex', row);

                                    var ed = dg.datagrid('getEditor', {
                                        index: rowIndex,
                                        field: 'total'
                                    });

                                    var ed3 = dg.datagrid('getEditor', {
                                        index: rowIndex,
                                        field: 'total_local'
                                    });

                                    var ed4 = dg.datagrid('getEditor', {
                                        index: rowIndex,
                                        field: 'rate'
                                    });

                                    var ed2 = dg.datagrid('getEditor', {
                                        index: rowIndex,
                                        field: 'price'
                                    });

                                    var price = $(ed2.target).numberbox('getValue');
                                    var rate = $(ed4.target).numberbox('getValue');
                                    $(ed.target).textbox('setValue', (parseFloat(price) * parseFloat(value)));
                                    $(ed3.target).textbox('setValue', (parseFloat(price) * parseFloat(value)) * parseFloat(rate));
                                }
                            }
                        }">Qty</th>

                    <th colspan="3" data-options="field:'',align:'center'">Original Currency</th>
                    <th colspan="3" data-options="field:'',align:'center'">Local Currency</th>
                    <th rowspan="2" data-options="field:'account_number',width:100, halign:'center', editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('finance/account_coa/reads') ?>',
                            panelWidth: 320,
                            idField: 'account_number',
                            textField: 'account_number',
                            mode: 'remote', 
                            fitColumns: true,
                            prompt: 'Choose Account No',
                            columns: [
                                [{
                                    field: 'account_number',
                                    title: 'Account No',
                                    width: 100
                                }, {
                                    field: 'account_name',
                                    title: 'Account Name',
                                    width: 200
                                }, ]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'account_name'
                                });

                                $(ed.target).textbox('setValue', rows.account_name);
                            }
                        }}">Account No</th>
                    <th rowspan="2" data-options="field:'account_name',width:150, editor: {type: 'textbox', options: {readonly: true}}">Account Name</th>
                    <th rowspan="2" data-options="field:'account_type',width:100, halign:'center', editor: {
                    type: 'combobox',
                    options: {
                        data: [{
                            'id':'DEBIT'
                        },{
                            'id':'CREDIT'
                        }],
                        valueField: 'id',
                        textField: 'id',
                        prompt: 'Choose Debit/Credit',
                        panelHeight: 'auto'
                    }}">Debit/Credit</th>
                </tr>
                <tr>
                <th data-options="field:'currency',align:'center',width:80, editor: {
                    type: 'combobox',
                    options: {
                        url: '<?= base_url('master/currencies/reads') ?>',
                        editable:false,
                        valueField: 'name',
                        textField: 'name',
                        prompt: 'Choose Currencies',
                        onSelect: function(curr){
                            var dg = $('#dg2');
                            var row = dg.datagrid('getSelected');
                            var rowIndex = dg.datagrid('getRowIndex', row);

                            var ed = dg.datagrid('getEditor', {
                                index: rowIndex,
                                field: 'rate'
                            });

                            var ed2 = dg.datagrid('getEditor', {
                                index: rowIndex,
                                field: 'currency_local'
                            });

                            var trans_date = $('#trans_date').datebox('getValue');

                            $.ajax({
                                type: 'post',
                                url: '<?= base_url('finance/purchase_invoices/readExchangeRates') ?>',
                                data: {period: trans_date, currency: curr.number},
                                dataType: 'json',
                                success: function(exchange) {
                                    var middle = 1;
                                    var name = 'IDR';
                                    if (exchange && exchange.length > 0 && exchange[0].middle) {
                                        middle = exchange[0].middle;
                                        name = exchange[0].currency_from;
                                    } else {
                                        toastr.error('Exchange Rate Data Not Found');
                                    }
                                    $(ed.target).numberbox('setValue', middle);
                                    $(ed2.target).textbox('setValue', 'IDR');
                                }
                            });
                        }
                    }}">Currency</th>

                    <th data-options="field:'price', width:80, halign:'center', align:'right', formatter:numberformat,editor: {type: 'numberbox', 
                        options: {
                            required: true,
                            precision: 4,
                            onChange: function(value) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total'
                                });

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total_local'
                                });

                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'rate'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty'
                                });

                                var qty = $(ed2.target).numberbox('getValue');
                                var rate = $(ed4.target).numberbox('getValue');
                                $(ed.target).textbox('setValue', (parseFloat(value) * parseFloat(qty)));
                                $(ed3.target).textbox('setValue', (parseFloat(value) * parseFloat(qty)) * parseFloat(rate));
                            }
                        }}">Price</th>

                    <th data-options="field:'total',width:120, formatter:numberformat, halign:'center', align:'right',editor: {type: 'numberbox', options: {required: true, readonly: true, precision: 4}}">Amount</th>

                    <!-- <th data-options="field:'rate',width:80, halign:'center',align:'right', formatter:numberformat,editor: {type: 'numberbox', options: {required: true, precision: 4}}">Rate</th> -->
                    <th data-options="field:'rate',width:80, halign:'center',align:'right', formatter:numberformat,editor: {
                        type: 'numberbox', 
                        options: {
                            required: true, 
                            precision: 4,
                            onChange: function(value) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var edTotal = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total'
                                });

                                var edTotalLocal = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total_local'
                                });

                                var total = $(edTotal.target).numberbox('getValue');

                                var totalLocal = parseFloat(total) * parseFloat(value);

                                $(edTotalLocal.target).textbox('setValue', totalLocal);
                            }
                        }
                    }">Rate</th>
                    <th data-options="field:'currency_local',width:80, editor: {type: 'textbox', options: {readonly: true}}">Currency</th>
                    <th data-options="field:'total_local',width:120, formatter:numberformat, halign:'center', align:'right',editor: {type: 'numberbox', options: {required: true, readonly: true, precision: 4}}">Amount</th>
                </tr>
            </thead>
        </table>

        <!-- inisiasi Tombol Add Jurnal -->
        <div style="width: 50%; float: left; margin-top:20px;">
            <a style="width: 100%;" class="easyui-linkbutton c2" onclick="addJournal()">Add to Journal</a>
            <br><br>
            <table id="dg3" class="easyui-datagrid" title="Journal Lists" style="width: 100%;" data-options="singleSelect: true" toolbar="#toolbar3"></table>

            <div class="fitem">
                <b style="width:50%; display:inline-block; padding-left: 50px;">BALANCE TOTAL</b>
                <input style="width:18%;" id="balance_debit" name="balance_debit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
                <input style="width:18%;" id="balance_credit" name="balance_credit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
            </div>
        </div>

        <div style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: right; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <div style="width: 100%; float: left;">
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">SUB TOTAL</b>
                        <input style="width:60%;" id="total_sub" name="total_sub" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">DPP</b>
                        <input style="width:60%;" id="total_dpp" name="total_dpp" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">VAT</b>
                        <input style="width:40%;" id="total_vat" name="total_vat" class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                        &nbsp; &nbsp; <input type="checkbox" class="easyui-checkbox" id="check_vat" data-options="onChange: check_vat" value="VAT">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">PPH</b>
                        <input style="width:30%;" id="total_pph" name="total_pph" class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                        <select style="width:30%;" id="pph" name="pph" class="easyui-combobox" required data-options="prompt: 'PPH'" panelHeight="auto">
                            <option value="0">NON PPH</option>
                            <option value="5">PPH 21</option>
                            <option value="2">PPH 23</option>
                            <option value="10">PPH 4(2)</option>
                            <option value="1">OTHER</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">GRAND TOTAL</b>
                        <input style="width:60%;" id="total_grand" name="total_grand" disabled required class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem" id="type_selection_dp">
                        <b style="width:35%; display:inline-block;">DOWN PAYMENT</b>
                        <input style="width:60%;" id="total_dp" name="total_dp" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                </div>
            </fieldset>
        </div>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    
    <b>CALCULATE JOURNAL</b>
    <div id="p_upload2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<div id="dlg_upload_backup" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>
<script>
    // Setting on/off FITUR AUTO POSTING JOURNAL => ubah ke TRUE jika ingin dinyalakan
    let auto_posting_journal = true; // actived on 2025-07-29 (request Bu Nina)

    function check_vat() {
        var check_vat = $("#check_vat").checkbox('options');

        if (check_vat.checked == true) {
            $("#total_vat").numberbox('enable');
        } else {
            $("#total_vat").numberbox('disable');
        }
    }

    let formMode = 'add'; // default

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $("#total_vat").numberbox('disable');

        var dg = $('#dg2').datagrid({
            onBeforeEdit: function(index, row) {
                row.editing = true;
                $(this).datagrid('refreshRow', index);
            },
            onAfterEdit: function(index, row) {
                row.editing = false;
                $(this).datagrid('refreshRow', index);
            },
            onCancelEdit: function(index, row) {
                row.editing = false;
                $(this).datagrid('refreshRow', index);
            },
        });

        $('#dg3').datagrid('loadData', []);
        $('#frm_insert').form('clear');

        $("#type_selection_others").hide();
        $("#type_selection_purchase").hide();
        $("#type_selection_dp").hide();

        $("#type").combobox({
            readonly: false
        });
        $("#trans_date").datebox('enable');
        //$("#category_id").combobox('enable');
        $("#supplier_id").combogrid('enable');
        $("#por_no").combogrid('enable');
        $("#po_no").combobox('enable');
        $("#account_purchase_name").textbox('setValue', "PURCHASE");
        $("#account_pay_name").textbox('setValue', "PAYABLE");
        $("#account_bal_name").textbox('setValue', "BALANCE");
        $("#preview").linkbutton('enable');

        $("#trans_date").datebox({
            onChange: function(val) {
                var trans_date = val;
                var payment_term = $("#payment_term").numberbox("getValue");

                if (payment_term != "") {
                    $.ajax({
                        type: "post",
                        url: "<?= base_url('finance/purchase_invoices/readDueDate/') ?>" + window.btoa(trans_date) + "/" + payment_term,
                        dataType: "text",
                        success: function(due_date) {
                            $("#due_date").datebox('setValue', due_date);
                        }
                    });
                }

                number(val);
            }
        });

        $("#type").combobox({
            onChange: function(t) {
                var type = $("#type").combobox('getValue');

                if (type == "purchase") {
                    $("#type_selection_purchase").show();
                    $("#type_selection_others").hide();
                    $("#type_selection_dp").hide();
                    $("#total_dp").numberbox('clear');
                } else if (type == "dp") {
                    $("#type_selection_purchase").show();
                    $("#type_selection_others").hide();
                    $("#type_selection_dp").show();
                } else {
                    $("#type_selection_others").show();
                    $("#type_selection_purchase").hide();
                    $("#type_selection_dp").hide();
                    $("#total_dp").numberbox('clear');
                }

                //Supplier Invoice Auto
                $.ajax({
                    type: "post",
                    url: "<?= base_url('finance/purchase_invoices/numberInvoice/') ?>" + type,
                    dataType: "html",
                    success: function(invoice_no) {
                        $("#invoice_no").textbox('setValue', invoice_no);
                    }
                });
            }
        });
    }


    function addJournal() {//berubah
        var rows = $('#dg2').datagrid('getRows');//datatatblesTemp
        var taxes = $("#taxes").numberbox('getValue');
        var pphname = $("#pph").combobox('getValue');
        var check_vat = $("#check_vat").checkbox('options');

        var totalrows = rows.length;

        var rows2 = $('#dg3').datagrid('getRows');//journal
        var totalrows2 = rows2.length;
        endEditing2();

        if (totalrows > 0) {
            var data_array = [];
            var data_array2 = [];
            var accountTotals = {}; 
            var total_sub = 0;
            
            for (let i = 0; i < totalrows; i++) 
            {
                var row = rows[i];
                var accountNumber = row.account_number;
                var totalValue = parseFloat(row.total);
                
                if (rows[i].account_type == "DEBIT") {
                    total_sub += Math.abs(parseFloat(rows[i].total));
                } else {
                    total_sub -= Math.abs(parseFloat(rows[i].total));
                }

                // mapping total berdasarkan account_number yang sama
                if (accountTotals[accountNumber]) {
                    accountTotals[accountNumber].total += totalValue;
                } else {
                    accountTotals[accountNumber] = {
                        account_name: row.account_name,
                        account_type: row.account_type,
                        total: totalValue
                    };
                }
            }

            // push ke data_array untuk di tampilkan            
            for (var accNum in accountTotals) {
                if (accountTotals.hasOwnProperty(accNum)) {
                    var aggregatedData = {
                        account_number: accNum,
                        account_name: accountTotals[accNum].account_name,
                        account_type: accountTotals[accNum].account_type, // Ambil account_type dari data pertama
                        total: accountTotals[accNum].total
                    };
                    data_array.push(aggregatedData);
                }
            }

            $("#total_sub").numberbox('setValue', total_sub);

            if (check_vat.checked == true) {
                var disc_tax = $("#total_vat").numberbox('getValue');
            } else {
                var total_dpp = Math.floor(total_sub * (11/12));
                $("#total_dpp").numberbox('setValue', total_dpp);

                var disc_tax = parseFloat(total_dpp * (taxes / 100));
                // var disc_tax = parseFloat(total_sub * (taxes / 100));
                $("#total_vat").numberbox('setValue', disc_tax);
            }

            var total_pph = $("#total_pph").numberbox('getValue');
            var total_grand = (parseFloat(total_sub) + parseFloat(disc_tax) - parseFloat(total_pph));
            $("#total_grand").numberbox('setValue', (total_grand));

            var pph_val = 0;
            var vat_val = 0;
            var arr_vat = ["170.160.00", "250.160.00"];
            var arr_pph = ["250.130.00"];
            var arr_ap = ["210.110.00", "120.140.00", "220.120.00"];

            for (let z = 0; z < totalrows2; z++) {
                // if (rows2[z].account_number == "1154105") {
                //     var debit = disc_tax;
                //     var credit = 0;
                //     vat_val = 1;
                // } else {
                //     var debit = rows2[z].debit;
                //     var credit = rows2[z].credit;
                // }

                if (jQuery.inArray(rows2[z].account_number, arr_vat) >= 0) {
                    var debit = disc_tax;
                    var credit = 0;
                }

                if (jQuery.inArray(rows2[z].account_number, arr_pph) >= 0) {
                    var debit = 0;
                    var credit = total_pph;
                    pph_val = 1;
                }

                if (jQuery.inArray(rows2[z].account_number, arr_ap) >= 0) {
                    var debit = 0;
                    var credit = total_grand;
                }

                if (rows2[z].account_number == "210.120.00") {
                    var debit = 0;
                    var credit = total_grand;
                }

                if (rows2[z].account_number == "170.170.00") {
                    var debit = disc_tax;
                    var credit = 0;
                }

                var data2 = {
                    account_number: rows2[z].account_number,
                    account_name: rows2[z].account_name,
                    debit: debit,
                    credit: credit,
                    flag: rows2[z].flag,
                }

                data_array2.push(data2);
            }

            // if (taxes > 0 && vat_val == 0) {
            //     var data2 = {
            //         account_number: "",
            //         account_name: "VAT",
            //         debit: disc_tax,
            //         credit: 0,
            //         flag: "3",
            //     }

            //     data_array2.push(data2);
            // }

            // if (taxes > 0 && vat_val == 0) {
            //     var data2 = {
            //         account_number: "250.160.00",
            //         account_name: "PPN Keluaran (VAT OUT)",
            //         debit: disc_tax,
            //         credit: 0,
            //         flag: "0",
            //     }

            //     data_array2.push(data2);
            // }

            // if (taxes > 0 && vat_val == 0) {
            //     var data2 = {
            //         account_number: "220.110.00",
            //         account_name: "Relatied Parties (Others)",
            //         debit: 0,
            //         credit: total_grand,
            //         flag: "0",
            //     }

            //     data_array2.push(data2);
            // }

            if (total_pph > 0 && pph_val == 0 && pphname == "5") {
                var data2 = {
                    account_number: "250.110.00",
                    account_name: "PPH 21",
                    debit: 0,
                    credit: total_pph,
                    flag: "4",
                }

                data_array2.push(data2);
            }

            if (total_pph > 0 && pph_val == 0 && pphname == "1") {
                var data2 = {
                    account_number: "220.130.00",
                    account_name: "OTHER INCOME",
                    debit: 0,
                    credit: total_pph,
                    flag: "4",
                }

                data_array2.push(data2);
            }

            if (total_pph > 0 && pph_val == 0 && pphname == "2") {
                var data2 = {
                    account_number: "250.130.00",
                    account_name: "PPH 23",
                    debit: 0,
                    credit: total_pph,
                    flag: "4",
                }

                data_array2.push(data2);
            }

            if (total_pph > 0 && pph_val == 0 && pphname == "10") {
                var data2 = {
                    account_number: "250.150.00",
                    account_name: "PPH 4(2)",
                    debit: 0,
                    credit: total_pph,
                    flag: "4",
                }

                data_array2.push(data2);
            }

            var jsonData = JSON.stringify(data_array);
            var jsonData2 = JSON.stringify(data_array2);

            $.ajax({
                type: "POST",
                url: "<?= base_url('finance/purchase_invoices/createJson') ?>",
                data: {
                    jsonData: jsonData,
                    jsonData2: jsonData2,
                },
                success: function(response) {
                    addTable2('<?= base_url('finance/purchase_invoices/calculateJournal') ?>');

                    setTimeout(function() {
                        balance_journal();
                    }, 2000);
                },
            });
        } else {
            toastr.warning("please selections your data in table first");
        }
    }

    $(document).ready(function() {
        var faktur_no = $('#faktur_no').textbox('getValue');

        if (faktur_no.length === 19) {
            // Lakukan pengecekan faktur_code menggunakan AJAX
            $.ajax({
                type: "GET",
                url: '<?= base_url('finance/purchase_invoices/check_faktur_no') ?>',
                data: {
                    faktur_no: window.btoa(faktur_no) // Mengencode faktur_no
                },
                dataType: "json",
                success: function(response) {
                    if (response.exists) {
                        toastr.error('Tax invoice already exists. Please input different Combination.');
                        $('#faktur_no').textbox('clear');
                        return;
                    }
                    // Proses lanjut jika faktur_no belum ada
                },
                error: function() {
                    toastr.error('Error occurred while checking the faktur number.');
                }
            });
        }
    });

    $('#faktur_no').textbox({
        validType: 'length[1,19]',
        inputEvents: $.extend({}, $.fn.textbox.defaults.inputEvents, {
            keyup: function(e) {
                var value = $(this).val();
                if (value.length > 19) {
                    $(this).val(value.slice(0, 19));
                }
            }
        })
    });

    // DATA ISISAN JURNAL LIST---------------------------------------------------------------------------------------
    function addTable2(link = "") {
        var lastIndex;
        var dg = $('#dg3').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'account_number',
                    width: 100,
                    halign: 'center',
                    title: "Account No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('finance/account_coa/reads') ?>',
                            panelWidth: 320,
                            idField: 'account_number',
                            textField: 'account_number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Account No',
                            columns: [
                                [{
                                    field: 'account_number',
                                    title: 'Account No',
                                    width: 100
                                }, {
                                    field: 'account_name',
                                    title: 'Account Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg3');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'account_name'
                                });

                                $(ed.target).textbox('setValue', rows.account_name);
                            }
                        }
                    }
                }, {
                    field: 'account_name',
                    width: 200,
                    halign: 'center',
                    title: "Account Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'debit',
                    width: 100,
                    halign: 'center',
                    title: "Debit",
                    formatter: numberformat,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2
                        }
                    }
                }, {
                    field: 'credit',
                    width: 100,
                    halign: 'center',
                    title: "Credit",
                    formatter: numberformat,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2
                        }
                    }
                }, {
                    field: 'flag',
                    width: 50,
                    halign: 'center',
                    title: "Order",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true
                        }
                    }
                }, ],
            ],
            onClickCell: onClickCell2,
            onBeginEdit: function(rowIndex, row) {
                balance_journal();
            }
        });
    }

    function balance_journal() {
        var rows = $('#dg3').datagrid('getRows');
        var totalrows = rows.length;
        endEditing2();

        if (totalrows > 0) {
            var debit = 0;
            var credit = 0;
            for (let i = 0; i < totalrows; i++) {
                debit += parseFloat(rows[i].debit);
                credit += parseFloat(rows[i].credit);
            }

            $("#balance_debit").numberbox('setValue', debit);
            $("#balance_credit").numberbox('setValue', credit);
        }
    }

    // DATA ISIAN JOURNAL LIST --------------------------------------------------
    function addTableJournal(link = "") {
        var lastIndex;
        var dg = $('#dgDetailJournal').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'account_number',
                    width: 100,
                    halign: 'center',
                    title: "Account No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('finance/account_coa/reads') ?>',
                            panelWidth: 320,
                            idField: 'account_number',
                            textField: 'account_number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Account No',
                            columns: [
                                [{
                                    field: 'account_number',
                                    title: 'Account No',
                                    width: 100
                                }, {
                                    field: 'account_name',
                                    title: 'Account Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dgDetailJournal');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'account_name'
                                });

                                $(ed.target).textbox('setValue', rows.account_name);
                            }
                        }
                    }
                }, {
                    field: 'account_name',
                    width: 200,
                    halign: 'center',
                    title: "Account Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'debit',
                    width: 100,
                    halign: 'center',
                    title: "Debit",
                    formatter: numberformat,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2
                        }
                    }
                }, {
                    field: 'credit',
                    width: 100,
                    halign: 'center',
                    title: "Credit",
                    formatter: numberformat,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2
                        }
                    }
                }, {
                    field: 'flag',
                    width: 50,
                    halign: 'center',
                    title: "Order",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true
                        }
                    }
                }, ],
            ],
            onLoadSuccess: function(data) {
                // hitung total debit dan credit journal 
                const rows = data.rows || []; 
                const totalrows = rows.length;

                let debit = 0;
                let credit = 0;

                if (totalrows > 0) {
                    for (let i = 0; i < totalrows; i++) {
                        debit += parseFloat(rows[i].debit || 0);
                        credit += parseFloat(rows[i].credit || 0);
                    }
                }
                
                $("#d_balance_debit").numberbox('setValue', debit);
                $("#d_balance_credit").numberbox('setValue', credit);
            }
        });
    }
    
    $('#dlg_detail').dialog({
        buttons: [{
            text: 'Close',
            iconCls: 'icon-ok',
            handler: function() { 
                $('#dlg_detail').dialog('close');
            }
        }]
    });

    //----------------------------------------------------------------------------------------------------------------

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg2').datagrid('validateRow', editIndex)) {
            $('#dg2').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function append() {
        if (endEditing()) {
            $('#dg2').datagrid('appendRow', {
                "action": 0,
                "currency": 'IDR',
            });
            editIndex = $('#dg2').datagrid('getRows').length - 1;
            $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);

            var dg = $('#dg2');
            var row = dg.datagrid('getSelected');
            var rowIndex = dg.datagrid('getRowIndex', row);

            var qty = dg.datagrid('getEditor', {
                index: rowIndex,
                field: 'qty'
            });

            var price = dg.datagrid('getEditor', {
                index: rowIndex,
                field: 'price'
            });

            $(qty.target).numberbox('readonly', false);
            $(price.target).numberbox('readonly', false);
        }
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function editrow(target) {
        $('#dg2').datagrid('selectRow', getRowIndex(target));
        $('#dg2').datagrid('beginEdit', getRowIndex(target));
    }

    function deleterow(target) {
        $.messager.confirm('Confirm', 'Are you sure?', function(r) {
            if (r) {
                var dg = $('#dg2');
                var row = dg.datagrid('getSelected');
                var rowIndex = dg.datagrid('getRowIndex', row);

                var ed = dg.datagrid('getEditor', {
                    index: editIndex,
                    field: 'id'
                });

                // ketika add hanya delete di datagrid
                if (formMode === 'add') {                     
                    toastr.success("Successfully deleted");

                } else {
                    $.ajax({
                        method: 'post',
                        url: '<?= base_url('finance/purchase_invoices/deleteSingle') ?>',
                        data: {
                            id: row.id,
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            toastr.success(result.message);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            toastr.error(jqXHR.statusText);
                            $.messager.alert("Error", jqXHR.statusText, 'error');
                        },
                        complete: function(data) {
                            $('#dg').datagrid('reload');
                        }
                    });
                }

                $('#dg2').datagrid('deleteRow', getRowIndex(target));
            }
        });
    }

    function saverow(target) {
        $('#dg2').datagrid('endEdit', getRowIndex(target));
    }

    function cancelrow(target) {
        $('#dg2').datagrid('cancelEdit', getRowIndex(target));
    }

    //DATAGRID JOURNAL
    var editIndex2 = undefined;

    function endEditing2() {
        if (editIndex2 == undefined) {
            return true
        }
        if ($('#dg3').datagrid('validateRow', editIndex2)) {
            $('#dg3').datagrid('endEdit', editIndex2);
            editIndex2 = undefined;
            return true;
        } else {
            return false;
        }
    }

    function onClickCell2(index, field) {
        if (editIndex2 != index) {
            if (endEditing2()) {
                $('#dg3').datagrid('selectRow', index).datagrid('beginEdit', index);
                editIndex2 = index;
            } else {
                setTimeout(function() {
                    $('#dg3').datagrid('selectRow', editIndex2);
                }, 0);
            }
        }
    }

    function append2() {
        if (endEditing2()) {
            $('#dg3').datagrid('appendRow', {
                debit: '0',
                credit: '0',
            });
            editIndex2 = $('#dg3').datagrid('getRows').length - 1;
            $('#dg3').datagrid('selectRow', editIndex2).datagrid('beginEdit', editIndex2);
        }
    }

    function removeit3() {
        if (editIndex2 == undefined) {
            return true;
        }

        $('#dg3').datagrid('cancelEdit', editIndex2).datagrid('deleteRow', editIndex2);
        editIndex2 = undefined;
    }

    //Edit Data
    function update() {
        formMode = 'update';
        var row = $('#dg').datagrid('getSelected');
        console.log("Data Loaded:",row);
        if (row) {
            if (row.status == 0) {
                if (row.gl_no == null) {
                    $('#dlg_insert').dialog('open');
                    $("#dlg_insert").window('setTitle', "Update " + row.number);
                    
                    $('#frm_insert').form('load', row);

                    $("#type_selection_others").hide();
                    $("#type_selection_purchase").hide();
                    $("#type_selection_dp").hide();

                    $("#taxes").numberbox('setValue', row.taxes);

                    $("#type").combobox({
                        readonly: true
                    });
                    
                    // $("#trans_date").datebox('disable'); // request Bu Nina bisa ubah tanggal saat update
                    //$("#category_id").combobox('disable');
                    $("#supplier_id").combogrid('disable');
                    // $("#por_no").combogrid('disable');
                    $("#po_no").combobox('disable');
                    // $("#preview").linkbutton('disable');

                    if (row.type == "purchase") {
                        $("#type_selection_purchase").show();
                        $("#type_selection_others").hide();
                        $("#type_selection_dp").hide();
                        $("#total_dp").numberbox('clear');
                    } else if (row.type == "dp") {
                        $("#type_selection_purchase").show();
                        $("#type_selection_others").hide();
                        $("#type_selection_dp").show();
                    } else {
                        $("#type_selection_others").show();
                        $("#type_selection_purchase").hide();
                        $("#type_selection_dp").hide();
                        $("#total_dp").numberbox('clear');
                    }

                    $("#category_id").combobox({
                        url: '<?= base_url('master/item_categories/readsnotfg') ?>',
                        valueField: 'id',
                        textField: 'name',
                        prompt: "Choose Product Family",
                        onLoadSuccess: function(item_category_load) {
                            $("#category_id").combobox('setValue', row.category_id);
                        },
                        onSelect: function(item_category) {
                            // conlose.log(item_category);
                            //GET SUPPLIER
                            $('#supplier_id').combogrid({
                                url: '<?= base_url('finance/purchase_invoices/readSupplierss?item_category_id=') ?>' + item_category.id,
                                panelWidth: 420,
                                idField: 'id',
                                textField: 'name',
                                mode: 'remote',
                                fitColumns: true,
                                prompt: "Choose Supplier",
                                columns: [
                                    [{
                                        field: 'number',
                                        title: 'Supplier No',
                                        width: 120
                                    }, {
                                        field: 'name',
                                        title: 'Supplier Name',
                                        width: 250
                                    }, ]
                                ],
                                onLoadSuccess: function(item_category_load) {
                                    $("#supplier_id").combogrid('setValue', row.supplier_id);
                                },
                            });

                            //GET POR
                            var receiptNos = row.por_numbers;
                            if (receiptNos && receiptNos.includes(',')) {
                                receiptNos = receiptNos.replace(/\s*,\s*/g, ',');
                            }

                            $("#por_no").combogrid('setValue', receiptNos);

                            $("#por_no").combogrid({
                                url: '<?= base_url('finance/purchase_invoices/readReceiptUpdate?supplier_id=') ?>' + row.supplier_id + "&item_category_id=" + item_category.id,
                                panelWidth: 500,
                                idField: 'receipt_no',
                                textField: 'receipt_no',
                                mode: 'remote',
                                multiple: true,
                                prompt: "Choose Purchase Order Receipts",
                                columns: [
                                    [ {
                                        field: 'ck', // Kolom checkbox
                                        checkbox: true, // Mengaktifkan checkbox
                                    }, {
                                        field: 'no',
                                        title: 'No',
                                        width: 20,
                                        align: 'center'
                                    }, {
                                        field: 'receipt_no',
                                        title: 'Receipt No',
                                        width: 150,
                                        align: 'left'
                                    }]
                                ],
                                fitColumns: true,
                                selectOnCheck: true,
                                selectOnCheck: true,
                                checkOnSelect: true,
                                onLoadSuccess: function(data) {
                                    if (row && row.por_numbers && receiptNos.includes(',')) {
                                        let selectedDeliveryNotes = row.por_numbers
                                                                        .split(',')
                                                                        .map(note => note.trim())
                                                                        .filter(note => note !== '');
                                                                        
                                        let grid = $('#por_no').combogrid('grid'); 
                                        if (grid) { 
                                            const rowsData = data.rows || data;  
                                            
                                            for (let i = 0; i < rowsData.length; i++) { 
                                                let currentDeliveryNote = rowsData[i].receipt_no;
                                                if (selectedDeliveryNotes.includes(currentDeliveryNote)) {
                                                    grid.datagrid('checkRow', i);
                                                }
                                            }
                                        } else {
                                            console.warn("Grid instance for receipt_no checklist not found.");
                                        }    
                                    } else {
                                        if (receiptNos == "" || receiptNos == null) { $("#por_no").combogrid('setValue', "-"); }
                                        else { $("#por_no").combogrid('setValue', receiptNos); }                                        
                                    }

                                },
                                // onCheck: function(index, rowData) { // ---- COMMENT KARENA HARUS KLIK ULANG AddJournal
                                //     // --- otomatis ubah dg2 ketika checklist ---
                                //     let checkedPOR = rowData.delivery_note_no;
                                //     // console.log(checkedPOR);
                                //     preview(); // refresh dg2 journal list
                                //     addJournal(); // refresh journal calculate
                                // },
                                onUncheck: function(index, rowData) {                                    
                                    // Dapatkan semua baris yang saat ini terceklis di combogrid
                                    let combogridGrid = $('#por_no').combogrid('grid');
                                    let checkedRows = combogridGrid.datagrid('getChecked');
                                    
                                    // Validasi pastikan minimal satu yang terceklis
                                    if (checkedRows.length === 0) {
                                        $.messager.alert('Warning', 'You must select at least one data.', 'warning', function() {
                                            combogridGrid.datagrid('checkRow', index); 
                                            addJournal();
                                        });
                                        return;
                                    }

                                    // otomatis ubah dg2 ketika Un-checklist
                                    let uncheckedPOR = rowData.receipt_no;
                                    console.log("Unchecked " + uncheckedPOR);                                    

                                    // delete from purchase_invoices by POR
                                    $.messager.confirm('Confirm', 'Are you sure want to remove data from this POR?', function(r) {
                                        if (r) {
                                            $.ajax({
                                                method: 'post',
                                                url: '<?= base_url('finance/purchase_invoices/deleteByPOR') ?>',
                                                data: {
                                                    por_no: rowData.receipt_no,
                                                },
                                                dataType: "json",
                                                success: function(result) {
                                                    console.log("Delete on Uncheck ", result);
                                                    toastr.success(result.message);
                                                    $.messager.alert("Warning", "<b>Please click Preview Data and Add To Journal again before Save All</b>", 'warning');
                                                },
                                                error: function(jqXHR, textStatus, errorThrown) {
                                                    toastr.error(jqXHR.statusText);
                                                    $.messager.alert("Error", jqXHR.statusText, 'error');                                                    
                                                },
                                                complete: function(data) {
                                                    $('#dg').datagrid('reload');
                                                }
                                            });
                                            
                                            preview(); // refresh dg2 journal list
                                            addJournal(); // refresh journal calculate
                                        }
                                    });
                                    
                                },
                            });
                        }
                    });

                    var lastIndex;
                    var dg = $('#dg2').datagrid({
                        url: '<?= base_url('finance/purchase_invoices/reads/') ?>' + window.btoa(row.number),
                        onBeforeEdit: function(index, row) {
                            row.editing = true;
                            $(this).datagrid('refreshRow', index);
                        },
                        onAfterEdit: function(index, row) {
                            row.editing = false;
                            $(this).datagrid('refreshRow', index);
                        },
                        onCancelEdit: function(index, row) {
                            row.editing = false;
                            $(this).datagrid('refreshRow', index);
                        },
                    });

                    addTable2('<?= base_url('finance/purchase_invoices/readJournals/') ?>' + window.btoa(row.number));

                    setTimeout(function() {
                        balance_journal();
                        $("#number").textbox('setValue', row.number);
                    }, 2000);
                } else {
                    toastr.error("Cannot Update because this Purchase Invoice has been created in Posting Journal");
                }
            } else {
                toastr.error("Purchase Invoices Status is closed");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //NOMOR AUTOMATIC
    function number(trans_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/purchase_invoices/number/') ?>" + window.btoa(trans_date),
            dataType: "html",
            success: function(result) {
                $("#number").textbox('setValue', result);
            }
        });
    }

    function preview() {
        var por_no = $("#por_no").combogrid('getText');
        var po_no = $("#po_no").combobox('getValue');
        var trans_date = $("#trans_date").datebox('getValue');
        var invoice_no = $("#invoice_no").textbox('getValue');
        var taxes = $("#taxes").numberbox('getValue');
        var journal_type_id = $("#journal_type").combobox('getValue');

        if (trans_date == "" || invoice_no == "" || taxes == "" || journal_type_id == "") {
            toastr.info('Please completed your data');
        } else {
            $("#pph").combobox('setValue', "0");

            var lastIndex;
            if (por_no != "") {
                var dg = $('#dg2').datagrid({
                    url: '<?= base_url('finance/purchase_invoices/datatablesTemp') ?>?por_no=' + window.btoa(por_no) + '&trans_date=' + window.btoa(trans_date),//menambahkan trans_date
                    onLoadSuccess: function(row) {
                        $("#total_sub").numberbox('setValue', row.total_sub);

                        var total_dpp = Math.floor((row.total_sub) * 11/12);
                        $("#total_dpp").numberbox('setValue', total_dpp);

                        var disc_tax = parseFloat(total_dpp * (taxes / 100));

                        // var disc_tax = parseFloat(row.total_sub * (taxes / 100));
                        $("#total_vat").numberbox('setValue', disc_tax);
                        var total_grand = (parseFloat(row.total_sub) + parseFloat(disc_tax));
                        $("#total_grand").numberbox('setValue', (total_grand));
                        addTable2('<?= base_url('finance/purchase_invoices/readJournal/') ?>' + window.btoa(journal_type_id));
                    }
                });
            } else if (po_no != "") {
                var dg = $('#dg2').datagrid({
                    url: '<?= base_url('finance/purchase_invoices/datatablesTemp2') ?>?po_no=' + window.btoa(po_no),
                    onLoadSuccess: function(row) {
                        $("#total_sub").numberbox('setValue', row.total_sub);

                        var total_dpp = Math.floor((row.total_sub) * 11/12);
                        $("#total_dpp").numberbox('setValue', total_dpp);

                        var disc_tax = parseFloat(total_dpp * (taxes / 100));

                        // var disc_tax = parseFloat(row.total_sub * (taxes / 100));
                        $("#total_vat").numberbox('setValue', disc_tax);
                        var total_grand = (parseFloat(row.total_sub) + parseFloat(disc_tax));
                        $("#total_grand").numberbox('setValue', (total_grand));
                        addTable2('<?= base_url('finance/purchase_invoices/readJournal/') ?>' + window.btoa(journal_type_id));
                    }
                });
            } else {
                toastr.info('Please completed your data');
            }
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        // $.ajax({
                        //     type: "post",
                        //     url: "<?= base_url('closing/locks/checkLock') ?>",
                        //     data: "period=" + row.trans_date + "&menus_id=<?= $menus_id ?>",
                        //     dataType: "json",
                        //     success: function (lock) {
                        //         if(lock.total > 0){
                        //             toastr.error("This period is not active by Accounting");
                        //             return false;
                        //         }

                        if (row.status == 0) {
                            if (row.gl_no == null) {
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('finance/purchase_invoices/delete') ?>',
                                    data: {
                                        number: row.number
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                        toastr.success(result.message);
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error(jqXHR.statusText);
                                        $.messager.alert("Error", jqXHR.statusText, 'error');
                                    },
                                    complete: function(data) {
                                        $('#dg').datagrid('reload');
                                    }
                                });
                            } else {
                                toastr.error("Cannot Delete because this Purchase Invoice has been created in Posting Journal");
                            }
                        } else {
                            toastr.error("AP Payment Status is closed");
                        }
                        //     }
                        // });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //FILTER DATA
    function filter() {
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_due_date_from = $("#filter_due_date_from").datebox('getValue');
        var filter_due_date_to = $("#filter_due_date_to").datebox('getValue');
        var filter_category_id = $("#filter_category_id").combobox('getValue');
        var filter_purchase_invoice = $("#filter_purchase_invoice").combobox('getValue');
        var filter_purchase_receipt = $("#filter_purchase_receipt").combobox('getValue');
        var filter_purchase_order = $("#filter_purchase_order").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_status_supplier = $("#filter_status_supplier").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_category_id=" + window.btoa(filter_category_id) +
            "&filter_purchase_invoice=" + window.btoa(filter_purchase_invoice) +
            "&filter_purchase_receipt=" + window.btoa(filter_purchase_receipt) +
            "&filter_purchase_order=" + window.btoa(filter_purchase_order) +
            "&filter_status_supplier=" + window.btoa(filter_status_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_status=" + window.btoa(filter_status);

        $('#dg').datagrid({
            url: '<?= base_url('finance/purchase_invoices/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/purchase_invoices/print') ?>' + url);
    }

    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_purchase_invoices.xls') ?>');
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //EXPORT TO EXCEL
    function excel() {
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_due_date_from = $("#filter_due_date_from").datebox('getValue');
        var filter_due_date_to = $("#filter_due_date_to").datebox('getValue');
        var filter_category_id = $("#filter_category_id").combobox('getValue');
        var filter_purchase_invoice = $("#filter_purchase_invoice").combobox('getValue');
        var filter_purchase_receipt = $("#filter_purchase_receipt").combobox('getValue');
        var filter_purchase_order = $("#filter_purchase_order").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_status_supplier = $("#filter_status_supplier").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_category_id=" + window.btoa(filter_category_id) +
            "&filter_purchase_invoice=" + window.btoa(filter_purchase_invoice) +
            "&filter_purchase_receipt=" + window.btoa(filter_purchase_receipt) +
            "&filter_purchase_order=" + window.btoa(filter_purchase_order) +
            "&filter_status_supplier=" + window.btoa(filter_status_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('finance/purchase_invoices/print/excel') ?>' + url);
    }

    function excelDetail() {
        //EXPORT TO EXCEL
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_due_date_from = $("#filter_due_date_from").datebox('getValue');
        var filter_due_date_to = $("#filter_due_date_to").datebox('getValue');
        var filter_category_id = $("#filter_category_id").combobox('getValue');
        var filter_purchase_invoice = $("#filter_purchase_invoice").combobox('getValue');
        var filter_purchase_receipt = $("#filter_purchase_receipt").combobox('getValue');
        var filter_purchase_order = $("#filter_purchase_order").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_status_supplier = $("#filter_status_supplier").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_category_id=" + window.btoa(filter_category_id) +
            "&filter_purchase_invoice=" + window.btoa(filter_purchase_invoice) +
            "&filter_purchase_receipt=" + window.btoa(filter_purchase_receipt) +
            "&filter_purchase_order=" + window.btoa(filter_purchase_order) +
            "&filter_status_supplier=" + window.btoa(filter_status_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('finance/purchase_invoices/printDetail/excel') ?>' + url);
    }

    function excelJournal() {
        //EXPORT TO EXCEL
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_due_date_from = $("#filter_due_date_from").datebox('getValue');
        var filter_due_date_to = $("#filter_due_date_to").datebox('getValue');
        var filter_category_id = $("#filter_category_id").combobox('getValue');
        var filter_purchase_invoice = $("#filter_purchase_invoice").combobox('getValue');
        var filter_purchase_receipt = $("#filter_purchase_receipt").combobox('getValue');
        var filter_purchase_order = $("#filter_purchase_order").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_status_supplier = $("#filter_status_supplier").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_category_id=" + window.btoa(filter_category_id) +
            "&filter_purchase_invoice=" + window.btoa(filter_purchase_invoice) +
            "&filter_purchase_receipt=" + window.btoa(filter_purchase_receipt) +
            "&filter_purchase_order=" + window.btoa(filter_purchase_order) +
            "&filter_status_supplier=" + window.btoa(filter_status_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('finance/purchase_invoices/printJournal/excel') ?>' + url);
    }

    // function print_invoicing() {
    //     var filter_purchase_invoice = $("#filter_purchase_invoice").combobox('getValue');

    //     if (filter_purchase_invoice == "") {
    //         toastr.warning("Please select Purchase Invoice No!");
    //     } else {
    //         window.open("<?= base_url('finance/purchase_invoices/print_invoicing/') ?>" + window.btoa(filter_purchase_invoice), "_blank", 'location=yes,height=600,width=1200,scrollbars=yes,status=yes');
    //     }

    // }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    //Format Datepicker
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

    //Format Datepicker
    function myparser(s) {
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0], 10);
        var m = parseInt(ss[1], 10);
        var d = parseInt(ss[2], 10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
            return new Date(y, m - 1, d);
        } else {
            return new Date();
        }
    }

    function numberformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function buttonEdit(value, row, index) {
        if (row.editing) {
            var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a> ';
            var c = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="cancelrow(this)">Cancel</a>';
            return s + c;
        } else {
            var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a> ';
            var d = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="deleterow(this)">Delete</a>';
            return e + d;
        }
    }

    $(function() {
        $("#total_pph").numberbox('disable');

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('finance/purchase_invoices/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.number + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                ddv.datagrid({
                    url: '<?= base_url('finance/purchase_invoices/datatables/details?number=') ?>' + window.btoa(row.number),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'por_no',
                            title: 'Purchase Receipt',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'po_no',
                            title: 'Purchase Order',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_no',
                            title: 'Product No',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 300
                        }, {
                            field: 'supplier_product',
                            title: 'Supplier Product',
                            halign: 'center',
                            width: 300
                        }, {
                            field: 'qty',
                            title: 'Qty',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'price',
                            title: 'Unit Price',
                            width: 150,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'total',
                            title: 'Total',
                            width: 150,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        // Validasi: account_number tidak boleh null, undefined, atau string kosong setelah di-trim (Bu Nina)
        function validateDatagrid(datagridSelector, listName) 
        {
            var dg = $(datagridSelector);
            var allRows = dg.datagrid('getRows');
            let nullAccountNumberRows = [];
            let isValid = true;

            if (allRows.length === 0) {
                $.messager.alert("Error", "<b>Failed!</b> " + listName + " is required", 'error');
                isValid = false;
            } else {
                for (var i = 0; i < allRows.length; i++) {
                    var row = allRows[i];
                    var accountNumber = row.account_number;

                    if (accountNumber === null || accountNumber === undefined || String(accountNumber).trim() === '') {
                        nullAccountNumberRows.push(i + 1);
                    }
                }

                if (nullAccountNumberRows.length > 0) {
                    isValid = false;
                    var errorMessage = "<b>Failed! Account Number on " + listName + " cannot be empty for rows: " + nullAccountNumberRows.join(', ') + "!</b> <br><br>Please re-check the List and re-calculate Journal before Save All.";
                    $.messager.alert("Error", errorMessage, 'error');
                }
            }
            return isValid; // Mengembalikan true jika valid, false jika tidak
        }

        // default button Save All belum di klik
        let isSubmitting = false;

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {

                    // --- validasi account_number call function validateDatagrid ---
                    var hasValidationError = false;
                    if (!validateDatagrid('#dg2', "Purchase Invoice Lists")) { // Validasi AP Payment Lists (#dg2)
                        hasValidationError = true;
                    }
                    
                    if (!hasValidationError && !validateDatagrid('#dg3', "Journal Lists")) { // Validasi Journal List (#dg3)
                        hasValidationError = true;
                    }
                    
                    if (hasValidationError) { // Jika ada error, maka hentikan eksekusi selanjutnya
                        if (typeof isSubmitting !== 'undefined' && isSubmitting) {
                            isSubmitting = false;
                        }
                        return;
                    }
                    // --- Lanjutkan proses jika tidak ada error validasi ---
                    
                    if (isSubmitting) return; // cegah klik dobel
                    
                    isSubmitting = true;
                    var btn = $(this);
                    btn.linkbutton('disable');
                    setTimeout(function() {
                        isSubmitting = false;
                        btn.linkbutton('enable');
                    }, 5000);

                    var type = $("#type").combobox('getValue');
                    var trans_date = $("#trans_date").datebox('getValue');
                    var number = $("#number").textbox('getValue');
                    var category_id = $("#category_id").combobox('getValue');
                    var supplier_id = $("#supplier_id").combogrid('getValue');
                    var journal_type_id = $("#journal_type").combobox('getValue');
                    var invoice_no = $("#invoice_no").textbox('getValue');
                    var taxes = $("#taxes").numberbox('getValue');
                    var payment_term = $("#payment_term").numberbox('getValue');
                    var due_date = $("#due_date").datebox('getValue');
                    var voucher = $("#voucher").textbox('getValue');
                    var remarks = $("#remarks").textbox('getValue');
                    var faktur_no = $("#faktur_no").textbox('getValue');

                    var balance_debit = $("#balance_debit").numberbox('getValue');
                    var balance_credit = $("#balance_credit").numberbox('getValue');

                    var total_sub = $("#total_sub").numberbox('getValue');
                    var total_vat = $("#total_vat").numberbox('getValue');
                    var total_dpp = $("#total_dpp").numberbox('getValue');
                    var total_pph = $("#total_pph").numberbox('getValue');
                    var total_grand = $("#total_grand").numberbox('getValue');
                    var total_dp = $("#total_dp").numberbox('getValue');

                    // $.ajax({
                    //     type: "post",
                    //     url: "<?= base_url('closing/locks/checkLock') ?>",
                    //     data: "period=" + trans_date + "&menus_id=<?= $menus_id ?>",
                    //     dataType: "json",
                    //     success: function (lock) {
                    //         if(lock.total > 0){
                    //             toastr.error("This period is not active by Accounting");
                    //             return false;
                    //         }

                    if (parseFloat(balance_debit) == parseFloat(balance_credit)) {
                        if (por_no == "" || invoice_no == "" || supplier_id == "" || total_grand == "") {
                            toastr.error("please complete your input data");
                        } else {
                            $('#dg2').datagrid('acceptChanges');

                            var rows = $('#dg2').datagrid('getRows');;
                            var totalrows = rows.length;

                            var rows2 = $('#dg3').datagrid('getRows');
                            var totalrows2 = rows2.length;
                            endEditing2();

                            $.ajax({
                                type: "post",
                                url: "<?= base_url('finance/purchase_invoices/deleteJournal') ?>",
                                data: "number=" + number,
                                dataType: "json",
                                success: function(response) {
                                    Swal.fire({
                                        title: 'Please Wait for Saving Data',
                                        showConfirmButton: false,
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        },
                                    });

                                    if (totalrows > 0) {
                                        requestData(totalrows, rows);
                                        $('#dlg_insert').dialog('close');

                                        function requestData(total, json, jml = 1, value = 0) {
                                            if (value < 100) {
                                                value = Math.floor((jml / total) * 100);
                                                var i = (jml - 1);

                                                $.ajax({
                                                    type: "post",
                                                    url: '<?= base_url('finance/purchase_invoices/create') ?>',
                                                    data: {
                                                        type: type,
                                                        trans_date: trans_date,
                                                        number: number,
                                                        category_id: category_id,
                                                        supplier_id: supplier_id,
                                                        journal_type_id: journal_type_id,
                                                        invoice_no: invoice_no,
                                                        taxes: taxes,
                                                        payment_term: payment_term,
                                                        due_date: due_date,
                                                        voucher: voucher,
                                                        remarks: remarks,
                                                        faktur_no: faktur_no,
                                                        // total_sub: total_sub,
                                                        total_vat: total_vat,
                                                        total_dpp: total_dpp,
                                                        total_pph: total_pph,
                                                        // total_grand: total_grand,
                                                        total_dp: total_dp,
                                                        id: json[i].id,
                                                        por_no: json[i].por_no,
                                                        po_no: json[i].po_no,
                                                        item_rm_id: json[i].item_rm_id,
                                                        item_no: json[i].item_number,
                                                        item_name: json[i].item_name,
                                                        supplier_product: json[i].supplier_product,
                                                        uom: json[i].uom,
                                                        currency: json[i].currency,
                                                        rate: json[i].rate,
                                                        qty: json[i].qty,
                                                        price: json[i].price,
                                                        discount: json[i].discount,
                                                        total: json[i].total,
                                                        total_idr: json[i].total_local,
                                                        account_number: json[i].account_number,
                                                        account_type: json[i].account_type,
                                                    },
                                                    dataType: "json",
                                                    success: function(result) {
                                                        requestData(total, json, jml + 1, value);

                                                        // ----- FITUR AUTO-CREATE FIXED ASSET -----
                                                        if (jml == total) { // make-sure insert hanya dipanggil sekali / saat total sesuai
                                                            var pi_number = $("#number").val();
                                                            $.ajax({
                                                                method: 'post',
                                                                url: '<?= base_url('finance/purchase_invoices/autoFixedAsset/') ?>' + window.btoa(pi_number),
                                                                dataType: 'json',
                                                                success: function(response_fixed_asset) {
                                                                    console.log('Fixed Asset process finished.', response_fixed_asset);
                                                                },
                                                                error: function(xhr, status, error) {
                                                                    console.error('Error in autoFixedAsset:', error);
                                                                }
                                                            });
                                                        }

                                                        if (auto_posting_journal !== true) { // -- setting on/off di awal <script>
                                                            
                                                            if (jml == total) {
                                                                Swal.close();
                                                                Swal.fire({
                                                                    title: result.message,
                                                                    icon: result.theme,
                                                                    confirmButtonText: 'Ok',
                                                                    allowOutsideClick: false,
                                                                }).then((result) => {
                                                                    if (result.isConfirmed) {
                                                                        window.location.reload();
                                                                    }
                                                                });

                                                                $('#dg').datagrid('reload');
                                                            }

                                                        } else {
                                                            // ----- FITUR AUTO POSTING JOURNAL -----
                                                            if (jml == total) { 
                                                                Swal.close();
                                                                Swal.fire({
                                                                    title: "Add Posting Journal?",
                                                                    text: result.message + ". Do you want to save the Posting Journal too?",
                                                                    icon: (result.theme || "success"),
                                                                    confirmButtonText: 'Yes, Add to Journal!',
                                                                    allowOutsideClick: false,
                                                                    showCancelButton: true,
                                                                }).then((result) => {
                                                                    if (result.isConfirmed) {
                                                                        Swal.fire({
                                                                            title: 'Please Wait for Saving Data',
                                                                            showConfirmButton: false,
                                                                            allowOutsideClick: false,
                                                                            allowEscapeKey: false,
                                                                            didOpen: () => {
                                                                                Swal.showLoading();
                                                                            },
                                                                        });

                                                                        // AUTO GENERATE POSTING JOURNALS
                                                                        var modul = 'PURCHASE INVOICING';
                                                                        var journalDate = trans_date;
                                                                        var companyId = $("#company_id").val();
                                                                        var documentNo = $("#number").val();
                                                                        
                                                                        $.ajax({
                                                                            method: 'post',
                                                                            url: '<?= base_url('finance/journal_postings/datatablesTemp') ?>?journal_date=' + window.btoa(journalDate) +
                                                                            "&modul=" + window.btoa(modul) +
                                                                            "&company_id=" + window.btoa(companyId) +
                                                                            "&document_no=" + window.btoa(documentNo),
                                                                            data: {
                                                                                journal_date: window.btoa(journalDate),
                                                                                modul: window.btoa(modul),
                                                                                company_id: window.btoa(companyId),
                                                                                document_no: window.btoa(documentNo),
                                                                            },
                                                                            dataType: "json",
                                                                            success: function(dataPosting) {
                                                                                // console.log(JSON.stringify(dataPosting));
                                                                                $.ajax({
                                                                                    type: "post",
                                                                                    url: "<?= base_url('finance/journal_postings/number/') ?>" + window.btoa(journalDate),
                                                                                    dataType: "html",
                                                                                    success: function(noGL) {
                                                                                        var nomorGL = noGL;
                                                                                        var rowsData  = dataPosting.rows;
                                                                                        var totalData = dataPosting.total;

                                                                                        for (let no = 0; no < rowsData.length; no++) {
                                                                                            // console.log(rowsData[no]);
                                                                                            $.ajax({
                                                                                                type: "post",
                                                                                                url: '<?= base_url('finance/journal_postings/create') ?>',
                                                                                                data: {
                                                                                                    journal_date: journalDate,
                                                                                                    modul: modul,
                                                                                                    journal_type_id: journal_type_id,
                                                                                                    number: nomorGL,
                                                                                                    remarks: null,
                                                                                                    trans_date: rowsData[no].trans_date,
                                                                                                    document_no: rowsData[no].document_no,
                                                                                                    invoice_no: rowsData[no].invoice_no,
                                                                                                    company_name: rowsData[no].company_name,
                                                                                                    account_number: rowsData[no].account_number,
                                                                                                    account_name: rowsData[no].account_name,
                                                                                                    description: rowsData[no].description,
                                                                                                    currency: rowsData[no].currency,
                                                                                                    original_debit: rowsData[no].original_debit,
                                                                                                    original_credit: rowsData[no].original_credit,
                                                                                                    rates: rowsData[no].rates,
                                                                                                    local_debit: rowsData[no].local_debit,
                                                                                                    local_credit: rowsData[no].local_credit
                                                                                                },
                                                                                                dataType: "json",
                                                                                                success: function(responses) {
                                                                                                    if (responses.theme == "success") {
                                                                                                        console.log('Success auto-generate Posting Journals #' + no);
                                                                                                    } else {
                                                                                                        console.log('Failed! auto-generate Posting Journals #' + no);
                                                                                                        console.log(responses);
                                                                                                    }
                                                                                                }
                                                                                            });
                                                                                        }

                                                                                        Swal.fire({
                                                                                            title: "Good Job",
                                                                                            icon: "success",
                                                                                            text: "Data Successfully created to Posting Journal with code: " + nomorGL,
                                                                                            confirmButtonText: 'Done',
                                                                                            allowOutsideClick: false,
                                                                                        }).then(function(){ 
                                                                                            window.location.reload();
                                                                                        });
                                                                                    }
                                                                                });
                                                                            }
                                                                        });
                                                                        // END - AUTO GENERATE POSTING JOURNAL
                                                                    } else {
                                                                        // WITHOUT AUTO GENERATE POSTING JOURNAL
                                                                        Swal.fire({
                                                                            title: "Purchase Invoices",
                                                                            icon: "info",
                                                                            text: "Data Successfully saved without Posting Journal.",
                                                                            confirmButtonText: 'Done',
                                                                            allowOutsideClick: false,
                                                                        }).then(function(){ 
                                                                            window.location.reload();
                                                                        });
                                                                    }
                                                                });

                                                                $('#dg').datagrid('reload');
                                                            }
                                                            // ----- END FITUR AUTO POSTING JOURNAL -----
                                                        }
                                                        
                                                    }
                                                });
                                            }
                                        }

                                        if (totalrows2 > 0) {
                                            for (let z = 0; z < totalrows2; z++) {
                                                $.ajax({
                                                    type: "post",
                                                    url: '<?= base_url('finance/purchase_invoices/createJournals') ?>',
                                                    data: {
                                                        number: number,
                                                        account_number: rows2[z].account_number,
                                                        account_name: rows2[z].account_name,
                                                        debit: rows2[z].debit,
                                                        credit: rows2[z].credit,
                                                        flag: rows2[z].flag,
                                                    },
                                                    dataType: "json",
                                                    success: function(result2) {
                                                        // if (result2.theme == "success") {
                                                        //     toastr.success(result2.message, result2.title);
                                                        // } else {
                                                        //     toastr.error(result2.message, result2.title);
                                                        // }
                                                    }
                                                });
                                            }
                                        }

                                        $('#dg').datagrid('reload');
                                        $('#dlg_insert').dialog('close');
                                    
                                    } else {
                                        toastr.warning("please select your data in table first");
                                    }
                                }
                            });
                        }
                    } else {
                        toastr.error("Balance Debit Cannot match on Balance Credit");
                    }
                    //     }
                    // });
                }
            }]
        });

        $("#filter_type").combobox({
            onChange: function(val) {
                if (val == "PID") {
                    $("#filter_trans_date_from").datebox('enable');
                    $("#filter_trans_date_to").datebox('enable');
                    $("#filter_due_date_from").datebox('disable');
                    $("#filter_due_date_to").datebox('disable');
                } else if (val == "PAY") {
                    $("#filter_trans_date_from").datebox('disable');
                    $("#filter_trans_date_to").datebox('disable');
                    $("#filter_due_date_from").datebox('enable');
                    $("#filter_due_date_to").datebox('enable');
                } else {
                    $("#filter_trans_date_from").datebox('enable');
                    $("#filter_trans_date_to").datebox('enable');
                    $("#filter_due_date_from").datebox('enable');
                    $("#filter_due_date_to").datebox('enable');
                }
            }
        });

        $("#filter_category_id").combobox({
            url: '<?= base_url('master/item_categories/readsnotfg') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Product Category",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(item_category) {
                $("#filter_purchase_invoice").combobox({
                    url: '<?= base_url('finance/purchase_invoices/readPurchaseInvoice/') ?>' + item_category.id,
                    valueField: 'number',
                    textField: 'number',
                    prompt: "Choose Purchase Invoice No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $("#filter_purchase_receipt").combobox({
                    url: '<?= base_url('finance/purchase_invoices/readPurchaseReceipt/') ?>' + item_category.id,
                    valueField: 'por_no',
                    textField: 'por_no',
                    prompt: "Choose Purchase Receipt",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $("#filter_purchase_order").combobox({
                    url: '<?= base_url('finance/purchase_invoices/readPurchaseOrder/') ?>' + item_category.id,
                    valueField: 'po_no',
                    textField: 'po_no',
                    prompt: "Choose Purchase Order",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $("#filter_invoice_no").combobox({
                    url: '<?= base_url('finance/purchase_invoices/readInvoice/') ?>' + item_category.id,
                    valueField: 'invoice_no',
                    textField: 'invoice_no',
                    prompt: "Choose Invoice No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $("#filter_supplier").combobox({
            url: '<?= base_url('master/suppliers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        //form_data_isian
        $("#category_id").combobox({
            url: '<?= base_url('master/item_categories/readsnotfg') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Product Category",
            onSelect: function(item_category) {
                //GET SUPPLIER
                $('#supplier_id').combogrid({
                    // url: '<?= base_url('finance/purchase_invoices/readSupplierss?item_category_id=') ?>' + item_category.id,
                    url: '<?= base_url('finance/purchase_invoices/readSupplierx') ?>',
                    panelWidth: 420,
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Supplier",
                    columns: [
                        [{
                            field: 'number',
                            title: 'Supplier No',
                            width: 120
                        }, {
                            field: 'name',
                            title: 'Supplier Name',
                            width: 250
                        }, ]
                    ],
                    onSelect: function(index, row) {
                        console.log(row);
                        var trans_date = $("#trans_date").datebox('getValue');
                        var type = $("#type").combobox('getValue');

                        $("#company_id").textbox('setValue', row.id); // get company_id for posting journal
                        $("#payment_term").numberbox("setValue", row.payment_term);
                        $("#taxes").numberbox("setValue", row.vat);

                        if (row.vat_status == 'VAT') {
                            $("#faktur_no").textbox({
                                required: true
                            });
                        } else {
                            $("#faktur_no").textbox({
                                required: false
                            });
                        }

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('finance/purchase_invoices/readDueDate/') ?>" + window.btoa(trans_date) + "/" + row.payment_term,
                            dataType: "text",
                            success: function(due_date) {
                                $("#due_date").datebox('setValue', due_date);
                            }
                        });

                        if (type == "purchase") {
                            $("#por_no").combogrid({
                                url: '<?= base_url('finance/purchase_invoices/readReceipt?supplier_id=') ?>' + row.id + "&item_category_id=" + item_category.id,
                                panelWidth: 500,
                                idField: 'receipt_no',
                                textField: 'receipt_no',
                                valueField: 'receipt_no',
                                mode: 'remote',
                                multiple: true,
                                prompt: "Choose Purchase Order Receipts",
                                columns: [
                                    [ {
                                        field: 'ck', // Kolom checkbox
                                        checkbox: true, // Mengaktifkan checkbox
                                    }, {
                                        field: 'receipt_no',
                                        title: 'Receipt No',
                                        width: 150,
                                        align: 'left'
                                    }]
                                ],
                                fitColumns: true,
                                selectOnCheck: true,
                                checkOnSelect: true
                            });
                        } else if (type == "dp") {
                            $("#por_no").combogrid({
                                url: '<?= base_url('finance/purchase_invoices/readReceipt/dp?supplier_id=') ?>' + row.id + "&item_category_id=" + item_category.id,
                                panelWidth: 500,
                                idField: 'receipt_no',
                                textField: 'receipt_no',
                                valueField: 'receipt_no',
                                mode: 'remote',
                                multiple: true,
                                prompt: "Choose Purchase Order Receipts",
                                columns: [
                                    [ {
                                        field: 'ck', // Kolom checkbox
                                        checkbox: true, // Mengaktifkan checkbox
                                    }, {
                                        field: 'receipt_no',
                                        title: 'Receipt No',
                                        width: 150,
                                        align: 'left'
                                    }, {
                                        field: 'total_dp',
                                        title: 'DP',
                                        width: 100,
                                        align: 'left'
                                    }]
                                ],
                                fitColumns: true,
                                selectOnCheck: true,
                                checkOnSelect: true,
                                onSelect: function(row) {
                                    $("#total_dp").numberbox('setValue', row.total_dp);
                                }
                            });
                        } else {
                            $("#po_no").combobox({
                                url: '<?= base_url('purchase/purchase_order_others/readPono/') ?>' + window.btoa(row.id),
                                valueField: 'po_no',
                                textField: 'po_no',
                                prompt: "Choose Purchase Order Misc",
                            });
                        }
                    }
                });
            }
        });

        $("#journal_type").combobox({
            url: '<?= base_url('finance/journal_types/reads/' . base64_encode("PURCHASE INVOICING")) ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Journal Types",
            onSelect: function(row) {
                addTable2('<?= base_url('finance/purchase_invoices/readJournal/') ?>' + window.btoa(row.id));
            }
        });

        $("#pph").combobox({
            onChange: function(e) {
                var total_sub = $("#total_sub").numberbox('getValue');
                var total_vat = $("#total_vat").numberbox('getValue');
                var pph = $("#pph").combobox('getValue');

                if (pph != "1") {
                    var total_pph = parseFloat(total_sub * parseFloat(parseInt(pph) / 100));
                    $("#total_pph").numberbox('setValue', total_pph);
                } else {
                    $("#total_pph").numberbox('enable');
                    $("#total_pph").numberbox('setValue', 0);
                }

                var grand_total = (parseFloat(total_sub) + parseFloat(total_vat) - parseFloat(total_pph));
                $("#total_grand").numberbox('setValue', grand_total);
            }
        });
    });
    
    function btnDetails(val, row) {
        var details = "viewDetails('" + row.number + "')";
        return '<a class="btn btn-primary w-100" onClick="' + details + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
    }
    
    //Detail Data
    function viewDetails(number) {        
        $("#d_number").textbox('disable');
        $("#d_number").textbox('setValue', number);

        formMode = 'detail';
        var row = $('#dg').datagrid('getSelected');
        console.log("Data Loaded:",row);
        if (row) {
            $('#dlg_detail').dialog('open');
            $("#dlg_detail").window('setTitle', "Detail of " + row.number);
            
            $('#frm_detail').form('load', row);

            $("#d_trans_date").datebox('disable');
            $("#d_trans_date").datebox('setValue', row.trans_date);
            
            // -- Disable all form input
            $('#frm_detail').find('input, select, textarea').prop('disabled', true);
            $('#frm_detail').find('.easyui-textbox').textbox('disable');
            $('#frm_detail').find('.easyui-numberbox').numberbox('disable');
            $('#frm_detail').find('.easyui-passwordbox').passwordbox('disable');
            $('#frm_detail').find('.easyui-combobox').combobox('disable');
            $('#frm_detail').find('.easyui-combogrid').combogrid('disable');
            $('#frm_detail').find('.easyui-datebox').datebox('disable');
            $('#frm_detail').find('.easyui-datetimebox').datetimebox('disable');
            $('#frm_detail').find('input[type="checkbox"]').prop('disabled', true);
            $('#frm_detail').find('input[type="radio"]').prop('disabled', true);
            $('#frm_detail').find('textarea').prop('disabled', true);
            
            $.ajax({
                url: '<?= base_url('finance/purchase_invoices/readJournalType/') ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: window.btoa(row.journal_type_id)
                },
                success: function(response) {
                    if (response && response.name) {
                        $("#d_journal_type").textbox('setValue', response.name);
                    } else {
                        console.warn("Nama jurnal tidak ditemukan dalam respons atau format tidak sesuai:", response);
                        $("#d_journal_type").textbox('setValue', '-');
                    }
                }
            });

            $("#d_category_id").combobox({
                url: '<?= base_url('master/item_categories/readsnotfg') ?>',
                valueField: 'id',
                textField: 'name',
                prompt: "Choose Product Family",
                onLoadSuccess: function(item_category_load) {
                    $("#d_category_id").combobox('setValue', row.category_id);
                },
            });
            
            $("#d_type").combobox('setValue', row.type);
            $("#d_supplier_name").textbox('setValue', row.supplier_name);
            $("#d_por_no").textbox('setValue', row.por_no);
            $("#d_po_no").textbox('setValue', row.po_no);
            $("#d_invoice_no").textbox('setValue', row.invoice_no);
            $("#d_faktur_no").textbox('setValue', row.faktur_no);
            $("#d_taxes").textbox('setValue', row.taxes);
            $("#d_payment_term").textbox('setValue', row.payment_term);
            $("#d_due_date").textbox('setValue', row.due_date);
            $("#d_remarks").textbox('setValue', row.remarks);
            
            // Bagian Box kanan bawah 
            $("#d_total").textbox('setValue', formatPriceDetail(row.total));
            $("#d_total_discount").textbox('setValue', formatPriceDetail(row.total_discount));
            $("#d_total_grand").textbox('setValue', formatPriceDetail(row.total_grand));
            $("#d_total_invoice").textbox('setValue', formatPriceDetail(row.total_invoice));
            $("#d_total_local").textbox('setValue', formatPriceDetail(row.total_local));
            $("#d_total_pph").textbox('setValue', formatPriceDetail(row.total_pph));
            $("#d_total_sub").textbox('setValue', formatPriceDetail(row.total_sub));
            $("#d_total_vat").textbox('setValue', formatPriceDetail(row.total_vat));
            $("#d_total_dp").textbox('setValue', formatPriceDetail(row.total_dp));
            
            var d_total_dpp = parseFloat((row.total_sub) * 11/12);
            $("#d_total_dpp").textbox('setValue', formatPriceDetail(d_total_dpp));
            
            var lastIndex;
            var dg = $('#dgDetail').datagrid({
                url: '<?= base_url('finance/purchase_invoices/reads/') ?>' + window.btoa(row.number),
                onBeforeEdit: function(index, row) {
                    row.editing = true;
                    $(this).datagrid('refreshRow', index);
                },
                onAfterEdit: function(index, row) {
                    row.editing = false;
                    $(this).datagrid('refreshRow', index);
                },
                onCancelEdit: function(index, row) {
                    row.editing = false;
                    $(this).datagrid('refreshRow', index);
                },
            });

            addTableJournal('<?= base_url('finance/purchase_invoices/readJournals/') ?>' + window.btoa(row.number));

        } else {
            console.log("Click again to get Detail " + number);
            // toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }
    
    function formatPriceDetail(nominal) {
        return parseFloat(nominal).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function priceformat(value, row) {
        if (row.currency == "USD") {
            var digits = 2;
            var currency = 'USD';
            var format = "en-IN";
        } else if (row.currency == "JPY") {
            var digits = 2;
            var currency = 'JPY';
            var format = "ja-JP";
        } else if (row.currency == "EUR") {
            var digits = 2;
            var currency = 'EUR';
            var format = "de-DE";
        } else {
            var digits = 0;
            var currency = 'IDR';
            var format = "id-ID";
        }

        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: digits
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function priceformatlocal(value, row) {
        var digits = 0;
        var currency = 'IDR';
        var format = "id-ID";

        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: digits
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function statusformatInv(value, row) {
        var invoice = value.split('-');
        if (invoice[0] == "INVTMP") {
            return "<b style='color:green;'>TEMPORARY</b>";
        } else if (invoice[0] == "INVWDP") {
            return "<b style='color:green;'>DOWN PAYMENT</b>";
        } else {
            return "<b style='color:red;'>CLOSE</b>";
        }
    }

    function statusStyleInv(value, row, index) {
        var invoice = value.split('-');
        if (invoice[0] == "INVTMP") {
            return 'background-color:#C8FFCC;';
        } else if (invoice[0] == "INVWDP") {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function print_invoicing() {
        var row = $('#dg').datagrid('getSelections');
        console.log(row);
        if (row.length == 1) {
            var invoice_no = row[0].number;
            window.open("<?= base_url('finance/purchase_invoices/print_invoicing/') ?>" + window.btoa(invoice_no), "_blank", 'location=yes,height=600,width=1200,scrollbars=yes,status=yes');
        } else {
            toastr.warning("Please select one data in the table first!", "Information");
        }
    }

    function exportAccurate() {
        var rows = $('#dg').datagrid('getSelections');
        console.log(rows);
        if (rows.length > 0) {
            // Extract the selected IDs and join them into a comma-separated string
            var ids = rows.map(function(row) {
                return row.number;
            }).join(',');

            // Send the selected IDs to the exportAccurate function
            window.open('<?= base_url('finance/purchase_invoices/exportAccurate/') ?>' + window.btoa(ids));
        } else {
            toastr.warning("Please select one or more data in the table first!", "Information");
        }
    }

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('finance/purchase_invoices/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('finance/purchase_invoices/upload') ?>',
                    onSubmit: function() {
                        if (!$(this).form('validate')) {
                            return false; // Langsung kembalikan false jika validasi gagal
                        }
                        $.messager.progress({
                            title: 'Please Wait',
                            msg: 'Importing Excel to Database'
                        });
                        return true; // Lanjutkan proses submit
                    },
                    success: function(result) {
                        $.messager.progress('close');

                        // Periksa hasil JSON dari server dengan cara yang lebih aman
                        try {
                            var json = JSON.parse(result);
                            // Mulai proses upload berurutan
                            processUpload(json.total, json.data);
                        } catch (e) {
                            $.messager.alert('Error', 'Invalid JSON response from server.', 'error');
                        }
                    }
                });
            }
        }]
    });

    // Gunakan fungsi terpisah yang lebih bersih untuk proses rekursif
    function processUpload(total, data, index = 0) {
        // Kondisi berhenti rekursi: jika semua data sudah diproses
        if (index >= total) {
            // Proses upload 1 selesai, mulai proses upload 2
            getJournalAndProcess();
            return; // Hentikan fungsi
        }

        let number = index + 1;
        let value = Math.floor((number / total) * 100);
        let itemData = data[index];
        
        // Perbarui progress bar
        $('#p_upload').progressbar('setValue', value);
        $('#p_start').html(number);
        $('#p_finish').html(total);

        // Kirim data satu per satu
        $.ajax({
            type: "POST",
            url: "<?= base_url('finance/purchase_invoices/uploadCreate') ?>",
            data: { "data": itemData },
            dataType: "json",
            success: function(response) {
                let title;
                if (response.theme === "success") {
                    title = `<b style='color: green;'>${response.title}</b> | Invoice: ${response.message}`;
                } else {
                    title = `<b style='color: red;'>${response.title}</b> | Invoice: ${response.message}`;
                    // Kirim data gagal ke server (tanpa 'async: true' yang tidak diperlukan)
                    $.post("<?= base_url('finance/purchase_invoices/uploadcreateFailed') ?>", {
                        data: itemData,
                        message: response.message
                    });
                }
                $("#p_remarks").append(title + "<br>");

                // Lanjutkan rekursi untuk item berikutnya
                processUpload(total, data, index + 1);
            },
            error: function(xhr, status, error) {
                let errorMessage = `Gagal mengupload data nomor ${number}. Status: ${status}, Error: ${error}`;
                $("#p_remarks").append(`<b style='color: red;'>Error</b> | ${errorMessage}<br>`);
                // Lanjutkan rekursi meskipun ada error
                processUpload(total, data, index + 1);
            }
        });
    }

    // Fungsi untuk mendapatkan jurnal dan memulai proses kedua
    function getJournalAndProcess() {
        $.ajax({
            type: "POST",
            url: "<?= base_url('finance/purchase_invoices/uploadGetJournal') ?>",
            dataType: "json",
            success: function(journal) {
                console.log("Data journal: ", journal);
                processUpload2(journal.total, journal.data);
            },
            error: function(xhr, status, error) {
                $.messager.alert('Error', 'Failed to get journal data. Please check server.', 'error');
            }
        });
    }

    // Fungsi untuk proses upload kedua (jurnal)
    function processUpload2(total, data, index = 0) {
        if (index >= total) {
            $.messager.alert('Success', 'Semua proses upload dan calculate selesai.', 'info');
            // Clear file
            $.get("<?= base_url('finance/purchase_invoices/uploadclearFailed') ?>");
            return;
        }

        let number = index + 1;
        let value = Math.floor((number / total) * 100);
        let itemData = data[index];
        
        // Perbarui progress bar kedua
        $('#p_upload2').progressbar('setValue', value);
        $('#p_start2').html(number);
        $('#p_finish2').html(total);

        $.ajax({
            type: "POST",
            url: "<?= base_url('finance/purchase_invoices/uploadCreateJournal') ?>",
            data: { "data": itemData },
            dataType: "json",
            success: function(response) {
                let title;
                if (response.theme === "success") {
                    title = `<b style='color: green;'>${response.title}</b> | Journal: ${response.message}`;
                } else {
                    title = `<b style='color: red;'>${response.title}</b> | Journal: ${response.message}`;
                }
                $("#p_remarks").append(title + "<br>");

                processUpload2(total, data, index + 1);
            },
            error: function(xhr, status, error) {
                let errorMessage = `Gagal mengupload jurnal nomor ${number}. Status: ${status}, Error: ${error}`;
                $("#p_remarks").append(`<b style='color: red;'>Error</b> | ${errorMessage}<br>`);
                processUpload2(total, data, index + 1);
            }
        });
    }
    
    $('#dlg_upload_backup').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('finance/purchase_invoices/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                // Validasi form dan tampilkan progress
                if (!$('#frm_upload').form('validate')) {
                    return;
                }

                $.messager.progress({
                    title: 'Please Wait',
                    msg: 'Importing Excel to Database'
                });

                // Gunakan FormData untuk mengirim data formulir dan file (pengganti eval() tidak works di Chrome)
                var formData = new FormData($('#frm_upload')[0]);

                $.ajax({
                    url: '<?= base_url('finance/purchase_invoices/uploadclearFailed') ?>',
                    type: 'POST',
                    async: false, // Penting: pastikan proses selesai sebelum lanjut
                    success: function() {
                        console.log('Previous failed records cleared successfully.');
                    },
                    error: function() {
                        console.error('Failed to clear previous failed records.');
                    }
                });

                $.ajax({
                    url: '<?= base_url('finance/purchase_invoices/upload') ?>',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    processData: false, // Penting: Jangan memproses data
                    contentType: false, // Penting: Biarkan jQuery mengatur Content-Type
                    success: function(json) {
                        $.messager.progress('close');
                        requestData(json.total, json.data);
                    },
                    error: function(xhr, status, error) {
                        $.messager.progress('close');
                        console.error('AJAX Error:', error);
                        $.messager.alert('Error', 'Invalid JSON response from server. Change browser or contact admin.', 'error');
                    }
                });

                // Lanjutkan dengan fungsi rekursi seperti sebelumnya (di dalam success handler)
                function requestData(total, data_array, number = 1, success = 0, failed = 0) {
                    if (number > total) {
                        $.messager.alert('Upload Finished', `Import process completed.<br>Successful: ${success}<br>Failed: ${failed}`, 'info');
                        $('#dg').datagrid('reload');
                        return;
                    }

                    let value = Math.floor((number / total) * 100);
                    $('#p_upload').progressbar('setValue', value);
                    $('#p_start').html(number);
                    $('#p_finish').html(total);
                    $('#p_success').html(success);
                    $('#p_failed').html(failed);
                    
                    $.ajax({
                        type: "POST",
                        url: "<?= base_url('finance/purchase_invoices/uploadCreate') ?>",
                        data: { "data": data_array[number - 1] },
                        dataType: "json",
                        success: function(result_item_create) {
                            let title = '';
                            if (result_item_create.theme === "success") {
                                title = `<b style='color: green;'>${result_item_create.title}</b> | ${result_item_create.message}`;
                                success++;
                            } else {
                                // warning
                                if (result_item_create.theme === "warning") {
                                    title = `<b style='color: orange;'>${result_item_create.title}</b> | ${result_item_create.message}`;
                                    success++;
                                } else {
                                    // error
                                    title = `<b style='color: red;'>${result_item_create.title}</b> | ${result_item_create.message}`;
                                    failed++;
                                    
                                    $.ajax({
                                        type: "POST",
                                        url: "<?= base_url('finance/purchase_invoices/uploadcreateFailed') ?>",
                                        data: { data: data_array[number - 1], message: result_item_create.message },
                                        cache: false
                                    });
                                }
                            }
                            
                            $("#p_remarks").append(title + "<br>");
                            requestData(total, data_array, number + 1, success, failed);
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', error);
                            failed++;
                            $("#p_remarks").append(`<b style='color: red;'>Error</b> | Failed to process item: ${error}<br>`);
                            requestData(total, data_array, number + 1, success, failed);
                        }
                    });
                }
            }
        }]
    });
</script>

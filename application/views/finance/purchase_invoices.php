<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
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
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
</div>

<div id="toolbar3">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append2()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit3()"><i class="fa fa-times"></i> Remove</a>
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
                        <input style="width:60%;" required="" id="por_no" name="por_no" class="easyui-combobox">
                    </div>
                    <div class="fitem" id="type_selection_others">
                        <span style="width:35%; display:inline-block;">Purchase Order Misc</span>
                        <input style="width:60%;" required="" id="po_no" name="po_no" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="preview()" id="preview"><i class="fa fa-search"></i> Preview Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Journal Type</span>
                        <input style="width:60%;" required="" name="journal_type_id" id="journal_type" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Supplier Invoice</span>
                        <input style="width:60%;" required="" id="invoice_no" name="invoice_no" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Taxes</span>
                        <input style="width:60%;" id="taxes" name="taxes" readonly class="easyui-numberbox">
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
                </div>
            </fieldset>
        </div>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Purchase Invoice Lists" data-options="singleSelect: true" toolbar="#toolbar2">
            <thead>
                <tr>
                    <th rowspan="2" data-options="field:'action',width:120,formatter:buttonEdit">Action</th>
                    <th hidden rowspan="2" data-options="field:'id',width:150, editor: {type: 'textbox'}">ID</th>
                    <th rowspan="2" data-options="field:'por_no',width:150,editor: {type: 'textbox'}">POR. No</th>
                    <th rowspan="2" data-options="field:'po_no',width:150,editor: {type: 'textbox'}">PO. No</th>
                    <th rowspan="2" data-options="field:'item_rm_id',width:150,editor: {type: 'textbox'}" hidden>Product Id</th>
                    <th rowspan="2" data-options="field:'item_number',width:150,editor: {type: 'textbox', options: {required: true}}">Product No</th>
                    <th rowspan="2" data-options="field:'item_name',width:200,editor: {type: 'textbox', options: {required: true}}">Product Name</th>
                    <th rowspan="2" data-options="field:'supplier_product',width:200,editor: {type: 'textbox', options: {required: true}}">Supplier Product</th>
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
                            readonly: true,
                            onChange: function(value) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'total'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'price'
                                });

                                var price = $(ed2.target).numberbox('getValue');
                                $(ed.target).textbox('setValue', (parseFloat(value) * parseFloat(price)));
                            }
                        }
                    }">Qty</th>
                    <th colspan="3" data-options="field:'',align:'center'">Original Currency</th>
                    <th colspan="2" data-options="field:'',align:'center'">Local Currency</th>
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
                        valueField: 'number',
                        textField: 'number',
                        prompt: 'Choose Currencies'
                    }}">Currency</th>
                    <th data-options="field:'price', width:80, halign:'center', align:'right', formatter:numberformat,editor: {type: 'numberbox', 
                        options: {
                            readonly: true,
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

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty'
                                });

                                var qty = $(ed2.target).numberbox('getValue');
                                $(ed.target).textbox('setValue', (parseFloat(value) * parseFloat(qty)));
                            }
                        }}">Price</th>
                    <th data-options="field:'total',width:120, formatter:numberformat, halign:'center', align:'right',editor: {type: 'numberbox', options: {required: true, readonly: true, precision: 4}}">Amount</th>
                    <th data-options="field:'currency_local',align:'center',width:80">Currency</th>
                    <th data-options="field:'total_local',width:120, formatter:numberformat, halign:'center', align:'right'">Amount</th>
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

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>
<script>

    function check_vat(){
        var check_vat = $("#check_vat").checkbox('options');

        if(check_vat.checked == true){
            $("#total_vat").numberbox('enable');
        }else{
            $("#total_vat").numberbox('disable');
        }
    }

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
        $("#supplier_id").combobox('enable');
        $("#por_no").combobox('enable');
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

    function addJournal() {
        var rows = $('#dg2').datagrid('getRows');
        var taxes = $("#taxes").numberbox('getValue');
        var pphname = $("#pph").combobox('getValue');
        var check_vat = $("#check_vat").checkbox('options');

        var totalrows = rows.length;

        var rows2 = $('#dg3').datagrid('getRows');
        var totalrows2 = rows2.length;
        endEditing2();

        if (totalrows > 0) {
            var data_array = [];
            var data_array2 = [];
            var total_sub = 0;
            for (let i = 0; i < totalrows; i++) {
                var data = {
                    account_number: rows[i].account_number,
                    account_name: rows[i].account_name,
                    account_type: rows[i].account_type,
                    total: rows[i].total
                }

                if(rows[i].account_type == "DEBIT"){
                    total_sub += Math.abs(parseFloat(rows[i].total));
                }else{
                    total_sub -= Math.abs(parseFloat(rows[i].total));
                }

                data_array.push(data);
            }

            $("#total_sub").numberbox('setValue', total_sub);
            
            if(check_vat.checked == true){
                var disc_tax = $("#total_vat").numberbox('getValue');
            }else{
                var disc_tax = parseFloat(total_sub * (taxes / 100));
                $("#total_vat").numberbox('setValue', disc_tax);
            }

            var total_pph = $("#total_pph").numberbox('getValue');
            var total_grand = (parseFloat(total_sub) + parseFloat(disc_tax) - parseFloat(total_pph));
            $("#total_grand").numberbox('setValue', (total_grand));

            var pph_val = 0;
            var vat_val = 0;
            var arr_pph = ["2031101", "2031103", "2031104"];
            var arr_ap = ["2022101", "2022102", "2022103"];

            for (let z = 0; z < totalrows2; z++) {
                if (rows2[z].account_number == "1154105") {
                    var debit = disc_tax;
                    var credit = 0;
                    vat_val = 1;
                } else {
                    var debit = rows2[z].debit;
                    var credit = rows2[z].credit;
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

                var data2 = {
                    account_number: rows2[z].account_number,
                    account_name: rows2[z].account_name,
                    debit: debit,
                    credit: credit,
                    flag: rows2[z].flag,
                }

                data_array2.push(data2);
            }

            if (taxes > 0 && vat_val == 0) {
                var data2 = {
                    account_number: "1154105",
                    account_name: "TAX",
                    debit: disc_tax,
                    credit: 0,
                    flag: "3",
                }

                data_array2.push(data2);
            }

            if (total_pph > 0 && pph_val == 0 && pphname == "5") {
                var data2 = {
                    account_number: "2031101",
                    account_name: "INCOME TAX ART 21",
                    debit: 0,
                    credit: total_pph,
                    flag: "4",
                }

                data_array2.push(data2);
            }

            if (total_pph > 0 && pph_val == 0 && pphname == "1") {
                var data2 = {
                    account_number: "2031103",
                    account_name: "INCOME TAX ART 23",
                    debit: 0,
                    credit: total_pph,
                    flag: "4",
                }

                data_array2.push(data2);
            }

            if (total_pph > 0 && pph_val == 0 && pphname == "2") {
                var data2 = {
                    account_number: "2031103",
                    account_name: "INCOME TAX ART 23",
                    debit: 0,
                    credit: total_pph,
                    flag: "4",
                }

                data_array2.push(data2);
            }

            if (total_pph > 0 && pph_val == 0 && pphname == "10") {
                var data2 = {
                    account_number: "2031104",
                    account_name: "INCOME TAX ART 4 (2)",
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
                "action": 0
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
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if(row.status == 0){
                if(row.gl_no == null){
                    $('#dlg_insert').dialog('open');
                    $('#frm_insert').form('load', row);

                    $("#type_selection_others").hide();
                    $("#type_selection_purchase").hide();
                    $("#type_selection_dp").hide();

                    $("#taxes").numberbox('setValue', row.taxes);

                    $("#type").combobox({
                        readonly: true
                    });
                    $("#trans_date").datebox('disable');
                    //$("#category_id").combobox('disable');
                    $("#supplier_id").combobox('disable');
                    $("#por_no").combobox('disable');
                    $("#po_no").combobox('disable');
                    $("#preview").linkbutton('disable');

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
                                url: '<?= base_url('master/supplier_items/readSupplierss?item_category_id=') ?>' + item_category.id,
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
                toastr.error("AP Payment Status is closed");
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
        var por_no = $("#por_no").combobox('getText');
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
                    url: '<?= base_url('finance/purchase_invoices/datatablesTemp') ?>?por_no=' + window.btoa(por_no),
                    onLoadSuccess: function(row) {
                        $("#total_sub").numberbox('setValue', row.total_sub);
                        var disc_tax = parseFloat(row.total_sub * (taxes / 100));
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
                        var disc_tax = parseFloat(row.total_sub * (taxes / 100));
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

                                if(row.status == 0){
                                    if(row.gl_no == null){
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
                                    }else{
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

    function print_invoicing() {
        var filter_purchase_invoice = $("#filter_purchase_invoice").combobox('getValue');

        if (filter_purchase_invoice == "") {
            toastr.warning("Please select Purchase Invoice No!");
        } else {
            window.open("<?= base_url('finance/purchase_invoices/print_invoicing/') ?>" + window.btoa(filter_purchase_invoice), "_blank", 'location=yes,height=600,width=1200,scrollbars=yes,status=yes');
        }

    }

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

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
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

                    var balance_debit = $("#balance_debit").numberbox('getValue');
                    var balance_credit = $("#balance_credit").numberbox('getValue');

                    var total_sub = $("#total_sub").numberbox('getValue');
                    var total_vat = $("#total_vat").numberbox('getValue');
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
                                                                //total_sub: total_sub,
                                                                total_vat: total_vat,
                                                                total_pph: total_pph,
                                                                //total_grand: total_grand,
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
                                                toastr.warning("please selections your data in table first");
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
                    url: '<?= base_url('master/supplier_items/readSupplierss?item_category_id=') ?>' + item_category.id,
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

                        $("#payment_term").numberbox("setValue", row.payment_term);
                        $("#taxes").numberbox("setValue", row.vat);

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('finance/purchase_invoices/readDueDate/') ?>" + window.btoa(trans_date) + "/" + row.payment_term,
                            dataType: "text",
                            success: function(due_date) {
                                $("#due_date").datebox('setValue', due_date);
                            }
                        });

                        if (type == "purchase") {
                            $("#por_no").combobox({
                                url: '<?= base_url('finance/purchase_invoices/readReceipt?supplier_id=') ?>' + row.id + "&item_category_id=" + item_category.id,
                                valueField: 'receipt_no',
                                textField: 'receipt_no',
                                multiple: true,
                                prompt: "Choose Purchase Order Receipts",
                            });
                        } else if (type == "dp") {
                            $("#por_no").combobox({
                                url: '<?= base_url('finance/purchase_invoices/readReceipt/dp?supplier_id=') ?>' + row.id + "&item_category_id=" + item_category.id,
                                valueField: 'receipt_no',
                                textField: 'receipt_no',
                                prompt: "Choose Purchase Order Receipts",
                                onSelect: function(row) {
                                    $("#total_dp").numberbox('setValue', row.total_dp);
                                }
                            });
                        } else {
                            $("#po_no").combobox({
                                url: '<?= base_url('purchase/purchase_order_others/readPono/') ?>' + row.id,
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

                if(pph != "1"){
                    var total_pph = parseFloat(total_sub * parseFloat(parseInt(pph) / 100));
                    $("#total_pph").numberbox('setValue', total_pph);
                }else{
                    $("#total_pph").numberbox('enable');
                    $("#total_pph").numberbox('setValue', 0);
                }

                var grand_total = (parseFloat(total_sub) + parseFloat(total_vat) - parseFloat(total_pph));
                $("#total_grand").numberbox('setValue', grand_total);
            }
        });
    });

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
</script>
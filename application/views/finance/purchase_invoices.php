<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Purchase Invoice No</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center',formatter:statusformat,styler:statusStyle">Payment<br>Status</th>
            <th rowspan="2" data-options="field:'status_invoice',width:110,align:'center',formatter:statusformatInv,styler:statusStyleInv">Supplier<br>Invoice</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'supplier_name',width:200,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'invoice_no',width:150,halign:'center'">Invoice No</th>
            <th rowspan="2" data-options="field:'taxes',width:80,halign:'center',align:'right'">Taxes %</th>
            <th rowspan="2" data-options="field:'payment_term',width:100,align:'center'">Payment Term <br>(Days)</th>
            <th rowspan="2" data-options="field:'due_date',width:100,align:'center'">Payment Due</th>
            <th rowspan="2" data-options="field:'currency',width:100,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'total_sub',width:120,halign:'center',align:'right',formatter: priceformat">Sub Total</th>
            <th rowspan="2" data-options="field:'total_vat',width:120,halign:'center',align:'right',formatter: priceformat">VAT</th>
            <th rowspan="2" data-options="field:'total_pph',width:120,halign:'center',align:'right',formatter: priceformat">PPH</th>
            <th rowspan="2" data-options="field:'total_grand',width:120,halign:'center',align:'right',formatter: priceformat">Grand Total</th>
            <th rowspan="2" data-options="field:'total_dp',width:120,halign:'center',align:'right',formatter: priceformat">Down Payment</th>
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
<div id="toolbar" style="height: 300px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->

    <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float: left;">
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
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" name="filter_family_id" id="filter_family_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purhcase Invoice No</span>
                <input style="width:60%;" name="filter_purchase_invoice" id="filter_purchase_invoice" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier Status</span>
                <select style="width:60%;" id="filter_status_supplier" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="INVWDP">DOWN PAYMENT</option>
                    <option value="INVTMP">TEMPORARY</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Invoice No</span>
                <input style="width:60%;" id="filter_invoice_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Order</span>
                <input style="width:60%;" id="filter_purchase_order" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Order Receipt</span>
                <input style="width:60%;" id="filter_purchase_receipt" class="easyui-combobox">
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
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1400px; height: 700px; padding:10px; top: 20px;">
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
                        <span style="width:35%; display:inline-block;">Product Family</span>
                        <input style="width:60%;" required="" name="family_id" id="family_id" class="easyui-combobox">
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
                        <span style="width:35%; display:inline-block;">Supplier Invoice</span>
                        <input style="width:60%;" required="" id="invoice_no" name="invoice_no" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Taxes</span>
                        <select style="width:60%;" id="taxes" name="taxes" required class="easyui-combobox" panelHeight="auto">
                            <option value="0">0%</option>
                            <option value="11">11%</option>
                            <option value="10">10%</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment Term</span>
                        <input style="width:60%;" required="" readonly="" id="payment_term" name="payment_term" class="easyui-numberbox" data-options="buttonText:'Days',buttonAlign:'right'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment Due</span>
                        <input style="width:60%;" disabled id="due_date" name="due_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
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
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="list Purchase Invoicing" data-options="singleSelect: false" idField="no_id">
            <thead>
                <tr>
                    <th rowspan="2" field="ck" checkbox="true"></th>
                    <th rowspan="2" data-options="field:'delete',width:50, formatter:removebtn">#</th>
                    <th rowspan="2" data-options="field:'por_no',width:150">POR. No</th>
                    <th rowspan="2" data-options="field:'po_no',width:150">PO. No</th>
                    <th rowspan="2" data-options="field:'item_id',width:150" hidden>Product Id</th>
                    <th rowspan="2" data-options="field:'item_number',width:150">Product No</th>
                    <th rowspan="2" data-options="field:'item_name',width:200">Product Name</th>
                    <th rowspan="2" data-options="field:'uom',align:'center',width:80">UoM</th>
                    <th rowspan="2" data-options="field:'qty',width:80, formatter:numberformat">Qty</th>
                    <th colspan="3" data-options="field:'',align:'center'">Original Currency</th>
                    <th colspan="2" data-options="field:'',align:'center'">Local Currency</th>
                    <th rowspan="2" data-options="field:'account_number',width:140, halign:'center', editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('finance/account_coa/reads') ?>',
                            valueField: 'account_number',
                            textField: 'account_name',
                            prompt: 'Choose Account No',
                        }}">Account No</th>
                    <th rowspan="2" data-options="field:'account_type',width:120, halign:'center', editor: {
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
                    <th data-options="field:'currency',align:'center',width:80">Currency</th>
                    <th data-options="field:'price',width:80, halign:'center', align:'right', formatter:priceformat">Price</th>
                    <th data-options="field:'total',width:120, formatter:priceformat, halign:'center', align:'right'">Amount</th>
                    <th data-options="field:'currency_local',align:'center',width:80">Currency</th>
                    <th data-options="field:'total_local',width:120, formatter:priceformatlocal, halign:'center', align:'right'">Amount</th>
                </tr>
            </thead>
        </table>

        <div style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: left; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <div style="width: 100%; float: left;">
                    <table style="width: 100%;">
                        <tr>
                            <th width="200">ACCOUNT NAME</th>
                            <th width="150">DEBT</th>
                            <th width="150">CREDIT</th>
                        </tr>
                        <tr>
                            <th><input style="width:100%;" id="account_purchase_name" name="account_purchase_name" disabled class="easyui-textbox"></th>
                            <th><input style="width:100%;" id="account_purchase_debt" name="account_purchase_debt" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','"></th>
                            <th><input style="width:100%;" id="account_purchase_credit" name="account_purchase_credit" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','"></th>
                        </tr>
                        <tr>
                            <th><input style="width:100%;" id="account_pay_name" name="account_pay_name" disabled class="easyui-textbox"></th>
                            <th><input style="width:100%;" id="account_pay_debt" name="account_pay_debt" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','"></th>
                            <th><input style="width:100%;" id="account_pay_credit" name="account_pay_credit" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','"></th>
                        </tr>
                        <tr>
                            <th><input style="width:100%;" id="account_bal_name" name="account_bal_name" disabled class="easyui-textbox"></th>
                            <th><input style="width:100%;" id="account_bal_debt" name="account_bal_debt" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','"></th>
                            <th><input style="width:100%;" id="account_bal_credit" name="account_bal_credit" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','"></th>
                        </tr>
                    </table>
                </div>
            </fieldset>
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
                        <input style="width:60%;" id="total_vat" name="total_vat" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">PPH</b>
                        <input style="width:30%;" id="total_pph" name="total_pph" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                        <select style="width:30%;" id="pph" name="pph" class="easyui-combobox" required data-options="prompt: 'PPH'" panelHeight="auto">
                            <option value="0">NON PPH</option>
                            <option value="5">PPH 21</option>
                            <option value="2">PPH 23</option>
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
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');

        $("#type_selection_others").hide();
        $("#type_selection_purchase").hide();
        $("#type_selection_dp").hide();

        $("#type").combobox({
            readonly:false
        });
        $("#trans_date").datebox('enable');
        $("#family_id").combobox('enable');
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
            onChange: function(t){
                var type = $("#type").combobox('getValue');
                
                if(type == "purchase"){
                    $("#type_selection_purchase").show();
                    $("#type_selection_others").hide();
                    $("#type_selection_dp").hide();
                    $("#total_dp").numberbox('clear');
                }else if(type == "dp"){
                    $("#type_selection_purchase").show();
                    $("#type_selection_others").hide();
                    $("#type_selection_dp").show();
                }else{
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
                    success: function (invoice_no) {
                        $("#invoice_no").textbox('setValue', invoice_no);
                    }
                });
            }
        });
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if(row.status == 0){
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);

                $("#type_selection_others").hide();
                $("#type_selection_purchase").hide();
                $("#type_selection_dp").hide();

                $("#type").combobox({
                    readonly:true
                });
                $("#trans_date").datebox('disable');
                $("#family_id").combobox('disable');
                $("#supplier_id").combobox('disable');
                $("#por_no").combobox('disable');
                $("#po_no").combobox('disable');
                $("#preview").linkbutton('disable');
                        
                if(row.type == "purchase"){
                    $("#type_selection_purchase").show();
                    $("#type_selection_others").hide();
                    $("#type_selection_dp").hide();
                    $("#total_dp").numberbox('clear');
                }else if(row.type == "dp"){
                    $("#type_selection_purchase").show();
                    $("#type_selection_others").hide();
                    $("#type_selection_dp").show();
                }else{
                    $("#type_selection_others").show();
                    $("#type_selection_purchase").hide();
                    $("#type_selection_dp").hide();
                    $("#total_dp").numberbox('clear');
                }

                $("#family_id").combobox({
                    url: '<?= base_url('master/item_familys/readNotFg') ?>',
                    valueField: 'id',
                    textField: 'name',
                    prompt: "Choose Product Family",
                    onLoadSuccess: function(item_family_load) {
                        $("#family_id").combobox('setValue', row.family_id);
                    },
                    onSelect: function(item_family) {
                        //GET SUPPLIER
                        $('#supplier_id').combogrid({
                            url: '<?= base_url('master/supplier_items/readSuppliers?item_family_id=') ?>' + item_family.id,
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
                            onLoadSuccess: function(item_family_load) {
                                $("#supplier_id").combogrid('setValue', row.supplier_id);
                            },
                        });
                    }
                });

                $("#taxes").combobox({
                    onChange: function(t){
                        var taxes = $("#taxes").combobox('getValue');
                        
                        $("#total_sub").numberbox('setValue', row.total_sub);
                        var disc_tax = parseFloat(row.total_sub * (taxes / 100));
                        $("#total_vat").numberbox('setValue', disc_tax);

                        $("#pph").combobox('clear');
                        $("#total_pph").numberbox('clear');
                        $("#total_grand").numberbox('clear');
                    }
                });
                
                var lastIndex;
                var dg = $('#dg2').datagrid({
                    url: '<?= base_url('finance/purchase_invoices/reads/') ?>' + window.btoa(row.number),
                    onClickRow: function(rowIndex) {
                        if (lastIndex != rowIndex) {
                            $(this).datagrid('endEdit', lastIndex);
                            $(this).datagrid('beginEdit', rowIndex);
                        }
                        lastIndex = rowIndex;
                    },
                });
            }else{
                toastr.error("Cannot Update because payment status is closed");
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
        var taxes = $("#taxes").combobox('getValue');

        if (trans_date == "" || invoice_no == "" || taxes == "") {
            toastr.info('Please completed your data');
        } else {
            var lastIndex;
            if(por_no != ""){
                var dg = $('#dg2').datagrid({
                    url: '<?= base_url('finance/purchase_invoices/datatablesTemp') ?>?por_no=' + window.btoa(por_no),
                    onLoadSuccess: function(row) {
                        $("#total_sub").numberbox('setValue', row.total_sub);
                        var disc_tax = parseFloat(row.total_sub * (taxes / 100));
                        $("#total_vat").numberbox('setValue', disc_tax);

                        $("#pph").combobox('clear');
                        $("#total_pph").numberbox('clear');
                        $("#total_grand").numberbox('clear');
                    },
                    onClickRow: function(rowIndex) {
                        if (lastIndex != rowIndex) {
                            $(this).datagrid('endEdit', lastIndex);
                            $(this).datagrid('beginEdit', rowIndex);
                        }
                        lastIndex = rowIndex;
                    },
                });
            }else if(po_no != ""){
                var dg = $('#dg2').datagrid({
                    url: '<?= base_url('finance/purchase_invoices/datatablesTemp2') ?>?po_no=' + window.btoa(po_no),
                    onLoadSuccess: function(row) {
                        $("#total_sub").numberbox('setValue', row.total_sub);
                        var disc_tax = parseFloat(row.total_sub * (taxes / 100));
                        $("#total_vat").numberbox('setValue', disc_tax);

                        $("#pph").combobox('clear');
                        $("#total_pph").numberbox('clear');
                        $("#total_grand").numberbox('clear');
                    },
                    onClickRow: function(rowIndex) {
                        if (lastIndex != rowIndex) {
                            $(this).datagrid('endEdit', lastIndex);
                            $(this).datagrid('beginEdit', rowIndex);
                        }
                        lastIndex = rowIndex;
                    },
                });
            }else{
                toastr.info('Please completed your data');
            }
        }
    }

    function removebtn(value, row, index) {
        return "<a href='#' onclick='removeit(" + index + "," + row.total_local + ")' style='pointer-events:auto !important; opacity:1;' class='btn btn-sm btn-danger w-100'><i class='fa fa-times'></i></a>";
    }

    function removeit(indexs, total) {
        toastr.success('Deleted Success');
        var total_sub = $("#total_sub").numberbox('getValue');
        $("#total_sub").numberbox('setValue', (parseFloat(total_sub) - parseFloat(total)));

        var taxes = $("#taxes").combobox('getValue');
        var disc_tax = parseFloat((parseFloat(total_sub) - parseFloat(total)) * parseFloat(taxes / 100));
        $("#total_vat").numberbox('setValue', disc_tax);

        $("#pph").combobox('clear');
        $("#total_pph").numberbox('clear');
        $("#total_grand").numberbox('clear');

        $("#dg2").datagrid("deleteRow", indexs);
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
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
        var filter_family_id = $("#filter_family_id").combobox('getValue');
        var filter_purchase_invoice = $("#filter_purchase_invoice").combobox('getValue');
        var filter_purchase_receipt = $("#filter_purchase_receipt").combobox('getValue');
        var filter_purchase_order = $("#filter_purchase_order").combobox('getValue');
        var filter_status_supplier = $("#filter_status_supplier").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_family_id=" + window.btoa(filter_family_id) +
            "&filter_purchase_invoice=" + window.btoa(filter_purchase_invoice) +
            "&filter_purchase_receipt=" + window.btoa(filter_purchase_receipt) +
            "&filter_purchase_order=" + window.btoa(filter_purchase_order) +
            "&filter_status_supplier=" + window.btoa(filter_status_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
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
        var filter_family_id = $("#filter_family_id").combobox('getValue');
        var filter_purchase_invoice = $("#filter_purchase_invoice").combobox('getValue');
        var filter_purchase_receipt = $("#filter_purchase_receipt").combobox('getValue');
        var filter_purchase_order = $("#filter_purchase_order").combobox('getValue');
        var filter_status_supplier = $("#filter_status_supplier").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_family_id=" + window.btoa(filter_family_id) +
            "&filter_purchase_invoice=" + window.btoa(filter_purchase_invoice) +
            "&filter_purchase_receipt=" + window.btoa(filter_purchase_receipt) +
            "&filter_purchase_order=" + window.btoa(filter_purchase_order) +
            "&filter_status_supplier=" + window.btoa(filter_status_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('finance/purchase_invoices/print/excel') ?>' + url);
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

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('finance/purchase_invoices/datatables') ?>',
            pagination: true,
            rownumbers: true,
            height: '810px',
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
                            formatter: priceformat
                        }, {
                            field: 'total',
                            title: 'Total',
                            width: 150,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
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
                    var family_id = $("#family_id").combogrid('getValue');
                    var supplier_id = $("#supplier_id").combogrid('getValue');
                    var invoice_no = $("#invoice_no").textbox('getValue');
                    var taxes = $("#taxes").combobox('getValue');
                    var payment_term = $("#payment_term").numberbox('getValue');
                    var due_date = $("#due_date").datebox('getValue');
                    var voucher = $("#voucher").textbox('getValue');
                    var remarks = $("#remarks").textbox('getValue');

                    var total_sub = $("#total_sub").numberbox('getValue');
                    var total_vat = $("#total_vat").numberbox('getValue');
                    var total_pph = $("#total_pph").numberbox('getValue');
                    var total_grand = $("#total_grand").numberbox('getValue');
                    var total_dp = $("#total_dp").numberbox('getValue');

                    if (por_no == "" || invoice_no == "" || supplier_id == "" || total_grand == "") {
                        toastr.error("please complete your input data");
                    } else {
                        $('#dg2').datagrid('acceptChanges');
                        var rows = $('#dg2').datagrid('getSelections');
                        var totalrows = rows.length;

                        if (totalrows > 0) {
                            for (let i = 0; i < totalrows; i++) {
                                if (rows[i].po_no) {
                                    $.ajax({
                                        type: "post",
                                        url: '<?= base_url('finance/purchase_invoices/create') ?>',
                                        data: {
                                            type: type,
                                            trans_date: trans_date,
                                            number: number,
                                            family_id: family_id,
                                            supplier_id: supplier_id,
                                            invoice_no: invoice_no,
                                            taxes: taxes,
                                            payment_term: payment_term,
                                            due_date: due_date,
                                            voucher: voucher,
                                            remarks: remarks,
                                            total_sub: total_sub,
                                            total_vat: total_vat,
                                            total_pph: total_pph,
                                            total_grand: total_grand,
                                            total_dp: total_dp,
                                            por_no: rows[i].por_no,
                                            po_no: rows[i].po_no,
                                            item_id: rows[i].item_id,
                                            item_no: rows[i].item_number,
                                            item_name: rows[i].item_name,
                                            uom: rows[i].uom,
                                            currency: rows[i].currency,
                                            qty: rows[i].qty,
                                            price: rows[i].price,
                                            discount: rows[i].discount,
                                            total: rows[i].total,
                                            total_idr: rows[i].total_local,
                                            account_number: rows[i].account_number,
                                            account_type: rows[i].account_type,
                                        },
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                toastr.success(result.message, result.title);
                                            } else {
                                                toastr.error(result.message, result.title);
                                            }
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

        $("#filter_family_id").combobox({
            url: '<?= base_url('master/item_familys/readNotFg') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Product Family",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(item_family) {
                $("#filter_purchase_invoice").combobox({
                    url: '<?= base_url('finance/purchase_invoices/readPurchaseInvoice/') ?>' + item_family.id,
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
                    url: '<?= base_url('finance/purchase_invoices/readPurchaseReceipt/') ?>' + item_family.id,
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
                    url: '<?= base_url('finance/purchase_invoices/readPurchaseOrder/') ?>' + item_family.id,
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
                    url: '<?= base_url('finance/purchase_invoices/readInvoice/') ?>' + item_family.id,
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

        $("#family_id").combobox({
            url: '<?= base_url('master/item_familys/readNotFg') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Product Family",
            onSelect: function(item_family) {
                //GET SUPPLIER
                $('#supplier_id').combogrid({
                    url: '<?= base_url('master/supplier_items/readSuppliers?item_family_id=') ?>' + item_family.id,
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
                        var trans_date = $("#trans_date").datebox('getValue');
                        var type = $("#type").combobox('getValue');
                        $("#payment_term").numberbox("setValue", row.payment_term);

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('finance/purchase_invoices/readDueDate/') ?>" + window.btoa(trans_date) + "/" + row.payment_term,
                            dataType: "text",
                            success: function(due_date) {
                                $("#due_date").datebox('setValue', due_date);
                            }
                        });

                        if(type == "purchase"){
                            $("#por_no").combobox({
                                url: '<?= base_url('finance/purchase_invoices/readReceipt?supplier_id=') ?>' + row.id + "&item_family_id=" + item_family.id,
                                valueField: 'receipt_no',
                                textField: 'receipt_no',
                                multiple: true,
                                prompt: "Choose Purchase Order Receipts",
                            });
                        }else if(type == "dp"){
                            $("#por_no").combobox({
                                url: '<?= base_url('finance/purchase_invoices/readReceipt/dp?supplier_id=') ?>' + row.id + "&item_family_id=" + item_family.id,
                                valueField: 'receipt_no',
                                textField: 'receipt_no',
                                prompt: "Choose Purchase Order Receipts",
                                onSelect: function(row){
                                    $("#total_dp").numberbox('setValue', row.total_dp);
                                }
                            });
                        }else{
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

        $("#pph").combobox({
            onChange: function(e){
                var total_sub = $("#total_sub").numberbox('getValue');
                var total_vat = $("#total_vat").numberbox('getValue');
                var pph = $("#pph").combobox('getValue');
                var total_pph = parseFloat(total_sub * (pph / 100));
                $("#total_pph").numberbox('setValue', total_pph);

                var grand_total = parseFloat(total_sub - total_vat - total_pph);
                $("#total_grand").numberbox('setValue', grand_total);
            }
        })
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
        }else{
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
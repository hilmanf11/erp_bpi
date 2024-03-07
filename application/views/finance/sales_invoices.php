<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Sales Invoice No</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center',formatter:statusformat,styler:statusStyle">Receipt<br>Status</th>
            <th rowspan="2" data-options="field:'gl_no',width:100,align:'center'">GL NO</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'taxes',width:80,halign:'center',align:'right'">Taxes %</th>
            <th rowspan="2" data-options="field:'payment_term',width:100,align:'center'">Payment Term <br>(Days)</th>
            <th rowspan="2" data-options="field:'due_date',width:100,align:'center'">Payment Due</th>
            <th rowspan="2" data-options="field:'currency',width:100,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'total_sub',width:120,halign:'center',align:'right',formatter: priceformat">Sub Total</th>
            <th rowspan="2" data-options="field:'total_vat',width:120,halign:'center',align:'right',formatter: priceformat">VAT</th>
            <th rowspan="2" data-options="field:'total_pph',width:120,halign:'center',align:'right',formatter: priceformat">PPH</th>
            <th rowspan="2" data-options="field:'total_grand',width:120,halign:'center',align:'right',formatter: priceformat">Grand Total</th>
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
<div id="toolbar" style="height: 270px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->

    <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type Date</span>
                <select style="width:60%;" id="filter_type" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="PID">Sales Invoice Date</option>
                    <option value="PAY">Payment Due</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Invoice Date</span>
                <input style="width:30%;" id="filter_trans_date_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_trans_date_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment Due</span>
                <input style="width:30%;" id="filter_due_date_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_due_date_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" name="filter_customer" id="filter_customer" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_commercial()"><i class="fa fa-print"></i> Commercial Invoice</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_invoice()"><i class="fa fa-print"></i> Sales Invoice</a>
            </div>
        </div>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery Note</span>
                <input style="width:60%;" id="filter_dn_number" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Invoice No</span>
                <input style="width:60%;" name="filter_sales_invoice" id="filter_sales_invoice" class="easyui-combobox">
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 99%; height: 600px; padding:10px; top: 10px; left:5px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Sales Invoice Date</span>
                        <input style="width:60%;" id="trans_date" name="trans_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Sales Invoice No</span>
                        <input style="width:60%;" readonly id="number" name="number" class="easyui-textbox" data-options="prompt:'Automatic From Purchase Invoce Date & Customer'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Journal Type</span>
                        <input style="width:60%;" required="" name="journal_type_id" id="journal_type" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Customer Name</span>
                        <input style="width:60%;" required="" id="customer_id" name="customer_name" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Delivery Note</span>
                        <input style="width:60%;" required="" id="dn_number" name="dn_number" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="preview()" id="preview"><i class="fa fa-search"></i> Preview Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Taxes</span>
                        <input style="width:30%;" id="taxes" name="taxes" readonly class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment Term</span>
                        <input style="width:30%;" name="payment_term" readonly="" id="payment_term" class="easyui-numberbox" data-options="buttonText:'Days',buttonAlign:'right'">
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
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Sales Invoicing Lists" data-options="singleSelect: true" toolbar="#toolbar2" rownumbers="true" , idField="dn_number">
            <thead>
                <tr>
                    <th data-options="field:'delete',width:120,formatter:removebtn">Action</th>
                    <th hidden data-options="field:'id',width:150,editor: {type: 'textbox'}">ID</th>
                    <th data-options="field:'dn_number',width:150,editor: {type: 'textbox', options: {required: true}}">Delivery Note</th>
                    <th data-options="field:'so_number',width:160,editor: {type: 'textbox', options: {required: true}}">SO. No</th>
                    <th data-options="field:'customer_po',width:120,editor: {type: 'textbox', options: {required: true}}">Customer PO</th>
                    <th data-options="field:'item_id',width:150" hidden>Product Id</th>
                    <th data-options="field:'item_no',width:150,editor: {type: 'textbox', options: {required: true}}">Product No</th>
                    <th data-options="field:'item_name',width:200,editor: {type: 'textbox', options: {required: true}}">Product Name</th>
                    <th data-options="field:'uom',width:80, editor: {
                    type: 'combobox',
                    options: {
                        url: '<?= base_url('master/uom/reads') ?>',
                        editable:false,
                        valueField: 'name',
                        textField: 'name',
                        prompt: 'Choose Uom'
                    }}">UoM</th>
                    <th data-options="field:'currency',width:80, editor: {
                    type: 'combobox',
                    options: {
                        url: '<?= base_url('master/currencies/reads') ?>',
                        editable:false,
                        valueField: 'number',
                        textField: 'number',
                        prompt: 'Choose Currencies'
                    }}">Currency</th>
                    <th data-options="field:'qty',width:80, formatter:numberformat,editor: {
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
                    <th data-options="field:'price',width:80, halign:'center',align:'right', formatter:priceformat,editor: {type: 'numberbox', 
                        options: {
                            required: true,
                            precision: 4,
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
                                    field: 'qty'
                                });

                                var qty = $(ed2.target).numberbox('getValue');
                                $(ed.target).textbox('setValue', (parseFloat(value) * parseFloat(qty)));
                            }
                        }}">Price</th>
                    <th data-options="field:'total',width:120, formatter:priceformat,halign:'center',align:'right',editor: {type: 'numberbox', options: {required: true, readonly: true, precision: 4}}">Amount</th>
                    <th data-options="field:'account_number',width:100, halign:'center', editor: {
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
                    <th data-options="field:'account_name',width:150, editor: {type: 'textbox', options: {readonly: true}}">Account Name</th>
                    <th data-options="field:'account_type',width:100, halign:'center', editor: {
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
            </thead>
        </table>

        <div style="width: 50%; float: left; margin-top:20px;">
            <a style="width: 100%;" class="easyui-linkbutton c2" onclick="addJournal()">Add to Journal</a>
            <br><br>
            <table id="dg3" class="easyui-datagrid" title="Journal Lists" style="width: 100%;" data-options="singleSelect: true" toolbar="#toolbar3"></table>

            <div class="fitem">
                <b style="width:45%; display:inline-block; padding-left: 50px;">BALANCE TOTAL</b>
                <input style="width:18%;" id="balance_debit" name="balance_debit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
                <input style="width:18%;" id="balance_credit" name="balance_credit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
            </div>
        </div>

        <div style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: right; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <div style="width: 100%; float: left;">
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">SUB TOTAL</b>
                        <input style="width:60%;" id="total_sub" name="total_sub" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">VAT</b>
                        <input style="width:60%;" id="total_vat" name="total_vat" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">PPH</b>
                        <input style="width:30%;" id="total_pph" name="total_pph" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                        <select style="width:30%;" id="pph" name="pph" class="easyui-combobox" data-options="prompt: 'PPH'" panelHeight="auto">
                            <option value="0">NON PPH</option>
                            <option value="5">PPH 21</option>
                            <option value="2">PPH 23</option>
                            <option value="10">PPH 4(2)</option>
                            <option value="10.0">Other Income</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">GRAND TOTAL</b>
                        <input style="width:60%;" id="total_grand" name="total_grand" readonly required class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">CONVERT IDR</b>
                        <input style="width:60%;" id="total_local" name="total_local" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
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

        $('#dg2').datagrid({
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

        $("#trans_date").datebox('enable');
        $("#customer_id").combobox('enable');
        $("#dn_number").combobox('enable');
        $("#preview").linkbutton('enable');

        $("#account_sales_name").textbox('setValue', "SALES");
        $("#account_pay_name").textbox('setValue', "PAYABLE");
        $("#account_bal_name").textbox('setValue', "BALANCE");

        // $("#trans_date").datebox({
        //     onChange: function(val) {
        //         number(val);
        //     }
        // });

        $('#customer_id').combogrid({
            url: '<?= base_url('master/customers/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Customer",
            columns: [
                [{
                    field: 'number',
                    title: 'Customer No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Customer Name',
                    width: 250
                }, ]
            ],
            onSelect: function(index, row) {
                var trans_date = $("#trans_date").datebox('getValue');
                number(trans_date, row.nickname);

                $("#payment_term").numberbox("setValue", row.payment_term);

                if (row.vat_status != "VAT") {
                    $("#taxes").numberbox('setValue', 0);
                } else {
                    $("#taxes").numberbox('setValue', row.vat);
                }

                $.ajax({
                    type: "post",
                    url: "<?= base_url('finance/sales_invoices/readDueDate/') ?>" + window.btoa(trans_date) + "/" + row.payment_term,
                    dataType: "text",
                    success: function(due_date) {
                        $("#due_date").datebox('setValue', due_date);
                    }
                });

                $("#dn_number").combobox({
                    url: '<?= base_url('finance/sales_invoices/readDelivery?customer_id=') ?>' + row.id,
                    valueField: 'number',
                    textField: 'number',
                    prompt: "Choose Delivery Note"
                });
            }
        });
    }

    function addJournal() {
        var customer_id = $("#customer_id").combogrid('getValue');

        var rows = $('#dg2').datagrid('getRows');
        var taxes = $("#taxes").numberbox('getValue');
        var pphname = $("#pph").combobox('getValue');
        var totalrows = rows.length;

        var rows2 = $('#dg3').datagrid('getRows');
        var totalrows2 = rows2.length;
        endEditing2();

        if(pphname != ""){
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
                        total_sub -= Math.abs(parseFloat(rows[i].total));
                    }else{
                        total_sub += Math.abs(parseFloat(rows[i].total));
                    }
                    
                    data_array.push(data);
                }

                $("#total_sub").numberbox('setValue', Math.abs(total_sub));
                var disc_tax = parseFloat(Math.abs(total_sub) * (taxes / 100));
                $("#total_vat").numberbox('setValue', disc_tax);
                var total_pph = $("#total_pph").numberbox('getValue');
                var total_grand = (parseFloat(Math.abs(total_sub)) + parseFloat(disc_tax) - parseFloat(total_pph));
                $("#total_grand").numberbox('setValue', (total_grand));

                $.ajax({
                    type: "post",
                    url: "<?= base_url('finance/sales_invoices/readExchangeRates?customer_id=') ?>" + customer_id,
                    dataType: "json",
                    success: function(exchange) {
                        if (exchange.length > 0) {
                            $("#total_local").numberbox('setValue', (parseInt(total_grand) * parseInt(exchange[0].selling)));
                        } else {
                            $("#total_local").numberbox('setValue', (parseInt(total_grand) * 1));
                        }
                    }
                });

                var pph_val = 0;
                var vat_val = 0;
                var arr_pph = ["1154101", "1154103", "1154106"];
                var arr_ar = ["1121101", "1121102", "1121103"];

                for (let z = 0; z < totalrows2; z++) {
                    if (rows2[z].account_number == "1154105" || rows2[z].account_number == "2031108") {
                        var debit = 0;
                        var credit = disc_tax;
                        vat_val = 1;
                    } else {
                        var debit = rows2[z].debit;
                        var credit = rows2[z].credit;
                    }

                    //Other income
                    if (jQuery.inArray(rows2[z].account_number, arr_pph) >= 0) {
                        var debit = 0;
                        var credit = total_pph;
                        pph_val = 1;
                    //Other Income
                    }else if(rows2[z].account_number == "5311006" && rows2[z].flag == "2"){
                        var debit = total_pph;
                        var credit = 0;
                    }

                    if (jQuery.inArray(rows2[z].account_number, arr_ar) >= 0) {
                        var debit = total_grand;
                        var credit = 0;
                    //Other Income
                    }else if(rows2[z].account_number == "5311006" && rows2[z].flag == "4"){
                        var debit = 0;
                        var credit = total_sub;
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
                        account_number: "2031108",
                        account_name: "TAX",
                        debit: 0,
                        credit: disc_tax,
                        flag: "3",
                    }

                    data_array2.push(data2);
                }

                if (total_pph > 0 && pph_val == 0 && pphname == "5") {
                    var data2 = {
                        account_number: "1154101",
                        account_name: "INCOME TAX ART 21",
                        debit: 0,
                        credit: total_pph,
                        flag: "4",
                    }

                    data_array2.push(data2);
                }

                if (total_pph > 0 && pph_val == 0 && pphname == "2") {
                    var data2 = {
                        account_number: "1154103",
                        account_name: "INCOME TAX ART 23",
                        debit: 0,
                        credit: total_pph,
                        flag: "4",
                    }

                    data_array2.push(data2);
                }

                if (total_pph > 0 && pph_val == 0 && pphname == "10") {
                    var data2 = {
                        account_number: "1154106",
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
                    url: "<?= base_url('finance/sales_invoices/createJson') ?>",
                    data: {
                        jsonData: jsonData,
                        jsonData2: jsonData2,
                    },
                    success: function(response) {
                        addTable2('<?= base_url('finance/sales_invoices/calculateJournal') ?>');

                        setTimeout(function() {
                            balance_journal();
                        }, 2000);
                    },
                });
            } else {
                toastr.warning("please selections your data in table first");
            }
        }else{
            toastr.warning("please select PPH");
        }
    }

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
                    width: 110,
                    halign: 'center',
                    title: "Debit",
                    formatter: numberformat,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 4
                        }
                    }
                }, {
                    field: 'credit',
                    width: 110,
                    halign: 'center',
                    title: "Credit",
                    formatter: numberformat,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 4
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
                    url: '<?= base_url('finance/sales_invoices/deleteSingle') ?>',
                    data: {
                        id: row.id
                    },
                    success: function(result) {
                        var result = eval('(' + result + ')');
                        toastr.success(result.message);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        //toastr.error(jqXHR.statusText);
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
            if (row.status == 0) {
                if(row.gl_no == null){
                    $('#dlg_insert').dialog('open');
                    $('#frm_insert').form('load', row);

                    $("#trans_date").datebox('disable');
                    $("#customer_id").combobox('disable');
                    $("#dn_number").combobox('disable');
                    $("#preview").linkbutton('disable');

                    $('#customer_id').combogrid({
                        url: '<?= base_url('master/customers/reads') ?>',
                        panelWidth: 420,
                        idField: 'id',
                        textField: 'name',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: "Choose Customer",
                        columns: [
                            [{
                                field: 'number',
                                title: 'Customer No',
                                width: 120
                            }, {
                                field: 'name',
                                title: 'Customer Name',
                                width: 250
                            }, ]
                        ],
                        onLoadSuccess: function(customer_id) {
                            $("#customer_id").combogrid('setValue', row.customer_id);
                        },
                        onSelect: function(index, customer) {
                            var trans_date = $("#trans_date").datebox('getValue');
                            $("#payment_term").numberbox("setValue", customer.payment_term);

                            $("#dn_number").combobox({
                                url: '<?= base_url('finance/sales_invoices/readDelivery?customer_id=') ?>' + customer.id,
                                valueField: 'number',
                                textField: 'number',
                                prompt: "Choose Delivery Note",
                                onLoadSuccess: function(delivery_no) {
                                    $("#dn_number").combobox('setValue', row.dn_number);
                                },
                            });
                        }
                    });

                    var lastIndex;
                    var dg = $('#dg2').datagrid({
                        url: '<?= base_url('finance/sales_invoices/reads/') ?>' + window.btoa(row.number),
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

                    addTable2('<?= base_url('finance/sales_invoices/readJournals/') ?>' + window.btoa(row.number));

                    setTimeout(function() {
                        balance_journal();
                        $("#number").textbox('setValue', row.number);
                    }, 2000);
                }else{
                    toastr.error("Cannot Update because this Sales Invoice has been created in Posting Journal");
                }
            } else {
                toastr.error("Cannot Update because AR Receipt status is closed");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //NOMOR AUTOMATIC
    function number(trans_date, nickname) {
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/sales_invoices/number/') ?>" + window.btoa(trans_date) + "/" + nickname,
            dataType: "html",
            success: function(result) {
                $("#number").textbox('setValue', result);
            }
        });
    }

    function preview() {
        var customer_id = $("#customer_id").combogrid('getValue');
        var dn_number = $("#dn_number").combobox('getValue');
        var trans_date = $("#trans_date").datebox('getValue');
        var due_date = $("#due_date").datebox('getValue');
        var taxes = $("#taxes").numberbox('getValue');
        var journal_type_id = $("#journal_type").combobox('getValue');

        if (dn_number == "" || trans_date == "" || due_date == "" || taxes == "") {
            toastr.info('Please completed your data');
        } else {
            $("#pph").combobox('setValue', "0");

            var lastIndex;
            var dg = $('#dg2').datagrid({
                url: '<?= base_url('finance/sales_invoices/datatablesTemp') ?>?dn_number=' + window.btoa(dn_number),
                onLoadSuccess: function(row) {
                    $("#total_sub").numberbox('setValue', row.total_sub);
                    var disc_tax = parseFloat(row.total_sub * (taxes / 100));
                    $("#total_vat").numberbox('setValue', disc_tax);
                    $("#total_grand").numberbox('setValue', row.total_sub);

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('finance/sales_invoices/readExchangeRates?customer_id=') ?>" + customer_id,
                        dataType: "json",
                        success: function(exchange) {
                            $("#total_local").numberbox('setValue', (row.total_sub * exchange[0].selling));
                        }
                    });

                    addTable2('<?= base_url('finance/sales_invoices/readJournal/') ?>' + window.btoa(journal_type_id));
                },
                onClickRow: function(rowIndex) {
                    if (lastIndex != rowIndex) {
                        $(this).datagrid('endEdit', lastIndex);
                        $(this).datagrid('beginEdit', rowIndex);
                    }
                    lastIndex = rowIndex;
                },
            });
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

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('closing/locks/checkLock') ?>",
                            data: "period=" + row.trans_date + "&menus_id=<?= $menus_id ?>",
                            dataType: "json",
                            success: function (lock) {
                                if(lock.total > 0){
                                    toastr.error("This period is not active by Accounting");
                                    return false;
                                }

                                if (row.status == 0) {
                                    if(row.gl_no == null){
                                        $.ajax({
                                            method: 'post',
                                            url: '<?= base_url('finance/sales_invoices/delete') ?>',
                                            data: {
                                                number: row.number,
                                                dn_number: row.dn_number,
                                            },
                                            success: function(result) {
                                                var result = eval('(' + result + ')');

                                                if (result.theme == "success") {
                                                    toastr.success(result.message);
                                                } else {
                                                    toastr.error(result.message);
                                                }
                                            },
                                            error: function(jqXHR, textStatus, errorThrown) {
                                                toastr.error(jqXHR.statusText);
                                            },
                                            complete: function(data) {
                                                $('#dg').datagrid('reload');
                                            }
                                        });
                                    }else{
                                        toastr.error("Cannot Delete because this Sales Invoice has been created in Posting Journal");
                                    }
                                } else {
                                    toastr.error("Cannot Update because AR Receipt status is closed");
                                }
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
        var filter_sales_invoice = $("#filter_sales_invoice").combobox('getValue');
        var filter_dn_number = $("#filter_dn_number").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_sales_invoice=" + window.btoa(filter_sales_invoice) +
            "&filter_dn_number=" + window.btoa(filter_dn_number) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_status=" + window.btoa(filter_status);

        $('#dg').datagrid({
            url: '<?= base_url('finance/sales_invoices/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/sales_invoices/print') ?>' + url);
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
        var filter_sales_invoice = $("#filter_sales_invoice").combobox('getValue');
        var filter_dn_number = $("#filter_dn_number").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_sales_invoice=" + window.btoa(filter_sales_invoice) +
            "&filter_dn_number=" + window.btoa(filter_dn_number) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('finance/sales_invoices/print/excel') ?>' + url);
    }

    function excelDetail() {
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_due_date_from = $("#filter_due_date_from").datebox('getValue');
        var filter_due_date_to = $("#filter_due_date_to").datebox('getValue');
        var filter_sales_invoice = $("#filter_sales_invoice").combobox('getValue');
        var filter_dn_number = $("#filter_dn_number").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_sales_invoice=" + window.btoa(filter_sales_invoice) +
            "&filter_dn_number=" + window.btoa(filter_dn_number) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('finance/sales_invoices/printDetail/excel') ?>' + url);
    }

    function excelJournal() {
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_due_date_from = $("#filter_due_date_from").datebox('getValue');
        var filter_due_date_to = $("#filter_due_date_to").datebox('getValue');
        var filter_sales_invoice = $("#filter_sales_invoice").combobox('getValue');
        var filter_dn_number = $("#filter_dn_number").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_sales_invoice=" + window.btoa(filter_sales_invoice) +
            "&filter_dn_number=" + window.btoa(filter_dn_number) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('finance/sales_invoices/printJournal/excel') ?>' + url);
    }

    //PRINT INVOICE
    function print_invoice() {
        var invoice_no = $("#filter_sales_invoice").combobox('getValue');
        if (invoice_no == "") {
            toastr.warning("Please select Sales Order Invoice!", "Information");
        } else {
            window.open("<?= base_url('finance/sales_invoices/print_dn/') ?>" + window.btoa(invoice_no), '_blank', 'location=yes,height=570,width=1000,scrollbars=yes,status=yes');
        }
    }

    //PRINT COMMERCIAL INVOICE
    function print_commercial() {
        var invoice_no = $("#filter_sales_invoice").combobox('getValue');
        if (invoice_no == "") {
            toastr.warning("Please select Sales Order Invoice!", "Information");
        } else {
            window.open("<?= base_url('finance/sales_invoices/print_commercial/') ?>" + window.btoa(invoice_no), '_blank', 'location=yes,height=570,width=1000,scrollbars=yes,status=yes');
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

    function removebtn(value, row, index) {
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
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('finance/sales_invoices/datatables') ?>',
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
                    url: '<?= base_url('finance/sales_invoices/datatables/details?number=') ?>' + window.btoa(row.number),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'dn_number',
                            title: 'Delivery Note',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'so_number',
                            title: 'Sales Order',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'customer_po',
                            title: 'Customer PO',
                            halign: 'center',
                            width: 120
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
                    var trans_date = $("#trans_date").datebox('getValue');
                    var number = $("#number").textbox('getValue');
                    var customer_id = $("#customer_id").combogrid('getValue');
                    var taxes = $("#taxes").numberbox('getValue');
                    var payment_term = $("#payment_term").numberbox('getValue');
                    var due_date = $("#due_date").datebox('getValue');
                    var remarks = $("#remarks").textbox('getValue');
                    var journal_type_id = $("#journal_type").combobox('getValue');

                    var balance_debit = $("#balance_debit").numberbox('getValue');
                    var balance_credit = $("#balance_credit").numberbox('getValue');

                    var total_sub = $("#total_sub").numberbox('getValue');
                    var total_vat = $("#total_vat").numberbox('getValue');
                    var total_pph = $("#total_pph").numberbox('getValue');
                    var total_grand = $("#total_grand").numberbox('getValue');
                    var total_local = $("#total_local").numberbox('getValue');

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('closing/locks/checkLock') ?>",
                        data: "period=" + trans_date + "&menus_id=<?= $menus_id ?>",
                        dataType: "json",
                        success: function (lock) {
                            if(lock.total > 0){
                                toastr.error("This period is not active by Accounting");
                                return false;
                            }

                            if (parseFloat(balance_debit) == parseFloat(balance_credit)) {
                                if (due_date == "" || trans_date == "" || customer_id == "" || journal_type_id == "") {
                                    toastr.error("please complete your input data");
                                } else {
                                    $('#dg2').datagrid('acceptChanges');
                                    var rows = $('#dg2').datagrid('getRows');
                                    var totalrows = rows.length;

                                    var rows2 = $('#dg3').datagrid('getRows');
                                    var totalrows2 = rows2.length;
                                    endEditing2();

                                    $.ajax({
                                        type: "post",
                                        url: "<?= base_url('finance/sales_invoices/deleteJournal') ?>",
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
                                                            url: '<?= base_url('finance/sales_invoices/create') ?>',
                                                            data: {
                                                                trans_date: trans_date,
                                                                number: number,
                                                                customer_id: customer_id,
                                                                journal_type_id: journal_type_id,
                                                                taxes: taxes,
                                                                payment_term: payment_term,
                                                                due_date: due_date,
                                                                remarks: remarks,
                                                                total_sub: total_sub,
                                                                total_vat: total_vat,
                                                                total_pph: total_pph,
                                                                total_grand: total_grand,
                                                                total_local: total_local,
                                                                id: json[i].id,
                                                                dn_number: json[i].dn_number,
                                                                so_number: json[i].so_number,
                                                                customer_po: json[i].customer_po,
                                                                item_id: json[i].item_id,
                                                                item_no: json[i].item_no,
                                                                item_name: json[i].item_name,
                                                                uom: json[i].uom,
                                                                currency: json[i].currency,
                                                                qty: json[i].qty,
                                                                price: json[i].price,
                                                                total: json[i].total,
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
                                                            url: '<?= base_url('finance/sales_invoices/createJournals') ?>',
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
                                            } else {
                                                toastr.warning("please selections your data in table first");
                                            }
                                        }
                                    });
                                }
                            } else {
                                toastr.error("Balance Debit Cannot match on Balance Credit");
                            }
                        }
                    });
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

        $("#filter_customer").combobox({
            url: '<?= base_url('master/customers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Customers",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_sales_invoice").combobox({
            url: '<?= base_url('finance/sales_invoices/readSalesInvoices') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Choose Sales Invoice No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_dn_number").combobox({
            url: '<?= base_url('finance/sales_invoices/readDeliveryNote') ?>',
            valueField: 'dn_number',
            textField: 'dn_number',
            prompt: "Choose Delivery Note",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
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
                var customer_id = $("#customer_id").combogrid('getValue');
                var total_sub = $("#total_sub").numberbox('getValue');
                var total_vat = $("#total_vat").numberbox('getValue');
                var pph = $("#pph").combobox('getValue');
                var total_pph = parseFloat(total_sub * parseFloat(parseInt(pph) / 100));
                $("#total_pph").numberbox('setValue', total_pph);

                var grand_total = (parseFloat(total_sub) + parseFloat(total_vat) - parseFloat(total_pph));
                $("#total_grand").numberbox('setValue', grand_total);

                $.ajax({
                    type: "post",
                    url: "<?= base_url('finance/sales_invoices/readExchangeRates?customer_id=') ?>" + customer_id,
                    dataType: "json",
                    success: function(exchange) {
                        if (exchange) {
                            $("#total_local").numberbox('setValue', (grand_total * exchange[0].selling));
                        }
                    }
                });
            }
        })

    });

    function priceformat(value, row) {
        if (row.currency == "USD") {
            var digits = 4;
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
            const formatter = new Intl.NumberFormat('id-ID', {
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
</script>
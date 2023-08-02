<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'payment_type',width:100,halign:'center'">Payment Type</th>
            <th rowspan="2" data-options="field:'payment_no',width:150,align:'center'">Payment No</th>
            <th rowspan="2" data-options="field:'payment_date',width:100,align:'center'">Payment Date</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'bank_account',width:150,halign:'center'">Bank Account</th>
            <th rowspan="2" data-options="field:'payment_by',width:100,align:'center'">Payment By</th>
            <th rowspan="2" data-options="field:'remarks',width:200,halign:'center'">Note</th>
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
<div id="toolbar" style="height: 220px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->

    <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment Date</span>
                <input style="width:30%;" id="filter_payment_date_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_payment_date_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment Type</span>
                <select style="width:60%;" id="filter_payment_type" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="PURCHASE">PURCHASE</option>
                    <option value="OTHERS">OTHERS</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier Name</span>
                <input style="width:60%;" id="filter_supplier" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_voucher()"><i class="fa fa-print"></i> Print Voucher</a>
            </div>
        </div>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment No</span>
                <input style="width:60%;" id="filter_payment_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Invoice No</span>
                <input style="width:60%;" id="filter_invoice_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Bank Account</span>
                <input style="width:60%;" id="filter_bank_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment By</span>
                <select style="width:60%;" id="filter_payment_by" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="TRANSFER">TRANSFER</option>
                    <option value="CASH">CASH</option>
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
                        <span style="width:35%; display:inline-block;">Payment Type</span>
                        <select style="width:60%;" id="payment_type" name="payment_type" required class="easyui-combobox" panelHeight="auto">
                            <option value="" selected disabled>Select Payment Type</option>
                            <option value="PURCHASE">PURCHASE</option>
                            <option value="OTHERS">OTHERS</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment Date</span>
                        <input style="width:60%;" id="payment_date" name="payment_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment No</span>
                        <input style="width:60%;" readonly id="payment_no" name="payment_no" class="easyui-textbox" data-options="prompt:'Automatic From Payment Date'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Supplier Name</span>
                        <input style="width:60%;" required="" id="supplier_id" name="supplier_id" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem" id="type_selection_purchase">
                        <span style="width:35%; display:inline-block;">Purchase Invoice No</span>
                        <input style="width:60%;" required="" id="purchase_invoice" name="purchase_invoice" class="easyui-combobox">
                    </div>
                    <div class="fitem" id="type_selection_purchase">
                        <span style="width:35%; display:inline-block;">Bank Account</span>
                        <input style="width:60%;" required="" id="bank_account" name="bank_account" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Payment By</span>
                        <select style="width:60%;" id="payment_by" name="payment_by" class="easyui-combobox" panelHeight="auto">
                            <option value="TRANSFER">TRANSFER</option>
                            <option value="CASH">CASH</option>
                            <option value="CHEQUE">CHEQUE</option>
                        </select>
                    </div>
                    <div class="fitem" id="f_cheque_no">
                        <span style="width:35%; display:inline-block;">Cheque No</span>
                        <input style="width:60%;" id="cheque_no" name="cheque_no" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Note</span>
                        <input style="width:60%;" id="note" name="note" class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
        </div>
        <div id="toolbar2">
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
        </div>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="list AP Payment" toolbar="#toolbar2" data-options="singleSelect: false" idField="purchase_invoice">
            <thead>
                <tr>
                    <th field="ck" checkbox="true"></th>
                    <th data-options="field:'delete',width:80, formatter:removebtn">#</th>
                    <th data-options="field:'purchase_invoice',width:150, editor: {type: 'textbox'}">Purchase Invoice</th>
                    <th data-options="field:'supplier_invoice',width:150, editor: {type: 'textbox'}">Supplier Invoice</th>
                    <th data-options="field:'currency',align:'center',width:80, editor: {type: 'textbox'}">Currency</th>
                    <th data-options="field:'amount',width:100, formatter:numberformat, align:'right', editor: {type: 'numberbox',options: {precision:2, readonly:true}}">Amount</th>
                    <th data-options="field:'balance',width:100, formatter:numberformat, align:'right', editor: {type: 'numberbox',options: {precision:2, readonly:true}}">Balance</th>
                    <th data-options="field:'payment',width:100, formatter:numberformat, align:'right', editor: {type: 'numberbox',options: {precision:2}}">Payment</th>
                    <th data-options="field:'remarks',width:100, editor: {type: 'textbox'}">Remarks</th>
                    <th data-options="field:'account_number',width:140, halign:'center', editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('finance/account_coa/reads') ?>',
                            valueField: 'account_number',
                            textField: 'account_name',
                            prompt: 'Choose Account No',
                        }}">Account No</th>
                    <th data-options="field:'account_type',width:120, halign:'center', editor: {
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
                        <b style="width:35%; display:inline-block;">Total Payment</b>
                        <input style="width:60%;" id="total_payment" name="total_payment" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
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
        
        $("#payment_date").datebox('enable');
        $("#payment_type").combobox('enable');
        $("#supplier_id").combogrid('enable');
        $("#purchase_invoice").combobox('enable');
        $("#f_cheque_no").hide();

        $("#payment_date").datebox({
            onChange: function(val) {
                number(val);
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

                $("#payment_date").datebox('disable');
                $("#payment_type").combobox('disable');
                $("#supplier_id").combogrid('disable');
                $("#purchase_invoice").combobox('disable');
                var payment_by = $("#payment_by").combobox('getValue');

                if(payment_by == "CHEQUE"){
                    $("#f_cheque_no").show();
                }else{
                    $("#f_cheque_no").hide();
                }

                $("#supplier_id").combobox({
                    url: '<?= base_url('master/suppliers/reads') ?>',
                    valueField: 'id',
                    textField: 'name',
                    prompt: "Choose Supplier",
                    onLoadSuccess: function(load_supplier) {
                        $("#supplier_id").combobox('setValue', row.supplier_id);
                    },
                    onSelect: function(supplier) {
                        $("#purchase_invoice").combobox({
                            url: '<?= base_url('finance/ap_payments/readInvoices/') ?>' + supplier.id,
                            valueField: 'purchase_invoice',
                            textField: 'purchase_invoice',
                            multiple: true,
                            prompt: "Choose Purchase Invoice No",
                            onLoadSuccess: function(load_invoice) {
                                $("#purchase_invoice").combobox('setValue', row.purchase_invoice);
                            },
                        });
                    }
                });
                
                preview('<?= base_url('finance/ap_payments/reads/') ?>' + window.btoa(row.payment_no));
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
            url: "<?= base_url('finance/ap_payments/number/') ?>" + window.btoa(trans_date),
            dataType: "html",
            success: function(result) {
                $("#payment_no").textbox('setValue', result);
            }
        });
    }

    var editIndex = undefined;
    function preview(link = "") {
        var purchase_invoice = $("#purchase_invoice").combobox('getText');

        if(link == ""){
            var linked = '<?= base_url('finance/ap_payments/datatablesTemp') ?>?purchase_invoice=' + window.btoa(purchase_invoice);
        }else{
            var linked = link;
        }

        if (purchase_invoice == "") {
            toastr.info('Please select purchase invoice');
        } else {
            var dg = $('#dg2').datagrid({
                url: linked,
                onLoadSuccess: function(row) {
                    if(row.total_payment){
                        $("#total_payment").numberbox('setValue', row.total_payment);
                    }else{
                        $("#total_payment").numberbox('setValue', row[0].total_payment);
                    }
                },
                onClickRow: function(rowIndex) {
                    if (editIndex != rowIndex) {
                        $(this).datagrid('endEdit', editIndex);
                        $(this).datagrid('beginEdit', rowIndex);
                    }
                    editIndex = rowIndex;
                },
            });
        }
    }

    function removebtn(value, row, index) {
        return  "<a href='#' onclick='saveit(" + index + ")' style='pointer-events:auto !important; opacity:1;' class='btn btn-sm btn-success'><i class='fa fa-check'></i></a> "+
                "<a href='#' onclick='removeit(" + index + ")' style='pointer-events:auto !important; opacity:1;' class='btn btn-sm btn-danger'><i class='fa fa-times'></i></a>";
    }

    function removeit(indexs) {
        toastr.success('Deleted Success');
        var total_payment = $("#total_payment").numberbox('getValue');
        $("#total_payment").numberbox('setValue', parseFloat(total_payment));
        $("#dg2").datagrid("deleteRow", indexs);
    }

    function saveit(indexs) {
        var editors = $('#dg2').datagrid('getEditors', indexs);
        var total_payment = $("#total_payment").numberbox('getValue');
        
        var amount = $(editors[3].target).numberbox('getValue');
        var payment = $(editors[5].target).numberbox('getValue');
        $("#total_payment").numberbox('setValue', parseFloat(total_payment) - parseFloat(amount) + parseFloat(payment));
        endEditing();
    }

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
        $('#dg2').datagrid('appendRow', {
            amount: '0',
            balance: '0',
            payment: '0',
        });

        editIndex = $('#dg2').datagrid('getRows').length - 1;
        $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
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
                            url: '<?= base_url('finance/ap_payments/delete') ?>',
                            data: {
                                payment_no: row.payment_no
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
        var filter_payment_type = $("#filter_payment_type").combobox('getValue');
        var filter_payment_date_from = $("#filter_payment_date_from").datebox('getValue');
        var filter_payment_date_to = $("#filter_payment_date_to").datebox('getValue');
        var filter_payment_no = $("#filter_payment_no").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_bank_no = $("#filter_bank_no").combobox('getValue');
        var filter_payment_by = $("#filter_payment_by").combobox('getValue');

        var url = "?filter_payment_type=" + window.btoa(filter_payment_type) +
            "&filter_payment_date_from=" + window.btoa(filter_payment_date_from) +
            "&filter_payment_date_to=" + window.btoa(filter_payment_date_to) +
            "&filter_payment_no=" + window.btoa(filter_payment_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_bank_no=" + window.btoa(filter_bank_no) +
            "&filter_payment_by=" + window.btoa(filter_payment_by);

        $('#dg').datagrid({
            url: '<?= base_url('finance/ap_payments/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/ap_payments/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //EXPORT TO EXCEL
    function excel() {
        var filter_payment_type = $("#filter_payment_type").combobox('getValue');
        var filter_payment_date_from = $("#filter_payment_date_from").datebox('getValue');
        var filter_payment_date_to = $("#filter_payment_date_to").datebox('getValue');
        var filter_payment_no = $("#filter_payment_no").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_bank_no = $("#filter_bank_no").combobox('getValue');
        var filter_payment_by = $("#filter_payment_by").combobox('getValue');

        var url = "?filter_payment_type=" + window.btoa(filter_payment_type) +
            "&filter_payment_date_from=" + window.btoa(filter_payment_date_from) +
            "&filter_payment_date_to=" + window.btoa(filter_payment_date_to) +
            "&filter_payment_no=" + window.btoa(filter_payment_no) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_bank_no=" + window.btoa(filter_bank_no) +
            "&filter_payment_by=" + window.btoa(filter_payment_by);

        window.location.assign('<?= base_url('finance/ap_payments/print/excel') ?>' + url);
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
            url: '<?= base_url('finance/ap_payments/datatables') ?>',
            pagination: true,
            rownumbers: true,
            height: '650px',
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.payment_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                ddv.datagrid({
                    url: '<?= base_url('finance/ap_payments/datatables/details?payment_no=') ?>' + window.btoa(row.payment_no),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'purchase_invoice',
                            title: 'Purchase Invoice',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'supplier_invoice',
                            title: 'Supplier Invoice',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'bank_account',
                            title: 'Bank Account',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'amount',
                            title: 'Amount',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
                        }, {
                            field: 'balance',
                            title: 'Balance',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
                        }, {
                            field: 'payment',
                            title: 'Payment',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
                        }, {
                            field: 'remarks',
                            title: 'Remarks',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'account_number',
                            title: 'Account No',
                            halign: 'center',
                            width: 150
                        },{
                            field: 'account_type',
                            title: 'Debt/Credit',
                            align: 'center',
                            width: 80
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
                    var payment_type = $("#payment_type").combobox('getValue');
                    var payment_date = $("#payment_date").datebox('getValue');
                    var payment_no = $("#payment_no").textbox('getValue');
                    var supplier_id = $("#supplier_id").combobox('getValue');
                    var bank_account = $("#bank_account").combogrid('getValue');
                    var payment_by = $("#payment_by").combobox('getValue');
                    var cheque_no = $("#cheque_no").textbox('getValue');
                    var note = $("#note").textbox('getValue');
                    var total_payment = $("#total_payment").numberbox('getValue');

                    if (purchase_invoice == "" || bank_account == "" || payment_date == "" || payment_by == "") {
                        toastr.error("please complete your input data");
                    } else {
                        $('#dg2').datagrid('acceptChanges');
                        var rows = $('#dg2').datagrid('getSelections');
                        var totalrows = rows.length;

                        if (totalrows > 0) {
                            for (let i = 0; i < totalrows; i++) {
                                if (rows[i].purchase_invoice) {
                                    $.ajax({
                                        type: "post",
                                        url: '<?= base_url('finance/ap_payments/create') ?>',
                                        data: {
                                            payment_type: payment_type,
                                            payment_date: payment_date,
                                            payment_no: payment_no,
                                            supplier_id: supplier_id,
                                            bank_account: bank_account,
                                            payment_by: payment_by,
                                            cheque_no: cheque_no,
                                            note: note,
                                            total_payment: total_payment,
                                            purchase_invoice: rows[i].purchase_invoice,
                                            supplier_invoice: rows[i].supplier_invoice,
                                            currency: rows[i].currency,
                                            amount: rows[i].amount,
                                            balance: rows[i].balance,
                                            payment: rows[i].payment,
                                            remarks: rows[i].remarks,
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
            onSelect: function(supplier) {
                $("#filter_payment_no").combobox({
                    url: '<?= base_url('finance/ap_payments/readPayments/') ?>' + supplier.id,
                    valueField: 'payment_no',
                    textField: 'payment_no',
                    prompt: "Choose Payment No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $("#filter_invoice_no").combobox({
                    url: '<?= base_url('finance/ap_payments/readInvoices/') ?>' + supplier.id,
                    valueField: 'purchase_invoice',
                    textField: 'purchase_invoice',
                    prompt: "Choose Purchase Invoice",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $("#filter_bank_no").combogrid({
            url: '<?= base_url('finance/account_banks/reads') ?>',
            panelWidth: 420,
            idField: 'bank_account',
            textField: 'bank_account',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Bank Account",
            columns: [
                [{
                    field: 'bank_account',
                    title: 'Bank Account',
                    width: 120
                }, {
                    field: 'bank_name',
                    title: 'Bank Name',
                    width: 250
                }, ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });
        
        $("#supplier_id").combobox({
            url: '<?= base_url('master/suppliers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Supplier",
            onSelect: function(supplier) {
                var payment_type = $("#payment_type").combobox('getValue');

                $("#purchase_invoice").combobox({
                    url: '<?= base_url('finance/ap_payments/readInvoiceType?supplier_id=') ?>' + supplier.id + "&payment_type=" + payment_type,
                    valueField: 'number',
                    textField: 'number',
                    multiple: true,
                    prompt: "Choose Purchase Invoice No",
                });
            }
        });

        $("#bank_account").combogrid({
            url: '<?= base_url('finance/account_banks/reads') ?>',
            panelWidth: 420,
            idField: 'bank_account',
            textField: 'bank_account',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Bank Account",
            columns: [
                [{
                    field: 'bank_account',
                    title: 'Bank Account',
                    width: 120
                }, {
                    field: 'bank_name',
                    title: 'Bank Name',
                    width: 250
                }, ]
            ],
        });

        $("#payment_by").combobox({
            onChange: function(val){
                if(val == "CHEQUE"){
                    $("#f_cheque_no").show();
                }else{
                    $("#f_cheque_no").hide();
                    $("#cheque_no").textbox('clear');
                }
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
</script>
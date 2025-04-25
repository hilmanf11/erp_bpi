<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'receipt_type',width:100,halign:'center'">Receipt Type</th>
            <th rowspan="2" data-options="field:'receipt_no',width:150,align:'center'">Receipt No</th>
            <th rowspan="2" data-options="field:'receipt_date',width:100,align:'center'">Receipt Date</th>
            <th rowspan="2" data-options="field:'customer_name',width:250,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'bank_account',width:150,halign:'center'">Bank Account</th>
            <th rowspan="2" data-options="field:'receipt_by',width:100,align:'center'">Receipt By</th>
            <th rowspan="2" data-options="field:'gl_no',width:100,align:'center'">GL No</th>
            <th rowspan="2" data-options="field:'note',width:200,halign:'center'">Note</th>
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
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->

    <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt Date</span>
                <input style="width:30%;" id="filter_receipt_date_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_receipt_date_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt Type</span>
                <select style="width:60%;" id="filter_receipt_type" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="SALES">SALES</option>
                    <option value="OTHERS">OTHERS</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer Name</span>
                <input style="width:60%;" id="filter_customer" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_voucher()"><i class="fa fa-print"></i> Print Voucher</a>
            </div>
        </div>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt No</span>
                <input style="width:60%;" id="filter_receipt_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Invoice No</span>
                <input style="width:60%;" id="filter_invoice_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Bank Account</span>
                <input style="width:60%;" id="filter_bank_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt By</span>
                <select style="width:60%;" id="filter_receipt_by" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="TRANSFER">TRANSFER</option>
                    <option value="CASH">CASH</option>
                </select>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="excelDetail()"><i class="fa fa-file"></i> Export Excel Detail</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="excelJournal()"><i class="fa fa-file"></i> Export Excel Journal</a>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 99%; height: 600px; padding:10px; left: 5px; top: 5px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Receipt Type</span>
                        <select style="width:60%;" id="receipt_type" name="receipt_type" required class="easyui-combobox" panelHeight="auto">
                            <option value="" selected disabled>Select Receipt Type</option>
                            <option value="SALES">SALES</option>
                            <option value="OTHERS">OTHERS</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Receipt Date</span>
                        <input style="width:60%;" id="receipt_date" name="receipt_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Receipt No</span>
                        <input style="width:60%;" readonly id="receipt_no" name="receipt_no" class="easyui-textbox" data-options="prompt:'Automatic From Receipt Date'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Customer Name</span>
                        <input style="width:60%;" required="" id="customer_id" name="customer_id" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem" id="type_selection_purchase">
                        <span style="width:35%; display:inline-block;">Sales Invoice No</span>
                        <input style="width:60%;" required="" id="sales_invoice" name="sales_invoice" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Journal Type</span>
                        <input style="width:60%;" required="" name="journal_type_id" id="journal_type" class="easyui-combobox">
                    </div>
                    <div class="fitem" id="type_selection_purchase">
                        <span style="width:35%; display:inline-block;">Bank Account</span>
                        <input style="width:60%;" required="" id="bank_account" name="bank_account" class="easyui-combogrid">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Bank code</span>
                        <input style="width:60%;" id="bank_code" name="bank_code" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Receipt By</span>
                        <select style="width:60%;" id="receipt_by" name="receipt_by" class="easyui-combobox" panelHeight="auto">
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
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append_dp()"><i class="fa fa-eye"></i> Find Down Payment</a>
        </div>

        <div id="toolbar3">
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append2()"><i class="fa fa-plus"></i> Add</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit3()"><i class="fa fa-times"></i> Remove</a>
        </div>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="list AR Receipt" toolbar="#toolbar2" data-options="singleSelect: true" idField="sales_invoice">
            <thead>
                <tr>
                    <th data-options="field:'delete',width:120, formatter:removebtn">Action</th>
                    <th hidden data-options="field:'id',width:150, editor: {type: 'textbox'}">ID</th>
                    <th data-options="field:'sales_invoice',width:150, editor: {type: 'textbox'}">Sales Invoice</th>
                    <th data-options="field:'description',width:150, editor: {type: 'textbox'}">Description</th>
                    <th data-options="field:'currency',align:'center',width:80, editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('master/currencies/reads') ?>',
                            valueField: 'name',
                            textField: 'name',
                            editable:false,
                            prompt: 'Choose Currency',
                            panelHeight: 'auto',
                            required: true,
                        }}">Currency</th>
                    <th data-options="field:'amount',width:100, formatter:numberformat, align:'right', editor: {type: 'numberbox',options: {precision:2, readonly:true}}">Amount</th>
                    <th data-options="field:'balance',width:100, formatter:numberformat, align:'right', editor: {type: 'numberbox',options: {precision:2, readonly:true}}">Balance</th>
                    <th data-options="field:'receipt',width:100, formatter:numberformat, align:'right', editor: {type: 'numberbox',options: {precision:2}}">Receipt</th>
                    <th data-options="field:'remarks',width:100, editor: {type: 'textbox'}">Remarks</th>
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

        <div style="width: 68%; float: left; margin-top:20px;">
            <div style="float: left; width: 30%; ">
                <a style="width: 90%; height: 50px; padding:10px;" class="easyui-linkbutton c2" onclick="addJournal()">Add to Journal</a>
            </div>
            <div style="float: left; width: 30%; border:4px solid green; padding:10px;" id="showExchange">
                <p style="font-size: 16px !important; margin:0;">Rate USD to IDR : <b style="font-size: 16px !important;" id="exchange"></b></p>
            </div>
        </div>

        <div style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: right; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <div style="width: 100%; float: left;">
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">Total Receipt</b>
                        <input style="width:60%;" id="total_receipt" name="total_receipt" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                </div>
            </fieldset>
        </div>

        <div style="width: 80%; float: left; margin-top:10px;">
            <table id="dg3" class="easyui-datagrid" title="Journal Lists" style="width: 100%;" data-options="singleSelect: true" toolbar="#toolbar3">
                <thead>
                    <tr>
                        <th rowspan="2" data-options="field:'account_number',halign:'center',width:100, editor: {
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
                        }">Account No</th>
                        <th rowspan="2" data-options="field:'account_name',halign:'center',width:200, editor: {type: 'textbox', options: {readonly: true}}">Account Name</th>
                        <th rowspan="2" data-options="field:'description',halign:'center',width:200, editor: {type: 'textbox', options: {required: true}}">Description</th>
                        <th colspan="2" data-options="field:'',width:150">Original Currency</th>
                        <th colspan="2" data-options="field:'',width:150">Local Currency</th>
                        <th rowspan="2" data-options="field:'flag',width:50,halign:'center',editor: {type: 'numberbox', options: {required: true}}">Index</th>
                    </tr>
                    <tr>
                        <th data-options="field:'debit',width:120,halign:'center',align:'right',formatter:numberformat,editor: {type: 'numberbox', options: {required: true, precision:2}}">Debit</th>
                        <th data-options="field:'credit',width:120,halign:'center',align:'right',formatter:numberformat,editor: {type: 'numberbox', options: {required: true, precision:2}}">Credit</th>
                        <th data-options="field:'local_debit',width:120,halign:'center',align:'right',formatter:numberformat,editor: {type: 'numberbox', options: {required: true, precision:2}}">Debit</th>
                        <th data-options="field:'local_credit',width:120,halign:'center',align:'right',formatter:numberformat,editor: {type: 'numberbox', options: {required: true, precision:2}}">Credit</th>
                    </tr>
                </thead>
            </table>

            <div class="fitem">
                <b style="width:48%; display:inline-block; padding-left: 50px;">BALANCE TOTAL</b>
                <input style="width:11%;" id="balance_debit" name="balance_debit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
                <input style="width:11%;" id="balance_credit" name="balance_credit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
                <input style="width:11%;" id="local_balance_debit" name="local_balance_debit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
                <input style="width:11%;" id="local_balance_credit" name="local_balance_credit" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:'.', decimalSeparator:','">
            </div>
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
        $("#showExchange").hide();

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

        $("#receipt_date").datebox('enable');
        $("#receipt_type").combobox('enable');
        $("#customer_id").combogrid('enable');
        $("#sales_invoice").combobox('enable');
        $("#receipt_by").combobox('setValue', "TRANSFER");
        $("#f_cheque_no").hide();

        $("#receipt_date").datebox({
            onChange: function(val) {
                var bank_code = $("#bank_code").textbox('getValue');
                number(val, bank_code);
            }
        });
    }

    function addJournal() {
        var rows = $('#dg2').datagrid('getRows');
        var totalrows = rows.length;

        var rows2 = $('#dg3').datagrid('getRows');
        var totalrows2 = rows2.length;
        endEditing2();

        var receipt_no = $("#receipt_no").textbox('getValue');
        var journal_type = $("#journal_type").combobox('getValue');
        var bank_account = $("#bank_account").combogrid('getValue');
        var receipt_date = $("#receipt_date").datebox('getValue');

        if (journal_type != "" && bank_account != "") {
            if (totalrows > 0) {
                var data_array = [];
                var data_array2 = [];
                var total_receipt = 0;
                var total_currency = 1;
                var currency = "";

                for (let i = 0; i < totalrows; i++) {
                    var data = {
                        account_number: rows[i].account_number,
                        account_name: rows[i].account_name,
                        account_type: rows[i].account_type,
                        description: rows[i].description,
                        currency: rows[i].currency,
                        receipt_date: receipt_date,
                        receipt: rows[i].receipt
                    }

                    if (currency == rows[i].currency) {
                        total_currency += 1;
                        currency = rows[i].currency;
                    } else {
                        total_currency += 0;
                        currency = rows[i].currency;
                    }

                    if (rows[i].account_type == "DEBIT") {
                        total_receipt -= parseFloat(rows[i].receipt);
                    } else if (rows[i].account_type == "CREDIT") {
                        total_receipt += parseFloat(rows[i].receipt);
                    }

                    data_array.push(data);
                }

                for (let z = 0; z < totalrows2; z++) {
                    var data2 = {
                        account_number: rows2[z].account_number,
                        account_name: rows2[z].account_name,
                        flag: rows2[z].flag,
                    }
                    data_array2.push(data2);
                }

                $("#total_receipt").numberbox('setValue', total_receipt);

                var jsonData = JSON.stringify(data_array);
                var jsonData2 = JSON.stringify(data_array2);

                if (totalrows == total_currency) {
                    if (currency != "IDR") {
                        $.ajax({
                            type: "post",
                            url: "<?= base_url('finance/ar_receipts/readExchangeRate') ?>",
                            data: "receipt_date=" + receipt_date + "&currency=" + currency,
                            dataType: "html",
                            success: function(exchange) {
                                $("#exchange").html(exchange);
                                $("#showExchange").show();
                            }
                        });
                    }

                    $.ajax({
                        type: "POST",
                        url: "<?= base_url('finance/ar_receipts/createJson') ?>",
                        data: {
                            jsonData: jsonData,
                            jsonData2: jsonData2,
                        },
                        success: function(response) {
                            addTable2('<?= base_url('finance/ar_receipts/calculateJournal/') ?>' + window.btoa(journal_type) + "/" + window.btoa(bank_account));

                            setTimeout(function() {
                                balance_journal();
                            }, 2000);
                        },
                    });

                } else {
                    toastr.error("Please correct the currency is not the same");
                }
            } else {
                toastr.warning("please selections your data in table first");
            }
        } else {
            toastr.info("Please Select Journal Type & Bank Account");
        }
    }

    function addTable2(link = "") {
        var lastIndex;
        var dg = $('#dg3').datagrid({
            url: link,
            singleSelect: true,
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
            var local_debit = 0;
            var local_credit = 0;
            for (let i = 0; i < totalrows; i++) {
                debit += parseFloat(rows[i].debit);
                credit += parseFloat(rows[i].credit);
                local_debit += parseFloat(rows[i].local_debit);
                local_credit += parseFloat(rows[i].local_credit);
            }

            $("#balance_debit").numberbox('setValue', debit);
            $("#balance_credit").numberbox('setValue', credit);
            $("#local_balance_debit").numberbox('setValue', local_debit);
            $("#local_balance_credit").numberbox('setValue', local_credit);
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
                delete: '0',
            });
            editIndex = $('#dg2').datagrid('getRows').length - 1;
            $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
        }
    }

    function append_dp() {
        var customer_id = $("#customer_id").combobox('getValue');
        var sales_invoice = $("#sales_invoice").combobox('getValue');

        if (endEditing()) {
            $.ajax({
                type: "post",
                url: "<?= base_url('finance/ar_receipts/readDp') ?>",
                data: "customer_id=" + customer_id + "&sales_invoice=" + sales_invoice,
                dataType: "json",
                success: function(dp) {
                    if (parseInt(dp.length) > 0) {
                        toastr.success("Data Down Payment Added Success");

                        for (let i = 0; i < dp.length; i++) {
                            $('#dg2').datagrid('appendRow', {
                                sales_invoice: dp[i].sales_invoice,
                                description: dp[i].description,
                                currency: dp[i].currency,
                                amount: dp[i].amount,
                                balance: dp[i].balance,
                                receipt: dp[i].receipt,
                                remarks: dp[i].remarks,
                                account_number: dp[i].account_number,
                                account_name: dp[i].account_name,
                                account_type: 'DEBIT',
                            });
                        }
                    } else {
                        toastr.info("Data Down Payment Not Found");
                    }
                }
            });
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

                // $.ajax({
                //     method: 'post',
                //     url: '<?= base_url('finance/ar_receipts/deleteSingle') ?>',
                //     data: {
                //         id: row.id,
                //         sales_invoice: row.sales_invoice
                //     },
                //     success: function(result) {
                //         var result = eval('(' + result + ')');
                //         toastr.success(result.message);
                //     },
                //     error: function(jqXHR, textStatus, errorThrown) {
                //         //toastr.error(jqXHR.statusText);
                //     },
                //     complete: function(data) {
                //         $('#dg').datagrid('reload');
                //     }
                // });

                $('#dg2').datagrid('deleteRow', getRowIndex(target));
                addJournal();
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

                    $("#receipt_date").datebox('disable');
                    $("#receipt_type").combobox('disable');
                    $("#customer_id").combogrid('disable');
                    $("#sales_invoice").combobox('disable');
                    $("#showExchange").hide();

                    var receipt_by = $("#receipt_by").combobox('getValue');

                    if (receipt_by == "CHEQUE") {
                        $("#f_cheque_no").show();
                    } else {
                        $("#f_cheque_no").hide();
                    }

                    $("#customer_id").combobox({
                        url: '<?= base_url('master/customers/reads') ?>',
                        valueField: 'id',
                        textField: 'name',
                        prompt: "Choose Customer",
                        onLoadSuccess: function(load_customer) {
                            $("#customer_id").combobox('setValue', row.customer_id);
                        },
                        onSelect: function(customer) {
                            $("#sales_invoice").combobox({
                                url: '<?= base_url('finance/ar_receipts/readInvoices/') ?>' + customer.id,
                                valueField: 'sales_invoice',
                                textField: 'sales_invoice',
                                multiple: true,
                                prompt: "Choose Sales Invoice No",
                                onLoadSuccess: function(load_invoice) {
                                    $("#sales_invoice").combobox('setValue', row.sales_invoice);
                                    $("#journal_type").combobox('setValue', row.journal_type_id);
                                },
                            });
                        }
                    });

                    preview('<?= base_url('finance/ar_receipts/reads/') ?>' + window.btoa(row.receipt_no));
                    addTable2('<?= base_url('finance/ar_receipts/readJournals/') ?>' + window.btoa(row.receipt_no) + "/" + window.btoa(row.journal_type_id) + "/" + window.btoa(row.bank_account));

                    setTimeout(function() {
                        balance_journal();
                        $("#receipt_no").textbox('setValue', row.receipt_no);
                    }, 2000);
                }else{
                    toastr.error("Cannot Update because this AR Receipt has been created in Posting Journal");
                }
            } else {
                toastr.error("Cannot Update because receipt status is closed");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //NOMOR AUTOMATIC
    // function number(trans_date) {
    //     $.ajax({
    //         type: "post",
    //         url: "<?= base_url('finance/ar_receipts/number/') ?>" + window.btoa(trans_date),
    //         dataType: "html",
    //         success: function(result) {
    //             $("#receipt_no").textbox('setValue', result);
    //         }
    //     });
    // }

    function number(trans_date, bank_code) {
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/ar_receipts/number/') ?>" + window.btoa(trans_date) +"/"+ bank_code,
            dataType: "html",
            success: function(result) {
                $("#receipt_no").textbox('setValue', result);
            }
        });
    }

    var editIndex = undefined;

    function preview(link = "") {
        var sales_invoice = $("#sales_invoice").combobox('getText');

        if (link == "") {
            var linked = '<?= base_url('finance/ar_receipts/datatablesTemp') ?>?sales_invoice=' + window.btoa(sales_invoice);
        } else {
            var linked = link;
        }

        if (sales_invoice == "") {
            toastr.info('Please select Sales Invoice');
        } else {
            var dg = $('#dg2').datagrid({
                url: linked,
                onClickRow: function(rowIndex) {
                    if (editIndex != rowIndex) {
                        $(this).datagrid('endEdit', editIndex);
                        $(this).datagrid('beginEdit', rowIndex);
                    }
                    editIndex = rowIndex;
                },
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

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    Swal.fire({
                        title: 'Please Wait for Delete Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        // $.ajax({
                        //     type: "post",
                        //     url: "<?= base_url('closing/locks/checkLock') ?>",
                        //     data: "period=" + row.receipt_date + "&menus_id=<?= $menus_id ?>",
                        //     dataType: "json",
                        //     success: function (lock) {
                        //         if(lock.total > 0){
                        //             Swal.close();
                        //             toastr.error("This module is not active by Accounting");
                        //             return false;
                        //         }

                                if(row.gl_no == null){
                                    $.ajax({
                                        method: 'post',
                                        url: '<?= base_url('finance/ar_receipts/delete') ?>',
                                        data: {
                                            receipt_no: row.receipt_no
                                        },
                                        success: function(result) {
                                            var result = eval('(' + result + ')');
                                            toastr.success(result.message);
                                            Swal.close();
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
                                    toastr.error("Cannot Delete because this AR Receipt has been created in Posting Journal");
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
        var filter_receipt_type = $("#filter_receipt_type").combobox('getValue');
        var filter_receipt_date_from = $("#filter_receipt_date_from").datebox('getValue');
        var filter_receipt_date_to = $("#filter_receipt_date_to").datebox('getValue');
        var filter_receipt_no = $("#filter_receipt_no").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_bank_no = $("#filter_bank_no").combobox('getValue');
        var filter_receipt_by = $("#filter_receipt_by").combobox('getValue');

        var url = "?filter_receipt_type=" + window.btoa(filter_receipt_type) +
            "&filter_receipt_date_from=" + window.btoa(filter_receipt_date_from) +
            "&filter_receipt_date_to=" + window.btoa(filter_receipt_date_to) +
            "&filter_receipt_no=" + window.btoa(filter_receipt_no) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_bank_no=" + window.btoa(filter_bank_no) +
            "&filter_receipt_by=" + window.btoa(filter_receipt_by);

        $('#dg').datagrid({
            url: '<?= base_url('finance/ar_receipts/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/ar_receipts/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //EXPORT TO EXCEL
    function excel() {
        var filter_receipt_type = $("#filter_receipt_type").combobox('getValue');
        var filter_receipt_date_from = $("#filter_receipt_date_from").datebox('getValue');
        var filter_receipt_date_to = $("#filter_receipt_date_to").datebox('getValue');
        var filter_receipt_no = $("#filter_receipt_no").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_bank_no = $("#filter_bank_no").combobox('getValue');
        var filter_receipt_by = $("#filter_receipt_by").combobox('getValue');

        var url = "?filter_receipt_type=" + window.btoa(filter_receipt_type) +
            "&filter_receipt_date_from=" + window.btoa(filter_receipt_date_from) +
            "&filter_receipt_date_to=" + window.btoa(filter_receipt_date_to) +
            "&filter_receipt_no=" + window.btoa(filter_receipt_no) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_bank_no=" + window.btoa(filter_bank_no) +
            "&filter_receipt_by=" + window.btoa(filter_receipt_by);

        window.location.assign('<?= base_url('finance/ar_receipts/print/excel') ?>' + url);
    }

    function excelDetail() {
        var filter_receipt_type = $("#filter_receipt_type").combobox('getValue');
        var filter_receipt_date_from = $("#filter_receipt_date_from").datebox('getValue');
        var filter_receipt_date_to = $("#filter_receipt_date_to").datebox('getValue');
        var filter_receipt_no = $("#filter_receipt_no").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_bank_no = $("#filter_bank_no").combobox('getValue');
        var filter_receipt_by = $("#filter_receipt_by").combobox('getValue');

        var url = "?filter_receipt_type=" + window.btoa(filter_receipt_type) +
            "&filter_receipt_date_from=" + window.btoa(filter_receipt_date_from) +
            "&filter_receipt_date_to=" + window.btoa(filter_receipt_date_to) +
            "&filter_receipt_no=" + window.btoa(filter_receipt_no) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_bank_no=" + window.btoa(filter_bank_no) +
            "&filter_receipt_by=" + window.btoa(filter_receipt_by);

        window.location.assign('<?= base_url('finance/ar_receipts/printDetail/excel') ?>' + url);
    }

    function excelJournal() {
        var filter_receipt_type = $("#filter_receipt_type").combobox('getValue');
        var filter_receipt_date_from = $("#filter_receipt_date_from").datebox('getValue');
        var filter_receipt_date_to = $("#filter_receipt_date_to").datebox('getValue');
        var filter_receipt_no = $("#filter_receipt_no").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_bank_no = $("#filter_bank_no").combobox('getValue');
        var filter_receipt_by = $("#filter_receipt_by").combobox('getValue');

        var url = "?filter_receipt_type=" + window.btoa(filter_receipt_type) +
            "&filter_receipt_date_from=" + window.btoa(filter_receipt_date_from) +
            "&filter_receipt_date_to=" + window.btoa(filter_receipt_date_to) +
            "&filter_receipt_no=" + window.btoa(filter_receipt_no) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_bank_no=" + window.btoa(filter_bank_no) +
            "&filter_receipt_by=" + window.btoa(filter_receipt_by);

        window.location.assign('<?= base_url('finance/ar_receipts/printJournal/excel') ?>' + url);
    }

    function print_voucher() {
        var row = $('#dg').datagrid('getSelections');
        if (row.length == 1) {
            var receipt_no = row[0].receipt_no;
            window.open("<?= base_url('finance/ar_receipts/print_voucher/') ?>" + window.btoa(receipt_no), "_blank", 'location=yes,height=600,width=1200,scrollbars=yes,status=yes');
        } else {
            toastr.warning("Please select one data in the table first!", "Information");
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

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('finance/ar_receipts/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.receipt_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                ddv.datagrid({
                    url: '<?= base_url('finance/ar_receipts/datatables/details?receipt_no=') ?>' + window.btoa(row.receipt_no),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'sales_invoice',
                            title: 'Sales Invoice',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'description',
                            title: 'Description',
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
                            formatter: numberformat
                        }, {
                            field: 'balance',
                            title: 'Balance',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'receipt',
                            title: 'Receipt',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
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
                        }, {
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
                    var receipt_type = $("#receipt_type").combobox('getValue');
                    var receipt_date = $("#receipt_date").datebox('getValue');
                    var receipt_no = $("#receipt_no").textbox('getValue');
                    var customer_id = $("#customer_id").combobox('getValue');
                    var journal_type_id = $("#journal_type").combobox('getValue');
                    var bank_account = $("#bank_account").combogrid('getValue');
                    var receipt_by = $("#receipt_by").combobox('getValue');
                    var cheque_no = $("#cheque_no").textbox('getValue');
                    var note = $("#note").textbox('getValue');
                    var total_receipt = $("#total_receipt").numberbox('getValue');

                    var balance_debit = $("#balance_debit").numberbox('getValue');
                    var balance_credit = $("#balance_credit").numberbox('getValue');

                    // $.ajax({
                    //     type: "post",
                    //     url: "<?= base_url('closing/locks/checkLock') ?>",
                    //     data: "period=" + receipt_date + "&menus_id=<?= $menus_id ?>",
                    //     dataType: "json",
                    //     success: function (lock) {
                    //         if(lock.total > 0){
                    //             toastr.error("This module is not active by Accounting");
                    //             return false;
                    //         }

                            if (parseFloat(balance_debit) == parseFloat(balance_credit)) {
                                if (sales_invoice == "" || bank_account == "" || receipt_date == "" || receipt_by == "" || journal_type_id == "") {
                                    toastr.error("please complete your input data");
                                } else {
                                    $('#dg2').datagrid('acceptChanges');
                                    var rows = $('#dg2').datagrid('getRows');
                                    var totalrows = rows.length;

                                    var rows2 = $('#dg3').datagrid('getRows');
                                    var totalrows2 = rows2.length;
                                    endEditing2();

                                    if (totalrows > 0) {
                                        requestData(totalrows, rows);
                                        $('#dlg_insert').dialog('close');

                                        Swal.fire({
                                            title: 'Please Wait for Saving Data',
                                            showConfirmButton: false,
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            },
                                        });

                                        $.ajax({
                                            method: 'post',
                                            url: "<?= base_url('finance/ar_receipts/deleteJournal') ?>",
                                            data: {
                                                receipt_no: receipt_no
                                            },
                                            success: function(result) {
                                                if (totalrows2 > 0) {
                                                    for (let z = 0; z < totalrows2; z++) {
                                                        $.ajax({
                                                            type: "post",
                                                            url: '<?= base_url('finance/ar_receipts/createJournals') ?>',
                                                            data: {
                                                                receipt_no: receipt_no,
                                                                account_number: rows2[z].account_number,
                                                                account_name: rows2[z].account_name,
                                                                description: rows2[z].description,
                                                                debit: rows2[z].debit,
                                                                credit: rows2[z].credit,
                                                                local_debit: rows2[z].local_debit,
                                                                local_credit: rows2[z].local_credit,
                                                                flag: rows2[z].flag,
                                                            },
                                                            dataType: "json",
                                                            success: function(result2) {
                                                                //
                                                            }
                                                        });
                                                    }
                                                }
                                            }
                                        });

                                        function requestData(total, json, jml = 1, value = 0) {
                                            if (value < 100) {
                                                value = Math.floor((jml / total) * 100);
                                                var i = (jml - 1);

                                                if (json[i].sales_invoice) {
                                                    $.ajax({
                                                        type: "post",
                                                        url: '<?= base_url('finance/ar_receipts/create') ?>',
                                                        data: {
                                                            receipt_type: receipt_type,
                                                            receipt_date: receipt_date,
                                                            receipt_no: receipt_no,
                                                            customer_id: customer_id,
                                                            journal_type_id: journal_type_id,
                                                            bank_account: bank_account,
                                                            receipt_by: receipt_by,
                                                            cheque_no: cheque_no,
                                                            note: note,
                                                            total_receipt: total_receipt,
                                                            id: rows[i].id,
                                                            sales_invoice: rows[i].sales_invoice,
                                                            description: rows[i].description,
                                                            so_number: rows[i].so_number,
                                                            currency: rows[i].currency,
                                                            amount: rows[i].amount,
                                                            balance: rows[i].balance,
                                                            receipt: rows[i].receipt,
                                                            remarks: rows[i].remarks,
                                                            account_number: rows[i].account_number,
                                                            account_type: rows[i].account_type,
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
                                                            }
                                                        }
                                                    });
                                                }
                                            }
                                        }

                                        $('#dg').datagrid('reload');
                                    } else {
                                        toastr.warning("please selections your data in table first");
                                    }

                                    // $.ajax({
                                    //     type: "post",
                                    //     url: "<?= base_url('finance/ar_receipts/deleteJournal') ?>",
                                    //     data: "receipt_no=" + receipt_no,
                                    //     dataType: "json",
                                    //     success: function(response) {
                                    //         Swal.fire({
                                    //             title: 'Please Wait for Saving Data',
                                    //             showConfirmButton: false,
                                    //             allowOutsideClick: false,
                                    //             allowEscapeKey: false,
                                    //             didOpen: () => {
                                    //                 Swal.showLoading();
                                    //             },
                                    //         });

                                    //         if (totalrows > 0) {
                                    //             for (let i = 0; i < totalrows; i++) {
                                    //                 if (rows[i].sales_invoice) {
                                    //                     $.ajax({
                                    //                         type: "post",
                                    //                         url: '<?= base_url('finance/ar_receipts/create') ?>',
                                    //                         data: {
                                    //                             receipt_type: receipt_type,
                                    //                             receipt_date: receipt_date,
                                    //                             receipt_no: receipt_no,
                                    //                             customer_id: customer_id,
                                    //                             journal_type_id: journal_type_id,
                                    //                             bank_account: bank_account,
                                    //                             receipt_by: receipt_by,
                                    //                             cheque_no: cheque_no,
                                    //                             note: note,
                                    //                             total_receipt: total_receipt,
                                    //                             id: rows[i].id,
                                    //                             sales_invoice: rows[i].sales_invoice,
                                    //                             description: rows[i].description,
                                    //                             so_number: rows[i].so_number,
                                    //                             currency: rows[i].currency,
                                    //                             amount: rows[i].amount,
                                    //                             balance: rows[i].balance,
                                    //                             receipt: rows[i].receipt,
                                    //                             remarks: rows[i].remarks,
                                    //                             account_number: rows[i].account_number,
                                    //                             account_type: rows[i].account_type,
                                    //                         },
                                    //                         dataType: "json",
                                    //                         success: function(result) {
                                    //                             Swal.fire({
                                    //                                 title: result.message,
                                    //                                 icon: result.theme,
                                    //                                 confirmButtonText: 'Ok',
                                    //                                 allowOutsideClick: false,
                                    //                             }).then((result) => {
                                    //                                 if (result.isConfirmed) {
                                    //                                     window.location.reload();
                                    //                                 }
                                    //                             });
                                    //                         }
                                    //                     });
                                    //                 }
                                    //             }

                                    //             if (totalrows2 > 0) {
                                    //                 for (let z = 0; z < totalrows2; z++) {
                                    //                     $.ajax({
                                    //                         type: "post",
                                    //                         url: '<?= base_url('finance/ar_receipts/createJournals') ?>',
                                    //                         data: {
                                    //                             receipt_no: receipt_no,
                                    //                             account_number: rows2[z].account_number,
                                    //                             account_name: rows2[z].account_name,
                                    //                             description: rows2[z].description,
                                    //                             debit: rows2[z].debit,
                                    //                             credit: rows2[z].credit,
                                    //                             local_debit: rows2[z].local_debit,
                                    //                             local_credit: rows2[z].local_credit,
                                    //                             flag: rows2[z].flag,
                                    //                         },
                                    //                         dataType: "json",
                                    //                         success: function(result2) {
                                    //                             //
                                    //                         }
                                    //                     });
                                    //                 }
                                    //             }

                                    //             $('#dg').datagrid('reload');
                                    //             $('#dlg_insert').dialog('close');
                                    //         } else {
                                    //             toastr.warning("please selections your data in table first");
                                    //         }
                                    //     }
                                    // });
                                }
                            } else {
                                toastr.error("Balance Debit Cannot match on Balance Credit");
                            }
                    //     }
                    // });
                }
            }]
        });

        $("#filter_customer").combobox({
            url: '<?= base_url('master/customers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Customer",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(customer) {
                $("#filter_receipt_no").combobox({
                    url: '<?= base_url('finance/ar_receipts/readReceipts/') ?>' + customer.id,
                    valueField: 'receipt_no',
                    textField: 'receipt_no',
                    prompt: "Choose Receipt No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $("#filter_invoice_no").combobox({
                    url: '<?= base_url('finance/ar_receipts/readInvoices/') ?>' + customer.id,
                    valueField: 'sales_invoice',
                    textField: 'sales_invoice',
                    prompt: "Choose Sales Invoice",
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
            panelWidth: 500,
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
                }, {
                    field: 'bank_code',
                    title: 'Bank Code',
                    width: 100
                }, ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });

        $("#journal_type").combobox({
            url: '<?= base_url('finance/journal_types/reads/' . base64_encode("AP PAYMENT")) ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Journal Types"
        });

        $("#customer_id").combobox({
            url: '<?= base_url('master/customers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Customer",
            onSelect: function(customer) {
                var receipt_type = $("#receipt_type").combobox('getValue');

                // $("#sales_invoice").combobox({
                //     url: '<?= base_url('finance/ar_receipts/readInvoiceType?customer_id=') ?>' + customer.id + "&receipt_type=" + receipt_type,
                //     valueField: 'number',
                //     textField: 'number',
                //     multiple: true,
                //     prompt: "Choose Sales Invoice No",
                //     onSelect: function(pi) {
                //         if (pi.journal_type_id != null) {
                //             $("#journal_type").combobox('setValue', pi.journal_type_id);
                //         } else {
                //             toastr.info("The journal type on the Sales Invoice is still empty");
                //             $("#journal_type").combobox('clear');
                //         }
                //     }
                // });

                $("#sales_invoice").combogrid({
                    url: '<?= base_url('finance/ar_receipts/readInvoiceType?customer_id=') ?>' + customer.id + "&receipt_type=" + receipt_type,
                    panelWidth: 520,
                    idField: 'number',
                    textField: 'number',
                    mode: 'remote',
                    multiple: true,
                    prompt: "Choose Purchase Invoice No",
                    columns: [
                        [ {
                            field: 'ck', // Kolom checkbox
                            checkbox: true, // Mengaktifkan checkbox
                        }, {
                            field: 'no',
                            title: 'No',
                            width: 60
                        }, {
                            field: 'number',
                            title: 'Sales Invoice No',
                            width: 150,
                            align: 'left'
                        }, {
                            field: 'trans_date',
                            title: 'SI Date',
                            width: 100,
                            align: 'left'
                        }, {
                            field: 'due_date',
                            title: 'Payment Due',
                            width: 100,
                            align: 'left'
                        }]
                    ],
                    fitColumns: true, // Menyesuaikan kolom secara otomatis
                    selectOnCheck: true, // Pilih baris ketika checkbox di-check
                    checkOnSelect: true,

                    onSelect: function (index, row) {
                        if (row.journal_type_id != null) {
                            $("#journal_type").combobox('setValue', row.journal_type_id);
                        } else {
                            toastr.info("The journal type on the Sales Invoice is still empty");
                            $("#journal_type").combobox('clear');
                        }
                    }
                });
            }
        });

        $("#bank_account").combogrid({
            url: '<?= base_url('finance/account_banks/reads') ?>',
            panelWidth: 500,
            idField: 'bank_account',
            textField: 'bank_name',
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
                }, {
                    field: 'bank_code',
                    title: 'Bank Code',
                    width: 100
                }, ]
            ],
            onSelect: function (index, row) {
                $("#bank_code").textbox('setValue', row.bank_code);
                var trans_date = $("#receipt_date").datebox('getValue');
                number(trans_date,row.bank_code);
            }
        });

        $("#receipt_by").combobox({
            onChange: function(val) {
                if (val == "CHEQUE") {
                    $("#f_cheque_no").show();
                } else {
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
<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div style="padding: 20px;">
        <ul>
            <li>The Data Amount Forecast Customer is taken from <b>Order Management > Forecasting > Forecast Customer</b></li>
        </ul>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'customer_name',width:220,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'issued_date',width:100,halign:'center'">Issued Date</th>
            <th colspan="2" data-options="field:'',width:200,halign:'center'">Period</th>
            <th rowspan="2" data-options="field:'revision',width:80,align:'center'">Revision</th>
            <th rowspan="2" data-options="field:'remark',width:100,halign:'center'">Remarks</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'p_month',width:80,align:'center'"> Month</th>
            <th data-options="field:'p_year',width:80,align:'center'"> Year</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Issued Date</span>
                    <input style="width:30%;" id="filter_issued_date_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser" class="easyui-datebox">
                    <input style="width:30%;" id="filter_issued_date_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" id="filter_period_month" value="<?= date("m") ?>" class="easyui-combobox">
                    <input style="width:30%;" id="filter_period_year" value="<?= date("Y") ?>" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" id="filter_customer_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <select style="width:30%;" id="filter_revision" class="easyui-combobox" panelHeight="auto">
                        <option value="" selected disabled>Choose All</option>
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Detail Histories -->
<div id="dlg_history" class="easyui-dialog" title="Forecast Histories" data-options="closed: true,modal:true" style="width: 1300px; height: 500px; top: 20px; left: 20px;">
    <table id="dg_history" class="easyui-datagrid" style="width:100%;"></table>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/amount_forecast/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('planning/amount_forecast/create') ?>';
        $('#frm_insert').form('clear');
        $("#customer_id").combogrid('enable');
        $("#p_month").combobox('enable');
        $("#p_year").combobox('enable');
        $("#document_no").textbox('enable');

        $("#revision").combobox('setValue', '0');
        $("#p_month").combobox('setValue', '<?= date("m") ?>');
        $("#p_month").combobox('setValue', '<?= date("m") ?>');
        $("#p_year").combobox('setValue', '<?= date("Y") ?>');
        $("#issued_date").datebox('setValue', '<?= date("Y-m-d") ?>');
    }

    function addTable(customer_id, link = "") {
        var p_month = $("#p_month").combobox('getValue');
        var p_year = $("#p_year").combobox('getValue');

        $.ajax({
            type: "post",
            url: "<?= base_url('planning/amount_forecast/readPeriodLists') ?>",
            data: "p_month=" + p_month + "&p_year=" + p_year,
            dataType: "json",
            success: function(result) {
                $('#dg2').datagrid({
                    url: link,
                    singleSelect: true,
                    columns: [
                        [{
                            field: 'item_fg_number',
                            width: 200,
                            halign: 'center',
                            title: "Product No.",
                            editor: {
                                type: 'combogrid',
                                options: {
                                    url: '<?= base_url('master/customer_items/reads/'); ?>' + window.btoa(customer_id),
                                    required: true,
                                    panelWidth: 400,
                                    idField: 'number',
                                    textField: 'number',
                                    mode: 'remote',
                                    fitColumns: true,
                                    prompt: 'Choose Product No.',
                                    columns: [
                                        [{
                                            field: 'number',
                                            title: 'Product No.',
                                            width: 150
                                        }, {
                                            field: 'name',
                                            title: 'Product Name',
                                            width: 200
                                        }]
                                    ],
                                    onSelect: function(value, rows) {
                                        var dg = $('#dg2');
                                        var row = dg.datagrid('getSelected');
                                        var rowIndex = dg.datagrid('getRowIndex', row);

                                        var ed = dg.datagrid('getEditor', {
                                            index: rowIndex,
                                            field: 'item_fg_name'
                                        });
                                        var ed2 = dg.datagrid('getEditor', {
                                            index: rowIndex,
                                            field: 'item_fg_id'
                                        });
                                        var ed3 = dg.datagrid('getEditor', {
                                            index: rowIndex,
                                            field: 'item_fg_customer'
                                        });

                                        $(ed.target).textbox('setValue', rows.name);
                                        $(ed2.target).textbox('setValue', rows.id);
                                        $(ed3.target).textbox('setValue', rows.number_customer);
                                    }
                                }
                            }
                        }, {
                            field: 'item_fg_id',
                            width: 150,
                            hidden: true,
                            halign: 'center',
                            title: "Product ID",
                            editor: {
                                type: 'textbox'
                            }
                        }, {
                            field: 'item_fg_name',
                            width: 200,
                            halign: 'center',
                            title: "Product Name",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'item_fg_customer',
                            width: 150,
                            halign: 'center',
                            title: "Product Customer",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'month_1',
                            width: 80,
                            align: 'center',
                            title: result[0].name,
                            editor: {
                                type: 'numberbox',
                                options: {
                                    required: true,
                                }
                            }
                        }, {
                            field: 'month_2',
                            width: 80,
                            align: 'center',
                            title: result[1].name,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'month_3',
                            width: 80,
                            align: 'center',
                            title: result[2].name,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'month_4',
                            width: 80,
                            align: 'center',
                            title: result[3].name,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'month_5',
                            width: 80,
                            align: 'center',
                            title: result[4].name,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'month_6',
                            width: 80,
                            align: 'center',
                            title: result[5].name,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'month_7',
                            width: 80,
                            align: 'center',
                            title: result[6].name,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'month_8',
                            width: 80,
                            align: 'center',
                            title: result[7].name,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'month_9',
                            width: 80,
                            align: 'center',
                            title: result[8].name,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'month_10',
                            width: 80,
                            align: 'center',
                            title: result[9].name,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'month_11',
                            width: 80,
                            align: 'center',
                            title: result[10].name,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'month_12',
                            width: 80,
                            align: 'center',
                            title: result[11].name,
                            editor: {
                                type: 'numberbox',
                            }
                        }]
                    ],
                    onClickCell: onClickCell
                });
            }
        });
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

    function onClickCell(index, field) {
        if (editIndex != index) {
            if (endEditing()) {
                $('#dg2').datagrid('selectRow', index).datagrid('beginEdit', index);
                editIndex = index;
            } else {
                setTimeout(function() {
                    $('#dg2').datagrid('selectRow', editIndex);
                }, 0);
            }
        }
    }

    function append() {
        var customer_id = $("#customer_id").combogrid('getValue');
        if (customer_id != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Customer first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return true;
        }

        var dg = $('#dg2');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: editIndex,
            field: 'item_fg_id'
        });

        var customer_id = $("#customer_id").combogrid('getValue');
        // var p_month = $("#p_month").combobox('getValue');
        // var p_year = $("#p_year").combobox('getValue');
        // var revision = $("#revision").combobox('getValue');
        var item_fg_id = $(ed.target).textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('planning/amount_forecast/delete') ?>',
            data: {
                customer_id: row.customer_id,
                // p_month: row.p_month,
                // p_year: row.p_year,
                // revision: row.revision,
                item_fg_id: item_fg_id,
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

        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //FILTER DATA
    function filter() {
        var filter_issued_date_from = $("#filter_issued_date_from").datebox('getValue');
        var filter_issued_date_to = $("#filter_issued_date_to").datebox('getValue');
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combogrid('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');

        var url = "?filter_issued_date_from=" + window.btoa(filter_issued_date_from) +
            "&filter_issued_date_to=" + window.btoa(filter_issued_date_to) +
            "&filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_revision=" + window.btoa(filter_revision);

        $('#dg').datagrid({
            url: '<?= base_url('planning/amount_forecast/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/amount_forecast/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_issued_date_from = $("#filter_issued_date_from").datebox('getValue');
        var filter_issued_date_to = $("#filter_issued_date_to").datebox('getValue');
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combogrid('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');

        var url = "?filter_issued_date_from=" + window.btoa(filter_issued_date_from) +
            "&filter_issued_date_to=" + window.btoa(filter_issued_date_to) +
            "&filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_revision=" + window.btoa(filter_revision);

        window.location.assign('<?= base_url('planning/amount_forecast/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('planning/amount_forecast/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.customer_name + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                $.ajax({
                    type: "post",
                    url: "<?= base_url('planning/amount_forecast/readPeriodLists') ?>",
                    data: "p_month=" + row.p_month + "&p_year=" + row.p_year,
                    dataType: "json",
                    success: function(result) {
                        ddv.datagrid({
                            url: '<?= base_url('planning/amount_forecast/datatableDetails?customer_id=') ?>' + window.btoa(row.customer_id) + "&p_month=" + window.btoa(row.p_month) + "&p_year=" + window.btoa(row.p_year) + "&revision=" + window.btoa(row.revision) + "&document_no=" + window.btoa(row.document_no),
                            singleSelect: true,
                            rownumbers: true,
                            columns: [
                                // First row of headers
                                [{
                                    field: 'btn',
                                    title: '#',
                                    rowspan: 2,
                                    halign: 'center',
                                    width: 50,
                                    formatter: btnHistories
                                }, {
                                    field: 'document_no',
                                    title: 'Document No',
                                    rowspan: 2,
                                    halign: 'center',
                                    width: 120
                                }, {
                                    field: 'item_fg_number',
                                    title: 'Product No',
                                    rowspan: 2,
                                    halign: 'center',
                                    width: 120
                                }, {
                                    field: 'item_fg_name',
                                    title: 'Product Name',
                                    rowspan: 2,
                                    halign: 'center',
                                    width: 120
                                }, {
                                    field: 'item_fg_customer',
                                    title: 'Product Customer',
                                    rowspan: 2,
                                    halign: 'center',
                                    width: 150
                                }, {
                                    field: 'currency',
                                    title: 'Currency',
                                    rowspan: 2,
                                    halign: 'center',
                                    align: 'center',
                                    width: 80
                                }, {
                                    field: 'price',
                                    title: 'Price',
                                    rowspan: 2,
                                    halign: 'center',
                                    align: 'right',
                                    width: 100,
                                    formatter: priceFormat
                                }, {
                                    field: 'rate',
                                    title: 'Rate',
                                    rowspan: 2,
                                    halign: 'center',
                                    align: 'right',
                                    width: 100,
                                    formatter: validateRate
                                }, {
                                    title: result[0].name, // Merged header
                                    colspan: 2
                                }, {
                                    title: result[1].name,
                                    colspan: 2
                                }, {
                                    title: result[2].name,
                                    colspan: 2
                                }, {
                                    title: result[3].name,
                                    colspan: 2
                                }, {
                                    title: result[4].name,
                                    colspan: 2
                                }, {
                                    title: result[5].name,
                                    colspan: 2
                                }, {
                                    title: result[6].name,
                                    colspan: 2
                                }, {
                                    title: result[7].name,
                                    colspan: 2
                                }, {
                                    title: result[8].name,
                                    colspan: 2
                                }, {
                                    title: result[9].name,
                                    colspan: 2
                                }, {
                                    title: result[10].name,
                                    colspan: 2
                                }, {
                                    title: result[11].name,
                                    colspan: 2
                                }],
                                // Second row of headers (under the merged header)
                                [{
                                    field: 'month_1',
                                    width: 70,
                                    halign: 'center',
                                    align: 'right',
                                    title: 'Qty',
                                    formatter: numberFormat
                                }, {
                                    field: 'amount_1',
                                    title: 'Amount',
                                    halign: 'center',
                                    align: 'right',
                                    width: 100,
                                    formatter: exchangeFormat
                                }, {
                                    field: 'month_2',
                                    width: 70,
                                    halign: 'center',
                                    align: 'right',
                                    title: 'Qty',
                                    formatter: numberFormat
                                }, {
                                    field: 'amount_2',
                                    title: 'Amount',
                                    halign: 'center',
                                    align: 'right',
                                    width: 100,
                                    formatter: exchangeFormat
                                }, {
                                    field: 'month_3',
                                    width: 70,
                                    halign: 'center',
                                    align: 'right',
                                    title: 'Qty',
                                    formatter: numberFormat
                                }, {
                                    field: 'amount_3',
                                    title: 'Amount',
                                    halign: 'center',
                                    align: 'right',
                                    width: 100,
                                    formatter: exchangeFormat
                                }, {
                                    field: 'month_4',
                                    width: 70,
                                    halign: 'center',
                                    align: 'right',
                                    title: 'Qty',
                                    formatter: numberFormat
                                }, {
                                    field: 'amount_4',
                                    title: 'Amount',
                                    halign: 'center',
                                    align: 'right',
                                    width: 100,
                                    formatter: exchangeFormat
                                }, {
                                    field: 'month_5',
                                    width: 70,
                                    halign: 'center',
                                    align: 'right',
                                    title: 'Qty',
                                    formatter: numberFormat
                                }, {
                                    field: 'amount_5',
                                    title: 'Amount',
                                    halign: 'center',
                                    align: 'right',
                                    width: 100,
                                    formatter: exchangeFormat
                                }, {
                                    field: 'month_6',
                                    width: 70,
                                    halign: 'center',
                                    align: 'right',
                                    title: 'Qty',
                                    formatter: numberFormat
                                }, {
                                    field: 'amount_6',
                                    title: 'Amount',
                                    halign: 'center',
                                    align: 'right',
                                    width: 100,
                                    formatter: exchangeFormat
                                }, {
                                    field: 'month_7',
                                    width: 70,
                                    halign: 'center',
                                    align: 'right',
                                    title: 'Qty',
                                    formatter: numberFormat
                                }, {
                                    field: 'amount_7',
                                    title: 'Amount',
                                    halign: 'center',
                                    align: 'right',
                                    width: 100,
                                    formatter: exchangeFormat
                                }, {
                                    field: 'month_8',
                                    width: 70,
                                    halign: 'center',
                                    align: 'right',
                                    title: 'Qty',
                                    formatter: numberFormat
                                }, {
                                    field: 'amount_8',
                                    title: 'Amount',
                                    halign: 'center',
                                    align: 'right',
                                    width: 100,
                                    formatter: exchangeFormat
                                }, {
                                    field: 'month_9',
                                    width: 70,
                                    halign: 'center',
                                    align: 'right',
                                    title: 'Qty',
                                    formatter: numberFormat
                                }, {
                                    field: 'amount_9',
                                    title: 'Amount',
                                    halign: 'center',
                                    align: 'right',
                                    width: 100,
                                    formatter: exchangeFormat
                                }, {
                                    field: 'month_10',
                                    width: 70,
                                    halign: 'center',
                                    align: 'right',
                                    title: 'Qty',
                                    formatter: numberFormat
                                }, {
                                    field: 'amount_10',
                                    title: 'Amount',
                                    halign: 'center',
                                    align: 'right',
                                    width: 100,
                                    formatter: exchangeFormat
                                }, {
                                    field: 'month_11',
                                    width: 70,
                                    halign: 'center',
                                    align: 'right',
                                    title: 'Qty',
                                    formatter: numberFormat
                                }, {
                                    field: 'amount_11',
                                    title: 'Amount',
                                    halign: 'center',
                                    align: 'right',
                                    width: 100,
                                    formatter: exchangeFormat
                                }, {
                                    field: 'month_12',
                                    width: 70,
                                    halign: 'center',
                                    align: 'right',
                                    title: 'Qty',
                                    formatter: numberFormat
                                }, {
                                    field: 'amount_12',
                                    title: 'Amount',
                                    halign: 'center',
                                    align: 'right',
                                    width: 100,
                                    formatter: exchangeFormat
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
            }
        });

    });

    $('#customer_id').combogrid({
        url: '<?= base_url('master/customers/reads/'); ?>',
        panelWidth: 550,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Customer",
        columns: [
            [{
                field: 'id',
                title: 'Customer ID',
                width: 110
            }, {
                field: 'number',
                title: 'Customer Code',
                width: 110
            }, {
                field: 'name',
                title: 'Customer Name',
                width: 300
            }]
        ],
        onSelect: function(val, row) {
            //ADD DATA
            addTable(row.id);
        }
    });

    $('#p_month').combobox({
        url: '<?= base_url('planning/amount_forecast/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
    });

    $('#p_year').combobox({
        url: '<?= base_url('planning/amount_forecast/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
    });

    $("#issued_date").datebox({
        onChange: function(value){
            $.ajax({
                type: "post",
                url: "<?= base_url('planning/amount_forecast/autoid') ?>",
                data: "issued_date="+value,
                dataType: "html",
                success: function(response) {
                    $('#document_no').textbox('setValue', response);
                }
            });
        }
    });

    $('#filter_customer_id').combogrid({
        url: '<?= base_url('master/customers/reads'); ?>',
        panelWidth: 550,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Customer",
        columns: [
            [{
                field: 'id',
                title: 'Customer ID',
                width: 110
            }, {
                field: 'number',
                title: 'Customer Code',
                width: 110
            }, {
                field: 'name',
                title: 'Customer Name',
                width: 300
            }]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $('#filter_period_month').combobox({
        url: '<?= base_url('planning/amount_forecast/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_period_year').combobox({
        url: '<?= base_url('planning/amount_forecast/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    // FORMAT tahun-bulan-tanggal
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

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

    function numberFormat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function validateRate(value, row) {
        let rate = value;
        if (row.currency == "IDR") {
            rate = 1;
        } 
        return "<b>" + parseFloat(rate) + "</b>";
    }

    function exchangeFormat(value, row) {
        let rate;
        if (row.currency == "IDR") {
            rate = 1;
        } else {
            if (row.rate == null) {
                rate = 0;
            } else {
                rate = row.rate;
            }
        }
        
        let toIDR = value * rate;

        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(toIDR) + "</b>";
    }

    function priceFormat(value, row) {
        let price;
        if (row.currency == "IDR") {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0
            });
            price = formatter.format(value);
        } else {
            price = value.toString().replace('.', ',');
        }
        return "<b>" + price + "</b>";
    }

    function btnHistories(val, row) {
        var history = "viewHistories('" + row.customer_id + "','" + row.item_fg_id + "','" + row.p_month + "','" + row.p_year + "','" + row.revision + "')";
        return '<a class="btn btn-primary w-100" onClick="' + history + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i></a>';
    }

    function viewHistories(customer_id, item_fg_id, p_month, p_year) {
        $("#dlg_history").dialog('open');

        $.ajax({
            type: "post",
            url: "<?= base_url('planning/amount_forecast/readPeriodLists') ?>",
            data: "p_month=" + p_month + "&p_year=" + p_year,
            dataType: "json",
            success: function(result) {
                $("#dg_history").datagrid({
                    url: '<?= base_url('planning/amount_forecast/datatableHistories?customer_id=') ?>' + btoa(customer_id) + "&item_fg_id=" + btoa(item_fg_id) + "&p_month=" + btoa(p_month) + "&p_year=" + btoa(p_year),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'created_date',
                            title: 'Trans Date',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_fg_number',
                            title: 'Product No',
                            halign: 'center',
                            width: 120
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 120
                        }, {
                            field: 'month_1',
                            width: 70,
                            halign: 'center',
                            align: 'right',
                            title: result[0].name,
                            formatter: numberFormat
                        }, {
                            field: 'month_2',
                            width: 70,
                            halign: 'center',
                            align: 'right',
                            title: result[1].name,
                            formatter: numberFormat
                        }, {
                            field: 'month_3',
                            width: 70,
                            halign: 'center',
                            align: 'right',
                            title: result[2].name,
                            formatter: numberFormat
                        }, {
                            field: 'month_4',
                            width: 70,
                            halign: 'center',
                            align: 'right',
                            title: result[3].name,
                            formatter: numberFormat
                        }, {
                            field: 'month_5',
                            width: 70,
                            halign: 'center',
                            align: 'right',
                            title: result[4].name,
                            formatter: numberFormat
                        }, {
                            field: 'month_6',
                            width: 70,
                            halign: 'center',
                            align: 'right',
                            title: result[5].name,
                            formatter: numberFormat
                        }, {
                            field: 'month_7',
                            width: 70,
                            halign: 'center',
                            align: 'right',
                            title: result[6].name,
                            formatter: numberFormat
                        }, {
                            field: 'month_8',
                            width: 70,
                            halign: 'center',
                            align: 'right',
                            title: result[7].name,
                            formatter: numberFormat
                        }, {
                            field: 'month_9',
                            width: 70,
                            halign: 'center',
                            align: 'right',
                            title: result[8].name,
                            formatter: numberFormat
                        }, {
                            field: 'month_10',
                            width: 70,
                            halign: 'center',
                            align: 'right',
                            title: result[9].name,
                            formatter: numberFormat
                        }, {
                            field: 'month_11',
                            width: 70,
                            halign: 'center',
                            align: 'right',
                            title: result[10].name,
                            formatter: numberFormat
                        }, {
                            field: 'month_12',
                            width: 70,
                            halign: 'center',
                            align: 'right',
                            title: result[11].name,
                            formatter: numberFormat
                        }]
                    ]
                });
            }
        });
    }

</script>
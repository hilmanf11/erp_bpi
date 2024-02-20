<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'print',width:59,align:'center', formatter:btnPrint">Print</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
            <th rowspan="2" data-options="field:'delivery_order_no',width:150,halign:'center'">Delivery Order No</th>
            <th rowspan="2" data-options="field:'delivery_order_date',width:100,halign:'center'">Delivery Order<br>Date</th>
            <th rowspan="2" data-options="field:'delivery_date',width:100,halign:'center'">Delivery Date</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'trans_type',width:100,halign:'center'">Transaction<br>Type</th>
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'delivery_note_no',width:150,halign:'center'">Delivery Note</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:120,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:120,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 240px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" id="filter_customer_id" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order No</span>
                    <input style="width:60%;" id="filter_delivery_order_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Order No</span>
                    <input style="width:60%;" id="filter_sales_order_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Order No</span>
                    <input style="width:60%;" id="filter_customer_order_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1200px; height: 600px; padding:10px; top: 20px; left: 10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order Date</span>
                    <input style="width:40%;" name="delivery_order_date" id="delivery_order_date" required="" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Date</span>
                    <input style="width:40%;" name="delivery_date" id="delivery_date" required="" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order No</span>
                    <input style="width:60%;" name="delivery_order_no" id="delivery_order_no" readonly required class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Type</span>
                    <select style="width:60%;" name="trans_type" id="trans_type" required class="easyui-combobox" panelHeight="auto">
                        <option value="SALES">SALES</option>
                        <option value="RETURN">RETURN</option>
                        <option value="SAMPLE">SAMPLE</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%; height: 100px;" name="remarks" id="remarks" class="easyui-textbox" multiline="true">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Delivery Order Lists" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/delivery_orders/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('planning/delivery_orders/create') ?>';
        $('#frm_insert').form('clear');

        $("#delivery_order_date").datebox('enable');
        $("#delivery_date").datebox('enable');
        $("#customer_id").combobox('enable');

        $('#delivery_order_date').datebox({
            onChange: function(delivery_order_date) {
                if (delivery_order_date != "") {
                    number(delivery_order_date);
                }
            }
        });

        $("#delivery_date").datebox({
            onChange: function(delivery_date) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('planning/delivery_orders/readSalesOrderDeliveries') ?>",
                    data: "delivery_date=" + delivery_date,
                    dataType: "json",
                    success: function(response) {
                        if (response.length == 0) {
                            toastr.error("Sales Order Delivery Data Not Found on Delivery Date");
                            $("#delivery_date").datebox('clear');
                        }
                    }
                });
            }
        })
    }

    function number(delivery_order_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/delivery_orders/number/') ?>" + window.btoa(delivery_order_date),
            dataType: "html",
            success: function(result) {
                $("#delivery_order_no").textbox('setValue', result);
            }
        });
    }

    function addTable(customer_id, link = "") {
        $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'item_fg_id',
                    width: 150,
                    halign: 'center',
                    title: "Product ID",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('planning/delivery_orders/readItemFg/'); ?>' + customer_id,
                            required: true,
                            panelWidth: 650,
                            idField: 'id',
                            textField: 'id',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product ID',
                            columns: [
                                [{
                                    field: 'id',
                                    title: 'Product ID',
                                    width: 200
                                }, {
                                    field: 'number',
                                    title: 'Product No.',
                                    width: 200
                                }, {
                                    field: 'name',
                                    title: 'Product Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var delivery_date = $("#delivery_date").datebox('getValue');

                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_number'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_name'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'sales_order_no'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty_so'
                                });
                                var ed6 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty_remain'
                                });
                                var ed7 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty_do'
                                });
                                var ed8 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty_del'
                                });

                                $(ed.target).textbox('setValue', rows.number);
                                $(ed2.target).textbox('setValue', rows.name);
                                $(ed3.target).textbox('setValue', rows.uom);
                                $(ed4.target).combogrid({
                                    url: '<?= base_url('planning/delivery_orders/readSalesOrders/'); ?>' + btoa(customer_id) + "/" + window.btoa(rows.id) + "/" + btoa(delivery_date),
                                    required: true,
                                    panelWidth: 400,
                                    idField: 'sales_order_no',
                                    textField: 'sales_order_no',
                                    mode: 'remote',
                                    fitColumns: true,
                                    prompt: 'Choose Sales Order No',
                                    columns: [
                                        [{
                                            field: 'sales_order_no',
                                            title: 'Sales Order No',
                                            width: 150
                                        }, {
                                            field: 'customer_order_no',
                                            title: 'Customer Order No',
                                            width: 150
                                        }, {
                                            field: 'qty_del',
                                            title: 'Qty',
                                            width: 80
                                        }]
                                    ],
                                    onSelect: function(val, sales_order) {
                                        $(ed5.target).numberbox('setValue', sales_order.qty_so);
                                        $(ed6.target).numberbox('setValue', sales_order.qty_remain);
                                        $(ed7.target).numberbox('setValue', sales_order.qty_do);
                                        $(ed8.target).numberbox('setValue', sales_order.qty_del);
                                    }
                                });
                            }
                        }
                    }
                }, {
                    field: 'item_fg_number',
                    width: 150,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'item_fg_name',
                    width: 150,
                    halign: 'center',
                    title: "Product Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'sales_order_no',
                    width: 150,
                    halign: 'center',
                    title: "Sales Order No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'uom',
                    width: 80,
                    halign: 'center',
                    title: "UoM",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty_so',
                    width: 80,
                    halign: 'center',
                    title: "Qty SO",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty_remain',
                    width: 80,
                    halign: 'center',
                    title: "Remain SO",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty_do',
                    width: 80,
                    halign: 'center',
                    title: "Total DO",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty_del',
                    width: 80,
                    halign: 'center',
                    title: "Delivery Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty_stock',
                    width: 80,
                    halign: 'center',
                    title: "Stock",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'bal_stock',
                    width: 80,
                    halign: 'center',
                    title: "Balance<br>Stock",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }]
            ],
            onClickCell: onClickCell
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
        var customer_id = $("#customer_id").combobox('getValue');
        if (customer_id != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0',
                    delivery: '0',
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Customer Name first");
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

        var delivery_order_no = $("#delivery_order_no").textbox('getValue');
        var item_fg_id = $(ed.target).textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('planning/delivery_orders/delete') ?>',
            data: {
                delivery_order_no: delivery_order_no,
                item_fg_id: item_fg_id
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                toastr.success(result.message);
            },
            complete: function(data) {
                $('#dg').datagrid('reload');
            }
        });

        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#delivery_order_date").datebox('disable');
            $("#delivery_date").datebox('disable');
            $("#customer_id").combobox('disable');

            addTable(row.customer_id, '<?= base_url('planning/delivery_orders/datatableUpdates?delivery_order_no=') ?>' + window.btoa(row.delivery_order_no));
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
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
                            method: 'post',
                            url: '<?= base_url('planning/delivery_orders/delete') ?>',
                            data: {
                                delivery_order_no: row.delivery_order_no
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
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
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_delivery_order_no = $("#filter_delivery_order_no").combobox('getValue');
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_customer_order_no = $("#filter_customer_order_no").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_delivery_order_no=" + window.btoa(filter_delivery_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_status=" + window.btoa(filter_status);

        $('#dg').datagrid({
            url: '<?= base_url('planning/delivery_orders/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/delivery_orders/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_delivery_order_no = $("#filter_delivery_order_no").combobox('getValue');
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_customer_order_no = $("#filter_customer_order_no").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_delivery_order_no=" + window.btoa(filter_delivery_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('planning/delivery_orders/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('planning/delivery_orders/datatables') ?>',
            pagination: true,
            rownumbers: true,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.delivery_order_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('planning/delivery_orders/datatableDetails?delivery_order_no=') ?>' + window.btoa(row.delivery_order_no),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'item_fg_id',
                            title: 'Product ID',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_number',
                            title: 'Product No.',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'sales_order_no',
                            title: 'Sales Order No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'customer_order_no',
                            title: 'Customer Order No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'qty_so',
                            title: 'SO Qty',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'qty_remain',
                            title: 'Remain Qty',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'qty_do',
                            title: 'Total DO',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'qty_del',
                            title: 'Delivery Qty',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'stock',
                            title: 'Stock',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'stock_bal',
                            title: 'Balance<br>Stock',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
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
                    var customer_id = $("#customer_id").combobox('getValue');
                    var delivery_order_date = $("#delivery_order_date").datebox('getValue');
                    var delivery_date = $("#delivery_date").datebox('getValue');
                    var delivery_order_no = $("#delivery_order_no").textbox('getValue');
                    var trans_type = $("#trans_type").combobox('getValue');
                    var remarks = $("#remarks").textbox('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    if (customer_id != "" && trans_type != "" && delivery_order_date != "" && delivery_date != "") {
                        for (let i = 0; i < totalrows; i++) {
                            if (rows[i].item_fg_id) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('planning/delivery_orders/create') ?>',
                                    data: {
                                        customer_id: customer_id,
                                        delivery_order_date: delivery_order_date,
                                        delivery_order_no: delivery_order_no,
                                        delivery_date: delivery_date,
                                        trans_type: trans_type,
                                        remarks: remarks,
                                        item_fg_id: rows[i].item_fg_id,
                                        sales_order_no: rows[i].sales_order_no,
                                        uom: rows[i].uom,
                                        qty_so: rows[i].qty_so,
                                        qty_remain: rows[i].qty_remain,
                                        qty_do: rows[i].qty_do,
                                        qty_del: rows[i].qty_del,
                                        stock: rows[i].stock,
                                        stock_bal: rows[i].stock_bal,
                                    },
                                    dataType: "json",
                                    success: function(result) {
                                        if (i == (totalrows - 1)) {
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

                        $('#dg').datagrid('reload');
                        $('#dlg_insert').dialog('close');
                    } else {
                        toastr.error("Please Completed your input");
                    }
                }
            }]
        });
    });

    $('#filter_customer_id').combobox({
        url: '<?= base_url('master/customers/reads'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose All',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onSelect: function(customer) {
            $('#filter_delivery_order_no').combobox({
                url: '<?= base_url('planning/delivery_orders/readDeliveryOrders/'); ?>' + customer.id,
                valueField: 'delivery_order_no',
                textField: 'delivery_order_no',
                prompt: 'Choose All',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
                onSelect: function(deliver_order) {
                    $('#filter_sales_order_no').combobox({
                        url: '<?= base_url('planning/delivery_orders/readSalesOrder/'); ?>' + deliver_order.deliver_order_no,
                        valueField: 'sales_order_no',
                        textField: 'sales_order_no',
                        prompt: 'Choose All',
                        icons: [{
                            iconCls: 'icon-clear',
                            handler: function(e) {
                                $(e.data.target).combobox('clear').combobox('textbox').focus();
                            }
                        }],
                    });

                    $('#filter_customer_order_no').combobox({
                        url: '<?= base_url('planning/delivery_orders/readCustomerOrder/'); ?>' + deliver_order.deliver_order_no,
                        valueField: 'customer_order_no',
                        textField: 'customer_order_no',
                        prompt: 'Choose All',
                        icons: [{
                            iconCls: 'icon-clear',
                            handler: function(e) {
                                $(e.data.target).combobox('clear').combobox('textbox').focus();
                            }
                        }],
                    });
                }
            });
        }
    });

    $('#filter_item_fg').combogrid({
        url: '<?= base_url("master/item_fg/reads") ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Product No",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        columns: [
            [{
                field: 'number',
                title: 'Product No',
                width: 200
            }, {
                field: 'name',
                title: 'Product Name',
                width: 200
            }]
        ],
    });

    $('#customer_id').combobox({
        url: '<?= base_url('master/customers/reads'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Customer Name',
        onSelect: function(customer) {
            addTable(customer.id);
        }
    });

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 0) {
            return 'OPEN';
        } else {
            return 'CLOSE';
        }
    };

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

    function btnPrint(val, row) {
        var print = "print_do('" + row.delivery_order_no + "')"; //mengambil id dari customers kemudian di simpan di function details
        return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
    }

    function print_do(delivery_order_no) {
        window.open("<?= base_url('planning/delivery_orders/print_do/') ?>" + window.btoa(delivery_order_no), "_blank", "width=1200,height=600");
    }
</script>
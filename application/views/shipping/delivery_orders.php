<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:180,halign:'center'">Delivery No</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center',formatter:statusformat,styler:statusStyle">Status</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Delivery Date</th>
            <th rowspan="2" data-options="field:'customer_name',width:300,halign:'center'">Customer</th>
            <th rowspan="2" data-options="field:'so_number',width:200,halign:'center'">Sales Order No</th>
            <th rowspan="2" data-options="field:'customer_po',width:120,halign:'center'">Customer PO</th>
            <th rowspan="2" data-options="field:'trans_type',width:80,halign:'center'">Trans Type</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'description',width:300,halign:'center'">Product Specification</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'note',width:100,halign:'center'">Note</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th rowspan="2" data-options="field:'delivery',width:100,halign:'center',align:'right',formatter:numberformat">Delivery</th>
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

<div id="toolbar" style="height: 220px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 35%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" id="filter_customer" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery No</span>
                <input style="width:60%;" id="filter_delivery_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_do()"><i class="fa fa-print"></i> Delivery Order</a>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" id="append" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" id="removeit" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1200px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery No</span>
                    <input style="width:60%;" name="number" id="number" readonly class="easyui-textbox" data-options="prompt: 'Automatic'" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Date</span>
                    <input style="width:60%;" name="trans_date" id="trans_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" class="easyui-combogrid" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Type</span>
                    <select style="width:60%;" name="trans_type" id="trans_type" class="easyui-combobox" data-options="prompt:'Choose Trans Type'" panelHeight="auto" required>
                        <option value="">Choose Trans Type</option>
                        <option value="SALES">SALES</option>
                        <option value="RETURN">RETURN</option>
                        <option value="SAMPLE">SAMPLE</option>
                    </select>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:20%; display:inline-block; vertical-align: top;">Note</span>
                    <input style="width:70%; height: 100px;" multiline="true" name="note" id="note" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Delivery Order List" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('shipping/delivery_orders/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);

        $("#append").show();
        $("#removeit").show();
        $("#customer_id").combogrid('enable');
        $("#trans_type").combobox('enable');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if (row.state == "closed") {
                if (row.status == "0") {
                    $('#dlg_insert').dialog('open');
                    $('#frm_insert').form('load', row);

                    $("#append").hide();
                    $("#removeit").hide();
                    $("#customer_id").combogrid('disable');
                    $("#trans_type").combobox('disable');

                    addTable(row.customer_id, row.trans_type,'<?= base_url('shipping/delivery_orders/datatable_updates?number=') ?>' + window.btoa(row.number));
                } else {
                    toastr.error("You cannot update this data, because status Delivery Order is CLOSE");
                }
            } else {
                toastr.error("Please Select Header of Delivery Order <br>" + row.number);
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //NOMOR AUTOMATIC
    function number(trans_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('shipping/delivery_orders/number/') ?>" + window.btoa(trans_date),
            dataType: "html",
            success: function(result) {
                $("#number").textbox('setValue', result);
            }
        });
    }

    //INSERT ADD ROW
    function addTable(customer, trans_type, link = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            singleSelect: true,
            url: link,
            columns: [
                [{
                    field: 'id',
                    hidden: true,
                    width: 100,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'so_number',
                    width: 200,
                    halign: 'center',
                    title: "Sales Order No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('shipping/delivery_orders/readSalesOrders?customer_id=') ?>' + customer + "&trans_type=" + trans_type,
                            panelWidth: 350,
                            idField: 'number',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Sales Order No',
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'SO No',
                                    width: 180
                                }, {
                                    field: 'trans_date',
                                    title: 'SO Date',
                                    width: 100
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_number'
                                });

                                $(ed.target).combogrid({
                                    url: '<?= base_url('shipping/delivery_orders/readSalesOrderItems?number=') ?>' + rows.number + "&trans_type=" + trans_type + "&customer_id=" + customer,
                                    required: true,
                                    panelWidth: 520,
                                    idField: 'item_number',
                                    textField: 'item_number',
                                    mode: 'remote',
                                    fitColumns: true,
                                    prompt: 'Choose Product No',
                                    columns: [
                                        [{
                                            field: 'item_number',
                                            title: 'Product No',
                                            width: 150
                                        }, {
                                            field: 'item_name',
                                            title: 'Product Name',
                                            width: 200
                                        }, {
                                            field: 'stock',
                                            title: 'Stock',
                                            width: 100
                                        }]
                                    ],
                                    onSelect: function(value, item) {
                                        $.ajax({
                                            type: "post",
                                            url: '<?= base_url('shipping/delivery_orders/readDeliveryTotal') ?>',
                                            data: 'so_number='+rows.number+'&item_id='+item.item_id, 
                                            dataType: "json",
                                            success: function (total) {
                                                var total_do = total[0]['total_do'];
                                                var dg2 = $('#dg2');
                                                var row2 = dg2.datagrid('getSelected');
                                                var rowIndex2 = dg2.datagrid('getRowIndex', row2);
                                                var ed = dg2.datagrid('getEditor', {
                                                    index: rowIndex2,
                                                    field: 'item_id'
                                                });
                                                var ed2 = dg2.datagrid('getEditor', {
                                                    index: rowIndex2,
                                                    field: 'item_name'
                                                });
                                                var ed3 = dg2.datagrid('getEditor', {
                                                    index: rowIndex2,
                                                    field: 'uom'
                                                });
                                                var ed4 = dg2.datagrid('getEditor', {
                                                    index: rowIndex2,
                                                    field: 'qty'
                                                });
                                                var ed5 = dg2.datagrid('getEditor', {
                                                    index: rowIndex2,
                                                    field: 'price'
                                                });
                                                var ed6 = dg2.datagrid('getEditor', {
                                                    index: rowIndex2,
                                                    field: 'stock'
                                                });
                                                var ed7 = dg2.datagrid('getEditor', {
                                                    index: rowIndex2,
                                                    field: 'balance'
                                                });
                                                var ed8 = dg2.datagrid('getEditor', {
                                                    index: rowIndex2,
                                                    field: 'status'
                                                });
                                                var ed9 = dg2.datagrid('getEditor', {
                                                    index: rowIndex2,
                                                    field: 'qty_do'
                                                });
                                                var ed10 = dg2.datagrid('getEditor', {
                                                    index: rowIndex2,
                                                    field: 'balance_do'
                                                });

                                                if (trans_type == "SALES") {
                                                    var transQty = item.qty;
                                                } else {
                                                    var transQty = "0.00";
                                                }

                                                var item_id = $(ed.target).textbox('setValue', item.item_id);
                                                var item_name = $(ed2.target).textbox('setValue', item.item_name);
                                                var uom = $(ed3.target).textbox('setValue', item.uom);
                                                var qty = $(ed4.target).numberbox('setValue', transQty);
                                                var price = $(ed5.target).numberbox('setValue', item.price);
                                                var stock = $(ed6.target).numberbox('setValue', item.stock);
                                                var status = $(ed8.target).textbox('setValue', item.status);
                                                var qty_do = $(ed9.target).numberbox('setValue', total_do);
                                                var balance_do = $(ed10.target).numberbox('setValue', (parseFloat(transQty) - parseFloat(total_do)));
                                            }
                                        });
                                    }
                                });
                            }
                        }
                    }
                }, {
                    field: 'item_id',
                    hidden: true,
                    width: 100,
                    halign: 'center',
                    title: "Product ID",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'item_number',
                    width: 180,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'combogrid',
                    }
                }, {
                    field: 'item_name',
                    width: 180,
                    halign: 'center',
                    title: "Product Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'status',
                    hidden: true,
                    width: 150,
                    halign: 'center',
                    title: "Status",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
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
                    field: 'qty',
                    width: 100,
                    halign: 'center',
                    title: "Qty SO",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'qty_do',
                    width: 100,
                    halign: 'center',
                    title: "Total DO",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'balance_do',
                    width: 100,
                    halign: 'center',
                    title: "SO Balance",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'delivery',
                    width: 100,
                    halign: 'center',
                    title: "Delivery",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'price',
                    width: 120,
                    hidden: true,
                    halign: 'center',
                    title: "Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            precision: 4
                        }
                    }
                }, {
                    field: 'stock',
                    width: 120,
                    halign: 'center',
                    title: "Stock",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'balance',
                    width: 120,
                    halign: 'center',
                    title: "Balance Stock",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            precision: 2
                        }
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dg2').datagrid('getEditors', rowIndex);

                var balance_do = $(editors[9].target);
                var delivery = $(editors[10].target);
                var stock = $(editors[12].target);
                var balance = $(editors[13].target);

                delivery.add(balance).numberbox({
                    onChange: function() {
                        var f_balance_do = balance_do.numberbox('getValue');
                        var f_delivery = delivery.numberbox('getValue');
                        var f_stock = stock.numberbox('getValue');

                        var total = (parseFloat(f_stock) - parseFloat(f_delivery));
                        if(parseFloat(f_balance_do) >= parseFloat(f_delivery)){
                            if (parseFloat(f_delivery) > parseFloat(f_stock)) {
                                toastr.warning("Delivery cannot > Stock FG");
                                $(delivery).numberbox('clear');
                            } else {
                                $(balance).numberbox('setValue', total);
                            }
                        }else{
                            toastr.warning("Delivery cannot > Balance DO");
                            $(delivery).numberbox('clear');
                        }
                    }
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

    function append() {
        var trans_type = $("#trans_type").combogrid('getValue');
        if (trans_type != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Trans Type first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }
    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('shipping/delivery_orders/delete') ?>',
                            data: {
                                id: row.id,
                                so_number: row.so_number,
                                item_id: row.item_id,
                                customer_id: row.customer_id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
                            },
                            complete: function(data) {
                                $('#dg').treegrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_delivery_no = $("#filter_delivery_no").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_delivery_no=" + filter_delivery_no + "&filter_customer=" + filter_customer;
        $('#dg').treegrid({
            url: '<?= base_url('shipping/delivery_orders/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('shipping/delivery_orders/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_delivery_no = $("#filter_delivery_no").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_delivery_no=" + filter_delivery_no + "&filter_customer=" + filter_customer;
        window.location.assign('<?= base_url('shipping/delivery_orders/print/excel') ?>' + url);
    }

    function print_do() {
        var delivery_no = $("#filter_delivery_no").combobox('getValue');
        if (delivery_no == "") {
            toastr.warning("Please select Delivery No!", "Information");
        } else {
            window.open("<?= base_url('shipping/delivery_orders/print_delivery/') ?>" + window.btoa(delivery_no), "_blank");
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').treegrid({
            url: '<?= base_url('shipping/delivery_orders/datatables') ?>',
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'number',
            singleSelect: false,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
            // rowStyler: function(row) {
            //     if (row.state != "closed") {
            //         return 'background-color:#CFE6FF;font-weight:bold;';
            //     }
            // },
        });

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var number = $("#number").textbox('getValue');
                    var trans_date = $("#trans_date").datebox('getValue');
                    var customer_id = $("#customer_id").combogrid('getValue');
                    var trans_type = $("#trans_type").combobox('getValue');
                    var note = $("#note").textbox('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;

                    endEditing();
                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_id) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('shipping/delivery_orders/create') ?>',
                                data: {
                                    number: number,
                                    trans_date: trans_date,
                                    customer_id: customer_id,
                                    trans_type: trans_type,
                                    note: note,
                                    id: rows[i].id,
                                    item_id: rows[i].item_id,
                                    so_number: rows[i].so_number,
                                    qty: rows[i].qty,
                                    qty_do: rows[i].qty_do,
                                    balance_do: rows[i].balance_do,
                                    delivery: rows[i].delivery,
                                    stock: rows[i].stock,
                                    balance: rows[i].balance
                                },
                                dataType: "json",
                                success: function(result) {
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
                            });
                        }
                    }

                    $('#dg').treegrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });

        $("#filter_customer").combobox({
            url: '<?= base_url('master/customers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Customer",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(customer) {
                $("#filter_delivery_no").combobox({
                    url: '<?= base_url('shipping/delivery_orders/readDeliveryno/') ?>' + customer.id,
                    valueField: 'number',
                    textField: 'number',
                    prompt: "Select Delivery No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $("#trans_date").datebox({
            onChange: function(date) {
                var trans_date = $("#trans_date").datebox('getValue');
                number(trans_date);
            }
        });

        //GET CUSTOMER
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
                $('#trans_type').combobox({
                    onChange: function(trans) {
                        addTable(row.id, trans)
                    }
                });
            }
        });

        number("<?= date("Y-m-d") ?>");
    });

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

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSE</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        }
    }
</script>
<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
            <th rowspan="2" data-options="field:'sales_order_no',width:150,halign:'center'">Sales Order No</th>
            <th rowspan="2" data-options="field:'customer_order_no',width:150,halign:'center'">Customer Order No</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'sales_order_date',width:150,halign:'center'">Sales Order Date</th>
            <th rowspan="2" data-options="field:'delivery_date',width:150,halign:'center'">Delivery Date</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'total_sub',width:100,halign:'center',align:'right',formatter: numberFormat">Sub Total</th>
            <th rowspan="2" data-options="field:'total_tax',width:100,halign:'center',align:'right',formatter: numberFormat">Taxes</th>
            <th rowspan="2" data-options="field:'total_pph',width:100,halign:'center',align:'right',formatter: numberFormat">PPh</th>
            <th rowspan="2" data-options="field:'total_grand',width:100,halign:'center',align:'right',formatter: numberFormat">Grand Total</th>
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'attachment',width:150,halign:'center'">Attachment</th>
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
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Order Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" id="filter_customer_id" class="easyui-combobox">
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1100px; height: 700px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Order No</span>
                    <input style="width:60%;" name="customer_order_no" id="customer_order_no" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Order Date</span>
                    <input style="width:40%;" name="sales_order_date" id="sales_order_date" required="" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Order No</span>
                    <input style="width:60%;" name="sales_order_no" id="sales_order_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" name="division" id="division" required="" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Date</span>
                    <input style="width:40%;" name="delivery_date" id="delivery_date" required data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Shipping Address</span>
                    <input style="width:60%;" name="customer_address_id" id="customer_address_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attention To</span>
                    <input style="width:60%;" name="attention_to" id="attention_to" disabled class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Taxes</span>
                    <input style="width:60%;" name="taxes" id="taxes" disabled class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="attachment" id="attachment" class="easyui-filebox">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Sales Order Lists" toolbar="#toolbar2"></table>
        <div style="width: 30%; float: right; margin-top: 10px;">
            <a style="width: 100%;" class="easyui-linkbutton c2" onclick="calculate()">Calculate</a>
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; margin-top: 10px; border-radius:4px;">
                <div style="width: 100%; float: left;">
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">SUB TOTAL</b>
                        <input style="width:60%;" id="total_sub" name="total_sub" readonly class="easyui-numberbox" data-options="groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">TAX (11%)</b>
                        <input style="width:60%;" id="total_tax" name="total_tax" readonly class="easyui-numberbox" data-options="groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:20%; display:inline-block;">PPH</b>
                        <input style="width:15%;" id="pph" name="pph" class="easyui-numberbox">
                        <input style="width:60%;" id="total_pph" name="total_pph" readonly class="easyui-numberbox" data-options="groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">GRAND TOTAL</b>
                        <input style="width:60%;" id="total_grand" name="total_grand" readonly required class="easyui-numberbox" data-options="groupSeparator:','">
                    </div>
                </div>
            </fieldset>
        </div>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/sales_orders/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('planning/sales_orders/create') ?>';
        $('#frm_insert').form('clear');

        $("#customer_id").combobox('enable');
        $("#sales_order_no").textbox('enable');
        $("#sales_order_date").datebox('enable');
        $("#customer_address_id").datebox('enable');
        $("#pph").numberbox('setValue', 0);

        $("#sales_order_date").datebox({
            onChange: function(sales_order_date) {
                var customer_id = $("#customer_id").combobox('getValue');
                if (customer_id == "") {
                    toastr.error("Please Choose Customer Name");
                    $("#sales_order_date").datebox('clear');
                } else {
                    number(customer_id, sales_order_date);
                    addTable(customer_id);
                }
            }
        });

        $('#customer_id').combobox({
            url: '<?= base_url('master/customers/reads/'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Customer Name',
            onSelect: function(customer) {
                var sales_order_date = $("#sales_order_date").datebox('getValue');
                $("#taxes").numberbox('setValue', customer.taxes);

                if (sales_order_date != "") {
                    number(customer.id, sales_order_date);
                }

                $('#customer_address_id').combobox({
                    url: '<?= base_url('master/customers/readAddress/'); ?>' + customer.id,
                    valueField: 'id',
                    textField: 'address',
                    panelHeight: 'panelHeight',
                    prompt: 'Choose Shipping Address',
                    onSelect: function(address) {
                        $("#attention_to").textbox('setValue', address.contact_person);
                    }
                });
            }
        });
    }

    function number(customer_id, sales_order_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/sales_orders/number/') ?>" + customer_id + '/' + window.btoa(sales_order_date),
            dataType: "html",
            success: function(result) {
                $("#sales_order_no").textbox('setValue', result);
            }
        });
    }

    function calculate() {
        var rows = $('#dg2').datagrid('getRows');
        var taxes = $("#taxes").numberbox('getValue');
        var pph = $("#pph").numberbox('getValue');

        endEditing();
        var totalrows = rows.length;

        if (totalrows > 0) {
            var total_sub = 0;
            for (let i = 0; i < totalrows; i++) {
                total_sub += parseFloat(rows[i].total);
            }

            $("#total_sub").numberbox('setValue', total_sub);
            var total_tax = parseFloat(total_sub * (taxes / 100));
            $("#total_tax").numberbox('setValue', total_tax);
            var total_pph = (((parseFloat(total_sub) + parseInt(total_tax)) * parseInt(pph)) / 100);
            $("#total_pph").numberbox('setValue', total_pph);
            var total_grand = (parseFloat(total_sub) + parseFloat(total_tax) - parseFloat(total_pph));
            $("#total_grand").numberbox('setValue', (total_grand));
        } else {
            toastr.error("Data in Sales order List Empty");
        }
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
                            url: '<?= base_url('planning/sales_orders/readItemFg/'); ?>' + customer_id,
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
                                    field: 'price'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'currency'
                                });

                                $(ed.target).textbox('setValue', rows.number);
                                $(ed2.target).textbox('setValue', rows.name);
                                $(ed3.target).textbox('setValue', rows.uom);
                                $(ed4.target).numberbox('setValue', rows.price);
                                $(ed5.target).textbox('setValue', rows.currency);
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
                    width: 80,
                    halign: 'center',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            onChange: function(qty) {
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

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'outstanding'
                                });

                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'delivery'
                                });

                                var delivery = $(ed4.target).numberbox('getValue');
                                var price = $(ed2.target).numberbox('getValue');

                                var total = (parseInt(qty) * parseInt(price));
                                var outstanding = (parseInt(qty) - parseInt(delivery));

                                $(ed3.target).numberbox('setValue', outstanding);
                                $(ed.target).numberbox('setValue', total);
                            }
                        }
                    }
                }, {
                    field: 'delivery',
                    width: 80,
                    halign: 'center',
                    title: "Delivery",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'outstanding',
                    width: 80,
                    halign: 'center',
                    title: "OS SO",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'currency',
                    width: 80,
                    halign: 'center',
                    title: "Currency",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'price',
                    width: 100,
                    align: 'center',
                    title: "Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'total',
                    width: 100,
                    halign: 'center',
                    title: "Total Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, ]
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

        var sales_order_no = $("#sales_order_no").textbox('getValue');
        var item_fg_id = $(ed.target).textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('planning/sales_orders/delete') ?>',
            data: {
                sales_order_no: sales_order_no,
                item_fg_id: item_fg_id
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                toastr.success(result.message);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                toastr.error(jqXHR.statusText);
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
            $("#customer_id").combobox('disable');
            $("#sales_order_no").textbox('disable');
            $("#sales_order_date").datebox('disable');
            $("#customer_address_id").datebox('disable');

            addTable(row.customer_id, '<?= base_url('planning/sales_orders/datatableUpdates?sales_order_no=') ?>' + window.btoa(row.sales_order_no));
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
                            url: '<?= base_url('planning/sales_orders/delete') ?>',
                            data: {
                                sales_order_no: row.sales_order_no
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
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_status=" + window.btoa(filter_status);

        $('#dg').datagrid({
            url: '<?= base_url('planning/sales_orders/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/sales_orders/print') ?>' + url);
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
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('planning/sales_orders/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('planning/sales_orders/datatables') ?>',
            pagination: true,
            rownumbers: true,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.sales_order_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('planning/sales_orders/datatableDetails?sales_order_no=') ?>' + window.btoa(row.sales_order_no),
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
                            field: 'uom',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'qty',
                            title: 'Qty',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'delivery',
                            title: 'Delivery',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'outstanding',
                            title: 'OS SO',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'total',
                            title: 'Total',
                            halign: 'center',
                            align: 'right',
                            width: 100,
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
                    var customer_order_no = $("#customer_order_no").textbox('getValue');
                    var sales_order_date = $("#sales_order_date").datebox('getValue');
                    var sales_order_no = $("#sales_order_no").textbox('getValue');
                    var division = $("#division").combobox('getValue');
                    var delivery_date = $("#delivery_date").datebox('getValue');
                    var customer_address_id = $("#customer_address_id").combobox('getValue');
                    var remarks = $("#remarks").textbox('getValue');
                    var pph = $("#pph").numberbox('getValue');
                    var taxes = $("#taxes").numberbox('getValue');
                    var total_sub = $("#total_sub").numberbox('getValue');
                    var total_tax = $("#total_tax").numberbox('getValue');
                    var total_pph = $("#total_pph").numberbox('getValue');
                    var total_grand = $("#total_grand").numberbox('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    if (customer_address_id != "" && total_grand != "") {
                        for (let i = 0; i < totalrows; i++) {
                            if (rows[i].item_fg_id) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('planning/sales_orders/create') ?>',
                                    data: {
                                        customer_id: customer_id,
                                        customer_order_no: customer_order_no,
                                        sales_order_date: sales_order_date,
                                        sales_order_no: sales_order_no,
                                        division: division,
                                        delivery_date: delivery_date,
                                        customer_address_id: customer_address_id,
                                        remarks: remarks,
                                        total_sub: total_sub,
                                        total_tax: total_tax,
                                        pph: pph,
                                        taxes: taxes,
                                        total_pph: total_pph,
                                        total_grand: total_grand,
                                        item_fg_id: rows[i].item_fg_id,
                                        uom: rows[i].uom,
                                        qty: rows[i].qty,
                                        delivery: rows[i].delivery,
                                        outstanding: rows[i].outstanding,
                                        currency: rows[i].currency,
                                        price: rows[i].price,
                                        total: rows[i].total
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
            $('#filter_sales_order_no').combobox({
                url: '<?= base_url('planning/sales_orders/readSalesOrder/'); ?>' + customer.id,
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
                url: '<?= base_url('planning/sales_orders/readCustomerOrder/'); ?>' + customer.id,
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

    $('#division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
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
</script>
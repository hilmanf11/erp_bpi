<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:200,halign:'center'">SO No</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center',formatter:statusformat,styler:statusStyle">Status WO</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">SO Date</th>
            <th rowspan="2" data-options="field:'customer_number',width:100,align:'center'">Customer No</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'customer_type',width:120,halign:'center'">Customer Type</th>
            <th rowspan="2" data-options="field:'customer_po',width:150,halign:'center'">Customer PO</th>
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
<div id="toolbar" style="height: 260px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" name="filter_customers" id="filter_customers" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="filter_items" id="filter_items" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type</span>
                <select style="width:60%;" id="filter_type" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="EXPORT">EXPORT</option>
                    <option value="LOCAL">LOCAL</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Order No</span>
                <input style="width:60%;" id="filter_number" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="helps()"><i class="fa fa-info-circle"></i> Help</a>
    </div>
</div>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- DIALOG SAVE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1200px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Sales Order Date</span>
                        <input style="width:60%;" id="trans_date" name="trans_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Customer</span>
                        <input style="width:60%;" required="" id="customer_id" name="customer_id" required="" class="easyui-combogrid">
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Customer PO</span>
                        <input style="width:60%;" required="" id="customer_po" name="customer_po" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Sales Order No</span>
                        <input style="width:60%;" readonly required="" id="number" name="number" class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
        </div>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Sales Order Product" toolbar="#toolbar2" data-options="singleSelect: true">
        </table>
    </form>
</div>

<!-- DIALOG HELP -->
<div id="dlg_help" class="easyui-dialog" title="Help about this Module" data-options="closed: true,modal:true" style="width: 600px; padding:10px; top: 20px;">
    <table style="width: 100%;">
        <tr>
            <td colspan="2" style="border: 2px solid black; text-align: center; font-weight: bold;">STATUS WO</td>
        </tr>
        <tr>
            <td style="background-color: #C8FFCC; padding: 5px; color: green; text-align: center; font-weight: bold;">OPEN</td>
            <td style="padding: 5px;">Data just created from <b>sales order</b></td>
        </tr>
        <tr>
            <td style="background-color: #FFB671; padding: 5px; color: black; text-align: center; font-weight: bold;">PRODUCTION</td>
            <td style="padding: 5px;">The data has been created in the <b>production schedule</b> module</td>
        </tr>
        <tr>
            <td style="background-color: #9EADFF; padding: 5px; color: blue; text-align: center; font-weight: bold;">DELIVERY</td>
            <td style="padding: 5px;">The data has been created in the <b>delivery orders</b> module</td>
        </tr>
        <tr>
            <td colspan="2" style="border: 2px solid black; text-align: center; font-weight: bold;">RELATION</td>
        </tr>
        <tr>
            <td style="padding: 5px; text-align: center; font-weight: bold;"> Customer</td>
            <td style="padding: 5px;">The data GET in the <b>master data/customers</b> module</td>
        </tr>
        <tr>
            <td style="padding: 5px; text-align: center; font-weight: bold;"> Product No</td>
            <td style="padding: 5px;">The data GET in the <b>master data/customer items</b> module</td>
        </tr>
        <tr>
            <td colspan="2" style="border: 2px solid black; text-align: center; font-weight: bold;">VALIDATION</td>
        </tr>
        <tr>
            <td style="padding: 5px; text-align: center; font-weight: bold;"> Error</td>
            <td style="padding: 5px;">If the customer, product no and sales order no are the same</td>
        </tr>
        <tr>
            <td style="padding: 5px; text-align: center; font-weight: bold;"> Error</td>
            <td style="padding: 5px;">If qty = 0</td>
        </tr>
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/sales_orders/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //HELP
    function helps(){
        $('#dlg_help').dialog('open');
    }

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#customer_id').combogrid('enable');
        $('#trans_date').datebox('enable');
        $('#frm_insert').form('clear');
        $('#trans_date').datebox('setValue', "<?= date("Y-m-d") ?>");
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if(row.status != 2){
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                $('#customer_id').combogrid('disable');
                $('#trans_date').datebox('disable');

                addTable(row.customer_id, '<?= base_url('planning/sales_orders/datatable_updates?number=') ?>' + window.btoa(row.number));
            }else{
                toastr.error("You cannot edit this sales order because status has been delivery!");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //NOMOR AUTOMATIC
    function number(customer, trans_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/sales_orders/number/') ?>" + customer + '/' + window.btoa(trans_date),
            dataType: "html",
            success: function(result) {
                $("#number").textbox('setValue', result);
            }
        });
    }

    //INSERT ADD ROW
    function addTable(customer, link = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'item_number',
                    width: 250,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('master/customer_items/readItems?customer_id=') ?>' + customer,
                            required: true,
                            panelWidth: 400,
                            idField: 'number',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product No',
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Product No',
                                    width: 100
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
                                    field: 'item_id'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'price'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'currency'
                                });
                                var item_id = $(ed.target).textbox('setValue', rows.id);
                                var price = $(ed2.target).textbox('setValue', rows.price);
                                var currency = $(ed3.target).textbox('setValue', rows.currency);
                            }
                        }
                    }
                }, {
                    field: 'item_id',
                    width: 150,
                    hidden: true,
                    halign: 'center',
                    title: "ID",
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
                            required: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'price',
                    width: 100,
                    halign: 'center',
                    title: "Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 4,
                            readonly: true
                        }
                    }
                }, {
                    field: 'discount',
                    width: 80,
                    halign: 'center',
                    title: "Disc %",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'total',
                    width: 100,
                    halign: 'center',
                    title: "Total Price",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true,
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
                    field: 'vat',
                    width: 100,
                    halign: 'center',
                    title: "Tax",
                    editor: {
                        type: 'combobox',
                        options: {
                            data: [{
                                "number": "VAT",
                                "name": "VAT"
                            }, {
                                "number": "NON VAT",
                                "name": "NON VAT"
                            }],
                            valueField: 'number',
                            textField: 'name',
                            panelHeight: 'auto',
                            required: true
                        }
                    }
                }, {
                    field: 'delivery',
                    width: 150,
                    halign: 'center',
                    title: "Delivery",
                    editor: {
                        type: 'datebox',
                        options: {
                            formatter: myformatter,
                            parser: myparser,
                            editable: false,
                            required: true
                        }
                    }
                },{
                    field: 'action',
                    title: 'Change',
                    width: 80,
                    align: 'center',
                    formatter: function(value, row, index) {
                        if(link != ""){
                            var s = '<a href="javascript:void(0)" class="btn btn-warning btn-sm" style="pointer-events:auto; opacity:1; width:100%;" onclick="changePrice(this)"><i class="fa fa-edit"></i></a> ';
                            return s;
                        }
                    }
                }, ]
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
                var trans_date = $("#trans_date").datebox('getValue');
                var item_id = $(editors[0].target);
                var qty = $(editors[2].target);
                var price = $(editors[3].target);
                var discount = $(editors[4].target);
                var total = $(editors[5].target);
                var delivery_date = $(editors[8].target);

                qty.add(total).textbox({
                    onChange: function() {
                        var f_qty = qty.numberbox('getValue');
                        var f_discount = discount.numberbox('getValue');
                        var f_price = price.numberbox('getValue');
                        total.textbox('setValue', ((parseFloat(f_price) * parseFloat(f_qty)) - (parseFloat(f_price) * parseFloat(f_qty)) * parseFloat(f_discount / 100)).toFixed(4));
                    }
                });

                discount.add(total).textbox({
                    onChange: function() {
                        var f_qty = qty.numberbox('getValue');
                        var f_discount = discount.numberbox('getValue');
                        var f_price = price.numberbox('getValue');
                        total.textbox('setValue', ((parseFloat(f_price) * parseFloat(f_qty)) - (parseFloat(f_price) * parseFloat(f_qty)) * parseFloat(f_discount / 100)).toFixed(4));
                    }
                });

                if(link == ""){
                    delivery_date.add(delivery_date).datebox({
                        onChange: function() {
                            var f_delivery_date = delivery_date.datebox('getValue');
                            if (f_delivery_date < trans_date) {
                                delivery_date.datebox('clear');
                                toastr.warning("Sales Order Date > Delivery Date");
                            }
                        }
                    });
                }
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
            return
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function changePrice(target) {
        var editors = $('#dg2').datagrid('getEditors', getRowIndex(target));
        var rows = $('#dg2').datagrid('getRows');

        var item_number = rows[getRowIndex(target)].item_number;
        var customer_id = $("#customer_id").combogrid('getValue');

        $.ajax({
            type: "post",
            url: "<?= base_url('master/customer_items/readPrice') ?>",
            data: "customer_id=" + customer_id + "&item_number=" + item_number,
            dataType: "json",
            success: function(json) {
                toastr.success("Price Changed!");
                $(editors[3].target).numberbox('setValue', json.price);

                var qty = $(editors[2].target);
                var discount = $(editors[4].target);
                var total = $(editors[5].target);

                var f_qty = qty.numberbox('getValue');
                var f_discount = discount.numberbox('getValue');
                var f_price = json.price;
                total.textbox('setValue', ((parseFloat(f_price) * parseFloat(f_qty)) - (parseFloat(f_price) * parseFloat(f_qty)) * parseFloat(f_discount / 100)).toFixed(4));
                
            }
        });
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
                                number: row.number
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
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
        var filter_customers = $("#filter_customers").combogrid('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_number = $("#filter_number").combogrid('getValue');
        var url = "?filter_customers=" + window.btoa(filter_customers) +
            "&filter_items=" + window.btoa(filter_items) +
            "&filter_number=" + window.btoa(filter_number) +
            "&filter_type=" + window.btoa(filter_type);
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
    //EXPORT TO EXCEL
    function excel() {
        var filter_customers = $("#filter_customers").combogrid('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_number = $("#filter_number").combogrid('getValue');
        var url = "?filter_customers=" + window.btoa(filter_customers) +
            "&filter_items=" + window.btoa(filter_items) +
            "&filter_number=" + window.btoa(filter_number) +
            "&filter_type=" + window.btoa(filter_type);
        window.location.assign('<?= base_url('planning/sales_orders/print/excel') ?>' + url);
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
        $("#trans_date").datebox({
            onSelect: function(date) {
                $("#customer_id").combogrid('clear');
            }
        });
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('planning/sales_orders/datatables') ?>',
            pagination: true,
            rownumbers: true,
            height: '810px',
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.number + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_customers = $("#filter_customers").combogrid('getValue');
                var filter_items = $("#filter_items").combogrid('getValue');
                var filter_type = $("#filter_type").combobox('getValue');
                var url = "?filter_customers=" + window.btoa(filter_customers) +
                    "&filter_items=" + window.btoa(filter_items) +
                    "&filter_type=" + window.btoa(filter_type);
                ddv.datagrid({
                    url: '<?= base_url('planning/sales_orders/datatables/details?number=') ?>' + window.btoa(row.number) +
                        "&filter_customers=" + window.btoa(filter_customers) +
                        "&filter_items=" + window.btoa(filter_items) +
                        "&filter_type=" + window.btoa(filter_type),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'item_number',
                            title: 'Product No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'qty',
                            title: 'Qty',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'price',
                            title: 'Price',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
                        }, {
                            field: 'discount',
                            title: 'Disc %',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'total',
                            title: 'Total',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            width: 100,
                            align: 'center'
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            width: 100,
                            align: 'center'
                        }, {
                            field: 'delivery',
                            title: 'Delivery',
                            width: 100,
                            align: 'center'
                        }, {
                            field: 'status',
                            title: 'Status',
                            width: 100,
                            align: 'center',
                            formatter: statusformat,
                            styler: statusStyle
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
                    var customer_id = $("#customer_id").combogrid('getValue');
                    var customer_po = $("#customer_po").textbox('getValue');
                    var number = $("#number").textbox('getValue');
                    var trans_date = $("#trans_date").datebox('getValue');
                    if (customer_id == "" || customer_po == "" || totalrows <= 0) {
                        toastr.error("please complete your input data");
                    } else {
                        var rows = $('#dg2').datagrid('getRows');
                        var totalrows = rows.length;
                        endEditing();
                        for (let i = 0; i < totalrows; i++) {
                            $('#dg2').datagrid('endEdit', i);
                            if (rows[i].item_id) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('planning/sales_orders/create') ?>',
                                    data: {
                                        customer_id: customer_id,
                                        customer_po: customer_po,
                                        number: number,
                                        trans_date: trans_date,
                                        item_id: rows[i].item_id,
                                        qty: rows[i].qty,
                                        price: rows[i].price,
                                        discount: rows[i].discount,
                                        total: rows[i].total,
                                        vat: rows[i].vat,
                                        delivery: rows[i].delivery
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
                    }
                }
            }]
        });

        //GET SALES ORDER NUMBER
        $("#filter_number").combogrid({
            url: '<?= base_url('planning/sales_orders/reads') ?>',
            panelWidth: 350,
            idField: 'number',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Sales Order No',
                    width: 150
                }, {
                    field: 'trans_date',
                    title: 'Sales Order Date',
                    width: 100
                }, ]
            ],
        });
        //GET CUSTOMER
        $('#filter_customers').combogrid({
            url: '<?= base_url('master/customers/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Customer",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
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
                //GET PRODUCT NO BY CUSTOMER
                $('#filter_items').combogrid({
                    url: '<?= base_url('master/customer_items/readItems?customer_id=') ?>' + row.id,
                    panelWidth: 420,
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Product",
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
                            width: 100
                        }, {
                            field: 'name',
                            title: 'Product Name',
                            width: 200
                        }, ]
                    ]
                });
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
                var trans_date = $("#trans_date").datebox('getValue');
                addTable(row.id);
                number(row.number, trans_date);
            }
        });
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
            const formatter = new Intl.NumberFormat(format, {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: digits
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:black;'>PRODUCTION</b>";
        } else if (value == 2) {
            return "<b style='color:blue;'>DELIVERY</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFB671;';
        } else if (value == 2) {
            return 'background-color:#9EADFF;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }
</script>
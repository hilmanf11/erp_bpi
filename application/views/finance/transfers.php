<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:200,halign:'center'">Document No</th>
            <th rowspan="2" data-options="field:'trans_date',width:120,align:'center'">Transfer Date</th>
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
<div id="toolbar" style="height: 230px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Transfer Date</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Document No</span>
                <input style="width:60%;" name="filter_document" id="filter_document" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Asset Name</span>
                <input style="width:60%;" id="filter_asset" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- DIALOG SAVE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Document No</span>
                        <input style="width:60%;" readonly required="" id="number" name="number" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Transfer Date</span>
                        <input style="width:60%;" id="transfer_date" name="transfer_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                </div>
            </fieldset>
        </div>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Transfer Asset" toolbar="#toolbar2" data-options="singleSelect: true">
        </table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('finance/transfers/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');
        $('#transfer_date').datebox('setValue', "<?= date("Y-m-d") ?>");
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            addTable('<?= base_url('finance/transfers/datatable_updates?number=') ?>' + window.btoa(row.number));
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //NOMOR AUTOMATIC
    function number(transfer_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/transfers/number/') ?>" + window.btoa(transfer_date),
            dataType: "html",
            success: function(result) {
                $("#number").textbox('setValue', result);
            }
        });
    }

    //INSERT ADD ROW
    function addTable(link = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'asset_name',
                    width: 250,
                    halign: 'center',
                    title: "Asset Name",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('finance/fixeds/reads') ?>',
                            required: true,
                            panelWidth: 400,
                            idField: 'name',
                            textField: 'name',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Asset Name',
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Asset Code',
                                    width: 100
                                }, {
                                    field: 'name',
                                    title: 'Asset Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'asset_id'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'asset_type'
                                });

                                $(ed.target).textbox('setValue', rows.id);
                                $(ed2.target).textbox('setValue', rows.type);
                            }
                        }
                    }
                }, {
                    field: 'asset_id',
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
                    field: 'asset_type',
                    width: 80,
                    halign: 'center',
                    title: "Category",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
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
                            required: true
                        }
                    }
                }, {
                    field: 'uom',
                    width: 80,
                    halign: 'center',
                    title: "Uom",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true,
                            readonly: true
                        }
                    }
                }, {
                    field: 'departement',
                    width: 100,
                    halign: 'center',
                    title: "Departement",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'location',
                    width: 100,
                    halign: 'center',
                    title: "Location",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'destination',
                    width: 100,
                    halign: 'center',
                    title: "Destination",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'account',
                    width: 100,
                    halign: 'center',
                    title: "Account",
                    editor: {
                        type: 'combobox',
                        options: {
                            readonly: true
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
        if (endEditing()) {
            addTable();
            $('#dg2').datagrid('appendRow', {
                qty: '0'
            });
            editIndex = $('#dg2').datagrid('getRows').length - 1;
            $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
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
                            url: '<?= base_url('finance/transfers/delete') ?>',
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
            url: '<?= base_url('finance/transfers/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/transfers/print') ?>' + url);
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
        window.location.assign('<?= base_url('finance/transfers/print/excel') ?>' + url);
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
            url: '<?= base_url('finance/transfers/datatables') ?>',
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
                    url: '<?= base_url('finance/transfers/datatables/details?number=') ?>' + window.btoa(row.number) +
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
                                    url: '<?= base_url('finance/transfers/create') ?>',
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
            url: '<?= base_url('finance/transfers/reads') ?>',
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
</script>
<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
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

<!-- DIALOG DELIVERY -->
<div id="dlg_delivery" class="easyui-dialog" title="Sales Delivery Order" data-options="closed: true,modal:true" style="width: 800px; height: 500px; top: 20px; left: 10px;">
    <table id="dg2" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar3">
        <thead>
            <tr>
                <th rowspan="2" field="ck" checkbox="true"></th>
                <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Delivery date</th>
                <th rowspan="2" data-options="field:'so_qty',width:100,align:'center'">Order Qty</th>
                <th rowspan="2" data-options="field:'qty',width:100,align:'center'">Delivery Qty</th>
                <th rowspan="2" data-options="field:'remain_qty',width:100,align:'center'">Remain Qty</th>
                <th rowspan="2" data-options="field:'status',width:80,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
                <th colspan="2" data-options="field:'',width:100,align:'center'"> Created</th>
            </tr>
            <tr>
                <th data-options="field:'created_by',width:100,align:'center'"> By</th>
                <th data-options="field:'created_date',width:150,align:'center'"> Date</th>

            </tr>
        </thead>
    </table>
</div>

<!-- DIALOG SAVE AND UPDATE CUSTOMER ADDRESS -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer Id</span>
                <input style="width:60%;" name="customer_id" id="customer_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Order No</span>
                <input style="width:60%;" name="sales_order_no" id="sales_order_no" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Id</span>
                <input style="width:60%;" name="item_fg_id" id="item_fg_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery Date</span>
                <input style="width:60%;" name="trans_date" id="trans_date" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:30%;" name="qty" id="qty" class="easyui-textbox">
            </div>

        </fieldset>
    </form>
</div>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 235px; padding: 10px;">
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" id="filter_customer_id" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Order No</span>
                    <input style="width:60%;" id="filter_customer_order_no" class="easyui-combobox">
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
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>


<div id="toolbar3">
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="add()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="deleted()"><i class="fa fa-times"></i> Remove</a>
</div>


<!-- PDF -->
<iframe id="printout" src="<?= base_url('sales/sales_order_delivery_rm/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        var customer_id = $("#customer_id").textbox('getValue');
        var sales_order_no = $("#sales_order_no").textbox('getValue');
        var item_fg_id = $("#item_fg_id").textbox('getValue');

        url_save = '<?= base_url('sales/sales_order_delivery_rm/create') ?>';
        $('#frm_insert').form('clear');

        $("#customer_id").textbox('setValue', customer_id);
        $("#sales_order_no").textbox('setValue', sales_order_no);
        $("#item_fg_id").textbox('setValue', item_fg_id);
        $("#trans_date").datebox('setValue', '<?= date("Y-m-d") ?>');
    }


    function btnDelivery(val, row) {
        var delivery = "delivery('" + row.customer_id + "','" + row.sales_order_no + "','" + row.item_fg_id + "')"; //mengambil id dari customers kemudian di simpan di function details
        return '<a class="btn btn-primary w-100" onClick="' + delivery + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-truck"></i></a>';
    }

    function delivery(customer_id, sales_order_no, item_fg_id) {
        $("#dlg_delivery").dialog('open');
        $("#customer_id").textbox('setValue', customer_id); // id customer di simpan di textbox customer_id sekaligus saat add id tersimpan
        $("#sales_order_no").textbox('setValue', sales_order_no);
        $("#item_fg_id").textbox('setValue', item_fg_id);

        $('#dg2').datagrid({
            url: '<?= base_url("sales/sales_order_delivery_rm/datatables2/") ?>' + btoa(customer_id) + '/' + btoa(sales_order_no) + '/' + btoa(item_fg_id)
        });
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg2').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('sales/sales_order_delivery_rm/delete') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                            },
                            complete: function(data) {
                                $('#dg2').datagrid('reload');
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
        var filter_customer_order_no = $("#filter_customer_order_no").combobox('getValue');
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        $('#dg').datagrid({
            url: '<?= base_url('sales/sales_order_delivery_rm/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.sales_order_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('sales/sales_order_delivery_rm/datatableDetails?sales_order_no=') ?>' + window.btoa(row.sales_order_no),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'btn',
                            title: 'Delivery',
                            halign: 'center',
                            formatter: btnDelivery,
                            width: 80
                        }, {
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
                            field: 'qty_del',
                            title: 'Delivery',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'qty_os',
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

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('sales/sales_order_delivery_rm/print') ?>' + url);
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
        var filter_customer_order_no = $("#filter_customer_order_no").combobox('getValue');
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        window.location.assign('<?= base_url('sales/sales_order_delivery_rm/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        filter();

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                                $('#dlg_insert').dialog('close');
                                $('#dg2').datagrid('reload');
                            } else {
                                toastr.error(result.message, result.title);
                            }
                        }
                    });
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
                url: '<?= base_url('sales/sales_order_delivery_rm/readSalesOrder/'); ?>' + customer.id,
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
                url: '<?= base_url('sales/sales_order_delivery_rm/readCustomerOrder/'); ?>' + customer.id,
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

            $('#filter_item_fg').combogrid({
                url: '<?= base_url('sales/sales_order_delivery_rm/readProductNo/'); ?>' + customer.id,
                panelWidth: 400,
                idField: 'id',
                textField: 'number',
                mode: 'remote',
                fitColumns: true,
                prompt: "Choose All",
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
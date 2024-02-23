<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATION" style="padding: 20px;">
            <ul>
                <li>The Data Customers is taken from <b>Master Data > Marketing > Customers</b></li>
                <li>The Data Line Production is taken from <b>Master Data > General Master > Line Production</b></li>
                <li>The Data Sales Order No is taken from the results of Customer selection and Get Data <b>Sales Order</b> Module</li>
                <li>The Data Product No is taken from the results of Sales Order No selection</li>
            </ul>
        </div>
        <div title="CONDITION" style="padding: 20px;">
            <ul>
                <li>If Status <b style="color: green">OPEN</b> then data new created in <b>Production Schedules</b></li>
                <li>If Status <b style="color: orange">SUPPLY</b> then data has been created in <b>Supply Sheet</b> when qty balance = 0</li>
                <li>If Status <b style="color: red">CLOSED</b> then data has been Scanned in <b>Scan Receipt FG</b></li>
                <li>If Qty in Production Schedule > Qty in Sales Order then <b style="color: red">ERROR</b></li>
            </ul>
        </div>
    </div>
</div>

<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'period',width:80,align:'center'">Period</th>
            <th rowspan="2" data-options="field:'wp',width:80,align:'center'">WP</th>
            <th rowspan="2" data-options="field:'status_wo',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status WO</th>
            <th rowspan="2" data-options="field:'trans_date',width:120,align:'center'">WP Date</th>
            <th rowspan="2" data-options="field:'workorder',width:150,align:'center'">Work Order</th>
            <th rowspan="2" data-options="field:'line_name',width:120,align:'center'">Line Production</th>
            <th colspan="3">Customer</th>
            <th rowspan="2" data-options="field:'so_number',width:150,align:'center'">SO No</th>
            <th rowspan="2" data-options="field:'so_date',width:80,align:'center'">SO Date</th>
            <th rowspan="2" data-options="field:'item_number',width:150">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200">Product Name</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'customer_number',width:80, align:'center'">ID</th>
            <th data-options="field:'customer_name',width:200, halign:'center'">Name</th>
            <th data-options="field:'customer_type',width:100, align:'center'">Type</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 200px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 30%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Select Month'">
                <input style="width:30%;" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Select Year'" panelHeight="auto">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Line Production</span>
                <input style="width:60%;" id="filter_line_productions" class="easyui-combobox" panelHeight="auto" data-options="prompt:'Select Line Production'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 30%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" id="filter_customers" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Order</span>
                <input style="width:60%;" id="filter_sales_order" class="easyui-combobox">
            </div>
        </div>
        <div style="width: 30%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status WO</span>
                <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="0">OPEN</option>
                    <option value="1">SUPPLY</option>
                    <option value="2">CLOSED</option>
                </select>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
    <!-- <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="print_job_order()"><i class="fa fa-print"></i> Print Job Order</a> -->
</div>
<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" id="month" name="month" required class="easyui-combobox">
                <input style="width:30%;" id="year" name="year" required class="easyui-combobox" panelHeight="auto">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Line Production</span>
                <input style="width:60%;" name="line_id" id="line_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" name="customer_id" required="" id="customer_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Order No</span>
                <input style="width:60%;" name="so_number" required="" id="so_number" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Order Date</span>
                <input style="width:60%;" name="so_date" id="so_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">WP</span>
                <input style="width:60%;" name="wp" id="wp" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">WP date</span>
                <input style="width:60%;" name="trans_date" id="trans_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="item_fg_id" required="" id="item_fg_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Quantity</span>
                <input style="width:30%;" name="qty" id="qty" required="" class="easyui-numberbox" data-options="precision:2">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/production_schedules/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //HELP
    function helps() {
        $('#dlg_help').dialog('open');
    }
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('planning/production_schedules/create') ?>';
        $('#frm_insert').form('clear');
        $("#wp").textbox('enable');
        $("#customer_id").combogrid('enable');
        $("#item_fg_id").combogrid('enable');
        $("#month").combobox('setValue', "<?= date("m") ?>");
        $("#year").combobox('setValue', "<?= date("Y") ?>");
    }
    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#wp").textbox('disable');
            $("#customer_id").combogrid('disable');
            $("#item_fg_id").combogrid('disable');
            url_save = '<?= base_url('planning/production_schedules/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }
    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('planning/production_schedules/delete') ?>',
                            data: {
                                id: row.id,
                                so_number: row.so_number,
                                item_fg_id: row.item_fg_id
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

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_line_productions = $("#filter_line_productions").combobox('getValue');
        var filter_customers = $("#filter_customers").combogrid('getValue');
        var filter_sales_order = $("#filter_sales_order").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_month=" + filter_month + "&filter_year=" + filter_year + "&filter_line_productions=" + filter_line_productions +
            "&filter_customers=" + filter_customers + "&filter_sales_order=" + filter_sales_order + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_status=" + filter_status;

        $('#dg').datagrid({
            url: '<?= base_url('planning/production_schedules/datatables') ?>' + url,
            fit: true,
            pagination: true,
            rownumbers: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/production_schedules/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_line_productions = $("#filter_line_productions").combobox('getValue');
        var filter_customers = $("#filter_customers").combogrid('getValue');
        var filter_sales_order = $("#filter_sales_order").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_month=" + filter_month + "&filter_year=" + filter_year + "&filter_line_productions=" + filter_line_productions +
            "&filter_customers=" + filter_customers + "&filter_sales_order=" + filter_sales_order + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_status=" + filter_status;

        window.location.assign('<?= base_url('planning/production_schedules/print/excel') ?>' + url);
    }

    function print_job_order() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            window.open('<?= base_url('planning/production_schedules/print_job_order/') ?>' + row.id, "_blank");
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        filter();

        //Save Data
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
                            } else {
                                toastr.error(result.message, result.title);
                            }
                            $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });
        //Get Customer
        $("#filter_line_productions").combobox({
            url: '<?= base_url('master/line_productions/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Line",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
        $("#filter_month").combobox({
            url: '<?= base_url('planning/production_schedules/readMonth') ?>',
            valueField: 'number',
            textField: 'name',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
        $("#filter_year").combobox({
            url: '<?= base_url('planning/production_schedules/readYear') ?>',
            valueField: 'number',
            textField: 'number',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
        $('#filter_customers').combogrid({
            url: '<?= base_url('master/customers/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Customer",
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
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            onSelect: function(val, row) {
                $("#filter_sales_order").combobox({
                    url: '<?= base_url('planning/sales_orders/readSalesOrder/') ?>' + row.id,
                    valueField: 'sales_order_no',
                    textField: 'sales_order_no',
                    prompt: "Select Sales Order",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });
        $('#filter_item_fg_id').combogrid({
            url: '<?= base_url('master/item_fg/reads/001') ?>',
            panelWidth: 420,
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
                    width: 100
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }, ]
            ]
        });
        $("#month").combobox({
            url: '<?= base_url('planning/production_schedules/readMonth') ?>',
            valueField: 'number',
            textField: 'name',
            prompt: "Month"
        });
        $("#year").combobox({
            url: '<?= base_url('planning/production_schedules/readYear') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Year"
        });
        $("#line_id").combobox({
            url: '<?= base_url('master/line_productions/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Line Production"
        });
        //Get Product
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
            onSelect: function(val, rowCust) {
                $('#so_number').combogrid({
                    url: '<?= base_url('planning/sales_orders/readSalesOrder/') ?>' + rowCust.id,
                    panelWidth: 420,
                    idField: 'sales_order_no',
                    textField: 'sales_order_no',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Sales Order",
                    columns: [
                        [{
                            field: 'sales_order_no',
                            title: 'SO Number',
                            width: 150
                        }, {
                            field: 'sales_order_date',
                            title: 'SO Date',
                            width: 200
                        }, ]
                    ],
                    onSelect: function(val, rowSo) {
                        $("#so_date").datebox('setValue', rowSo.sales_order_date);
                        $('#item_fg_id').combogrid({
                            url: '<?= base_url('planning/sales_orders/readItems/') ?>' + rowCust.id + '/' + rowSo.sales_order_no,
                            panelWidth: 500,
                            idField: 'id',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: "Choose Item",
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Product No',
                                    width: 200
                                }, {
                                    field: 'name',
                                    title: 'Product Name',
                                    width: 200
                                }, {
                                    field: 'qty',
                                    title: 'Qty',
                                    width: 80
                                }]
                            ],
                            onSelect: function(val, rowItem) {
                                $("#qty").numberbox('setValue', rowItem.qty);
                            }
                        });
                    }
                });
            }
        });

        $("#trans_date").datebox({
            onChange: function(trans_date) {
                var so_date = $("#so_date").datebox('getValue');
                if (so_date == "") {
                    toastr.warning("Please Select Sales Order Date");
                    $("#trans_date").datebox('clear');
                } else if (Date.parse(trans_date) < Date.parse(so_date)) {
                    toastr.warning("WP Date < Sales Order Date ");
                    $("#trans_date").datebox('clear');
                }
            }
        });
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

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if(value == 1){
            return "<b style='color:orange;'>SUPPLY</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if(value == 1){
            return 'background-color:#FFDFBD;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }
    //Number Format Currency
    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }
</script>
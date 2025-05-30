<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'print',width:59,align:'center', formatter:btnPrint">Print</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
            <th rowspan="2" data-options="field:'status_scan_label',width:80,align:'center', styler:cellStyler, formatter:cellFormatter">Status Scan</th>
            <th rowspan="2" data-options="field:'delivery_order_no',width:150,halign:'center'">Delivery Order No</th>
            <th rowspan="2" data-options="field:'delivery_order_date',width:100,halign:'center'">Delivery Order<br>Date</th>
            <th rowspan="2" data-options="field:'delivery_date',width:100,halign:'center'">Delivery Date</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'trans_type',width:100,halign:'center'">Transaction<br>Type</th>
            <th rowspan="2" data-options="field:'division',width:80,halign:'center'">Division</th>
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
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 35%; float: left;">
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
            <div style="width: 35%; float: left;">
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
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="filter_division" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true,fit:true" style="width: 1200px; height: 600px; padding:10px; top: 20px; left: 10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Order</span>
                    <select style="width:40%;" name="sales_order" id="sales_order" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="" disabled selected>Choose Sales Order</option>
                        <option value="FG">FINISH GOOD</option>
                        <option value="RM">RAW MATERIAL</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order Date</span>
                    <input style="width:40%;" name="delivery_order_date" id="delivery_order_date" required="" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Date</span>
                    <input style="width:40%;" name="delivery_date" id="delivery_date" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" id="btnPreview" onclick="preview()"><i class="fa fa-search"></i> Preview</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order No</span>
                    <input style="width:60%;" name="delivery_order_no" id="delivery_order_no" readonly required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Order No</span>
                    <input style="width:60%;" name="customer_order_no" id="customer_order_no" required class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="division" name="division" class="easyui-textbox">
                </div>
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
                    <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Delivery Order List" idField="item_number" rowNumbers="true">
            <thead>
                <tr>
                    <!-- <th field="ck" checkbox="true"></th>
                    <th data-options="field:'item_fg_id',width:150,editor:{type:'textbox', options:{readonly:true}}">Product ID</th> -->
                    <th data-options="field:'item_fg_number',width:170">Product No</th>
                    <th data-options="field:'item_fg_name',width:200">Product Name</th>
                    <th data-options="field:'sales_order_no',width:150,editor:{type:'textbox', options:{readonly:true}}">Sales Order No</th>
                    <th data-options="field:'division',width:80">Division</th>
                    <th hidden data-options="field:'customer_order_no',width:150">Customer Order No</th>
                    <th data-options="field:'uom',width:80">UoM</th>
                    <th data-options="field:'qty_so',width:100,editor:{type:'numberbox', options:{readonly:true, precision:2}}">Qty SO</th>
                    <th data-options="field:'qty_dn',width:100,editor:{type:'numberbox', options:{readonly:true, precision:2}}">Accum. <br>Qty DN</th>
                    <th data-options="field:'accum_qty_do',width:100,editor:{type:'numberbox', options:{readonly:true, precision:2}}">Accum. <br>Qty DO</th>
                    <th data-options="field:'qty_remain',width:100,editor:{type:'numberbox', options:{readonly:true, precision:2}},formatter:formatQtyRemain">Qty <br>Remain</th>
                    <th data-options="field:'qty_remain_date',width:100,editor:{type:'numberbox', options:{readonly:true, precision:2}},formatter:formatQtyRemain">OS <br>Delivery</th>                    
                    <th data-options="field:'qty_del',width:100,editor:{type:'numberbox',options:{precision:2}}">Qty <br>Delivery</th>
                    <th hidden data-options="field:'qty_do',width:100,editor:{type:'numberbox'}">Qty <br>DO</th>
                    <th data-options="field:'stock',width:100,editor:{type:'numberbox', options:{readonly:true, precision:2}}">Stock WHS</th>
                    <th data-options="field:'stock_bal',width:100,editor:{type:'numberbox', options:{readonly:true, precision:2}}">Balance <br>Stock</th>
                    <th data-options="field:'qty_sod',width:100,editor:{type:'numberbox', options:{readonly:true, precision:2}}">Qty SO <br>Delivery</th>
                    <th data-options="field:'njo_number',width:100">NJO Number</th>
                </tr>
            </thead>
        </table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('sales/delivery_orders/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg_request').datagrid('loadData', []);
        $('#frm_insert').form('clear');
        $("#delivery_order_date").datebox('enable');
        $("#delivery_date").combobox('enable');
        $("#customer_id").combobox('enable');
        $("#customer_order_no").combogrid('enable');
        $("#btnPreview").linkbutton('enable');

        $("#delivery_order_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#trans_type").combobox('setValue', "SALES");

        $("#sales_order").combobox({
            onChange: function(sales_order){
                $("#delivery_date").combobox({
                    url: "<?= base_url('sales/delivery_orders/readSalesOrderDeliveries/') ?>" + sales_order,
                    valueField: 'trans_date',
                    textField: 'trans_date',
                    prompt: 'Choose Delivery Date',
                    onSelect: function(delivery) {
                    console.log(delivery);
                        $('#customer_id').combobox({
                            url: '<?= base_url('sales/delivery_orders/readsC/'); ?>' + sales_order + "/" + btoa(delivery.trans_date),
                            valueField: 'id',
                            textField: 'name',
                            prompt: 'Choose Customer Name',
                            onSelect: function(customer) {
                                var delivery_order_date = $("#delivery_order_date").datebox('getValue');
                                var division = $("#division").textbox('getValue');
                                number(delivery_order_date, customer.number, division);

                                $('#customer_order_no').combogrid({
                                    url: '<?= base_url('sales/delivery_orders/readsCustOrderNo/'); ?>' + sales_order + "/" + btoa(customer.id) +"/"+ btoa(delivery.trans_date),
                                    panelWidth: 300,
                                    idField: 'customer_order_no',
                                    textField: 'customer_order_no',
                                    mode: 'remote',
                                    fitColumns: true,
                                    prompt: "Choose Customer Order No",
                                    multiple:true,
                                    icons: [{
                                        iconCls: 'icon-clear',
                                        handler: function(e) {
                                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                                        }
                                    }],
                                    columns: [[
                                        {
                                            field: 'customer_order_no',
                                            title: 'Customer Order No',
                                            width: 200
                                        },
                                        {
                                            field: 'division',
                                            title: 'Division',
                                            width: 80
                                        }
                                    ]],
                                    onSelect: function(index, row) {
                                        $("#division").textbox('setValue', row.division);

                                        var delivery_order_date = $("#delivery_order_date").datebox('getValue');
                                        number(delivery_order_date, customer.number, row.division);
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
    }

    $('#delivery_order_date').datebox({
        onChange: function(delivery_order_date) {
            if (delivery_order_date != "") {
                $("#customer_id").combobox('clear');
            }
        }
    });

    $(document).ready(function() {
        $('#delivery_date').combobox({
            onChange: function(newValue, oldValue) {
                var deliveryOrderDate = $('#delivery_order_date').datebox('getValue');
                var deliveryDate = $('#delivery_date').combobox('getValue');


                if(deliveryOrderDate > deliveryDate){
                    toastr.warning('Delivery Date should not be earlier than Delivery Order Date', 'Warning');
                }

            }
        });
    });

    function preview(url = "",isUpdate = false) {
        var sales_order = $("#sales_order").combobox('getValue');
        var delivery_date = $("#delivery_date").combobox('getValue');
        var customer_id = $("#customer_id").combobox('getValue');
        var customer_order_no = $("#customer_order_no").combogrid('getText');

        if (url == "") {
            var urlGet = "<?= base_url('sales/delivery_orders/datatablesTemp/') ?>" + sales_order + "/" + btoa(delivery_date) + "/" + btoa(customer_id) + "/" + btoa(customer_order_no);
        } else {
            var urlGet = url;
        }

        if (delivery_date == "" || customer_id == "" || customer_order_no == "") {
            toastr.warning('Please Select Delivery Date, Customer and Customer Order No', 'Required');
        } else {
            var lastIndex;
            var dg = $('#dg_request').datagrid({
                url: urlGet,
                fitColumns: true,
                onClickRow: function(rowIndex) {
                    if (lastIndex != rowIndex) {
                        $(this).datagrid('endEdit', lastIndex);
                        $(this).datagrid('beginEdit', rowIndex);
                    }
                    lastIndex = rowIndex;
                },
                onLoadSuccess: function(data) {
                    if (data.rows.length > 0) {
                        for (var i = 0; i < data.rows.length; i++) {
                            $('#dg_request').datagrid('beginEdit', i);
                            editors = $('#dg_request').datagrid('getEditors', i);
                            var item_fg_id = $(editors[0].target);
                            var sales_order_no = $(editors[1].target);
                            var accum_qty_do = $(editors[4].target);
                            var qty_del = $(editors[7].target);
                            var stock = $(editors[9].target);
                            var stock_bal = $(editors[10].target);

                            var f_qty_del = parseFloat(qty_del.numberbox('getValue'));
                            var f_stock = parseFloat(stock.numberbox('getValue'));

                            var f_balance = parseFloat(f_stock - f_qty_del);

                            stock_bal.numberbox('setValue', f_balance);

                            if (isUpdate) {
                                var qty_remain_date = $(editors[6].target);
                                qty_remain_date.numberbox('setValue', 0);
                            } 

                            // var f_item_fg_id = item_fg_id.textbox('getValue');
                            // var f_sales_order_no = sales_order_no.textbox('getValue');
                            // if (f_item_fg_id && f_sales_order_no) {
                                // $.ajax({
                                //     url: '<?= base_url('sales/delivery_orders/checkDo/') ?>' + window.btoa(f_item_fg_id) + "/" + f_sales_order_no,
                                //     type: 'POST',
                                //     dataType: 'json',
                                //     success: function(response) {
                                //         console.log(response);
                                //         $(editors[5].target).numberbox('setValue',response.qty);
                                //     },
                                //     error: function(xhr, status, error) {
                                //         toastr.error('An error occurred while checking qty');
                                //     }
                                // });
                            // }
                            $('#dg_request').datagrid('endEdit', i);
                        }
                    }
                },
                onBeginEdit: function(rowIndex, row) {
                    var editors = $('#dg_request').datagrid('getEditors', rowIndex);

                    var qty_remain = $(editors[5].target); // Kolom Qty Remain
                    var qty_del = $(editors[7].target); // Kolom Qty Delivery
                    var stock = $(editors[9].target); // Kolom Stock
                    var stock_bal = $(editors[10].target); // Kolom Balance Stock
                    var accum_qty_do = $(editors[4].target); // Kolom Accumulated Qty DO
                    var qty_remain_date = $(editors[6].target);
                    var qty_so = $(editors[2].target); // Kolom Qty SO
                    var qty_sod = $(editors[11].target); // Kolom Qty SOD

                    var initial_qty_so = parseFloat(qty_so.numberbox('getValue')) || 0;
                    var initial_qty_sod = parseFloat(qty_sod.numberbox('getValue')) || 0;
                    var initial_accum_qty_do = parseFloat(accum_qty_do.numberbox('getValue')) || 0;

                    var initial_qty_del = parseFloat(qty_del.numberbox('getValue')) || 0;
                    var initial_accum_qty_do_date = parseFloat(qty_remain_date.numberbox('getValue')) || 0;

                    // Hitung nilai awal Qty Remain
                    var initial_qty_remain = initial_qty_so - initial_accum_qty_do;

                    // Hitung nilai awal Qty Remain Date
                    var initial_qty_remain_date = initial_accum_qty_do_date;

                    console.log("QTY SOD: ", initial_qty_sod);
                    console.log("Accumulated Qty DO Date: ", initial_accum_qty_do_date);
                    console.log("Qty Delivery: ", initial_qty_del);
                    console.log("Qty Remain: ", initial_qty_sod - (initial_qty_remain_date + initial_qty_del));

                    qty_del.numberbox({
                        onChange: function(delivery) {
                            var f_qty_del = parseFloat(qty_del.numberbox('getValue')) || 0;
                            var f_stock = parseFloat(stock.numberbox('getValue')) || 0;

                            // Hitung Balance Stock
                            var f_balance = parseFloat(f_stock - f_qty_del);
                            stock_bal.numberbox('setValue', f_balance);

                            // Validasi sebelum perubahan qty_remain
                            if (f_qty_del > initial_qty_remain) {
                                qty_del.numberbox('setValue', 0);
                                toastr.error("Qty Delivery > Qty Remain, Please change qty delivery");
                                return; // Keluar dari fungsi jika validasi gagal
                            }

                            // Hitung Qty Remain
                            var new_qty_remain = initial_qty_so - (initial_accum_qty_do + f_qty_del);
                            qty_remain.numberbox('setValue', new_qty_remain);

                            // Hitung Qty Remain per Date
                            var new_qty_remain_date = initial_qty_sod - (f_qty_del + initial_qty_remain_date);
                            qty_remain_date.numberbox('setValue', new_qty_remain_date);

                            
                        }
                    });
                }
            });
        }
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
       
        if (row) {
            if (row.status == "0" || row.status == null ) {
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);

                console.log(row);

                $("#delivery_order_date").datebox('disable');
                $("#delivery_date").combobox('disable');
                $("#customer_id").combobox('disable');
                $("#customer_order_no").combogrid('disable');
                $("#btnPreview").linkbutton('disable');

                $("#customer_id").combobox('setValue',row.customer_id);

                preview("<?= base_url('sales/delivery_orders/datatableUpdates?delivery_order_no=') ?>" + btoa(row.delivery_order_no), true);
            } else {
                    toastr.error("You cannot update this data");
                }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function number(delivery_order_date, customer_no, division) {
        $.ajax({
            type: "post",
            url: "<?= base_url('sales/delivery_orders/number/') ?>" + window.btoa(delivery_order_date) + "/" + customer_no + "/" + division,
            dataType: "html",
            success: function(result) {
                $("#delivery_order_no").textbox('setValue', result);
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
                            url: '<?= base_url('sales/delivery_orders/delete') ?>',
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
        var filter_division = $("#filter_division").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_delivery_order_no=" + window.btoa(filter_delivery_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_division=" + window.btoa(filter_division);

        $('#dg').datagrid({
            url: '<?= base_url('sales/delivery_orders/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.delivery_order_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('sales/delivery_orders/datatableDetails?delivery_order_no=') ?>' + window.btoa(row.delivery_order_no),
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
                            field: 'sales_order_number',
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
                        }, 
                        // {
                        //     field: 'qty_do',
                        //     title: 'Total DO',
                        //     halign: 'center',
                        //     align: 'right',
                        //     width: 80,
                        //     formatter: numberFormat
                        // },
                         {
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
                            field: 'njo_number',
                            title: 'NJO Number',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                        }, {
                            field: 'status_scan',
                            title: 'Status Scan',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            styler:cellStyler, 
                            formatter:cellFormatter
                        }
                        // , {
                        //     field: 'stock_bal',
                        //     title: 'Balance<br>Stock',
                        //     halign: 'center',
                        //     align: 'right',
                        //     width: 80,
                        //     formatter: numberFormat
                        // }
                        ]
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
        $("#printout").attr('src', '<?= base_url('sales/delivery_orders/print') ?>' + url);
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
        var filter_division = $("#filter_division").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_delivery_order_no=" + window.btoa(filter_delivery_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_division=" + window.btoa(filter_division);

        window.location.assign('<?= base_url('sales/delivery_orders/print/excel') ?>' + url);
    }


    //RELOAD

    function reload() {

        window.location.reload();

    }



    $(function() {
        //SETTING DATAGRID EASYUI
        filter();

        //SAVE DATA
        // $('#dlg_insert').dialog({
        //     buttons: [{
        //         text: 'Save All',
        //         iconCls: 'icon-ok',
        //         handler: function() {
        //             var sales_order = $("#sales_order").combobox('getValue');
        //             var customer_id = $("#customer_id").combobox('getValue');
        //             var delivery_order_date = $("#delivery_order_date").datebox('getValue');
        //             var delivery_date = $("#delivery_date").combobox('getValue');
        //             var delivery_order_no = $("#delivery_order_no").textbox('getValue');
        //             var trans_type = $("#trans_type").combobox('getValue');
        //             var remarks = $("#remarks").textbox('getValue');

        //             $('#dg_request').datagrid('acceptChanges');

        //             var rows = $('#dg_request').datagrid('getSelections');
        //             var totalrows = rows.length;

        //             if (customer_id != "" && trans_type != "" && delivery_order_date != "" && delivery_date != "") {
        //                 if (totalrows > 0) {
        //                     for (let i = 0; i < totalrows; i++) {
        //                         if (rows[i].item_fg_id) {
        //                             $.ajax({
        //                                 type: "post",
        //                                 url: '<?= base_url('sales/delivery_orders/create') ?>',
        //                                 data: {
        //                                     sales_order: sales_order,
        //                                     customer_id: customer_id,
        //                                     delivery_order_date: delivery_order_date,
        //                                     delivery_order_no: delivery_order_no,
        //                                     delivery_date: delivery_date,
        //                                     trans_type: trans_type,
        //                                     remarks: remarks,
        //                                     item_fg_id: rows[i].item_fg_id,
        //                                     customer_order_no: rows[i].customer_order_no,
        //                                     sales_order_no: rows[i].sales_order_no,
        //                                     uom: rows[i].uom,
        //                                     qty_so: rows[i].qty_so,
        //                                     qty_remain: rows[i].qty_remain,
        //                                     qty_do: rows[i].qty_do,
        //                                     qty_del: rows[i].qty_del,
        //                                     qty_dn: rows[i].qty_dn,
        //                                     accum_qty_do: rows[i].accum_qty_do,
        //                                     stock: rows[i].stock,
        //                                     stock_bal: rows[i].stock_bal,
        //                                 },
        //                                 dataType: "json",
        //                                 success: function(result) {
        //                                     if (i == (totalrows - 1)) {
        //                                         Swal.fire({
        //                                             title: result.message,
        //                                             icon: result.theme,
        //                                             confirmButtonText: 'Ok',
        //                                             allowOutsideClick: false,
        //                                         }).then((result) => {
        //                                             if (result.isConfirmed) {
        //                                                 window.location.reload();
        //                                             }
        //                                         });
        //                                     }
        //                                 }
        //                             });
        //                         }
        //                     }

        //                     $('#dg').datagrid('reload');
        //                     $('#dlg_insert').dialog('close');
        //                 } else {
        //                     toastr.error("Please select at least one item to save.");
        //                 }
        //             } else {
        //                 toastr.error("Please complete your input");
        //             }
        //         }
        //     }]
        // });

        // $('#dlg_insert').dialog({
        //     buttons: [{
        //         text: 'Save All',
        //         iconCls: 'icon-ok',
        //         handler: function() {
        //             var sales_order = $("#sales_order").combobox('getValue');
        //             var customer_id = $("#customer_id").combobox('getValue');
        //             var delivery_order_date = $("#delivery_order_date").datebox('getValue');
        //             var delivery_date = $("#delivery_date").combobox('getValue');
        //             var delivery_order_no = $("#delivery_order_no").textbox('getValue');
        //             var trans_type = $("#trans_type").combobox('getValue');
        //             var remarks = $("#remarks").textbox('getValue');

        //             $('#dg_request').datagrid('acceptChanges');

        //             var totalrows = selectedItems.length;
        //             console.log(totalrows);

        //             if (totalrows > 25) {
        //                 toastr.error("Data Exceed 25");  // Menampilkan pesan jika lebih dari 25
        //             } else {
        //                 if (customer_id != "" && trans_type != "" && delivery_order_date != "" && delivery_date != "") {
        //                     if (totalrows > 0) {
        //                         for (let i = 0; i < totalrows; i++) {
        //                             if (selectedItems[i].item_fg_id) {
        //                                 $.ajax({
        //                                     type: "post",
        //                                     url: '<?= base_url('sales/delivery_orders/create') ?>',
        //                                     data: {
        //                                         sales_order: sales_order,
        //                                         customer_id: customer_id,
        //                                         delivery_order_date: delivery_order_date,
        //                                         delivery_order_no: delivery_order_no,
        //                                         delivery_date: delivery_date,
        //                                         trans_type: trans_type,
        //                                         remarks: remarks,
        //                                         item_fg_id: selectedItems[i].item_fg_id,
        //                                         customer_order_no: selectedItems[i].customer_order_no,
        //                                         sales_order_no: selectedItems[i].sales_order_no,
        //                                         uom: selectedItems[i].uom,
        //                                         qty_so: selectedItems[i].qty_so,
        //                                         qty_remain: selectedItems[i].qty_remain,
        //                                         qty_do: selectedItems[i].qty_do,
        //                                         qty_del: selectedItems[i].qty_del,
        //                                         qty_dn: selectedItems[i].qty_dn,
        //                                         accum_qty_do: selectedItems[i].accum_qty_do,
        //                                         stock: selectedItems[i].stock,
        //                                         stock_bal: selectedItems[i].stock_bal,
        //                                     },
        //                                     dataType: "json",
        //                                     success: function(result) {
        //                                         if (i == (totalrows - 1)) {
        //                                             Swal.fire({
        //                                                 title: result.message,
        //                                                 icon: result.theme,
        //                                                 confirmButtonText: 'Ok',
        //                                                 allowOutsideClick: false,
        //                                             }).then((result) => {
        //                                                 if (result.isConfirmed) {
        //                                                     window.location.reload();
        //                                                 }
        //                                             });
        //                                         }
        //                                     }
        //                                 });
        //                             }
        //                         }

        //                         $('#dg').datagrid('reload');
        //                         $('#dlg_insert').dialog('close');
        //                     } else {
        //                         toastr.error("Please select at least one item to save.");
        //                     }
        //                 } else {
        //                     toastr.error("Please complete your input");
        //                 }
        //             } 
        //         }
        //     }]
        // });

         // Array untuk menyimpan item yang dipilih
         var selectedItems = [];

        $('#dg_request').datagrid({
            onCheck: function(index, row) {
                // Menambahkan item yang dipilih ke dalam array
                selectedItems.push(row);
            },
            onUncheck: function(index, row) {
                // Menghapus item yang di-uncheck dari array
                selectedItems = selectedItems.filter(function(item) {
                    return item.item_fg_id !== row.item_fg_id;
                });
            },
            onCheckAll: function(rows) {
                // Menambahkan semua item yang dipilih ke dalam array
                selectedItems = selectedItems.concat(rows);
            },
            onUncheckAll: function(rows) {
                // Menghapus semua item dari array yang dipilih
                rows.forEach(function(row) {
                    selectedItems = selectedItems.filter(function(item) {
                        return item.item_fg_id !== row.item_fg_id;
                    });
                });
            }
        });

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var sales_order = $("#sales_order").combobox('getValue');
                    var customer_id = $("#customer_id").combobox('getValue');
                    var delivery_order_date = $("#delivery_order_date").datebox('getValue');
                    var delivery_date = $("#delivery_date").combobox('getValue');
                    var delivery_order_no = $("#delivery_order_no").textbox('getValue');
                    var division = $("#division").textbox('getValue');
                    var trans_type = $("#trans_type").combobox('getValue');
                    var remarks = $("#remarks").textbox('getValue');

                    $('#dg_request').datagrid('acceptChanges');

                    var totalrows = selectedItems.length;
                    console.log(totalrows);

                    if (parseInt(totalrows, 10) > 20) { 
                        toastr.error("Data Exceed 20");
                    } else {
                        if (customer_id != "" && trans_type != "" && delivery_order_date != "" && delivery_date != "") {
                            if (totalrows > 0) {
                                for (let i = 0; i < totalrows; i++) {
                                    if (selectedItems[i].item_fg_id) {
                                        $.ajax({
                                            type: "post",
                                            url: '<?= base_url('sales/delivery_orders/create') ?>',
                                            data: {
                                                sales_order: sales_order,
                                                customer_id: customer_id,
                                                delivery_order_date: delivery_order_date,
                                                delivery_order_no: delivery_order_no,
                                                division: division,
                                                delivery_date: delivery_date,
                                                trans_type: trans_type,
                                                remarks: remarks,
                                                item_fg_id: selectedItems[i].item_fg_id,
                                                customer_order_no: selectedItems[i].customer_order_no,
                                                sales_order_no: selectedItems[i].sales_order_no,
                                                uom: selectedItems[i].uom,
                                                qty_so: selectedItems[i].qty_so,
                                                qty_sod: selectedItems[i].qty_sod,
                                                qty_remain: selectedItems[i].qty_remain,
                                                qty_remain_date: selectedItems[i].qty_remain_date,
                                                qty_do: selectedItems[i].qty_do,
                                                qty_del: selectedItems[i].qty_del,
                                                qty_dn: selectedItems[i].qty_dn,
                                                accum_qty_do: selectedItems[i].accum_qty_do,
                                                stock: selectedItems[i].stock,
                                                stock_bal: selectedItems[i].stock_bal,
                                                njo_number: selectedItems[i].njo_number,
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
                                toastr.error("Please select at least one item to save.");
                            }
                        } else {
                            toastr.error("Please complete your input");
                        }
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
                url: '<?= base_url('sales/delivery_orders/readDeliveryOrder/'); ?>' + customer.id,
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
                        url: '<?= base_url('sales/delivery_orders/readSalesOrder/'); ?>' + deliver_order.sales_order_no,
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
                        url: '<?= base_url('sales/delivery_orders/readCustomerOrder/'); ?>' + deliver_order.customer_order_no,
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

    $('#filter_delivery_order_no').combobox({
        url: '<?= base_url('sales/delivery_orders/readDeliveryOrders'); ?>',
        valueField: 'delivery_order_no',
        textField: 'delivery_order_no',
        prompt: 'Choose All',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_sales_order_no').combobox({
        url: '<?= base_url('sales/delivery_orders/readSalesOrders'); ?>',
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
        url: '<?= base_url('sales/delivery_orders/readCustomerOrders'); ?>',
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

    $('#filter_division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'number',
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
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function btnPrint(val, row) {
        var print = "print_do('" + row.delivery_order_no + "')"; //mengambil id dari customers kemudian di simpan di function details
        return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
    }

    function print_do(delivery_order_no) {
        window.open("<?= base_url('sales/delivery_orders/print_do/') ?>" + window.btoa(delivery_order_no), "_blank", "width=1200,height=600");
    }

    // function checkValue(newValue, oldValue) {
    //     if(newValue > 0){
    //         $(this).numberbox('readonly', true);
    //     }else{
    //         $(this).numberbox('readonly', false);
    //     }
    // }

    function formatQtyRemain(value, row, index) {
        if (value == 0) {
            return '<span style="color:red;">' + value + '</span>';
        }
        return value;
    }

    $('#dlg_insert').dialog({
        onOpen: function () {
            $('#dg_request').datagrid({
                height: 350, // Sesuaikan tinggi grid agar tidak melewati batas dialog
                fitColumns: true,
                frozenColumns: [[
                    { field: 'ck', checkbox: true },
                    { field: 'item_fg_id', title: 'Product ID', width: 150, editor: { type: 'textbox', options: { readonly: true } } }
                ]]
            });
        }
    });

</script>
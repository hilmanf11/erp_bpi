<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'checksheet_number',width:120,align:'center'">Checksheet ID</th>
            <th rowspan="2" data-options="field:'workorder',width:120,align:'center'">Workorder</th>
            <th rowspan="2" data-options="field:'trans_date',width:80,align:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'wp',width:80,align:'center'">WP</th>
            <th rowspan="2" data-options="field:'product_no',width:150">Product No</th>
            <th rowspan="2" data-options="field:'product_name',width:200">Product Name</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th rowspan="2" data-options="field:'lot_label',width:80,halign:'center',align:'right'">Lot Label</th>
            <th rowspan="2" data-options="field:'lot_box',width:80,halign:'center',align:'right'">Lot Box</th>
            <th rowspan="2" data-options="field:'label',width:80,halign:'center',align:'right'">Label Qty</th>
            <th rowspan="2" data-options="field:'label_box',width:80,halign:'center',align:'right'">Box Qty</th>
            <th rowspan="2" data-options="field:'print',width:80,align:'center',formatter:BtnPrint">Label</th>
            <th rowspan="2" data-options="field:'print_box',width:80,align:'center',formatter:BtnPrintBox">Box</th>
            <th rowspan="2" data-options="field:'print_strip',width:80,align:'center',formatter:BtnPrintStrip">Small</th>
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
<div id="toolbar" style="height: 190px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 35%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" id="filter_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Checksheet</span>
                <input style="width:60%;" id="filter_checksheet" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:60%;" name="trans_date" id="trans_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Checksheet ID</span>
                <input style="width:60%;" name="checksheet_number" id="checksheet_number" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">WP</span>
                <input style="width:30%;" name="wp" id="wp" required="" readonly="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" id="customer" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="product_no" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Name</span>
                <input style="width:60%;" id="product_name" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt Qty</span>
                <input style="width:30%;" name="qty" id="qty" onchange="receiptQty()" readonly="" required="" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Lot Label</span>
                <input style="width:30%;" name="lot_label" id="lot_label" required="" readonly="" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Lot Box</span>
                <input style="width:30%;" name="lot_box" id="lot_box"  readonly="" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Label Qty</span>
                <input style="width:30%;" name="label" id="label" required="" readonly="" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Box Qty</span>
                <input style="width:30%;" name="label_box" id="label_box" required="" readonly="" class="easyui-numberbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- INSERT LABEL -->
<div id="dlg_label" class="easyui-dialog" title="Create Data Label" data-options="closed: true,modal:true,closable: false" style="width: 500px; padding:10px; top: 20px;">
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Create Label" class="easyui-panel" style="width:100%; height:300px; padding:10px; margin-top: 10px;">
        <ul id="remarks">

        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('warehouse/wip_receipts/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('warehouse/wip_receipts/create') ?>';
        $('#frm_insert').form('clear');
        $("#trans_date").datebox('setValue', "<?= date("Y-m-d") ?>");

        $('#checksheet_number').combogrid({
            url: '<?= base_url('warehouse/wip_receipts/readChecksheet') ?>',
            panelWidth: 380,
            idField: 'number',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Checksheet",
            columns: [
                [{
                    field: 'number',
                    title: 'Checksheet ID',
                    width: 150
                }, {
                    field: 'wp',
                    title: 'WP',
                    width: 80,
                    align: 'center'
                }, {
                    field: 'receipt',
                    title: 'Receipt Qty',
                    width: 100,
                    halign: 'center',
                    align: 'right'
                }]
            ],
            onSelect: function(val, row) {
                if (row.box == "0") {
                    toastr.error("Box in Master Items is 0");
                } else {
                    $("#wp").textbox('setValue', row.wp);
                    $("#period").textbox('setValue', row.period);
                    $("#product_no").textbox('setValue', row.product_no);
                    $("#product_name").textbox('setValue', row.product_name);
                    $("#customer").textbox('setValue', row.customer_name);
                    $("#qty").numberbox('setValue', row.receipt);
                    $("#lot_label").numberbox('setValue', row.box_sub);
                    $("#lot_box").numberbox('setValue', row.qty_box);
                    $("#label").numberbox('setValue', row.label);
                    $("#label_box").numberbox('setValue', row.label_box);
                }
            }
        });
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
                            url: '<?= base_url('warehouse/wip_receipts/delete') ?>',
                            data: {
                                id: row.id,
                                checksheet_number: row.checksheet_number
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
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_checksheet = $("#filter_checksheet").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_checksheet=" + filter_checksheet;
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/wip_receipts/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/wip_receipts/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_checksheet = $("#filter_checksheet").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_checksheet=" + filter_checksheet;

        window.location.assign('<?= base_url('warehouse/wip_receipts/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/wip_receipts/datatables') ?>',
            pagination: true,
            rownumbers: true
        });

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            if ($(this).form('validate') == true) {
                                $('#dlg_insert').dialog('close');
                                // Swal.fire({
                                //     title: 'Please Wait for Create WIP Receipt',
                                //     showConfirmButton: false,
                                //     allowOutsideClick: false,
                                //     allowEscapeKey: false,
                                //     didOpen: () => {
                                //         Swal.showLoading();
                                //     },
                                // });
                            } else {
                                return $(this).form('validate');
                            }
                        },
                        success: function(result) {
                            //Swal.close();
                            $("#dlg_label").dialog('open');

                            var checksheet_number = $("#checksheet_number").combogrid('getValue');
                            var qty = $("#qty").numberbox('getValue');
                            var lot_box = $("#lot_box").numberbox('getValue');
                            var lot_label = $("#lot_label").numberbox('getValue');
                            var label_box = $("#label_box").numberbox('getValue');
                            var label = $("#label").numberbox('getValue');

                            requestDataBox(label_box, qty);

                            function requestDataBox(total, qty, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    if (parseInt(qty) > parseInt(lot_box)) {
                                        var qty_final = lot_box;
                                    } else {
                                        var qty_final = qty;
                                    }

                                    $.ajax({
                                        type: "POST",
                                        async: true,
                                        url: "<?= base_url('warehouse/wip_receipts/create_label_box') ?>",
                                        data: {
                                            "checksheet_number": checksheet_number,
                                            "qty": qty_final,
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;

                                                var qty_balance = (parseInt(qty) - parseInt(lot_box));
                                                requestDataBox(total, qty_balance, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                var qty_balance = (parseInt(qty) - parseInt(lot_box));
                                                requestDataBox(total, qty_balance, number + 1, value, success + 0, failed + 1);
                                            }

                                            $("#p_remarks").append(title + "<br>");

                                            if (value == 100) {
                                                requestDataLabel(label, qty);
                                            }
                                        }
                                    }).fail(function(jqXHR, textStatus) {
                                        toastr.error("Connection Time Out, Please Wait");
                                        requestDataBox(total, qty, number, value, success, failed);
                                    });
                                }
                            }

                            function requestDataLabel(total, qty, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    if (parseInt(qty) > parseInt(lot_label)) {
                                        var qty_final = lot_label;
                                    } else {
                                        var qty_final = qty;
                                    }

                                    $.ajax({
                                        type: "POST",
                                        async: true,
                                        url: "<?= base_url('warehouse/wip_receipts/create_label') ?>",
                                        data: {
                                            "checksheet_number": checksheet_number,
                                            "qty": qty_final,
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;

                                                var qty_balance = (parseInt(qty) - parseInt(lot_label));
                                                requestDataLabel(total, qty_balance, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                var qty_balance = (parseInt(qty) - parseInt(lot_label));
                                                requestDataLabel(total, qty_balance, number + 1, value, success + 0, failed + 1);
                                            }

                                            $("#p_remarks").append(title + "<br>");

                                            if (value == 100) {
                                                $("#dlg_label").dialog('close');
                                                $('#dg').datagrid('reload');
                                                toastr.success("Create Label Completed");
                                                filter_checksheet();
                                            }
                                        }
                                    }).fail(function(jqXHR, textStatus) {
                                        toastr.error("Connection Time Out, Please Wait");
                                        requestDataLabel(total, qty, number, value, success, failed);
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

        filter_checksheet();

        $('#receipt').numberbox({
            onChange: function(value) {
                var qty = $("#qty").numberbox("getValue");
                var receipt = $("#receipt").numberbox('getValue');
                var result = parseInt(qty) - parseInt(receipt);
                var balance = $("#balance").numberbox('setValue', result);

                if (result < 0) {
                    toastr.warning("Receipt Qty not minus");
                    $("#receipt").numberbox('setValue', 0);
                } else {
                    return result;
                }
            }
        });
    });

    function filter_checksheet() {
        //Get Product
        $('#filter_checksheet').combogrid({
            url: '<?= base_url('warehouse/wip_receipts/readChecksheet/filter') ?>',
            panelWidth: 300,
            idField: 'checksheet_number',
            textField: 'checksheet_number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Checksheet",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'checksheet_number',
                    title: 'Checksheet',
                    width: 150
                }, {
                    field: 'wp',
                    title: 'WP',
                    width: 80,
                    align: 'center'
                }]
            ],
        });
    }

    function BtnPrint(val, row) {
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('warehouse/wip_receipts/print_label/') ?>' + window.btoa(row.checksheet_number) + '"><i class="fa fa-print"></i> Print</a>';
    }

    function BtnPrintBox(val, row) {
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('warehouse/wip_receipts/print_label_box/') ?>' + window.btoa(row.checksheet_number) + '"><i class="fa fa-print"></i> Print</a>';
    }

    function BtnPrintStrip(val, row) {
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('warehouse/wip_receipts/print_label_strip/') ?>' + window.btoa(row.checksheet_number) + '"><i class="fa fa-print"></i> Print</a>';
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

    //Number Format Currency
    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }
</script>
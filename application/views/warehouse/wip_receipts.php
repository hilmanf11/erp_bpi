<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'document_no',width:200,align:'center'">Document No</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'prod_date',width:100,align:'center'">Production <br>Date</th>
            <th rowspan="2" data-options="field:'shift',width:80,align:'center'">Shift</th>
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
<div id="toolbar" style="height: 230px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" id="filter_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" id="filter_document_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Final Checksheets Date</span>
                    <input style="width:60%;" id="filter_prod_date" class="easyui-datebox" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Shift</span>
                    <select style="width:60%;" id="filter_shift" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Checksheets</span>
                    <input style="width:60%;" id="filter_checksheet" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Final Checksheets</span>
                    <input style="width:60%;" id="filter_checksheet_number" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 99%; height: 600px; padding:10px; top: 5px; left:10px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:50%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Form Data</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receiving Date</span>
                    <input style="width:60%;" name="trans_date" id="trans_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No </span>
                    <input style="width:60%;" name="document_no" id="document_no" readonly class="easyui-textbox" data-options="prompt: 'Automatic'" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Final Checksheets Date</span>
                    <input style="width:60%;" name="prod_date" id="prod_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Shift</span>
                    <select style="width:60%;" name="shift" id="shift" class="easyui-combobox">
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Final Checksheet No</span>
                    <input style="width:60%;" name="checksheet_number" id="checksheet_number" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()" id="preview"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </fieldset>
        </div>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="WIP Receipts Lists" idField="item_number">
            <thead>
                <tr>
                    <th data-options="field:'action',width:120,formatter:buttonEdit">Action</th>
                    <th hidden data-options="field:'id',width:150">ID</th>
                    <th data-options="field:'checksheet_number',width:150">Final Checksheet No</th>
                    <th data-options="field:'wo_no',width:150">Wo No</th>
                    <th data-options="field:'item_fg_id',width:150" hidden>Product Id</th>
                    <th data-options="field:'item_number',width:150">Product No</th>
                    <th data-options="field:'item_name',width:200">Product Name</th>
                    <th data-options="field:'checksheet_qty',width:100">Checksheet Qty</th>
                    <th data-options="field:'qty',width:100,editor: {type: 'numberbox', options: {required: true}}">Receipt Qty</th>
                    <th data-options="field:'lot_no',width:100">Lot No</th>
                    <th data-options="field:'packing',width:100">Packing</th>
                    <th data-options="field:'packing_qty',width:100,editor: {type: 'numberbox', options: {required: true}}">MPQ, Qty/Box</th>
                    <th data-options="field:'label',width:100,formatter:labelFormatter">Label Qty</th>
                    <th data-options="field:'remarks',width:150,editor: {type: 'textbox', options: {required: true}}">Remarks</th>
                </tr>
            </thead>
        </table>
    </form>
</div>

<!-- INSERT LABEL -->
<div id="dlg_label" class="easyui-dialog" title="Create Data Label" data-options="closed: true,modal:true,closable: true" style="width: 500px; padding:10px; top: 20px;">
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
    // Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');
        document_no(); //autoid

        $("#prod_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        // $("#trans_date").datebox('setValue', "<?= date("Y-m-d") ?>");

        var dg = $('#dg2').datagrid({
            onBeforeEdit: function(index, row) {
                row.editing = true;
                $(this).datagrid('refreshRow', index);
            },
            onAfterEdit: function(index, row) {
                row.editing = false;
                $(this).datagrid('refreshRow', index);
            },
            onCancelEdit: function(index, row) {
                row.editing = false;
                $(this).datagrid('refreshRow', index);
            },
        });

    }

    function preview() {
        var checksheet_number = $("#checksheet_number").combogrid('getText');
        console.log(checksheet_number);

        if (checksheet_number == "") {
            toastr.info('Please completed your data');
        } else {
            var lastIndex;
            if (checksheet_number != "") {
                var dg = $('#dg2').datagrid({
                    url: '<?= base_url('warehouse/wip_receipts/datatablesTemp') ?>?checksheet_number=' + window.btoa(checksheet_number),
                });
            } else {
                toastr.info('Please completed your data');
            }
        }
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
            $('#dg2').datagrid('appendRow', {
                "action": 0
            });
            editIndex = $('#dg2').datagrid('getRows').length - 1;
            $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);

            var dg = $('#dg2');
            var row = dg.datagrid('getSelected');
            var rowIndex = dg.datagrid('getRowIndex', row);
        }
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function editrow(target) {
        $('#dg2').datagrid('selectRow', getRowIndex(target));
        $('#dg2').datagrid('beginEdit', getRowIndex(target));
    }

    function deleterow(target) {
        $.messager.confirm('Confirm', 'Are you sure?', function(r) {
            if (r) {
                var dg = $('#dg2');
                var row = dg.datagrid('getRows');
                var rowIndex = dg.datagrid('getRowIndex', row);

                var ed = dg.datagrid('getEditor', {
                    index: editIndex,
                    field: 'id'
                });

                $.ajax({
                    method: 'post',
                    url: '<?= base_url('warehouse/wip_receipts/deleteSingle') ?>',
                    data: {
                        id: row.id,
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

                $('#dg2').datagrid('deleteRow', getRowIndex(target));
            }
        });
    }

    function saverow(target) {
        $('#dg2').datagrid('endEdit', getRowIndex(target));
    }

    function cancelrow(target) {
        $('#dg2').datagrid('cancelEdit', getRowIndex(target));
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
                                document_no: row.document_no
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
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_checksheet = $("#filter_checksheet").combogrid('getValue');
        var filter_document_no = $("#filter_document_no").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_checksheet_number = $("#filter_checksheet_number").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_checksheet=" + filter_checksheet +
            "&filter_document_no=" + filter_document_no + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_shift=" + filter_shift +
            "&filter_checksheet_number=" + filter_checksheet_number;

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
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_checksheet = $("#filter_checksheet").combogrid('getValue');
        var filter_document_no = $("#filter_document_no").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_checksheet_number = $("#filter_checksheet_number").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_checksheet=" + filter_checksheet +
            "&filter_document_no=" + filter_document_no + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_shift=" + filter_shift +
            "&filter_checksheet_number=" + filter_checksheet_number;

        window.location.assign('<?= base_url('warehouse/wip_receipts/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        //ADD DATA

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/wip_receipts/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.document_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('warehouse/wip_receipts/datatableDetails?document_no=') ?>' + window.btoa(row.document_no) + "&filter_item_fg_id=" + window.btoa(filter_item_fg_id),
                    singleSelect: true,
                    rownumbers: true,
                    width: '1600px',
                    columns: [
                        [{
                            field: 'checksheet_number',
                            title: 'Final Checksheet No',
                            halign: 'center',
                            width: 170
                        }, {
                            field: 'wo_no',
                            title: 'Wo No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'product_no',
                            title: 'Product No',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'product_name',
                            title: 'Product name',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'checksheet_qty',
                            title: 'Checksheet Qty',
                            width: 100,
                            halign: 'center',
                        }, {
                            field: 'qty',
                            title: 'Receipt Qty',
                            width: 100,
                            halign: 'center',
                        }, {
                            field: 'lot_no',
                            title: 'Lot No',
                            halign: 'center',
                            width: 100
                        }, {
                            field: 'packing_qty',
                            title: 'MPQ, Qty/Box',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'label',
                            title: 'Label Qty',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter : labelFormatter
                        }, {
                            field: 'print',
                            title: 'Label',
                            halign: 'center',
                            width: 80,
                            formatter: BtnPrint
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

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All & Print Receiving Note',
                iconCls: 'icon-ok',
                handler: function() {
                    var trans_date = $("#trans_date").datebox('getValue');
                    var document_no = $("#document_no").textbox('getValue');
                    var prod_date = $("#prod_date").datebox('getValue');
                    var shift = $("#shift").combobox('getValue');

                    $('#dg2').datagrid('acceptChanges');
                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    console.log(rows);
                    for (let z = 0; z < totalrows; z++) {
                        if (rows.qty > rows.checksheet_qty) {
                            toastr.error(`Qty on row ${z + 1} cannot be greater than Checksheet Qty`);
                            return false;
                        }
                        // Calculate and save the label value
                        var qty = rows[z].qty || 0;
                        var packing_qty = rows[z].packing_qty || 1;
                        rows[z].label = (qty / packing_qty).toFixed(2);
                    }

                    if (totalrows > 0) {
                        requestData(totalrows, rows);
                        $('#dlg_insert').dialog('close');

                        function requestData(totalData, jsonData, jmlData = 1, valueData = 0) {
                            if (valueData < 100) {
                                valueData = Math.floor((jmlData / totalData) * 100);
                                var i = (jmlData - 1);

                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('warehouse/wip_receipts/create') ?>',
                                    data: {
                                        trans_date: trans_date,
                                        document_no: document_no,
                                        prod_date: prod_date,
                                        shift: shift,
                                        checksheet_number: jsonData[i].checksheet_number,
                                        item_fg_id: jsonData[i].item_fg_id,
                                        wo_no: jsonData[i].wo_no,
                                        qty: jsonData[i].qty,
                                        checksheet_qty: jsonData[i].checksheet_qty,
                                        lot_no: jsonData[i].lot_no,
                                        label: jsonData[i].label,
                                        packing: jsonData[i].packing,
                                        packing_qty: jsonData[i].packing_qty,
                                        remarks: jsonData[i].remarks
                                    },
                                    dataType: "json",
                                    success: function(result) {

                                        if (jmlData == totalData) {
                                            $("#dlg_label").dialog('close');
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

                                            $('#dg').datagrid('reload');
                                        } else {
                                            $("#dlg_label").dialog('open');
                                        }

                                        var checksheet_number = jsonData[i].checksheet_number;
                                        var qty = jsonData[i].qty;
                                        var packing_qty = jsonData[i].packing_qty;
                                        var packing = jsonData[i].packing;
                                        var label = jsonData[i].label;

                                        if (packing == 2) {
                                            requestDataBox(label, qty, 1, 0, 1, 1);

                                            function requestDataBox(total, qty, number, value, success, failed) {
                                                if (value < 100) {
                                                    value = Math.floor((number / total) * 100);
                                                    $('#p_upload').progressbar('setValue', value);
                                                    $('#p_start').html(number);
                                                    $('#p_finish').html(total);

                                                    var qty_final = (parseInt(qty) > parseInt(packing_qty)) ? packing_qty : qty;

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
                                                            var qty_balance = (parseInt(qty) - parseInt(packing_qty));
                                                            if (result.theme == "success") {
                                                                $('#p_success').html(success);
                                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                                requestDataBox(total, qty_balance, number + 1, value, success + 1, failed);
                                                            } else {
                                                                $('#p_failed').html(failed);
                                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                                requestDataBox(total, qty_balance, number + 1, value, success, failed + 1);
                                                            }

                                                            if (value == 100) {
                                                                $("#dlg_label").dialog('close');
                                                                $('#dg').datagrid('reload');
                                                                toastr.success("Create Label Completed");
                                                                filter_checksheet();
                                                                requestData(totalData, jsonData, jmlData + 1, valueData);
                                                            }

                                                            $("#p_remarks").append(title + "<br>");
                                                        }
                                                    }).fail(function(jqXHR, textStatus) {
                                                        toastr.error("Connection Time Out, Please Wait");
                                                        requestDataBox(total, qty, number, value, success, failed);
                                                    });
                                                }
                                            }
                                        } else { //15  //1047
                                            requestDataLabel(label, qty, 1, 0, 1, 1);

                                            function requestDataLabel(total, qty, number, value, success, failed) {
                                                if (total > 0) {
                                                    if (value < 100) {
                                                        value = Math.floor((number / total) * 100);
                                                        $('#p_upload').progressbar('setValue', value);
                                                        $('#p_start').html(number);
                                                        $('#p_finish').html(total);
                                                        //1047                //70
                                                        var qty_final = (parseInt(qty) > parseInt(packing_qty)) ? packing_qty : qty;

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
                                                                var qty_balance = (parseInt(qty) - parseInt(packing_qty));
                                                                if (result.theme == "success") {
                                                                    $('#p_success').html(success);
                                                                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                                    requestDataLabel(total, qty_balance, number + 1, value, success + 1, failed);
                                                                } else {
                                                                    $('#p_failed').html(failed);
                                                                    var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                                    requestDataLabel(total, qty_balance, number + 1, value, success, failed + 1);
                                                                }

                                                                $("#p_remarks").append(title + "<br>");

                                                                if (value == 100) {
                                                                    $("#dlg_label").dialog('close');
                                                                    $('#dg').datagrid('reload');
                                                                    toastr.success("Create Label Completed");
                                                                    filter_checksheet();
                                                                    requestData(totalData, jsonData, jmlData + 1, valueData);
                                                                }
                                                            }
                                                        }).fail(function(jqXHR, textStatus) {
                                                            toastr.error("Connection Time Out, Please Wait");
                                                            requestDataLabel(total, qty, number, value, success, failed);
                                                        });
                                                    }
                                                } else {
                                                    toastr.error("Qty Label is Zero, Please Add Qty Sub Box in Item Finish Good");
                                                    requestData(totalData, jsonData, jmlData + 1, valueData);
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                        }

                    } else {
                        toastr.warning("please select your data in table first");
                    }
                    $('#dg').datagrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });

        filter_checksheet();

        // $('#receipt').numberbox({
        //     onChange: function(value) {
        //         var qty = $("#qty").numberbox("getValue");
        //         var receipt = $("#receipt").numberbox('getValue');
        //         var result = parseInt(qty) - parseInt(receipt);
        //         var balance = $("#balance").numberbox('setValue', result);

        //         if (result < 0) {
        //             toastr.warning("Receipt Qty not minus");
        //             $("#receipt").numberbox('setValue', 0);
        //         } else {
        //             return result;
        //         }
        //     }
        // });
    });

    $('#checksheet_number').combogrid({
        panelWidth: 450,
        multiple: true,
        idField: 'number',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Final Checksheet",
        columns: [
            [{
                field: 'number',
                title: 'Checksheet Number',
                width: 200
            }, {
                field: 'wo_no',
                title: 'Wo No',
                width: 150
            }, {
                field: 'trans_date',
                title: 'Trans Date',
                width: 150
            }, {
                field: 'product_no',
                title: 'Product No',
                width: 150
            }]
        ]
    });

    function loadChecksheet(date, shift) {
        $("#checksheet_number").combogrid('grid').datagrid('loadData', []);
        $('#checksheet_number').combogrid({
            url: '<?= base_url('warehouse/wip_receipts/finalchecksheet') ?>?trans_date=' + encodeURIComponent(date) + '&shift=' + encodeURIComponent(shift),
            method: 'get'
        });
    }

    $("#filter_document_no").combobox({
        url: '<?= base_url('warehouse/wip_receipts/documentNo') ?>',
        valueField: 'document_no',
        textField: 'document_no',
        prompt: "Choose Document No",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/reads'); ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No",
        columns: [
            [{
                field: 'number',
                title: 'Product No',
                width: 150
            }, {
                field: 'name',
                title: 'Product Name',
                width: 250
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $("#filter_checksheet_number").combobox({
        url: '<?= base_url('warehouse/wip_receipts/readfinalchecksheet') ?>',
        valueField: 'checksheet_number',
        textField: 'checksheet_number',
        prompt: "Choose Checksheet No",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $("#trans_date").datebox({
        onSelect: function(date) {
            var formattedDate = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
            document_no(formattedDate);
            loadShifts(formattedDate);
        }
    });

    function document_no(date = "") {
        $.ajax({
            type: "post",
            url: "<?= base_url('warehouse/wip_receipts/document_no/') ?>" + window.btoa(date),
            dataType: "html",
            success: function(result) {
                $("#document_no").textbox('setValue', result);
            }
        });
    }

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
        if (row.packing == 1 || row.packing == 3) {
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('warehouse/wip_receipts/print_label/') ?>' + window.btoa(row.checksheet_number) + '"><i class="fa fa-print"></i> Print</a>';
        } else {
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('warehouse/wip_receipts/print_label_box/') ?>' + window.btoa(row.checksheet_number) + '"><i class="fa fa-print"></i> Print</a>';
        }
    }

    // function BtnPrintBox(val, row) {
    //     return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('warehouse/wip_receipts/print_label_box/') ?>' + window.btoa(row.checksheet_number) + '"><i class="fa fa-print"></i> Print</a>';
    // }

    function BtnPrintStrip(val, row) {
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('warehouse/wip_receipts/print_label_strip/') ?>' + window.btoa(row.checksheet_number) + '"><i class="fa fa-print"></i> Print</a>';
    }

    function buttonEdit(value, row, index) {
        if (row.editing) {
            var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a> ';
            var c = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="cancelrow(this)">Cancel</a>';
            return s + c;
        } else {
            var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a> ';
            var d = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="deleterow(this)">Delete</a>';
            return e + d;
        }
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

    function loadShifts(date) {
        $("#shift").combobox({
            url: '<?= base_url('warehouse/wip_receipts/getshift') ?>?trans_date=' + encodeURIComponent(date),
            valueField: 'shift',
            textField: 'shift',
            prompt: "Choose Shift",
            onSelect: function(record) {
                var shift = record.shift;
                var trans_date = $('#trans_date').datebox('getValue');
                loadChecksheet(trans_date, shift);
            }
        });
    }

    function labelFormatter(value, row, index) {
        var qty = row.qty || 0;
        var packing_qty = row.packing_qty || 1;
        var result = Math.ceil(qty / packing_qty);

        console.log(result);

        return result <= 0 ? 1 : result;
    }
</script>
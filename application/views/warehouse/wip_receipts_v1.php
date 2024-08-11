<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'document_no',width:150,align:'center'">Document No</th>
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
                    <span style="width:35%; display:inline-block;">Production Date</span>
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
                    <input style="width:60%;" id="filter_checksheet_number" class="easyui-combogrid">
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1100px; height: 500px; padding:10px; left:5px; top: 0;">
    <form id="frm_insert" method="post" novalidate>
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
                <span style="width:35%; display:inline-block;">Production Date</span>
                <input style="width:60%;" name="prod_date" id="prod_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
            <span style="width:35%; display:inline-block;">Shift</span>
                    <select style="width:60%;" id="shift" class="easyui-combobox" panelHeight="auto">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Final Checksheet No</span>
                <input style="width:60%;" name="checksheet_number" id="checksheet_number" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="preview()" id="preview"><i class="fa fa-search"></i> Preview Data</a>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="WIP Receipt List" toolbar="#toolbar2"></table>
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
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');
        document_no();//autoid

        $("#prod_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#trans_date").datebox('setValue', "<?= date("Y-m-d") ?>");

        url_save = '<?= base_url('warehouse/wip_receipts/create') ?>';
    }

    function addTable(checksheet_number, link = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'id',
                    width: 150,
                    readonly: true,
                    hidden: true,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox'
                    }
                },{
                    field: 'item_number',
                    width: 250,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('warehouse/wip_receipts/readItems?checksheet_number=') ?>' + checksheet_number,
                            required: true,
                            panelWidth: 650,
                            idField: 'item_number',
                            textField: 'item_number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product',
                            columns: [
                                [{
                                    field: 'item_number',
                                    title: 'Product No',
                                    width: 450
                                }, {
                                    field: 'item_name',
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
                                    field: 'id'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_name'
                                });

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'wo_no'
                                });

                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'lot_no'
                                });

                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'packing_qty'
                                });

                                var ed6 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'label_box'
                                });

                                var ed7 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'label'
                                });

                                var ed8 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_id'
                                });


                                $(ed.target).textbox('setValue', rows.id);
                                $(ed2.target).textbox('setValue', rows.item_name);
                                $(ed3.target).textbox('setValue', rows.wo_no);
                                $(ed4.target).textbox('setValue', rows.lot_no);
                                $(ed5.target).textbox('setValue', rows.packing_qty);
                                $(ed6.target).textbox('setValue', rows.label_box);
                                $(ed7.target).textbox('setValue', rows.label);
                                $(ed8.target).textbox('setValue', rows.item_id);
                                

                            }
                        }
                    }
                }, {
                    field: 'item_name',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Product Name",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'item_fg_id',
                    hidden: true,
                    width: 100,
                    halign: 'center',
                    title: "Item Id",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'wo_no',
                    width: 100,
                    halign: 'center',
                    title: "Wo No",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true,
                        }
                    }
                }, {
                    field: 'qty',
                    width: 80,
                    halign: 'center',
                    title: "Receipt <br>Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'lot_no',
                    width: 100,
                    halign: 'center',
                    title: "Lot No",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                        }
                    }
                }, {
                    field: 'packing_qty',
                    width: 100,
                    halign: 'center',
                    title: "MPQ <br>Qty/Box",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                        }
                    }
                }, {
                    field: 'label',
                    width: 100,
                    halign: 'center',
                    title: "Label Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                        }
                    }
                }, {
                    field: 'label_box',
                    width: 100,
                    halign: 'center',
                    title: "Box Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                        }
                    }
                }, {
                    field: 'remarks',
                    width: 200,
                    halign: 'center',
                    title: "Remarks",
                    editor: {
                        type: 'textbox'
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
        var checksheet_number = $("#checksheet_number").combobox('getValue');
        if (checksheet_number != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: ''
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Checksheets");
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
            field: 'id'
        });

        var id = $(ed.target).textbox('getValue');

        if(id != ""){
            $.ajax({
                method: 'post',
                url: '<?= base_url('warehouse/wip_receipts/delete') ?>',
                data: {
                    id: id
                },
                success: function(result) {
                    var result = eval('(' + result + ')');
                    toastr.success(result.message);
                },
                complete: function(data) {
                    $('#dg2').datagrid('reload');
                }
            });
        }

        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
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
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_checksheet = $("#filter_checksheet").combogrid('getValue');
        var filter_document_no = $("#filter_document_no").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_checksheet=" + filter_checksheet + 
        "&filter_document_no=" + window.btoa(filter_document_no) + "&filter_item_fg_id=" + window.btoa(filter_item_fg_id);
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

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_checksheet=" + filter_checksheet + 
        "&filter_document_no=" + window.btoa(filter_document_no) + "&filter_item_fg_id=" + window.btoa(filter_item_fg_id);

        window.location.assign('<?= base_url('warehouse/wip_receipts/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
           //ADD DATA
           addTable();

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
                    columns: [
                        [{
                            field: 'checksheet_number',
                            title: 'Final Checksheet No',
                            halign: 'center',
                            width: 150
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
                            width: 100
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
                        }, {
                            field: 'label_box',
                            title: 'Box Qty',
                            width: 100,
                            halign: 'center',
                        }, {
                            field: 'print',
                            title: 'Label',
                            halign: 'center',
                            width: 80,
                            formatter: BtnPrint
                        }, {
                            field: 'print_box',
                            title: 'Box',
                            halign: 'center',
                            width: 80,
                            formatter: BtnPrintBox
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
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var trans_date = $("#trans_date").datebox('getValue');
                    var document_no = $("#document_no").textbox('getValue');
                    var prod_date = $("#prod_date").datebox('getValue');
                    var shift = $("#shift").combobox('getValue');
                    var checksheet_number = $("#checksheet_number").combobox('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        // alert(rows[i].item_fg_id);
                        if (rows[i].item_fg_id) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('warehouse/wip_receipts/create') ?>',
                                data: {
                                    trans_date: trans_date,
                                    document_no: document_no,
                                    prod_date: prod_date,
                                    shift: shift,
                                    checksheet_number: checksheet_number,
                                    item_fg_id: rows[i].item_fg_id,
                                    wo_no: rows[i].wo_no,
                                    qty: rows[i].qty,
                                    lot_no: rows[i].lot_no,
                                    label: rows[i].label,
                                    label_box: rows[i].label_box,
                                    packing_qty: rows[i].packing_qty,
                                    remarks: rows[i].remarks
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

                                    $("#dlg_label").dialog('open');
                                    var checksheet_number = $("#checksheet_number").combobox('getValue');
                                    var qty = rows[i].qty; 
                                    var lot_box = rows[i].packing_qty; 
                                    var lot_label = rows[i].lot_no; 
                                    var label_box = rows[i].label_box; 
                                    var label = rows[i].label; 

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

                                            if (parseInt(qty) > parseInt(lot_label)) {//lot_label
                                                var qty_final = lot_label;//lot_label
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

                                                        var qty_balance = (parseInt(qty) - parseInt(lot_label));//lot_label
                                                        requestDataLabel(total, qty_balance, number + 1, value, success + 1, failed + 0);
                                                    } else {
                                                        $('#p_failed').html(failed);
                                                        var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                        var qty_balance = (parseInt(qty) - parseInt(lot_label));//lot_label
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
                    }

                    $('#dg').datagrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });

        // $('#dlg_insert').dialog({
        //     buttons: [{
        //         text: 'Save',
        //         iconCls: 'icon-ok',
        //         handler: function() {
        //             $('#frm_insert').form('submit', {
        //                 url: url_save,
        //                 onSubmit: function() {
        //                     if ($(this).form('validate') == true) {
        //                         $('#dlg_insert').dialog('close');
        //                         // Swal.fire({
        //                         //     title: 'Please Wait for Create WIP Receipt',
        //                         //     showConfirmButton: false,
        //                         //     allowOutsideClick: false,
        //                         //     allowEscapeKey: false,
        //                         //     didOpen: () => {
        //                         //         Swal.showLoading();
        //                         //     },
        //                         // });
        //                     } else {
        //                         return $(this).form('validate');
        //                     }
        //                 },
        //                 success: function(result) {
        //                     //Swal.close();
        //                     $("#dlg_label").dialog('open');

        //                     var checksheet_number = $("#checksheet_number").combogrid('getValue');
        //                     var qty = $("#qty").numberbox('getValue');
        //                     var lot_box = $("#lot_box").numberbox('getValue');
        //                     var lot_label = $("#lot_label").numberbox('getValue');
        //                     var label_box = $("#label_box").numberbox('getValue');
        //                     var label = $("#label").numberbox('getValue');

        //                     requestDataBox(label_box, qty);

        //                     function requestDataBox(total, qty, number = 1, value = 0, success = 1, failed = 1) {
        //                         if (value < 100) {
        //                             value = Math.floor((number / total) * 100);
        //                             $('#p_upload').progressbar('setValue', value);
        //                             $('#p_start').html(number);
        //                             $('#p_finish').html(total);

        //                             if (parseInt(qty) > parseInt(lot_box)) {
        //                                 var qty_final = lot_box;
        //                             } else {
        //                                 var qty_final = qty;
        //                             }

        //                             $.ajax({
        //                                 type: "POST",
        //                                 async: true,
        //                                 url: "<?= base_url('warehouse/wip_receipts/create_label_box') ?>",
        //                                 data: {
        //                                     "checksheet_number": checksheet_number,
        //                                     "qty": qty_final,
        //                                 },
        //                                 cache: false,
        //                                 dataType: "json",
        //                                 success: function(result) {
        //                                     if (result.theme == "success") {
        //                                         $('#p_success').html(success);
        //                                         var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;

        //                                         var qty_balance = (parseInt(qty) - parseInt(lot_box));
        //                                         requestDataBox(total, qty_balance, number + 1, value, success + 1, failed + 0);
        //                                     } else {
        //                                         $('#p_failed').html(failed);
        //                                         var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

        //                                         var qty_balance = (parseInt(qty) - parseInt(lot_box));
        //                                         requestDataBox(total, qty_balance, number + 1, value, success + 0, failed + 1);
        //                                     }

        //                                     $("#p_remarks").append(title + "<br>");

        //                                     if (value == 100) {
        //                                         requestDataLabel(label, qty);
        //                                     }
        //                                 }
        //                             }).fail(function(jqXHR, textStatus) {
        //                                 toastr.error("Connection Time Out, Please Wait");
        //                                 requestDataBox(total, qty, number, value, success, failed);
        //                             });
        //                         }
        //                     }

        //                     function requestDataLabel(total, qty, number = 1, value = 0, success = 1, failed = 1) {
        //                         if (value < 100) {
        //                             value = Math.floor((number / total) * 100);
        //                             $('#p_upload').progressbar('setValue', value);
        //                             $('#p_start').html(number);
        //                             $('#p_finish').html(total);

        //                             if (parseInt(qty) > parseInt(lot_label)) {
        //                                 var qty_final = lot_label;
        //                             } else {
        //                                 var qty_final = qty;
        //                             }

        //                             $.ajax({
        //                                 type: "POST",
        //                                 async: true,
        //                                 url: "<?= base_url('warehouse/wip_receipts/create_label') ?>",
        //                                 data: {
        //                                     "checksheet_number": checksheet_number,
        //                                     "qty": qty_final,
        //                                 },
        //                                 cache: false,
        //                                 dataType: "json",
        //                                 success: function(result) {
        //                                     if (result.theme == "success") {
        //                                         $('#p_success').html(success);
        //                                         var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;

        //                                         var qty_balance = (parseInt(qty) - parseInt(lot_label));
        //                                         requestDataLabel(total, qty_balance, number + 1, value, success + 1, failed + 0);
        //                                     } else {
        //                                         $('#p_failed').html(failed);
        //                                         var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

        //                                         var qty_balance = (parseInt(qty) - parseInt(lot_label));
        //                                         requestDataLabel(total, qty_balance, number + 1, value, success + 0, failed + 1);
        //                                     }

        //                                     $("#p_remarks").append(title + "<br>");

        //                                     if (value == 100) {
        //                                         $("#dlg_label").dialog('close');
        //                                         $('#dg').datagrid('reload');
        //                                         toastr.success("Create Label Completed");
        //                                         filter_checksheet();
        //                                     }
        //                                 }
        //                             }).fail(function(jqXHR, textStatus) {
        //                                 toastr.error("Connection Time Out, Please Wait");
        //                                 requestDataLabel(total, qty, number, value, success, failed);
        //                             });
        //                         }
        //                     }
        //                 }
        //             });
        //         }
        //     }]
        // });

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

    $("#checksheet_number").combobox({
        url: '<?= base_url('warehouse/wip_receipts/finalChecksheet/') ?>',
        valueField: 'number',
        textField: 'number',
        multiple:true,
        prompt: "Select Final Checksheet",
        onChange: function(row) {
            var selectedRows = $("#checksheet_number").combobox('getValues');

            addTable(selectedRows); 
        }
    });

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

    $("#trans_date").datebox({
        onSelect: function(date) {
            document_no(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate());
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
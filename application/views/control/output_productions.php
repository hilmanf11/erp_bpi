<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:150,align:'left'">Document No</th>
            <th rowspan="2" data-options="field:'period',width:200,halign:'center'">Period</th>
            <th rowspan="2" data-options="field:'trans_date',width:200,halign:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'shift',width:80,halign:'center'">Shift</th>
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
<div id="toolbar" style="height: 235px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 70%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="filter_division" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Wo No</span>
                    <input style="width:60%;" id="filter_wo_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No.</span>
                    <input style="width:60%;" id="filter_number" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Shift</span>
                    <select style="width:60%;" id="filter_shift" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1100px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:60%;" name="trans_date" id="trans_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No.</span>
                    <input style="width:60%;" name="number" id="number" class="easyui-textbox" readonly required>
                </div>
            </div>
            <div style="float: left; width:48%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" name="period" id="period" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Shift</span>
                    <select style="width:60%;" name="shift" id="shift" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
            </div>

        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Product Lists" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('control/output_productions/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('control/output_productions/create') ?>';
        $('#frm_insert').form('clear');
        $("#trans_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#trans_date").datebox('enable');
        $("#number").textbox('enable');
        $("#period").combobox('enable');
        $("#shift").combobox('enable');
        autonumber();

        $("#period").combobox({
            url: '<?= base_url('planning/supply_sheets/bpiPeriod') ?>',
            valueField: 'period',
            textField: 'period',
            prompt: "Select Period",
            onLoadSuccess: function(data) {
                // Parse the data if needed
                $("#period").combobox('setValue', "<?= date("Ym") ?>");
            }
        });
    }

    function autonumber() {
        $.ajax({
            type: "post",
            url: "<?= base_url('control/output_productions/autonumber') ?>",
            dataType: "html",
            success: function(result) {
                $("#number").textbox('setValue', result);
            }
        });
    }

    function addTable(link = "") {
        $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'id',
                    width: 150,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox'
                    },
                    hidden: true
                }, {
                    field: 'item_fg_id',
                    width: 150,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('control/output_productions/readItemFg/'); ?>',
                            required: true,
                            panelWidth: 400,
                            idField: 'item_fg_id',
                            textField: 'Product ID',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product No',
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Product No',
                                    width: 150
                                }, {
                                    field: 'name',
                                    title: 'Product Name',
                                    width: 200
                                }, {
                                    field: 'lot_no',
                                    title: 'Lot No',
                                    width: 80
                                }, {
                                    field: 'wo_no',
                                    title: 'Work Order No',
                                    width: 80
                                }, {
                                    field: 'modul',
                                    title: 'Modul',
                                    width: 100
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_id'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_number'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_name'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'wo_no'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'lot_no'
                                });


                                $(ed.target).textbox('setValue', rows.id);
                                $(ed2.target).textbox('setValue', rows.number);
                                $(ed3.target).textbox('setValue', rows.name);
                                $(ed4.target).textbox('setValue', rows.wo_no);
                                $(ed5.target).textbox('setValue', rows.lot_no);
                            }
                        }
                    }
                }, {
                    field: 'item_fg_number',
                    width: 150,
                    halign: 'center',
                    title: "Product Number",
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
                    field: 'lot_no',
                    width: 150,
                    halign: 'center',
                    title: "Lot No",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'wo_no',
                    width: 150,
                    halign: 'center',
                    title: "Work Order No",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty',
                    width: 80,
                    align: 'right',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                        }
                    }
                }, {
                    field: 'qty_wip',
                    width: 80,
                    align: 'right',
                    title: "Qty WIP",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
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
        var period = $("#period").combobox('getValue');
        if (period != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0',
                    qty_wip: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);

                var dg = $('#dg2');
                var row = dg.datagrid('getSelected');
                var rowIndex = dg.datagrid('getRowIndex', row);

                var item_fg_id = dg.datagrid('getEditor', {
                    index: rowIndex,
                    field: 'item_fg_id'
                });
                var item_fg_number = dg.datagrid('getEditor', {
                    index: rowIndex,
                    field: 'item_fg_number'
                });
                var item_fg_name = dg.datagrid('getEditor', {
                    index: rowIndex,
                    field: 'item_fg_name'
                });
                var wo_no = dg.datagrid('getEditor', {
                    index: rowIndex,
                    field: 'wo_no'
                });
                var lot_no = dg.datagrid('getEditor', {
                    index: rowIndex,
                    field: 'lot_no'
                });

                $(item_fg_id.target).combogrid({
                    url: '<?= base_url('control/output_productions/readItemFg/'); ?>' + period,
                    panelWidth: 700,
                    idField: 'item_fg_id',
                    textField: 'Product ID',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: 'Choose Product No',
                    columns: [
                        [{
                            field: 'number',
                            title: 'Product No',
                            width: 150
                        }, {
                            field: 'name',
                            title: 'Product Name',
                            width: 200
                        }, {
                            field: 'lot_no',
                            title: 'Lot No',
                            width: 80
                        }, {
                            field: 'wo_no',
                            title: 'Work Order No',
                            width: 80
                        }, {
                            field: 'modul',
                            title: 'Modul',
                            width: 100
                        }]
                    ],
                    onSelect: function(value, rows) {
                        $(item_fg_number.target).textbox('setValue', rows.number);
                        $(item_fg_name.target).textbox('setValue', rows.name);
                        $(wo_no.target).textbox('setValue', rows.wo_no);
                        $(lot_no.target).textbox('setValue', rows.lot_no);
                        // $(dn_number.target).textbox('setValue', rows.document_no);
                        // $(so_number.target).textbox('setValue', rows.document_no);
                    }
                });
            }
        } else {
            toastr.error("Please Choose Product No and Process first");
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

        $.ajax({
            method: 'post',
            url: '<?= base_url('control/output_productions/delete') ?>',
            data: {
                id: id,
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

        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#trans_date").datebox('disable');
            $("#number").textbox('disable');
            $("#period").combobox('disable');
            $("#shift").combobox('disable');

            addTable('<?= base_url('control/output_productions/datatableUpdates?number=') ?>' + window.btoa(row.number));
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
                            url: '<?= base_url('control/output_productions/delete') ?>',
                            data: {
                                number: row.number,
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
    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_output_productions.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_wo_no = $("#filter_wo_no").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_number = $("#filter_number").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to +
            "&filter_wo_no=" + filter_wo_no + "&filter_number=" + filter_number +
            "&filter_shift=" + filter_shift + "&filter_item_fg_id=" + filter_item_fg_id +
            "&filter_division=" + filter_division;

        $('#dg').datagrid({
            url: '<?= base_url('control/output_productions/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/output_productions/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_wo_no = $("#filter_wo_no").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_number = $("#filter_number").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to +
            "&filter_wo_no=" + filter_wo_no + "&filter_number=" + filter_number +
            "&filter_shift=" + filter_shift + "&filter_item_fg_id=" + filter_item_fg_id +
            "&filter_division=" + filter_division;

        window.location.assign('<?= base_url('control/output_productions/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //ADD DATA
        addTable();

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('control/output_productions/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            fitColumns: true,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.number + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('control/output_productions/datatableDetails?number=') ?>' + window.btoa(row.number) + "&filter_item_fg_id=" + window.btoa(filter_item_fg_id),
                    singleSelect: true,
                    rownumbers: true,
                    fitColumns: true,
                    columns: [
                        [{
                            field: 'item_fg_id',
                            title: 'Product ID',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_fg_number',
                            title: 'Product Number',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'lot_no',
                            title: 'Lot No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'wo_no',
                            title: 'Work Order No',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'qty',
                            title: 'Qty FG',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberformat
                        }, {
                            field: 'qty_wip',
                            title: 'Qty WIP',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberformat
                        }, {
                            field: 'remarks',
                            title: 'Remarks',
                            width: 200,
                            halign: 'center',
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
                    var trans_date = $("#trans_date").datebox('getValue');
                    var number = $("#number").textbox('getValue');
                    var period = $("#period").combobox('getValue');
                    var shift = $("#shift").combobox('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_fg_id) {

                            var dataFinal = {
                                trans_date: trans_date,
                                number: number,
                                period: period,
                                shift: shift,
                                id: rows[i].id,
                                item_fg_id: rows[i].item_fg_id,
                                wo_no: rows[i].wo_no,
                                lot_no: rows[i].lot_no,
                                qty: rows[i].qty,
                                qty_wip: rows[i].qty_wip,
                                remarks: rows[i].remarks
                            };

                            var url_save = "<?= base_url('control/output_productions/create') ?>";

                            $.ajax({
                                type: "post",
                                url: url_save,
                                data: dataFinal,
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
                }
            }]
        });
    });

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('control/output_productions/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('control/output_productions/upload') ?>',
                    onSubmit: function() {
                        if ($(this).form('validate') == false) {
                            return $(this).form('validate');
                        } else {
                            $.messager.progress({
                                title: 'Please Wait',
                                msg: 'Importing Excel to Database'
                            });
                        }
                    },
                    success: function(result) {
                        $.messager.progress('close');
                        //Clear File
                        $.ajax({
                            url: "<?= base_url('control/output_productions/uploadclearFailed') ?>"
                        });
                        var json = eval('(' + result + ')');
                        requestData(json.total, json);

                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                            if (value < 100) {
                                value = Math.floor((number / total) * 100);
                                $('#p_upload').progressbar('setValue', value);
                                $('#p_start').html(number);
                                $('#p_finish').html(total);

                                $.ajax({
                                    type: "POST",
                                    async: true,
                                    url: "<?= base_url('control/output_productions/uploadCreate') ?>",
                                    data: {
                                        "data": json[number - 1]
                                    },
                                    cache: false,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            $('#p_success').html(success);
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            $('#p_failed').html(failed);
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                            //Json Failed
                                            $.ajax({
                                                type: "POST",
                                                async: true,
                                                url: "<?= base_url('control/output_productions/uploadcreateFailed') ?>",
                                                data: {
                                                    data: json[number - 1],
                                                    message: result.message
                                                },
                                                cache: false
                                            });
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }
                                        $("#p_remarks").append(title + "<br>");
                                    }
                                });
                            }
                        }
                    }
                });
            }
        }]
    });

    $('#filter_division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'id',
        textField: 'name',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
    });

    $('#filter_wo_no').combobox({
        url: '<?= base_url('control/output_productions/readWoNos'); ?>',
        valueField: 'wo_no',
        textField: 'wo_no',
        prompt: 'Choose Wo No'
    });

    $('#filter_number').combobox({
        url: '<?= base_url('control/output_productions/readNumber'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: 'Choose Wo No'
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
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }
</script>
<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:150,align:'left'">Document No</th>
            <th rowspan="2" data-options="field:'start_date',width:150,halign:'center'">Start Date</th>
            <th rowspan="2" data-options="field:'end_date',width:150,halign:'center'">End Date</th>
            <th rowspan="2" data-options="field:'division',width:150,halign:'center'">Division</th>
            <th colspan="2" data-options="field:'',width:150,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:150,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 75%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
                <div style="float:left; width:50%">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Start Date</span>
                        <input style="width:60%;" id="filter_start_date" class="easyui-datebox" value="<?= date('Y') . '-01-01'; ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Ending Date</span>
                        <input style="width:60%;" id="filter_end_date" class="easyui-datebox" value="<?= date('Y') . '-12-31'; ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    </div>
                </div>
                <div style="float:left; width:48%">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Document No</span>
                        <input style="width:60%;" id="filter_number" class="easyui-combobox">
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1100px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:15%; display:inline-block;">Start Date</span>
                <input style="width:20%;" name="start_date" id="start_date" class="easyui-datebox" required="" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:15%; display:inline-block;">End Date</span>
                <input style="width:20%;" name="end_date" id="end_date" class="easyui-datebox" required="" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:15%; display:inline-block;">Division</span>
                <input style="width:20%;" name="division_id" id="division_id" class="easyui-combobox" required>
            </div>
            <div class="fitem">
                <span style="width:15%; display:inline-block;">Document No</span>
                <input style="width:20%;" readonly id="number" name="number" class="easyui-textbox" data-options="prompt:'Automatic'">
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="List Product " toolbar="#toolbar2"></table>
    </form>
</div>

<!-- Detail Histories -->
<div id="dlg_history" class="easyui-dialog" title="Price Histories" data-options="closed: true,modal:true" style="width: 650px; height: 300px; top: 20px;">
    <table id="dg_history" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'price',width:100,halign:'center',formatter: priceformat">Standard Price</th>
                <th data-options="field:'start_date',width:100,halign:'center'">Start Date</th>
                <th data-options="field:'end_date',width:100,halign:'center'">End Date</th>
                <th data-options="field:'created_by',width:120,align:'center'"> Created By</th>
                <th data-options="field:'created_date',width:150,align:'center'"> Created Date</th>
            </tr>
        </thead>
    </table>
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
<iframe id="printout" src="<?= base_url('master/standard_price_fg/print') ?>" style="width: 100%;" hidden></iframe>

<script>

    $(document).ready(function() {
        $.ajax({
            url: '<?= base_url('master/standard_price_fg/checkYear') ?>',
            dataType: 'json',
            success: function(response) {
                if (response.show) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'The year changes',
                        text: response.message
                    });
                }
            }
        });
    });

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('master/standard_price_fg/create') ?>';
        $('#frm_insert').form('clear');
        $("#number").textbox('enable');
        $("#start_date").datebox('enable');
        $("#end_date").datebox('enable');
        $("#division_id").combobox('enable');

        var year = new Date().getFullYear();
        var start_date = '<?= date("Y") ?>-01-01';  // 1 Januari tahun ini
        var end_date = '<?= date("Y") ?>-12-31';    // 31 Desember tahun ini

        $('#start_date').datebox('setValue', start_date);
        $('#end_date').datebox('setValue', end_date);

        number(start_date);
    }

    function addTable(division,link = "") {
       var dg = $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'item_fg_number',
                    width: 200,
                    halign: 'center',
                    title: "Product No.",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('master/standard_price_fg/readItems/'); ?>' + division,
                            required: true,
                            panelWidth: 400,
                            idField: 'number',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product No.',
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
                            onSelect: function(value, row) {
                                var dg = $('#dg2');
                                var allRows = dg.datagrid('getRows');
                                var isDuplicate = allRows.some(function(r) {
                                    return r.item_fg_number === row.number;
                                });

                                if (isDuplicate) {
                                    toastr.warning('Item Has Been Add!');
                                    var rowIndex = dg.datagrid('getRowIndex', row);
                                    dg.datagrid('cancelEdit', rowIndex);
                                    return;
                                }

                                var rowIndex = dg.datagrid('getRowIndex', dg.datagrid('getSelected'));

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_number'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_id'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_name'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_family_id'
                                });
                                var ed6 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_family_name'
                                });

                                $(ed.target).textbox('setValue', row.number);
                                $(ed2.target).textbox('setValue', row.id);
                                $(ed3.target).textbox('setValue', row.name);
                                $(ed4.target).textbox('setValue', row.uom);
                                $(ed5.target).textbox('setValue', row.item_family_id);
                                $(ed6.target).textbox('setValue', row.item_family_name);
                            }
                        }
                    }
                }, {
                    field: 'item_fg_id',
                    width: 150,
                    hidden: true,
                    halign: 'center',
                    title: "Product ID",
                    editor: {
                        type: 'textbox'
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
                    field: 'item_family_id',
                    width: 150,
                    hidden: true,
                    halign: 'center',
                    title: "Category",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'item_family_name',
                    width: 150,
                    halign: 'center',
                    title: "Product Family",
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
                    title: "UOM",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'currency',
                    width: 150,
                    halign: 'center',
                    title: "Currency",
                    editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('master/currencies/reads') ?>',
                            editable:false,
                            valueField: 'name',
                            textField: 'name',
                            mode: 'remote',
                            fitColumns: true,
                            required: true,
                            prompt: 'Choose Currencies'
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
                            precision: 4,
                            required: true
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
        var start_date = $("#start_date").datebox('getValue');
        var end_date = $("#end_date").datebox('getValue');
        var division_id = $("#division_id").combobox('getValue');
        if (start_date != "" && end_date != "" && division_id != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please completed your data");
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

        var item_fg_id = $(ed.target).textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('master/standard_price_fg/delete') ?>',
            data: {
                item_fg_id: item_fg_id
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
        url_save = '<?= base_url('master/standard_price_fg/create') ?>';
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#number").textbox('disable');
            $("#start_date").datebox('disable');
            $("#end_date").datebox('disable');
            $("#division_id").combobox('disable');

            addTable(division=row.division_id,'<?= base_url('master/standard_price_fg/datatableUpdates?number=') ?>' + window.btoa(row.number));
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
                            url: '<?= base_url('master/standard_price_fg/delete') ?>',
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
    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_standard_price_fg.xls') ?>');
    }

    //NOMOR AUTOMATIC
    function number(start_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('master/standard_price_fg/number/') ?>" + window.btoa(start_date),
            dataType: "html",
            success: function(result) {
                $("#number").textbox('setValue', result);
            }
        });
    }

    //FILTER DATA
    function filter() {
        var filter_start_date = $("#filter_start_date").datebox('getValue');
        var filter_end_date = $("#filter_end_date").datebox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_number = $("#filter_number").combobox('getValue');

        var url = "?filter_item_fg_id=" + window.btoa(filter_item_fg_id) +
            "&filter_start_date=" + window.btoa(filter_end_date) +
            "&filter_end_date=" + window.btoa(filter_end_date) +
            "&filter_number=" + window.btoa(filter_number);

        $('#dg').datagrid({
            url: '<?= base_url('master/standard_price_fg/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('master/standard_price_fg/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_start_date = $("#filter_start_date").datebox('getValue');
        var filter_end_date = $("#filter_end_date").datebox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_number = $("#filter_number").combobox('getValue');

        var url = "?filter_item_fg_id=" + window.btoa(filter_item_fg_id) +
            "&filter_start_date=" + window.btoa(filter_end_date) +
            "&filter_end_date=" + window.btoa(filter_end_date) +
            "&filter_number=" + window.btoa(filter_number);

        window.location.assign('<?= base_url('master/standard_price_fg/print/excel') ?>' + url);
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
            url: '<?= base_url('master/standard_price_fg/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.start_date + " to " + row.end_date + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('master/standard_price_fg/datatableDetails?number=') ?>' + window.btoa(row.number) + "&filter_item_fg_id=" + window.btoa(filter_item_fg_id),
                    singleSelect: true,
                    rownumbers: true,
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
                            width: 150
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'uom',
                            title: 'Uom',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'division',
                            title: 'Division',
                            width: 80,
                            halign: 'center',
                        }, {
                            field: 'category',
                            title: 'Category',
                            halign: 'center',
                            width: 100
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'price',
                            title: 'Price',
                            halign: 'center',
                            align: 'right',
                            width: 100,
                            formatter: priceformat
                        }, {
                            field: 'btn',
                            title: 'History',
                            halign: 'center',
                            width: 80,
                            formatter: btnHistories
                        }, {
                            field: 'remarks',
                            title: 'Remarks',
                            width: 150,
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
                    var start_date = $("#start_date").datebox('getValue');
                    var end_date = $("#end_date").datebox('getValue');
                    var division_id = $("#division_id").combobox('getValue');
                    var number = $("#number").textbox('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_fg_id) {
                            var dataFinal = {
                                start_date: start_date,
                                end_date: end_date,
                                division_id: division_id,
                                number: number,
                                item_fg_id: rows[i].item_fg_id,
                                item_fg_number: rows[i].item_fg_number,
                                item_fg_name: rows[i].item_fg_name,
                                item_family_id: rows[i].item_family_id,
                                item_family_name: rows[i].item_family_name,
                                uom: rows[i].uom,
                                currency: rows[i].currency,
                                price: rows[i].price,
                                remarks: rows[i].remarks
                            }
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

    $('#division_id').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'id',
        textField: 'number',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
        onSelect: function(division) {
            addTable(division.id);
        }
    }); 

    $('#item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/reads/'); ?>',
        panelWidth: 420,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No",
        columns: [
            [{
                field: 'number',
                title: 'Product No',
                width: 120
            }, {
                field: 'name',
                title: 'Product Name',
                width: 250
            }, ]
        ]
    });

    $('#start_date').datebox({
        onSelect: function(date) {
            var startDateFormatted = date.getFullYear() + '-' + 
                ('0' + (date.getMonth() + 1)).slice(-2) + '-' + 
                ('0' + date.getDate()).slice(-2);

            number(startDateFormatted);
        }
    });

    $('#filter_item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/reads'); ?>',
        panelWidth: 750,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No",
        columns: [
            [{
                field: 'id',
                title: 'Product ID',
                width: 150
            }, {
                field: 'number',
                title: 'Product No',
                width: 150
            }, {
                field: 'number_customer',
                title: 'Product Customer',
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

    $('#filter_number').combobox({
        url: '<?= base_url('master/standard_price_fg/readNumber'); ?>',
        valueField: 'number',
        textField: 'number',
        panelHeight: 'panelHeight',
        prompt: 'Choose Document No',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    }); 

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/standard_price_fg/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/standard_price_fg/upload') ?>',
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
                            url: "<?= base_url('master/standard_price_fg/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('master/standard_price_fg/uploadCreate') ?>",
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
                                                url: "<?= base_url('master/standard_price_fg/uploadcreateFailed') ?>",
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

    function btnHistories(val, row) {
        var history = "viewHistories('" + row.item_fg_id + "')";
        return '<a class="btn btn-primary w-100" onClick="' + history + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewHistories(item_fg_id) {
        $("#dlg_history").dialog('open');
        $('#dg_history').datagrid({
            url: '<?= base_url('master/standard_price_fg/datatableHistories?item_fg_id=') ?>' + btoa(item_fg_id),
            pagination: false,
            rownumbers: true,
        });
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

    function priceformat(value, row) {
        var digits, currency, format;

        if (row.currency === "USD") {
            digits = 4;
            currency = 'USD';
            format = "en-US";
        } else if (row.currency === "JPY") {
            digits = 2;
            currency = 'JPY';
            format = "ja-JP";
        } else if (row.currency === "EUR") {
            digits = 2;
            currency = 'EUR';
            format = "de-DE";
        } else {
            digits = 2;
            currency = 'IDR';
            format = "id-ID";
        }

        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                style: 'decimal',
                minimumFractionDigits: digits
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }
</script>
<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Subcont is taken from <b>Master Data > PPIC > Subconts</b></li>
                <li>The Data Machine No is taken from <b>Master Data > Maintenance > Machines</b></li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'subcont_id',width:200,halign:'center'">Subcont ID</th>
            <th rowspan="2" data-options="field:'subcont_number',width:200,halign:'center'">Subcont Code</th>
            <th rowspan="2" data-options="field:'subcont_name',width:300,halign:'center'">Subcont Name</th>
            <th rowspan="2" data-options="field:'status',width:150,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
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
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 45%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Subcont</span>
                <input style="width:60%;" id="filter_subcont_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Machine No</span>
                <input style="width:60%;" id="filter_machine_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1400px; height: 500px; padding:10px; top: 20px; left: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:15%; display:inline-block;">Subcont</span>
                <input style="width:40%;" name="subcont_id" id="subcont_id" required="" class="easyui-combogrid">
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Subcont Item Lists" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- Detail Histories -->
<!-- <div id="dlg_history" class="easyui-dialog" title="Price Histories" data-options="closed: true,modal:true" style="width: 400px; height: 300px; top: 20px;">
    <table id="dg_history" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'price',width:100,halign:'center',formatter: priceformat">Price</th>
                <th data-options="field:'valid_date',width:100,halign:'center'">Valid Date</th>
            </tr>
        </thead>
    </table>
</div> -->

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
<iframe id="printout" src="<?= base_url('master/menu_loading_subconts/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('master/menu_loading_subconts/create') ?>';
        $('#frm_insert').form('clear');
    }

    function addTable(link = "") {
        $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'machine_number',
                    width: 200,
                    halign: 'center',
                    title: "Machine No.",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('master/machines/reads'); ?>',
                            required: true,
                            panelWidth: 400,
                            idField: 'number',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Machine No.',
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Machine No.',
                                    width: 150
                                }, {
                                    field: 'name',
                                    title: 'Machine Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_number'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_id'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_name'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_spec'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_maker'
                                });
                                var ed6 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_toonage'
                                });
                                var ed7 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_tiebar'
                                });
                                var ed8 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_uom_tiebar'
                                });
                                var ed9 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_min_close'
                                });
                                var ed10 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_uom_min_close'
                                });
                                var ed11 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_max_open'
                                });
                                var ed12 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_uom_max_open'
                                });
                                var ed13 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_volume'
                                });
                                var ed14 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_uom_volume'
                                });
                                var ed15 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_diameter'
                                });
                                var ed16 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_uom_diameter'
                                });
                                var ed17 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_brand'
                                });
                                var ed18 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'machine_status'
                                });

                                $(ed.target).textbox('setValue', rows.number);
                                $(ed2.target).textbox('setValue', rows.id);
                                $(ed3.target).textbox('setValue', rows.name);
                                $(ed4.target).textbox('setValue', rows.specification);
                                $(ed5.target).textbox('setValue', rows.maker);
                                $(ed6.target).textbox('setValue', rows.toonage);
                                $(ed7.target).textbox('setValue', rows.tiebar);
                                $(ed8.target).textbox('setValue', rows.uom_tiebar);
                                $(ed9.target).textbox('setValue', rows.min_closing);
                                $(ed10.target).textbox('setValue', rows.uom_min);
                                $(ed11.target).textbox('setValue', rows.max_open);
                                $(ed12.target).textbox('setValue', rows.uom_max);
                                $(ed13.target).textbox('setValue', rows.volume);
                                $(ed14.target).textbox('setValue', rows.uom_volume);
                                $(ed15.target).textbox('setValue', rows.diameter);
                                $(ed16.target).textbox('setValue', rows.uom_diameter);
                                $(ed17.target).textbox('setValue', rows.brand);
                                $(ed18.target).textbox('setValue', rows.status);
                            }
                        }
                    }
                }, {
                    field: 'machine_id',
                    width: 150,
                    hidden: true,
                    halign: 'center',
                    title: "Part ID",
                    editor: {
                        type: 'textbox'
                    }
                },  {
                    field: 'capacity',
                    width: 100,
                    align: 'center',
                    title: "Capacity/Day",
                    editor: {
                        type: 'numberbox'
                    }
                },  {
                    field: 'machine_name',
                    width: 150,
                    halign: 'center',
                    title: "Name of Machine",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'machine_spec',
                    width: 150,
                    halign: 'center',
                    title: "Specification",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'machine_maker',
                    width: 150,
                    halign: 'center',
                    title: "Maker",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'machine_toonage',
                    width: 120,
                    align: 'center',
                    title: "Tonage Of Machine",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'machine_tiebar',
                    width: 100,
                    align: 'center',
                    title: "Tie Bar",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'machine_uom_tiebar',
                    width: 100,
                    align: 'center',
                    title: "UOM",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'machine_min_close',
                    width: 120,
                    align: 'center',
                    title: "Minimum Closing",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                },  {
                    field: 'machine_uom_min_close',
                    width: 100,
                    align: 'center',
                    title: "UOM",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                },  {
                    field: 'machine_max_open',
                    width: 100,
                    align: 'center',
                    title: "Maximum Open",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                },  {
                    field: 'machine_uom_max_open',
                    width: 100,
                    align: 'center',
                    title: "UOM",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                },  {
                    field: 'machine_volume',
                    width: 100,
                    align: 'center',
                    title: "Barrel Volume",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                },  {
                    field: 'machine_uom_volume',
                    width: 100,
                    align: 'center',
                    title: "UOM",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                },  {
                    field: 'machine_diameter',
                    width: 100,
                    align: 'center',
                    title: "Screw Diameter",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
                    }
                },  {
                    field: 'machine_uom_diameter',
                    width: 100,
                    align: 'center',
                    title: "UOM",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                },  {
                    field: 'machine_brand',
                    width: 100,
                    align: 'center',
                    title: "Brand",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                },  {
                    field: 'machine_status',
                    width: 100,
                    align: 'center',
                    title: "Status",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
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
        var subcont_id = $("#subcont_id").combogrid('getValue');
        if (subcont_id != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Subcont first");
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
            field: 'machine_id'
        });

        var subcont_id = $("#subcont_id").combogrid('getValue');
        var machine_id = $(ed.target).textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('master/menu_loading_subconts/delete') ?>',
            data: {
                subcont_id: row.subcont_id,
                machine_id: machine_id
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
            $("#machine_id").combogrid('disable');

            addTable('<?= base_url('master/menu_loading_subconts/datatableUpdates?subcont_id=') ?>' + window.btoa(row.subcont_id));
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
                            url: '<?= base_url('master/menu_loading_subconts/delete') ?>',
                            data: {
                                subcont_id: row.subcont_id
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
        window.location.assign('<?= base_url('template/tmp_menu_loading_subconts.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_subcont_id = $("#filter_subcont_id").combogrid('getValue');
        var filter_machine_id = $("#filter_machine_id").combogrid('getValue');

        var url = "?filter_subcont_id=" + window.btoa(filter_subcont_id) +
            "&filter_machine_id=" + window.btoa(filter_machine_id);

        $('#dg').datagrid({
            url: '<?= base_url('master/menu_loading_subconts/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('master/menu_loading_subconts/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_subcont_id = $("#filter_subcont_id").combogrid('getValue');
        var filter_machine_id = $("#filter_machine_id").combogrid('getValue');

        var url = "?filter_subcont_id=" + window.btoa(filter_subcont_id) +
            "&filter_machine_id=" + window.btoa(filter_machine_id);

        window.location.assign('<?= base_url('master/menu_loading_subconts/print/excel') ?>' + url);
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
            url: '<?= base_url('master/menu_loading_subconts/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.subcont_name + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_machine_id = $("#filter_machine_id").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('master/menu_loading_subconts/datatableDetails?number=') ?>' + window.btoa(row.subcont_number) + "&filter_machine_id=" + window.btoa(filter_machine_id),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'capacity',
                            title: 'Capacity/Day',
                            halign: 'center',
                            width: 100
                        },  {
                            field: 'machine_id',
                            title: 'Machine ID',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'machine_number',
                            title: 'Machine No.',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'machine_name',
                            title: 'Machine Name',
                            halign: 'center',
                            width: 200
                        },  {
                            field: 'machine_spec',
                            title: 'Specification',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'machine_maker',
                            title: 'Maker',
                            halign: 'center',
                            width: 100
                        }, {
                            field: 'machine_toonage',
                            title: 'Toonage of Machine',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'machine_tiebar',
                            title: 'Tie Bar',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'machine_uom_tiebar',
                            title: 'UOM',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'machine_min_close',
                            title: 'Minimum Closing',
                            halign: 'center',
                            width: 120
                        }, {
                            field: 'machine_uom_min_close',
                            title: 'UOM',
                            halign: 'center',
                            width: 80
                        },  {
                            field: 'machine_max_open',
                            title: 'Maximum Open',
                            halign: 'center',
                            width: 120
                        },  {
                            field: 'machine_uom_max_open',
                            title: 'UOM',
                            halign: 'center',
                            width: 80
                        },  {
                            field: 'machine_volume',
                            title: 'Barrel Volume',
                            halign: 'center',
                            width: 120
                        },  {
                            field: 'machine_uom_volume',
                            title: 'UOM',
                            halign: 'center',
                            width: 80
                        },  {
                            field: 'machine_diameter',
                            title: 'Screw Diameter',
                            halign: 'center',
                            width: 120
                        },  {
                            field: 'machine_uom_diameter',
                            title: 'UOM',
                            halign: 'center',
                            width: 80
                        },  {
                            field: 'machine_brand',
                            title: 'Brand',
                            halign: 'center',
                            width: 80
                        },  {
                            field: 'machine_status',
                            title: 'Status',
                            halign: 'center',
                            styler:cellStyler, 
                            formatter:cellFormatter,
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

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var subcont_id = $("#subcont_id").combogrid('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].machine_id) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('master/menu_loading_subconts/create') ?>',
                                data: {
                                    subcont_id: subcont_id,
                                    machine_id: rows[i].machine_id,
                                    capacity: rows[i].capacity
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
                }
            }]
        });
    });

    $('#subcont_id').combogrid({
        url: '<?= base_url('master/subconts/reads/'); ?>',
        panelWidth: 420,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose subcont",
        columns: [
            [{
                field: 'number',
                title: 'subcont Code',
                width: 120
            }, {
                field: 'name',
                title: 'subcont Name',
                width: 250
            }, ]
        ]
    });

    $('#filter_subcont_id').combogrid({
        url: '<?= base_url('master/subconts/reads'); ?>',
        panelWidth: 750,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Subcont",
        columns: [
            [{
                field: 'id',
                title: 'subcont ID',
                width: 150
            }, {
                field: 'number',
                title: 'subcont Code',
                width: 150
            }, {
                field: 'name',
                title: 'subcont Name',
                width: 200
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $('#filter_machine_id').combogrid({
        url: '<?= base_url('master/machine/reads'); ?>',
        panelWidth: 500,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Machine No.",
        columns: [
            [{
                field: 'id',
                title: 'Machine ID',
                width: 180
            }, {
                field: 'number',
                title: 'Machine No.',
                width: 150
            }, {
                field: 'name',
                title: 'Machine Name',
                width: 150
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
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
            return 'Active';
        } else {
            return 'Not Active';
        }
    };

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/menu_loading_subconts/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/menu_loading_subconts/upload') ?>',
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
                            url: "<?= base_url('master/menu_loading_subconts/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('master/menu_loading_subconts/uploadCreate') ?>",
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
                                                url: "<?= base_url('master/menu_loading_subconts/uploadcreateFailed') ?>",
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
</script>
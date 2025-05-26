<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'document_number',width:150,align:'left'">Document No.</th>
            <th rowspan="2" data-options="field:'start_date',width:120,halign:'center'">Start Date</th>
            <th rowspan="2" data-options="field:'end_date',width:120,halign:'center'">Ending Date</th>
            <th rowspan="2" data-options="field:'division_name',width:120,halign:'center'">Division</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center'"> Approved</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:100,align:'center'">By</th>
            <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
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
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="filter_division" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_rm_id" class="easyui-combogrid">
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
                    <span style="width:30%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="division" name="division" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Start Date</span>
                    <input style="width:60%;" name="start_date" id="start_date" class="easyui-datebox" required data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Ending Date</span>
                    <input style="width:60%;" name="end_date" id="end_date" class="easyui-datebox" required data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
            <div style="float: left; width:48%;">
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Document No.</span>
                    <input style="width:60%;" name="document_number" id="document_number" class="easyui-textbox" readonly required>
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Material Lists" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- Historycal -->
<div id="dlg_history" class="easyui-dialog" title="History" data-options="closed: true,modal:true" style="width: 1100px; height: 300px; top: 20px;">
    <table id="dg_history" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'start_date',width:150,align:'center'">Start Date</th>
                <th data-options="field:'end_date',width:150,align:'center'">Ending Date</th>
                <th data-options="field:'item_rm_id',width:150,align:'center'">Part ID</th>
                <th data-options="field:'item_rm_number',width:150,align:'center'">Part No</th>
                <th data-options="field:'item_rm_name',width:200,halign:'center'">Part Name</th>
                <!-- <th data-options="field:'uom',width:80,halign:'center',align:'center'">UOM</th>
                <th data-options="field:'division_name',width:150,halign:'center'">Division</th>
                <th data-options="field:'item_category_name',width:150,halign:'center'">Category</th>
                <th data-options="field:'item_family_name',width:150,halign:'center'">Product Family</th>-->
                <th data-options="field:'currency',width:80,halign:'center',align:'center'">Currency</th>
                <th data-options="field:'price',width:150,halign:'center',align:'right',formatter: priceformat">Price</th>
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
<iframe id="printout" src="<?= base_url('master/standard_price_rm/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('master/standard_price_rm/create') ?>';
        $('#frm_insert').form('clear');

        $("#division").combobox('enable', true);
        $('#start_date').datebox('setValue', '<?= date('Y') . '-01-01' ?>');
        $('#end_date').datebox('setValue', '<?= date('Y') . '-12-31' ?>');
    }

    function addTable(division = "", link = "") {
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
                    field: 'item_rm_id',
                    width: 150,
                    halign: 'center',
                    title: "Part ID",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('master/standard_price_rm/readItemByDivision/'); ?>' + division,
                            required: true,
                            panelWidth: 450,
                            idField: 'id',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Part ID',
                            columns: [
                                [{
                                    field: 'id',
                                    title: 'Part ID',
                                    width: 150
                                }, {
                                    field: 'number',
                                    title: 'Part No',
                                    width: 200
                                }, {
                                    field: 'name',
                                    title: 'Part name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_number'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_name'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_category_name'
                                });
                                var ed6 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_family_name'
                                });
                                var ed7 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'division'
                                });


                                $(ed.target).textbox('setValue', rows.number);
                                $(ed3.target).textbox('setValue', rows.name);
                                $(ed4.target).textbox('setValue', rows.uom);
                                $(ed5.target).textbox('setValue', rows.item_category_name);
                                $(ed6.target).textbox('setValue', rows.item_family_name);
                                $(ed7.target).textbox('setValue', rows.division);
                            }
                        }
                    }
                }, {
                    field: 'item_rm_number',
                    width: 150,
                    halign: 'center',
                    title: "Item Number ",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'item_rm_name',
                    width: 150,
                    halign: 'center',
                    title: "Part Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'division',
                    width: 150,

                    halign: 'center',
                    title: "Division",
                    editor: {
                        type: 'textbox'
                    },
                    hidden: true
                }, {
                    field: 'uom',
                    width: 80,
                    halign: 'center',
                    title: "Uom",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'item_category_name',
                    width: 120,
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
                    width: 120,
                    halign: 'center',
                    title: "Product Family",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'currency',
                    width: 100,
                    halign: 'center',
                    title: "Currency",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('master/currencies/reads'); ?>',
                            required: true,
                            panelWidth: 400,
                            idField: 'name',
                            textField: 'name',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Currency',
                            columns: [
                                [{
                                    field: 'symbol',
                                    title: 'Symbol',
                                    width: 80
                                }, {
                                    field: 'name',
                                    title: 'Name',
                                    width: 80
                                }, {
                                    field: 'description',
                                    title: 'Description',
                                    width: 170
                                }]
                            ]
                        }
                    },
                }, {
                    field: 'price',
                    width: 100,
                    halign: 'center',
                    align: 'right',
                    title: "Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                        }
                    }
                }, ]
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
            var ed1 = $('#dg2').datagrid('getEditor', {
                index: editIndex,
                field: 'currency'
            });
            if (ed1) {
                var text = $(ed1.target).combobox('getText'); // get combobox text
                var row = $('#dg2').datagrid('getRows')[editIndex];
                row.currency_name = text; // update field value
            }

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
        var division = $("#division").combobox('getValue');
        if (division != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    price: '0',
                    currency: 'IDR',
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Division first");
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
            url: '<?= base_url('master/standard_price_rm/deleteItem') ?>',
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

            $("#division").combobox('disable', true);
            var document_number = $("#document_number").textbox('getValue');

            $('#dg2').datagrid('loadData', []);
            addTable(row.division, '<?= base_url('master/standard_price_rm/datatableUpdates?document_number=') ?>' + window.btoa(row.document_number));
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
                            url: '<?= base_url('master/standard_price_rm/delete') ?>',
                            data: {
                                document_number: row.document_number
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
        window.location.assign('<?= base_url('template/tmp_standard_price_material.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_start_date = $("#filter_start_date").datebox('getValue');
        var filter_end_date = $("#filter_end_date").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

        var url = "?filter_start_date=" + window.btoa(filter_start_date) +
            "&filter_end_date=" + window.btoa(filter_end_date) +
            "&filter_division=" + window.btoa(filter_division) +
            "&filter_item_rm_id=" + window.btoa(filter_item_rm_id);

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/standard_price_rm/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            fitColumns: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.document_number + ' | ' + row.start_date + ' to ' + row.end_date + ' | ' + row.division_name + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('master/standard_price_rm/datatableDetails?document_number=') ?>' + window.btoa(row.document_number),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'item_rm_id',
                            title: 'Part ID',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_rm_number',
                            title: 'Part No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_rm_name',
                            title: 'Part Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            halign: 'center',
                            width: 100,
                            align: 'center'
                        }, {
                            field: 'division_name',
                            title: 'Division',
                            width: 100,
                            halign: 'center',
                            align: 'left',
                        }, {
                            field: 'item_category_name',
                            title: 'Category',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_family_name',
                            title: 'Family',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'price',
                            title: 'Price',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
                        }, {
                            field: 'btnHistory',
                            title: 'History',
                            align: 'center',
                            width: 80,
                            formatter: btnHistory
                        }, {
                            field: 'approved_to',
                            title: 'Status<br>Approve',
                            width: 80,
                            halign: 'center',
                            align: 'center',
                            styler: cellStylerApproval,
                            formatter: cellFormatterApproval
                        }, {
                            field: 'approved_by',
                            title: 'Approved<br>By',
                            width: 120,
                            halign: 'center',
                            align: 'center',
                        }, {
                            field: 'approved_date',
                            title: 'Approved<br>Date',
                            width: 150,
                            halign: 'center',
                            align: 'center',
                        }, {
                            field: 'created_by',
                            title: 'Create<br>By',
                            width: 120,
                            halign: 'center',
                            align: 'center',
                        }, {
                            field: 'created_date',
                            title: 'Create<br>Date',
                            width: 150,
                            halign: 'center',
                            align: 'center',
                        }, {
                            field: 'updated_by',
                            title: 'Update<br>By',
                            width: 120,
                            halign: 'center',
                            align: 'center',
                        }, {
                            field: 'updated_date',
                            title: 'Update<br>Date',
                            width: 150,
                            halign: 'center',
                            align: 'center',
                        }, ]
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
        $("#printout").attr('src', '<?= base_url('master/standard_price_rm/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_start_date = $("#filter_start_date").datebox('getValue');
        var filter_end_date = $("#filter_end_date").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

        var url = "?filter_start_date=" + window.btoa(filter_start_date) +
            "&filter_end_date=" + window.btoa(filter_end_date) +
            "&filter_division=" + window.btoa(filter_division) +
            "&filter_item_rm_id=" + window.btoa(filter_item_rm_id);

        window.location.assign('<?= base_url('master/standard_price_rm/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //ADD DATA
        filter();
        addTable();

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var start_date = $("#start_date").datebox('getValue');
                    var end_date = $("#end_date").datebox('getValue');
                    var document_number = $("#document_number").textbox('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_rm_id) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('master/standard_price_rm/create') ?>',
                                data: {
                                    start_date: start_date,
                                    end_date: end_date,
                                    document_number: document_number,
                                    division: rows[i].division,
                                    item_rm_id: rows[i].item_rm_id,
                                    currency: rows[i].currency,
                                    price: rows[i].price,
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

    $('#filter_division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'number',
        textField: 'name',
        prompt: 'Choose Division',
        onSelect: function(val) {
            $('#filter_item_rm_id').combogrid({
                url: '<?= base_url('master/standard_price_rm/readItemByDivision/'); ?>' + val.number,
                panelWidth: 500,
                idField: 'id',
                textField: 'number',
                mode: 'remote',
                fitColumns: true,
                prompt: "Choose Part No",
                columns: [
                    [{
                        field: 'id',
                        title: 'Part ID',
                        width: 150
                    }, {
                        field: 'number',
                        title: 'Part No',
                        width: 150
                    }, {
                        field: 'name',
                        title: 'Part Name',
                        width: 150
                    }, ]
                ],
                // icons: [{
                //     iconCls: 'icon-clear',
                //     handler: function(e) {
                //         $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                //     }
                // }],
            });
        }
    });

    $('#filter_item_rm_id').combogrid({
        url: '<?= base_url('master/item_rm/reads'); ?>',
        panelWidth: 500,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Part No",
        columns: [
            [{
                field: 'id',
                title: 'Part ID',
                width: 150
            }, {
                field: 'number',
                title: 'Part No',
                width: 150
            }, {
                field: 'name',
                title: 'Part Name',
                width: 150
            }, ]
        ],
        // icons: [{
        //     iconCls: 'icon-clear',
        //     handler: function(e) {
        //         $(e.data.target).combogrid('clear').combogrid('textbox').focus();
        //     }
        // }],
    });

    $('#division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'number',
        textField: 'name',
        prompt: 'Choose Division',
        onSelect: function(val) {
            var start_date = $('#start_date').datebox('getValue');
            var end_date = $('#end_date').datebox('getValue');
            autonumber(val.number, start_date, end_date);
            setTimeout(function() {
                var document_number = $("#document_number").textbox('getValue');

                $('#dg2').datagrid('loadData', []);
                addTable(val.number, '<?= base_url('master/standard_price_rm/datatableUpdates?document_number=') ?>' + window.btoa(document_number));
            }, 500);

        }
    });

    function autonumber(division, start_date, end_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('master/standard_price_rm/autonumber') ?>",
            data: {
                division: division,
                start_date: start_date,
                end_date: end_date,
            },
            dataType: "html",
            success: function(result) {
                $("#document_number").textbox('setValue', result);
            }
        });
    }

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/standard_price_rm/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/standard_price_rm/upload') ?>',
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
                            url: "<?= base_url('master/standard_price_rm/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('master/standard_price_rm/uploadCreate') ?>",
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
                                                url: "<?= base_url('master/standard_price_rm/uploadcreateFailed') ?>",
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

    function priceformat(value, row) {
        if (row.currency == "USD") {
            var digits = 4;
            var currency = 'USD';
            var format = "en-IN";
        } else if (row.currency == "JPY") {
            var digits = 2;
            var currency = 'JPY';
            var format = "ja-JP";
        } else if (row.currency == "EUR") {
            var digits = 2;
            var currency = 'EUR';
            var format = "de-DE";
        } else {
            var digits = 0;
            var currency = 'IDR';
            var format = "id-ID";
        }
        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: digits
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }


    function cellStylerApproval(value, row, index) {
        if (row.approved_to == "" || row.approved_to == null) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterApproval(val, row) {
        if (row.approved_to == "" || row.approved_to == null) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    function btnHistory(val, row) {
        var history = "viewHistory('" + row.start_date + "','" + row.end_date + "','" + row.item_rm_id + "')";
        return '<a class="btn btn-primary w-100" onClick="' + history + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewHistory(start_date, end_date, item_rm_id) {
        $("#dlg_history").dialog('open');
        $('#dg_history').datagrid({
            url: '<?= base_url('master/standard_price_rm/datatableHistory?start_date=') ?>' + btoa(start_date) + '&end_date=' + btoa(end_date) + "&item_rm_id=" + btoa(item_rm_id),
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
</script>
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'document',width:150,halign:'center'">Document No</th>
            <th rowspan="2" data-options="field:'departement',width:120,halign:'center'">Departement</th>
            <th rowspan="2" data-options="field:'process',width:120,halign:'center'">Process</th>
            <th rowspan="2" data-options="field:'type',width:100,halign:'center'">NG Type</th>
            <th rowspan="2" data-options="field:'workorder',width:150,halign:'center'">Work Order</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'stock',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">NG</th>
            <th rowspan="2" data-options="field:'scrap',width:80,halign:'center',align:'right',formatter:numberformat">Scrap</th>
            <th rowspan="2" data-options="field:'balance',width:80,halign:'center',align:'right',formatter:numberformat">Balance</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center'">Remarks</th>
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

<div id="toolbar" style="height: 260px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width: 100%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" id="filter_document" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_family_id" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
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

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1100px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:60%;" name="trans_date" id="trans_date" class="easyui-datebox" required data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" name="document" id="document" class="easyui-textbox" readonly required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Departement</span>
                    <input style="width:60%;" name="departement" id="departement" class="easyui-textbox" required>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Process</span>
                    <input style="width:60%;" name="process" id="process" class="easyui-textbox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">NG Type</span>
                    <input style="width:60%;" name="type" id="type" class="easyui-textbox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Workorder</span>
                    <input style="width:60%;" name="workorder" id="workorder" class="easyui-combogrid" required>
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="NG Transaction List" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- Update -->
<div id="dlg_update" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:30%;" name="stock" id="stock" class="easyui-numberbox" data-options="precision:2" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">NG</span>
                <input style="width:30%;" name="qty" id="qty" class="easyui-numberbox" data-options="precision:2" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Scrap</span>
                <input style="width:30%;" name="scrap" id="scrap" class="easyui-numberbox" data-options="precision:2" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Balance</span>
                <input style="width:30%;" name="balance" id="balance" class="easyui-numberbox" data-options="precision:2" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%; height: 50px;" name="remarks" id="remarks" class="easyui-textbox" multiline="true">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('production/item_ng/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');
        $("#trans_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#departement").textbox('setValue', "PRODUCTION");
        $('#dg2').datagrid('loadData', []);
    }

    function addTable(workorder) {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            singleSelect: true,
            columns: [
                [{
                    field: 'item_number',
                    width: 250,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('production/item_ng/readItems/') ?>' + window.btoa(workorder),
                            required: true,
                            panelWidth: 350,
                            idField: 'number',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product',
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Product No',
                                    width: 150
                                }, {
                                    field: 'name',
                                    title: 'Product Name',
                                    width: 150
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_id'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_name'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'stock'
                                });

                                $(ed.target).textbox('setValue', rows.id);
                                $(ed2.target).textbox('setValue', rows.name);
                                $(ed3.target).textbox('setValue', rows.uom);
                                $(ed4.target).numberbox('setValue', rows.qty);
                            }
                        }
                    }
                }, {
                    field: 'item_name',
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
                    field: 'item_id',
                    hidden: true,
                    width: 100,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox'
                    }
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
                    field: 'stock',
                    width: 80,
                    halign: 'center',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            readonly: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'qty',
                    width: 80,
                    halign: 'center',
                    title: "NG",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 2,
                            onChange: function(valQty) {
                                var row = $('#dg2').datagrid('getSelected');
                                var rowIndex = $('#dg2').datagrid('getRowIndex', row);

                                var ed = $('#dg2').datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'stock'
                                });

                                var ed2 = $('#dg2').datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'scrap'
                                });

                                var ed3 = $('#dg2').datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'balance'
                                });

                                var stock = $(ed.target).numberbox('getValue');
                                var scrap = $(ed2.target).numberbox('getValue');
                                $(ed3.target).numberbox('setValue', (parseInt(stock) - (parseInt(valQty) + parseInt(scrap))));
                            }
                        }
                    }
                }, {
                    field: 'scrap',
                    width: 80,
                    halign: 'center',
                    title: "Scrap",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 2,
                            onChange: function(valScrap) {
                                var row = $('#dg2').datagrid('getSelected');
                                var rowIndex = $('#dg2').datagrid('getRowIndex', row);

                                var ed = $('#dg2').datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'stock'
                                });

                                var ed2 = $('#dg2').datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty'
                                });

                                var ed3 = $('#dg2').datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'balance'
                                });

                                var stock = $(ed.target).numberbox('getValue');
                                var qty = $(ed2.target).numberbox('getValue');
                                $(ed3.target).numberbox('setValue', (parseInt(stock) - (parseInt(valScrap) + parseInt(qty))));
                            }
                        }
                    }
                }, {
                    field: 'balance',
                    width: 80,
                    halign: 'center',
                    title: "Balance",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            readonly: true,
                            precision: 2
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
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dg2').datagrid('getEditors', rowIndex);
            }
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

    function append() {
        var workorder = $("#workorder").combogrid('getValue');
        if (workorder != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    stock: '0',
                    qty: '0',
                    scrap: '0',
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Workorder first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_update').dialog('open');
            $('#frm_update').form('load', row);

            url_save = '<?= base_url('production/item_ng/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('production/item_ng/delete') ?>',
                            data: {
                                id: row.id
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
        var filter_document = $("#filter_document").combobox('getValue');
        var filter_family_id = $("#filter_family_id").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_document=" + filter_document + "&filter_family_id" + filter_family_id + "&filter_item_id=" + filter_item_id;
        $('#dg').datagrid({
            url: '<?= base_url('production/item_ng/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('production/item_ng/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_document = $("#filter_document").combobox('getValue');
        var filter_family_id = $("#filter_family_id").combobox('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_document=" + filter_document + "&filter_family_id" + filter_family_id + "&filter_item_id=" + filter_item_id;

        window.location.assign('<?= base_url('production/item_ng/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('production/item_ng/datatables') ?>',
            pagination: true,
            rownumbers: true
        });

        $("#trans_date").datebox({
            onChange: function(val) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('production/item_ng/item_ng_no/') ?>" + window.btoa(val),
                    dataType: "html",
                    success: function(scraps_no) {
                        $("#document").textbox('setValue', scraps_no);
                    }
                });
            }
        });

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var trans_date = $("#trans_date").datebox('getValue');
                    var document = $("#document").textbox('getValue');
                    var departement = $("#departement").textbox('getValue');
                    var process = $("#process").textbox('getValue');
                    var type = $("#type").textbox('getValue');
                    var workorder = $("#workorder").combogrid('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();
                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_id) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('production/item_ng/create') ?>',
                                data: {
                                    trans_date: trans_date,
                                    document: document,
                                    departement: departement,
                                    process: process,
                                    type: type,
                                    workorder: workorder,
                                    item_id: rows[i].item_id,
                                    stock: rows[i].stock,
                                    qty: rows[i].qty,
                                    scrap: rows[i].scrap,
                                    balance: rows[i].balance,
                                    uom: rows[i].uom,
                                    remarks: rows[i].remarks
                                },
                                dataType: "json",
                                success: function(result) {
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
                            });
                        }
                    }
                    $('#dlg_insert').dialog('close');
                }
            }]
        });

        $('#dlg_update').dialog({
            buttons: [{
                text: 'Update',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_update').form('submit', {
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
                            $('#dlg_update').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#qty').numberbox({
            onChange: function(val) {
                var stock = $('#stock').numberbox('getValue');
                var scrap = $('#scrap').numberbox('getValue');
                $("#balance").numberbox('setValue', parseInt(stock) - (parseInt(val) + parseInt(scrap)));
            }
        });

        $('#scrap').numberbox({
            onChange: function(val) {
                var stock = $('#stock').numberbox('getValue');
                var qty = $('#qty').numberbox('getValue');
                $("#balance").numberbox('setValue', parseInt(stock) - (parseInt(val) + parseInt(qty)));
            }
        });

        $('#workorder').combogrid({
            url: '<?= base_url('production/item_ng/readWorkorders') ?>',
            panelWidth: 350,
            idField: 'workorder',
            textField: 'workorder',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Workorder",
            columns: [
                [{
                    field: 'workorder',
                    title: 'Workorder',
                    width: 150
                }, {
                    field: 'wp',
                    title: 'WP',
                    width: 80,
                    align: 'center'
                }]
            ],
            onSelect: function(val, row) {
                addTable(row.workorder);
            }
        });

        //Get Product Family
        $("#filter_family_id").combobox({
            url: '<?= base_url('master/item_familys/readNotFg') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Product Family",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row) {
                $('#filter_item_id').combogrid({
                    url: '<?= base_url('master/items/reads/') ?>' + row.number,
                    panelWidth: 500,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Product No",
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
                            width: 150
                        }, {
                            field: 'name',
                            title: 'Product Name',
                            width: 200
                        }]
                    ],
                });
            }
        });

        $("#filter_document").combobox({
            url: '<?= base_url('production/item_ng/readDocument') ?>',
            valueField: 'document',
            textField: 'document',
            prompt: "Choose Document",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
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

    //Number Format Currency
    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }
</script>
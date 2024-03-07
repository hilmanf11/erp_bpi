<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Journal Name is taken from <b>Master Data > Accounting & Finance > Journal Type</b></li>
                <li>The Data Account No is taken from <b>Master Data > Accounting & Finance > Chart of Account</b></li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'journal_name',width:250,halign:'center'">Journal Name</th>
            <th rowspan="2" data-options="field:'account_number',width:100,halign:'center'">Account No</th>
            <th rowspan="2" data-options="field:'account_name',width:200,halign:'center'">Account Name</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center'">Debit/Credit</th>
            <th rowspan="2" data-options="field:'ap_payment',width:100,align:'center'">Ap Payment</th>
            <th rowspan="2" data-options="field:'ar_receipt',width:100,align:'center'">AR Receipt</th>
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

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 700px; height: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Journal Name</span>
                <input style="width:60%;" name="journal_type_id" id="journal_type_id" required="" class="easyui-combobox">
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Journal Setup List" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('finance/journal_setups/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');
        $('#dg2').datagrid('loadData', []);
        $("#journal_type_id").combobox('enable');
        addTable();
    }

    function addTable(link = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'account_number',
                    width: 100,
                    halign: 'center',
                    title: "Account No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('finance/account_coa/reads') ?>',
                            panelWidth: 320,
                            idField: 'account_number',
                            textField: 'account_number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Account No',
                            columns: [
                                [{
                                    field: 'account_number',
                                    title: 'Account No',
                                    width: 100
                                }, {
                                    field: 'account_name',
                                    title: 'Account Name',
                                    width: 200
                                }, ]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'account_name'
                                });

                                $(ed.target).textbox('setValue', rows.account_name);
                            }
                        }
                    }
                }, {
                    field: 'account_name',
                    width: 250,
                    readonly: true,
                    halign: 'center',
                    title: "Account Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'status',
                    width: 80,
                    readonly: true,
                    halign: 'center',
                    title: "Status",
                    editor: {
                        type: 'combobox',
                        options: {
                            valueField: 'name',
                            textField: 'name',
                            panelHeight: 'auto',
                            required: true,
                            data: [{
                                    "name": "DEBIT"
                                },
                                {
                                    "name": "CREDIT"
                                },
                            ],
                            prompt: "Choose Status",
                        }
                    }
                }, {
                    field: 'ap_payment',
                    width: 100,
                    readonly: true,
                    halign: 'center',
                    title: "Ap Payment",
                    editor: {
                        type: 'combobox',
                        options: {
                            data: [{
                                'id': 'YES'
                            }, {
                                'id': 'NO'
                            }],
                            valueField: 'id',
                            textField: 'id',
                            prompt: 'Choose AP',
                            panelHeight: 'auto'
                        }
                    }
                }, {
                    field: 'ar_receipt',
                    width: 100,
                    readonly: true,
                    halign: 'center',
                    title: "Ar Receipt",
                    editor: {
                        type: 'combobox',
                        options: {
                            data: [{
                                'id': 'YES'
                            }, {
                                'id': 'NO'
                            }],
                            valueField: 'id',
                            textField: 'id',
                            prompt: 'Choose AP',
                            panelHeight: 'auto'
                        }
                    }
                }, {
                    field: 'flag',
                    width: 80,
                    readonly: true,
                    halign: 'center',
                    title: "Index",
                    editor: {
                        type: 'numberbox',
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
        var journal_type_id = $("#journal_type_id").combobox('getValue');
        if (journal_type_id != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Journal Name first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return true;
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if (row.datatable == "1") {
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                $("#journal_type_id").combobox('disable');
                addTable('<?= base_url('finance/journal_setups/datatable_updates?journal_type_id=') ?>' + window.btoa(row.journal_type_id));
            } else {
                toastr.error("Please Select Header of Journal <br>" + row.journal_name);
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if (row.state == "closed") {
                            toastr.error("Please Select Detail of Journal <br>" + row.journal_name);
                        } else {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('finance/journal_setups/delete') ?>',
                                data: {
                                    id: row.id
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');

                                    if (i == rows.length) {
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
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    toastr.error(jqXHR.statusText);
                                    $.messager.alert("Error", jqXHR.statusText, 'error');
                                },
                            });
                        }
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('finance/journal_setups/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').treegrid({
            url: '<?= base_url('finance/journal_setups/datatables') ?>',
            pagination: false,
            rownumbers: true,
            idField: 'id',
            treeField: 'journal_name',
            singleSelect: false,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
            rowStyler: function(row) {
                if (row.state != "closed") {
                    return 'background-color:#CFE6FF;font-weight:bold;';
                }
            },
        }).treegrid('enableFilter');

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var journal_type_id = $("#journal_type_id").combobox('getValue');

                    $('#dg2').datagrid('acceptChanges');
                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].account_number) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('finance/journal_setups/create') ?>',
                                data: {
                                    journal_type_id: journal_type_id,
                                    account_number: rows[i].account_number,
                                    ap_payment: rows[i].ap_payment,
                                    ar_receipt: rows[i].ar_receipt,
                                    status: rows[i].status,
                                    flag: rows[i].flag
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
                    $('#dg').treegrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });


        $("#journal_type_id").combobox({
            url: '<?= base_url('finance/journal_types/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Journal Type"
        });
    });
</script>
<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'menu_name',width:250">Menu Name</th>
            <th data-options="field:'m_view',width:100, styler:cellStyler, formatter:cellFormatter">View</th>
            <th data-options="field:'m_add',width:100, styler:cellStyler, formatter:cellFormatter">Add</th>
            <th data-options="field:'m_edit',width:100, styler:cellStyler, formatter:cellFormatter">Edit</th>
            <th data-options="field:'m_delete',width:100, styler:cellStyler, formatter:cellFormatter">Delete</th>
            <th data-options="field:'m_upload',width:100, styler:cellStyler, formatter:cellFormatter">Upload</th>
            <th data-options="field:'m_download',width:100, styler:cellStyler, formatter:cellFormatter">Download</th>
            <th data-options="field:'m_print',width:100, styler:cellStyler, formatter:cellFormatter">Print</th>
            <th data-options="field:'m_excel',width:100, styler:cellStyler, formatter:cellFormatter">Excel</th>
        </tr>
    </thead>
</table>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Menu Name</span>
                <input style="width:200px;" name="menus_id" required="" id="menus_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">View</span>
                <input class="easyui-switchbutton" name="m_view" data-options="onText:'Yes', offText:'No'">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Add</span>
                <input class="easyui-switchbutton" name="m_add" data-options="onText:'Yes', offText:'No'">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Edit</span>
                <input class="easyui-switchbutton" name="m_edit" data-options="onText:'Yes', offText:'No'">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Delete</span>
                <input class="easyui-switchbutton" name="m_delete" data-options="onText:'Yes', offText:'No'">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Upload</span>
                <input class="easyui-switchbutton" name="m_upload" data-options="onText:'Yes', offText:'No'">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Download</span>
                <input class="easyui-switchbutton" name="m_download" data-options="onText:'Yes', offText:'No'">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Print</span>
                <input class="easyui-switchbutton" name="m_print" data-options="onText:'Yes', offText:'No'">
            </div>
            <div class="fitem">
                <span style="width:30%; display:inline-block;">Excel</span>
                <input class="easyui-switchbutton" name="m_excel" data-options="onText:'Yes', offText:'No'">
            </div>
        </fieldset>
    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('admin/setting_menus/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('admin/setting_menus/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('admin/setting_menus/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.info("Please select one of the data in the table first");
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
                            url: '<?= base_url('admin/setting_menus/delete') ?>',
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
            toastr.info("Please select one of the data in the table first");
        }
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //EXPORT EXCEL
    function excel() {
        window.location.assign('<?= base_url('admin/setting_menus/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    function cellStyler(value, row, index) {
        if (value == "on") {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatter(val) {
        if (val == "on") {
            return 'Active';
        } else {
            return 'Not Active';
        }
    };
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('admin/setting_menus/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');
        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
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
                            // $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });
        //DATA MENU
        $('#menus_id').combogrid({
            url: '<?= base_url('admin/setting_menus/getmenu') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Menu",
            columns: [
                [{
                    field: 'name',
                    title: 'Name',
                    width: 200
                }, {
                    field: 'parent_name',
                    title: 'Parent Menu',
                    width: 200
                }, ]
            ]
        });
    });
</script>
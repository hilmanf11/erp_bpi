<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'id',width:80,align:'center'">ID</th>
            <th rowspan="2" data-options="field:'phase_name',width:200,halign:'center'">Phase Name</th>
            <th rowspan="2" data-options="field:'division',width:80,halign:'center'">Division</th>
            <th rowspan="2" data-options="field:'phase_name_sub',width:200,halign:'center'">Phase Name Subs</th>
            <th rowspan="2" data-options="field:'section',width:100,halign:'center'">Section</th>
            <th rowspan="2" data-options="field:'module',width:150,halign:'center'">Module</th>
            <th rowspan="2" data-options="field:'link',width:150,halign:'center'">Link</th>
            <th rowspan="2" data-options="field:'assign',width:150,halign:'center'">Assign</th>
            <th rowspan="2" data-options="field:'checked_by',width:150,halign:'center'">Checked</th>
            <th rowspan="2" data-options="field:'approve',width:150,halign:'center'">Approve</th>
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
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">ID</span>
                <input style="width:40%;" name="id" id="id" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Phase Id</span>
                <input style="width:60%;" name="phase_id" id="phase_id" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Phase Name</span>
                <input style="width:60%;" name="phase_name" id="phase_name" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Division</span>
                <input style="width:60%;" name="division_id" id="division_id" class="easyui-combobox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Phase Name Sub</span>
                <input style="width:60%;" name="phase_name_sub" id="phase_name_sub" class="easyui-textbox" required>
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Menu ID</span>
                <input style="width:60%;" name="menus_id" id="menus_id" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Module</span>
                <input style="width:60%;" name="module" id="module" class="easyui-combobox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Link</span>
                <input style="width:60%;" name="link" id="link" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Section</span>
                <input style="width:60%;" name="section" id="section" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Assign</span>
                <input style="width:60%;" name="assign" id="assign" class="easyui-combobox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Checked</span>
                <input style="width:60%;" name="checked_by" id="checked_by" class="easyui-combobox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Approve</span>
                <input style="width:60%;" name="approve" id="approve" class="easyui-combobox" required>
            </div>
            <!-- <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select class="easyui-combobox" name="status" id="status" style="width:60%;" data-options="
                    prompt:'<Active/Inactive>',
                    valueField: 'value',
                    textField: 'text',
                    data: [{
                        text: 'Active',
                        value: '0'
                    },{
                        text: 'Inactive',
                        value: '1'
                    }]
                ">
                </select>
            </div> -->
        </fieldset>
    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('npd/project_phase_subs/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('npd/project_phase_subs/create') ?>';
        $('#frm_insert').form('clear');
        
        $.ajax({
            type : "post",
            url : "<?= base_url('npd/project_phase_subs/autoid')?>",
            dataType : "html",
            success : function(response){
                $('#id').textbox('setValue', response);
            }
        });
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('npd/project_phase_subs/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('npd/project_phase_subs/delete') ?>',
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
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('npd/project_phase_subs/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('npd/project_phase_subs/datatables') ?>',
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
                            
                            $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });
    });

    $('#phase_name').combobox({
        url:'<?= base_url('npd/project_phases/reads'); ?>',
        valueField:'name',
        textField:'name',
        prompt: 'Choose Phase Name',
        onSelect: function(phase_name){
            $('#phase_id').textbox('setValue',phase_name.id);
        }
    });
    
    $('#division_id').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'id',
        textField: 'number',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
    });

    $('#module').combobox({
        url: '<?= base_url('npd/project_phase_subs/readMenus'); ?>',
        valueField: 'module',
        textField: 'module',
        prompt: 'Choose Module',
        onSelect: function(menu){
            $('#link').textbox('setValue',menu.link);
            $('#menus_id').textbox('setValue',menu.menus_id);
        }
    });

    $('#assign').combobox({
        url: '<?= base_url('npd/project_phase_subs/readUsers'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Assign',
    });

    $('#checked_by').combobox({
        url: '<?= base_url('npd/project_phase_subs/readUsers'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Checked',
    });

    $('#approve').combobox({
        url: '<?= base_url('npd/project_phase_subs/readUsers'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Approve',
    });

    $('#section').combobox({
        url: '<?= base_url('master/sub_departments/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Section',
    });

    function statusformat(value, row) {
        // active=0 / inactive=1
        if (value == '1') {
            return "<b style='color:red;'>INACTIVE</b>";
        } else if (value == 'footer') {
            return "";
        } else {
            return "<b style='color:green;'>ACTIVE</b>";
        }
    }
    function statusStyle(value, row, index) {
        if (value == '1') {
            return 'background-color:#FFC8C8;';
        } else if (value == 'footer') {
            return "";
        } else {
            return 'background-color:#C8FFCC;';
        }
    }
</script>
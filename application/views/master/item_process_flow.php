<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'id',width:80,align:'center'">ID</th>
            <th rowspan="2" data-options="field:'item_process_id',width:150,halign:'center'">Process ID</th>
            <th rowspan="2" data-options="field:'item_process_name',width:150,halign:'center'">Process Name</th>
            <th rowspan="2" data-options="field:'type_a',width:100,halign:'center'">Type A</th>
            <th rowspan="2" data-options="field:'type_b',width:100,halign:'center'">Type B</th>
            <th rowspan="2" data-options="field:'type_c',width:100,halign:'center'">Type C</th>
            <th rowspan="2" data-options="field:'type_d',width:100,halign:'center'">Type D</th>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">ID</span>
                <input style="width:60%;" name="id" id="id" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Process Name</span>
                <input style="width:60%;" name="item_process_id" id="item_process_id" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type A</span>
                <input style="width:60%;" name="type_a" id="type_a" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type B</span>
                <input style="width:60%;" name="type_b" id="type_b" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type C</span>
                <input style="width:60%;" name="type_c" id="type_c" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type D</span>
                <input style="width:60%;" name="type_d" id="type_d" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/item_process_flow/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/item_process_flow/create') ?>';
        $('#frm_insert').form('clear');
        
        $.ajax({
            type : "post",
            url : "<?= base_url('master/item_process_flow/autoid')?>",
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
            url_save = '<?= base_url('master/item_process_flow/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/item_process_flow/delete') ?>',
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
        window.location.assign('<?= base_url('master/item_process_flow/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/item_process_flow/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true
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

    $('#item_process_id').combobox({
        url:'<?= base_url('master/item_process/reads'); ?>',
        valueField:'id',
        textField:'name',
        prompt: 'Choose Process',
    });
</script>
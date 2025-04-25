<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:100">Number ID</th>
            <th rowspan="2" data-options="field:'name',width:200">Name</th>
            <th rowspan="2" data-options="field:'username',width:150">Username</th>
            <th rowspan="2" data-options="field:'email',width:200">Email</th>
            <th rowspan="2" data-options="field:'phone',width:150">Phone</th>
            <th rowspan="2" data-options="field:'position',width:150">Position</th>
            <th rowspan="2" data-options="field:'division',width:100">Division</th>
            <th rowspan="2" data-options="field:'department',width:100">Department</th>
            <th rowspan="2" data-options="field:'sub_department',width:150">Sub Department</th>
            <th rowspan="2" data-options="field:'avatar',width:100">File Foto</th>
            <th rowspan="2" data-options="field:'api_key',width:250">API Key</th>
            <th rowspan="2" data-options="field:'actived',width:80, styler:cellStyler, formatter:cellFormatter">Status</th>
            <th rowspan="2" data-options="field:'access',width:100, styler:cellStyler2, formatter:cellFormatter2">Access</th>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">User ID</span>
                    <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Fullname</span>
                    <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Username</span>
                    <input style="width:60%;" name="username" id="username" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Password</span>
                    <input style="width:60%;" name="password" id="password" required="" class="easyui-passwordbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Full Access</span>
                    <input class="easyui-checkbox" id="full_access">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" name="division" id="division" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Department</span>
                    <input style="width:60%;" name="department" id="department" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sub Department</span>
                    <input style="width:60%;" name="sub_department" id="sub_department" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Position</span>
                    <input style="width:60%;" name="position" id="position" required="true" data-options="prompt:'Staff Production'" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Email</span>
                    <input style="width:60%;" name="email" id="email" required="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Phone</span>
                    <input style="width:60%;" name="phone" id="phone" data-options="buttonText:'+62',buttonAlign:'left'" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Foto Profile</span>
                    <input style="width:60%;" name="avatar" id="avatar" class="easyui-filebox" accept=".jpg, .png, .jpeg">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" name="actived" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="0">Active</option>
                        <option value="1">Not Active</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Access</span>
                    <select style="width:60%;" name="access" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="0">Full Access</option>
                        <option value="1">Limited</option>
                    </select>
                </div>
            </div>
        </fieldset>
    </form>
</div>
<!-- PRINT PDF -->
<iframe id="printout" src="<?= base_url('admin/users/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#number').textbox('enable');
        $('#username').textbox('enable');
        $('#password').textbox('enable');
        url_save = '<?= base_url('admin/users/create') ?>';
        $('#frm_insert').form('clear');

        $("#full_access").checkbox({
            onChange: function(checked) {
                if (checked) {
                    $('#division').combobox('disable').combobox('clear');
                    $('#department').combobox('disable').combobox('clear');
                    $('#sub_department').combobox('disable').combobox('clear');
                } else {
                    $('#division').combobox('enable');
                    $('#department').combobox('enable');
                    $('#sub_department').combobox('enable');
                }   
            }
        });
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $('#number').textbox('disable');
            $('#username').textbox('disable');
            $('#password').textbox('disable');
            
            url_save = '<?= base_url('admin/users/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('admin/users/delete') ?>',
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
        window.location.assign('<?= base_url('admin/users/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('admin/users/datatables') ?>',
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
        
        $('#division').combobox({
            url: '<?= base_url('master/divisions/reads'); ?>',
            valueField: 'number',
            textField: 'number',
            prompt: 'Choose Division',
        }); 

        $('#department').combobox({
            url: '<?= base_url('admin/users/readDepartement'); ?>',
            valueField: 'name',
            textField: 'name',
            prompt: 'Choose Department',
            onSelect: function(departement){
                $('#sub_department').combobox({
                    url: '<?= base_url('admin/users/readDepartementSub/'); ?>' + departement.id,
                    valueField: 'name',
                    textField: 'name',
                    prompt: 'Choose Sub Department',
                }); 
            }
        });
    });

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellStyler2(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FAD277; color:white;';
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

    function cellFormatter2(value) {
        if (value == 0) {
            return 'Full Access';
        } else {
            return 'Limited';
        }
    };
</script>
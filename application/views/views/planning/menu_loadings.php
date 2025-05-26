<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'item_fg_id',width:150,align:'center'">Product ID</th>
            <th data-options="field:'item_fg_number',width:150,halign:'center'">Product No.</th>
            <th data-options="field:'item_fg_name',width:200,halign:'center'">Product Name</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'machine_number',width:150,halign:'center'">Machine No.</th>
            <th rowspan="2" data-options="field:'machine_toonage',width:150,halign:'center'">Toonage of Machine </th>
            <th rowspan="2" data-options="field:'mold_id',width:150,halign:'center'">Mold ID</th>
            <th rowspan="2" data-options="field:'mold_cavity_actual',width:100,halign:'center'">Cavity Actual</th>
            <th rowspan="2" data-options="field:'mold_cavity_standard',width:120,halign:'center'">Cavity Standard</th>
            <th rowspan="2" data-options="field:'shift',width:100,halign:'center'">Shift</th>
            <th rowspan="2" data-options="field:'shift_hour',width:100,halign:'center'">Hour/Shift</th>
            <th rowspan="2" data-options="field:'productcivity',width:100,halign:'center'">Productivity <br>Factor (%)</th>
            <th rowspan="2" data-options="field:'cycle_time',width:90,halign:'center'">Cycle Time <br>(Second)</th>
            <th rowspan="2" data-options="field:'cycle_time_process',width:150,halign:'center'">Cycle Time Second <br>Process</th>
            <th rowspan="2" data-options="field:'manpower',width:100,halign:'center'">Man Power</th>
            <th rowspan="2" data-options="field:'runner',width:110,halign:'center'">Runner/Shoot</th>
            <th rowspan="2" data-options="field:'priority',width:110,halign:'center'">Priority</th>
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
    <form id="frm_insert" method="post" novalidate enctype="multipart/form-data">
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No.</span>
                <input style="width:60%;" name="item_fg_id" id="item_fg_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Mold ID</span>
                <input style="width:60%;" name="mold_id" id="mold_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Machine No.</span>
                <input style="width:60%;" name="machine_id" id="machine_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Shift</span>
                <input style="width:60%;" name="shift" id="shift" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Shift/Hour</span>
                <input style="width:60%;" name="shift_hour" id="shift_hour" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Productivity Factor</span>
                <input style="width:60%;" name="productcivity" id="productcivity" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cycle Time</span>
                <input style="width:60%;" name="cycle_time" id="cycle_time" precision="2" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cycle Time Process</span>
                <input style="width:60%;" name="cycle_time_process" id="cycle_time_process" precision="2" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Man Power</span>
                <input style="width:30%;" name="manpower" id="manpower" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Runner/Shoot</span>
                <input style="width:30%;" name="runner" id="runner" precision="5" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Priority</span>
                <input style="width:30%;" name="priority" id="priority" class="easyui-numberbox">
            </div>
        </fieldset>
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
<iframe id="printout" src="<?= base_url('master/menu_loadings/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/menu_loadings/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/menu_loadings/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/menu_loadings/delete') ?>',
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
    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_menu_loadings.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/menu_loadings/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/menu_loadings/datatables') ?>',
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

    $('#item_fg_id').combobox({
        url:'<?= base_url('master/item_fg/reads/'); ?>',
        valueField:'id',
        textField:'number',
        prompt: 'Choose Product No.',
        onSelect: function(item_fg){
            $('#mold_id').combobox({
                url:'<?= base_url('master/molds/reads/'); ?>' + btoa(item_fg.id),
                valueField:'id',
                textField:'id',
                prompt: 'Choose Mold ID',
            });
        }
    });

    $('#machine_id').combobox({
        url:'<?= base_url('master/machines/reads/'); ?>',
        valueField:'id',
        textField:'number',
        prompt: 'Choose Machine No.',
    });

    // UPLOAD DATA
    $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('master/menu_loadings/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('master/menu_loadings/upload') ?>',
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
                                url: "<?= base_url('master/menu_loadings/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('master/menu_loadings/uploadCreate') ?>",
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
                                                    url: "<?= base_url('master/menu_loadings/uploadcreateFailed') ?>",
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
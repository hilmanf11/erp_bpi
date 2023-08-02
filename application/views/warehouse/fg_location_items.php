<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'location',width:150,halign:'center'">Location</th>
            <th rowspan="2" data-options="field:'area',width:150,halign:'center'">Area</th>
            <th rowspan="2" data-options="field:'rack',width:150,halign:'center'">Rack</th>
            <th rowspan="2" data-options="field:'level',width:150,halign:'center'">Level</th>
            <th rowspan="2" data-options="field:'level_sub',width:150,halign:'center'">Level Sub</th>
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
                <span style="width:35%; display:inline-block;">Type</span>
                <input style="width:30%;" name="type" id="type" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="item_id" id="item_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Location</span>
                <input style="width:60%;" name="location" id="location" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Area</span>
                <input style="width:60%;" name="area" id="area" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Rack</span>
                <input style="width:60%;" name="rack" id="rack" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Level</span>
                <input style="width:60%;" name="level" id="level" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Level Sub</span>
                <input style="width:60%;" name="level_sub" id="level_sub" required="" class="easyui-combobox">
            </div>
        </fieldset>
    </form>
</div>
<!-- DIALOG UPLOAD -->
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
<iframe id="printout" src="<?= base_url('warehouse/fg_location_items/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('warehouse/fg_location_items/create') ?>';
        $('#frm_insert').form('clear');
        $('#type').textbox('setValue', 'FG');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('warehouse/fg_location_items/update') ?>?id=' + btoa(row.id);
            $("#location").combobox({
                url: '<?= base_url('warehouse/fg_locations/readLocations') ?>',
                valueField: 'location',
                textField: 'location',
                prompt: "Choose Location",
                onLoadSuccess: function() {
                    $("#location").combobox('setValue', row.location);
                },
                onSelect: function(loc) {
                    $("#area").combobox({
                        url: '<?= base_url('warehouse/fg_locations/readAreas?location=') ?>' + loc.location,
                        valueField: 'area',
                        textField: 'area',
                        prompt: "Choose Area",
                        onLoadSuccess: function() {
                            $("#area").combobox('setValue', row.area);
                        },
                        onSelect: function(ar) {
                            $("#rack").combobox({
                                url: '<?= base_url('warehouse/fg_locations/readRacks?location=') ?>' + loc.location + "&area=" + ar.area,
                                valueField: 'rack',
                                textField: 'rack',
                                prompt: "Choose Rack",
                                onLoadSuccess: function() {
                                    $("#rack").combobox('setValue', row.rack);
                                },
                                onSelect: function(rc) {
                                    $("#level").combobox({
                                        url: '<?= base_url('warehouse/fg_locations/readLevels?location=') ?>' + loc.location + "&area=" + ar.area + "&rack=" + rc.rack,
                                        valueField: 'level',
                                        textField: 'level',
                                        prompt: "Choose Level",
                                        onLoadSuccess: function() {
                                            $("#level").combobox('setValue', row.level);
                                        },
                                        onSelect: function(lv) {
                                            $("#level_sub").combobox({
                                                url: '<?= base_url('warehouse/fg_locations/readLevelSubs?location=') ?>' + loc.location + "&area=" + ar.area + "&rack=" + rc.rack + "&level=" + lv.level,
                                                valueField: 'level_sub',
                                                textField: 'level_sub',
                                                prompt: "Choose Level Sub",
                                                onLoadSuccess: function() {
                                                    $("#level_sub").combobox('setValue', row.level_sub);
                                                },
                                            });
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            });
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
                            url: '<?= base_url('warehouse/fg_location_items/delete') ?>',
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
    //UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    //DOWNLOAD TEMPLATE UPLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_location_items.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('warehouse/fg_location_items/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/fg_location_items/datatables') ?>',
            pagination: true,
            rownumbers: true
        });
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
        //UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('warehouse/fg_location_items/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('warehouse/fg_location_items/upload') ?>',
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
                                url: "<?= base_url('warehouse/fg_location_items/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('warehouse/fg_location_items/uploadCreate') ?>",
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
                                                    url: "<?= base_url('warehouse/fg_location_items/uploadcreateFailed') ?>",
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
        $('#item_id').combogrid({
            url: '<?= base_url('master/items/reads/001') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product",
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }, ]
            ]
        });
        $("#location").combobox({
            url: '<?= base_url('warehouse/fg_locations/readLocations') ?>',
            valueField: 'location',
            textField: 'location',
            prompt: "Choose Location",
            onSelect: function(loc) {
                $("#area").combobox({
                    url: '<?= base_url('warehouse/fg_locations/readAreas?location=') ?>' + loc.location,
                    valueField: 'area',
                    textField: 'area',
                    prompt: "Choose Area",
                    onSelect: function(ar) {
                        $("#rack").combobox({
                            url: '<?= base_url('warehouse/fg_locations/readRacks?location=') ?>' + loc.location + "&area=" + ar.area,
                            valueField: 'rack',
                            textField: 'rack',
                            prompt: "Choose Rack",
                            onSelect: function(rc) {
                                $("#level").combobox({
                                    url: '<?= base_url('warehouse/fg_locations/readLevels?location=') ?>' + loc.location + "&area=" + ar.area + "&rack=" + rc.rack,
                                    valueField: 'level',
                                    textField: 'level',
                                    prompt: "Choose Level",
                                    onSelect: function(lv) {
                                        $("#level_sub").combobox({
                                            url: '<?= base_url('warehouse/fg_locations/readLevelSubs?location=') ?>' + loc.location + "&area=" + ar.area + "&rack=" + rc.rack + "&level=" + lv.level,
                                            valueField: 'level_sub',
                                            textField: 'level_sub',
                                            prompt: "Choose Level Sub",
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
    });
</script>
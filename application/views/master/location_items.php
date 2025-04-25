<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Product No is taken from <b>Master Data > Engineering > Item Finish Good / Item Raw Material</b></li>
                <li>The Data Location is taken from <b>Master Data > Warehouse > Locations</b></li>
                <li>The Data Area is taken from <b>Master Data > Warehouse > Locations</b></li>
                <li>The Data Rack is taken from <b>Master Data > Warehouse > Locations</b></li>
                <li>The Data Level is taken from <b>Master Data > Warehouse > Locations</b></li>
                <li>The Data Level Sub is taken from <b>Master Data > Warehouse > Locations</b></li>
            </ul>
        </div>
        <div title="CONDITIONS" style="padding: 20px;">
            <ul>
                <li><b>Product No :</b> In Type if <b>Raw Material</b> selected, data from <b>Item Raw Material</b> will appear, likewise if <b>Finish Good</b> selected, data from <b>Item Finish Good</b> will appear.</li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'item_number',width:250,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'">Product Name</th>
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
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type</span>
                <select style="width:60%;" name="type" id="type" required class="easyui-combobox" panelHeight="auto">
                    <option value="RM">Raw Material</option>
                    <option value="FG">Finish Good</option>
                </select>
            </div>
            <div class="fitem" id="frm_fg">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="item_fg_id" id="item_fg_id" class="easyui-combogrid">
            </div>
            <div class="fitem" id="frm_rm">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="item_rm_id" id="item_rm_id" class="easyui-combogrid">
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
<iframe id="printout" src="<?= base_url('master/location_items/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/location_items/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/location_items/update') ?>?id=' + btoa(row.id);
            
            $("#type").combobox({
                onLoadSuccess: function() {
                    $("#type").combobox('setValue', row.type);
                },
                onChange: function(type) {
                    $("#location").combobox({
                        url: '<?= base_url('master/locations/readLocations?type=') ?>' + type,
                        valueField: 'location',
                        textField: 'location',
                        prompt: "Choose Location",
                        onLoadSuccess: function() {
                            $("#location").combobox('setValue', row.location);
                        },
                        onSelect: function(loc) {
                            $("#area").combobox({
                                url: '<?= base_url('master/locations/readAreas?location=') ?>' + loc.location + "&type=" + type,
                                valueField: 'area',
                                textField: 'area',
                                prompt: "Choose Area",
                                onLoadSuccess: function() {
                                    $("#area").combobox('setValue', row.area);
                                },
                                onSelect: function(ar) {
                                    $("#rack").combobox({
                                        url: '<?= base_url('master/locations/readRacks?location=') ?>' + loc.location + "&area=" + ar.area + "&type=" + type,
                                        valueField: 'rack',
                                        textField: 'rack',
                                        prompt: "Choose Rack",
                                        onLoadSuccess: function() {
                                            $("#rack").combobox('setValue', row.rack);
                                        },
                                        onSelect: function(rc) {
                                            $("#level").combobox({
                                                url: '<?= base_url('master/locations/readLevels?location=') ?>' + loc.location + "&area=" + ar.area + "&rack=" + rc.rack + "&type=" + type,
                                                valueField: 'level',
                                                textField: 'level',
                                                prompt: "Choose Level",
                                                onLoadSuccess: function() {
                                                    $("#level").combobox('setValue', row.level);
                                                },
                                                onSelect: function(lv) {
                                                    $("#level_sub").combobox({
                                                        url: '<?= base_url('master/locations/readLevelSubs?location=') ?>' + loc.location + "&area=" + ar.area + "&rack=" + rc.rack + "&level=" + lv.level + "&type=" + type,
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
                            url: '<?= base_url('master/location_items/delete') ?>',
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
        window.location.assign('<?= base_url('master/location_items/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/location_items/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        $("#frm_fg").hide();
        
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
        //UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('master/location_items/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('master/location_items/upload') ?>',
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
                                url: "<?= base_url('master/location_items/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('master/location_items/uploadCreate') ?>",
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
                                                    url: "<?= base_url('master/location_items/uploadcreateFailed') ?>",
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

        $("#type").combobox({
            onChange: function(type) {
                if(type == "RM"){
                    $("#frm_fg").hide();
                    $("#frm_rm").show();
                    $('#item_rm_id').combogrid({
                        url: '<?= base_url('master/item_rm/reads') ?>',
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
                                width: 150
                            }, {
                                field: 'name',
                                title: 'Product Name',
                                width: 200
                            }, ]
                        ]
                    });
                }else if(type == "FG"){
                    $("#frm_fg").show();
                    $("#frm_rm").hide();
                    $('#item_fg_id').combogrid({
                        url: '<?= base_url('master/item_fg/reads') ?>',
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
                                width: 150
                            }, {
                                field: 'name',
                                title: 'Product Name',
                                width: 200
                            }, ]
                        ]
                    });
                }

                $("#location").combobox({
                    url: '<?= base_url('master/locations/readLocations?type=') ?>' + type,
                    valueField: 'location',
                    textField: 'location',
                    prompt: "Choose Location",
                    onSelect: function(loc) {
                        $("#area").combobox({
                            url: '<?= base_url('master/locations/readAreas?location=') ?>' + loc.location + "&type=" + type,
                            valueField: 'area',
                            textField: 'area',
                            prompt: "Choose Area",
                            onSelect: function(ar) {
                                $("#rack").combobox({
                                    url: '<?= base_url('master/locations/readRacks?location=') ?>' + loc.location + "&area=" + ar.area + "&type=" + type,
                                    valueField: 'rack',
                                    textField: 'rack',
                                    prompt: "Choose Rack",
                                    onSelect: function(rc) {
                                        $("#level").combobox({
                                            url: '<?= base_url('master/locations/readLevels?location=') ?>' + loc.location + "&area=" + ar.area + "&rack=" + rc.rack + "&type=" + type,
                                            valueField: 'level',
                                            textField: 'level',
                                            prompt: "Choose Level",
                                            onSelect: function(lv) {
                                                $("#level_sub").combobox({
                                                    url: '<?= base_url('master/locations/readLevelSubs?location=') ?>' + loc.location + "&area=" + ar.area + "&rack=" + rc.rack + "&level=" + lv.level + "&type=" + type,
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
            }
        });
    });
</script>
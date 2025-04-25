<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Product No is taken from <b>Master Data > Engineering > Item Finish Good</b></li>
                <li>The Data Cycle Time is taken from <b>Master Data > Engineering > Menu Loading</b></li>
                <li>The Data Productivity is taken from <b>Master Data > Engineering > Menu Loading</b></li>
                <li>The Data Cavity Actual is taken from <b>Master Data > Engineering > Menu Loading</b></li>
                <li>The Data Capacity/Hour is taken from <b>Calculation, to know any futher Please Check FORMULATION Below</b></li>
                <li>The Data Capacity/Shift is taken from <b>Calculation, to know any futher Please Check FORMULATION Below</b></li>
                <li>The Data Capacity/Day is taken from <b>Calculation, to know any futher Please Check FORMULATION Below</b></li>
            </ul>
        </div>
        <div title="FORMULATION" style="padding: 20px;">
            <ul>
                <li><b>Capacity/Hour :</b> (3600/Cycle Time) * Cavity Actual * (Productivity/100).</li>
                <li><b>Capacity/Shift :</b> (3600/Cycle Time) * Cavity Actual * (Productivity/100) * Capacity Hour.</li>
                <li><b>Capacity/Day :</b> ((3600/Cycle Time) * Cavity Actual * (Productivity/100) * Capacity Hour) * Shift hour * Shift.</li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'item_fg_number',width:150,align:'center'">Product No.</th>
            <th rowspan="2" data-options="field:'machine_number',width:150,align:'center'">Machine No.</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:150,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'cycle_time',width:100,halign:'center'">Cycle Time <br>(Second)</th>
            <th rowspan="2" data-options="field:'productcivity',width:100,halign:'center'">Productivity <br>Factor (%)</th>
            <th rowspan="2" data-options="field:'cavity_actual',width:100,halign:'center'">Cavity Actual</th>
            <th rowspan="2" data-options="field:'capacity_hour',width:100,halign:'center'">Capacity/Hour</th>
            <th rowspan="2" data-options="field:'capacity_shift',width:100,halign:'center'">Capacity/Shift</th>
            <th rowspan="2" data-options="field:'capacity_day',width:100,halign:'center'">Capacity/Day</th>
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
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 450px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No.</span>
                <input style="width:60%;" name="item_fg_id" id="item_fg_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Machine No.</span>
                <input style="width:60%;" name="machine_id" id="machine_id" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cycle Time</span>
                <input style="width:60%;" id="cycle_time" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Productivity Factor %</span>
                <input style="width:60%;" id="productcivity" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cavity Actual</span>
                <input style="width:60%;" id="cavity_actual" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cavity/Hour</span>
                <input style="width:60%;" name="capacity_hour" id="capacity_hour" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cavity/Shift</span>
                <input style="width:60%;" name="capacity_shift" id="capacity_shift" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cavity/Day</span>
                <input style="width:60%;" name="capacity_day" id="capacity_day" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
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
<iframe id="printout" src="<?= base_url('master/production_capacities/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/production_capacities/create') ?>';
        $('#frm_insert').form('clear');

    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/production_capacities/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/production_capacities/delete') ?>',
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
        window.location.assign('<?= base_url('template/tmp_production_capacities.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/production_capacities/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/production_capacities/datatables') ?>',
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

    $('#item_fg_id').combogrid({
            url: '<?php echo base_url('master/production_capacities/readItems'); ?>',
            required: true,
            panelWidth: 500,
            idField: 'item_fg_id',
            textField: 'item_fg_number',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Product No',
            columns: [
                [{
                    field: 'item_fg_id',
                    title: 'Product ID',
                    width: 120
                }, {
                    field: 'item_fg_number',
                    title: 'Product No.',
                    width: 150
                }, {
                    field: 'item_fg_name',
                    title: 'Product Name',
                    width: 200
                }]
            ],
            onSelect: function(val, rows) {
                    $("#machine_id").textbox('setValue', rows.machine_id);
                    $("#cycle_time").textbox('setValue', rows.cycle_time);
                    $("#productcivity").textbox('setValue', rows.productcivity);
                    $("#cavity_actual").textbox('setValue', rows.cavity_actual); // mengambil dari molds

                    var capacity_hour = (3600 / rows.cycle_time) * rows.cavity_actual * (rows.productcivity / 100);
                    var capacity_shift = (capacity_hour * capacity_hour);
                    var capacity_day = (capacity_hour *  capacity_hour * rows.shift_hour * rows.shift);

                    $("#capacity_hour").textbox('setValue', capacity_hour);
                    $("#capacity_shift").textbox('setValue', capacity_shift);
                    $("#capacity_day").textbox('setValue', capacity_day);
                    
                // $('#machine_id').combobox({
                //     url: '<?php echo base_url('master/production_capacities/readMachines/'); ?>' + btoa(rows.item_fg_id),
                //     valueField: 'machine_id',
                //     textField: 'machine_number',
                //     prompt: "Choose Machine No",
                //     onSelect: function(menu_loadings){
                //         $("#cycle_time").textbox('setValue', menu_loadings.cycle_time);
                //         $("#productcivity").textbox('setValue', menu_loadings.productcivity);
                //         $("#cavity_actual").textbox('setValue', menu_loadings.cavity_actual); // mengambil dari molds

                //         var capacity_hour = (3600 / menu_loadings.cycle_time) * menu_loadings.cavity_actual * (menu_loadings.productcivity / 100);
                //         var capacity_shift = (capacity_hour * capacity_hour);
                //         var capacity_day = (capacity_hour *  capacity_hour * menu_loadings.shift_hour * menu_loadings.shift);

                //         $("#capacity_hour").textbox('setValue', capacity_hour);
                //         $("#capacity_shift").textbox('setValue', capacity_shift);
                //         $("#capacity_day").textbox('setValue', capacity_day);
                //     }
                // });
            }
    });

    // UPLOAD DATA
    $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('master/production_capacities/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('master/production_capacities/upload') ?>',
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
                                url: "<?= base_url('master/production_capacities/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('master/production_capacities/uploadCreate') ?>",
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
                                                    url: "<?= base_url('master/production_capacities/uploadcreateFailed') ?>",
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
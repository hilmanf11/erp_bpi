<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Unit of Measure is taken from <b>Master Data > General Master > Unit of Measure</b></li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'id',width:150,align:'center'">Machine ID</th>
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Machine No.</th>
            <th rowspan="2" data-options="field:'name',width:150,halign:'center'">Name Of Machine</th>
            <th rowspan="2" data-options="field:'specification',width:100,halign:'center'">Spesification</th>
            <th rowspan="2" data-options="field:'purchase_date',width:150,halign:'center'">Purchase Date</th>
            <th rowspan="2" data-options="field:'manufacturing_date',width:150,halign:'center'">Manufacturing Date</th>
            <th rowspan="2" data-options="field:'maker',width:150,halign:'center'">Maker</th>
            <th rowspan="2" data-options="field:'toonage',width:150,halign:'center'">Toonage Of Machine</th>
            <th rowspan="2" data-options="field:'tiebar',width:100,halign:'center'">Tie Bar</th>
            <th rowspan="2" data-options="field:'uom_tiebar',width:80,halign:'center'">UOM</th>
            <th rowspan="2" data-options="field:'min_closing',width:140,halign:'center'">Minimum Closing</th>
            <th rowspan="2" data-options="field:'uom_min',width:80,halign:'center'">UOM</th>
            <th rowspan="2" data-options="field:'max_open',width:140,halign:'center'">Maximum Open</th>
            <th rowspan="2" data-options="field:'uom_max',width:80,halign:'center'">UOM</th>
            <th rowspan="2" data-options="field:'volume',width:140,halign:'center'">Barrel Volume</th>
            <th rowspan="2" data-options="field:'uom_volume',width:80,halign:'center'">UOM</th>
            <th rowspan="2" data-options="field:'diameter',width:140,halign:'center'">Screw Diameter</th>
            <th rowspan="2" data-options="field:'uom_diameter',width:80,halign:'center'">UOM</th>
            <th rowspan="2" data-options="field:'brand',width:150,halign:'center'">Brand</th>
            <th rowspan="2" data-options="field:'status',width:150,halign:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Machine ID</span>
                    <input style="width:60%;" name="id" id="id" required="" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Machine No.</span>
                    <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Machine Name</span>
                    <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Spesification</span>
                    <input style="width:60%;" name="specification" id="specification" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Date</span>
                    <input style="width:60%;" name="purchase_date" id="purchase_date" data-options="formatter:myformatter,parser:myparser" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Manufacturing Date</span>
                    <input style="width:60%;" name="manufacturing_date" id="manufacturing_date" data-options="formatter:myformatter,parser:myparser" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Maker</span>
                    <input style="width:60%;" name="maker" id="maker" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Toonage of Machine</span>
                    <input style="width:60%;" name="toonage" id="toonage" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Brand</span>
                    <input style="width:60%;" name="brand" id="brand" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" name="status" id="status" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="0">Active</option>
                        <option value="1">Not Active</option>
                    </select>
                </div>
            </div>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Tie of Bar</span>
                    <input style="width:60%;" name="tiebar" id="tiebar" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Unit of Measure</span>
                    <input style="width:60%;" name="uom_tiebar" id="uom_tiebar" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Min Closing</span>
                    <input style="width:60%;" name="min_closing" id="min_closing" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Unit of Measure</span>
                    <input style="width:60%;" name="uom_min" id="uom_min" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Max Open</span>
                    <input style="width:60%;" name="max_open" id="max_open" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Unit of Measure</span>
                    <input style="width:60%;" name="uom_max" id="uom_max" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Barel Volume</span>
                    <input style="width:60%;" name="volume" id="volume" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Unit of Measure</span>
                    <input style="width:60%;" name="uom_volume" id="uom_volume" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Screw Diameter</span>
                    <input style="width:60%;" name="diameter" id="diameter" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Unit of Measure</span>
                    <input style="width:60%;" name="uom_diameter" id="uom_diameter" class="easyui-combobox">
                </div>
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
<iframe id="printout" src="<?= base_url('master/machines/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/machines/create') ?>';
        $('#frm_insert').form('clear');

        $('#status').combobox('setValue', '0');

        $.ajax({
            type : "post",
            url : "<?= base_url('master/machines/autoid')?>",
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
            url_save = '<?= base_url('master/machines/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/machines/delete') ?>',
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
        window.location.assign('<?= base_url('template/tmp_machines.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/machines/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/machines/datatables') ?>',
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

    $('#uom_tiebar').combobox({
        url:'<?= base_url('master/uom/reads'); ?>',
        valueField:'name',
        textField:'name',
        prompt: 'Choose Unit of Measure',
    });

    $('#uom_min').combobox({
        url:'<?= base_url('master/uom/reads'); ?>',
        valueField:'name',
        textField:'name',
        prompt: 'Choose Unit of Measure',
    });

    $('#uom_max').combobox({
        url:'<?= base_url('master/uom/reads'); ?>',
        valueField:'name',
        textField:'name',
        prompt: 'Choose Unit of Measure',
    });

    $('#uom_volume').combobox({
        url:'<?= base_url('master/uom/reads'); ?>',
        valueField:'name',
        textField:'name',
        prompt: 'Choose Unit of Measure',
    });

    $('#uom_diameter').combobox({
        url:'<?= base_url('master/uom/reads'); ?>',
        valueField:'name',
        textField:'name',
        prompt: 'Choose Unit of Measure',
    });

    // FORMAT tahun-bulan-tanggal
    function myformatter(date){
        var y = date.getFullYear();
        var m = date.getMonth()+1;
        var d = date.getDate();
        return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);
    }
    function myparser(s){
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0],10);
        var m = parseInt(ss[1],10);
        var d = parseInt(ss[2],10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
            return new Date(y,m-1,d);
        } else {
            return new Date();
        }
    }

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
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

    // UPLOAD DATA
    $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('master/machines/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('master/machines/upload') ?>',
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
                                url: "<?= base_url('master/machines/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('master/machines/uploadCreate') ?>",
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
                                                    url: "<?= base_url('master/machines/uploadcreateFailed') ?>",
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
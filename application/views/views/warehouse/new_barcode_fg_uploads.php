<!-- <div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Machine No is taken from <b>Master Data > Maintenance > Machines</b></li>
                <li>The Data Sub Product Family is taken from <b>Master Data > General Master > Product Family Sub</b></li>
                <li>The Data Kind of Colors is taken from <b>Master Data > Engineering > Colors</b></li>
            </ul>
        </div>
    </div>
</div> -->

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'print',width:80,align:'center',formatter:BtnPrint">Print</th>
            <th rowspan="2" data-options="field:'label_no',width:150,align:'center'">Label No</th>
            <th rowspan="2" data-options="field:'item_number',width:150,align:'center'">Product No</th>
            <th rowspan="2" data-options="field:'label_type',width:100,halign:'center'">Type</th>
            <th rowspan="2" data-options="field:'shift',width:100,halign:'center'">Shift</th>
            <th rowspan="2" data-options="field:'lot_no',width:100,halign:'center'">Lot No</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center'">Qty</th>
            <th rowspan="2" data-options="field:'op_1',width:100,halign:'center'">Operator 1</th>
            <th rowspan="2" data-options="field:'op_2',width:100,halign:'center'">Operator 2</th>
            <th rowspan="2" data-options="field:'qc_1',width:100,halign:'center'">Qc 1</th>
            <th rowspan="2" data-options="field:'qc_2',width:100,halign:'center'">Qc 2</th>
            <th rowspan="2" data-options="field:'cut_off_date',width:100,halign:'center'">Cut Of Date</th>
            <th rowspan="2" data-options="field:'prod_date',width:100,halign:'center'">Prod Date</th>
            <th rowspan="2" data-options="field:'packing_date',width:100,halign:'center'">Packing Date</th>

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
<!-- <div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate enctype="multipart/form-data">
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">NIK</span>
                <input style="width:60%;" name="nik" id="nik" required="" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Full Name</span>
                <input style="width:60%;" name="name" id="name" class="easyui-textbox" required="">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Position</span>
                <select style="width:60%;" name="position" id="position" required="" panelHeight="auto" class="easyui-combobox">
                    <option value="Operator">Operator</option>
                    <option value="Qc">QC</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Effective Date</span>
                <input style="width:60%;" name="effective_date" id="effective_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:60%;" name="status" id="status" required="" panelHeight="auto" class="easyui-combobox">
                    <option value="0">Active</option>
                    <option value="1">Not Active</option>
                </select>
            </div>
        </fieldset>
    </form>
</div> -->

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
<iframe id="printout" src="<?= base_url('warehouse/new_barcode_fg_uploads/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_new_barcode_fg_uploads.xls') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/new_barcode_fg_uploads/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        //SAVE DATA
        // $('#dlg_insert').dialog({
        //     buttons: [{
        //         text: 'Save',
        //         iconCls: 'icon-ok',
        //         handler: function() {
        //             $('#frm_insert').form('submit', {
        //                 url: url_save,
        //                 onSubmit: function() {
        //                     return $(this).form('validate');
        //                 },
        //                 success: function(result) {
        //                     var result = eval('(' + result + ')');
        //                     if (result.theme == "success") {
        //                         toastr.success(result.message, result.title);
        //                     } else {
        //                         toastr.error(result.message, result.title);
        //                     }
                            
        //                     $('#dlg_insert').dialog('close');
        //                     $('#dg').datagrid('reload');
        //                 }
        //             });
        //         }
        //     }]
        // });
    });

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
            return 'Inactive';
        }
    };    

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('warehouse/new_barcode_fg_uploads/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('warehouse/new_barcode_fg_uploads/upload') ?>',
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
                            url: "<?= base_url('warehouse/new_barcode_fg_uploads/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('warehouse/new_barcode_fg_uploads/uploadCreate') ?>",
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
                                                url: "<?= base_url('warehouse/new_barcode_fg_uploads/uploadcreateFailed') ?>",
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

     //DELETE DATA
     function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        console.log(row);
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('warehouse/new_barcode_fg_uploads/delete') ?>',
                            data: {
                                id: row.id,
                                label_no: row.label_no
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error("This item cannot be deleted, Please make sure it didn't have any relation");
                                // $.messager.alert("Error", jqXHR.statusText, 'error');
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

    //Format Datepicker
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }
    //Format Datepicker
    function myparser(s) {
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0], 10);
        var m = parseInt(ss[1], 10);
        var d = parseInt(ss[2], 10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
            return new Date(y, m - 1, d);
        } else {
            return new Date();
        }
    }

    function BtnPrint(val, row) {
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('warehouse/new_barcode_fg_uploads/print/') ?>' + window.btoa(row.id) + '"><i class="fa fa-print"></i> Print</a>';
    }
</script>
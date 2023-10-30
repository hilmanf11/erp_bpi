<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'detail',width:80,align:'center',formatter: btnDetails">Address</th>
            <th rowspan="2" data-options="field:'id',width:100,align:'center'">Customer ID</th>
            <th rowspan="2" data-options="field:'name',width:200,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'number',width:110,halign:'center'">Customer Code</th>
            <th rowspan="2" data-options="field:'type',width:150,halign:'center'">Type</th>
            <th rowspan="2" data-options="field:'currency',width:150,halign:'center'">Currency</th>
            <th rowspan="2" data-options="field:'taxes',width:80,halign:'center'">Taxes</th>
            <th rowspan="2" data-options="field:'payment_term',width:150,halign:'center'">Payment Term (Day)</th>
            <th rowspan="2" data-options="field:'bank_account',width:150,halign:'center'">Bank Account</th>
            <th rowspan="2" data-options="field:'bank_name',width:150,halign:'center'">Bank Name</th>
            <th rowspan="2" data-options="field:'status',width:100,halign:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
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
<!-- TOOLBAR CUSTOMERS ADDRESS DATAGRID -->
<div id="toolbar2" style="height: 35px;">
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="add2()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="update2()"><i class="fa fa-edit"></i> Update</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="deleted2()"><i class="fa fa-trash"></i> Delete</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="upload2()"><i class="fa fa-upload"></i> Upload</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="download_excel2()"><i class="fa fa-download"></i> Download Template</a>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer ID</span>
                    <input style="width:30%;" name="id" id="id" required="" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Code</span>
                    <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Type</span>
                    <select style="width:60%;" name="type" id="type" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="LOCAL">LOCAL</option>
                        <option value="EXPORT">EXPORT</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:60%;" name="currency" id="currency" required="" class="easyui-textbox">
                </div>
            </div>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Taxes</span>
                    <input style="width:60%;" name="taxes" id="taxes" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Payment Term (Day)</span>
                    <input style="width:60%;" name="payment_term" id="payment_term" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Bank Account</span>
                    <input style="width:60%;" name="bank_account" id="bank_account" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Bank Name</span>
                    <input style="width:60%;" name="bank_name" id="bank_name" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" name="status" id="status" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="0">Active</option>
                        <option value="1">Not Active</option>
                    </select>
                </div>
            </div>
        </fieldset>
    </form>
</div>

<!-- DIALOG CUSTOMER ADDRESS -->
<div id="dlg_details" class="easyui-dialog" title="Customer Address" data-options="closed: true,modal:true" style="width: 1200px; height: 500px; top: 20px; left: 10px;">
    <table id="dg2" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2">
        <thead>
            <tr>
                <th rowspan="2" field="ck" checkbox="true"></th>
                <th rowspan="2" data-options="field:'address',width:150,halign:'center'">Address</th>
                <th rowspan="2" data-options="field:'address_billing',width:150,halign:'center'">Billing Address</th>
                <th rowspan="2" data-options="field:'contact_person',width:150,halign:'center'">Contact Person</th>
                <th rowspan="2" data-options="field:'telp',width:150,halign:'center'">Telepon</th>
                <th rowspan="2" data-options="field:'telp_billing',width:150,halign:'center'">Billing Contact</th>
                <th rowspan="2" data-options="field:'email',width:150,halign:'center'">Email</th>
                <th rowspan="2" data-options="field:'website',width:150,halign:'center'">Website</th>
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
</div>

<!-- DIALOG SAVE AND UPDATE CUSTOMER ADDRESS -->
<div id="dlg_insert2" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert2" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Customer ID</span>
                <input style="width:60%;" name="customer_id" id="customer_id" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Address</span>
                <input style="width:60%;" name="address" id="address" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Billing Address</span>
                <input style="width:60%;" name="address_billing" id="address_billing" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Contact Person</span>
                <input style="width:60%;" name="contact_person" id="contact_person" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Telepon</span>
                <input style="width:60%;" name="telp" id="telp" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Billing Contact</span>
                <input style="width:60%;" name="telp_billing" id="telp_billing" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Email</span>
                <input style="width:60%;" name="email" id="email" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Website</span>
                <input style="width:60%;" name="website" id="website" class="easyui-textbox">
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

<!-- Upload -->
<div id="dlg_upload2" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload2" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload2" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success2">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed2">0</b></span>
    <div id="p_upload2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
    <div id="p_remarks2" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks2">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/customers/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/customers/create') ?>';
        $('#frm_insert').form('clear');

        $('#type').combobox('setValue', 'LOCAL');
        $('#status').combobox('setValue', '0');
        $('#taxes').numberbox('setValue', '11');

        $.ajax({
            type: "post",
            url: "<?= base_url('master/customers/autoid') ?>",
            dataType: "html",
            success: function(response) {
                $('#id').textbox('setValue', response);
            }
        });
    }

    //ADD DATA
    function add2() {
        $('#dlg_insert2').dialog('open');
        var customer_id = $("#customer_id").textbox('getValue');
        url_save2 = '<?= base_url('master/customers/create2') ?>';
        $('#frm_insert2').form('clear');
        $("#customer_id").textbox('setValue', customer_id);
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/customers/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function update2() {
        var row = $('#dg2').datagrid('getSelected');
        if (row) {
            $('#dlg_insert2').dialog('open');
            $('#frm_insert2').form('load', row);
            url_save2 = '<?= base_url('master/customers/update2') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/customers/delete') ?>',
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

    function deleted2() {
        var rows = $('#dg2').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('master/customers/delete2') ?>',
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
                                $('#dg2').datagrid('reload');
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

    // UPLOAD DATA
    function upload2() {
        $('#dlg_upload2').dialog('open');
    }

    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_customers.xls') ?>');
    }

     // DOWNLOAD
     function download_excel2() {
        window.location.assign('<?= base_url('template/tmp_customer_address.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/customers/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/customers/datatables') ?>',
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

        $('#dlg_insert2').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert2').form('submit', {
                        url: url_save2,
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
                            $('#dlg_insert2').dialog('close');
                            $('#dg2').datagrid('reload');
                        }
                    });
                }
            }]
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
    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 0) {
            return 'Active';
        } else {
            return 'Not Active';
        }
    };

    function btnDetails(val, row) {
        var details = "details('" + row.id + "')"; //mengambil id dari customers kemudian di simpan di function details
        return '<a class="btn btn-primary w-100" onClick="' + details + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-list"></i> Detail</a>';
    }

    function details(customer_id) {
        $("#dlg_details").dialog('open');
        $("#customer_id").textbox('setValue', customer_id); // id customer di simpan di textbox customer_id sekaligus saat add id tersimpan

        $('#dg2').datagrid({
            url: '<?= base_url('master/customers/datatables2/') ?>' + customer_id,
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true
        }).datagrid('enableFilter');
    }

    $('#currency').combobox({
        url: '<?= base_url('master/currencies/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Currencies',
    });

    // UPLOAD
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/customers/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/customers/upload') ?>',
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
                            url: "<?= base_url('master/customers/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('master/customers/uploadCreate') ?>",
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
                                                url: "<?= base_url('master/customers/uploadcreateFailed') ?>",
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

    // UPLOAD
    $('#dlg_upload2').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/customers/uploadDownloadFailed2') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload2').form('submit', {
                    url: '<?= base_url('master/customers/upload2') ?>',
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
                            url: "<?= base_url('master/customers/uploadclearFailed2') ?>"
                        });
                        var json = eval('(' + result + ')');
                        requestData(json.total, json);

                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                            if (value < 100) {
                                value = Math.floor((number / total) * 100);
                                $('#p_upload2').progressbar('setValue', value);
                                $('#p_start2').html(number);
                                $('#p_finish2').html(total);

                                $.ajax({
                                    type: "POST",
                                    async: true,
                                    url: "<?= base_url('master/customers/uploadCreate2') ?>",
                                    data: {
                                        "data": json[number - 1]
                                    },
                                    cache: false,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            $('#p_success2').html(success);
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            $('#p_failed2').html(failed);
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                            //Json Failed
                                            $.ajax({
                                                type: "POST",
                                                async: true,
                                                url: "<?= base_url('master/customers/uploadcreateFailed2') ?>",
                                                data: {
                                                    data: json[number - 1],
                                                    message: result.message
                                                },
                                                cache: false
                                            });
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }
                                        $("#p_remarks2").append(title + "<br>");
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
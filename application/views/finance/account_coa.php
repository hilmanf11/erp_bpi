<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'account_group_detail_id',width:100,align:'left'">
                <div style="text-align: center;">Category</div>
            </th>
            <th rowspan="2" data-options="field:'account_number',width:100,halign:'center'">Account Code</th>
            <th rowspan="2" data-options="field:'account_name',width:150,halign:'center'">Account Name</th>
            <!-- <th rowspan="2" data-options="field:'closing_jurnal',width:150,halign:'center'">Closing Jurnal</th> -->
            <th colspan="3" data-options="field:'',width:150,halign:'center'"> Original Currency</th>
            <th colspan="3" data-options="field:'',width:150,halign:'center'"> Local Currency</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'original_currency',width:100,align:'center'">Currency</th>
            <th data-options="field:'original_debit',width:150,halign:'center',align:'right',formatter: priceformat">Debit</th>
            <th data-options="field:'original_kredit',width:150,halign:'center',align:'right',formatter: priceformat">Credit</th>
            <th data-options="field:'local_currency',width:100,align:'center'">Currency</th>
            <th data-options="field:'local_debit',width:150,halign:'center',align:'right',formatter: priceformatlocal">Debit</th>
            <th data-options="field:'local_kredit',width:150,halign:'center',align:'right',formatter: priceformatlocal">Credit</th>
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
                <span style="width:35%; display:inline-block;">Category</span>
                <input style="width:250px;" name="account_group_detail_id" id="account_group_detail_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Account Code</span>
                <input style="width:50%;" name="account_number" id="account_number" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Account Name</span>
                <input style="width:50%;" name="account_name" id="account_name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <input style="width:25%;" name="status" id="status" required="" class="easyui-combobox">
            </div>
        </fieldset>
        <div style="width:50%; float: left; box-sizing: border-box; margin-bottom: 10px;">
            <fieldset style="border:1px solid #d0d0d0; border-radius: 4px; margin-bottom: 10px;">
                <legend><b>Origial Currency</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:150px;" name="original_currency" id="original_currency" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Debit</span>
                    <input style="width:60%;" name="original_debit" id="original_debit" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Credit</span>
                    <input style="width:60%;" name="original_kredit" id="original_kredit" class="easyui-textbox">
                </div>
            </fieldset>
        </div>
        <div style="width:50%; float: left; box-sizing: border-box; margin-bottom: 10px;">
            <fieldset style="border:1px solid #d0d0d0; border-radius: 4px; margin-bottom: 10px;">
                <legend><b>Local Currency</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:150px;" name="local_currency" id="local_currency" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Debit</span>
                    <input style="width:60%;" name="local_debit" id="local_debit" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Credit</span>
                    <input style="width:60%;" name="local_kredit" id="local_kredit" class="easyui-textbox">
                </div>
            </fieldset>
        </div>
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
<iframe id="printout" src="<?= base_url('finance/account_coa/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('finance/account_coa/create') ?>';
        $('#frm_insert').form('clear');

        $.ajax({
            type: "post",
            url: "<?= base_url('finance/account_coa/autoid') ?>",
            dataType: "html",
            success: function(response) {
                $('#number').textbox('setValue', response);
            }
        });
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('finance/account_coa/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('finance/account_coa/delete') ?>',
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

    // PRICE FORMAT
    function priceformat(value, row) {
        var digits = 0;
        var currency = 'IDR';
        var format = "id-ID";

        if (row.original_currency == "USD") {
            digits = 2;
            currency = 'USD';
            format = "en-IN";
        } else if (row.original_currency == "JPY") {
            digits = 2;
            currency = 'JPY';
            format = "ja-JP";
        } else if (row.original_currency == "EUR") {
            digits = 2;
            currency = 'EUR';
            format = "de-DE";
        }

        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: digits
            });

            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function priceformatlocal(value, row) {
        var digits = 0;
        var currency = 'IDR';
        var format = "id-ID";

        if (row.local_currency == "USD") {
            digits = 2;
            currency = 'USD';
            format = "en-IN";
        } else if (row.local_currency == "JPY") {
            digits = 2;
            currency = 'JPY';
            format = "ja-JP";
        } else if (row.local_currency == "EUR") {
            digits = 2;
            currency = 'EUR';
            format = "de-DE";
        }

        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: digits
            });

            return "<b>" + formatter.format(value) + "</b>";
        }
    }


    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_account_coa.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('finance/account_coa/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('finance/account_coa/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        $('#account_group_detail_id').combobox({
            url: '<?= base_url('finance/account_group_details/reads') ?>', // URL to your PHP script
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Category',
        });

        $('#status').combobox({
            url: '<?= base_url('finance/account_coa/get_statuses') ?>', // URL to your PHP script to retrieve statuses
            valueField: 'value', // Field that represents the value (1 or 0)
            textField: 'label', // Field that represents the display label (Yes or No)
        });

        $('#original_currency').combobox({
            url: '<?= base_url('master/currencies/reads') ?>', // URL to your PHP script
            valueField: 'name',
            textField: 'name',
            prompt: 'Choose Currencies',
        });

        $('#local_currency').combobox({
            url: '<?= base_url('master/currencies/reads') ?>', // URL to your PHP script
            valueField: 'name',
            textField: 'name',
            prompt: 'Choose Currencies',
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

                            $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });
        // UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('finance/account_coa/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('finance/account_coa/upload') ?>',
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
                                url: "<?= base_url('finance/account_coa/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('finance/account_coa/uploadCreate') ?>",
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
                                                    url: "<?= base_url('finance/account_coa/uploadcreateFailed') ?>",
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
    });
</script>
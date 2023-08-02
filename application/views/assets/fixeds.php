<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'a',width:100,align:'center'">Asset Code</th>
            <th rowspan="2" data-options="field:'b',width:200,halign:'center'">Asset Name</th>
            <th rowspan="2" data-options="field:'c',width:150,halign:'center'">Asset Category</th>
            <th rowspan="2" data-options="field:'d',width:200,halign:'center'">Asset Type</th>
            <th rowspan="2" data-options="field:'e',width:150,halign:'center'">Purchase Invoice No</th>
            <th rowspan="2" data-options="field:'f',width:200,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'g',width:100,align:'center'">Purchase Date</th>
            <th rowspan="2" data-options="field:'h',width:100,align:'center'">Usage Date</th>
            <th rowspan="2" data-options="field:'i',width:80,align:'center'">Quantity</th>
            <th rowspan="2" data-options="field:'j',width:100,align:'center'">Estimated<br>Economic<br>Life (Month)</th>
            <th rowspan="2" data-options="field:'k',width:100,align:'center'">Asset Cost</th>
            <th rowspan="2" data-options="field:'l',width:100,align:'center'">Residual Value</th>
            <th rowspan="2" data-options="field:'m',width:100,align:'center'">Book Value</th>
            <th rowspan="2" data-options="field:'n',width:100,align:'center'">Depreciation<br>Method</th>
            <th rowspan="2" data-options="field:'o',width:100,align:'center'">Account No</th>
            <th rowspan="2" data-options="field:'p',width:100,align:'center'">Accumulated<br>Depreciation<br>Account</th>
            <th rowspan="2" data-options="field:'q',width:100,align:'center'">Depreciation<br>Expense<br>Account</th>
            <th rowspan="2" data-options="field:'r',width:100,align:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'s',width:80,align:'center'">Status</th>
            <th colspan="2" data-options="field:'t',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'u',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 265px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Date</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Name</span>
                    <input style="width:60%;" name="filter_asset_name" id="filter_asset_name" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Category</span>
                    <input style="width:60%;" name="filter_asset_category" id="filter_asset_category" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Economic Life</span>
                    <input style="width:60%;" name="filter_economic_life" id="filter_economic_life" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Invoice No</span>
                    <input style="width:60%;" name="filter_invoice_no" id="filter_invoice_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="filter_supplier" id="filter_supplier" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <input style="width:60%;" name="filter_status" id="filter_status" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;  margin-bottom: 20px;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Invoice No</span>
                    <input style="width:60%;" name="invoice_no" id="invoice_no" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Name</span>
                    <input style="width:60%;" name="asset_name" id="asset_name" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Category</span>
                    <input style="width:60%;" name="asset_categories_id" id="asset_categories_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Date</span>
                    <input style="width:60%;" name="purchase_date" id="purchase_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="supplier_name" id="supplier_name" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty</span>
                    <input style="width:40%;" name="qty" id="qty" required class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:40%;" name="currency" id="currency" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Cost</span>
                    <input style="width:40%;" name="asset_cost" id="asset_cost" disabled class="easyui-numberbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Usage Date</span>
                    <input style="width:60%;" name="usage_date" id="usage_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Estimated Economic</span>
                    <select style="width:40%;" name="estimated_economic" id="estimated_economic" panelHeight="auto" class="easyui-combobox">
                        <option value="48">48</option>
                        <option value="96">96</option>
                        <option value="240">240</option>
                    </select>
                    &nbsp; Month
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Residual Value</span>
                    <input style="width:40%;" name="residual_value" id="residual_value" required class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%; height: 100px;" name="remarks" id="remarks" class="easyui-textbox" multiline="true">
                </div>
            </div>
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>General Information</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Depreciation Method</span>
                        <input style="width:50%;" name="depreciation" id="depreciation" disabled class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Asset Account</span>
                        <input style="width:50%;" name="asset_account" id="asset_account" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Accumulated Depreciation</span>
                        <input style="width:50%;" name="asset_account" id="asset_account" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Depreciation Expense Account</span>
                        <input style="width:50%;" name="asset_account" id="asset_account" class="easyui-combobox">
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Previous Departement</span>
                        <input style="width:50%;" name="previous_departement" id="previous_departement" disabled class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Previous Location</span>
                        <input style="width:50%;" name="previous_location" id="previous_location" disabled class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Current Departement</span>
                        <input style="width:50%;" name="current_departement" id="current_departement" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Current Location</span>
                        <input style="width:50%;" name="current_location" id="current_location" class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
            <div style="width: 30%; float: right;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Total Asset</span>
                    <input style="width:60%;" name="total_asset" id="total_asset" disabled class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Book Value</span>
                    <input style="width:60%;" name="book_value" id="book_value" disabled class="easyui-numberbox">
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
<iframe id="printout" src="<?= base_url('assets/fixeds/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('assets/fixeds/create') ?>';
        $('#frm_insert').form('clear');
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('assets/fixeds/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('assets/fixeds/delete') ?>',
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

    function filter() {
        var filter_customers = $("#filter_customers").combogrid('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_components = $("#filter_components").combogrid('getValue');

        var url = "?filter_customers=" + window.btoa(filter_customers) +
            "&filter_items=" + window.btoa(filter_items) +
            "&filter_components=" + window.btoa(filter_components);
        $('#dg').datagrid({
            url: '<?= base_url('assets/fixeds/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('assets/fixeds/print') ?>' + url);
    }
    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_bom.xls') ?>');
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_customers = $("#filter_customers").combogrid('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_components = $("#filter_components").combogrid('getValue');

        var url = "?filter_customers=" + window.btoa(filter_customers) +
            "&filter_items=" + window.btoa(filter_items) +
            "&filter_components=" + window.btoa(filter_components);

        window.location.assign('<?= base_url('assets/fixeds/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('assets/fixeds/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true
        });
        //Save Data
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
        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('assets/fixeds/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('assets/fixeds/upload') ?>',
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
                                url: "<?= base_url('assets/fixeds/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('assets/fixeds/uploadCreate') ?>",
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
                                                    url: "<?= base_url('assets/fixeds/uploadcreateFailed') ?>",
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

        //GET COMPONENT
        // $('#component_id').combogrid({
        //     url: '<?= base_url('master/items/readNotFg') ?>',
        //     panelWidth: 420,
        //     idField: 'id',
        //     textField: 'number',
        //     mode: 'remote',
        //     fitColumns: true,
        //     prompt: "Choose Component",
        //     columns: [
        //         [{
        //             field: 'number',
        //             title: 'Component No',
        //             width: 120
        //         }, {
        //             field: 'name',
        //             title: 'Component Name',
        //             width: 250
        //         }, ]
        //     ]
        // });
    });

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
</script>
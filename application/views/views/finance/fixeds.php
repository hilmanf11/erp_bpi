<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'number',width:200,halign:'center'">Asset No</th>
            <th data-options="field:'name',width:200,halign:'center'">Asset Name</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'asset_category_name',width:200,halign:'center'">Asset Category</th>
            <th rowspan="2" data-options="field:'asset_category_type',width:120,halign:'center'">Asset Type</th>
            <th rowspan="2" data-options="field:'purchase_invoice_number',width:150,halign:'center'">Purchase Invoice No</th>
            <th rowspan="2" data-options="field:'supplier_name',width:200,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Purchase Date</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right'">Qty</th>
            <th rowspan="2" data-options="field:'cost',width:100,halign:'center',align:'right', formatter:priceformat">Cost</th>
            <th colspan="2" data-options="field:'',width:100,align:'center'">Estimated</th>
            <th rowspan="2" data-options="field:'expired_date',width:100,align:'center'">Expired Date</th>
            <th rowspan="2" data-options="field:'depreciation',width:100,halign:'center',align:'right', formatter:priceformat">Depreciation</th>
            <th rowspan="2" data-options="field:'depreciation_acc',width:100,halign:'center',align:'right', formatter:priceformat">Accumulation<br>Depreciation</th>
            <th rowspan="2" data-options="field:'book_value',width:100,halign:'center',align:'right', formatter:priceformat">Book<br>Value</th>
            <th rowspan="2" data-options="field:'method',width:100,halign:'center'">Depreciation<br>Method</th>
            <th rowspan="2" data-options="field:'departement',width:100,halign:'center'">Departement</th>
            <th rowspan="2" data-options="field:'location',width:100,halign:'center'">Location</th>
            <th rowspan="2" data-options="field:'status_expired',width:100,align:'center',formatter:statusformat,styler:statusStyle">Status</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'estimate_year',width:80,align:'center'"> Year</th>
            <th data-options="field:'estimate_month',width:80,align:'center'"> Month</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 225px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Date</span>
                    <input style="width:28%;" id="filter_from" value="<?= $filter_from ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Category</span>
                    <input style="width:60%;" id="filter_category" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset No</span>
                    <input style="width:60%;" id="filter_number" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Estimate Economic</span>
                    <select style="width:60%;" id="filter_estimate" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose Estimate Economic</option>
                        <option value="1">1 Year</option>
                        <option value="2">2 Year</option>
                        <option value="3">3 Year</option>
                        <option value="4">4 Year</option>
                        <option value="5">5 Year</option>
                        <option value="6">6 Year</option>
                        <option value="7">7 Year</option>
                        <option value="8">8 Year</option>
                        <option value="9">9 Year</option>
                        <option value="10">10 Year</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Invoice No</span>
                    <input style="width:60%;" id="filter_purchase_invoice_number" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combobox">
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
                    <input style="width:60%;" name="purchase_invoice_number" id="purchase_invoice_number" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset No</span>
                    <input style="width:60%;" name="number" id="number" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Name</span>
                    <input style="width:60%;" name="name" id="name" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Category</span>
                    <input style="width:60%;" name="asset_category_number" id="asset_category_number" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Date</span>
                    <input style="width:60%;" name="trans_date" id="trans_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="supplier_name" id="supplier_name" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty</span>
                    <input style="width:30%;" name="qty" id="qty" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Cost</span>
                    <input style="width:30%;" name="cost" id="cost" readonly class="easyui-numberbox" data-options="groupSeparator:'.',decimalSeparator:','">
                    <input style="width:30%;" name="currency" id="currency" readonly class="easyui-textbox" data-options="prompt:'Curency'">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Estimated Economic</span>
                    <input style="width:30%;" name="estimate_year" id="estimate_year" required class="easyui-numberbox" data-options="prompt:'Year'">
                    <input style="width:30%;" name="estimate_month" id="estimate_month" required readonly class="easyui-numberbox" data-options="prompt:'Month'">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Expired Date</span>
                    <input style="width:60%;" name="expired_date" id="expired_date" class="easyui-datebox" required readonly data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Depreciation</span>
                    <input style="width:40%;" name="depreciation" id="depreciation" readonly required class="easyui-numberbox" data-options="groupSeparator:'.',decimalSeparator:','">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%; height: 80px;" name="remarks" id="remarks" class="easyui-textbox" multiline="true">
                </div>
            </div>
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>General Information</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Depreciation Method</span>
                        <input style="width:50%;" name="method" id="method" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Previous Departement</span>
                        <input style="width:50%;" id="previous_departement" readonly class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Previous Location</span>
                        <input style="width:50%;" id="previous_location" readonly class="easyui-textbox">
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Current Departement</span>
                        <input style="width:50%;" name="departement" id="departement" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Current Location</span>
                        <input style="width:50%;" name="location" id="location" class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
            <div style="width: 30%; float: right;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Total Asset</span>
                    <input style="width:60%;" name="total" id="total" readonly class="easyui-numberbox" data-options="groupSeparator:'.',decimalSeparator:','">
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
<iframe id="printout" src="<?= base_url('finance/fixeds/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('finance/fixeds/create') ?>';
        $('#frm_insert').form('clear');

        //GET PURCHASE INVOICING
        $('#purchase_invoice_number').combogrid({
            url: '<?= base_url('finance/fixeds/readPi') ?>',
            panelWidth: 300,
            idField: 'number',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Purchase Invoice",
            columns: [
                [{
                    field: 'number',
                    title: 'Purchase Invoice',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Product Family',
                    width: 150
                }, ]
            ],
            onSelect: function(val, row) {
                $('#number').combogrid({
                    url: '<?= base_url('finance/fixeds/readProductPi/') ?>' + window.btoa(row.number),
                    panelWidth: 400,
                    idField: 'item_no',
                    textField: 'item_no',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Asset No",
                    columns: [
                        [{
                            field: 'item_no',
                            title: 'Asset No',
                            width: 200
                        }, {
                            field: 'item_name',
                            title: 'Asset Name',
                            width: 150
                        }, ]
                    ],
                    onSelect: function(val2, row2) {
                        $.ajax({
                            type: "post",
                            url: "<?= base_url('finance/fixeds/readExchangeRates') ?>",
                            data: "trans_date=" + row2.trans_date + "&currency=" + row2.currency,
                            dataType: "json",
                            success: function(exchange) {
                                if (exchange.length > 0) {
                                    $("#cost").numberbox('setValue', parseFloat(row2.price * parseFloat(exchange[0].middle)));
                                    $("#total").numberbox('setValue', (parseFloat(row2.qty) * parseFloat(row2.price * parseFloat(exchange[0].middle))));
                                } else {
                                    $("#cost").numberbox('setValue', row2.price);
                                    $("#total").numberbox('setValue', (parseFloat(row2.qty) * parseFloat(row2.price)));
                                }
                            }
                        });

                        $("#name").textbox('setValue', row2.item_name);
                        $("#trans_date").datebox('setValue', row2.trans_date);
                        $("#supplier_name").textbox('setValue', row2.supplier_name);
                        $("#qty").numberbox('setValue', row2.qty);
                        $("#currency").textbox('setValue', "IDR");
                    }
                });
            }
        });
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('finance/fixeds/update') ?>?id=' + btoa(row.id);
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
                    Swal.fire({
                        title: 'Please Wait for Deleting Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        // $.ajax({
                        //     type: "post",
                        //     url: "<?= base_url('closing/locks/checkLock') ?>",
                        //     data: "period=" + row.trans_date + "&menus_id=<?= $menus_id ?>",
                        //     dataType: "json",
                        //     success: function (lock) {
                        //         if(lock.total > 0){
                        //             toastr.error("This period is not active by Accounting");
                        //             return false;
                        //         }

                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('finance/fixeds/delete') ?>',
                                    data: {
                                        id: row.id
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');

                                        if (i == rows.length) {
                                            Swal.close();
                                            $('#dg').datagrid('reload');
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error(jqXHR.statusText);
                                        $.messager.alert("Error", jqXHR.statusText, 'error');
                                    },
                                    complete: function(data) {
                                        //$('#dg').datagrid('reload');
                                    }
                                });
                        //     }
                        // });
                    }

                    $('#dg').datagrid('reload');
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_number = $("#filter_number").combobox('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_estimate = $("#filter_estimate").combobox('getValue');
        var filter_purchase_invoice_number = $("#filter_purchase_invoice_number").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_number=" + window.btoa(filter_number) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_estimate=" + window.btoa(filter_estimate) +
            "&filter_purchase_invoice_number=" + window.btoa(filter_purchase_invoice_number) +
            "&filter_supplier=" + window.btoa(filter_supplier);

        $('#dg').datagrid({
            url: '<?= base_url('finance/fixeds/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/fixeds/print') ?>' + url);
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_asset_fixeds.xls') ?>');
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_number = $("#filter_number").combobox('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_estimate = $("#filter_estimate").combobox('getValue');
        var filter_purchase_invoice_number = $("#filter_purchase_invoice_number").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_number=" + window.btoa(filter_number) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_estimate=" + window.btoa(filter_estimate) +
            "&filter_purchase_invoice_number=" + window.btoa(filter_purchase_invoice_number) +
            "&filter_supplier=" + window.btoa(filter_supplier);

        window.location.assign('<?= base_url('finance/fixeds/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('finance/fixeds/datatables') ?>',
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
                    var trans_date = $("#trans_date").datebox('getValue');

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('closing/locks/checkLock') ?>",
                        data: "period=" + trans_date + "&menus_id=<?= $menus_id ?>",
                        dataType: "json",
                        success: function (lock) {
                            if(lock.total > 0){
                                toastr.error("This period is not active by Accounting");
                                return false;
                            }

                            $('#frm_insert').form('submit', {
                                url: url_save,
                                onSubmit: function() {
                                    if ($(this).form('validate') == true) {
                                        $('#dlg_insert').dialog('close');
                                        Swal.fire({
                                            title: 'Please Wait for Saving Data',
                                            showConfirmButton: false,
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            },
                                        });
                                    }
                                },
                                success: function(result) {
                                    Swal.close();
                                    var result = eval('(' + result + ')');
                                    if (result.theme == "success") {
                                        toastr.success(result.message, result.title);
                                    } else {
                                        toastr.error(result.message, result.title);
                                    }

                                    $('#dg').datagrid('reload');
                                }
                            });
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
                    window.open('<?= base_url('finance/fixeds/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('finance/fixeds/upload') ?>',
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
                                url: "<?= base_url('finance/fixeds/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('finance/fixeds/uploadCreate') ?>",
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
                                                    url: "<?= base_url('finance/fixeds/uploadcreateFailed') ?>",
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

        $("#asset_category_number").combobox({
            url: '<?= base_url('finance/categories/reads') ?>',
            valueField: 'number',
            textField: 'name',
            prompt: "Choose Category",
        });

        $("#filter_category").combobox({
            url: '<?= base_url('finance/categories/reads') ?>',
            valueField: 'number',
            textField: 'name',
            prompt: "Choose Category",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(category) {
                $("#filter_number").combogrid({
                    url: '<?= base_url('finance/fixeds/readNumber/') ?>' + btoa(category.number),
                    panelWidth: 450,
                    idField: 'number',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Asset No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'number',
                            title: 'Asset No',
                            width: 150
                        }, {
                            field: 'name',
                            title: 'Asset Name',
                            width: 250
                        }, ]
                    ],
                });
            }
        });

        $("#filter_purchase_invoice_number").combobox({
            url: '<?= base_url('finance/fixeds/readPurchaseInvoiceNumber') ?>',
            valueField: 'purchase_invoice_number',
            textField: 'purchase_invoice_number',
            prompt: "Choose Purchase Invoice",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_supplier").combobox({
            url: '<?= base_url('finance/fixeds/readSupplier') ?>',
            valueField: 'supplier_name',
            textField: 'supplier_name',
            prompt: "Choose Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#estimate_year").numberbox({
            onChange: function(val) {
                var cost = $("#cost").numberbox('getValue');
                var trans_date = $("#trans_date").datebox('getValue');

                if(trans_date != ""){
                    $("#estimate_month").numberbox('setValue', (parseInt(val) * 12));
                    $("#depreciation").numberbox('setValue', (cost / (parseInt(val) * 12)));

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('finance/fixeds/readExpired/') ?>" + (parseInt(val) * 12) + "/" + btoa(trans_date),
                        dataType: "html",
                        success: function (response) {
                            $("#expired_date").datebox('setValue', response);
                        }
                    });
                }else{
                    toastr.error("Please Select Purchase Date First");
                    $("#estimate_year").numberbox('clear');
                }
            }
        })
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

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>ACTIVE</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>EXPIRED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        }
    }

    function priceformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat("id-ID", {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }
</script>
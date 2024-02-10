<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'id',align:'center',width:100">Product ID</th>
            <th rowspan="2" data-options="field:'number',halign:'center',width:150">Product Number</th>
            <th rowspan="2" data-options="field:'name',halign:'center',width:150">Product Name</th>
            <th rowspan="2" data-options="field:'specification',align:'center',width:100">Specification</th>
            <th rowspan="2" data-options="field:'type',align:'center',width:120">Product Type</th>
            <th rowspan="2" data-options="field:'uom',halign:'center',width:150">Unit Of Measure</th>
            <th rowspan="2" data-options="field:'item_category_name',halign:'center',width:150">Category</th>
            <th rowspan="2" data-options="field:'item_familys_name',halign:'center',width:150">Product Family</th>
            <th rowspan="2" data-options="field:'item_family_sub_name',halign:'center',width:150">Sub Product Family</th>
            <th rowspan="2" data-options="field:'leadtime',halign:'center',width:150">Lead Time Production</th>
            <th rowspan="2" data-options="field:'weight',halign:'center',width:100">Weight (gr)</th>
            <th rowspan="2" data-options="field:'mpq',halign:'center',width:100">MPQ</th>
            <th rowspan="2" data-options="field:'moq',align:'center',width:100">MOQ</th>
            <th rowspan="2" data-options="field:'safety_stock',align:'center',width:150">Safety Stock (%)</th>
            <th rowspan="2" data-options="field:'lot',width:100,halign:'center'">Lot</th>
            <th rowspan="2" data-options="field:'lifetime',width:100,halign:'center'">Lifetime</th>
            <th rowspan="2" data-options="field:'min',halign:'center',width:80">Min</th>
            <th rowspan="2" data-options="field:'max',halign:'center',width:80">Max</th>
            <th rowspan="2" data-options="field:'status',width:150, styler:cellStyler, formatter:cellFormatter, align:'center'">Status</th>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; padding:20px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width:50%;float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product ID</span>
                <input style="width:30%;" name="id" id="id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Number</span>
                <input style="width:60%;" name="number" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Name</span>
                <input style="width:60%;" name="name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Specification</span>
                <input style="width:60%;" name="specification" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Type</span>
                <select style="width:30%;" name="type" class="easyui-combobox" panelHeight="auto">
                    <option value="EXPORT">EXPORT</option>
                    <option value="IMPORT">IMPORT</option>
                    <option value="LOCAL">LOCAL</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Category</span>
                <input style="width:60%;" name="item_category_number" id="item_category_number" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" name="item_family_number" id="item_family_number" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sub Product Family</span>
                <input style="width:60%;" name="item_family_sub_number" id="item_family_sub_number" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Uom</span>
                <input style="width:30%;" name="uom" id="uom" required="" class="easyui-combobox">
            </div>
            </div>

            <div style="width:50%;float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Leadtime Production</span>
                <input style="width:30%;" name="leadtime" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Weight (gr)</span>
                <input style="width:30%;" name="weight" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">MPQ</span>
                <input style="width:30%;" name="mpq" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">MOQ</span>
                <input style="width:30%;" name="moq" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Safety Stock (%)</span>
                <input style="width:30%;" name="safety_stock" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Lot</span>
                <input style="width:30%;" name="lot" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Lifetime</span>
                <input style="width:30%;" name="lifetime" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Min</span>
                <input style="width:30%;" name="min" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Max</span>
                <input style="width:30%;" name="max" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:30%;" name="status" required="" panelHeight="auto" class="easyui-combobox">
                    <option value="0">Active</option>
                    <option value="1">Inactive</option>
                </select>
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
<iframe id="printout" src="<?= base_url('master/items/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/items/create') ?>';
        $('#frm_insert').form('clear')
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/items/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/items/delete') ?>',
                            data: {
                                id: row.id
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

   //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_items.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/items/print/excel') ?>');
    }
    
    //RELOAD
    function reload() {
        window.location.reload();
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
            return 'Inactive';
        }
    };

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/items/datatables') ?>',
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

        //GET CURRENCY
        $('#currency').combogrid({
            url: '<?= base_url('master/currencies/reads') ?>',
            panelWidth: 420,
            idField: 'name',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Currency",
            columns: [
                [{
                    field: 'symbol',
                    title: 'Symbol',
                    width: 100
                }, {
                    field: 'number',
                    title: 'Currency ID',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Currency Name',
                    width: 250
                }, ]
            ]
        });

         //Upload Data
         $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('master/items/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('master/items/upload') ?>',
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
                                url: "<?= base_url('master/items/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('master/items/uploadCreate') ?>",
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
                                                    url: "<?= base_url('master/items/uploadcreateFailed') ?>",
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


        $('#item_category_number').combobox({
            url: '<?php echo base_url('master/item_categories/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: "Choose Category",
            onSelect: function(item_categories) {
                $('#item_family_number').combobox({
                    url: '<?php echo base_url('master/item_familys/reads'); ?>/' + item_categories.number,
                    valueField: 'number',
                    textField: 'name',
                    prompt: "Choose Family Product",
                    onSelect: function(item_family_subs){
                        $.ajax({
                            type: "post",
                            url: '<?php echo base_url('master/items/autoid/'); ?>' + item_categories.number + '/' + item_family_subs.number,
                            dataType: "html",
                            success: function (response) {
                                $('#id').textbox('setValue', response);
                            }
                        });

                        $('#item_family_sub_number').combobox({
                            url: '<?php echo base_url('master/item_family_subs/reads'); ?>/' + item_family_subs.number,
                            valueField: 'number',
                            textField: 'name',
                            prompt: "Choose Sub Family Product",
                            onSelect: function(item_family){
                                $.ajax({
                                    type: "post",
                                    url: '<?php echo base_url('master/items/autoid/'); ?>' + item_categories.number + '/' + item_family_subs.number + '/' + item_family.number,
                                    dataType: "html",
                                    success: function (response) {
                                        $('#id').textbox('setValue', response);
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
        
        $('#uom').combobox({
            url: '<?= base_url('master/uom/reads') ?>',
            valueField: 'name',
            textField: 'name',
            prompt: "Choose Unit Of Measure"
        });
    });
</script>
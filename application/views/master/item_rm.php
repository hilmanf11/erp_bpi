<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Unit of Measure is taken from <b>Master Data > General Master > Unit of Measure</b></li>
                <li>The Data Category is taken from <b>Master Data > General Master > Category</b></li>
                <li>The Data Product Family is taken from <b>Master Data > Engineering > Product Family</b></li>
                <li>The Data Product Family Sub is taken from <b>Master Data > Engineering > Product Family Sub</b></li>
            </ul>
        </div>
    </div>
</div>
<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead data-options="frozen:true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'id',width:150,align:'center'">Part ID</th>
            <th data-options="field:'number',width:200,halign:'center'">Part No</th>
            <th data-options="field:'name',width:150,halign:'center'">Part Name</th>
        </tr>
    </thead>

    <thead>
        <tr>
            <th rowspan="2" data-options="field:'uom',width:100,halign:'center'">Uom</th>
            <th rowspan="2" data-options="field:'division',width:100,halign:'center'">Division</th>
            <th rowspan="2" data-options="field:'item_category_name',width:150,halign:'center'">Category</th>
            <th rowspan="2" data-options="field:'item_family_name',width:150,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'color',width:100,halign:'center'">Color</th>
            <th rowspan="2" data-options="field:'item_sub_family_name',width:150,halign:'center'">Sub Product Family</th>
            <th rowspan="2" data-options="field:'account_number',width:150,halign:'center'">Account No</th>
            <th rowspan="2" data-options="field:'account_name',width:150,halign:'center'">Account Name</th>
            <th rowspan="2" data-options="field:'kind',width:150,halign:'center'">Kind</th>
            <th rowspan="2" data-options="field:'length',width:100,halign:'center'">Length</th>
            <th rowspan="2" data-options="field:'width',width:100,halign:'center'">Width</th>
            <th rowspan="2" data-options="field:'thickness',width:100,halign:'center'">Thickness</th>
            <th rowspan="2" data-options="field:'diameter',width:100,halign:'center'">Diameter</th>
            <th rowspan="2" data-options="field:'density',width:100,halign:'center'">Density</th>
            <th rowspan="2" data-options="field:'volume',width:100,halign:'center'">Volume</th>
            <th rowspan="2" data-options="field:'weight_gr',width:100,halign:'center'">Weight (GR)</th>
            <th rowspan="2" data-options="field:'weight_kg',width:100,halign:'center'">Weight (KG)</th>
            <th rowspan="2" data-options="field:'description',width:150,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'supply',width:80,halign:'center', styler:cellStyler, formatter:cellFormatterSup">Supply</th>
            <th rowspan="2" data-options="field:'status',width:80,halign:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1300px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 33%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part ID</span>
                    <input style="width:60%;" name="id" id="id" required="" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part Name</span>
                    <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Unit of Measure</span>
                    <input style="width:60%;" name="uom" id="uom" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" name="item_category_id" id="item_category_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" name="item_family_id" id="item_family_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Color</span>
                    <input style="width:60%;" name="color" id="color" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family Sub</span>
                    <input style="width:60%;" name="item_sub_family_id" id="item_sub_family_id" class="easyui-combobox">
                </div>
            </div>

            <div style="width: 33%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" name="division" id="division" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Account No</span>
                    <input style="width:60%;" name="account_number" id="account_number" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Account Name</span>
                    <input style="width:60%;" name="account_name" id="account_name" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Description</span>
                    <input style="width:60%;" name="description" id="description" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supply</span>
                    <select style="width:60%;" name="supply" id="supply" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="0">YES</option>
                        <option value="1">NO</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Kind</span>
                    <input style="width:60%;" name="kind" id="kind" readonly class="easyui-textbox" >
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Length</span>
                    <input style="width:60%;" name="length" id="length" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Width</span>
                    <input style="width:60%;" name="width" id="width" class="easyui-numberbox">
                </div>
            </div>

            <div style="width: 33%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Thickness</span>
                    <input style="width:60%;" name="thickness" id="thickness" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Diameter</span>
                    <input style="width:60%;" name="diameter" id="diameter" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Density</span>
                    <input style="width:60%;" name="density" id="density" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Volume</span>
                    <input style="width:60%;" name="volume" id="volume" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Weight (GR)</span>
                    <input style="width:60%;" name="weight_gr" id="weight_gr" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Weight (KG)</span>
                    <input style="width:60%;" name="weight_kg" id="weight_kg"  precision="2" class="easyui-numberbox">
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
<iframe id="printout" src="<?= base_url('master/item_rm/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/item_rm/create') ?>';
        $('#frm_insert').form('clear');
        $('#status').combobox('setValue', '0');
        $('#supply').combobox('setValue', 'NO');
        $("#division").combobox('setValue','INJ');
        $("#weight_kg").numberbox('setValue','1');
        $('#id').textbox('enable');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        console.log(row);
        $('#id').textbox('disable');

        setTimeout(function() { 
            $('#id').textbox('setValue', row.id);
        }, 500);

        // $('#item_sub_family_id').combobox({
        //     url:'<?= base_url('master/item_family_subs/reads_number/'); ?>',
        //     valueField:'id',
        //     textField:'number',
        //     prompt: 'Choose Sub Product Family',
        //     onLoadSuccess: function(){
        //         if (row.item_sub_family_id) {
        //             $('#item_sub_family_id').combobox('setValue', row.item_sub_family_id);
        //         }
        //     },
        //     onSelect: function (family_sub) {
        //         $('#kind').textbox('setValue', family_sub.kind);
        //         $('#density').textbox('setValue', family_sub.density);
        //     }
        // });

        $('#item_category_id').combobox({
            url: '<?= base_url('master/item_categories/readsnotfg'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Category',
            onLoadSuccess: function () {
                $('#item_category_id').combobox('setValue', row.item_category_id);
            },
            onSelect: function (category) {
                if (category.id !== 'C01') {
                    $('#supply').combobox('setValue', 'NO');
                } else {
                    $('#supply').combobox('setValue', 'YES');
                }
                
                $('#item_family_id').combogrid({
                    url:'<?= base_url('master/item_rm/readFamily/'); ?>' + category.id,
                    panelWidth: 300,
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: 'Choose Product Family',
                    columns: [
                        [{
                            field: 'name',
                            title: 'Product Family',
                            width: 200
                        }, {
                            field: 'division_number',
                            title: 'Div',
                            width: 100
                        }]
                    ],
                    onLoadSuccess: function (data) {
                        if (row && row.item_family_id) {
                            const match = data.rows.find(item => item.id == row.item_family_id);
                            if (match) {
                                $('#item_family_id').combogrid('setValue', row.item_family_id);
                            }
                        }
                    },
                    onSelect: function(value, rows) {
                        $('#account_number').textbox('setValue', rows.account_number);
                        $('#account_name').textbox('setValue', rows.account_name);
                        
                        $('#item_sub_family_id').combobox({
                            url: '<?= base_url('master/item_family_subs/reads/'); ?>' + rows.id,
                            valueField: 'id',
                            textField: 'name',
                            editable: false,
                            prompt: 'Choose Sub Product Family',
                            onLoadSuccess: function () {
                                $('#item_sub_family_id').combobox('setValue', row.item_sub_family_id);
                            },
                            onSelect: function (family_sub) {
                                $('#kind').textbox('setValue', family_sub.kind);
                                $('#density').textbox('setValue', family_sub.density);
                            }
                        });
                    }
                });
            }
        });

        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/item_rm/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/item_rm/delete') ?>',
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
        window.location.assign('<?= base_url('template/tmp_item_rm.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/item_rm/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/item_rm/datatables') ?>',
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

    $('#division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'number',
        textField: 'number',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
    }); 

    $('#uom').combobox({
        url:'<?= base_url('master/uom/reads'); ?>',
        valueField:'name',
        textField:'name',
        prompt: 'Choose Unit of Measure',
    });

    $('#item_category_id').combobox({
        url:'<?= base_url('master/item_categories/readsnotfg'); ?>',
        valueField:'id',
        textField:'name',
        prompt: 'Choose Category',
        onSelect: function(category){
            if (category.id !== 'C01') {
                $('#supply').combobox('setValue', 'NO');
            } else {
                $('#supply').combobox('setValue', 'YES');
            }

            $('#item_family_id').combogrid({
                url:'<?= base_url('master/item_rm/readFamily/'); ?>' + category.id,
                panelWidth: 300,
                idField: 'id',
                textField: 'name',
                mode: 'remote',
                fitColumns: true,
                prompt: 'Choose Product Family',
                columns: [
                    [{
                        field: 'name',
                        title: 'Product Family',
                        width: 200
                    }, {
                        field: 'division_number',
                        title: 'Div',
                        width: 100
                    }]
                ],
                onSelect: function(value, rows) {
                    $('#account_number').textbox('setValue',rows.account_number);
                    $('#account_name').textbox('setValue',rows.account_name);
                    $('#item_sub_family_id').combobox({
                        url:'<?= base_url('master/item_family_subs/reads/'); ?>' + rows.id,
                        valueField:'id',
                        textField:'name',
                        editable: false,
                        prompt: 'Choose Sub Product Family',
                        onSelect: function(family_sub) {
                            $('#kind').textbox('setValue',family_sub.kind);
                            $('#density').textbox('setValue',family_sub.density);
                        }
                    });
                    $.ajax({
                        type : "post",
                        url : "<?= base_url('master/item_rm/autoid/')?>" + category.number + "/" + rows.number,
                        dataType : "html",
                        success : function(response){
                            $('#id').textbox('setValue', response);
                        }
                    });
                }
            });

            // $('#item_family_id').combobox({
            //     url:'<?= base_url('master/item_rm/readFamily/'); ?>' + category.id,
            //     valueField:'id',
            //     textField:'name',
            //     prompt: 'Choose Product Family',
            //     onSelect: function(family) {
            //         $('#account_number').textbox('setValue',family.account_number);
            //         $('#account_name').textbox('setValue',family.account_name);
            //         $('#item_sub_family_id').combobox({
            //             url:'<?= base_url('master/item_family_subs/reads/'); ?>' + family.id,
            //             valueField:'id',
            //             textField:'name',
            //             editable: false,
            //             prompt: 'Choose Sub Product Family',
            //             onSelect: function(family_sub) {
            //                 $('#kind').textbox('setValue',family_sub.kind);
            //                 $('#density').textbox('setValue',family_sub.density);
            //             }
            //         });
            //         $.ajax({
            //             type : "post",
            //             url : "<?= base_url('master/item_rm/autoid/')?>" + category.number + "/" + family.number,
            //             dataType : "html",
            //             success : function(response){
            //                 $('#id').textbox('setValue', response);
            //             }
            //         });
            //     }
            // });
        }
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
     //FORMATTER STATUS
     function cellFormatterSup(value) {
        if (value == 0) {
            return 'YES';
        } else {
            return 'NO';
        }
    };

    // function formatDecimal(value) {
    //     return parseFloat(value).toFixed(2);
    // }

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/item_rm/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                // Validasi form dan tampilkan progress
                if (!$('#frm_upload').form('validate')) {
                    return;
                }

                $.messager.progress({
                    title: 'Please Wait',
                    msg: 'Importing Excel to Database'
                });

                // Gunakan FormData untuk mengirim data formulir dan file (pengganti eval() tidak works di Chrome)
                var formData = new FormData($('#frm_upload')[0]);

                $.ajax({
                    url: '<?= base_url('master/item_rm/uploadclearFailed') ?>',
                    type: 'POST',
                    async: false, // Penting: pastikan proses selesai sebelum lanjut
                    success: function() {
                        console.log('Previous failed records cleared successfully.');
                    },
                    error: function() {
                        console.error('Failed to clear previous failed records.');
                    }
                });

                $.ajax({
                    url: '<?= base_url('master/item_rm/upload') ?>',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    processData: false, // Penting: Jangan memproses data
                    contentType: false, // Penting: Biarkan jQuery mengatur Content-Type
                    success: function(json) {
                        $.messager.progress('close');
                        requestData(json.total, json.data);
                    },
                    error: function(xhr, status, error) {
                        $.messager.progress('close');
                        console.error('AJAX Error:', error);
                        $.messager.alert('Error', 'Invalid JSON response from server. Change browser or contact admin.', 'error');
                    }
                });

                // Lanjutkan dengan fungsi rekursi seperti sebelumnya (di dalam success handler)
                function requestData(total, data_array, number = 1, success = 0, failed = 0) {
                    if (number > total) {
                        $.messager.alert('Upload Finished', `Import process completed.<br>Successful: ${success}<br>Failed: ${failed}`, 'info');
                        $('#dg').datagrid('reload');
                        return;
                    }

                    let value = Math.floor((number / total) * 100);
                    $('#p_upload').progressbar('setValue', value);
                    $('#p_start').html(number);
                    $('#p_finish').html(total);
                    $('#p_success').html(success);
                    $('#p_failed').html(failed);
                    
                    $.ajax({
                        type: "POST",
                        url: "<?= base_url('master/item_rm/uploadCreate') ?>",
                        data: { "data": data_array[number - 1] },
                        dataType: "json",
                        success: function(result_item_create) {
                            let title = '';
                            if (result_item_create.theme === "success") {
                                title = `<b style='color: green;'>${result_item_create.title}</b> | ${result_item_create.message}`;
                                success++;
                            } else {
                                title = `<b style='color: red;'>${result_item_create.title}</b> | ${result_item_create.message}`;
                                failed++;
                                
                                $.ajax({
                                    type: "POST",
                                    url: "<?= base_url('master/item_rm/uploadcreateFailed') ?>",
                                    data: { data: data_array[number - 1], message: result_item_create.message },
                                    cache: false
                                });
                            }
                            
                            $("#p_remarks").append(title + "<br>");
                            requestData(total, data_array, number + 1, success, failed);
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', error);
                            failed++;
                            $("#p_remarks").append(`<b style='color: red;'>Error</b> | Failed to process item: ${error}<br>`);
                            requestData(total, data_array, number + 1, success, failed);
                        }
                    });
                }
            }
        }]
    });

    // UPLOAD DATA BACKUP (error eval() di Chrome)
    // $('#dlg_upload').dialog({
    //     buttons: [{
    //         text: 'List Failed',
    //         handler: function() {
    //             window.open('<?= base_url('master/item_rm/uploadDownloadFailed') ?>', '_blank');
    //         }
    //     }, {
    //         text: 'Upload',
    //         iconCls: 'icon-ok',
    //         handler: function() {
    //             $('#frm_upload').form('submit', {
    //                 url: '<?= base_url('master/item_rm/upload') ?>',
    //                 onSubmit: function() {
    //                     if ($(this).form('validate') == false) {
    //                         return $(this).form('validate');
    //                     } else {
    //                         $.messager.progress({
    //                             title: 'Please Wait',
    //                             msg: 'Importing Excel to Database'
    //                         });
    //                     }
    //                 },
    //                 success: function(result) {
    //                     $.messager.progress('close');
    //                     //Clear File
    //                     $.ajax({
    //                         url: "<?= base_url('master/item_rm/uploadclearFailed') ?>"
    //                     });
    //                     var json = eval('(' + result + ')');
    //                     requestData(json.total, json);

    //                     function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
    //                         if (value < 100) {
    //                             value = Math.floor((number / total) * 100);
    //                             $('#p_upload').progressbar('setValue', value);
    //                             $('#p_start').html(number);
    //                             $('#p_finish').html(total);

    //                             $.ajax({
    //                                 type: "POST",
    //                                 async: true,
    //                                 url: "<?= base_url('master/item_rm/uploadCreate') ?>",
    //                                 data: {
    //                                     "data": json[number - 1]
    //                                 },
    //                                 cache: false,
    //                                 dataType: "json",
    //                                 success: function(result) {
    //                                     if (result.theme == "success") {
    //                                         $('#p_success').html(success);
    //                                         var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
    //                                         requestData(total, json, number + 1, value, success + 1, failed + 0);
    //                                     } else {
    //                                         $('#p_failed').html(failed);
    //                                         var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
    //                                         //Json Failed
    //                                         $.ajax({
    //                                             type: "POST",
    //                                             async: true,
    //                                             url: "<?= base_url('master/item_rm/uploadcreateFailed') ?>",
    //                                             data: {
    //                                                 data: json[number - 1],
    //                                                 message: result.message
    //                                             },
    //                                             cache: false
    //                                         });
    //                                         requestData(total, json, number + 1, value, success + 0, failed + 1);
    //                                     }
    //                                     $("#p_remarks").append(title + "<br>");
    //                                 }
    //                             });
    //                         }
    //                     }
    //                 }
    //             });
    //         }
    //     }]
    // });

    $('#length').numberbox({ 
        onChange: function(value) {
            calculateVolume();
        }
    });

    $('#diameter').numberbox({ 
        onChange: function(value) {
            calculateVolume();
        }
    });

    $('#width').numberbox({ 
        precision: 2,
        onChange: function(value) {
            calculateVolume();
        }
    });

    $('#thickness').numberbox({ 
        onChange: function(value) {
            calculateVolume();
        }
    });

    $('#volume').numberbox({ 
        onChange: function(value) {
            calculateVolume();
        }
    });

    $('#weight_kg').numberbox({ 
        precision: 3
    });

    function calculateVolume() {
        // Ambil nilai input
        var kind = document.getElementById("kind").value;
        var length = parseFloat(document.getElementById("length").value) || 0;
        var width = parseFloat(document.getElementById("width").value) || 0;
        var thickness = parseFloat(document.getElementById("thickness").value) || 0;
        var diameter = parseFloat(document.getElementById("diameter").value) || 0;
        var density = parseFloat(document.getElementById("density").value) || 0;

        console.log(density);
        
        var volume = 0;
        var weightGr = 0;
        var weightKg = 0;

        if (kind.toUpperCase() === "TUBE") {
            volume = 3.14*(diameter/2)*(diameter/2)*length;
        } else if (kind.toUpperCase() === "CUBE") {
            volume = length * width * thickness;
        }

        weightGr = density * volume;
        weightKg = weightGr / 1000000;

        // Set nilai ke input
        $('#volume').numberbox('setValue', volume);
        $('#weight_gr').numberbox('setValue', weightGr);
        $('#weight_kg').numberbox('setValue', weightKg);
    }
</script>
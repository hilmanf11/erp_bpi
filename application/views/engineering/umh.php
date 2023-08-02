<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-treegrid" style="width:100%; height: 670px;" toolbar="#toolbar" idField="id" treeField="name" singleSelect="true" rownumbers="true" url="<?= base_url('engineering/umh/datatables') ?>">
    <thead data-options="frozen:true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'name',width:400,halign:'center'">Customer / Product No</th>
            <th data-options="field:'circuit',width:100,align:'center'">Circuit</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <?php
            foreach ($main_process as $main) {
                echo '<th colspan="' . $main['total'] . '" data-options="width:100"> ' . $main['name'] . '</th>';
            }
            ?>
            <th rowspan="2" data-options="field:'total',width:100,align:'center'">Total UMH</th>
        </tr>
        <tr>
            <?php
            foreach ($main_process_sub as $sub) {
                $proces_field = "field:'" . $sub['number'] . "',width:100,align:'center', styler:cellStyler";
                echo '<th data-options="' . $proces_field . '"> ' . $sub['name'] . '</th>';
            }
            ?>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 190px;">
    <fieldset style="width:99.5%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div class="fitem">
            <span style="width:200px; display:inline-block;">Customer</span>
            <input style="width:300px;" name="filter_customer_id" id="filter_customer_id" class="easyui-combogrid">
        </div>
        <div class="fitem">
            <span style="width:200px; display:inline-block;">Product No</span>
            <input style="width:300px;" name="filter_item_id" id="filter_item_id" class="easyui-combogrid">
        </div>
        <div class="fitem">
            <span style="width:200px; display:inline-block;"></span>
            <a href="javascript:300px;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" name="customer_id" id="customer_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="item_id" id="item_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Circuit</span>
                <input style="width:60%;" id="circuit" name="circuit" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Main Process</span>
                <input style="width:60%;" name="main_process_id" id="main_process_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Main Process Sub</span>
                <input style="width:60%;" name="main_process_sub_id" id="main_process_sub_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cycle Time</span>
                <input style="width:60%;" name="cycle_time" required="" class="easyui-numberbox" precision="2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Description</span>
                <input style="width:60%;" name="description" class="easyui-textbox">
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
<iframe id="printout" src="<?= base_url('engineering/umh/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('engineering/umh/create') ?>';
        $('#frm_insert').form('clear');
        $("#customer_id").combogrid('enable');
        $("#item_id").combogrid('enable');
        $("#circuit").combobox('enable');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            var result = row.id.split('_');
            if(result[0] == "3"){
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                $("#customer_id").combogrid('disable');
                $("#item_id").combogrid('disable');
                $("#circuit").combobox('disable');

                url_save = '<?= base_url('engineering/umh/update') ?>?item_id=' + row.item_id + '&circuit=' + row.circuit + '&customer_id=' + row.customer_id;
            }else{
                toastr.info("Please select level 3 in the table for update!");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            var result = row.id.split('_');
            if(result[0] == "3"){
                $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                    if (r) {
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('engineering/umh/delete') ?>',
                            data: {
                                customer_id: row.customer_id,
                                item_id: row.item_id,
                                circuit: row.circuit,
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                                if (result.theme == "success") {
                                    toastr.success(result.message, result.title);
                                } else {
                                    toastr.error(result.message, result.title);
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
                            },
                            complete: function(data) {
                                $('#dg').treegrid('reload');
                            }
                        });
                    }
                });
            }else{
                toastr.info("Please select level 3 in the table for update!");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_umh.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_customer_id = $("#filter_customer_id").combogrid('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var url = "?filter_customer_id=" + filter_customer_id + "&filter_item_id=" + filter_item_id;

        if (filter_customer_id == "") {
            toastr.warning("Please Select Customer", "Customer Name");
        } else {
            $('#dg').treegrid({
                url: '<?= base_url('engineering/umh/datatables') ?>' + url
            });

            $("#printout").attr('src', '<?= base_url('engineering/umh/print') ?>' + url);
        }
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        var filter_customer_id = $("#filter_customer_id").combogrid('getValue');
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var url = "?filter_customer_id=" + filter_customer_id + "&filter_item_id=" + filter_item_id;

        if (filter_customer_id == "") {
            toastr.warning("Please Select Customer", "Customer Name");
        } else {
            window.location.assign('<?= base_url('engineering/umh/print/excel') ?>' + url);
        }
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {

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
                            
                            //$('#dlg_insert').dialog('close');
                            $('#dg').treegrid('reload');
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
                    window.open('<?= base_url('engineering/umh/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('engineering/umh/upload') ?>',
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
                                url: "<?= base_url('engineering/umh/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('engineering/umh/uploadCreate') ?>",
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
                                                    url: "<?= base_url('engineering/umh/uploadcreateFailed') ?>",
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

        $("#main_process_id").combobox({
            url: '<?= base_url('engineering/main_process/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Main Process",
            onSelect: function(main_process){
                $("#main_process_sub_id").combobox({
                    url: '<?= base_url('engineering/main_process_subs/reads/') ?>' + main_process.id,
                    valueField: 'id',
                    textField: 'name',
                    prompt: "Choose Main Process Sub",
                });
            }
        });

        //GET CUSTOMER
        $('#customer_id').combogrid({
            url: '<?= base_url('master/customers/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Customer",
            columns: [
                [{
                    field: 'number',
                    title: 'Customer No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Customer Name',
                    width: 250
                }, ]
            ],
            onSelect: function(index, row) {
                $('#item_id').combogrid({
                    url: '<?= base_url('master/customer_items/readItems?customer_id=') ?>' + row.id,
                    panelWidth: 420,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Product No",
                    columns: [
                        [{
                            field: 'number',
                            title: 'Product No',
                            width: 100
                        }, {
                            field: 'name',
                            title: 'Product Name',
                            width: 200
                        }, ]
                    ],
                    onSelect: function(index2, row2){
                        $("#circuit").combobox({
                            url: '<?= base_url('engineering/job_orders/reads/') ?>' + row.id + "/" + row2.id,
                            valueField: 'circuit',
                            textField: 'circuit',
                            prompt: "Choose Circuit",
                        });
                    }
                });
            }
        });

        //GET CUSTOMER
        $('#filter_customer_id').combogrid({
            url: '<?= base_url('master/customers/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Customer",
            columns: [
                [{
                    field: 'number',
                    title: 'Customer No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Customer Name',
                    width: 250
                }, ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            onSelect: function(index, row) {
                $('#filter_item_id').combogrid({
                    url: '<?= base_url('master/customer_items/readItems?customer_id=') ?>' + row.id,
                    panelWidth: 420,
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Product No",
                    columns: [
                        [{
                            field: 'number',
                            title: 'Product No',
                            width: 100
                        }, {
                            field: 'name',
                            title: 'Product Name',
                            width: 200
                        }, ]
                    ],
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                });
            }
        });
    });

    function cellStyler(value, row, index) {
        if (value > 0) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }
</script>
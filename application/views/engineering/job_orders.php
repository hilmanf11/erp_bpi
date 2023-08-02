<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <th field="ck" checkbox="true"></th>
        <th data-options="field:'item_number',width:200,halign:'center'">Product No</th>
        <th data-options="field:'item_name',width:150,halign:'center'">Product Name</th>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'circuit',width:80,align:'center'">CCT</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer</th>
            <th rowspan="2" data-options="field:'wire',width:80,align:'center'">Wire Code</th>
            <th rowspan="2" data-options="field:'type',width:80,align:'center'">Type</th>
            <th rowspan="2" data-options="field:'size',width:80,align:'center'">Size</th>
            <th rowspan="2" data-options="field:'color',width:80,align:'center'">Color</th>
            <th rowspan="2" data-options="field:'length',width:80,align:'center'">Length</th>
            <th colspan="7" data-options="field:'',width:100,align:'center'">Terminal Side A</th>
            <th colspan="7" data-options="field:'',width:100,align:'center'">Terminal Side B</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'a_terminal',width:150,align:'center'"> Terminal No</th>
            <th data-options="field:'a_seal',width:80,align:'center'"> Seal</th>
            <th data-options="field:'a_chi',width:80,align:'center'"> CHI</th>
            <th data-options="field:'a_chc',width:80,align:'center'"> CHC</th>
            <th data-options="field:'a_stripping',width:80,align:'center'"> Stripping</th>
            <th data-options="field:'a_process',width:80,align:'center'"> Process</th>
            <th data-options="field:'a_note',width:100,align:'center'"> Note</th>
            <th data-options="field:'b_terminal',width:150,align:'center'"> Terminal No</th>
            <th data-options="field:'b_seal',width:80,align:'center'"> Seal</th>
            <th data-options="field:'b_chi',width:80,align:'center'"> CHI</th>
            <th data-options="field:'b_chc',width:80,align:'center'"> CHC</th>
            <th data-options="field:'b_stripping',width:80,align:'center'"> Stripping</th>
            <th data-options="field:'b_process',width:80,align:'center'"> Process</th>
            <th data-options="field:'b_note',width:100,align:'center'"> Note</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 190px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 35%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_product_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Eff Date</span>
                    <input style="width:30%;" id="trans_date" name="trans_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" id="customer_id" name="customer_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="item_id" name="item_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Circuit No</span>
                    <input style="width:30%;" name="circuit" required="" class="easyui-textbox">
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Wire</span>
                    <input style="width:60%;" id="wire" name="wire" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Type & Size</span>
                    <input style="width:30%;" id="type" name="type" required="" class="easyui-combobox">
                    <input style="width:30%;" id="size" name="size" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Color</span>
                    <input style="width:60%;" id="color" name="color" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Length</span>
                    <input style="width:30%;" name="length" required="" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <fieldset style="width:49%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Terminal Side A</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Terminal No</span>
                <input style="width:60%;" id="a_terminal" name="a_terminal" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Seal</span>
                <input style="width:60%;" name="a_seal" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">CHI</span>
                <input style="width:60%;" name="a_chi" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">CHC</span>
                <input style="width:60%;" name="a_chc" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Stripping</span>
                <input style="width:60%;" name="a_stripping" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Process</span>
                <input style="width:60%;" name="a_process" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Note</span>
                <input style="width:60%;" name="a_note" class="easyui-textbox">
            </div>
        </fieldset>
        <fieldset style="width:50%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Terminal Side B</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Terminal No</span>
                <input style="width:60%;" id="b_terminal" name="b_terminal" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Seal</span>
                <input style="width:60%;" name="b_seal" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">CHI</span>
                <input style="width:60%;" name="b_chi" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">CHC</span>
                <input style="width:60%;" name="b_chc" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Stripping</span>
                <input style="width:60%;" name="b_stripping" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Process</span>
                <input style="width:60%;" name="b_process" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Note</span>
                <input style="width:60%;" name="b_note" class="easyui-textbox">
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
<iframe id="printout" src="<?= base_url('engineering/job_orders/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('engineering/job_orders/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('engineering/job_orders/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }
    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('engineering/job_orders/delete') ?>',
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
                                $('#dg').treegrid('reload');
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
        window.location.assign('<?= base_url('template/tmp_job_orders.xls') ?>');
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_product_no = $("#filter_product_no").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_product_no=" + filter_product_no;
        $('#dg').treegrid({
            url: '<?= base_url('engineering/job_orders/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('engineering/job_orders/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_product_no = $("#filter_product_no").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_product_no=" + filter_product_no;
        window.location.assign('<?= base_url('engineering/job_orders/print/excel') ?>' + url);
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        $('#dg').treegrid({
            url: '<?= base_url('engineering/job_orders/datatables') ?>',
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'item_number',
            singleSelect: false,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
            rowStyler: function(row) {
                if (row.state != "closed") {
                    return 'background-color:#CFE6FF;font-weight:bold;';
                }
            },
        });
        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save & Repeat Data',
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
                    window.open('<?= base_url('engineering/job_orders/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('engineering/job_orders/upload') ?>',
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
                                url: "<?= base_url('engineering/job_orders/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('engineering/job_orders/uploadCreate') ?>",
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
                                                    url: "<?= base_url('engineering/job_orders/uploadcreateFailed') ?>",
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

        $("#customer_id").combobox({
            url: '<?= base_url('master/customers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Customer",
            onSelect: function(row) {
                $('#item_id').combogrid({
                    url: '<?= base_url('master/customer_items/readItems?customer_id=') ?>' + row.id,
                    panelWidth: 700,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: false,
                    prompt: "Select Product No",
                    columns: [
                        [{
                            field: 'number',
                            title: 'Product No',
                            width: 150
                        }, {
                            field: 'name',
                            title: 'Product Name',
                            width: 200
                        }, {
                            field: 'description',
                            title: 'Specification',
                            width: 250
                        }]
                    ]
                });
            }
        });
        
        $("#wire").combobox({
            url: '<?= base_url('master/item_products/reads') ?>',
            valueField: 'name',
            textField: 'name',
            prompt: "Select Wire"
        });
        $("#type").combobox({
            url: '<?= base_url('master/item_types/reads') ?>',
            valueField: 'name',
            textField: 'name',
            prompt: "Select Type"
        });
        $("#size").combobox({
            url: '<?= base_url('master/item_sizes/reads') ?>',
            valueField: 'name',
            textField: 'name',
            prompt: "Select Size"
        });
        $("#color").combobox({
            url: '<?= base_url('master/item_colors/reads') ?>',
            valueField: 'name',
            textField: 'name',
            prompt: "Select Color"
        });
        $('#a_terminal').combogrid({
            url: '<?= base_url('master/items/reads') ?>',
            panelWidth: 600,
            idField: 'number',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Terminal A",
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 100
                }, {
                    field: 'description',
                    title: 'Specification',
                    width: 250
                }, ]
            ]
        });
        $('#b_terminal').combogrid({
            url: '<?= base_url('master/items/reads') ?>',
            panelWidth: 600,
            idField: 'number',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Terminal B",
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 100
                }, {
                    field: 'description',
                    title: 'Specification',
                    width: 250
                }, ]
            ]
        });
        $('#terminal').combogrid({
            url: '<?= base_url('master/items/reads') ?>',
            panelWidth: 600,
            idField: 'number',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Terminal",
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 100
                }, {
                    field: 'description',
                    title: 'Specification',
                    width: 250
                }, ]
            ]
        });
        //Get Product No
        $('#filter_product_no').combogrid({
            url: '<?= base_url('master/items/reads/001') ?>',
            panelWidth: 600,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 100
                }, {
                    field: 'description',
                    title: 'Specification',
                    width: 250
                }, ]
            ]
        });
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

    function numberformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:red;'>UNCONVERTED</b>";
        } else if (value == 1) {
            return "<b style='color:green;'>CONVERTED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#FFC8C8;';
        } else if (value == 1) {
            return 'background-color:#C8FFCC;';
        }
    }
</script>
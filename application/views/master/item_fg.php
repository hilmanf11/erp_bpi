<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Process Type is taken from <b>Master Data > Engineering > Flow Process</b></li>
                <li>The Data Divisions is taken from <b>Master Data > General Master > Divisions</b></li>
                <li>The Data Box is taken from <b>Master Data > Engineering > Boxs</b></li>
                <li>The Data Colors is taken from <b>Master Data > Engineering > Colors</b></li>
                <li>The Data UoM is taken from <b>Master Data > General Master > Unit of Measure</b></li>
            </ul>
        </div>
    </div>
</div>
<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead data-options="frozen:true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'id',width:150,align:'center'">Product ID</th>
            <th data-options="field:'number',width:150,halign:'center'">Product No.</th>
            <th data-options="field:'name',width:150,halign:'center'">Product Name</th>
        </tr>
    </thead>

    <thead>
        <tr>
            <th rowspan="2" data-options="field:'number_customer',width:150,halign:'center'">Product Customer</th>
            <th rowspan="2" data-options="field:'alias',width:150,halign:'center'">Product Alias</th>
            <th rowspan="2" data-options="field:'total_mold',width:50,align:'center'">Total <br>Mold</th>
            <th rowspan="2" data-options="field:'process',width:80,align:'center'">Process <br>Type</th>
            <th rowspan="2" data-options="field:'division_name',width:100,halign:'center'">Division</th>
            <th rowspan="2" data-options="field:'control_id',width:100,halign:'center'">Control</th>
            <th rowspan="2" data-options="field:'boxs',width:200,halign:'center'">Box</th>
            <th rowspan="2" data-options="field:'polybag',width:150,align:'center'">Polybag <br>Label</th>
            <th rowspan="2" data-options="field:'box_label',width:70,align:'center'">Box <br>Label</th>
            <th rowspan="2" data-options="field:'lot',width:100,align:'center'">Lot</th>
            <th rowspan="2" data-options="field:'ng_ration',width:90,align:'center'">NG Ratio (%)</th>
            <th rowspan="2" data-options="field:'is_no',width:100,halign:'center'">IS No.</th>
            <th rowspan="2" data-options="field:'weight',width:100,align:'center'">Weight (gram)</th>
            <th rowspan="2" data-options="field:'color',width:100,halign:'center'">Color</th>
            <th rowspan="2" data-options="field:'leadtime',width:80,align:'center'">Lead Time <br>(Day)</th>
            <th rowspan="2" data-options="field:'mpq',width:50,align:'center'">MPQ</th>
            <th rowspan="2" data-options="field:'moq',width:50,align:'center'">MOQ</th>
            <th rowspan="2" data-options="field:'uom',width:50,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'qty_box',width:80,align:'center'">QTY/Box</th>
            <th rowspan="2" data-options="field:'box_sub',width:80,align:'center'">QTY/Sub Box</th>
            <!-- <th rowspan="2" data-options="field:'safety_stock',width:100,halign:'center'">Safety Stock</th> -->
            <th rowspan="2" data-options="field:'min',width:50,align:'center'">Min</th>
            <th rowspan="2" data-options="field:'max',width:50,align:'center'">Max</th>
            <th rowspan="2" data-options="field:'attachment',width:100,halign:'center',formatter:cellbutton">Attachment</th>
            <th rowspan="2" data-options="field:'type',width:50,align:'center'">Type</th>
            <th rowspan="2" data-options="field:'logo',width:100,align:'center', styler:cellStyler, formatter:cellFormatterLogo">Logo</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
            <th rowspan="2" data-options="field:'approved_to',width:100,halign:'center', styler:styleApproved, formatter:formatApproved">Approved To</th>
            <th rowspan="2" data-options="field:'approved_by',width:100,halign:'center'">Approved by</th>
            <th rowspan="2" data-options="field:'approved_date',width:100,halign:'center'">Approved date</th>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 95%; height: 500px; padding:10px; top: 10px; left: 10px;">
    <form id="frm_insert" method="post" novalidate enctype="multipart/form-data">
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>

            <div style="float:left; width:33%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product ID</span>
                    <input style="width:60%;" name="id" id="id" required="" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No.</span>
                    <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Name</span>
                    <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Customer</span>
                    <input style="width:60%;" name="number_customer" id="number_customer" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Alias</span>
                    <input style="width:60%;" name="alias" id="alias" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Process Type</span>
                    <input style="width:60%;" name="process" id="process" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" name="division_id" id="division_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Control</span>
                    <input style="width:60%;" name="control_id" id="control_id" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Type</span>
                    <select style="width:60%;" name="type" id="type" class="easyui-combobox" panelHeight="auto">
                        <option value="FG">FG</option>
                        <option value="RM">RM</option>
                    </select>
                </div>
            </div>

            <div style="float:left; width:33%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Box</span>
                    <input style="width:60%;" name="boxs" id="boxs" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Lot</span>
                    <input style="width:60%;" name="lot" id="lot" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Polybag</span>
                    <select style="width:60%;" name="polybag" id="polybag" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="Label Manual Logo BPI">Label Manual Logo BPI</option>
                        <option value="Label Manual Logo Askara">Label Manual Logo Askara</option>
                        <option value="Tidak Pakai Label Manual">Tidak Pakai Label Manual</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Box Label</span>
                    <select style="width:60%;" name="box_label" id="box_label" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="YES">YES</option>
                        <option value="NO">NO</option>
                    </select>
                </div>
                <div class="fitem">
                        <span style="width:35%; display:inline-block;">NG Ratio (%)</span>
                        <input style="width:30%;" name="ng_ration" id="ng_ration" precision="5" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">IS No.</span>
                    <input style="width:60%;" name="is_no" id="is_no" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Weight (Gram)</span>
                    <input style="width:30%;" name="weight" id="weight" precision="2" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Color</span>
                    <input style="width:60%;" name="color" id="color" required="" class="easyui-textbox">
                </div>
            </div>
            <div style="float:left; width:33%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Leadtime (Day)</span>
                    <input style="width:60%;" name="leadtime" id="leadtime" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MPQ</span>
                    <input style="width:60%;" name="mpq" id="mpq" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MOQ</span>
                    <input style="width:60%;" name="moq" id="moq" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">UoM</span>
                    <input style="width:60%;" name="uom" id="uom" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty/Box</span>
                    <input style="width:60%;" name="qty_box" id="qty_box" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty/Sub Box</span>
                    <input style="width:60%;" name="box_sub" id="box_sub" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="attachment" id="attachment" class="easyui-filebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Logo</span>
                    <select style="width:60%;" name="status" id="status" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="0">YES</option>
                        <option value="1">NO</option>
                    </select>
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
<iframe id="printout" src="<?= base_url('master/item_fg/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/item_fg/create') ?>';
        $('#frm_insert').form('clear');

        $('#polybag').combobox('setValue', 'Label Manual Logo BPI');
        $('#box_label').combobox('setValue', 'YES');
        $('#status').combobox('setValue', '0');
        $('#type').combobox('setValue', 'FG');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');

        setTimeout(function() { 
            $('#id').textbox('setValue', row.id);
        }, 500);

        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            // $('#id').textbox('disable');

            url_save = '<?= base_url('master/item_fg/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/item_fg/delete') ?>',
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
        window.location.assign('<?= base_url('template/tmp_item_fg.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/item_fg/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/item_fg/datatables') ?>',
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

    $('#process').combobox({
        url: '<?= base_url('master/item_process_flow/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Process Type',
    });

    $('#boxs').combobox({
        url: '<?= base_url('master/item_boxs/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Boxs',
    });

    $('#color').combobox({
        url: '<?= base_url('master/item_colors/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Colors',
    });

    $('#division_id').combobox({
        url: '<?= base_url('master/divisions/reads/'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Division',
        onSelect: function(division) {
            $.ajax({
                type: "post",
                url: "<?= base_url('master/item_fg/autoid/') ?>" + "/" + division.number,
                dataType: "html",
                success: function(response) {
                    $('#id').textbox('setValue', response);
                }
            });
        }
    });

    $('#uom').combobox({
        url: '<?= base_url('master/uom/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Unit of Measure',
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

     //FORMATTER LOGO
     function cellFormatterLogo(value) {
        if (value == 0) {
            return 'YES';
        } else {
            return 'NO';
        }
    };

    //CELLSTYLE APPROVE
    function styleApproved(value, row, index) {
        if (value == "" || value === null ) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER APPROVE
    function formatApproved(value) {
        if (value == "" || value === null ) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    //
    function cellbutton(value) {
        if (value != null) {
            return '<a target="_blank" href="' + value + '" class="btn btn-primary btn-sm" style="pointer-events: auto; opacity:1; width:100%;"><i class="fa fa-eye"></i> View</a>';
            // alert(value);
        }
    };

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/item_fg/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/item_fg/upload') ?>',
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
                            url: "<?= base_url('master/item_fg/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('master/item_fg/uploadCreate') ?>",
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
                                                url: "<?= base_url('master/item_fg/uploadcreateFailed') ?>",
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
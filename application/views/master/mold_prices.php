<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Mold Name is taken from <b>Master Data > Engineering > Master Mold</b></li>
                <li>The Data Product No is taken from <b>Master Data > Engineering > Item Finish Good</b></li>
                <li>The Data Mold Type Product is taken from <b>Master Data > Engineering > Item Finish Good</b></li>
            </ul>
        </div>
        <div title="CONDITIONS" style="padding: 20px;">
            <ul>
                <li><b>Mold Type Product</b> can be select if Mold Type is <b>Double</b></li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'mold_id',width:130,halign:'center'">Mold Id</th>
            <th rowspan="2" data-options="field:'mold_name',width:130,halign:'center'">Mold Name</th>
            <th rowspan="2" data-options="field:'mold_type',width:80,halign:'center'">Mold Type</th>
            <th rowspan="2" data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformat">Price</th>
            <th rowspan="2" data-options="field:'currency',width:80,halign:'center'">Currency</th>
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
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 50%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Mold Name</span>
                    <input style="width:60%;" id="filter_mold_name" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Mold Type</span>
                    <select style="width:60%;" name="filter_mold_type" id="filter_mold_type" prompt="Select All" panelHeight="auto" class="easyui-combobox">
                        <option value="SINGLE">SINGLE</option>
                        <option value="DOUBLE">DOUBLE</option>
                </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
        </fieldset>
        <?= $button ?>
        <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a> -->
    </div>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Mold id</span>
                <input style="width:60%;" name="mold_id" id="mold_id" required="" class="easyui-textbox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Product id</span>
                <input style="width:60%;" name="item_fg_id" id="item_fg_id" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Mold Name</span>
                <input style="width:60%;" name="mold_name" id="mold_name" required="" class="easyui-combogrid">
            </div>
            <!-- auto ambil dari molds -->
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Mold Type</span>
                <input style="width:60%;" name="mold_type" id="mold_type" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Price</span>
                <input style="width:60%;" name="price" id="price" precision="2" class="easyui-numberbox">
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
<iframe id="printout" src="<?= base_url('master/mold_prices/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/mold_prices/create') ?>';
        $('#frm_insert').form('clear');

        $('#status').combobox('setValue', '0');
        $('#mold_name').combogrid('enable');
    }
    
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/mold_prices/update') ?>?id=' + btoa(row.id);

            $('#mold_name').combogrid('disable');
            $("#mold_type").textbox('setValue', row.mold_type);
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
                            url: '<?= base_url('master/mold_prices/delete') ?>',
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
        window.location.assign('<?= base_url('template/tmp_mold_prices.xls') ?>');
    }
    //FILTER DATA
    function filter() {
        var filter_mold_name = $("#filter_mold_name").combobox('getValue');
        var filter_mold_type = $("#filter_mold_type").combobox('getValue');

        var url = "?filter_mold_name=" + window.btoa(filter_mold_name) +
            "&filter_mold_type=" + window.btoa(filter_mold_type);

        $('#dg').datagrid({
            url: '<?= base_url('master/mold_prices/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('master/mold_prices/print') ?>' + url);
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        var filter_mold_name = $("#filter_mold_name").combobox('getValue');
        var filter_mold_type = $("#filter_mold_type").combobox('getValue');

        var url = "?filter_mold_name=" + window.btoa(filter_mold_name) +
            "&filter_mold_type=" + window.btoa(filter_mold_type);


        window.location.assign('<?= base_url('master/mold_prices/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        $('#filter_mold_type').combobox('clear');
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/mold_prices/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
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
    });

    $('#mold_name').combogrid({
        url: '<?= base_url("master/mold_prices/readss") ?>',
        panelWidth: 600,
        idField: 'mold_name',
        textField: 'mold_name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Mold Name",
        columns: [
            [{
                field: 'id',
                title: 'Mold Id',
                width: 200
            }, {
                field: 'mold_name',
                title: 'Mold Name',
                width: 200
            }, {
                field: 'number',
                title: 'Product Number',
                width: 200
            }]
        ],
        onSelect: function(val, molds) {
            $("#mold_id").textbox('setValue', molds.id);
            $("#mold_type").textbox('setValue', molds.mold_type);
            $("#item_fg_id").textbox('setValue', molds.item_fg_id);
        }
    });

    $('#filter_mold_name').combobox({
        url: '<?= base_url('master/molds/reads'); ?>',
        valueField: 'id',
        textField: 'mold_name',
        prompt: 'Choose All',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }]
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

    function numberformat(value, row) {
        // if (value != null) {
        //     // Tentukan format berdasarkan mata uang
        //     const formatter = new Intl.NumberFormat(row.currency === 'USD' ? 'en-US' : 'id-ID', {
        //         minimumFractionDigits: row.currency === 'USD' ? 4 : 2
        //     });
        //     return "<b>" + formatter.format(value) + "</b>";
        // }

        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return formatter.format(value);
        }
    }

    // UPLOAD DATA
    $('#dlg_upload').dialog({
            buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/mold_prices/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/mold_prices/upload') ?>',
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
                            url: "<?= base_url('master/mold_prices/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('master/mold_prices/uploadCreate') ?>",
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
                                                url: "<?= base_url('master/mold_prices/uploadcreateFailed') ?>",
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

    //untuk kebutuhan NPD
    $(document).ready(function() {
        if (localStorage.getItem('trigger_add') === 'yes') {
            localStorage.removeItem('trigger_add');

            $('<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:#ffffff;z-index:8999;"></div>').appendTo('body');

            setTimeout(function() {
                if (typeof add === "function") {
                    add(); 

                    var checkClose = setInterval(function() {
                        var isHidden = $('#dlg_insert').closest('.window').is(':hidden');
                        if (isHidden) {
                            clearInterval(checkClose); // Hentikan monitoring
                            if (window.parent.$('#dlg_outer_wrapper').length) {
                                window.parent.$('#dlg_outer_wrapper').dialog('close');
                            }
                        }
                    }, 500);
                }
            }, 1000); 
        }

        // ==========================================
        // SKRIP TRIGGER UPLOAD
        // ==========================================
        var urlParams = new URLSearchParams(window.location.search);
        var action = urlParams.get('action');

        if (action === 'upload') {
            $('<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:#ffffff;z-index:8999;"></div>').appendTo('body');

            setTimeout(function() {
                if (typeof upload === 'function') {
                    upload(); 
                    var checkCloseUpload = setInterval(function() {
                        var isHidden = $('#dlg_upload').closest('.window').is(':hidden'); 
                        if (isHidden) {
                            clearInterval(checkCloseUpload); 
                            if (window.parent.$('#dlg_upload_wrapper').length) {
                                window.parent.$('#dlg_upload_wrapper').dialog('close');
                            }
                        }
                    }, 500);
                }
            }, 500);
        }
    });
</script>
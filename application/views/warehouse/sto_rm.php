<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'departement',width:150,halign:'center'">Departement</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Component No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Component Name</th>
            <th rowspan="2" data-options="field:'stock',width:100,halign:'center',align:'right',formatter:numberformat">Ending Stock</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty STO</th>
            <th rowspan="2" data-options="field:'balance',width:100,halign:'center',align:'right',formatter:numberformat">Balance</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th rowspan="2" data-options="field:'remark',width:200,halign:'center'">Remark</th>
            <th rowspan="2" data-options="field:'pic',width:150,align:'center'">PIC</th>
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
<div id="toolbar" style="height: 185px;">
    <div style="width: 100%;">
        <fieldset style="width: 35%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cut Off</span>
                <input style="width:40%;" id="filter_date" value="<?= date("Y-m-d") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cuf Off</span>
                <input style="width:60%;" name="trans_date" id="trans_date" class="easyui-datebox" required data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Departement</span>
                <input style="width:60%;" name="departement" id="departement" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Component No</span>
                <input style="width:60%;" name="item_id" id="item_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Ending Stock</span>
                <input style="width:30%;" name="stock" id="stock" readonly class="easyui-numberbox" data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty STO</span>
                <input style="width:30%;" name="qty" id="qty" required="" class="easyui-numberbox" data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Balance</span>
                <input style="width:30%;" name="balance" id="balance" readonly class="easyui-numberbox" data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remark</span>
                <input style="width:60%;" name="remark" id="remark" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PIC</span>
                <input style="width:60%;" name="pic" id="pic" required="" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- UPLOAD DATA -->
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
<iframe id="printout" src="<?= base_url('warehouse/sto_rm/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //FILTER DATA 
    function filter() {
        var filter_date = $("#filter_date").datebox('getValue');
        var filter_item = $("#filter_item").combogrid('getValue');

        url = "?filter_date=" + filter_date + "&filter_item=" + filter_item;

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/sto_rm/datatables') ?>' + url,
            pagination: true,
            rownumbers: true
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/sto_rm/print') ?>' + url);
    }

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('warehouse/sto_rm/create') ?>';
        $('#frm_insert').form('clear');
        $('#trans_date').datebox('setValue', "<?= date("Y-m-d") ?>");
        $('#pic').textbox('setValue', "<?= $this->session->name ?>");
        $('#departement').textbox('setValue', "WAREHOUSE RM");
        $('#item_id').combogrid('enable');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $('#item_id').combogrid('disable');
            url_save = '<?= base_url('warehouse/sto_rm/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('warehouse/sto_rm/delete') ?>',
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
    //UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    //DOWNLOAD TEMPLATE UPLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_sto_rm.xls') ?>');
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        var filter_date = $("#filter_date").datebox('getValue');
        var filter_item = $("#filter_item").combogrid('getValue');

        var url = "?filter_date=" + filter_date + "&filter_item=" + filter_item;
        window.location.assign('<?= base_url('warehouse/sto_rm/print/excel') ?>' + url);
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/sto_rm/datatables') ?>',
            pagination: true,
            rownumbers: true
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

        //UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('warehouse/sto_rm/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('warehouse/sto_rm/upload') ?>',
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
                                url: "<?= base_url('warehouse/sto_rm/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('warehouse/sto_rm/uploadCreate') ?>",
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
                                                    url: "<?= base_url('warehouse/sto_rm/uploadcreateFailed') ?>",
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

        $('#filter_item').combogrid({
            url: '<?= base_url('master/items/readNotFg') ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Component No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Component No',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Component Name',
                    width: 200
                }]
            ],
        });

        $('#item_id').combogrid({
            url: '<?= base_url('master/items/readNotFg') ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Component No",
            columns: [
                [{
                    field: 'number',
                    title: 'Component No',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Component Name',
                    width: 200
                }]
            ],
            onSelect: function(val, row) {
                var trans_date = $("#trans_date").datebox('getValue');
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('warehouse/report_history_transactions/readEndingStock') ?>",
                    data: "item_id=" + row.id + "&trans_date=" + trans_date,
                    dataType: "json",
                    success: function(response) {
                        $("#stock").numberbox('setValue', response[0].end_stock);
                    }
                });
            }
        });

        $("#qty").numberbox({
            onChange: function(qty) {
                var stock = $("#stock").numberbox('getValue');
                $("#balance").numberbox('setValue', (parseInt(stock) - parseInt(qty)));
            }
        });

        //Get Product Family
        // $("#process_id").combobox({
        //     url: '<?= base_url('master/process/reads') ?>',
        //     valueField: 'id',
        //     textField: 'name',
        //     prompt: "Choose Process"
        // });
    });

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

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
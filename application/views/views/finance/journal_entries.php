<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'periode',width:80,halign:'center'">Period</th>
            <th rowspan="2" data-options="field:'trans_date',width:80,halign:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'gl_no',width:100,halign:'center'">GL No</th>
            <th rowspan="2" data-options="field:'asset_no',width:150,halign:'center'">Asset No</th>
            <th rowspan="2" data-options="field:'asset_name',width:200,halign:'center'">Asset Name</th>
            <th rowspan="2" data-options="field:'asset_category_name',width:150,halign:'center'">Asset Category</th>
            <th rowspan="2" data-options="field:'purchase_invoice_number',width:150,halign:'center'">Purchase Invoice No</th>
            <th rowspan="2" data-options="field:'purchase_date',width:120,align:'center'">Purchase Date</th>
            <th rowspan="2" data-options="field:'cost',width:100,halign:'center',align:'right', formatter:priceformat">Asset Cost</th>
            <th rowspan="2" data-options="field:'estimate_year',width:100,align:'center'">Est. Economic<br>(year)</th>
            <th rowspan="2" data-options="field:'estimate_month',width:100,align:'center'">Est. Economic<br>(month)</th>
            <th rowspan="2" data-options="field:'expired_date',width:120,align:'center'">Expired Date</th>
            <th rowspan="2" data-options="field:'account_number',width:100,halign:'center'">Account No</th>
            <th rowspan="2" data-options="field:'account_name',width:150,halign:'center'">Account Name</th>
            <th rowspan="2" data-options="field:'debit',width:100,halign:'center',align:'right', formatter:priceformat">Debit</th>
            <th rowspan="2" data-options="field:'credit',width:100,halign:'center',align:'right', formatter:priceformat">Credit</th>
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

<!-- FORM FILTER DATAGRID -->
<div id="toolbar" style="height: 260px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Periode</span>
                <input style="width:30%;" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                <input style="width:30%;" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Year'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Asset Category</span>
                <input style="width:60%;" id="filter_category" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Asset Name</span>
                <input style="width:60%;" id="filter_asset_no" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Process Data</b></legend>
            <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
            <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <div id="p_remarks" class="easyui-panel" style="width:100%; height:100px; padding:10px; margin-top: 10px; overflow: auto;">
                <ul id="remarks">

                </ul>
            </div>
        </fieldset>
        <!-- <fieldset style="width: 43%; height: 210px; overflow: auto; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Journal Lists</b></legend>
            <a href="javascript:;" style="margin-bottom: 10px;" class="easyui-linkbutton" onclick="calculate()"><i class="fa fa-refresh"></i> Calculate Journal</a>
            <table id="dg3" class="easyui-datagrid" style="width: 99%;">
                <thead>
                    <tr>
                        <th field="ck" checkbox="true"></th>
                        <th data-options="field:'account_number',halign:'center',width:90">Account No</th>
                        <th data-options="field:'account_name',halign:'center',width:190">Account Name</th>
                        <th data-options="field:'debit',width:100,halign:'center',align:'right',formatter:priceformat">Debit</th>
                        <th data-options="field:'credit',width:100,halign:'center',align:'right',formatter:priceformat">Credit</th>
                    </tr>
                </thead>
            </table>
            <a href="javascript:;" style="margin-top: 10px; width:100%;" class="easyui-linkbutton c6" onclick="saveJournal()"><i class="fa fa-check"></i> Save Data Journal</a>
        </fieldset> -->
    </div>
    <?= $button ?>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('finance/journal_entries/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_asset_no = $("#filter_asset_no").combogrid('getValue');

        // $.ajax({
        //     type: "post",
        //     url: "<?= base_url('closing/locks/checkLock') ?>",
        //     data: "period=" + filter_year + "-" + filter_month + "-01" + "&menus_id=<?= $menus_id ?>",
        //     dataType: "json",
        //     success: function (lock) {
        //         if(lock.total > 0){
        //             toastr.error("This period is not active by Accounting");
        //             return false;
        //         }

                $.ajax({
                    type: "post",
                    url: "<?= base_url('finance/journal_entries/getData') ?>",
                    data: "month=" + filter_month + "&year=" + filter_year + "&category=" + filter_category + "&number=" + filter_asset_no,
                    dataType: "json",
                    success: function(get) {
                        if (get.total > 0) {
                            requestData(get.total, get.rows);
                            $.ajax({
                                type: "post",
                                url: "<?= base_url('finance/journal_entries/uploadclearFailed') ?>",
                            });

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    var i = (number - 1);

                                    $.ajax({
                                        type: "post",
                                        url: '<?= base_url('finance/journal_entries/create') ?>',
                                        data: {
                                            asset_category_number: json[i].asset_category_number,
                                            asset_no: json[i].number,
                                            asset_name: json[i].name,
                                            depreciation: json[i].depreciation,
                                            trans_date: json[i].trans_date,
                                            periode: filter_year + "-" + filter_month,
                                        },
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
                                                    url: "<?= base_url('finance/journal_entries/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });

                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            if (value == 100) {
                                                Swal.fire('Good job!', 'Process Add Journal Entries Completed!', 'success');
                                                $("#dg").datagrid('reload');
                                            }

                                            $("#p_remarks").append(title + "<br>");
                                        }
                                    });
                                }
                            }
                        } else {
                            toastr.warning("Fixed Asset Data Not Found");
                        }
                    }
                });
        //     }
        // });
    }

    function calculate() {
        var filter_category = $("#filter_category").combobox('getValue');

        var rows = $('#dg').datagrid('getSelections');
        var totalrows = rows.length;

        if (filter_category != "") {
            if (totalrows > 0) {
                var total = 0;
                for (let i = 0; i < totalrows; i++) {
                    if (filter_category == rows[i].asset_category_number) {
                        total += rows[i].debit;
                    }
                }

                $('#dg3').datagrid({
                    url: '<?= base_url('finance/journal_entries/calculate/') ?>' + window.btoa(filter_category) + "/" + total,
                    rownumbers: true,
                });
            } else {
                toastr.info("Please Select All Data in the Table first");
            }
        } else {
            toastr.info("Please Select Category First");
        }
    }

    function saveJournal() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');

        var rows = $('#dg3').datagrid('getSelections');
        var totalrows = rows.length;

        if (totalrows > 0) {
            for (let i = 0; i < totalrows; i++) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('finance/journal_entries/saveJournal') ?>",
                    data: {
                        periode: filter_year + "-" + filter_month,
                        asset_category_number: rows[i].asset_category_number,
                        account_number: rows[i].account_number,
                        account_name: rows[i].account_name,
                        debit: rows[i].debit,
                        credit: rows[i].credit,
                        flag: rows[i].flag,
                    },
                    dataType: "json",
                    success: function(response) {
                        toastr.success(response.message);
                    }
                });
            }
        } else {
            toastr.info("Please Select All Data in the Table Journal List first");
        }
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    requestDataDelete(rows.length, rows);
                    function requestDataDelete(total, json, number = 1, value = 0, success = 1, failed = 1) {
                        if (value < 100) {
                            value = Math.floor((number / total) * 100);
                            $('#p_upload').progressbar('setValue', value);
                            $('#p_start').html(number);
                            $('#p_finish').html(total);
                            var i = (number - 1);

                            $.ajax({
                                type: "post",
                                url: "<?= base_url('closing/locks/checkLock') ?>",
                                data: "period=" + json[i].periode + "-01" + "&menus_id=<?= $menus_id ?>",
                                dataType: "json",
                                success: function (lock) {
                                    if(lock.total > 0){
                                        toastr.error("This period is not active by Accounting");
                                        return false;
                                    }

                                    $.ajax({
                                        method: 'post',
                                        url: '<?= base_url('finance/journal_entries/delete') ?>',
                                        data: {
                                            id: json[i].id
                                        },
                                        success: function(result) {
                                            var result = eval('(' + result + ')');

                                            if (result.theme == "success") {
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                requestDataDelete(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                requestDataDelete(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            if (value == 100) {
                                                Swal.fire('Good job!', 'Process Delete Journal Entries Completed!', 'success');
                                                $("#dg").datagrid('reload');
                                            }

                                            $("#p_remarks").append(title + "<br>");
                                        },
                                    });
                                }
                            });
                        }
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //FILTER DATA
    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_asset_no = $("#filter_asset_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_asset_no=" + window.btoa(filter_asset_no);

        $('#dg').datagrid({
            url: '<?= base_url('finance/journal_entries/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/journal_entries/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //EXPORT TO EXCEL
    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_asset_no = $("#filter_asset_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_asset_no=" + window.btoa(filter_asset_no);

        window.location.assign('<?= base_url('finance/journal_entries/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function downloadFailed() {
        window.open('<?= base_url('finance/journal_entries/uploadDownloadFailed') ?>', '_blank');
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

    $(function() {
        $("#add").html("Add Journal Entry");

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            rownumbers: true,
            pagination: true,
            fit: true,
            rowStyler: function(index, row) {
                if (row.asset_no == null) {
                    return 'background-color:#FFC9C9;';
                }
            }
        });

        $('#filter_month').combobox({
            url: '<?php echo base_url('finance/journal_entries/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Month',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('finance/journal_entries/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Year',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
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
                var month = $("#filter_month").combobox('getValue');
                var year = $("#filter_year").combobox('getValue');

                $("#filter_asset_no").combogrid({
                    url: '<?= base_url('finance/journal_entries/readAssetNo/') ?>' + window.btoa(category.number) + "/" + month + "/" + year,
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
    });

    function priceformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat("id-ID", {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }
</script>
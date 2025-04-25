<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'period',width:80,halign:'center'">Period</th>
            <th rowspan="2" data-options="field:'number',width:80,halign:'center'">Number</th>
            <th rowspan="2" data-options="field:'document_no',width:150,halign:'center'">Document No</th>
            <!-- <th rowspan="2" data-options="field:'company_name',width:150,align:'center'">Company Name</th> -->
            <th rowspan="2" data-options="field:'gl_no',width:100,halign:'center'">GL No</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center', align:'right',formatter:priceformat">Amount</th>
            <th rowspan="2" data-options="field:'rate',width:100,halign:'center', align:'right',formatter:priceformat">Rate</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'revaluation',width:150,halign:'center', align:'right',formatter:priceformat">Amount</th>
            <th rowspan="2" data-options="field:'journal_type_name',width:200,halign:'center'">Journal Name</th>
            <th rowspan="2" data-options="field:'account_number',width:100,halign:'center'">Account No</th>
            <th rowspan="2" data-options="field:'account_name',width:200,halign:'center'">Account Name</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Revaluation</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'debit',width:100,halign:'center',align:'right',formatter:priceformat">Debit</th>
            <th data-options="field:'credit',width:100,halign:'center',align:'right',formatter:priceformat">Credit</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- FORM FILTER DATAGRID -->
<div id="toolbar" style="height: 265px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Periode</span>
                <input style="width:30%;" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                <input style="width:30%;" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Year'">
            </div>
            <!-- <div class="fitem">
                <span style="width:35%; display:inline-block;">Journal Type</span>
                <input style="width:60%;" id="filter_journal_type" class="easyui-combogrid">
            </div> -->
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Modul</span>
                <select style="width:60%;" id="filter_modul" class="easyui-combobox" panelHeight="auto">
                    <option value="PURCHASE INVOICING">PURCHASE INVOICING</option>
                    <option value="SALES INVOICING">SALES INVOICING</option>
                    <option value="CASH BANK">CASH BANK</option>
                </select>
            </div>
            <div class="fitem" id="f_account">
                <span style="width:35%; display:inline-block;">Account No</span>
                <input style="width:60%;" id="filter_account" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Process Generate Data</b></legend>
            <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
            <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <div id="p_remarks" class="easyui-panel" style="width:100%; height:100px; padding:10px; margin-top: 10px; overflow: auto;">
                <ul id="remarks">

                </ul>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<iframe id="printout" src="<?= base_url('finance/foreign_currencies/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_modul = $("#filter_modul").combobox('getValue');
        var filter_account = $("#filter_account").combogrid('getValue');
        var period = filter_year + "-" + filter_month + "-01";

        $.ajax({
            type: "post",
            url: "<?= base_url('closing/locks/checkLock') ?>",
            data: "period=" + period + "&menus_id=<?= $menus_id ?>",
            dataType: "json",
            success: function (lock) {
                if(lock.total > 0){
                    toastr.error("This period is not active by Accounting");
                    return false;
                }

                $.messager.confirm('Warning', 'Are you sure you want to Generate this data?', function(r) {
                    if (r) {
                        $.ajax({
                            type: "post",
                            url: "<?= base_url('finance/foreign_currencies/getData') ?>",
                            data: "month=" + filter_month + "&year=" + filter_year + "&modul=" + filter_modul + "&filter_account=" + filter_account,
                            dataType: "json",
                            success: function(get) {
                                if (get.total > 0) {
                                    requestData(get.total, get.rows);

                                    $.ajax({
                                        type: "post",
                                        url: "<?= base_url('finance/foreign_currencies/uploadclearFailed') ?>",
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
                                                url: '<?= base_url('finance/foreign_currencies/create') ?>',
                                                data: json[i],
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
                                                            url: "<?= base_url('finance/foreign_currencies/uploadcreateFailed') ?>",
                                                            data: {
                                                                data: json[number - 1],
                                                                message: result.message
                                                            },
                                                            cache: false
                                                        });

                                                        requestData(total, json, number + 1, value, success + 0, failed + 1);
                                                    }

                                                    if (value == 100) {
                                                        if(filter_month == "10"){
                                                            doubleAdd(filter_month, filter_year, filter_modul, filter_account);
                                                        }else{
                                                            Swal.fire('Good job!', 'Process Add Journal Entries Completed!', 'success');
                                                            $("#dg").datagrid('reload');
                                                        }
                                                    }

                                                    $("#p_remarks").append(title + "<br>");
                                                }
                                            });
                                        }
                                    }
                                } else {
                                    toastr.warning("Data Not Found");
                                }
                            }
                        });
                    }
                });
            }
        });
    }

    function doubleAdd(filter_month, filter_year, filter_modul, filter_account){
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/foreign_currencies/getData') ?>",
            data: "month=" + filter_month + "&year=" + filter_year + "&modul=" + filter_modul + "&filter_account=" + filter_account + "&double=YES",
            dataType: "json",
            success: function(get) {
                if (get.total > 0) {
                    requestData(get.total, get.rows);

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('finance/foreign_currencies/uploadclearFailed') ?>",
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
                                url: '<?= base_url('finance/foreign_currencies/create') ?>',
                                data: json[i],
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
                                            url: "<?= base_url('finance/foreign_currencies/uploadcreateFailed') ?>",
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
                    toastr.warning("Data Not Found");
                }
            }
        });
    }

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
                        //     data: "period=" + row.period + "-01" + "&menus_id=<?= $menus_id ?>",
                        //     dataType: "json",
                        //     success: function (lock) {
                        //         if(lock.total > 0){
                        //             Swal.close();
                        //             toastr.error("This period is not active by Accounting");
                        //             return false;
                        //         }

                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('finance/foreign_currencies/delete') ?>',
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
                                    },
                                });
                        //     }
                        // });
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
        var filter_modul = $("#filter_modul").combobox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_modul=" + window.btoa(filter_modul);

        $('#dg').datagrid({
            url: '<?= base_url('finance/foreign_currencies/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/foreign_currencies/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //EXPORT TO EXCEL
    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_modul = $("#filter_modul").combobox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_modul=" + window.btoa(filter_modul);

        window.location.assign('<?= base_url('finance/foreign_currencies/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function downloadFailed() {
        window.open('<?= base_url('finance/foreign_currencies/uploadDownloadFailed') ?>', '_blank');
    }

    $(function() {
        $("#add").html("Generate Data");

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            rownumbers: true,
            fit: true
        });

        $('#filter_month').combobox({
            url: '<?php echo base_url('finance/foreign_currencies/readMonths'); ?>',
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
            url: '<?php echo base_url('finance/foreign_currencies/readYears'); ?>',
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

        $("#f_account").hide();
        $("#filter_modul").combobox({
            onChange: function(val){
                if(val == "CASH BANK"){
                    $("#f_account").show();
                    $('#filter_account').combogrid({
                        url: '<?= base_url('finance/account_coa/readBanks') ?>',
                        panelWidth: 320,
                        idField: 'account_number',
                        textField: 'account_number',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: 'Choose Account No',
                        columns: [
                            [{
                                field: 'account_number',
                                title: 'Account No',
                                width: 100
                            }, {
                                field: 'account_name',
                                title: 'Account Name',
                                width: 200
                            }, ]
                        ],
                    });
                }else{
                    $('#filter_account').combogrid('clear');
                    $("#f_account").hide();
                }
            }
        });
    });

    function priceformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat("id-ID", {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }
</script>
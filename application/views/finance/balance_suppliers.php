<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Supplier Name is taken from <b>Master Data > Marketing > Suppliers</b></li>
                <li>The Data Account No is taken from <b>Master Data > Accounting & Finance > Chart of Account</b></li>
                <li>The Data Original Currency is taken from <b>Master Data > General Master > Currency</b></li>
                <li>The Data Local Currency is taken from <b>Master Data > General Master > Currency</b></li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">

    <thead>

        <tr>

            <th rowspan="2" field="ck" checkbox="true"></th>

            <th rowspan="2" data-options="field:'supplier_number',width:80,halign:'center'">Code</th>

            <th rowspan="2" data-options="field:'supplier_name',width:300,halign:'center'">Supplier Name</th>

            <th rowspan="2" data-options="field:'account_number',width:150,halign:'center', 

                editor: {

                    type: 'combobox',

                    options: {

                        url: '<?= base_url('finance/account_coa/reads') ?>',

                        valueField: 'account_number',

                        textField: 'account_name',

                        prompt: 'Choose Account No',

                        required: true

                    }

                }">Account No</th>

            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Original Currency</th>

            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Local Currency</th>

            <th rowspan="2" data-options="field:'start_date',width:120,align:'center',

                editor: {

                    type: 'datebox',

                    options: {

                        formatter: myformatter,

                        parser: myparser,

                        editable: false,

                        required: true

                    }

                }">Start Date</th>

            <th rowspan="2" data-options="field:'debt_limit',width:150,halign:'center',align:'right',formatter: priceformat,

                editor: {

                    type: 'numberbox',

                    options: {

                        required: true,

                        precision: 4

                    }

                }">Debt Limit</th>

            <th rowspan="2" data-options="field:'status',width:100,align:'center',formatter: statusformat, styler:statusStyle">Status</th>

            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>

            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>

        </tr>

        <tr>

            <th data-options="field:'currency',width:80,halign:'center',align:'right', 

                editor: {

                    type: 'combobox',

                    options: {

                        url: '<?= base_url('master/currencies/reads') ?>',

                        valueField: 'name',

                        textField: 'name',

                        prompt: 'Choose Currency',

                        required: true

                    }

                }">Currency</th>

            <th data-options="field:'balance',width:150,halign:'center',align:'right',formatter: priceformat, 

                editor: {

                    type: 'numberbox',

                    options: {

                        required: true,

                        precision: 4

                    }

                }">Balance</th>

            <th data-options="field:'currency_local',width:80,halign:'center', 

                editor: {

                    type: 'combobox',

                    options: {

                        url: '<?= base_url('master/currencies/reads') ?>',

                        valueField: 'name',

                        textField: 'name',

                        prompt: 'Choose Currency',

                        required: true

                    }

                }">Currency</th>

            <th data-options="field:'balance_local',width:150,halign:'center',align:'right',formatter: priceformat, 

                editor: {

                    type: 'numberbox',

                    options: {

                        required: true,

                        precision: 2

                    }

                }">Balance</th>

            <th data-options="field:'created_by',width:100,align:'center'"> By</th>

            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>

            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>

            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>

        </tr>

    </thead>

</table>



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



<!-- TOOLBAR DATAGRID -->

<div id="toolbar" style="height: 35px;">

    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
</div>



<!-- PDF -->

<iframe id="printout" src="<?= base_url('finance/balance_suppliers/print') ?>" style="width: 100%;" hidden></iframe>

<script>

    var editIndex = undefined;

    function endEditing(){

        if (editIndex == undefined){return true}

        if ($('#dg').datagrid('validateRow', editIndex)){

            $('#dg').datagrid('endEdit', editIndex);

            editIndex = undefined;

            return true;

        } else {

            return false;

        }

    }

    

    //EDIT DATA

    function update() {

        var row = $('#dg').datagrid('getSelected');

        var rowIndex = $("#dg").datagrid("getRowIndex", row);



        if (row) {

            $('#dg').datagrid('beginEdit', rowIndex);

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

                            url: '<?= base_url('finance/balance_suppliers/delete') ?>',

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



    //Upload Data

    function upload() {

        $('#dlg_upload').dialog('open');

    }



    function download_excel() {

        window.location.assign('<?= base_url('template/tmp_account_balance_suppliers.xls') ?>');

    }



    //PRINT PDF

    function pdf() {

        $("#printout").get(0).contentWindow.print();

    }

    //PRINT EXCEL

    function excel() {

        window.location.assign('<?= base_url('finance/balance_suppliers/print/excel') ?>');

    }

    //RELOAD

    function reload() {

        window.location.reload();

    }

    

    $(function() {

        //SETTING DATAGRID EASYUI

        $('#dg').datagrid({

            url: '<?= base_url('finance/balance_suppliers/datatables') ?>',

            pagination: true,

            singleSelect: true,

            clientPaging: false,

            remoteFilter: true,

            rownumbers: true,

            fit: true,

            pageList: [20, 50, 100, 500, 1000],

            pageSize: 20,

            onClickCell: function(index, field){

                if (editIndex != index){

                    if (endEditing()){

                        var rows = $('#dg').datagrid('getSelected');

                        if(rows){

                            $.ajax({

                                type: "post",

                                url: "<?= base_url('finance/balance_suppliers/create') ?>",

                                data: rows,

                                success: function (response) {

                                    var result = eval('(' + response + ')');

                                    if (result.theme == "success") {

                                        toastr.success(result.message, result.title);

                                    } else {

                                        toastr.error(result.message, result.title);

                                    }



                                    $('#dg').datagrid('reload');

                                }

                            });

                        }

                        editIndex = index;

                    } else {

                        setTimeout(function(){

                            $('#dg').datagrid('selectRow', editIndex);

                        },0);

                    }

                }

            }

        }).datagrid('enableFilter');



        //Upload Data

        $('#dlg_upload').dialog({

            buttons: [{

                text: 'List Failed',

                handler: function() {

                    window.open('<?= base_url('finance/balance_suppliers/uploadDownloadFailed') ?>', '_blank');

                }

            }, {

                text: 'Upload',

                iconCls: 'icon-ok',

                handler: function() {

                    $('#frm_upload').form('submit', {

                        url: '<?= base_url('finance/balance_suppliers/upload') ?>',

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

                                url: "<?= base_url('finance/balance_suppliers/uploadclearFailed') ?>"

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

                                        url: "<?= base_url('finance/balance_suppliers/uploadCreate') ?>",

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

                                                    url: "<?= base_url('finance/balance_suppliers/uploadcreateFailed') ?>",

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



        $("#currency").combobox({

            url: '<?= base_url('master/currencies/reads') ?>',

            valueField: 'number',

            textField: 'number',

            prompt: "Choose Currencies"

        });

    });



    function priceformat(value, row) {

        if (row.currency == "USD") {

            var digits = 4;

            var currency = 'USD';

            var format = "id-ID";

        } else if (row.currency == "JPY") {

            var digits = 2;

            var currency = 'JPY';

            var format = "ja-JP";

        } else if (row.currency == "EUR") {

            var digits = 2;

            var currency = 'EUR';

            var format = "de-DE";

        } else {

            var digits = 0;

            var currency = 'IDR';

            var format = "id-ID";

        }

        if (value != null) {

            const formatter = new Intl.NumberFormat(format, {

                minimumFractionDigits: digits

            });

            return "<b>" + formatter.format(value) + "</b>";

        }

    }



    function statusformat(value, row) {

        if (value == 1) {

            return "<b style='color:green;'>YES</b>";

        } else {

            return "<b style='color:red;'>NO</b>";

        }

    }



    function statusStyle(value, row, index) {

        if (value == 1) {

            return 'background-color:#C8FFCC;';

        } else {

            return 'background-color:#FFC8C8;';

        }

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
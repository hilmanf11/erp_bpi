<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Category is taken from <b>Master Data > Accounting & Finance > Account Group Details</b></li>
                <li>The Data Account No is taken from <b>Master Data > Accounting & Finance > Chart of Account</b></li>
                <li>The Data Original Currency is taken from <b>Master Data > General Master > Currency</b></li>
                <li>The Data Local Currency is taken from <b>Master Data > General Master > Currency</b></li>
            </ul>
        </div>
        <div title="CONDITIONS" style="padding: 20px;">
            <ul>
                <li><b>Account No :</b> Data from Account No refer to Category.</li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->

<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'bank_name',width:200,halign:'center'">Bank Name</th>
            <th rowspan="2" data-options="field:'bank_account',width:150,halign:'center'">Bank Account</th>
            <th rowspan="2" data-options="field:'bank_code',width:120,halign:'center'">Bank Code</th>
            <th rowspan="2" data-options="field:'account_number',width:120,halign:'center'">Account Code</th>
            <th rowspan="2" data-options="field:'account_name',width:200,halign:'center'">Account Name</th>
            <th rowspan="2" data-options="field:'start_date',width:120,align:'center'">Start Date</th>
            <th colspan="2" data-options="field:'',width:120,halign:'center'">Original Currency</th>
            <th colspan="2" data-options="field:'',width:120,halign:'center'">Local Currency</th>
            <th colspan="2" data-options="field:'',width:120,halign:'center'">Transaction</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th data-options="field:'balance',width:150,halign:'center',align:'right',formatter: priceformat">Balance</th>
            <th data-options="field:'currency_local',width:80,align:'center'">Currency</th>
            <th data-options="field:'balance_local',width:150,halign:'center',align:'right',formatter: priceformatLocal">Balance</th>
            <th data-options="field:'p_supplier',width:150,align:'center',formatter:statusformat,styler:statusStyle"> Payment Supplier</th>
            <th data-options="field:'p_customer',width:150,align:'center',formatter:statusformat,styler:statusStyle"> Customer Receipt</th>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 700px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left; margin-bottom: 20px;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" name="account_group_detail_id" id="account_group_detail_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Account No</span>
                    <input style="width:60%;" name="account_number" id="account_number" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Start Date</span>
                    <input style="width:60%;" name="start_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">

                </div>
            </div>
            <div style="width: 50%; float: left; margin-bottom: 20px;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Bank Name</span>
                    <input style="width:60%;" name="bank_name" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Bank Account</span>
                    <input style="width:60%;" name="bank_account" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Bank Code</span>
                    <input style="width:60%;" name="bank_code" required="" class="easyui-textbox">
                </div>
            </div>
            <div style="width: 49%; float: left; margin-right:5px;">
                <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b>Original Currency</b></legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Currency</span>
                        <input style="width:60%;" name="currency" id="currency" required="" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Balance</span>
                        <input style="width:60%;" name="balance" required="" class="easyui-numberbox">
                    </div>
                </fieldset>
            </div>
            <div style="width: 50%; float: left;">
                <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b>Local Currency</b></legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Currency</span>
                        <input style="width:60%;" name="currency_local" id="currency_local" required="" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Balance</span>
                        <input style="width:60%;" name="balance_local" id="balance_local" required="" class="easyui-numberbox">
                    </div>
                </fieldset>
            </div>
            <div style="width: 100%; float: left;">
                <div class="fitem" style="margin-bottom: 10px; margin-top:5px;">
                    <span style="width:25%; display:inline-block;">Payment Supplier</span>
                    <input class="easyui-checkbox" name="p_supplier" value="1">
                </div>
                <div class="fitem">
                    <span style="width:25%; display:inline-block;">Customer Receipt</span>
                    <input class="easyui-checkbox" name="p_customer" value="1">
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
<iframe id="printout" src="<?= base_url('finance/account_banks/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('finance/account_banks/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('finance/account_banks/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('finance/account_banks/delete') ?>',
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
        window.location.assign('<?= base_url('template/tmp_account_banks.xls') ?>');
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('finance/account_banks/print/excel') ?>');
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }


    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('finance/account_banks/datatables') ?>',
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



        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('finance/account_banks/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('finance/account_banks/upload') ?>',
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

                                url: "<?= base_url('finance/account_banks/uploadclearFailed') ?>"

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

                                        url: "<?= base_url('finance/account_banks/uploadCreate') ?>",

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

                                                    url: "<?= base_url('finance/account_banks/uploadcreateFailed') ?>",

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

            valueField: 'name',

            textField: 'name',

            prompt: "Choose Currencies",

            panelHeight: 'auto'

        });



        $("#currency_local").combobox({

            url: '<?= base_url('master/currencies/reads') ?>',

            valueField: 'name',

            textField: 'name',

            prompt: "Choose Currencies",

            panelHeight: 'auto'

        });



        $('#account_group_detail_id').combobox({

            url: '<?= base_url('finance/account_group_details/reads') ?>',

            valueField: 'number',

            textField: 'name',

            prompt: "Choose Category",

            onSelect: function(category){

                $('#account_number').combobox({

                    url: '<?= base_url('finance/account_coa/reads/') ?>' + category.id,

                    valueField: 'account_number',

                    textField: 'account_name',

                    prompt: "Choose Account No"

                });

            }

        });

    });



    function priceformat(value, row) {

        if (row.currency == "USD") {

            var digits = 2;

            var currency = 'USD';

            var format = "en-IN";

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

                style: 'currency',

                currency: currency,

                minimumFractionDigits: digits

            });

            return "<b>" + formatter.format(value) + "</b>";

        }

    }



    function priceformatLocal(value, row) {

        var digits = 0;

        var currency = 'IDR';

        var format = "id-ID";



        if (value != null) {

            const formatter = new Intl.NumberFormat(format, {

                style: 'currency',

                currency: currency,

                minimumFractionDigits: digits

            });

            return "<b>" + formatter.format(value) + "</b>";

        }

    }



    function statusformat(value, row) {

        if (value == 1) {

            return "<b style='color:green;'>ACTIVE</b>";

        } else {

            return "<b style='color:red;'>NOT ACTIVE</b>";

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
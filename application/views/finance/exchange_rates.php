<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Currency From is taken from <b>Master Data > General Master > Currency</b></li>
                <li>The Data Currency To is taken from <b>Master Data > General Master > Currency</b></li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'start_date',width:120,halign:'center'">Start Date</th>
            <th rowspan="2" data-options="field:'end_date',width:120,halign:'center'">Ending Date</th>
            <th rowspan="2" data-options="field:'currency_from',width:100,align:'center'">Currency From</th>
            <th rowspan="2" data-options="field:'currency_to',width:100,align:'center'">Currency To</th>
            <th rowspan="2" data-options="field:'selling',width:120,halign:'center',align:'right',formatter: priceformat">Selling</th>
            <th rowspan="2" data-options="field:'buying',width:120,halign:'center',align:'right',formatter: priceformat">Buying</th>
            <th rowspan="2" data-options="field:'middle',width:120,halign:'center',align:'right',formatter: priceformat">Middle</th>
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
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#viewBi').dialog('open');"><i class="fa fa-info"></i> Kurs</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Start Date</span>
                <input style="width:60%;" name="start_date" class="easyui-datebox" required="" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Ending Date</span>
                <input style="width:60%;" name="end_date" class="easyui-datebox" required="" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Currency From</span>
                <input style="width:60%;" name="currency_from" id="currency_from" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Currency To</span>
                <input style="width:60%;" name="currency_to" id="currency_to" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Selling</span>
                <input style="width:60%;" name="selling" id="selling" required="" class="easyui-numberbox" data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Buying</span>
                <input style="width:60%;" name="buying" id="buying" required="" class="easyui-numberbox" data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Middle</span>
                <input style="width:60%;" name="middle" id="middle" required class="easyui-numberbox" data-options="precision:2">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('finance/exchange_rates/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('finance/exchange_rates/create') ?>';
        $('#frm_insert').form('clear');
    }

    function viewBi() {
        window.open('https://www.bi.go.id/id/statistik/informasi-kurs/transaksi-bi/default.aspx', '_blank');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#middle").numberbox('setValue', ((parseFloat(row.buying) + parseFloat(row.selling)) / 2));
            url_save = '<?= base_url('finance/exchange_rates/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('finance/exchange_rates/delete') ?>',
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

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('finance/exchange_rates/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('finance/exchange_rates/datatables') ?>',
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

        $("#currency_from").combobox({
            url: '<?= base_url('master/currencies/reads') ?>',
            valueField: 'name',
            textField: 'name',
            prompt: "Choose Currencies",
            panelHeight: 'auto'
        });

        $("#currency_to").combobox({
            url: '<?= base_url('master/currencies/reads') ?>',
            valueField: 'name',
            textField: 'name',
            prompt: "Choose Currencies",
            panelHeight: 'auto'
        });

        $("#selling").numberbox({
            onChange: function(selling) {
                var buying = $("#buying").numberbox('getValue');
                $("#middle").numberbox('setValue', ((parseFloat(buying) + parseFloat(selling)) / 2));
            }
        });

        $("#buying").numberbox({
            onChange: function(buying) {
                var selling = $("#selling").numberbox('getValue');
                $("#middle").numberbox('setValue', ((parseFloat(buying) + parseFloat(selling)) / 2));
            }
        });
    });

    function priceformat(value, row) {
        if (row.currency_to == "USD") {
            var digits = 4;
            var currency = 'USD';
            var format = "en-IN";
        } else if (row.currency_to == "JPY") {
            var digits = 2;
            var currency = 'JPY';
            var format = "ja-JP";
        } else if (row.currency_to == "EUR") {
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
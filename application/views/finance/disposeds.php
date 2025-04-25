<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'disposal_date',width:100,align:'center'">Disposal Date</th>
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Asset Code</th>
            <th rowspan="2" data-options="field:'name',width:250,halign:'center'">Asset Name</th>
            <th rowspan="2" data-options="field:'asset_category_name',width:150,halign:'center'">Asset Category</th>
            <th rowspan="2" data-options="field:'cost',width:100,halign:'center',align:'right', formatter:priceformat">Asset<br>Cost</th>
            <th rowspan="2" data-options="field:'residual',width:100,halign:'center',align:'right', formatter:priceformat">Residual<br>Value</th>
            <th rowspan="2" data-options="field:'disposal',width:100,halign:'center',align:'right', formatter:priceformat">Disposal<br>Value</th>
            <th rowspan="2" data-options="field:'book_value',width:100,halign:'center',align:'right', formatter:priceformat">Net Book<br>Value</th>
            <th rowspan="2" data-options="field:'gainloss_type',width:100,align:'center'">Gain/Loss</th>
            <th rowspan="2" data-options="field:'gainloss_value',width:100,halign:'center',align:'right', formatter:priceformat">Gain/Loss<br>Value</th>
            <th rowspan="2" data-options="field:'remarks',width:100,align:'center'">Remarks</th>
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

<div id="toolbar" style="height: 195px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Disposal Date</span>
                    <input style="width:28%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Name</span>
                    <input style="width:60%;" name="filter_number" id="filter_number" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem" style="margin-bottom: 10px;">
                <span style="width:35%; display:inline-block;">Disposed Type</span>
                <input name="type" id="selling" value="SELLING" class="easyui-radiobutton"> &nbsp; Selling &nbsp; &nbsp; &nbsp;
                <input name="type" id="retairement" value="RETAIREMENT" class="easyui-radiobutton"> &nbsp; Retairement &nbsp; &nbsp; &nbsp;
                <input name="type" id="exchange" value="EXCHANGE" class="easyui-radiobutton"> &nbsp; Exchange
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Disposed Date</span>
                <input style="width:60%;" name="disposal_date" id="disposal_date" class="easyui-datebox" required data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Asset No</span>
                <input style="width:60%;" name="asset_fixed_id" id="asset_fixed_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Asset Name</span>
                <input style="width:60%;" id="asset_name" class="easyui-textbox" disabled>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Asset Date</span>
                <input style="width:60%;" id="asset_date" class="easyui-datebox" disabled>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Asset Cost</span>
                <input style="width:30%;" id="asset_cost" class="easyui-numberbox" disabled data-options="groupSeparator:'.',decimalSeparator:','">
                <input style="width:30%;" id="currency" class="easyui-textbox" disabled data-options="prompt:'Currency'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Est. Economic</span>
                <input style="width:30%;" id="estimate_year" class="easyui-textbox" disabled data-options="prompt:'Year'">
                <input style="width:30%;" id="estimate_month" class="easyui-textbox" disabled data-options="prompt:'Month'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Residual Value</span>
                <input style="width:30%;" id="residual_value" class="easyui-numberbox" disabled data-options="groupSeparator:'.',decimalSeparator:','">
                <input style="width:30%;" id="currency2" class="easyui-textbox" disabled data-options="prompt:'Currency'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Accumulated Depreciation</span>
                <input style="width:30%;" id="depreciation" class="easyui-numberbox" disabled data-options="groupSeparator:'.',decimalSeparator:','">
                <input style="width:30%;" id="currency3" class="easyui-textbox" disabled data-options="prompt:'Currency'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Disposal Value</span>
                <input style="width:40%;" name="disposal" id="disposal" required class="easyui-numberbox" data-options="groupSeparator:'.',decimalSeparator:','">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Net Book Value</span>
                <input style="width:40%;" name="book_value" id="book_value" required class="easyui-numberbox" data-options="groupSeparator:'.',decimalSeparator:','">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Gain/Loss</span>
                <select style="width:30%;" name="gainloss_type" id="gainloss_type" required class="easyui-combobox" panelHeight="auto">
                    <option value="GAIN">GAIN</option>
                    <option value="LOSS">LOSS</option>
                </select>
                <input style="width:30%;" name="gainloss_value" id="gainloss_value" required class="easyui-numberbox" data-options="groupSeparator:'.',decimalSeparator:','">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%; height: 80px;" name="remarks" id="remarks" class="easyui-textbox" multiline="true">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('finance/disposeds/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('finance/disposeds/create') ?>';
        $('#frm_insert').form('clear');
        $('#selling').radiobutton({
            checked: true
        });
        $("#disposal_date").datebox('setValue', "<?= date("Y-m-d") ?>");
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('finance/disposeds/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('finance/disposeds/delete') ?>',
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

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_number = $("#filter_number").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_number=" + window.btoa(filter_number);

        $('#dg').datagrid({
            url: '<?= base_url('finance/disposeds/datatables') ?>' + url,
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/disposeds/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_number = $("#filter_number").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_number=" + window.btoa(filter_number);

        window.location.assign('<?= base_url('finance/disposeds/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        filter();

        //Save Data
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

        //GET COMPONENT
        $('#asset_fixed_id').combogrid({
            url: '<?= base_url('finance/disposeds/readFixeds') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Asset No",
            columns: [
                [{
                    field: 'number',
                    title: 'Asset No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Asset Name',
                    width: 250
                }, ]
            ],
            onSelect: function(index, row) {
                $("#asset_name").textbox('setValue', row.name);
                $("#asset_date").datebox('setValue', row.usage_date);
                $("#asset_cost").numberbox('setValue', row.cost);
                $("#currency").textbox('setValue', row.currency);
                $("#currency2").textbox('setValue', row.currency);
                $("#currency3").textbox('setValue', row.currency);
                $("#estimate_year").textbox('setValue', row.estimate_year);
                $("#estimate_month").textbox('setValue', row.estimate_month);
                $("#residual_value").numberbox('setValue', row.residual_value);
                $("#depreciation").numberbox('setValue', row.depreciation);
                $("#book_value").numberbox('setValue', row.book_value);
            }
        });

        $('#filter_number').combogrid({
            url: '<?= base_url('finance/disposeds/readfinance') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Asset No",
            columns: [
                [{
                    field: 'number',
                    title: 'Asset No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Asset Name',
                    width: 250
                }, ]
            ],
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

    function priceformat(value, row) {
        var digits = 0;
        var format = "id-ID";

        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                minimumFractionDigits: digits
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }
</script>
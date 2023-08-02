<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',align:'center',width:100">Code</th>
            <th rowspan="2" data-options="field:'name',halign:'center',width:200">Name</th>
            <th rowspan="2" data-options="field:'type',align:'center',width:100">Type</th>
            <th rowspan="2" data-options="field:'address',halign:'center',width:250">Address</th>
            <th rowspan="2" data-options="field:'attention',halign:'center',width:150">Contact Person</th>
            <th rowspan="2" data-options="field:'telp',halign:'center',width:150">Telp</th>
            <th rowspan="2" data-options="field:'fax',halign:'center',width:150">Fax</th>
            <th rowspan="2" data-options="field:'email',halign:'center',width:200">Email</th>
            <th rowspan="2" data-options="field:'website',halign:'center',width:150">Website</th>
            <th rowspan="2" data-options="field:'currency',align:'center',width:80">Currency</th>
            <th rowspan="2" data-options="field:'payment_term',align:'center',width:100">Payment Term</th>
            <th rowspan="2" data-options="field:'incoterm',align:'center',width:80">Incoterm</th>
            <th rowspan="2" data-options="field:'vat_status',align:'center',width:80">Vat Status</th>
            <th rowspan="2" data-options="field:'vat',align:'center',width:80">Vat</th>
            <th rowspan="2" data-options="field:'tax',halign:'center',width:120">Tax No</th>
            <th rowspan="2" data-options="field:'account_number',width:100,halign:'center'">Account No</th>
            <th rowspan="2" data-options="field:'account_name',width:200,halign:'center'">Account Name</th>
            <th rowspan="2" data-options="field:'bank_account',halign:'center',width:120">Bank Account</th>
            <th rowspan="2" data-options="field:'bank_name',halign:'center',width:200">Bank Name</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_name',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_name',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Code</span>
                <input style="width:30%;" name="number" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Name</span>
                <input style="width:60%;" name="name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type</span>
                <select style="width:30%;" name="type" class="easyui-combobox" panelHeight="auto">
                    <option value="IMPORT">IMPORT</option>
                    <option value="LOCAL">LOCAL</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Address</span>
                <input style="width:60%; height: 60px;" name="address" required="" class="easyui-textbox" multiline="true">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Contact Person</span>
                <input style="width:60%;" name="attention" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Telp</span>
                <input style="width:60%;" name="telp" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Fax</span>
                <input style="width:60%;" name="fax" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Email</span>
                <input style="width:60%;" name="email" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Website</span>
                <input style="width:60%;" name="website" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Currency</span>
                <input style="width:60%;" name="currency" id="currency" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Payment Term (Days)</span>
                <input style="width:30%;" name="payment_term" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Incoterm</span>
                <input style="width:60%;" name="incoterm" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Vat Status</span>
                <select style="width:30%;" name="vat_status" class="easyui-combobox" panelHeight="auto">
                    <option value="VAT">VAT</option>
                    <option value="NON VAT">NON VAT</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">VAT (%)</span>
                <input style="width:30%;" name="vat" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Tax No</span>
                <input style="width:60%;" name="tax" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Account Name</span>
                <input style="width:60%;" name="account_number" id="account_number" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Bank Account</span>
                <input style="width:60%;" name="bank_account" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Bank Name</span>
                <input style="width:60%;" name="bank_name" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/suppliers/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/suppliers/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/suppliers/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/suppliers/delete') ?>',
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
        window.location.assign('<?= base_url('master/suppliers/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/suppliers/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true
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
                            // $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });
        //GET CURRENCY
        $('#currency').combogrid({
            url: '<?= base_url('master/currencies/reads') ?>',
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Currency",
            columns: [
                [{
                    field: 'symbol',
                    title: 'Symbol',
                    width: 100
                }, {
                    field: 'number',
                    title: 'Currency ID',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Currency Name',
                    width: 250
                }, ]
            ]
        });

        $('#account_number').combobox({
            url: '<?= base_url('finance/account_coa/reads') ?>',
            valueField: 'account_number',
            textField: 'account_name',
            prompt: "Choose Account No"
        });
    });
</script>
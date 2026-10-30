<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Default Account is taken from <b>Master Data > Accounting & Finance > Chart of Account</b></li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:80,halign:'center'">Code</th>
            <th rowspan="2" data-options="field:'name',width:150,halign:'center'">Journal Name</th>
            <th rowspan="2" data-options="field:'type',width:100,halign:'center'">Type</th>
            <th rowspan="2" data-options="field:'module',width:200,halign:'center'">Module</th>
            <th rowspan="2" data-options="field:'account_number',width:100,halign:'center'">Account No</th>
            <th rowspan="2" data-options="field:'account_name',width:200,halign:'center'">Account Name</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center'">Debit/Credit</th>
            <th rowspan="2" data-options="field:'is_journal_inventory',width:120,align:'center',formatter:function(v){return v==1?'YES':'NO';}">Journal Inventory</th>
            <th rowspan="2" data-options="field:'inventory_process',width:120,align:'center'">Inventory Process</th>
            <th rowspan="2" data-options="field:'transaction_type',width:150,align:'center'">Transaction Type</th>
            <th rowspan="2" data-options="field:'division',width:80,align:'center'">Division</th>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Journal Code</span>
                <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Journal Name</span>
                <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <!-- Journal Inventory menentukan Type, Module, dan sumber akun DEBIT/CREDIT -->
                <span style="width:35%; display:inline-block;">Journal Inventory</span>
                <select style="width:60%;" name="is_journal_inventory" id="is_journal_inventory" panelHeight="auto" class="easyui-combobox" data-options="editable:false, onChange:toggleInventoryProcess">
                    <option value="0">NO</option>
                    <option value="1">YES</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type</span>
                <input style="width:60%;" name="type" id="type" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Module</span>
                <!-- Pilihan module diisi loadModules(): Journal Inventory = NO -> module finance, YES -> module inventory -->
                <select style="width:60%;" name="module" id="module" class="easyui-combobox" data-options="panelHeight:200, editable:false, multiple:true, multivalue:false, separator:',', onChange:function(){ loadTransactionTypes(); }">
                </select>
            </div>
            <div class="fitem" id="info_setup" style="display:none; color:#a15c00;">
                <i class="fa fa-info-circle"></i> Debit/Credit account diambil dari <b>Journal Setups</b>.
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Default Account</span>
                <input style="width:60%;" name="account_number" id="account_number" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:60%;" name="status" id="status" panelHeight="auto" class="easyui-combobox">
                    <option value=""></option>
                    <option value="DEBIT">DEBIT</option>
                    <option value="CREDIT">CREDIT</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Inventory Process</span>
                <select style="width:60%;" name="inventory_process" id="inventory_process" panelHeight="auto" class="easyui-combobox" data-options="editable:false">
                    <option value=""></option>
                    <option value="IN">IN</option>
                    <option value="OUT">OUT</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Transaction Type</span>
                <input style="width:60%;" name="transaction_type" id="transaction_type" class="easyui-combobox" data-options="valueField:'type', textField:'type', editable:false, panelHeight:'auto'">
            </div>
            <div class="fitem">
                <!-- Journal type inventory per division item (item_rm.division), kosong = semua division -->
                <span style="width:35%; display:inline-block;">Division</span>
                <select style="width:60%;" name="division" id="division" class="easyui-combobox" data-options="editable:false, panelHeight:'auto'">
                    <option value="">ALL DIVISION</option>
                    <?php foreach ($divisions as $division) : ?>
                        <option value="<?= $division ?>"><?= $division ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('finance/journal_types/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    var url_save = '';

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('finance/journal_types/create') ?>';
        $('#frm_insert').form('clear');
        $('#module').combobox('clear');
        $('#is_journal_inventory').combobox('setValue', '0');
        toggleInventoryProcess('0');
    }

    // Journal Posting biasa (journal_postings) dan Journal Posting Inventory (journal_inventory) memakai module berbeda
    var financeModules = <?= json_encode($finance_modules) ?>;
    var inventoryModules = <?= json_encode($inventory_modules) ?>;

    // Isi pilihan Module sesuai Journal Inventory, module terpilih yang tidak berlaku dibuang
    function loadModules(isInventory) {
        var $module = $('#module');
        var list = isInventory ? inventoryModules : financeModules;
        var current = $module.combobox('getValues') || [];
        $module.combobox('loadData', $.map(list, function(m) { return {value: m, text: m}; }));
        $module.combobox('setValues', $.grep(current, function(m) { return list.indexOf(m) >= 0; }));
    }

    // Inventory Process hanya berlaku untuk journal type inventory
    function toggleInventoryProcess(val) {
        loadModules(val == '1');

        // YES: Type = Inventory, akun DEBIT/CREDIT dari Journal Setups (Default Account & Status tidak dipakai)
        var $typeBox = $('#type');
        if (val == '1') {
            $typeBox.textbox('setValue', 'Inventory').textbox('readonly', true);
            $('#account_number').combogrid('clear').combogrid('disable');
            $('#status').combobox('clear').combobox('disable');
            $('#info_setup').show();
        } else {
            if ($typeBox.textbox('getValue') == 'Inventory') $typeBox.textbox('setValue', '');
            $typeBox.textbox('readonly', false);
            $('#account_number').combogrid('enable');
            $('#status').combobox('enable');
            $('#info_setup').hide();
        }

        var $process = $('#inventory_process');
        if (val == '1') {
            $process.combobox('enable');
            if (!$process.combobox('getValue')) {
                $process.combobox('setValue', 'IN');
            }
        } else {
            $process.combobox('clear').combobox('disable');
        }
        $('#division').combobox(val == '1' ? 'enable' : 'disable');
        if (val != '1') $('#division').combobox('setValue', '');
        loadTransactionTypes();
    }

    // Jenis transaksi untuk module yang punya lebih dari 1 jenis transaksi dalam 1 menu
    var transactionRmTypes = []; // master transactions (Transaction RM), diload saat halaman dibuka
    var transactionFgTypes = []; // master fg_transactions (Transaction FG), diload saat halaman dibuka
    var nonSupplySheetTypes = [{type: 'Issued Production'}, {type: 'Issued Customer'}, {type: 'Issued Subcont'}];

    // selected: nilai yang di-set setelah list diload (dipakai saat edit)
    function loadTransactionTypes(selected) {
        var $type = $('#transaction_type');
        var mods = $('#module').combobox('getValues') || [];
        var isInventory = $('#is_journal_inventory').combobox('getValue') == '1';
        var current = (selected !== undefined) ? selected : $type.combobox('getValue');

        var data = [];
        if (mods.indexOf('TRANSACTION RM') >= 0) data = data.concat(transactionRmTypes);
        if (mods.indexOf('TRANSACTION FG') >= 0) data = data.concat(transactionFgTypes);
        if (mods.indexOf('NON SUPPLY SHEET') >= 0) data = data.concat(nonSupplySheetTypes);

        $type.combobox('loadData', data);
        var exists = data.some(function(d) { return d.type == current; });
        if (isInventory && data.length > 0) {
            $type.combobox('enable').combobox('setValue', exists ? current : '');
        } else {
            $type.combobox('clear').combobox('disable');
        }
    }

    //EDIT DATA
    function update() {
        var selected = $('#dg').datagrid('getSelected');
        if (selected) {
            // Nilai null (misal Module belum diisi) bikin combobox multiple error (split of null) dan menghentikan load
            var row = {};
            $.each(selected, function(k, v) { row[k] = (v === null || v === undefined) ? '' : v; });
            url_save = '<?= base_url('finance/journal_types/update') ?>?id=' + btoa(row.id);
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            var isJInv = (row.is_journal_inventory == 1) ? '1' : '0';
            $('#inventory_process').combobox('setValue', row.inventory_process || '');
            $('#is_journal_inventory').combobox('setValue', isJInv);
            toggleInventoryProcess(isJInv);
            // Module di-set setelah pilihan module dimuat sesuai Journal Inventory
            $('#module').combobox('setValues', row.module ? row.module.split(',') : []);
            $('#division').combobox('setValue', row.division || '');
            loadTransactionTypes(row.transaction_type || '');
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
                            url: '<?= base_url('finance/journal_types/delete') ?>',
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
        window.location.assign('<?= base_url('finance/journal_types/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('finance/journal_types/datatables') ?>',
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
                        onSubmit: function(param) {
                            var mods = $('#module').combobox('getValues');
                            if (mods && mods.length > 0) {
                                param.module = mods.join(',');
                            }
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                                $('#dlg_insert').dialog('close');
                                $('#dg').datagrid('reload');
                            } else {
                                // Dialog tetap terbuka supaya isian bisa diperbaiki
                                toastr.error(result.message, result.title);
                            }
                        }
                    });
                }
            }]
        });

        $.getJSON('<?= base_url('warehouse/transaction_rm/readType') ?>', function(records) {
            transactionRmTypes = $.map(records, function(r) { return {type: r.type}; });
        });
        $.getJSON('<?= base_url('warehouse/transaction_fg/readType') ?>', function(records) {
            transactionFgTypes = $.map(records, function(r) { return {type: r.type}; });
        });

        $("#account_number").combogrid({
            url: '<?= base_url('finance/account_coa/reads') ?>',
            panelWidth: 420,
            idField: 'account_number',
            textField: 'account_name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Default Account",
            columns: [
                [{
                    field: 'account_number',
                    title: 'Account No',
                    width: 120
                }, {
                    field: 'account_name',
                    title: 'Account Name',
                    width: 250
                }, ]
            ],
        });
    });
</script>
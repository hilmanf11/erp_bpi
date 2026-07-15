<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'item_number',width:200,halign:'center'">Part No</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'">Part Name</th>
            <th rowspan="2" data-options="field:'uom',width:100,align:'center'">Uom</th>
            <th rowspan="2" data-options="field:'request_no',width:150,align:'center'">Supply Sheet</th>
            <th rowspan="2" data-options="field:'product_number',width:150,align:'center'">Product No</th>
            <th rowspan="2" data-options="field:'workorder',width:150,align:'center'">Workorder</th>
            <th rowspan="2" data-options="field:'begin',width:80,halign:'center',align:'right', formatter:numberformats">Begin</th>
            <th rowspan="2" data-options="field:'needs',width:80,halign:'center',align:'right', formatter:numberformats">Need</th>  <!-- need setelah di tambah purging -->
            <th rowspan="2" data-options="field:'qty_purging',width:80,halign:'center',align:'right', formatter:numberformats">Purging</th>
            <th rowspan="2" data-options="field:'supply',width:80,halign:'center',align:'right', formatter:numberformats">Supply</th>
            <th rowspan="2" data-options="field:'balance',width:80,halign:'center',align:'right', formatter:numberformats">Balance</th>
            <th rowspan="2" data-options="field:'warehouse',width:80,halign:'center',align:'right', formatter:numberformats">Warehouse</th>
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
    <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="adjWipBalance()"><i class="fa fa-adjust"></i> Adjust</a>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Item ID</span>
                <input style="width:60%;" name="item_rm_id" id="item_rm_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supply Sheet</span>
                <input style="width:60%;" name="request_no" id="request_no" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Uom</span>
                <input style="width:60%;" name="uom" id="uom" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Begin</span>
                <input style="width:30%;" name="begin" class="easyui-numberbox" required data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Need</span>
                <input style="width:30%;" name="need" class="easyui-numberbox" required data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Issued</span>
                <input style="width:30%;" name="issued" class="easyui-numberbox" required data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Balance</span>
                <input style="width:30%;" name="balance" class="easyui-numberbox" required data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Warehouse</span>
                <input style="width:30%;" name="warehouse" class="easyui-numberbox" required data-options="precision:2">
            </div>
        </fieldset>
    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('warehouse/wip_balances/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('warehouse/wip_balances/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('warehouse/wip_balances/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('warehouse/wip_balances/delete') ?>',
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
        window.location.assign('<?= base_url('warehouse/wip_balances/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/wip_balances/datatables') ?>',
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
                            // $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });
        $('#item_rm_id').combogrid({
            url: '<?= base_url('master/item_rm/reads') ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product",
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }]
            ],
            onSelect: function(valItem, rowItem) {
                $("#uom").textbox('setValue', rowItem.uom);
            }
        });
        $("#request_no").combobox({
            url: '<?= base_url('planning/supply_sheets/readRequestNo') ?>',
            valueField: 'request_no',
            textField: 'request_no',
            prompt: "Choose Supply Sheet"
        });
    });
    function numberformat(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }
    function numberformats(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 4
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function adjWipBalance() {
        $.messager.confirm('Confirm', 'Are you sure you want to create WIP balance?', function(r) {
            if (r) {
                Swal.fire({
                    title: 'Enter Password',
                    input: 'password',
                    inputPlaceholder: 'Enter your password',
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    allowOutsideClick: false,
                    inputValidator: (value) => {
                        if (!value) {
                            return 'You need to write something!'
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Validasi Password
                        if (result.value === 'PM001@123#') {
                            Swal.fire({
                                title: 'Please wait...',
                                text: 'Creating WIP balances...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            $.post("<?= site_url('warehouse/wip_balances/create_all') ?>", function(response) {
                                Swal.close();
                                Swal.fire({
                                    title: 'Success',
                                    text: response.message,
                                    icon: 'success'
                                });
                                $('#dg').datagrid('reload');
                            }, 'json')
                            .fail(function() {
                                Swal.close();
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Failed to create WIP balances.',
                                    icon: 'error'
                                });
                                $('#dg').datagrid('reload');
                            });
                        } else {
                            // Jika password salah
                            Swal.fire({
                                title: 'Access Denied',
                                text: 'Incorrect password!',
                                icon: 'error'
                            });
                        }
                    }
                });
            }
        });
    }
</script>
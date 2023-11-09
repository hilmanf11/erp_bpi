<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'item_fg_id',width:150,align:'center'">Product No.</th>
            <th rowspan="2" data-options="field:'machine_number',width:150,align:'center'">Machine No.</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:150,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'cycle_time',width:100,halign:'center'">Cycle Time <br>(Second)</th>
            <th rowspan="2" data-options="field:'productcivity',width:100,halign:'center'">Productivity <br>Factor (%)</th>
            <th rowspan="2" data-options="field:'cavity_actual',width:100,halign:'center'">Cavity Actual</th>
            <th rowspan="2" data-options="field:'capacity_hour',width:100,halign:'center'">Capacity/Hour</th>
            <th rowspan="2" data-options="field:'capacity_shift',width:100,halign:'center'">Capacity/Shift</th>
            <th rowspan="2" data-options="field:'capacity_day',width:100,halign:'center'">Capacity/Day</th>
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center'">Remarks</th>
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
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 450px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No.</span>
                <input style="width:60%;" name="item_fg_id" id="item_fg_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Machine No.</span>
                <input style="width:60%;" name="machine_id" id="machine_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cycle Time</span>
                <input style="width:60%;" id="cycle_time" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Productivity Factor %</span>
                <input style="width:60%;" id="productcivity" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cavity Actual</span>
                <input style="width:60%;" id="cavity_actual" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cavity/Hour</span>
                <input style="width:60%;" name="capacity_hour" id="capacity_hour" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cavity/Shift</span>
                <input style="width:60%;" name="capacity_shift" id="capacity_shift" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cavity/Day</span>
                <input style="width:60%;" name="capacity_day" id="capacity_day" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/production_capacities/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/production_capacities/create') ?>';
        $('#frm_insert').form('clear');

    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/production_capacities/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/production_capacities/delete') ?>',
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
        window.location.assign('<?= base_url('master/production_capacities/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/production_capacities/datatables') ?>',
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
                            $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });
    });

    $('#item_fg_id').combogrid({
            url: '<?php echo base_url('master/production_capacities/readItems'); ?>',
            required: true,
            panelWidth: 500,
            idField: 'item_fg_id',
            textField: 'item_fg_id',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Product ID',
            columns: [
                [{
                    field: 'item_fg_id',
                    title: 'Product ID',
                    width: 120
                }, {
                    field: 'item_fg_number',
                    title: 'Product No.',
                    width: 150
                }, {
                    field: 'item_fg_name',
                    title: 'Product Name',
                    width: 200
                }]
            ],
            onSelect: function(val, rows) {
                $('#machine_id').combobox({
                    url: '<?php echo base_url('master/production_capacities/readMachines/'); ?>' + btoa(rows.item_fg_id),
                    valueField: 'machine_id',
                    textField: 'machine_number',
                    prompt: "Choose Machine No",
                    onSelect: function(menu_loadings){
                        $("#cycle_time").textbox('setValue', menu_loadings.cycle_time);
                        $("#productcivity").textbox('setValue', menu_loadings.productcivity);
                        $("#cavity_actual").textbox('setValue', menu_loadings.cavity_actual); // mengambil dari molds

                        var capacity_hour = (3600 / menu_loadings.cycle_time) * menu_loadings.cavity_actual * (menu_loadings.productcivity / 100);
                        var capacity_shift = (capacity_hour * capacity_hour);
                        var capacity_day = (capacity_hour *  capacity_hour * capacity_shift * menu_loadings.shift);

                        $("#capacity_hour").textbox('setValue', capacity_hour);
                        $("#capacity_shift").textbox('setValue', capacity_shift);
                        $("#capacity_day").textbox('setValue', capacity_day);
                    }
                });
            }
    });

    

</script>
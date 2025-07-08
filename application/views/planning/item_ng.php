<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'document',width:150,halign:'center'">Document No</th>
            <th rowspan="2" data-options="field:'document_scrap',width:150,halign:'center'">Scrap No</th>
            <th rowspan="2" data-options="field:'departement',width:120,halign:'center'">Departement</th>
            <th rowspan="2" data-options="field:'process',width:120,halign:'center'">Process</th>
            <th rowspan="2" data-options="field:'type',width:150,halign:'center'">NG Type</th>
            <th rowspan="2" data-options="field:'workorder',width:150,halign:'center'">Work Order</th>
            <th rowspan="2" data-options="field:'product_no',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'product_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'qty_sh',width:80,halign:'center',align:'right',formatter:numberformat">Qty WO</th>
            <th rowspan="2" data-options="field:'qty_product',width:80,halign:'center',align:'right',formatter:numberformat">Qty NG</th>
            <th rowspan="2" data-options="field:'shift',width:80,halign:'center'">Shift</th>
            <!--<th rowspan="2" data-options="field:'scrap',width:80,halign:'center',align:'right',formatter:numberformat">Scrap</th>
            <th rowspan="2" data-options="field:'balance',width:80,halign:'center',align:'right',formatter:numberformat">Balance</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">Uom</th> -->
            <!-- <th rowspan="2" data-options="field:'remarks',width:150,halign:'center'">Remarks</th> -->
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

<div id="toolbar" style="height: 260px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width: 100%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" id="filter_document" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_family_id" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
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

<!-- <div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div> -->

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1100px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:60%;" name="trans_date" id="trans_date" class="easyui-datebox" required data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document NG</span>
                    <input style="width:60%;" name="document" id="document" class="easyui-textbox" readonly required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document Scrap</span>
                    <input style="width:60%;" name="document_scrap" id="document_scrap" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Departement</span>
                    <input style="width:60%;" name="departement" id="departement" class="easyui-textbox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Process</span>
                    <input style="width:60%;" name="process" id="process" class="easyui-combogrid" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">NG Type</span>
                    <input style="width:60%;" id="type" name="type" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Product Id</span>
                    <input style="width:60%;" name="item_fg_id" id="item_fg_id" class="easyui-textbox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_fg_number" id="item_fg_number" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Workorder</span>
                    <input style="width:60%;" name="workorder" id="workorder" class="easyui-textbox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty WO</span>
                    <input style="width:60%;" name="qty_sh" id="qty_sh" class="easyui-numberbox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty Product</span>
                    <input style="width:60%;" name="qty_product" id="qty_product" class="easyui-numberbox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Accumulate</span>
                    <input style="width:60%;" name="accumulate_sh" id="accumulate_sh" class="easyui-numberbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Balance</span>
                    <input style="width:60%;" name="balance_sh" id="balance_sh" class="easyui-numberbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Shift</span>
                        <select style="width:30%;" name="shift" id="shift" required="" panelHeight="auto" class="easyui-combobox">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" name="period" id="period" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="NG Transaction Lists" idField="item_number"><!-- OK -->
            <thead>
                <tr>
                    <th field="ck" checkbox="true"></th>
                    <th data-options="field:'action',width:120,formatter:buttonEdit">Action</th>
                    <th hidden data-options="field:'id',width:100">ID</th>
                    <th data-options="field:'item_rm_id',width:150">Part id</th>
                    <th data-options="field:'number',width:150">Part No</th>
                    <th data-options="field:'name',width:100">Part Name</th>
                    <th data-options="field:'uom',width:80">Uom</th>
                    <th data-options="field:'stock',width:80">Qty</th>
                    <th data-options="field:'qty',width:100,editor: {type: 'numberbox', options: {required: true}}">NG</th>
                    <th data-options="field:'scrap',width:100,editor: {type: 'numberbox', options: {required: true}}">Scrap</th>
                    <th data-options="field:'balance',width:100,formatter:balanceFormatter">Balance</th>
                    <th data-options="field:'remarks',width:150,editor: {type: 'textbox', options: {required: true}}">Remarks</th>
                </tr>
            </thead>
        </table>
    </form>
</div>

<!-- Update -->
<div id="dlg_update" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:30%;" name="stock" id="stock" class="easyui-numberbox" data-options="precision:2" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">NG</span>
                <input style="width:30%;" name="qty" id="qty" class="easyui-numberbox" data-options="precision:2" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Scrap</span>
                <input style="width:30%;" name="scrap" id="scrap" class="easyui-numberbox" data-options="precision:2" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Balance</span>
                <input style="width:30%;" name="balance" id="balance" class="easyui-numberbox" data-options="precision:2" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%; height: 50px;" name="remarks" id="remarks" class="easyui-textbox" multiline="true">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->

<iframe id="printout" src="<?= base_url('planning/item_ng/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {

        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');

        $("#trans_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#departement").textbox('setValue', "PRODUCTION");

        // addTable();

        var dg = $('#dg2').datagrid({
            onBeforeEdit: function(index, row) {
                row.editing = true;
                $(this).datagrid('refreshRow', index);
            },
            onAfterEdit: function(index, row) {
                row.editing = false;
                $(this).datagrid('refreshRow', index);
            },
            onCancelEdit: function(index, row) {
                row.editing = false;
                $(this).datagrid('refreshRow', index);
            },
        });
    }

    function balanceFormatter(value, row, index) {
        var stock = row.stock || 0;
        var qty = row.qty || 0;
        var scrap = row.scrap || 0;

        var balance = stock - qty - scrap;

        return balance;
    }

    // function addTable(wo_no) {

    //     var lastIndex;
    //     var dg = $('#dg2').datagrid({
    //         singleSelect: true,
    //         columns: [
    //             [{
    //                 field: 'item_number',
    //                 width: 250,
    //                 halign: 'center',
    //                 title: "Product No",
    //                 editor: {
    //                     type: 'combogrid',
    //                     options: {
    //                         url: '<?= base_url('planning/item_ng/readItems/') ?>' + window.btoa(wo_no),
    //                         required: true,
    //                         panelWidth: 350,
    //                         idField: 'number',
    //                         textField: 'number',
    //                         mode: 'remote',
    //                         fitColumns: true,
    //                         prompt: 'Choose Product',
    //                         columns: [
    //                             [{
    //                                 field: 'number',
    //                                 title: 'Product No',
    //                                 width: 150
    //                             }, {
    //                                 field: 'name',
    //                                 title: 'Product Name',
    //                                 width: 150
    //                             }]
    //                         ],
    //                         onSelect: function(value, rows) {
    //                             console.log(rows);
    //                             var dg = $('#dg2');
    //                             var row = dg.datagrid('getSelected');
    //                             var rowIndex = dg.datagrid('getRowIndex', row);
    //                             var ed = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'item_rm_id'
    //                             });
    //                             var ed2 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'item_name'
    //                             });
    //                             var ed3 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'uom'
    //                             });
    //                             var ed4 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'stock'
    //                             });
    //                             var ed5 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'scrap'
    //                             });

    //                             $(ed.target).textbox('setValue', rows.id);
    //                             $(ed2.target).textbox('setValue', rows.name);
    //                             $(ed3.target).textbox('setValue', rows.uom);
    //                             $(ed4.target).numberbox('setValue', rows.qty);
    //                             $(ed5.target).numberbox('setValue', rows.scrap);
    //                         }
    //                     }
    //                 }
    //             }, {
    //                 field: 'item_name',
    //                 width: 150,
    //                 halign: 'center',
    //                 title: "Product Name",
    //                 editor: {
    //                     type: 'textbox',
    //                     options: {
    //                         readonly: true
    //                     }
    //                 }
    //             }, {
    //                 field: 'item_rm_id',
    //                 hidden: true,
    //                 width: 100,
    //                 halign: 'center',
    //                 title: "ID",
    //                 editor: {
    //                     type: 'textbox'
    //                 }
    //             }, {
    //                 field: 'uom',
    //                 width: 80,
    //                 halign: 'center',
    //                 title: "Uom",
    //                 editor: {
    //                     type: 'textbox',
    //                     options: {
    //                         readonly: true
    //                     }
    //                 }
    //             }, {
    //                 field: 'stock',
    //                 width: 80,
    //                 halign: 'center',
    //                 title: "Qty",
    //                 editor: {
    //                     type: 'numberbox',
    //                     options: {
    //                         required: true,
    //                         readonly: true,
    //                         precision: 2
    //                     }
    //                 }
    //             }, {
    //                 field: 'qty',
    //                 width: 80,
    //                 halign: 'center',
    //                 title: "NG",
    //                 editor: {
    //                     type: 'numberbox',
    //                     options: {
    //                         required: true,
    //                         precision: 2,
    //                         onChange: function(valQty) {
    //                             var row = $('#dg2').datagrid('getSelected');
    //                             var rowIndex = $('#dg2').datagrid('getRowIndex', row);

    //                             var ed = $('#dg2').datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'stock'
    //                             });

    //                             var ed2 = $('#dg2').datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'scrap'
    //                             });

    //                             var ed3 = $('#dg2').datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'balance'
    //                             });

    //                             var stock = $(ed.target).numberbox('getValue');
    //                             var scrap = $(ed2.target).numberbox('getValue');
    //                             $(ed3.target).numberbox('setValue', (parseFloat(stock) - (parseFloat(valQty) + parseFloat(scrap))));
    //                         }
    //                     }
    //                 }
    //             }, {
    //                 field: 'scrap',
    //                 width: 80,
    //                 halign: 'center',
    //                 title: "Scrap",
    //                 editor: {
    //                     type: 'numberbox',
    //                     options: {
    //                         required: true,
    //                         precision: 2
    //                     },
    //                     options: {
    //                         required: true,
    //                         precision: 2,
    //                         onChange: function(valScrap) {
    //                             var row = $('#dg2').datagrid('getSelected');
    //                             var rowIndex = $('#dg2').datagrid('getRowIndex', row);

    //                             var ed = $('#dg2').datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'stock'
    //                             });

    //                             var ed2 = $('#dg2').datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'qty'
    //                             });

    //                             var ed3 = $('#dg2').datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'balance'
    //                             });

    //                             var stock = $(ed.target).numberbox('getValue');
    //                             var qty = $(ed2.target).numberbox('getValue');
    //                             $(ed3.target).numberbox('setValue', (parseFloat(stock) - (parseFloat(valScrap) + parseFloat(qty))));
    //                         }
    //                     }
    //                 }
    //             }, {
    //                 field: 'balance',
    //                 width: 80,
    //                 halign: 'center',
    //                 title: "Balance",
    //                 editor: {
    //                     type: 'numberbox',
    //                     options: {
    //                         required: true,
    //                         readonly: true,
    //                         precision: 2
    //                     }
    //                 }
    //             }, {
    //                 field: 'remarks',
    //                 width: 200,
    //                 halign: 'center',
    //                 title: "Remarks",
    //                 editor: {
    //                     type: 'textbox'
    //                 }
    //             }]
    //         ],
    //         onClickRow: function(rowIndex) {
    //             if (lastIndex != rowIndex) {
    //                 $(this).datagrid('endEdit', lastIndex);
    //                 $(this).datagrid('beginEdit', rowIndex);
    //             }
    //             lastIndex = rowIndex;
    //         },
    //         onBeginEdit: function(rowIndex, row) {
    //             var editors = $('#dg2').datagrid('getEditors', rowIndex);
    //         }
    //     });
    // }
    // var editIndex = undefined;

    // function endEditing() {
    //     if (editIndex == undefined) {
    //         return true
    //     }
    //     if ($('#dg2').datagrid('validateRow', editIndex)) {
    //         $('#dg2').datagrid('endEdit', editIndex);
    //         editIndex = undefined;
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }

    // function append() {
    //     var workorder = $("#workorder").combobox('getValue');
    //     console.log(workorder);
    //     if (workorder != "") {
    //         if (endEditing()) {
    //             $('#dg2').datagrid('appendRow', {
    //                 stock: '0',
    //                 qty: '0',
    //                 scrap: '0',
    //             });
    //             editIndex = $('#dg2').datagrid('getRows').length - 1;
    //             $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
    //         }
    //     } else {
    //         toastr.error("Please Choose Workorder first");
    //     }
    // }

    // function removeit() {
    //     if (editIndex == undefined) {
    //         return
    //     }
    //     $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
    //     editIndex = undefined;
    // }

    function preview() {
        var workorder = $("#workorder").textbox('getValue');
        var qty_product = $("#qty_product").textbox('getValue');
        var qty_sh = $("#qty_sh").textbox('getValue');
        console.log(workorder);

        if (workorder == "" || qty_product == "") {
            toastr.info('Please completed your data');
        } else {
            var lastIndex;
            if (workorder != "") {
                var dg = $('#dg2').datagrid({
                    url: '<?= base_url('planning/item_ng/datatablesTemp') ?>?workorder=' + window.btoa(workorder) + '&qty_product=' + qty_product + '&qty_sh=' + qty_sh,
                });
            } else {
                toastr.info('Please completed your data');
            }
        }
    }

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg2').datagrid('validateRow', editIndex)) {
            $('#dg2').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function append() {
        if (endEditing()) {
            $('#dg2').datagrid('appendRow', {
                "action": 0
            });
            editIndex = $('#dg2').datagrid('getRows').length - 1;
            $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);

            var dg = $('#dg2');
            var row = dg.datagrid('getSelected');
            var rowIndex = dg.datagrid('getRowIndex', row);
        }
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function editrow(target) {
        $('#dg2').datagrid('selectRow', getRowIndex(target));
        $('#dg2').datagrid('beginEdit', getRowIndex(target));
    }

    function deleterow(target) {
        $.messager.confirm('Confirm', 'Are you sure?', function(r) {
            if (r) {
                var dg = $('#dg2');
                var row = dg.datagrid('getRows');
                var rowIndex = dg.datagrid('getRowIndex', row);

                var ed = dg.datagrid('getEditor', {
                    index: editIndex,
                    field: 'id'
                });

                $.ajax({
                    method: 'post',
                    url: '<?= base_url('warehouse/wip_receipts/deleteSingle') ?>',
                    data: {
                        id: row.id,
                    },
                    success: function(result) {
                        var result = eval('(' + result + ')');
                        toastr.success(result.message);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        toastr.error(jqXHR.statusText);
                        $.messager.alert("Error", jqXHR.statusText, 'error');
                    },
                    complete: function(data) {
                        $('#dg').datagrid('reload');
                    }
                });

                $('#dg2').datagrid('deleteRow', getRowIndex(target));
            }
        });
    }

    function saverow(target) {
        $('#dg2').datagrid('endEdit', getRowIndex(target));
    }

    function cancelrow(target) {
        $('#dg2').datagrid('cancelEdit', getRowIndex(target));
    }


    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_update').dialog('open');
            $('#frm_update').form('load', row);

            url_save = '<?= base_url('planning/item_ng/update') ?>?id=' + btoa(row.id);
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
                        console.log('Deleting row with id:', row.id);

                        // $.ajax({
                        //     type: "post",
                        //     url: "<?= base_url('closing/locks/checkLock') ?>",
                        //     data: "period=" + row.trans_date + "&menus_id=<?= $menus_id ?>",
                        //     dataType: "json",
                        //     success: function (lock) {
                        //         if(lock.total > 0){
                        //             toastr.error("This period is not active by Accounting");
                        //             return false;
                        //         }

                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('planning/item_ng/delete') ?>',
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
                        //     }
                        // });
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

        var filter_document = $("#filter_document").combobox('getValue');

        var filter_family_id = $("#filter_family_id").combobox('getValue');

        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');



        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_document=" + filter_document + "&filter_family_id" + filter_family_id + "&filter_item_fg_id=" + filter_item_fg_id;

        $('#dg').datagrid({

            url: '<?= base_url('planning/item_ng/datatables') ?>' + url

        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");

        $("#printout").attr('src', '<?= base_url('planning/item_ng/print') ?>' + url);

    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_document = $("#filter_document").combobox('getValue');
        var filter_family_id = $("#filter_family_id").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_document=" + filter_document + "&filter_family_id" + filter_family_id + "&filter_item_fg_id=" + filter_item_fg_id;

        window.location.assign('<?= base_url('planning/item_ng/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {

         //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('planning/item_ng/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.document + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                // var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('planning/item_ng/datatableDetails?document=') ?>' + window.btoa(row.document),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'item_rm_id',
                            title: 'Part ID',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_number',
                            title: 'Part No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_name',
                            title: 'Part Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'stock',
                            title: 'Qty',
                            halign: 'center',
                            width: 100
                        }, {
                            field: 'qty',
                            title: 'NG',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'scrap',
                            title: 'Scrap',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'balance',
                            title: 'Balance',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'uom',
                            title: 'Uom',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'remarks',
                            title: 'Remarks',
                            width: 200,
                            halign: 'center',
                        }]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        $('#type').combobox({
            url: '<?= base_url('planning/item_ng/getNgTypes') ?>',
            valueField: 'name',
            textField: 'name',
            prompt: 'Choose NG Type',
            multiple: true
        });

        $("#trans_date").datebox({
            onChange: function(val) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('planning/item_ng/item_ng_no/') ?>" + window.btoa(val),
                    dataType: "html",
                    success: function(scraps_no) {
                        $("#document").textbox('setValue', scraps_no);
                    }
                });

                $.ajax({
                    type: "post",
                    url: "<?= base_url('planning/scraps/scraps_no/') ?>" + window.btoa(val),
                    dataType: "html",
                    success: function(scraps_no) {
                        $("#document_scrap").textbox('setValue', scraps_no);
                    }
                });
            }
        });

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var trans_date = $("#trans_date").datebox('getValue');
                    var document = $("#document").textbox('getValue');
                    var document_scrap = $("#document_scrap").textbox('getValue');
                    var departement = $("#departement").textbox('getValue');
                    var process = $("#process").combogrid('getValue');
                    var type = $("#type").combobox('getText');
                    var workorder = $("#workorder").textbox('getValue');
                    var item_fg_id = $("#item_fg_id").textbox('getValue');
                    var qty_sh = $("#qty_sh").numberbox('getValue');
                    var qty_product = $("#qty_product").numberbox('getValue');
                    var accumulate_sh = $("#accumulate_sh").numberbox('getValue');
                    var balance_sh = $("#balance_sh").numberbox('getValue');
                    var shift = $("#shift").combobox('getValue');
                    var period = $("#period").textbox('getValue');

                    // $.ajax({
                    //     type: "post",
                    //     url: "<?= base_url('closing/locks/checkLock') ?>",
                    //     data: "period=" + trans_date + "&menus_id=<?= $menus_id ?>",
                    //     dataType: "json",
                    //     success: function (lock) {
                    //         if(lock.total > 0){
                    //             toastr.error("This period is not active by Accounting");
                    //             return false;
                    //         }

                    $('#dg2').datagrid('acceptChanges');
                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;

                    for (let i = 0; i < totalrows; i++) {
                        var stock = rows[i].stock || 0;
                        var qty = rows[i].qty || 0;
                        var scrap = rows[i].scrap || 0;
                        rows[i].balance = stock - qty - scrap; // Hitung balance dan simpan di row
                    }

                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_rm_id) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('planning/item_ng/create') ?>',
                                data: {
                                    trans_date: trans_date,
                                    document: document,
                                    document_scrap: document_scrap,
                                    departement: departement,
                                    process: process,
                                    type: type,
                                    workorder: workorder,
                                    item_fg_id: item_fg_id,
                                    qty_sh: qty_sh,
                                    qty_product: qty_product,
                                    accumulate_sh: accumulate_sh,
                                    balance_sh: balance_sh,
                                    shift: shift,
                                    period: period,
                                    item_rm_id: rows[i].item_rm_id,
                                    stock: rows[i].stock,
                                    qty: rows[i].qty,
                                    scrap: rows[i].scrap,
                                    balance: rows[i].balance,
                                    uom: rows[i].uom,
                                    remarks: rows[i].remarks
                                },
                                dataType: "json",
                                success: function(result) {
                                    Swal.fire({
                                        title: result.message,
                                        icon: result.theme,
                                        confirmButtonText: 'Ok',
                                        allowOutsideClick: false,
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.reload();
                                        }
                                    });
                                }
                            });
                        }
                    }
                    //     }
                    // });

                    $('#dlg_insert').dialog('close');
                }
            }]
        });

        $('#dlg_update').dialog({
            buttons: [{
                text: 'Update',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_update').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            if ($(this).form('validate') === true) {
                                $('#dlg_update').dialog('close');

                                Swal.fire({
                                    title: 'Please Wait...',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    },
                                });
                            } else {
                                return $(this).form('validate');
                            }
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            Swal.close();
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#qty').numberbox({
            onChange: function(val) {
                var stock = $('#stock').numberbox('getValue');
                var scrap = $('#scrap').numberbox('getValue');
                $("#balance").numberbox('setValue', parseInt(stock) - (parseInt(val) + parseInt(scrap)));
            }
        });

        $('#scrap').numberbox({
            onChange: function(val) {
                var stock = $('#stock').numberbox('getValue');
                var qty = $('#qty').numberbox('getValue');
                $("#balance").numberbox('setValue', parseInt(stock) - (parseInt(val) + parseInt(qty)));
            }
        });

        //Get Product Family
        $("#filter_family_id").combobox({
            url: '<?= base_url('master/item_familys/readNotFg') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Product Family",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_document").combobox({
            url: '<?= base_url('planning/item_ng/readDocument') ?>',
            valueField: 'document',
            textField: 'document',
            prompt: "Choose Document",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_item_fg_id').combogrid({
            url: '<?= base_url('master/item_fg/reads/') ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }]
            ],
        });

        $("#item_fg_number").combogrid({
            url: "<?= base_url('planning/item_ng/readWorkorders/') ?>",
            panelWidth: 550,
            idField: 'product_no',
            textField: 'product_no',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product No",
            columns: [
                [{
                    field: 'period',
                    title: 'Period',
                    width: 150
                }, {
                    field: 'lot_no',
                    title: 'Lot No',
                    width: 100,
                    align: 'left'
                }, {
                    field: 'wo_no',
                    title: 'Wo No',
                    width: 100,
                    align: 'left'
                }, {
                    field: 'product_no',
                    title: 'Product No',
                    width: 200,
                    align: 'left'
                }]
            ],
            onSelect: function(val, row) {
                // addTable(row.wo_no);
                $("#period").textbox('setValue', row.period);
                $("#item_fg_id").textbox('setValue', row.item_fg_id);
                $("#workorder").textbox('setValue', row.wo_no);
                $("#qty_sh").numberbox('setValue', row.qty);

                var wo_no = row.wo_no;
                var item_fg_id = row.item_fg_id

                $.ajax({
                    url: '<?= base_url("planning/item_ng/checkWo_no/") ?>' + window.btoa(wo_no) + '/' + window.btoa(item_fg_id), 
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);
                        accumulateAjax = data[0].qty;
                        $("#accumulate_sh").textbox('setValue', data[0].qty);
                    }
                });

                $('#qty_product').numberbox({
                    onChange: function(value) {
                        if(value != ""){
                            var qty = $("#qty_sh").numberbox("getValue");
                            var receipt = $("#qty_product").numberbox('getValue');

                            var calculate = parseInt(receipt) + parseInt(accumulateAjax);
                            var result = parseInt(qty) - parseInt(calculate);

                            var balance = $("#balance_sh").numberbox('setValue', result);
                            var accumulate_total = $("#accumulate_sh").numberbox('setValue', calculate);

                            // if (result < 0) {
                            //     toastr.warning("Balance minus, please correct your Qty!");
                            //     $("#qty_product").numberbox('setValue', 0);
                            //     $("#accumulate_sh").numberbox('setValue', accumulateAjax);
                            // } else {
                            //     return result;
                            // }
                        }else{
                            $("#qty_product").numberbox('setValue', 0);
                        }
                    }
                });
            }
        });

        $("#process").combogrid({
            url: '<?= base_url('master/item_process/reads') ?>',
            panelWidth: 400,
            idField: 'name',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Process",
            columns: [
                [{
                    field: 'id',
                    title: 'Process ID',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Process Name',
                    width: 250
                }]
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

    //Number Format Currency
    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function buttonEdit(value, row, index) {
        if (row.editing) {
            var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a> ';
            var c = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="cancelrow(this)">Cancel</a>';
            return s + c;
        } else {
            var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a> ';
            var d = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="deleterow(this)">Delete</a>';
            return e + d;
        }
    }
</script>
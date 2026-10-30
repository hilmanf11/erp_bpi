<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'request_no',halign:'center',width:190">Transaction No</th>
            <th rowspan="2" data-options="field:'request_id',width:150,halign:'center'">Transaction Id</th>
            <th rowspan="2" data-options="field:'status',width:120,align:'center',formatter:statusformat,styler:statusStyle">Status</th>
            <th rowspan="2" data-options="field:'request_date',width:120,halign:'center'">Transaction Date</th>
            <th rowspan="2" data-options="field:'request_no_return',width:120,halign:'center'">Transaction No <br> Return</th>
            <th rowspan="2" data-options="field:'request_name',width:120,halign:'center'">Requester</th>
            <th rowspan="2" data-options="field:'transaction_type',width:200,halign:'center',align:'center'">Type</th>
            <th rowspan="2" data-options="field:'transaction_kind',width:200,halign:'center',align:'center'">Kind</th>
            <!-- <th rowspan="2" data-options="field:'period',width:100,halign:'center'">Period</th>
            <th rowspan="2" data-options="field:'workorder',width:120,halign:'center'">Workorder</th> -->
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'specification',width:100,align:'center'">Specification</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatQpa">Qty</th>
            <th rowspan="2" data-options="field:'qty_mpq',width:80,halign:'center',align:'right'">Mpq</th>
            <th rowspan="2" data-options="field:'qty_label',width:80,halign:'center',align:'right'">Qty label</th>
            <th rowspan="2" data-options="field:'state',width:80,align:'center',formatter:BtnPrintLabel">Label</th>
            <th rowspan="2" data-options="field:'remarks',width:100,align:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'journal_no',width:140,halign:'center',align:'center'">GL INV No</th>
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

<div id="toolbar" style="height: 200px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" id="filter_period" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Workorder</span>
                    <input style="width:60%;" id="filter_workorder" class="easyui-combobox">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction No</span>
                    <input style="width:60%;" id="filter_request_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                  <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Date</span>
                    <input style="width:30%;" id="filter_kanban_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Date</span>
                    <input style="width:29%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:27%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Type</span>
                    <input style="width:60%;" id="filter_transaction_type" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <?= $button ?>

    </div>
</div>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>
<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1100px; height: 100%; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Date</span>
                    <input style="width:60%;" name="request_date" id="request_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Cut Off</span>
                    <input style="width:60%;" id="cutoff" class="easyui-datebox" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Type</span>
                    <input style="width:60%;" name="transaction_id" id="transaction_id" class="easyui-combobox" data-options="onChange: checkTransactionType">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Kind</span>
                    <input style="width:60%;" name="transaction_kind" id="transaction_kind" class="easyui-textbox" readonly="" prompt="auto">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sub Kind</span>
                    <input style="width:60%;" name="transaction_sub_kind" id="transaction_sub_kind" class="easyui-textbox" readonly="" data-options="onChange: checkSubKind" prompt="auto">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Kind Label</span>
                    <input style="width:60%;" name="transaction_kind_label" id="transaction_kind_label" class="easyui-textbox" readonly="" prompt="auto">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Transaction ID</span>
                    <input style="width:60%;" name="transaction_type" id="transaction_type" class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Name</span>
                    <input style="width:60%;" name="request_name" id="request_name" value="<?= $this->session->name ?>" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" class="easyui-combogrid" data-options="onChange: filterSupplierData">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction No</span>
                    <input style="width:60%;" name="request_no" id="request_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction No Return</span>
                    <input style="width:60%;" name="request_no_return" id="request_no_return" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">POR No</span>
                    <input style="width:60%;" name="purchase_order_receipt" id="purchase_order_receipt" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="attachment" id="attachment" class="easyui-filebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Division/Category</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" name="division" id="division" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" name="item_category_id" id="item_category_id" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Transaction RM" toolbar="#toolbar2" data-options="singleSelect: true">
        </table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('warehouse/transaction_rm/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    var is_updating = false;

    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        request_no();

        $("#request_date").datebox('enable');
        $("#request_no").textbox('enable');
        // $("#period").combobox({
        //     url: '<?= base_url('planning/production_schedules/readPeriodAll') ?>',
        //     valueField: 'period',
        //     textField: 'period',
        //     prompt: "Choose Period",
        //     onSelect: function(rowPeriod) {
        //         $("#workorder").combobox({
        //             url: '<?= base_url('planning/production_schedules/readWpAll?period=') ?>' + window.btoa(rowPeriod.period),
        //             valueField: 'wo_no',
        //             textField: 'wo_no',
        //             prompt: "Choose Workorder",
        //             onSelect: function(rowWP) {
        //                 $("#item_fg_id").textbox("setValue", rowWP.item_fg_id);
        //                 $("#item_fg_number").textbox("setValue", rowWP.item_fg_number);
        //             }
        //         });
        //     }
        // });
    }

    function request_no(reqDate = "") {
        if (reqDate == "") {
            var request_date = $("#request_date").datebox('getValue');
        } else {
            var request_date = reqDate;
        }
        $.ajax({
            type: "post",
            url: "<?= base_url('warehouse/transaction_rm/request_no') ?>/" + window.btoa(request_date),
            dataType: "html",
            success: function(result) {
                $("#request_no").textbox('setValue', result);
            }
        });
    }
    //INSERT ADD ROW
    function addTable(url = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            url: url,
            columns: [
                [{
                    field: 'item_number',
                    width: 250,
                    halign: 'center',
                    title: "Part No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('warehouse/transaction_rm/readItemRm') ?>',
                            required: true,
                            panelWidth: 400,
                            idField: 'number',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Part No',
                            onBeforeLoad: function(param) {
                                param.division = $('#division').combobox('getValue');
                                param.item_category_id = $('#item_category_id').combobox('getValue');
                            },
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Part No',
                                    width: 100
                                }, {
                                    field: 'name',
                                    title: 'Part Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var rowIndex = editIndex; 
                                
                                if (rowIndex === undefined) return;
                                
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_id'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_name'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'stock'
                                });
                                var edSpec = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'specification'
                                });

                                var item_rm_id = $(ed.target).textbox('setValue', rows.id);
                                var item_name = $(ed2.target).textbox('setValue', rows.name);
                                var uom = $(ed3.target).textbox('setValue', rows.uom);
                                var cutoffDate = $('#cutoff').datebox('getValue');
                                var trans_type = $('#transaction_type').textbox('getValue');

                                $.ajax({
                                    type: "post",
                                    url: "<?= base_url('warehouse/report_history_transactions/readEndingStock') ?>",
                                    data: "item_rm_id=" + rows.id,
                                    dataType: "json",
                                    success: function(stockWarehouse) {
                                        $(ed4.target).numberbox('setValue', stockWarehouse[0].end_stock);
                                    }
                                });

                                if (edSpec) {
                                    var combo = $(edSpec.target);
                                    combo.combobox('clear');

                                    $.ajax({
                                        type: "POST",
                                        url: "<?= base_url('warehouse/transaction_rm/readSpec') ?>",
                                        data: { item_rm_id: rows.id,
                                                trans_date: cutoffDate,
                                                trans_type: trans_type
                                         },
                                        dataType: "json",
                                        success: function (response) {
                                            combo.combobox('loadData', response);
                                        }
                                    });
                                }
                            }
                        }
                    }
                }, {
                    field: 'item_rm_id',
                    width: 150,
                    hidden: true,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'item_name',
                    width: 150,
                    halign: 'center',
                    title: "Part Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'specification',
                    width: 200,
                    halign: 'center',
                    title: "Specification",
                    editor: {
                        type: 'combobox',
                        options: {
                            valueField: 'specification',
                            textField: 'specification',
                            mode: 'remote',
                            prompt: 'Choose Specification'
                        }
                    }
                }, {
                    field: 'po_no',
                    width: 100,
                    hidden: true,
                    title: "PO No",
                    editor: { type: 'textbox' }
               }, {
                    field: 'max_qty',
                    width: 100, 
                    halign: 'center',
                    hidden: true,
                    align: 'right', 
                    title: "Outstanding", 
                    editor: { 
                        type: 'numberbox', 
                        options: { 
                            precision: 4,
                            readonly: true 
                        } 
                    }
                }, {
                    field: 'mpq',
                    width: 80,
                    halign: 'center',
                    align: 'right',
                    title: "MPQ",
                    editor: {
                        type: 'numberbox',
                        options: { readonly: true, precision: 2 }
                    }
                }, {
                    field: 'qty',
                    width: 80,
                    halign: 'right',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 4,
                            onChange: function(newValue, oldValue) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                if (!row) return;
                                
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var edQty = dg.datagrid('getEditor', { index: rowIndex, field: 'qty' });
                                var edLabel = dg.datagrid('getEditor', { index: rowIndex, field: 'qty_label' });
                                
                                var maxQty = parseFloat(row.max_qty) || 0;
                                var mpq = parseFloat(row.mpq) || 0;
                                var currentQty = parseFloat(newValue) || 0;
                                
                                var transText = $('#transaction_id').combobox('getText').toUpperCase();

                                if (edQty && edLabel) {
                                    
                                    var isRestrictedTrans = (transText === 'RETURN NG TO SUPPLIER' || transText === 'REPLACEMENT NG FROM SUPPLIER');

                                    if (isRestrictedTrans && currentQty > maxQty) {
                                        toastr.warning("Qty cannot exceed the limit (" + maxQty + ")", "Warning");
                                        $(edQty.target).numberbox('setValue', maxQty); 
                                        currentQty = maxQty; 
                                    }
                                    
                                    if (transText === 'RETURN NG TO SUPPLIER') {
                                        $(edLabel.target).numberbox('setValue', 0);
                                    } else {
                                        if (mpq > 0 && currentQty > 0) {
                                            var labels = Math.ceil(currentQty / mpq);
                                            $(edLabel.target).numberbox('setValue', labels);
                                        } else {
                                            $(edLabel.target).numberbox('setValue', 0);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }, {
                    field: 'qty_label',
                    width: 80,
                    halign: 'center',
                    align: 'center',
                    title: "Total Label",
                    editor: {
                        type: 'numberbox',
                        options: {readonly: true } 
                    }
                }, {
                    field: 'uom',
                    width: 80,
                    align: 'center',
                    title: "Uom",
                    editor: {
                        type: 'textbox',
                        options: { readonly: true }
                    }
                }]
            ],
            onClickRow: function(index) {
                if (editIndex != index) {
                    if (endEditing()) {
                        $(this).datagrid('selectRow', index).datagrid('beginEdit', index);
                        editIndex = index;
                    } else {
                        $(this).datagrid('selectRow', editIndex); 
                    }
                }
            },
        });
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
                qty: '0'
            });
            editIndex = $('#dg2').datagrid('getRows').length - 1;
            $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
        }
    }

    function removeit() {
        if (endEditing()) {
            var row = $('#dg2').datagrid('getSelected'); // Dapatkan baris yang dipilih
            if (row) {
                var rowIndex = $('#dg2').datagrid('getRowIndex', row); // Dapatkan index baris
                $('#dg2').datagrid('deleteRow', rowIndex); // Hapus baris yang dipilih
            }
            editIndex = undefined; // Reset editIndex
        }
    }

    //Update Data
    // function update() {
    //     var row = $('#dg').treegrid('getSelected');
    //     if (row) {
    //         if(row.state == "closed"){
    //             $('#dlg_insert').dialog('open');
    //             $('#frm_insert').form('load', row);
    //             $("#request_date").datebox('disable');
    //             $("#request_no").textbox('disable');
                
    //             setTimeout(function() {
    //                 $("#request_no").textbox('setValue', row.request_no);
    //             }, 1000);
                
    //             $("#transaction_id").combobox({
    //                 url: '<?= base_url('warehouse/transaction_rm/readType/') ?>',
    //                 valueField: 'id',
    //                 textField: 'type',
    //                 prompt: "Choose Type",
    //                 onLoadSuccess: function(data){
    //                     if (data.length > 0) {
                         
    //                         $("#transaction_id").combobox('setValue', row.transaction_id);
    //                     }
    //                 },
    //                 onSelect: function(record) {
    //                     $("#transaction_type").textbox('setValue', record.type);
    //                     $("#transaction_kind").textbox('setValue', record.kind);
    //                 }
    //             });

    //             addTable('<?= base_url('warehouse/transaction_rm/datatableUpdate/') ?>' + window.btoa(row.request_no));
    //         }else{
    //             toastr.warning("Please Select Header of Table", "Information");
    //         }
    //     } else {
    //         toastr.warning("Please select one of the data in the table first!", "Information");
    //     }
    // }

    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if(row.state == "closed"){
                is_updating = true;
                $('#dlg_insert').dialog('open');
                
                if (row.purchase_order_receipt) {
                    $('#purchase_order_receipt').combobox('loadData', [{receipt_no: row.purchase_order_receipt}]);
                }
                if (row.request_no_return) {
                    $('#request_no_return').combobox('loadData', [{request_no: row.request_no_return}]);
                }

                $('#frm_insert').form('load', row);
                
                $("#request_date").datebox('disable');
                $("#request_no").textbox('disable');
                
                $("#transaction_id").combobox({
                    url: '<?= base_url('warehouse/transaction_rm/readType/') ?>',
                    valueField: 'id',
                    textField: 'type',
                    prompt: "Choose Type",
                    onLoadSuccess: function(data){
                        if (data.length > 0) {
                            $("#transaction_id").combobox('setValue', row.transaction_id);
                        }
                    },
                    onSelect: function(record) {
                        $("#transaction_type").textbox('setValue', record.type);
                        $("#transaction_kind").textbox('setValue', record.kind);
                    }
                });

                addTable('<?= base_url('warehouse/transaction_rm/datatableUpdate/') ?>' + window.btoa(row.request_no));

                setTimeout(function() {
                    $("#request_no").textbox('setValue', row.request_no);
                    
                    if (row.purchase_order_receipt && row.purchase_order_receipt !== "") {
                        $('#purchase_order_receipt').combobox('setValue', row.purchase_order_receipt);
                    }
                    if (row.request_no_return && row.request_no_return !== "") {
                        $('#request_no_return').combobox('setValue', row.request_no_return);
                    }
                    if (row.division && row.division !== "") {
                        $('#division').combobox('setValue', row.division);
                    }
                    if (row.item_category_id && row.item_category_id !== "") {
                        $('#item_category_id').combobox('setValue', row.item_category_id);
                    }
                    
                    is_updating = false; 
                }, 800);
                
            } else {
                toastr.warning("Please Select Header of Table", "Information");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (let i = 0; i < rows.length; i++) {
                        let row = rows[i];
                        
                        if (row.status == 1) {
                            toastr.warning("Transaction " + row.request_no + " is already closed and cannot be deleted.", "Warning");
                            continue; 
                        }

                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('warehouse/transaction_rm/delete') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(response) {
                                try {
                                    var result = JSON.parse(response.trim());
                                    
                                    if (result.theme == "success") {
                                        toastr.success(result.message, result.title || "Success");
                                    } else {
                                        toastr.error(result.message, result.title || "Error");
                                    }
                                } catch (e) {
                                    console.error("Error parsing response:", e);
                                }
                                $('#dg').treegrid('reload');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText, "System Error");
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
        // var filter_period = $("#filter_period").combobox('getValue');
        // var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        // var filter_kanban_date = $("#filter_kanban_date").datebox('getValue');
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_transaction_type = $("#filter_transaction_type").combobox('getValue');

        url = "?&filter_request_no=" + filter_request_no + "&filter_product_no=" + btoa(filter_product_no) + "&filter_product_family=" + filter_product_family + "&filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_transaction_type=" + filter_transaction_type;

        $('#dg').treegrid({
            url: '<?= base_url('warehouse/transaction_rm/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/transaction_rm/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        // var filter_period = $("#filter_period").combobox('getValue');
        // var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        // var filter_kanban_date = $("#filter_kanban_date").datebox('getValue');
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_transaction_type = $("#filter_transaction_type").combobox('getValue');

        url = "?&filter_request_no=" + filter_request_no + "&filter_product_no=" + btoa(filter_product_no) + "&filter_product_family=" + filter_product_family + "&filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_transaction_type=" + filter_transaction_type;
        window.location.assign('<?= base_url('warehouse/transaction_rm/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        addTable();
        $('#dg').treegrid({
            url: '<?= base_url('warehouse/transaction_rm/datatables') ?>',
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'request_no',
            singleSelect: false,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
            // onClickRow: function(index) {
            //     if (index != 1) {
            //         $(this).datagrid('unselectRow', index).datagrid('selectRow', 1);
            //     }
            // }
            // rowStyler: function(row) {
            //     if (row.state != "closed") {
            //         return 'background-color:#CFE6FF;font-weight:bold;';
            //     }
            // },
        });

        //Save Data
        // $('#dlg_insert').dialog({
        //     buttons: [{
        //         text: 'Save All',
        //         iconCls: 'icon-ok',
        //         handler: function() {
        //             var request_no = $("#request_no").textbox('getValue');
        //             var request_date = $("#request_date").datebox('getValue');
        //             var request_name = $("#request_name").textbox('getValue');
        //             var transaction_type = $("#transaction_type").textbox('getValue');
        //             var transaction_kind = $("#transaction_kind").textbox('getValue');
        //             var transaction_id = $("#transaction_id").combobox('getValue');
        //             var remarks = $("#remarks").textbox('getValue');
        //             // var item_fg_id = $("#item_fg_id").textbox('getValue');
        //             // var item_fg_number = $("#item_fg_number").textbox('getValue');
        //             // var workorder = $("#workorder").textbox('getValue');
        //             // var period = $("#period").combobox('getValue');
        //             // var wp = $("#wp").combogrid('getValue');

        //             // if (period == "" || item_fg_id == "" || totalrows <= 0) {
        //             if (totalrows <= 0) {
        //                 toastr.error("please complete your input data");
        //             } else {

        //                 $("#dg2").datagrid('acceptChanges');
        //                 var rows = $('#dg2').datagrid('getRows');
        //                 var totalrows = rows.length;
        //                 endEditing();

        //                 for (let i = 0; i < totalrows; i++) {
        //                     if (rows[i].item_rm_id) {
        //                         $.ajax({
        //                             type: "post",
        //                             url: '<?= base_url('warehouse/transaction_rm/create') ?>',
        //                             data: {
        //                                 // item_fg_id: item_fg_id,
        //                                 // item_fg_number: item_fg_number,
        //                                 request_date: request_date,
        //                                 request_no: request_no,
        //                                 request_name: request_name,
        //                                 transaction_type: transaction_type,
        //                                 transaction_id: transaction_id,
        //                                 transaction_kind: transaction_kind,
        //                                 remarks: remarks,
        //                                 // period: period,
        //                                 // wp: wp,
        //                                 // workorder: workorder,
        //                                 item_rm_id: rows[i].item_rm_id,
        //                                 specification: rows[i].specification,
        //                                 qty: rows[i].qty
        //                             },
        //                             dataType: "json",
        //                             success: function(result) {
        //                                 if (result.theme == "error") {
        //                                     toastr.warning(result.message, "Error");
        //                                 }
        //                             }
        //                         });
        //                     }
        //                 }

        //                 // Validate Auto Posting Journal Inventory
        //                 exec_autoposting(transaction_type, request_no);

        //                 Swal.fire({
        //                     title: "Data Saved Successfully",
        //                     icon: "success",
        //                     confirmButtonText: 'Ok',
        //                     allowOutsideClick: false,
        //                 }).then((result) => {
        //                     if (result.isConfirmed) {
        //                         window.location.reload();
        //                     }
        //                 });
        //                 $('#dg').treegrid('reload');
        //                 $('#dlg_insert').dialog('close');
        //             }
        //         }
        //     }]
        // });

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    // Ambil semua data form
                    var request_no = $("#request_no").textbox('getValue');
                    var request_date = $("#request_date").datebox('getValue');
                    var request_name = $("#request_name").textbox('getValue');
                    var transaction_id = $("#transaction_id").combobox('getValue');
                    var transaction_type = $("#transaction_id").combobox('getText').toUpperCase(); // Ambil teks (RETURN/REPLACEMENT)
                    var transaction_kind = $("#transaction_kind").textbox('getValue');
                    var transaction_sub_kind = $("#transaction_sub_kind").textbox('getValue');
                    var transaction_kind_label = $("#transaction_kind_label").textbox('getValue');
                    var cutoff = $("#cutoff").datebox('getValue');
                    var supplier_id = $('#supplier_id').combogrid('getValue');
                    var request_no_return = $('#request_no_return').combobox('getValue');
                    var purchase_order_receipt = $('#purchase_order_receipt').combobox('getValue');
                    var division = $('#division').combobox('getValue');
                    var item_category_id = $('#item_category_id').combobox('getValue');
                    var remarks = $("#remarks").textbox('getValue');
                    
                    var fileAttachment = $('#attachment').filebox('files')[0];

                    $("#dg2").datagrid('acceptChanges');
                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    if (totalrows <= 0) {
                        toastr.error("Please complete your input data", "Error");
                        return;
                    }

                    var hasProcessedData = false;
                    var saveRequests = []; // ditunggu semua selesai sebelum Auto Posting Journal Inventory

                    // Looping baris tabel
                    for (let i = 0; i < totalrows; i++) {
                        
                        // FILTER: HANYA PROSES JIKA QTY LEBIH DARI 0
                        if (rows[i].item_rm_id && parseFloat(rows[i].qty) > 0) {
                            hasProcessedData = true;

                            var formData = new FormData();
                            formData.append('request_date', request_date);
                            formData.append('request_no', request_no);
                            formData.append('request_name', request_name);
                            formData.append('transaction_id', transaction_id);
                            formData.append('transaction_type', transaction_type);
                            formData.append('transaction_kind', transaction_kind);
                            formData.append('transaction_sub_kind', transaction_sub_kind);
                            formData.append('transaction_kind_label', transaction_kind_label);
                            formData.append('supplier_id', supplier_id);
                            formData.append('request_no_return', request_no_return);
                            formData.append('purchase_order_receipt', purchase_order_receipt);
                            formData.append('division', division);
                            formData.append('item_category_id', item_category_id);
                            formData.append('remarks', remarks);

                            // Data dari Datagrid dg2
                            formData.append('item_rm_id', rows[i].item_rm_id);
                            formData.append('specification', rows[i].specification);
                            formData.append('po_no', rows[i].po_no || '');
                            formData.append('lotno', rows[i].lotno || '');
                            formData.append('qty_mpq', rows[i].mpq || 0);
                            formData.append('max_qty', rows[i].max_qty || 0);
                            formData.append('qty_label', rows[i].qty_label || 0);
                            formData.append('qty', rows[i].qty);

                            // Append file jika user memilih file
                            if (fileAttachment) {
                                formData.append('attachment', fileAttachment);
                            }

                            // Eksekusi AJAX (Satu per satu)
                            // Deferred sendiri: selesai (sukses/gagal) baru dihitung, supaya $.when tidak berhenti di request gagal pertama
                            const saveDone = $.Deferred();
                            saveRequests.push(saveDone.promise());
                            $.ajax({
                                type: "POST",
                                url: '<?= base_url('warehouse/transaction_rm/create') ?>',
                                data: formData,
                                processData: false, // Wajib false untuk FormData
                                contentType: false, // Wajib false untuk FormData
                                dataType: "json",
                                success: function(result) {
                                    if (result && result.theme == "error") {
                                        toastr.warning(result.message, "Error");
                                    }
                                },
                                complete: function() { saveDone.resolve(); }
                            });
                        }
                    }

                    if (!hasProcessedData) {
                        toastr.warning("No items to process. Ensure Qty > 0.", "Warning");
                        return;
                    }

                    Swal.fire({
                        html: 'Saving data & Auto Posting Journal Inventory...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    // Semua baris tersimpan -> Auto Posting Journal Inventory (gagal tercatat di Failed Posting)
                    $.when.apply($, saveRequests).always(function() {
                        $.ajax({
                            type: "POST",
                            url: '<?= base_url('warehouse/transaction_rm/autoPostJournal') ?>',
                            data: { request_no: request_no },
                            dataType: "json",
                            complete: function(xhr) {
                                var res = xhr.responseJSON || {};
                                if (res.status === true) {
                                    toastr.success(res.message, "Journal Inventory");
                                } else if (res.status === false) {
                                    toastr.warning(res.message, "Journal Inventory (Failed Posting)");
                                }
                                showSavedDialog();
                            }
                        });
                    });

                    function showSavedDialog() {
                    if (transaction_type === 'REPLACEMENT NG FROM SUPPLIER') {
                        Swal.fire({
                            title: "Data Saved Successfully",
                            text: "Do you want to print the barcode?",
                            icon: "success",
                            showDenyButton: true,
                            confirmButtonText: "Yes, Print",
                            denyButtonText: "No, Close",
                            allowOutsideClick: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var encodedRequestNo = window.btoa(request_no);
                                var url = '<?= base_url('warehouse/transaction_rm/print_label/') ?>' + encodedRequestNo;
                                window.open(url, '_blank');
                                window.location.reload();
                            } else {
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: "Data Saved Successfully",
                            icon: "success",
                            confirmButtonText: 'Ok',
                            allowOutsideClick: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    }
                    }

                    $('#dg').treegrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });

        // $('#item_fg_id').combogrid({
        //     url: '<?= base_url('master/item_fg/reads/001') ?>',
        //     panelWidth: 420,
        //     idField: 'id',
        //     textField: 'number',
        //     mode: 'remote',
        //     fitColumns: true,
        //     prompt: "Choose Product",
        //     columns: [
        //         [{
        //             field: 'number',
        //             title: 'Product No',
        //             width: 100
        //         }, {
        //             field: 'name',
        //             title: 'Product Name',
        //             width: 200
        //         }, ]
        //     ]
        // });

        $("#filter_period").combobox({
            url: '<?= base_url('warehouse/transaction_rm/readPeriod') ?>',
            valueField: 'period',
            textField: 'period',
            prompt: "Choose period",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(period) {
                $("#filter_workorder").combobox({
                    url: '<?= base_url('warehouse/transaction_rm/readWp/') ?>' + period.period,
                    valueField: 'workorder',
                    textField: 'workorder',
                    prompt: "Choose Workorder",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(wp) {
                        $("#filter_request_no").combobox({
                            url: '<?= base_url('warehouse/transaction_rm/readRequestNo/') ?>' + period.period + '/' + window.btoa(wp.workorder),
                            valueField: 'request_no',
                            textField: 'request_no',
                            prompt: "Choose Request No",
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }],
                        });
                    }
                });
            }
        });

        $("#filter_request_no").combobox({
            url: '<?= base_url('warehouse/transaction_rm/readRequestNos/') ?>',
            valueField: 'request_no',
            textField: 'request_no',
            prompt: "Choose Request No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_product_family").combobox({
            url: '<?= base_url('master/item_familys/readNotFg/') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Product Family",
            onSelect: function(prodfam){
                $('#filter_product_no').combogrid({
                    url: '<?= base_url('warehouse/transaction_rm/readProduct/') ?>' + prodfam.id,
                    panelWidth: 420,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Product",
                    columns: [
                        [{
                            field: 'number',
                            title: 'Product No',
                            width: 100
                        }, {
                            field: 'name',
                            title: 'Product Name',
                            width: 200
                        }, ]
                    ]
                });
            }
        });

        $('#filter_product_no').combogrid({
            url: '<?= base_url('warehouse/transaction_rm/readProducts/') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product",
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }, ]
            ]
        });

        $("#request_date").datebox({
            onChange: function(val) {
                request_no(val);
            }
        });
    });

    $("#filter_transaction_type").combobox({
        url: '<?= base_url('warehouse/transaction_rm/readType/') ?>',
        valueField: 'type',
        textField: 'type',
        prompt: "Choose Type",
    });

    $("#transaction_id").combobox({
        url: '<?= base_url('warehouse/transaction_rm/readType/') ?>',
        valueField: 'id',
        textField: 'type',
        prompt: "Choose Type",
        onSelect: function(record){
            $("#transaction_type").textbox('setValue', record.type);
            $("#transaction_kind").textbox('setValue', record.kind);
            $("#transaction_sub_kind").textbox('setValue', record.sub_kind);
            $("#transaction_kind_label").textbox('setValue', record.kind_label);
        }
    });

    $('#supplier_id').combogrid({
        url: '<?= base_url('master/suppliers/reads'); ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Supplier",
        columns: [
            [{
                field: 'name',
                title: 'Supplier Name',
                width: 200
            }, {
                field: 'type',
                title: 'Type',
                width: 100
            }, {
                field: 'currency',
                title: 'Currency',
                width: 100
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $("#purchase_order_receipt").combobox({
        valueField: 'receipt_no',
        textField: 'receipt_no',
        prompt: "Choose Purchase Order Receipt",

        onSelect: function(record) {
            if (is_updating) return;
            var transText = $('#transaction_id').combobox('getText').toUpperCase();
            
            // JIKA RETURN: Tarik data baris tabel dari tabel POR
            if (transText === 'RETURN NG TO SUPPLIER') {
                $('#dg2').datagrid('loadData', {"total":0,"rows":[]});
                
                $('#division').combobox('clear');
                $('#item_category_id').combobox('clear');
                
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('warehouse/transaction_rm/readPorItems') ?>", 
                    data: { 
                        receipt_no: record.receipt_no,
                        trans_type: transText 
                    },
                    dataType: "json",
                    success: function(response) {
                        $('#dg2').datagrid('loadData', response);
                        
                        if (response && response.length > 0) {
                            var firstRow = response[0];
                            
                            if (firstRow.division) {
                                $('#division').combobox('setValue', firstRow.division);
                            }
                            if (firstRow.item_category_id) {
                                $('#item_category_id').combobox('setValue', firstRow.item_category_id);
                            }
                        }
                    }
                });
            }
        },
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
                
                var transText = $('#transaction_id').combobox('getText').toUpperCase();
                if (transText === 'RETURN NG TO SUPPLIER') {
                    $('#dg2').datagrid('loadData', {"total":0,"rows":[]}); 
                    
                    $('#division').combobox('clear');
                    $('#item_category_id').combobox('clear');
                }
            }
        }],
    });

    $("#request_no_return").combobox({
        valueField: 'request_no',
        textField: 'request_no',
        prompt: "Choose Return Transaction",
        onSelect: function(record) {
            if (is_updating) return;
            if(record.purchase_order_receipt) {
                $('#purchase_order_receipt').combobox('loadData', [{receipt_no: record.purchase_order_receipt}]);
                $('#purchase_order_receipt').combobox('setValue', record.purchase_order_receipt);
            }
            
            var transText = $('#transaction_id').combobox('getText').toUpperCase();
            
            // JIKA REPLACEMENT: Tarik data baris tabel menggunakan nomor retur
            if (transText === 'REPLACEMENT NG FROM SUPPLIER') {
                $('#dg2').datagrid('loadData', {"total":0,"rows":[]});
                
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('warehouse/transaction_rm/readPorItems') ?>", 
                    data: { 
                        request_no_return: record.request_no,
                        trans_type: transText
                    },
                    dataType: "json",
                    success: function(response) {
                        $('#dg2').datagrid('loadData', response);
                    }
                });
            }
        },
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) { 
                $(e.data.target).combobox('clear').combobox('textbox').focus(); 
                $('#purchase_order_receipt').combobox('clear');
                
                var transText = $('#transaction_id').combobox('getText').toUpperCase();
                if (transText === 'REPLACEMENT NG FROM SUPPLIER') {
                    $('#dg2').datagrid('loadData', {"total":0,"rows":[]}); 
                }
            }
        }],
    });

    $('#division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'number',
        textField: 'number',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
    }); 

    $('#item_category_id').combobox({
        url:'<?= base_url('master/item_rm/readsnotfg'); ?>',
        valueField:'id',
        textField:'name',
        prompt: 'Choose Category',
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

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatQpa(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        }
    }

    function BtnPrintLabel(val, row) {
        var transType = (row.transaction_kind_label || row.transaction_type || "").toUpperCase();
        
        if (val != "closed" && transType === 'REPLACEMENT NG FROM SUPPLIER') {
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onclick="printConfirmation(\'' + row.request_id + '\')"><i class="fa fa-print"></i> Print</a>';
        } else {
            return ''; 
        }
    }

    function printConfirmation(request_id) {
        swal.fire({
            title: 'Confirmation',
            text: 'Are you sure want print this Label',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'YES',
            cancelButtonText: 'CANCEL'
        }).then((result) => {
            if (result.isConfirmed) {
                window.open('<?= base_url('warehouse/transaction_rm/print_label_grid/') ?>' + window.btoa(request_id), '_blank');
            } else {
                window.location.reload();
            }
        });
    }

    function cellbutton(value) {
        if (value != null) {
            return '<a target="_blank" href="' + value + '" class="btn btn-primary btn-sm" style="pointer-events: auto; opacity:1; width:100%;"><i class="fa fa-eye"></i> View</a>';
            // alert(value);
        }
    };

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

    function statusformatFinance(value, row) {
        if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        } else {
            return "<b style='color:green;'>OPEN</b>";
        }
    }

    function statusStyleFinance(value, row, index) {
        if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function checkSubKind(newValue, oldValue) {
        if (newValue && newValue.toLowerCase() === 'supplier') {
            $('#supplier_id').combogrid('enable');
        } else {
            $('#supplier_id').combogrid('clear');
            $('#supplier_id').combogrid('disable');
        }
    }

    function checkTransactionType(newValue, oldValue) {
        var transText = $('#transaction_id').combobox('getText').toUpperCase();
        
        $('#purchase_order_receipt').combobox('clear').combobox('disable');
        $('#request_no_return').combobox('clear').combobox('disable');
        
        if (transText === 'RETURN NG TO SUPPLIER') {
            $('#purchase_order_receipt').combobox('enable');
        } 
        else if (transText === 'REPLACEMENT NG FROM SUPPLIER') {
            $('#request_no_return').combobox('enable');
            $('#purchase_order_receipt').combobox('readonly', true);
        }
    }

    function filterSupplierData(newValue, oldValue) {
        $('#purchase_order_receipt').combobox('clear');
        $('#request_no_return').combobox('clear');
        
        if (newValue) {
            var url_por = '<?= base_url('warehouse/transaction_rm/readReceipts') ?>?supplier_id=' + newValue;
            $('#purchase_order_receipt').combobox('reload', url_por);
            
            var url_return = '<?= base_url('warehouse/transaction_rm/readReturnTransactions') ?>?supplier_id=' + newValue;
            $('#request_no_return').combobox('reload', url_return);
        } else {
            $('#purchase_order_receipt').combobox('loadData', []);
            $('#request_no_return').combobox('loadData', []);
        }
    }

    /** --- Auto Posting Journal Inventory --- */

    // Helper untuk eksekusi Auto Posting Journal Inventory
    function exec_autoposting(transaction_type, document_no) {
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/journal_inventory/execute_auto_journal/') ?>",
            data: { 
                modul: transaction_type,
                document_no: document_no,
            },
            dataType: "json",
            success: function(response) {
                Swal.close();
                if (response.status === true) { 
                    toastr.success(response.message || "Success", "Auto Posting Journal Success");
                } else {
                    toastr.info(response.message, "Failed to Auto Posting Journal");
                }
            },
            error: function(xhr) {
                Swal.close();
                toastr.error("Failed to connect to Auto Posting server");
            }
        });
    }
</script>
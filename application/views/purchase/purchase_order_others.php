<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'po_no',width:200,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status PO</th>
            <th rowspan="2" data-options="field:'po_date',width:100,align:'center'">PO Date</th>
            <th rowspan="2" data-options="field:'delivery_date',width:100,align:'center'">Delivery Date</th>
            <th rowspan="2" data-options="field:'supplier_name',width:200,halign:'center'">Supplier</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'mpq',width:80,halign:'center',align:'right',formatter:numberformat">MPQ</th>
            <th rowspan="2" data-options="field:'moq',width:80,halign:'center',align:'right',formatter:numberformat">MOQ</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformat">Price</th>
            <th rowspan="2" data-options="field:'total',width:120,halign:'center',align:'right',formatter:numberformat">Total Price</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'revision',width:80,align:'center'">Revision</th>
            <th rowspan="2" data-options="field:'remarks',width:100,halign:'center'">Remarks</th>
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

<div id="toolbar" style="height: 220px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 35%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier</span>
                <input style="width:60%;" id="filter_supplier_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Po No</span>
                <input style="width:60%;" id="filter_po_no" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_po()"><i class="fa fa-print"></i> Purchase Order</a>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1200px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" name="po_no" id="po_no" readonly class="easyui-textbox" data-options="prompt: 'Automatic'" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO Date</span>
                    <input style="width:60%;" name="po_date" id="po_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO Name</span>
                    <input style="width:60%;" name="po_name" id="po_name" value="<?= $this->session->name ?>" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" required class="easyui-combobox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Tax</span>
                    <input style="width:60%;" name="taxes" id="taxes" class="easyui-numberbox" data-options="precision: 2">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Purchase Order Misc List" toolbar="#toolbar2"></table>

        <div style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: right; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <div style="width: 100%; float: left;">
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">SUB TOTAL</b>
                        <input style="width:60%;" name="total_sub" id="total_sub" readonly class="easyui-numberbox" value="0" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">VAT</b>
                        <input style="width:60%;" name="total_vat" id="total_vat" readonly class="easyui-numberbox" value="0" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">TOTAL AMOUNT</b>
                        <input style="width:60%;" name="total_amount" id="total_amount" readonly class="easyui-numberbox" value="0" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">DOWN PAYMENT</b>
                        <input style="width:60%;" name="total_dp" id="total_dp" required class="easyui-numberbox" value="0" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">GRAND TOTAL</b>
                        <input style="width:60%;" name="total_grand" id="total_grand" readonly class="easyui-numberbox" value="0" data-options="precision:2,groupSeparator:','">
                    </div>
                </div>
            </fieldset>
        </div>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('purchase/purchase_order_others/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $("#supplier_id").combobox('enable');
        $("#supplier_id").combobox('clear');
    }

    function addTable(supplier_id, link = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'item_number',
                    width: 250,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('master/supplier_items/readItems?supplier_id=') ?>' + supplier_id,
                            required: true,
                            panelWidth: 800,
                            idField: 'number',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product',
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Product No',
                                    width: 150
                                }, {
                                    field: 'name',
                                    title: 'Product Name',
                                    width: 150
                                }, {
                                    field: 'description',
                                    title: 'Specification',
                                    width: 300
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_id'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_name'
                                });

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'mpq'
                                });

                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'moq'
                                });

                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'currency'
                                });

                                var ed6 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'price'
                                });


                                $(ed.target).textbox('setValue', rows.id);
                                $(ed2.target).textbox('setValue', rows.name);
                                $(ed3.target).numberbox('setValue', rows.mpq);
                                $(ed4.target).numberbox('setValue', rows.moq);
                                $(ed5.target).textbox('setValue', rows.currency);
                                $(ed6.target).numberbox('setValue', rows.price);
                            }
                        }
                    }
                }, {
                    field: 'item_name',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Product Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true,
                        }
                    }
                }, {
                    field: 'item_id',
                    hidden: true,
                    width: 100,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'mpq',
                    width: 80,
                    halign: 'center',
                    title: "MPQ",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            readonly: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'moq',
                    width: 80,
                    halign: 'center',
                    title: "MOQ",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            readonly: true,
                            precision: 2
                        }
                    }
                },{
                    field: 'qty',
                    width: 80,
                    halign: 'center',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'currency',
                    width: 80,
                    halign: 'center',
                    title: "Currency",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true,
                            readonly: true,
                        }
                    }
                },{
                    field: 'price',
                    width: 100,
                    halign: 'center',
                    title: "Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            readonly: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'amount',
                    width: 100,
                    halign: 'center',
                    title: "Amount",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            readonly: true,
                            precision: 2
                        }
                    }
                },{
                    field: 'delivery_date',
                    width: 120,
                    halign: 'center',
                    title: "Delivery Date",
                    editor: {
                        type: 'datebox',
                        options: {
                            formatter: myformatter,
                            parser: myparser,
                            editable: false,
                            required: true
                        }
                    }
                }, {
                    field: 'remarks',
                    width: 100,
                    halign: 'center',
                    title: "Remarks",
                    editor: {
                        type: 'textbox'
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dg2').datagrid('getEditors', rowIndex);
                var total_sub = $("#total_sub").numberbox('getValue');

                if(link != ""){
                    var amount = $(editors[8].target);
                    $(editors[5].target).numberbox({
                        onChange: function() {
                            var qty_after = $(editors[5].target).numberbox('getValue');

                            var price = $(editors[7].target).numberbox('getValue');

                            var total_dp = $("#total_dp").numberbox('getValue');
                            var total_vat = $("#total_vat").numberbox('getValue');
                            var taxes = $("#taxes").numberbox('getValue');

                            amount.numberbox('setValue', (qty_after * price));
                            var total_subs = (parseFloat(qty_after * price) + parseFloat(total_sub));
                            var total_vats = ((total_subs * taxes) / 100);

                            $("#total_sub").numberbox('setValue', total_subs);
                            $("#total_vat").numberbox('setValue', total_vats);
                            $("#total_amount").numberbox('setValue', (total_subs - total_vats));
                            $("#total_grand").numberbox('setValue', ((total_subs - total_vats) - total_dp));
                        }
                    });
                }else{
                    var qty = $(editors[5].target).numberbox('getValue');
                    var amount = $(editors[8].target);
                    $(editors[5].target).numberbox({
                        onChange: function() {
                            var qty_after = $(editors[5].target).numberbox('getValue');

                            var price = $(editors[7].target).numberbox('getValue');

                            var total_dp = $("#total_dp").numberbox('getValue');
                            var total_vat = $("#total_vat").numberbox('getValue');
                            var taxes = $("#taxes").numberbox('getValue');

                            amount.numberbox('setValue', (qty_after * price));
                            var total_subs = (total_sub - (qty * price)) + (qty_after * price);
                            var total_vats = ((total_subs * taxes) / 100);

                            $("#total_sub").numberbox('setValue', total_subs);
                            $("#total_vat").numberbox('setValue', total_vats);
                            $("#total_amount").numberbox('setValue', (total_subs - total_vats));
                            $("#total_grand").numberbox('setValue', ((total_subs - total_vats) - total_dp));
                        }
                    });
                }
            }
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
        var supplier_id = $("#supplier_id").combobox('getValue');
        if (supplier_id != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Supplier first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return true;
        }
        var editors = $('#dg2').datagrid('getEditors', editIndex);
        var total_sub = $("#total_sub").numberbox('getValue');
        var total_dp = $("#total_dp").numberbox('getValue');

        var amount = $(editors[8].target).numberbox('getValue');

        var total_subs = (parseFloat(total_sub) - parseFloat(amount));
        var total_vats = ((total_subs * taxes) / 100);

        $("#total_sub").numberbox('setValue', total_subs);
        $("#total_vat").numberbox('setValue', total_vats);
        $("#total_amount").numberbox('setValue', (total_subs - total_vats));
        $("#total_grand").numberbox('setValue', ((total_subs - total_vats) - total_dp));

        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if (row.datatable == "1") {
                if (row.status == "0") {
                    $('#dlg_insert').dialog('open');
                    $('#frm_insert').form('load', row);
                    $("#supplier_id").combobox('disable');

                    setTimeout(function() {
                        $('#po_no').textbox('setValue', row.po_no);
                    }, 3000);

                    addTable(row.supplier_id, '<?= base_url('purchase/purchase_order_others/datatable_updates?po_no=') ?>' + window.btoa(row.po_no));
                } else {
                    toastr.error("You cannot update this data, because status Purchase Order is CLOSED");
                }
            } else {
                toastr.error("Please Select Header of Purchase Order <br>" + row.po_no);
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
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if (row.state == "closed") {
                            toastr.error("Please Select Detail of PO <br>" + row.id);
                        } else {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('purchase/purchase_order_others/delete') ?>',
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
                                    $('#dg').treegrid('reload');
                                }
                            });
                        }
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
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_supplier_id = $("#filter_supplier_id").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_supplier_id=" + filter_supplier_id;
        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_order_others/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('purchase/purchase_order_others/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_supplier_id = $("#filter_supplier_id").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_supplier_id=" + filter_supplier_id;
        window.location.assign('<?= base_url('purchase/purchase_order_others/print/excel') ?>' + url);
    }

    function print_po() {
        var po_no = $("#filter_po_no").combobox('getValue');
        if (po_no == "") {
            toastr.warning("Please select Purchase Order No!", "Information");
        } else {
            window.open("<?= base_url('purchase/purchase_order_others/print_po/') ?>" + window.btoa(po_no), "_blank");
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_order_others/datatables') ?>',
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'po_no',
            singleSelect: false,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
            // rowStyler: function(row) {
            //     if (row.state != "closed") {
            //         return 'background-color:#CFE6FF;font-weight:bold;';
            //     }
            // },
        });

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var po_no = $("#po_no").textbox('getValue');
                    var po_date = $("#po_date").datebox('getValue');
                    var po_name = $("#po_name").textbox('getValue');
                    var supplier_id = $("#supplier_id").combobox('getValue');
                    var taxes = $("#taxes").numberbox('getValue');
                    var total_sub = $("#total_sub").numberbox('getValue');
                    var total_vat = $("#total_vat").numberbox('getValue');
                    var total_amount = $("#total_amount").numberbox('getValue');
                    var total_dp = $("#total_dp").numberbox('getValue');
                    var total_grand = $("#total_grand").numberbox('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();
                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_id) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('purchase/purchase_order_others/create') ?>',
                                data: {
                                    po_no: po_no,
                                    po_date: po_date,
                                    po_name: po_name,
                                    taxes: taxes,
                                    supplier_id: supplier_id,
                                    item_id: rows[i].item_id,
                                    delivery_date: rows[i].delivery_date,
                                    qty: rows[i].qty,
                                    price: rows[i].price,
                                    total: rows[i].amount,
                                    total_dp: total_dp,
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

                    $('#dg').treegrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });

        $("#supplier_id").combobox({
            url: '<?= base_url('master/suppliers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Supplier",
            onSelect: function(row) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('purchase/purchase_order_others/po_no/') ?>" + row.number,
                    dataType: "html",
                    success: function(result) {
                        addTable(row.id);
                        $("#po_no").textbox('setValue', result);
                    }
                });

                $.ajax({
                    type: "post",
                    url: "<?= base_url('admin/config/read') ?>",
                    dataType: "json",
                    success: function(config) {
                        if (row.vat_status == "VAT") {
                            taxes = config.tax;
                        }else{
                            taxes = "0";
                        }

                        $("#taxes").numberbox('setValue', taxes);
                    }
                });
            }
        });

        //Get Customer
        $("#filter_supplier_id").combobox({
            url: '<?= base_url('master/suppliers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row) {
                $("#filter_po_no").combobox({
                    url: '<?= base_url('purchase/purchase_order_others/readPono/') ?>' + row.id,
                    valueField: 'po_no',
                    textField: 'po_no',
                    prompt: "Select Purchase Order No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $("#total_dp").numberbox({
            onChange: function(val){
                var total_amount = $("#total_amount").numberbox('getValue');
                $("#total_grand").numberbox('setValue', (total_amount - val));
            }
        })
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
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        } else if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else if (value == 0) {
            return 'background-color:#C8FFCC;';
        }
    }
</script>
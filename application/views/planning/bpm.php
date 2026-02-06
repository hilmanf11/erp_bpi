<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'id',halign:'center',width:190">BPM No</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status</th>
            <th rowspan="2" data-options="field:'request_date',width:120,halign:'center'">BPM Date</th>
            <th rowspan="2" data-options="field:'request_name',width:120,halign:'center'">Requester</th>
            <!-- <th rowspan="2" data-options="field:'period',width:100,halign:'center'">Period</th>
            <th rowspan="2" data-options="field:'workorder',width:120,halign:'center'">Workorder</th> -->
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Part No</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'">Part Name</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatQpa">Qty</th>
            <th rowspan="2" data-options="field:'qty_actual',width:80,halign:'center',align:'right',formatter:numberformatQpa">Qty Scan</th>
            <th rowspan="2" data-options="field:'label',width:80,align:'center'">Qty Label</th>
            <th rowspan="2" data-options="field:'state',width:80,align:'left',formatter:BtnPrint">Print</th>
            <th rowspan="2" data-options="field:'lot_no',width:100,align:'left'">Lot No</th>
            <th rowspan="2" data-options="field:'remarks',width:250,align:'left'">Remarks</th>
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
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float: left;">
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" id="filter_period" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Workorder</span>
                    <input style="width:60%;" id="filter_workorder" class="easyui-combobox">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">BPM ID</span>
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
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">BPM Date</span>
                    <input style="width:60%;" id="filter_kanban_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Report Display</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="print_doc()"><i class="fa fa-print"></i> Print Doc</a>
    </div>
</div>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>
<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Date</span>
                    <input style="width:60%;" name="request_date" id="request_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request No</span>
                    <input style="width:60%;" name="request_no" id="request_no" readonly class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Name</span>
                    <input style="width:60%;" name="request_name" id="request_name" value="<?= $this->session->name ?>" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" name="period" id="period" required="" class="easyui-combobox">
                </div> -->
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">WP</span>
                    <input style="width:60%;" name="wp" id="wp" required="" class="easyui-combogrid">
                </div> -->
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Workorder</span>
                    <input style="width:60%;" name="workorder" id="workorder" class="easyui-combobox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_fg_id" id="item_fg_id" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_fg_number" id="item_fg_number" required="" class="easyui-textbox">
                </div> -->
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Request Kanban Material" toolbar="#toolbar2" data-options="singleSelect: true">
        </table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/bpm/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        request_no();

        $("#request_date").datebox('enable');
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
            url: "<?= base_url('planning/bpm/request_no') ?>/" + window.btoa(request_date),
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
                            url: '<?= base_url('planning/bpm/readItemRm') ?>',
                            required: true,
                            panelWidth: 400,
                            idField: 'number',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Part No',
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
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
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
                                // var ed4 = dg.datagrid('getEditor', {
                                //     index: rowIndex,
                                //     field: 'stock'
                                // });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'packing_qty'
                                });

                                var item_rm_id = $(ed.target).textbox('setValue', rows.id);
                                var item_name = $(ed2.target).textbox('setValue', rows.name);
                                var uom = $(ed3.target).textbox('setValue', rows.uom);
                                var packing_qty = $(ed5.target).numberbox('setValue', rows.mpq);

                                // $.ajax({
                                //     type: "post",
                                //     url: "<?= base_url('warehouse/report_history_transactions/readEndingStock') ?>",
                                //     data: "item_rm_id=" + rows.id,
                                //     dataType: "json",
                                //     success: function(stockWarehouse) {
                                //         $(ed4.target).numberbox('setValue', stockWarehouse[0].end_stock);
                                //     }
                                // });
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
                    field: 'lot_no',
                    width: 150,
                    halign: 'center',
                    title: "Lot No",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'qty',
                    width: 80,
                    halign: 'center',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 2,
                            onChange: function(qty) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'packing_qty'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'label'
                                });

                                var qtyValue = parseFloat(qty) || 0;
                                var packingQty = parseFloat($(ed.target).numberbox('getValue')) || 0;

                                var label = Math.ceil(qtyValue / packingQty);

                                $(ed2.target).numberbox('setValue', label);
                            }
                        }
                    }
                }, 
                // {
                //     field: 'stock',
                //     width: 100,
                //     halign: 'center',
                //     title: "Warehouse",
                //     editor: {
                //         type: 'numberbox',
                //         options: {
                //             readonly: true,
                //             precision: 2
                //         }
                //     }
                // }, 
                {
                    field: 'uom',
                    width: 80,
                    align: 'center',
                    title: "Uom",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'packing_qty',
                    width: 120,
                    align: 'center',
                    title: "MPQ/User Entry",
                    editor: {
                        type: 'numberbox',
                        options: {
                            onChange: function(packing_qty) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'label'
                                });

                                var packingQty = parseFloat(packing_qty) || 0;
                                var qtyValue = parseFloat($(ed.target).numberbox('getValue')) || 0;

                                var label = Math.ceil(qtyValue / packingQty);

                                $(ed2.target).numberbox('setValue', label);
                            }
                        }
                    }
                }, {
                    field: 'label',
                    width: 80,
                    align: 'center',
                    title: "Label",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
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

            var ed = $('#dg2').datagrid('getEditor', {
                index: editIndex,
                field: 'lot_no'
            });

            var input = $(ed.target);
            input.textbox({
                onChange: function (newValue, oldValue) {
                    if (newValue.length < 7) {
                        toastr.error("Lot No Must 7 Character.");
                        $(this).textbox('setValue', '');
                    } else if (newValue.length > 7) {
                        let trimmed = newValue.slice(0, 7);
                        $(this).textbox('setValue', trimmed);
                        toastr.warning("Lot No Must 7 Character: " + trimmed);
                    }
                }
            });
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //Update Data
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if(row.state == "closed"){
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                $("#request_date").datebox('disable');
                $("#request_no").textbox('disable');
                
                // $("#item_fg_id").textbox("setValue", row.item_fg_id);
                // $("#item_fg_number").textbox("setValue", row.item_number);
                
                setTimeout(function() {
                    $("#request_no").textbox('setValue', row.request_no);
                }, 1000);

                addTable('<?= base_url('planning/bpm/datatableUpdate/') ?>' + window.btoa(row.request_no));
            }else{
                toastr.warning("Please Select Header of Table", "Information");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
    function deleted() {
        let rows = $('#dg').treegrid('getSelections');

        if (rows.length === 0) {
            toastr.warning("Please select one of the data in the table first!", "Information");
            return;
        }

        $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function (r) {
            if (!r) return;

            // Tampilkan loading selama proses berlangsung
            $.messager.progress({
                title: 'Please Wait',
                msg: 'Deleting data...'
            });

            let promises = [];

            rows.forEach(row => {

                if (row.state === "closed") {
                    toastr.error("Please Select Detail of BPM <br>" + row.id);
                    return;
                }

                // Wrapper promise untuk setiap row
                let p = new Promise((resolve, reject) => {

                    // Step 1: Check first
                    $.ajax({
                        method: 'POST',
                        url: '<?= base_url('planning/bpm/checkReceipt') ?>',
                        data: { request_id: row.id },
                        success: function (resCheck) {

                            let check = JSON.parse(resCheck);

                            if (check.status === 'error') {
                                toastr.error(check.message);
                                return resolve(); // skip saja
                            }

                            // Step 2: Delete
                            $.ajax({
                                method: 'POST',
                                url: '<?= base_url('planning/bpm/delete') ?>',
                                data: {
                                    request_id: row.id,
                                    request_no: row.request_no,
                                    item_rm_id: row.item_rm_id
                                },
                                success: function (resDelete) {
                                    resolve();
                                },
                                error: function (xhr) {
                                    toastr.error("Error: " + xhr.statusText);
                                    resolve(); // tetap lanjut
                                }
                            });

                        },
                        error: function (xhr) {
                            toastr.error("Error: " + xhr.statusText);
                            resolve(); // tetap lanjut
                        }
                    });

                });

                promises.push(p);
            });

            // Eksekusi setelah semua selesai
            Promise.all(promises).then(() => {
                $.messager.progress('close'); // tutup loading
                toastr.success("Selected data has been deleted");
                $('#dg').treegrid('reload');
                readReceiptNo();
            });

        });
    }

    function filter() {
        // var filter_period = $("#filter_period").combobox('getValue');
        // var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var filter_kanban_date = $("#filter_kanban_date").datebox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?&filter_request_no=" + filter_request_no + "&filter_product_family=" + filter_product_family + "&filter_product_no=" + btoa(filter_product_no) + "&filter_kanban_date=" + filter_kanban_date + "&filter_status=" + filter_status;
        $('#dg').treegrid({
            url: '<?= base_url('planning/bpm/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/bpm/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        // var filter_period = $("#filter_period").combobox('getValue');
        // var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var filter_kanban_date = $("#filter_kanban_date").datebox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?&filter_request_no=" + filter_request_no + "&filter_product_family=" + filter_product_family + "&filter_product_no=" + btoa(filter_product_no) + "&filter_kanban_date=" + filter_kanban_date + "&filter_status=" + filter_status;
        window.location.assign('<?= base_url('planning/bpm/print/excel') ?>' + url);
    }

    function print_doc() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            window.open("<?= base_url('planning/bpm/print_doc/') ?>" + window.btoa(row.request_no));// + "/" + window.btoa(operation), "_blank"
        }else{
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        addTable();
        $('#dg').treegrid({
            url: '<?= base_url('planning/bpm/datatables') ?>',
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'id',
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
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var request_no = $("#request_no").textbox('getValue');
                    var request_date = $("#request_date").datebox('getValue');
                    var request_name = $("#request_name").textbox('getValue');
                    var remarks = $("#remarks").textbox('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;

                    if (totalrows <= 0) {
                        toastr.error("please complete your input data");
                    } else {
                        endEditing();
                        for (let i = 0; i < totalrows; i++) {
                            if (!rows[i].lot_no) {
                                toastr.error("Lot No is required");
                                return;
                            }

                            if (rows[i].lot_no.length !== 7) {
                                toastr.error(`Lot No must be 7 characters`);
                                return;
                            }

                            if (rows[i].label == 0) {
                                toastr.error(`Row ${i + 1} has Label 0. Please correct it.`);
                                return;
                            }

                            if (rows[i].item_rm_id) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('planning/bpm/create') ?>',
                                    data: {
                                        request_date: request_date,
                                        request_no: request_no,
                                        request_name: request_name,
                                        remarks: remarks,
                                        item_rm_id: rows[i].item_rm_id,
                                        lot_no: rows[i].lot_no,
                                        label: rows[i].label,
                                        packing_qty: rows[i].packing_qty,
                                        qty: rows[i].qty
                                    },
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "error") {
                                            toastr.warning(result.message, "Error");
                                        }
                                    }
                                });
                            }
                        }

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
                        $('#dg').treegrid('reload');
                        $('#dlg_insert').dialog('close');
                    }
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
            url: '<?= base_url('planning/bpm/readPeriod') ?>',
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
                    url: '<?= base_url('planning/bpm/readWp/') ?>' + period.period,
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
                            url: '<?= base_url('planning/bpm/readRequestNo/') ?>' + period.period + '/' + window.btoa(wp.workorder),
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
            url: '<?= base_url('planning/bpm/readRequestNos/') ?>',
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
                    url: '<?= base_url('planning/bpm/readProduct/') ?>' + prodfam.id,
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
            url: '<?= base_url('planning/bpm/readProducts/') ?>',
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

    function BtnPrint(val, row) {
        if (val != "closed") {
            console.log(row);
            return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onclick="printConfirmation(\'' + row.id + '\')"><i class="fa fa-print"></i> Print</a>';
        }
    }

    function printConfirmation(id) {
        swal.fire({
            title: 'Confirmation',
            text: 'Are you sure want print this Label',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'YES',
            cancelButtonText: 'CANCEL'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika pengguna menekan tombol "Ya", lakukan pencetakan
                window.open('<?= base_url('planning/bpm/print_label/') ?>' + window.btoa(id), '_blank');
            } else {
                window.location.reload();
            }
        });
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
</script>
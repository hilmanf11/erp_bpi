<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'memo_no',width:180,halign:'center',resizable:true">Memo No</th>
            <th rowspan="2" data-options="field:'memo_date',width:100,halign:'center'">Memo Date</th>
            <th rowspan="2" data-options="field:'approved_to',width:100,halign:'center',formatter:formatApproved,styler:styleApproved">Status <br>Approve</th>
            <th rowspan="2" data-options="field:'approved_by',width:100,halign:'center'">Approve By</th>
            <th rowspan="2" data-options="field:'approved_date',width:150,halign:'center'">Approve Date</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Part No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Part Name</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'supplier_name',width:200,halign:'center'">Supplier</th>
            <th rowspan="2" data-options="field:'maker',width:200,halign:'center'">Maker</th>
            <th rowspan="2" data-options="field:'po_no',width:200,halign:'center'">Po No</th>
            <th rowspan="2" data-options="field:'os_po',width:80,halign:'center',formatter:numberformat">OS PO</th>
            <th rowspan="2" data-options="field:'min',width:80,halign:'center',formatter:numberformat">Min Stock</th>
            <th rowspan="2" data-options="field:'act_stock',width:80,halign:'center',formatter:numberformat">Act Stock</th>
            <th rowspan="2" data-options="field:'max',width:80,halign:'center',formatter:numberformat">Max Stock</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',formatter:numberformat">Qty Request</th>
            <th rowspan="2" data-options="field:'request_date',width:100,halign:'center'">Request Date</th>
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'status',width:100,halign:'center',styler:styleStatus">Status</th>
            <!-- <th rowspan="2" data-options="field:'state',width:100,align:'center',formatter:BtnPrintLabel">Print Memo</th> -->
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
<div id="toolbar" style="height: 210px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 65%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Memo Date</span>
                    <input style="width:28%;" id="filter_from2" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to2" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Memo No</span>
                    <input style="width:60%;" id="filter_memo_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="print_memo()"><i class="fa fa-print"></i> Print Memo</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" id="filter_po_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_part_no" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <!-- <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Print Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt No</span>
                <input style="width:60%;" id="filter_receipt_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_receiving_note()"><i class="fa fa-print"></i> Receiving Note</a>
            </div>
        </fieldset> -->
    </div>
    <?= $button ?>
</div>
<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add Request Material Supplier" data-options="closed: true,modal:true" style="width: 1500px; height: 100%; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period Date</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:29%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Cut Off</span>
                    <input style="width:60%;" id="filter_cutoff" class="easyui-datebox" required data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Find Item</span>
                    <input style="width:60%;" required="" id="item_rm_id" name="item_rm_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" id="btnPreview" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Memo Date</span>
                    <input style="width:60%;" name="memo_date" id="memo_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false, onChange:changeRequestDate">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Type</span>
                    <select style="width:60%;" id="type" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose Type</option>
                        <option value="P">PICK UP</option>
                        <option value="D">DELIVERY</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Memo No</span>
                    <input style="width:60%;" name="receipt_no" id="receipt_no" readonly class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Request Material List" idField="id">
            <thead>
                <tr>
                    <th field="ck" checkbox="true"></th>
                    <th hidden data-options="field:'id',width:150">ID</th>
                    <th hidden data-options="field:'item_rm_id',width:150">Product ID</th>
                    <th data-options="field:'number',width:150">Product No</th>
                    <th data-options="field:'name',width:100">Product Name</th>
                    <th data-options="field:'uom',width:60">UoM</th>
                    <th hidden data-options="field:'supplier_id',width:200">Supplier Id</th>
                    <th data-options="field:'supplier_name',width:200">Supplier</th>
                    <th data-options="field:'po_no',width:130">PO NO</th>
                    <th data-options="field:'qty_os',width:80,editor:{type:'numberbox', options:{readonly:true}}">OS PO</th>
                    <th data-options="field:'maker',width:80,editor:{type:'textbox', options:{readonly:true}}">Maker</th>
                    <th data-options="
                        field:'objective',
                        width:100,
                        editor:{
                            type:'combobox',
                            options:{
                                valueField:'id',
                                textField:'text',
                                data:[
                                    {id:'maker', text:'Maker'},
                                    {id:'supplier', text:'Supplier'}
                                ],
                                required:true
                            }
                        }
                    ">Objective</th>
                    <th data-options="field:'min_stock',width:80,editor:{type:'numberbox', options:{readonly:true, precision:2}}">Min Stock</th>
                    <th data-options="field:'stock_current',width:80,editor:{type:'numberbox', options:{readonly:true, precision:2}}">Stock Current</th>
                    <th data-options="field:'max_stock',width:80,editor:{type:'numberbox', options:{readonly:true, precision:2}}">Max Stock</th>
                    <th data-options="field:'qty',width:80,editor:{type:'numberbox', options:{precision:2}}">Qty Request</th>
                    <th data-options="field:'request_date',width:100, editor:{type:'datebox', options:{formatter:myformatter,parser:myparser, editable:false}}">Request Date</th>
                    <th data-options="field:'status',width:80">Status</th>
                    <th data-options="field:'remarks',width:80,editor:{type:'textbox'}">Remarks</th>
                </tr>
            </thead>
        </table>
    </form>
</div>
<!-- PDF -->
<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;">
    <iframe id="printout" src="<?= base_url('purchase/purchase_order_receipts/print') ?>" style="width: 100%;" hidden></iframe>
    </div>

<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; text-align: center; color: white; font-size: 20px; padding-top: 20%;">
    <b>Please Wait your Dialog download will show up...</b>
</div>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg_request').datagrid('loadData', []);
        $('#frm_insert').form('clear');
        $('#filter_from').datebox('setValue', '<?= date("Y-m-01") ?>');
        $('#filter_to').datebox('setValue', '<?= date("Y-m-t") ?>');
        $("#btnPreview").linkbutton('enable');
        $("#filter_from").datebox('enable');
        $("#filter_to").datebox('enable');
        $("#filter_cutoff").datebox('enable');
        $("#item_rm_id").combogrid('enable');
        $("#memo_date").datebox('enable');
        $("#type").combobox('enable');
        $("#receipt_no").textbox('enable');
        url_save = "<?= base_url('purchase/request_material_memo/create') ?>";
        receipt_no();

        $("#filter_cutoff").datebox({
            onSelect: function(date) {
            var filter_from = $("#filter_from").datebox('getValue');
            var filter_to = $("#filter_to").datebox('getValue');

            let year = date.getFullYear();
            let month = (date.getMonth() + 1).toString().padStart(2, '0');
            let day = date.getDate().toString().padStart(2, '0');

            let filter_cutoff = `${year}-${month}-${day}`;

            console.log("DIPILIH CUTOFF:", filter_cutoff);
            console.log("TERKINI FROM:", filter_from);
            console.log("TERKINI TO:", filter_to); 

                $("#item_rm_id").combogrid({
                    url: '<?= base_url('purchase/request_material_memo/readItems') ?>' + '?filter_from=' + filter_from +'&filter_to=' + filter_to +'&filter_cutoff=' + filter_cutoff,
                    panelWidth: 1000,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    multiple: true,
                    prompt: "Choose Item",
                    fitColumns: true, // Menyesuaikan kolom secara otomatis
                    // pagination: true, // Jika data besar, tambahkan pagination
                    selectOnCheck: true, // Pilih baris ketika checkbox di-check
                    checkOnSelect: true, // Centang checkbox ketika baris dipilih
                    onHidePanel: function() {
                        var g = $(this).combogrid('grid');
                        var rows = g.datagrid('getSelections');
                        var ids = [];
                        var texts = [];

                        $.each(rows, function(i, row) {
                            ids.push(row.id);
                            texts.push(row.number);
                        });

                        // Set ulang nilai agar hanya hasil pilihan yang muncul
                        $(this).combogrid('setValues', ids);
                        $(this).combogrid('setText', texts.join(', '));
                    },
                                    columns: [
                        [
                            {
                                field: 'ck', // Kolom checkbox
                                checkbox: true, // Mengaktifkan checkbox
                            },
                            {
                                field: 'no',
                                title: 'No',
                                width: 60
                            }, 
                            {
                                field: 'number',
                                title: 'Part No',
                                width: 150,
                                align: 'left'
                            }, 
                            {
                                field: 'name',
                                title: 'Part Name',
                                width: 120,
                                align: 'left'
                            }, 
                            {
                                field: 'supplier_name',
                                title: 'Supplier Name',
                                width: 150,
                                align: 'left'
                            },   
                            {
                                field: 'qty_os',
                                title: 'OS PO',
                                width: 100,
                                align: 'left'
                            },  
                            {
                                field: 'min_stock',
                                title: 'Min Stock',
                                width: 80,
                                align: 'left',
                                formatter: function(value, row, index) {
                                    return parseFloat(value).toFixed(2);
                                }
                            }, 
                            {
                                field: 'stock_current',
                                title: 'Stock <br>Current',
                                width: 80,
                                align: 'left',
                                formatter: function(value, row, index) {
                                    return parseFloat(value).toFixed(2);
                                }
                            }, 
                            {
                                field: 'max_stock',
                                title: 'Max Stock',
                                width: 80,
                                align: 'left',
                                formatter: function(value, row, index) {
                                    return parseFloat(value).toFixed(2);
                                }
                            }, 
                            {
                                field: 'status',
                                title: 'Status',
                                width: 80,
                                align: 'center'
                            }
                        ]
                    ],
                });
            }
        });
    }

    function receipt_no(date = "",type ="") {
        $.ajax({
            type: "post",
            url: "<?= base_url('purchase/request_material_memo/receipt_no/') ?>" + window.btoa(date) + "/" + type,
            dataType: "html",
            success: function(result) {
                $("#receipt_no").textbox('setValue', result);
            }
        });
    }

    let suppressChange = false;

    $("#memo_date").datebox({
        onSelect: function(date) {
            if (suppressChange) return;
            var type = $("#type").combobox('getValue');
            receipt_no(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate(),type);
        }
    });

    $("#type").combobox({
        onSelect: function(record) {
            if (suppressChange) return;
            var memo_date = $("#memo_date").datebox('getValue');
            if (memo_date) {
                receipt_no(memo_date, record.value);
            }
        }
    });

    var lastIndex;

    function preview(url = "") {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_cutoff = $("#filter_cutoff").datebox('getValue');
        var item_rm_id = $("#item_rm_id").combogrid('getValues');
        var memo_date = $("#memo_date").datebox('getValue');
        var type = $("#type").combobox('getValue');

        console.log("Preview URL:", url);

        if (filter_from == "" || filter_to == "" || memo_date == "" || type == "") {
            toastr.warning('Please Select Filters first', 'Required');
        } else {

            $('#dg_request').datagrid('clearSelections');
            $('#dg_request').datagrid('clearChecked');

            var final_url = url !== "" 
                ? url 
                : '<?= base_url('purchase/request_material_memo/datatablesTemp') ?>?filter_from=' + filter_from + '&filter_to=' + filter_to + '&filter_cutoff=' + filter_cutoff + '&item_rm_id=' + item_rm_id;
        
            $('#dg_request').datagrid({
                url: final_url,
                fitColumns: true,
                onClickRow: function (rowIndex) {
                    if (lastIndex != rowIndex) {
                        $('#dg_request').datagrid('endEdit', lastIndex);
                        $('#dg_request').datagrid('beginEdit', rowIndex);
                        lastIndex = rowIndex;
                    }
                },
                onBeginEdit: function(index, row) {
                    var ed = $(this).datagrid('getEditor', { index: index, field: 'objective' });
                    if (ed && !$(ed.target).combobox('getValue')) {
                        $(ed.target).combobox('setValue', 'maker'); 
                    }
                },
                onLoadSuccess: function(data) {
                    $(this).datagrid('clearSelections');
                    $(this).datagrid('clearChecked');
                    lastIndex = undefined; // Reset lastIndex saat data baru dimuat
                }
            });
        }
    }

    function changeRequestDate(newValue, oldValue) {
        var dg = $('#dg_request');
        var rows = dg.datagrid('getRows');

        if (rows && rows.length > 0) {
            
            if (lastIndex !== undefined) {
                dg.datagrid('endEdit', lastIndex);
                lastIndex = undefined;
            }

            for (var i = 0; i < rows.length; i++) {
                dg.datagrid('updateRow', {
                    index: i,
                    row: {
                        request_date: newValue
                    }
                });
            }
        }
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        console.log(row);
        if (row) {
            if (row.datatable == "1") {
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                $("#btnPreview").linkbutton('disable');     
                $("#filter_from").datebox('disable');
                $("#filter_to").datebox('disable');
                $("#filter_cutoff").datebox('disable');
                $("#item_rm_id").combogrid('disable');
                $("#memo_date").datebox('disable');
                $("#type").combobox('disable');
                $("#receipt_no").textbox('disable');

                url_save = "<?= base_url('purchase/request_material_memo/create') ?>";

                setTimeout(function () {
                    suppressChange = true;

                    $('#type').combobox('setValue', row.type);
                    $('#filter_cutoff').datebox('setValue', row.cutoff);
                    $('#receipt_no').textbox('setValue', row.memo_no);
                    $('#memo_date').datebox('setValue', row.memo_date); // <-- ini penting jika dipakai di auto no

                    suppressChange = false;

                    preview('<?= base_url('purchase/request_material_memo/datatable_updates') ?>?memo_no=' + btoa(row.memo_no));
                }, 300);
            } else {
                toastr.error("Please Select Header of Memo <br>" + row.memo_no);
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        console.log(rows);
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if (row.state == "closed") {
                            toastr.error("Please Select Detail of Request Material <br>" + row.memo_no);
                        } else {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('purchase/request_material_memo/delete') ?>',
                                data: {
                                    id: row.id
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                    readReceiptNo();
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
        var filter_from2 = $("#filter_from2").datebox('getValue');
        var filter_to2 = $("#filter_to2").datebox('getValue');
        var filter_memo_no = $("#filter_memo_no").combobox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        url = "?filter_from2=" + filter_from2 + "&filter_to2=" + filter_to2 + 
        "&filter_po_no=" + filter_po_no + 
        "&filter_part_no=" + filter_part_no + 
        "&filter_memo_no=" + filter_memo_no ;

        $('#dg').treegrid({
            url: '<?= base_url('purchase/request_material_memo/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('purchase/purchase_order_receipts/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from2 = $("#filter_from2").datebox('getValue');
        var filter_to2 = $("#filter_to2").datebox('getValue');
        var filter_memo_no = $("#filter_memo_no").combobox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');

        url = "?filter_from2=" + filter_from2 + "&filter_to2=" + filter_to2 + 
        "&filter_po_no=" + filter_po_no + 
        "&filter_part_no=" + filter_part_no + 
        "&filter_memo_no=" + filter_memo_no ;
        
         // Tampilkan overlay
         $("#loadingOverlay").show();
        
        window.location.assign('<?= base_url('purchase/request_material_memo/print/excel') ?>' + url);
    
         // Sembunyikan overlay setelah beberapa saat
         setTimeout(function () {
            $("#loadingOverlay").hide();
        }, 3000); // Sesuaikan waktu jika perlu
    }

    function print_receiving_note() {
        var receipt_no = $("#filter_receipt_no").combobox('getValue');
        if (receipt_no == "") {
            toastr.warning("Please select Receipt No!", "Information");
        } else {
            $.ajax({
                type: "post",
                url: "<?= base_url('purchase/purchase_order_receipts/checkLabel/') ?>" + window.btoa(receipt_no),
                dataType: "json",
                success: function (response) {
                    console.log(response);
                    if (response.category === 'C01') {
                        if (response.qty_label == response.label_no) {
                            window.open("<?= base_url('purchase/purchase_order_receipts/print_receiving/') ?>" + window.btoa(receipt_no), "_blank");
                        } else {
                            toastr.error("The labels haven't been scanned yet for category RM");
                        }
                    } else {
                        window.open("<?= base_url('purchase/purchase_order_receipts/print_receiving/') ?>" + window.btoa(receipt_no), "_blank");
                    }
                }
            });            
        }
    }

    function reload() {
        window.location.reload();
    }

    function readReceiptNo() {
        $("#filter_receipt_no").combobox({
            url: '<?= base_url('purchase/purchase_order_receipts/readReceiptNo') ?>',
            valueField: 'receipt_no',
            textField: 'receipt_no',
            prompt: "Select Receipt No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    }

    $(function() {
        $('#dg').treegrid({
            url: '<?= base_url('purchase/request_material_memo/datatables') ?>',
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'memo_no',
            fit: true,
            singleSelect: false,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
            // rowStyler: function(row) {
            //     if (row.state != "closed") {
            //         return 'background-color:#CFE6FF;';
            //     }
            // },
        });
        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var filter_from = $("#filter_from").datebox('getValue');
                    var filter_to = $("#filter_to").datebox('getValue');
                    var cutoff = $("#filter_cutoff").datebox('getValue');
                    var memo_date = $("#memo_date").datebox('getValue');
                    var type = $("#type").combobox('getValue');
                    var receipt_no = $("#receipt_no").textbox('getValue');

                    if (filter_from == "" || filter_to == "") {
                        toastr.warning("Please Select Filters first !", "Information");
                    } else {
                        //untuk memastikan semua baris diakhiri edit-nya sebelum diambil datanya
                        var rowsAll = $('#dg_request').datagrid('getRows');
                        for (var i = 0; i < rowsAll.length; i++) {
                            $('#dg_request').datagrid('endEdit', i);
                        }

                        $('#dg_request').datagrid('acceptChanges');

                        // Ambil data yang dicentang dan buat salinan agar tidak hilang setelah clear
                        var rows = [...$('#dg_request').datagrid('getChecked')];
                        console.log('Rows selected:', rows.length, rows);

                        if (rows.length === 0) {
                            toastr.warning("Please Select Item first!", "Information");
                            return;
                        }

                        // Bersihkan tampilan (tidak mempengaruhi rows karena sudah disalin)
                        $('#dg_request').datagrid('clearSelections');
                        $('#dg_request').datagrid('clearChecked');

                        if (rows.length > 0) {
                            $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                                if (r) {
                                    let total = rows.length;
                                    let done = 0;
                                    let allSuccess = true;
                                    let lastMessage = '';
                                    let lastTheme = 'success';

                                    for (let i = 0; i < total; i++) {
                                        let row = rows[i];
                                        $.ajax({
                                            type: "post",
                                            url: url_save,
                                            data: {
                                                id: row.id || "",
                                                filter_from: filter_from,
                                                filter_to: filter_to,
                                                cutoff: cutoff,
                                                memo_date: memo_date,
                                                type: type,
                                                memo_no: receipt_no,
                                                supplier_id: row.supplier_id,
                                                po_no: row.po_no,
                                                os_po: row.qty_os,
                                                maker: row.maker,
                                                objective: row.objective,
                                                min: row.min_stock,
                                                act_stock: row.stock_current,
                                                max: row.max_stock,
                                                qty: row.qty,
                                                request_date: row.request_date,
                                                item_rm_id: row.item_rm_id,
                                                status: row.status,
                                                remarks: row.remarks
                                            },
                                            dataType: "json",
                                            success: function(result) {
                                                done++;
                                                if (result.theme !== 'success') {
                                                    allSuccess = false;
                                                }
                                                lastMessage = result.message;
                                                lastTheme = result.theme;

                                                if (done === total) {
                                                    $('#dlg_insert').dialog('close');
                                                    Swal.fire({
                                                        title: allSuccess ? 'Data saved successfully!' : lastMessage,
                                                        icon: lastTheme,
                                                        confirmButtonText: 'Ok',
                                                        allowOutsideClick: false,
                                                    }).then(() => {
                                                        $('#dg').treegrid('reload');
                                                    });
                                                }
                                            },
                                            error: function(xhr) {
                                                done++;
                                                allSuccess = false;
                                                lastMessage = "Some error occurred.";
                                                lastTheme = "error";

                                                if (done === total) {
                                                    $('#dlg_insert').dialog('close');
                                                    Swal.fire({
                                                        title: lastMessage,
                                                        icon: lastTheme,
                                                        confirmButtonText: 'Ok',
                                                        allowOutsideClick: false,
                                                    }).then(() => {
                                                        $('#dg').treegrid('reload');
                                                    });
                                                }
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }
                }
            }]
        });

        readReceiptNo();
        $("#filter_memo_no").combobox({
            url: '<?= base_url('purchase/request_material_memo/readMemoNo') ?>',
            valueField: 'memo_no',
            textField: 'memo_no',
            prompt: "Select All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_receipt").combobox({
            url: '<?= base_url('purchase/purchase_order_receipts/readReceipts') ?>',
            valueField: 'receipt_no',
            textField: 'receipt_no',
            prompt: "Select Receipt No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_doc_no").combobox({
            url: '<?= base_url('purchase/purchase_order_receipts/readDocnos/') ?>',
            valueField: 'bc_document',
            textField: 'bc_document',
            prompt: "Select Document No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_po_no").combobox({
            url: '<?= base_url('purchase/request_material_memo/readPoNos/') ?>',
            valueField: 'po_no',
            textField: 'po_no',
            prompt: "Select PO No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_lotno").combobox({
            url: '<?= base_url('purchase/purchase_order_receipts/readLotNo/') ?>',
            valueField: 'lotno',
            textField: 'lotno',
            prompt: "Select Lot No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_part_no').combogrid({
            url: '<?= base_url('master/item_rm/reads'); ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Part No",
            columns: [
                [{
                    field: 'id',
                    title: 'Part ID',
                    width: 150
                }, {
                    field: 'number',
                    title: 'Part No',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Part Name',
                    width: 150
                }, ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });

        $("#filter_categories").combobox({
            url: '<?= base_url('purchase/purchase_order_receipts/readCategories/') ?>',
            valueField: 'id',
            textField: 'number',
            prompt: "Select Categeries",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_division').combobox({
            url: '<?= base_url('master/divisions/reads/'); ?>',
            valueField: 'number',
            textField: 'number',
            prompt: 'Select Division',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
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
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    //FORMATTER APPROVE
    function formatApproved(value) {
        if (value == "" || value === null ) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    //CELLSTYLE APPROVE
    function styleApproved(value, row, index) {
        if (value == "" || value === null ) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function styleStatus(value, row, index) {
        if (value == "" || value === null ) {
            return 'background: #ffffffff; color:white;';
        } else {
           return 'background: #FFFF00; color:white;';
        } 
    }

    function statusformat(value, row) {
        if (value != null) {
            if (row.total_scan == row.qty_label) {
                return "<b style='color:red;'>CLOSED</b>";
            } else {
                return "<b style='color:green;'>OPEN</b>";
            }
        }
    }

    function statuspriceformat(value, row) {
        if (value == "Complete") {
            return "<b style='color:green;'>COMPLETE</b>";
        } else{
            return "<b style='color:red;'>INCOMPLETE</b>";
        }
    }

    function statuspriceStyle(value, row, index) {
        if (value == "Complete") {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function statusStyle(value, row, index) {
        if (value != null) {
        console.log(row.total_scan);
            if (row.total_scan == row.qty_label) {
                return 'background-color:#FFC8C8;';
            } else {
                return 'background-color:#C8FFCC;';
            }
        }
    }

    function statusformatFinance(value, row) {
        if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        } else {
            return "<b style='color:green;'>OPEN</b>";
        }
    }

    function numberformatDefault(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusStyleFinance(value, row, index) {
        if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function BtnPrintLabel(val, row) {
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
                window.open('<?= base_url('purchase/purchase_order_receipts/print_label/') ?>' + window.btoa(id), '_blank');
            } else {
                window.location.reload();
            }
        });
    }

   function print_memo() {
        var memo_no = $("#filter_memo_no").combobox('getValue');
        console.log(memo_no);
        if (memo_no == "") {
            toastr.warning("Please select Memo No First!", "Information");
        } else {
            $.ajax({
                type: "POST",
                url: "<?= base_url('purchase/request_material_memo/checkMemo') ?>",
                data: {
                    memo_no: memo_no
                },
                dataType: "json",
                success: function(response) {
                    console.log(response);
                    if (response == 'NO') {
                        toastr.warning("Memo has not been approved", "Information");
                    } else {
                        window.open("<?= base_url('purchase/request_material_memo/print_memo/') ?>" + window.btoa(memo_no), "_blank");
                    }
                },
                error: function() {
                    toastr.error("An error occurred while checking Memo for selected Memo No!", "Error");
                }
            });
        }
    }
</script>
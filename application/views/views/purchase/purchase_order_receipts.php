<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'id',width:230,halign:'center'">Receipt No</th>
            <th rowspan="2" data-options="field:'category_code',width:100,halign:'center',align:'center'">Category</th>
            <th rowspan="2" data-options="field:'division',width:100,halign:'center',align:'center'">Division</th>
            <th rowspan="2" data-options="field:'total_scan',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status<br>POR</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformatFinance,styler:statusStyleFinance">Status<br>Invoice</th>
            <th rowspan="2" data-options="field:'print',width:80,align:'center',formatter:statusformatFinance,styler:statusStyleFinance">Status<br>Print GRN</th>
            <th rowspan="2" data-options="field:'po_no',width:150,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'receipt_date',width:100,halign:'center'">Receipt Date</th>
            <th colspan="2" data-options="field:'coslpan',halign:'center'">Supplier</th>
            <th rowspan="2" data-options="field:'bc_document',width:200,halign:'center'">Document</th>
            <th rowspan="2" data-options="field:'bc_date',width:80,halign:'center'">Document <br>Date</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'qty_receipt',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th rowspan="2" data-options="field:'uom',width:80,halign:'center',align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'currency',width:80,halign:'center',align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'mpq',width:80,halign:'center',align:'right'">MPQ</th>
            <th rowspan="2" data-options="field:'qty_label',width:80,halign:'center',align:'right'">Qty <br> Label</th>
            <th rowspan="2" data-options="field:'state',width:80,align:'center',formatter:BtnPrintLabel">Label</th>
            <th rowspan="2" data-options="field:'invoice_no',width:120,halign:'center',align:'right'">Invoice No</th>
            <th rowspan="2" data-options="field:'lotno',width:80,halign:'center',align:'right'">Lot No</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'supplier_id',width:80,halign:'center'">ID</th>
            <th data-options="field:'supplier_name',width:200,halign:'center'">Name</th>
            <!-- <th data-options="field:'bc_kind',width:80,halign:'center'">Kind</th>
            <th data-options="field:'bc_aju',width:100,halign:'center'">AJU</th>
            <th data-options="field:'bc_document',width:200,halign:'center'">Document</th>
            <th data-options="field:'bc_date',width:80,halign:'center'">Date</th> -->
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 310px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 65%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt Date</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" id="filter_categories" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt No</span>
                    <input style="width:60%;" id="filter_receipt" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" id="filter_doc_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
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
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="filter_division" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status Invoice</span>
                    <select style="width:60%;" id="filter_status_invoice" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Lot No</span>
                    <input style="width:60%;" id="filter_lotno" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Print Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt No</span>
                <input style="width:60%;" id="filter_receipt_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_receiving_note()"><i class="fa fa-print"></i> Receiving Note</a>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>
<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1200px; height: 100%; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt Date</span>
                    <input style="width:60%;" name="receipt_date" id="receipt_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt No</span>
                    <input style="width:60%;" name="receipt_no" id="receipt_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Lot No</span>
                    <input style="width:60%;" name="lotno" id="lotno" readonly class="easyui-textbox">
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">BC Kind</span>
                    <input style="width:60%;" name="bc_kind" id="bc_kind" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">No AJU</span>
                    <input style="width:60%;" name="bc_aju" id="bc_aju" required="" class="easyui-textbox">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Doc No</span>
                    <input style="width:60%;" name="bc_document" id="bc_document" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Doc Date</span>
                    <input style="width:60%;" name="bc_date" id="bc_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" name="po_no" id="po_no" required="" class="easyui-combogrid">
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">AWB No</span>
                    <input style="width:60%;" name="awb_no" id="awb_no" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">AWB Date</span>
                    <input style="width:60%;" name="awb_date" id="awb_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div> -->
            </div>
        </fieldset>
        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Purchase Order List" idField="item_number">
            <thead>
                <tr>
                    <th field="ck" checkbox="true"></th>
                    <th data-options="field:'item_rm_id',width:150">Product ID</th>
                    <th data-options="field:'item_number',width:150">Product No</th>
                    <th data-options="field:'item_name',width:200">Product Name</th>
                    <th data-options="field:'uom',width:80">UoM</th>
                    <th data-options="field:'qty_po',width:80,editor:{type:'numberbox', options:{readonly:true}}">PO</th>
                    <th data-options="field:'qty_os',width:80,editor:{type:'numberbox', options:{readonly:true}}">OS PO</th>
                    <th data-options="field:'qty_receipt',width:80,editor:{type:'numberbox',options:{precision:2}}">Receipt</th>
                    <th data-options="field:'mpq',width:80,editor:{type:'numberbox', options:{readonly:true, precision:2}}">MPQ</th>
                    <th data-options="field:'convertion',width:80,editor:{type:'numberbox', options:{readonly:true, precision:2}}">Convertion</th>
                    <th data-options="field:'qty_convertion',width:100,editor:{type:'numberbox', options:{readonly:true, precision:2}}">Qty Convertion</th>
                    <th data-options="field:'qty_label',width:80,editor:{type:'numberbox', options:{readonly:true}}">Label</th>
                    <th data-options="field:'price',width:80">Price</th>
                    <th data-options="field:'currency',width:80">Currency</th>
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
        $('#receipt_date').datebox('setValue', '<?= date("Y-m-d") ?>');
        receipt_no();
        lotno();
        // $("#bc_kind").combobox({
        //     url: '<?= base_url('master/bc_kind/reads') ?>',
        //     valueField: 'name',
        //     textField: 'name',
        //     prompt: "Select BC Kind",
        //     panelHeight: "auto"
        // });
        $("#supplier_id").combogrid({
            url: '<?= base_url('master/suppliers/reads') ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            columns: [
                [{
                    field: 'number',
                    title: 'Supplier No',
                    width: 50
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 200
                }]
            ],
            onSelect: function(val, row) {
                $('#po_no').combogrid({
                    url: '<?= base_url('purchase/purchase_orders/readPono?supplier_id=') ?>' + row.id,
                    panelWidth: 500,
                    idField: 'po_no',
                    textField: 'po_no',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Purchase Order",
                    columns: [
                        [{
                            field: 'po_no',
                            title: 'PO No',
                            width: 120
                        }, {
                            field: 'po_date',
                            title: 'PO Date',
                            width: 150
                        }]
                    ],
                });
            }
        });
    }

    function receipt_no(date = "") {
        $.ajax({
            type: "post",
            url: "<?= base_url('purchase/purchase_order_receipts/receipt_no/') ?>" + window.btoa(date),
            dataType: "html",
            success: function(result) {
                $("#receipt_no").textbox('setValue', result);
            }
        });
    }

    function lotno(date = "") {
        $.ajax({
            type: "post",
            url: "<?= base_url('purchase/purchase_order_receipts/lotno/') ?>" + window.btoa(date),
            dataType: "html",
            success: function(result) {
                $("#lotno").textbox('setValue', result);
            }
        });
    }

    function preview() {
        var po_no = $("#po_no").combogrid('getValue');
        if (po_no == "") {
            toastr.warning('Please select PO No', 'Required');
        } else {
            var lastIndex;
            var dg = $('#dg_request').datagrid({
                url: '<?= base_url('purchase/purchase_order_receipts/datatablesTemp') ?>?po_no=' + po_no,
                fitColumns: true,
                // onLoadSuccess: function(data) {
                //     // Set default value for qty_receipt to 0 after loading data
                //     var rows = $(this).datagrid('getRows');
                //     for (var i = 0; i < rows.length; i++) {
                //         rows[i].qty_receipt = 0;
                //     }
                //     $(this).datagrid('loadData', rows);
                // },

                onClickRow: function(rowIndex) {
                    if (lastIndex != rowIndex) {
                        $(this).datagrid('endEdit', lastIndex);
                        $(this).datagrid('beginEdit', rowIndex);
                    }
                    lastIndex = rowIndex;
                },
                
                onBeginEdit: function(rowIndex, row) {
                    var editors = $('#dg_request').datagrid('getEditors', rowIndex);
                    var qty_po = $(editors[0].target);
                    var qty_os = $(editors[1].target);
                    var qty_receipt = $(editors[2].target);
                    var qty_mpq = $(editors[3].target);
                    var convertion = $(editors[4].target);
                    var qty_convertion = $(editors[5].target);
                    var qty_label = $(editors[6].target);

                    // qty_receipt.numberbox('setValue', 0);
                    
                    qty_receipt.add(qty_mpq).numberbox({
                        onChange: function() {
                            var f_qty_po = qty_po.numberbox('getValue');
                            var f_qty_os = qty_os.numberbox('getValue');
                            var f_qty_receipt = qty_receipt.numberbox('getValue');
                            var f_qty_mpq = qty_mpq.numberbox('getValue');
                            var f_convertion = convertion.numberbox('getValue');

                            var convertions = f_qty_receipt * f_convertion;

                            if (parseInt(f_qty_os) == 0) {
                                if (parseInt(f_qty_po) >= parseInt(f_qty_receipt)) {
                                    var cost = Math.ceil(convertions / f_qty_mpq);
                                    qty_convertion.numberbox('setValue', convertions);
                                    qty_label.numberbox('setValue', cost);
                                } else {
                                    qty_receipt.numberbox('setValue', 0);
                                    toastr.warning("Qty Receipt > Qty PO", "Information");
                                }
                            } else {
                                if (parseInt(f_qty_os) >= parseInt(f_qty_receipt)) {
                                    var cost = Math.ceil(convertions / f_qty_mpq);
                                    qty_convertion.numberbox('setValue', convertions);
                                    qty_label.numberbox('setValue', cost);
                                } else {
                                    qty_receipt.numberbox('setValue', 0);
                                    toastr.warning("Qty Receipt > Qty OS PO", "Information");
                                }
                            }
                        }
                    });
                }
            });
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
                            toastr.error("Please Select Detail of POR <br>" + row.id);
                        } else {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('purchase/purchase_order_receipts/delete') ?>',
                                data: {
                                    id: row.purchase_order_receipts_id,
                                    receipt_id: row.id,
                                    po_no: row.po_no,
                                    item_rm_id: row.item_rm_id,
                                    qty_receipt: row.qty_receipt
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
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_categories = $("#filter_categories").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_receipt = $("#filter_receipt").combobox('getValue');
        var filter_lotno = $("#filter_lotno").combobox('getValue');
        var filter_doc_no = $("#filter_doc_no").combobox('getValue');
        var filter_status_invoice = $("#filter_status_invoice").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + 
        "&filter_po_no=" + filter_po_no + "&filter_part_no=" + filter_part_no + 
        "&filter_supplier=" + filter_supplier + "&filter_receipt=" + filter_receipt + 
        "&filter_lotno=" + filter_lotno + 
        "&filter_doc_no=" + filter_doc_no + "&filter_categories=" + filter_categories + 
        "&filter_division=" + filter_division + "&filter_status_invoice=" + filter_status_invoice;

        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_order_receipts/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('purchase/purchase_order_receipts/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_categories = $("#filter_categories").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_receipt = $("#filter_receipt").combobox('getValue');
        var filter_lotno = $("#filter_lotno").combobox('getValue');
        var filter_doc_no = $("#filter_doc_no").combobox('getValue');
        var filter_status_invoice = $("#filter_status_invoice").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + 
        "&filter_po_no=" + filter_po_no + "&filter_part_no=" + filter_part_no + 
        "&filter_supplier=" + filter_supplier + "&filter_receipt=" + filter_receipt + 
        "&filter_lotno=" + filter_lotno + 
        "&filter_doc_no=" + filter_doc_no + "&filter_categories=" + filter_categories + 
        "&filter_division=" + filter_division + "&filter_status_invoice=" + filter_status_invoice;
        
         // Tampilkan overlay
         $("#loadingOverlay").show();
        
        window.location.assign('<?= base_url('purchase/purchase_order_receipts/print/excel') ?>' + url);
    
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
            url: '<?= base_url('purchase/purchase_order_receipts/datatables') ?>',
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'id',
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
        // $('#dlg_insert').dialog({
        //     buttons: [{
        //         text: 'Save All & Print Receiving Note',
        //         iconCls: 'icon-ok',
        //         handler: function() {
        //             var receipt_date = $("#receipt_date").datebox('getValue');
        //             var receipt_no = $("#receipt_no").textbox('getValue');
        //             var lotno = $("#lotno").textbox('getValue');
        //             // var bc_kind = $("#bc_kind").textbox('getValue');
        //             // var bc_aju = $("#bc_aju").textbox('getValue');
        //             var bc_document = $("#bc_document").textbox('getValue');
        //             var bc_date = $("#bc_date").datebox('getValue');
        //             //var awb_no = $("#awb_no").textbox('getValue');
        //             //var awb_date = $("#awb_date").datebox('getValue');

        //             if (bc_document == "" || bc_date == "") {
        //                 toastr.warning("Please input Doc No and Doc Date!", "Information");
        //             } else {
        //                 $('#dg_request').datagrid('acceptChanges');
        //                 var rows = $('#dg_request').datagrid('getSelections');
        //                 if (rows.length > 0) {
        //                     $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
        //                         if (r) {
        //                             for (var i = 0; i < rows.length; i++) {
        //                                 var row = rows[i];
        //                                 $.ajax({
        //                                     type: "post",
        //                                     url: '<?= base_url('purchase/purchase_order_receipts/create') ?>',
        //                                     data: 'item_rm_id=' + row.item_rm_id +
        //                                         '&supplier_id=' + row.supplier_id +
        //                                         '&receipt_date=' + receipt_date +
        //                                         '&receipt_no=' + receipt_no +
        //                                         '&lotno=' + lotno +
        //                                         '&po_no=' + row.po_no +
        //                                         // '&bc_kind=' + bc_kind +
        //                                         // '&bc_aju=' + bc_aju +
        //                                         '&bc_document=' + bc_document +
        //                                         '&bc_date=' + bc_date +
        //                                         // '&awb_no=' + awb_no +
        //                                         // '&awb_date=' + awb_date +
        //                                         '&qty_po=' + row.qty_po +
        //                                         '&qty_os=' + row.qty_os +
        //                                         '&qty_receipt=' + row.qty_receipt +
        //                                         '&qty_mpq=' + row.mpq +
        //                                         '&qty_label=' + row.qty_label,
        //                                     dataType: "json",
        //                                     success: function(result) {
        //                                         //toastr.success(result.message, result.title);
        //                                         Swal.fire({
        //                                             title: result.message,
        //                                             icon: result.theme,
        //                                             confirmButtonText: 'Ok',
        //                                             allowOutsideClick: false,
        //                                         }).then((result) => {
        //                                             if (result.isConfirmed) {
        //                                                 Swal.fire({
        //                                                     title: "Are you Want to Print Barcode?",
        //                                                     showDenyButton: true,
        //                                                     confirmButtonText: "Yes",
        //                                                     denyButtonText: `No`
        //                                                 }).then((result) => {

        //                                                     if (result.isConfirmed) {
        //                                                         var receipt_no = $("#receipt_no").textbox('getValue');
        //                                                         var qty_receipt = row ? row.qty_receipt : 0;
        //                                                         var qty_label = row ? row.qty_label : 0;

        //                                                         var po = {
        //                                                             receipt_no: receipt_no,
        //                                                             qty_receipt: qty_receipt,
        //                                                             qty_label: qty_label
        //                                                         };

        //                                                         window.location.reload();
                                                                
        //                                                         print_po(po);
        //                                                     } else if (result.isDenied) {
        //                                                         Swal.fire("You can print QR Code in Datagrid", "", "info").then((result) => {
        //                                                             if (result.isConfirmed) {
        //                                                                 window.location.reload();
        //                                                             }
        //                                                         });
        //                                                     }
        //                                                 });
        //                                             }
        //                                         });
        //                                     }
        //                                 });
        //                             }
                                    
        //                             $('#dg').treegrid('reload');
        //                             $('#dlg_insert').dialog('close');
        //                         }
        //                     });

        //                 } else {
        //                     toastr.warning("Please select one of the data in the table first!", "Information");
        //                 }
        //             }
        //         }
        //     }]
        // });

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All & Print Receiving Note',
                iconCls: 'icon-ok',
                handler: function() {
                    var receipt_date = $("#receipt_date").datebox('getValue');
                    var lotno = $("#lotno").textbox('getValue');
                    var bc_document = $("#bc_document").textbox('getValue');
                    var bc_date = $("#bc_date").datebox('getValue');

                    if (bc_document == "" || bc_date == "") {
                        toastr.warning("Please input Doc No and Doc Date!", "Information");
                    } else {
                        $('#dg_request').datagrid('acceptChanges');
                        var rows = $('#dg_request').datagrid('getSelections');
                        if (rows.length > 0) {
                            $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                                if (r) {
                                    // Fetch the latest receipt number from the server
                                    var encodedReceiptDate = window.btoa(receipt_date);
                                    $.ajax({
                                        type: "get",
                                        url: '<?= base_url('purchase/purchase_order_receipts/receipt_no/') ?>' + encodedReceiptDate,
                                        success: function(receipt_number) {
                                            $.ajax({
                                                type: "get",
                                                url: '<?= base_url('purchase/purchase_order_receipts/lotno/') ?>' + encodedReceiptDate,
                                                success: function(lotno) {
                                                    for (var i = 0; i < rows.length; i++) {
                                                        var row = rows[i];
                                                        $.ajax({
                                                            type: "post",
                                                            url: '<?= base_url('purchase/purchase_order_receipts/create') ?>',
                                                            data: {
                                                                item_rm_id: row.item_rm_id,
                                                                supplier_id: row.supplier_id,
                                                                receipt_date: receipt_date,
                                                                receipt_no: receipt_number,  // gunakan receipt_number dari server
                                                                lotno: lotno, // gunakan lotno dari server
                                                                po_no: row.po_no,
                                                                bc_document: bc_document,
                                                                bc_date: bc_date,
                                                                qty_po: row.qty_po,
                                                                qty_os: row.qty_os,
                                                                qty_receipt: row.qty_convertion,
                                                                qty_receipt2: row.qty_receipt,
                                                                qty_mpq: row.mpq,
                                                                qty_label: row.qty_label,
                                                                currency: row.currency,
                                                                price: row.price
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
                                                                        Swal.fire({
                                                                            title: "Do you want to print the barcode?",
                                                                            showDenyButton: true,
                                                                            confirmButtonText: "Yes",
                                                                            denyButtonText: "No"
                                                                        }).then((result) => {
                                                                            if (result.isConfirmed) {
                                                                                var qty_receipt = row ? row.qty_receipt : 0;
                                                                                var qty_label = row ? row.qty_label : 0;

                                                                                var po = {
                                                                                    receipt_no: receipt_number,
                                                                                    qty_receipt: qty_receipt,
                                                                                    qty_label: qty_label
                                                                                };

                                                                                window.location.reload();
                                                                                print_po(po);
                                                                            } else if (result.isDenied) {
                                                                                Swal.fire("You can print QR Code in Datagrid", "", "info").then((result) => {
                                                                                    if (result.isConfirmed) {
                                                                                        window.location.reload();
                                                                                    }
                                                                                });
                                                                            }
                                                                        });
                                                                    }
                                                                });
                                                            }
                                                        });
                                                    }

                                                    $('#dg').treegrid('reload');
                                                    $('#dlg_insert').dialog('close');
                                                }
                                            });
                                        }
                                    });
                                }
                            });

                        } else {
                            toastr.warning("Please select one of the data in the table first!", "Information");
                        }
                    }
                }
            }]
        });
        
        $("#receipt_date").datebox({
            onSelect: function(date) {
                receipt_no(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate());
                lotno(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate());
            }
        });

        readReceiptNo();
        $("#filter_supplier").combobox({
            url: '<?= base_url('purchase/purchase_order_receipts/readSupplier') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(supp) {
                $("#filter_receipt").combobox({
                    url: '<?= base_url('purchase/purchase_order_receipts/readReceipt/') ?>' + window.btoa(supp.id),
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
                    url: '<?= base_url('purchase/purchase_order_receipts/readDocno/') ?>' + window.btoa(supp.id),
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
                    url: '<?= base_url('purchase/purchase_order_receipts/readPoNo/') ?>' + window.btoa(supp.id),
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
            }
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
            url: '<?= base_url('purchase/purchase_order_receipts/readPoNos/') ?>',
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

    function statusformat(value, row) {
        if (value != null) {
            if (row.total_scan == row.qty_label) {
                return "<b style='color:red;'>CLOSED</b>";
            } else {
                return "<b style='color:green;'>OPEN</b>";
            }
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



    function print_po(po) {
        console.log(po);
        var url = '<?= base_url('purchase/purchase_order_receipts/print_label_po/') ?>' + window.btoa(po.receipt_no);
        window.open(url, '_blank');
    }
</script>
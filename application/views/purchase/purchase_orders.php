<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'po_no',width:180,halign:'center',resizable:true">PO No</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status PO</th>
            <th rowspan="2" data-options="field:'status_pi',width:80,align:'center',formatter:statusformatFinance,styler:statusStyleFinance">Status<br>Invoice</th>
            <th rowspan="2" data-options="field:'approved_to',width:100,halign:'center',formatter:formatApproved,styler:styleApproved">Status <br>Approve</th>
            <th rowspan="2" data-options="field:'category_code',width:100,halign:'center',align:'center'">Category</th>
            <th rowspan="2" data-options="field:'approved_by',width:100,halign:'center'">Approve By</th>
            <th rowspan="2" data-options="field:'approved_date',width:150,halign:'center'">Approve Date</th>
            <th rowspan="2" data-options="field:'request_no',width:150,halign:'center'">Request No</th>
            <th rowspan="2" data-options="field:'po_date',width:100,align:'center'">PO Date</th>
            <th rowspan="2" data-options="field:'delivery_date',width:100,align:'center'">Delivery Date</th>
            <th rowspan="2" data-options="field:'supplier_name',width:200,halign:'center'">Supplier</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'item_supplier',width:200,halign:'center'">Supplier Product</th>
            <th rowspan="2" data-options="field:'specification',width:200,halign:'center'">Specification</th>
            <th rowspan="2" data-options="field:'mpq',width:80,halign:'center',align:'right',formatter:numberformatDefault">MPQ</th>
            <th rowspan="2" data-options="field:'moq',width:80,halign:'center',align:'right',formatter:numberformatDefault">MOQ</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatDefault">Qty</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformat">Price</th>
            <th rowspan="2" data-options="field:'discount',width:80,halign:'center',align:'right',formatter:numberformatDefault">Disc %</th>
            <th rowspan="2" data-options="field:'total',width:120,halign:'center',align:'right',formatter:numberformat">Total Price</th>
            <th rowspan="2" data-options="field:'status_price',width:120,halign:'center',formatter:statuspriceformat,styler:statuspriceStyle">Status Price</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'notes',width:80,align:'center'">Note</th>
            <th rowspan="2" data-options="field:'revision',width:80,align:'center'">Revision</th>
            <th rowspan="2" data-options="field:'remarks',width:100,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'remark_revision',width:150,halign:'center'">Remark Revision</th>
            <th colspan="4" data-options="field:'',width:100,halign:'center'"> Forecast</th>
            <th rowspan="2" data-options="field:'btn',width:100,halign:'center',align:'right',formatter:btnHistories">History</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'month_1',width:100,halign:'center'">Month 1</th>
            <th data-options="field:'month_2',width:100,halign:'center'">Month 2</th>
            <th data-options="field:'month_3',width:100,halign:'center'">Month 3</th>
            <th data-options="field:'month_4',width:100,halign:'center'">Month 4</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 250px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">    
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Updated Date</span>
                    <input style="width:28%;" id="filter_from_update" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to_update" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_suppliers" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="print_po()"><i class="fa fa-print"></i> Purchase Order</a>
                    <!-- <a href="javascript:;" class="easyui-linkbutton" onclick="complete_po()"><i class="fa fa-check"></i> Complete</a> -->
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Po No</span>
                    <input style="width:60%;" id="filter_po_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_part_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part Name</span>
                    <input style="width:60%;" id="filter_part_name" class="easyui-combogrid">
                </div>
            </div>
             <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" id="filter_categories" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSED</option>
                        <option value="2">COMPLETE</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="complete_po()"><i class="fa fa-check"></i> Complete/Open</a>
    </div>
</div>

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Convert Purchase Request to Purchase Order" data-options="closed: true,modal:true" style="width: 100%; height: 100%; padding:10px; top: 0; left: 0;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:60%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PO Period</span>
                <input style="width:28%;" name="po_date" id="po_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PR No</span>
                <input style="width:60%;" name="request_no" id="request_no" required class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Note</span>
                <input style="width:60%;" name="notes" id="notes" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" id="btnPreview" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
            </div>
        </fieldset>

        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Purchase Request Data" data-options="fitColumns: false, rownumbers: true" idField="item_number">
        </table>

        <div id="frm_calculate" style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: right; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <div style="width: 100%; float: right; margin-top: 10px;">
                <!-- <a style="width: 100%;" class="easyui-linkbutton c2" onclick="calculate()">Calculate</a> -->
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">SUB TOTAL</b>
                        <input style="width:60%; text-align:right;" id="total_sub" name="total_sub" readonly class="easyui-numberbox" value="0" readonly data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">DISC %</b>
                        <input style="width:10%;" id="disc_pr" name="disc_pr" value="0" class="easyui-numberbox" data-options="precision:2">
                        <input style="width:50%; text-align:right;" id="discount_total" name="discount_total" class="easyui-numberbox" value="0" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">DPP</b>
                        <input style="width:60%; text-align:right;" id="total_dpp" name="total_dpp" readonly class="easyui-numberbox" value="0" readonly data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">VAT</b>
                        <input style="width:60%; text-align:right;" id="total_vat" name="total_vat" readonly class="easyui-numberbox" value="0" readonly data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">INCOME TAX</b>
                        <input style="width:10%;" id="income_tax" name="income_tax" value="0" class="easyui-numberbox">
                        <input style="width:50%; text-align:right;" id="income_total" name="income_total" readonly class="easyui-numberbox" value="0" readonly data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">DOWN PAYMENT</b>
                        <input style="width:60%; text-align:right;" id="total_dp" name="total_dp" required class="easyui-numberbox" value="0" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">GRAND TOTAL</b>
                        <input style="width:60%; text-align:right;" id="total_grand" name="total_grand" class="easyui-numberbox" value="0" readonly data-options="precision:2,groupSeparator:','">
                    </div>

                    <div class="fitem" style="margin-top: 10px;">
                        <a href="javascript:void(0)" class="easyui-linkbutton c2" style="width:100%;" onclick="calculateManually()">Recalculate</a>
                    </div>
                </div>
            </fieldset>
        </div>
    </form>
</div>

<!-- Detail Histories -->
<div id="dlg_history" class="easyui-dialog" title="PO Histories" data-options="closed: true,modal:true" style="width: 1250px; height: 300px; top: 20px;">
    <table id="dg_history" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'request_no',width:130,halign:'center'">Request No</th>
                <th data-options="field:'po_no',width:150,halign:'center'">PO NO</th>
                <th data-options="field:'part_number',width:150,halign:'center'">Part Number</th>
                <th data-options="field:'qty',width:80,halign:'center'">Qty</th>
                <th data-options="field:'specification',width:140,halign:'center'">Specification</th>
                <th data-options="field:'price',width:80,halign:'center',formatter: numberformat">Price</th>
                <th data-options="field:'discount',width:100,halign:'center'">Disc</th>
                <th data-options="field:'discount_nominal',width:100,halign:'center'">Disc Nominal</th>
                <th data-options="field:'created_by',width:100,halign:'center'">Cerated By</th>
                <th data-options="field:'created_date',width:120,halign:'center'">Created Date</th>
            </tr>
        </thead>
    </table>
</div>

<!-- UPDATE SIGNATURE -->
<div id="dlg_approval" class="easyui-dialog" title="Edit Signature" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_approval" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Approved By</span>
                <input style="width:60%;" name="po_approved" id="po_approved" value="<?= $approval->po_approved ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Checked By</span>
                <input style="width:60%;" name="po_checked" id="po_checked" value="<?= $approval->po_checked ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Prepared By</span>
                <input style="width:60%;" name="po_prepared" id="po_prepared" value="<?= $approval->po_prepared ?>" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('purchase/purchase_orders/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $.messager.prompt('Convert PR to PO', 'Please input Password to Convert', function(r) {
            if (r) {
                var encodedPassword = window.btoa(r);
                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('purchase/purchase_orders/checkPassword') ?>', // Ganti dengan URL endpoint Anda
                    data: {password: encodedPassword},
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            // Buka dialog setelah sedikit penundaan untuk memastikan loading ditampilkan dengan benar
                            setTimeout(() => {
                                $('#dlg_insert').dialog('open');
                                $('#frm_calculate').hide();
                                $("#btnPreview").linkbutton('enable');
                                $('#dg_request').datagrid('loadData', []);
                                $("#request_no").combobox({
                                    url: '<?= base_url('purchase/purchase_requests/readRequestnumber') ?>',
                                    valueField: 'request_no',
                                    textField: 'request_no',
                                    prompt: "Select Purchase Request No",
                                    editable: false,
                                    icons: [{
                                        iconCls: 'icon-clear',
                                        handler: function(e) {
                                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                                        }
                                    }]
                                });

                                // Menghilangkan loading setelah dialog terbuka
                                Swal.close();
                            }, 500);
                        } else {
                            toastr.warning("Please Input Correct Password!", "Information");
                        }
                    },
                    error: function() {
                        toastr.error("There was an error processing your request.", "Error");
                    }
                });
            }
        });

        // Menggunakan setTimeout untuk menunggu elemen input dibuat oleh $.messager.prompt
        setTimeout(function() {
            var inputField = $('.messager-input');
            inputField.attr('type', 'password');
        }, 100);
    }

    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if (row.datatable == "1") {
                if (row.status_pi == "0" || row.status_pi == null ) {
                    $('#dlg_insert').dialog('open');
                    
                    // Ini akan mengisi seluruh form secara otomatis, termasuk diskon & tax dari DB
                    $('#frm_insert').form('load', row); 
                    
                    $('#frm_calculate').show();
                    $("#btnPreview").linkbutton('disable');

                    // HAPUS blok setTimeout di sini! Langsung saja panggil preview.

                    preview('<?= base_url('purchase/purchase_orders/datatable_updates') ?>?po_no=' + btoa(row.po_no));
                } else {
                    toastr.error("You cannot update this data, because status PI in POR is closed");
                }
            } else {
                toastr.error("Please Select Header of PO <br>" + row.po_no);
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //EDIT DATA
    // function update() {
    //     var row = $('#dg').treegrid('getSelected');
    //     if (row) {
    //         console.log("Update :",row);
    //         console.log("row.convertion", row.convertion);
    //         if (row.datatable == "1") {
    //             if (row.status_pi == "0" || row.status_pi == null ) {
    //                 $('#dlg_insert').dialog('open');
    //                 $('#frm_insert').form('load', row);
    //                 $('#frm_calculate').show();
    //                 $("#btnPreview").linkbutton('disable');
                    
    //                 setTimeout(function() { 
    //                     $('#income_total').numberbox('setValue', row.income_total);
    //                     $('#discount_total').numberbox('setValue', row.discount_total);
    //                 }, 1000);

    //                 preview('<?= base_url('purchase/purchase_orders/datatable_updates') ?>?po_no=' + btoa(row.po_no));
    //             } else {
    //                 toastr.error("You cannot update this data, because status PI in POR is closed");
    //             }
    //         } else {
    //             toastr.error("Please Select Header of PO <br>" + row.po_no);
    //         }
    //     } else {
    //         toastr.warning("Please select one of the data in the table first!", "Information");
    //     }
    // }
    
    function preview(url = "") {
        var request_no = $("#request_no").combobox('getValue');
        var po_date = $("#po_date").datebox('getValue');

        if(url == ""){
            var url = '<?= base_url('purchase/purchase_requests/reads') ?>?request_no=' + request_no;
        }

        if (request_no == "") {
            toastr.warning('Please select Purchase Request No', 'Required');
        } else {
            var lastIndex;

            $.ajax({
                type: "post",
                url: "<?= base_url('purchase/purchase_orders/readPeriodLists/') ?>",
                data: "po_date=" + po_date,
                dataType: "json",
                success: function(result) {
                    $('#dg_request').datagrid({
                        singleSelect: true,
                        url: url,
                        columns: [
                            [{
                                field: 'action',
                                width: 80,
                                halign: 'center',
                                title: "Action",
                                formatter: buttonEdit
                            }, {//0
                                field: 'item_number',
                                width: 150,
                                readonly: true,
                                halign: 'center',
                                title: "Product No",
                                editor: {
                                    type: 'textbox',
                                    options: {
                                        readonly: true
                                    }
                                }
                            }, {//1
                                field: 'po_no',
                                width: 150,
                                hidden: true,
                                halign: 'center',
                                title: "PO No",
                                editor: {
                                    type: 'textbox',
                                    options: {
                                        readonly: true
                                    }
                                }
                            }, {
                                field: 'item_name',
                                width: 200,
                                readonly: true,
                                halign: 'center',
                                title: "Product Name"
                             }, {//2
                                field: 'item_supplier',
                                width: 150,
                                halign: 'center',
                                title: "Supplier Product",
                                editor: {
                                    type: 'textbox',
                                    options: {
                                        readonly: true
                                    }
                                }
                            }, {
                                field: 'length',
                                width: 80,
                                readonly: true,
                                halign: 'center',
                                title: "Length"
                            }, {
                                field: 'width',
                                width: 80,
                                readonly: true,
                                halign: 'center',
                                title: "Width"
                            }, {
                                field: 'thickness',
                                width: 80,
                                readonly: true,
                                halign: 'center',
                                title: "Thickness"
                            }, {
                                field: 'diameter',
                                width: 80,
                                readonly: true,
                                halign: 'center',
                                title: "Diameter"
                            }, {
                                field: 'kind',
                                hidden: true,
                                width: 80,
                                readonly: true,
                                halign: 'center',
                                title: "Kind"
                            }, {
                                field: 'category_name',
                                width: 150,
                                readonly: true,
                                halign: 'center',
                                title: "Product <br>Family"
                            }, {//3
                                field: 'uom_default',
                                width: 80,
                                readonly: true,
                                halign: 'center',
                                title: "UOM",
                                editor: {
                                    type: 'textbox'
                                }
                            }, {//4
                                field: 'supplier_number',
                                width: 250,
                                halign: 'center',
                                title: "Supplier",
                                editor: {
                                    type: 'combogrid'
                                }
                            }, {//5
                                field: 'supplier_id',
                                hidden: true,
                                width: 250,
                                halign: 'center',
                                title: "Supplier Id",
                                editor: {
                                    type: 'textbox',
                                }
                            }, {//6
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
                            }, {//7
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
                            }, {//8
                                field: 'qty',
                                width: 80,
                                halign: 'center',
                                title: "Qty",
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        precision: 2
                                    }
                                }
                            }, {//9
                                field: 'currency',
                                width: 80,
                                halign: 'center',
                                title: "Currency",
                                editor: {
                                    type: 'textbox',
                                    options: {
                                        readonly: true,
                                    }
                                }
                            }, {//10
                                field: 'discount',
                                width: 80,
                                halign: 'center',
                                title: "Disc %",
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        precision: 2
                                    }
                                }
                             }, {//11
                                field: 'discount_nominal',
                                width: 80,
                                halign: 'center',
                                title: "Discount",
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        precision: 2
                                    }
                                }
                            }, {//12
                                field: 'price',
                                width: 100,
                                halign: 'center',
                                align: 'right',
                                title: "Price",
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        formatter: numberformats,
                                        readonly: true,
                                        precision: 2
                                    }
                                }
                            }, {//13
                                field: 'price_conv',
                                width: 100,
                                halign: 'center',
                                align: 'right',
                                title: "Price <br>Conversion",
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        formatter: numberformats,
                                        readonly: true,
                                        precision: 2
                                    }
                                }
                            }, {//14
                                field: 'total',
                                width: 100,
                                halign: 'center',
                                align: 'right',
                                title: "Amount",
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        formatter: numberformats,
                                        readonly: true,
                                        precision: 2
                                    }
                                }
                            }, {//15
                                field: 'delivery_date',
                                width: 120,
                                halign: 'center',
                                title: "Delivery <br>Date",
                                editor: {
                                    type: 'datebox',
                                    options: {
                                        formatter: myformatter,
                                        parser: myparser,
                                        editable: true,
                                        required: true
                                    }
                                }
                            }, {//16
                                field: 'remarks',
                                width: 200,
                                halign: 'center',
                                title: "Remarks",
                                editor: {
                                    type: 'textbox'
                                }
                            }, {//17
                                field: 'month_1',
                                width: 80,
                                align: 'center',
                                title: result[0].name,
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        required: true,
                                    }
                                }
                            }, {//18
                                field: 'month_2',
                                width: 80,
                                align: 'center',
                                title: result[1].name,
                                editor: {
                                    type: 'numberbox',
                                }
                            }, {//19
                                field: 'month_3',
                                width: 80,
                                align: 'center',
                                title: result[2].name,
                                editor: {
                                    type: 'numberbox',
                                }
                            }, {//20
                                field: 'month_4',
                                width: 80,
                                align: 'center',
                                title: result[3].name,
                                editor: {
                                    type: 'numberbox',
                                }
                            },{//21
                                field: 'item_rm_id',
                                width: 150,
                                hidden: true,
                                halign: 'center',
                                title: "Product ID",
                                editor: {
                                    type: 'textbox',
                                }
                            },{//22
                                field: 'taxes',
                                width: 150,
                                hidden: true,
                                halign: 'center',
                                title: "Tax",
                                editor: {
                                    type: 'textbox',
                                }
                            },{//23
                                field: 'type',
                                width: 150,
                                // hidden: true,
                                halign: 'center',
                                title: "Type",
                                editor: {
                                    type: 'textbox',
                                }
                            },{//24
                                field: 'specification',
                                width: 150,
                                hidden: true,
                                halign: 'center',
                                title: "Sp",
                                editor: {
                                    type: 'textbox',
                                }
                            },{
                                field: 'density',
                                width: 150,
                                hidden: true,
                                halign: 'center',
                                title: "Density"
                            },{
                                field: 'weight',
                                width: 150,
                                hidden: true,
                                halign: 'center',
                                title: "Weight"
                             },{//25
                                field: 'convertion',
                                width: 100,
                                //hidden: true,
                                halign: 'center',
                                title: "Convertion",
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        readonly: true,
                                        precision: 2
                                    }
                                }
                            }]
                        ],
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
                        onBeginEdit: function(rowIndex, row) {
                            var editors = $('#dg_request').datagrid('getEditors', rowIndex);
                            var item_rm_id = $(editors[21].target).textbox('getValue');
                            var supplier_id = $(editors[4].target);
                            var po_date = $("#po_date").datebox('getValue');
                            var total_sub = $("#total_sub").numberbox('getValue');
                            
                            var delivery_date = $(editors[15].target);

                            var length = row.length || "";
                            var width = row.width || "";
                            var thickness = row.thickness || "";

                            if (length && width && thickness) {
                                var spec = length + " x " + width + " x " + thickness;
                                if (editors[24]) {
                                    $(editors[24].target).textbox('setValue', spec);
                                }
                            }

                            $(editors[8].target).numberbox({
                                onChange: function() {
                                    var qty = $(editors[8].target).numberbox('getValue');
                                    var discount_nominal = $(editors[11].target).numberbox('getValue');
                                    var price = $(editors[13].target).numberbox('getValue');
                                    var total = ((qty * price)-(discount_nominal));
                                    editors[14].target.numberbox('setValue', total);
                                }
                            });

                            let isUpdatingFromPercent = false;
                            let isUpdatingFromNominal = false;

                            $(editors[10].target).numberbox({
                                onChange: function () {
                                    if (isUpdatingFromNominal) return; // Hindari loop silang

                                    isUpdatingFromPercent = true;

                                    var qty = parseFloat($(editors[8].target).numberbox('getValue')) || 0;
                                    var discount = parseFloat($(editors[10].target).numberbox('getValue')) || 0;
                                    var price = parseFloat($(editors[12].target).numberbox('getValue')) || 0;

                                    var sub_total = qty * price;
                                    var discount_nominal = (discount / 100) * sub_total;
                                    var total = sub_total - discount_nominal;

                                    $(editors[11].target).numberbox('setValue', discount_nominal.toFixed(2));
                                    $(editors[14].target).numberbox('setValue', total.toFixed(2));

                                    isUpdatingFromPercent = false;
                                }
                            });

                            $(editors[11].target).numberbox({
                                onChange: function () {
                                    if (isUpdatingFromPercent) return; // Hindari loop silang

                                    isUpdatingFromNominal = true;

                                    var qty = parseFloat($(editors[8].target).numberbox('getValue')) || 0;
                                    var discount_nominal = parseFloat($(editors[11].target).numberbox('getValue')) || 0;
                                    var price = parseFloat($(editors[12].target).numberbox('getValue')) || 0;

                                    var sub_total = qty * price;
                                    var disc_pr = sub_total > 0 ? (discount_nominal / sub_total) * 100 : 0;
                                    var total = sub_total - discount_nominal;

                                    $(editors[10].target).numberbox('setValue', disc_pr.toFixed(2));
                                    $(editors[14].target).numberbox('setValue', total.toFixed(2));

                                    isUpdatingFromNominal = false;
                                }
                            });

                            supplier_id.combogrid({
                                url: '<?= base_url('master/supplier_items/readSuppliers?item_rm_id=') ?>' + item_rm_id,
                                required: true,
                                panelWidth: 400,
                                idField: 'id', // Pastikan idField adalah 'id', bukan 'name'
                                textField: 'name',
                                mode: 'remote',
                                fitColumns: true,
                                prompt: 'Choose Supplier',
                                columns: [
                                    [{
                                        field: 'number',
                                        title: 'Supplier No',
                                        width: 100
                                    }, {
                                        field: 'name',
                                        title: 'Supplier Name',
                                        width: 250
                                    }]
                                ],
                                
                                onLoadSuccess: function(supp) {
                                    console.log("Data Loaded: ", supp);

                                    var currentSupplierID = $(editors[5].target).textbox('getValue');

                                    if (currentSupplierID) {
                                        // Jika dalam mode edit, cari supplier berdasarkan ID
                                        var currentSupplier = supp.rows.find(s => s.id == currentSupplierID);
                                        if (currentSupplier) {
                                            supplier_id.combogrid('setValue', currentSupplier.id);
                                            updateSupplierFields(currentSupplier);
                                        }
                                    } else {
                                        // Mode add: cari supplier dengan share_order "100"
                                        var selectedSupplier = supp.rows.find(s => s.share_order == "100");

                                        if (selectedSupplier) {
                                            supplier_id.combogrid('setValue', selectedSupplier.id);
                                            updateSupplierFields(selectedSupplier);
                                        } else {
                                            toastr.warning("Please Input Product No in Supplier Items");
                                        }
                                    }
                                },

                                onSelect: function(index, row) {
                                    console.log("Selected Supplier: ", row);
                                    supplier_id.combogrid('setValue', row.id);
                                    updateSupplierFields(row);
                                }
                            });

                            // Fungsi untuk mengupdate field lain saat memilih supplier
                            function updateSupplierFields(supplier) {
                                $(editors[3].target).textbox('setValue', supplier.uom_default);
                                $(editors[4].target).combogrid('setValue', supplier.name);
                                $(editors[5].target).textbox('setValue', supplier.id);
                                $(editors[6].target).textbox('setValue', supplier.mpq);
                                $(editors[7].target).textbox('setValue', supplier.moq);
                                $(editors[9].target).textbox('setValue', supplier.currency);
                                $(editors[10].target).textbox('setValue', 0);
                                $(editors[11].target).textbox('setValue', 0);
                                $(editors[12].target).numberbox('setValue', supplier.price);
                                $(editors[22].target).textbox('setValue', supplier.vat); // Tambahkan ini agar VAT juga diset
                                $(editors[23].target).textbox('setValue', supplier.type); // Supplier Type
                                $(editors[2].target).textbox('setValue', supplier.item_supplier);
                                // Menghitung total harga setelah diskon
                                var qty = parseFloat($(editors[8].target).numberbox('getValue')) || 0;
                                var price = parseFloat($(editors[12].target).numberbox('getValue')) || 0;
                                var discount = parseFloat($(editors[10].target).numberbox('getValue')) || 0;
                                var convertion = parseFloat($(editors[25].target).numberbox('getValue')) || 1;
                                var totalDiscountedPrice = (qty * (convertion * price)) - ((qty * (convertion * price)) * (discount / 100));
                                var price_conv = convertion * price;

                                $(editors[13].target).numberbox('setValue', price_conv);
                                $(editors[14].target).numberbox('setValue', totalDiscountedPrice);

                                if (supplier.remark && supplier.remark.trim() !== "") {
        
                                    if (supplier.remark.toLowerCase().includes("price before discount")) {
                                        toastr.info("This item have Price Before Discount", "Information", {timeOut: 5000});
                                    } else {
                                        toastr.info("Remark: " + supplier.remark, "Information", {timeOut: 5000});
                                    }
                                }
                            }

                            delivery_date.add(delivery_date).datebox({
                                onChange: function() {
                                    var f_delivery_date = delivery_date.datebox('getValue');
                                    if (f_delivery_date < po_date) {
                                        delivery_date.datebox('clear');
                                        toastr.warning("Po Date > Expected Date");
                                    }
                                }
                            });

                            delivery_date.datebox('setValue', row.delivery_date);
                        },
                        onLoadSuccess: function() {
                            var rows = $('#dg_request').datagrid('getRows');
                            console.log("dari Onload",rows);
                            endEditing();

                            for (var i = 0; i < rows.length; i++) {
                                var r = rows[i];

                                var density = parseFloat(r.density) || 0;
                                var diameter = parseFloat(r.diameter) || 0;
                                var length = parseFloat(r.length) || 0;
                                var width = parseFloat(r.width) || 0;
                                var thickness = parseFloat(r.thickness) || 0;
                                var weight = parseFloat(r.weight) || 0;

                                let volume = 0;

                                if (r.kind === "TUBE" && diameter && length) {
                                    volume = Math.PI * Math.pow(diameter / 2, 2) * length;
                                    r.specification = `π × (${diameter})² × ${length}`;
                                } else if (r.kind === "CUBE" && length && width && thickness) {
                                    volume = length * width * thickness;
                                    r.specification = `${length} x ${width} x ${thickness}`;
                                }

                                // var weightGr = density * volume;
                                // var weightKg = weightGr / 1000000;
                                var defaultweightKg = 1;

                                if (length != 0 ) {
                                    r.convertion = parseFloat(weight);
                                }else{
                                    r.convertion = parseFloat(defaultweightKg.toFixed(2));
                                }

                                $('#dg_request').datagrid('refreshRow', i);
                            }
                                                    
                            var totalrows = rows.length;
                            
                            if (totalrows > 0) {
                                var total_subs = 0;
                                var selected_tax = 0;
                                var has_c07 = false;
                                
                                for (let i = 0; i < totalrows; i++) {
                                    total_subs += parseFloat(rows[i].total);

                                    if (rows[i].item_category_id === 'C07') {
                                        has_c07 = true;
                                    }
                                }

                                if (rows.length > 0) {
                                    tax = parseFloat(rows[0].taxes || 0);
                                    type = rows[0].type;
                                }

                                $("#total_sub").numberbox('setValue', total_subs);

                                var current_income_tax = parseFloat($("#income_tax").numberbox('getValue')) || 0;

                                if (has_c07 && current_income_tax === 0) {
                                    $("#income_tax").numberbox('setValue', 2);
                                    current_income_tax = 2; 
                                    toastr.info("Income Tax auto-filled to 2% for category C07");
                                }

                                // ==========================================
                                // PENCEGAHAN BENTROK (BACA NILAI DARI FORM)
                                // ==========================================
                                // 1. Ambil nilai diskon dan DP yang terbawa dari database saat Update
                                var current_disc_pr = parseFloat($("#disc_pr").numberbox('getValue')) || 0;
                                var current_discount_total = parseFloat($("#discount_total").numberbox('getValue')) || 0;
                                var current_total_dp = parseFloat($("#total_dp").numberbox('getValue')) || 0;

                                // 2. Beritahu sistem apakah user sebelumnya memakai diskon Persen atau Nominal
                                if (current_discount_total > 0 && current_disc_pr === 0) {
                                    lastChanged = 'discount_total'; // Mengunci agar fungsi hitung tidak merusak nominal
                                } else if (current_disc_pr > 0) {
                                    lastChanged = 'disc_pr';
                                }

                                // 3. Panggil kalkulator dengan parameter ASLI dari form, BUKAN angka 0
                                calculateTotal(total_subs, current_disc_pr, current_discount_total, current_income_tax, current_total_dp, tax, type);

                                $("#disc_pr").numberbox({
                                    onChange: function () {
                                        if (ignoreChange) return;
                                        lastChanged = 'disc_pr';

                                        const disc_pr = parseFloat($(this).numberbox('getValue')) || 0;
                                        const income_tax = parseFloat($("#income_tax").numberbox('getValue')) || 0;
                                        const total_dp = parseFloat($("#total_dp").numberbox('getValue')) || 0;

                                        calculateTotal(total_subs, disc_pr, null, income_tax, total_dp, tax, type);
                                    }
                                });

                                $("#discount_total").numberbox({
                                    onChange: function () {
                                        if (ignoreChange) return;
                                        lastChanged = 'discount_total';

                                        const discount_total = parseFloat($(this).numberbox('getValue')) || 0;
                                        const income_tax = parseFloat($("#income_tax").numberbox('getValue')) || 0;
                                        const total_dp = parseFloat($("#total_dp").numberbox('getValue')) || 0;

                                        calculateTotal(total_subs, null, discount_total, income_tax, total_dp, tax, type);
                                    }
                                });

                                $("#income_tax").numberbox({
                                    onChange: function () {
                                        var disc_pr = parseFloat($("#disc_pr").numberbox('getValue')) || 0;
                                        var discount_total = parseFloat($("#discount_total").numberbox('getValue')) || 0;
                                        var income_tax = parseFloat($("#income_tax").numberbox('getValue')) || 0;
                                        var total_dp = parseFloat($("#total_dp").numberbox('getValue')) || 0;

                                        calculateTotal(total_subs, disc_pr, discount_total, income_tax, total_dp, tax, type);
                                    }
                                });

                                $("#total_dp").numberbox({
                                    onChange: function () {
                                        var disc_pr = parseFloat($("#disc_pr").numberbox('getValue')) || 0;
                                        var discount_total = parseFloat($("#discount_total").numberbox('getValue')) || 0;
                                        var income_tax = parseFloat($("#income_tax").numberbox('getValue')) || 0;
                                        var total_dp = parseFloat($("#total_dp").numberbox('getValue')) || 0;

                                        calculateTotal(total_subs, disc_pr, discount_total, income_tax, total_dp, tax, type);
                                    }
                                });
                                
                            } else {
                                toastr.error("Data in Sales order List Empty");
                            }
                        }
                    });
                }            
            });
        }
    }

    let lastChanged = null;
    let ignoreChange = false;

    function calculateTotal(total_subs, disc_pr = 0, discount_total = null, income_tax = 0, total_dp = 0, tax = 0, type = "") {
        ignoreChange = true;

        if (lastChanged === 'disc_pr') {
            // User isi persen → hitung nominal
            discount_total = (total_subs * (disc_pr / 100));
            $("#discount_total").numberbox('setValue', discount_total.toFixed(2));
        } else if (lastChanged === 'discount_total') {
            // User isi nominal → hitung persen
            disc_pr = (discount_total / total_subs) * 100;
            $("#disc_pr").numberbox('setValue', disc_pr.toFixed(2));
        } else {
            // Default: hitung berdasarkan disc_pr
            discount_total = (total_subs * (disc_pr / 100));
            $("#discount_total").numberbox('setValue', discount_total.toFixed(2));
        }

        $("#total_dpp").numberbox('setValue', type === "LOCAL" ? ((total_subs - discount_total) * 11 / 12).toFixed(2) : 0);
        const total_dpp = parseFloat($("#total_dpp").numberbox('getValue')) || 0;

        const total_vat = total_dpp * (tax / 100);
        $("#total_vat").numberbox('setValue', total_vat.toFixed(2));

        const income_total = ((total_subs - discount_total) * (income_tax / 100));
        $("#income_total").numberbox('setValue', income_total.toFixed(2));

        const total_grand = ((total_subs - discount_total) + total_vat - income_total - total_dp);
        $("#total_grand").numberbox('setValue', total_grand.toFixed(2));

        ignoreChange = false;
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }
    
    function editrow(target) {
        $('#dg_request').datagrid('selectRow', getRowIndex(target));
        $('#dg_request').datagrid('beginEdit', getRowIndex(target));
    }

    function saverow(target) {
        $('#dg_request').datagrid('endEdit', getRowIndex(target));
    }

    function changePrice(target) {
        var editors = $('#dg_request').datagrid('getEditors', getRowIndex(target));
        var rows = $('#dg_request').datagrid('getRows');

        var item_rm_id = rows[getRowIndex(target)].item_rm_id;//item_number
        var supplier_id = rows[getRowIndex(target)].supplier_id;

        $.ajax({
            type: "post",
            url: "<?= base_url('master/supplier_items/readItem') ?>",
            data: "supplier_id=" + supplier_id + "&item_rm_id=" + item_rm_id, //item_number
            dataType: "json",
            success: function(json) {
                toastr.success("Price Changed!");
                $(editors[1].target).textbox('setValue', json.price);
            }
        });
    }

    //Add Signature
    function signatures() {
        $('#dlg_approval').dialog('open');
    }

    function readPo() {
        $("#filter_suppliers").combobox({
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
            onSelect: function(supp) {
                $("#filter_po_no").combobox({
                    url: '<?= base_url('purchase/purchase_orders/readPono?supplier_id=') ?>' + supp.id,
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

        $("#filter_po_no").combobox({
            url: '<?= base_url('purchase/purchase_orders/readPonos/') ?>',
            valueField: 'po_no',
            textField: 'po_no',
            prompt: "Select Purchase Order No",
            panelHeight: 200,
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_categories").combobox({
            url: '<?= base_url('purchase/purchase_orders/readCategories/') ?>',
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

        $('#filter_part_no').combogrid({
            url: '<?= base_url('master/item_rm/reads'); ?>',
            panelWidth: 500,
            idField: 'number',
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

        $('#filter_part_name').combogrid({
            url: '<?= base_url('master/item_rm/reads'); ?>',
            panelWidth: 500,
            idField: 'name',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Part Name",
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
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if (row.datatable == "1") {
                            toastr.error("Please Select Detail of PO <br>" + row.po_no);
                        } else {
                            if (row.status == "0") {
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('purchase/purchase_orders/delete') ?>',
                                    data: {
                                        id: row.id,
                                        request_no: row.request_no,
                                        item_rm_id: row.item_rm_id
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
                                        $('#dg').treegrid('reload');
                                    }
                                });
                            } else {
                                toastr.error("You cannot update this data, because status PO is closed");
                            }
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
        var filter_from_update = $("#filter_from_update").datebox('getValue');
        var filter_to_update = $("#filter_to_update").datebox('getValue');
        var filter_po_no = $("#filter_po_no").combogrid('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_part_name = $("#filter_part_name").combogrid('getValue');
        var filter_suppliers = $("#filter_suppliers").combobox('getValue');
        var filter_categories = $("#filter_categories").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_from_update=" + filter_from_update + "&filter_to_update=" + filter_to_update + "&filter_po_no=" + filter_po_no + "&filter_part_no=" + filter_part_no + "&filter_part_name=" + filter_part_name + "&filter_suppliers=" + filter_suppliers + "&filter_status=" + filter_status + "&filter_categories=" + filter_categories;
        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_orders/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('purchase/purchase_orders/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_from_update = $("#filter_from_update").datebox('getValue');
        var filter_to_update = $("#filter_to_update").datebox('getValue');
        var filter_po_no = $("#filter_po_no").combogrid('getValue');
        var filter_part_no = $("#filter_part_no").combogrid('getValue');
        var filter_part_name = $("#filter_part_name").combogrid('getValue');
        var filter_suppliers = $("#filter_suppliers").combobox('getValue');
        var filter_categories = $("#filter_categories").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_from_update=" + filter_from_update + "&filter_to_update=" + filter_to_update + "&filter_po_no=" + filter_po_no + "&filter_part_no=" + filter_part_no + "&filter_part_name=" + filter_part_name + "&filter_suppliers=" + filter_suppliers + "&filter_status=" + filter_status + "&filter_categories=" + filter_categories;
        window.location.assign('<?= base_url('purchase/purchase_orders/print/excel') ?>' + url);
    }

    function complete_po(){
        var rows = $('#dg').treegrid('getSelections');

        if (rows.length > 0) {
            // $.messager.confirm('Warning', 'Are you sure you want to completed this data?', function(r) {
            //     if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if (row.datatable == "1") {
                            toastr.error("Please Select Detail of PO <br>" + row.po_no);
                        } else {
                            if (row.status == "0") {
                                Swal.fire({
                                    title: "Are you sure?",
                                    text: "You want to Complete this data!",
                                    icon: "warning",
                                    showCancelButton: true,
                                    confirmButtonColor: "#3085d6",
                                    cancelButtonColor: "#d33",
                                    confirmButtonText: "Yes",
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $.ajax({
                                            method: 'post',
                                            url: '<?= base_url('purchase/purchase_orders/completePo') ?>',
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
                                                $('#dg').treegrid('reload');
                                            }
                                        });
                                    }
                                });
                            } else {
                                // toastr.error("this data has been Completed");
                                Swal.fire({
                                    title: "Are you sure?",
                                    text: "You want to Open this data!",
                                    icon: "warning",
                                    showCancelButton: true,
                                    confirmButtonColor: "#3085d6",
                                    cancelButtonColor: "#d33",
                                    confirmButtonText: "Yes",
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $.ajax({
                                            method: 'post',
                                            url: '<?= base_url('purchase/purchase_orders/uncompletePo') ?>',
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
                                                $('#dg').treegrid('reload');
                                            }
                                        });
                                    }
                                });
                            }
                        }
                    }
            //     }
            // });
        }
    }

    function print_po() {
        var po_no = $("#filter_po_no").combogrid('getValue');
        console.log(po_no);
        if (po_no == "") {
            toastr.warning("Please select Purchase Order No!", "Information");
        } else {
            $.ajax({
                type: "POST",
                url: "<?= base_url('purchase/purchase_orders/checkTotalSub') ?>",
                data: {
                    po_no: po_no
                },
                dataType: "json",
                success: function(response) {
                    console.log(response);
                    if (response.total_sub == 0) {
                        toastr.warning("Total Sub , VAT and Grand Total is 0 for selected PO no, Please Update first for Calculated ", "Information");
                    } else {
                        window.open("<?= base_url('purchase/purchase_orders/print_po/') ?>" + window.btoa(po_no), "_blank");
                    }
                },
                error: function() {
                    toastr.error("An error occurred while checking Total Sub for selected Purchase Order!", "Error");
                }
            });
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        readPo();
        $("#add").html("Convert PR to PO");

        $("#delivery_date").datebox({
            onChange: function() {
                var po_date = $("#po_date").datebox('getValue');
                var delivery_date = $("#delivery_date").datebox('getValue');
                if (delivery_date < po_date) {
                    $("#delivery_date").datebox('clear');
                    toastr.warning("Po Date > Delivery Date");
                }
            }
        });

        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_orders/datatables') ?>',
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'po_no',
            singleSelect: false,
            fit: true,
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
                    var request_no = $("#request_no").combobox('getValue');
                    var po_date = $("#po_date").datebox('getValue');
                    var notes = $("#notes").textbox('getValue');
                    if (po_date == "") {
                        toastr.warning('Please select Po Date', 'Required');
                    } else {
                        var rows = $('#dg_request').datagrid('getRows');
                        var totalrows = rows.length;

                        var inEditMode = false;
                        for (var i = 0; i < totalrows; i++) {
                            if (rows[i].editing) {
                                inEditMode = true;
                                break;
                            }
                        }

                        if (inEditMode) {
                            toastr.warning("Please save all edited rows before next Process!", "Information");
                        } else {
                            // endEditing();
                            if (totalrows > 0) {
                                $.messager.confirm('Warning', 'Are you sure you want Process this Data?', function(r) {
                                    if (r) {
                                        for (var i = 0; i < totalrows; i++) {
                                            var row = rows[i];

                                            var item_rm_id = row.item_rm_id;//item_number
                                            var po_no = row.po_no;
                                            var item_supplier = row.item_supplier;
                                            var supplier_id = row.supplier_id;
                                            var qty = row.qty;
                                            var length = row.length;
                                            var width = row.width;
                                            var thickness = row.thickness;
                                            var diameter = row.diameter;
                                            var weight = row.weight;
                                            var specification = row.specification;
                                            var convertion = row.convertion;
                                            var discount = row.discount;
                                            var discount_nominal = row.discount_nominal;
                                            var price = row.price_conv;
                                            var currency = row.currency;
                                            var total = row.total;
                                            var taxes = row.taxes;
                                            var type = row.type;
                                            var delivery_date = row.delivery_date;
                                            var remarks = row.remarks;
                                            var month_1 = row.month_1;
                                            var month_2 = row.month_2;
                                            var month_3 = row.month_3;
                                            var month_4 = row.month_4;

                                            var total_sub = $("#total_sub").numberbox('getValue');
                                            var disc_pr = $("#disc_pr").numberbox('getValue');
                                            var discount_total = $("#discount_total").numberbox('getValue');
                                            var total_vat = $("#total_vat").numberbox('getValue');
                                            var total_dpp = $("#total_dpp").numberbox('getValue');
                                            var income_tax = $("#income_tax").numberbox('getValue');
                                            var income_total = $("#income_total").numberbox('getValue');
                                            var total_dp = $("#total_dp").numberbox('getValue');
                                            var total_grand = $("#total_grand").numberbox('getValue');


                                            if(po_no == ""){
                                                var url_save = "<?= base_url('purchase/purchase_orders/create') ?>";
                                            }else{
                                                var url_save = "<?= base_url('purchase/purchase_orders/update') ?>";
                                            }

                                            if(price == 1){
                                                status_price = "Incomplete";
                                            }else{
                                                status_price = "Complete";
                                            }

                                            $.ajax({
                                                type: "post",
                                                url: url_save,
                                                data: 'item_rm_id=' + item_rm_id +
                                                    '&po_no=' + po_no +
                                                    '&item_supplier=' + item_supplier +
                                                    '&supplier_id=' + supplier_id +
                                                    '&request_no=' + request_no +
                                                    '&request_date=' + row.request_date +
                                                    '&request_name=' + row.request_name +
                                                    '&po_date=' + po_date +
                                                    '&qty=' + qty +
                                                    '&length=' + length +
                                                    '&width=' + width +
                                                    '&thickness=' + thickness +
                                                    '&diameter=' + diameter +
                                                    '&weight=' + weight +
                                                    '&specification=' + specification +
                                                    '&convertion=' + convertion +
                                                    '&discount=' + discount +
                                                    '&discount_nominal=' + discount_nominal +
                                                    '&price=' + price +
                                                    '&currency=' + currency +
                                                    '&total=' + total +
                                                    '&taxes=' + taxes +
                                                    '&type=' + type +
                                                    '&delivery_date=' + delivery_date +
                                                    '&remarks=' + remarks +
                                                    '&notes=' + notes +
                                                    '&month_1=' + month_1 +
                                                    '&month_2=' + month_2 +
                                                    '&month_3=' + month_3 +
                                                    '&month_4=' + month_4 +
                                                    '&total_sub=' + total_sub +
                                                    '&disc_pr=' + disc_pr +
                                                    '&total_vat=' + total_vat +
                                                    '&total_dpp=' + total_dpp +
                                                    '&income_tax=' + income_tax +
                                                    '&income_total=' + income_total +
                                                    '&total_grand=' + total_grand +
                                                    '&total_dp=' + total_dp +
                                                    '&status_price=' + status_price +
                                                    '&discount_total=' + discount_total,
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
                                        // setTimeout(window.open("<?= base_url('purchase/purchase_orders/print_po/') ?>" + window.btoa(po_no), "_blank"), 3000);
                                        readPo();
                                        $('#dg').treegrid('reload');
                                        $('#dlg_insert').dialog('close');
                                    }
                                });
                            } else {
                                toastr.warning("Please select one of the data in the table first!", "Information");
                            }
                        }
                    }
                }
            }]
        });

        //Update Data
        $('#dlg_approval').dialog({
            buttons: [{
                text: 'Update Data',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_approval').form('submit', {
                        url: '<?= base_url('purchase/purchase_orders/update_approval') ?>',
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
                            $('#dlg_approval').dialog('close');
                        }
                    });
                }
            }]
        });
    });

    function buttonEdit(value, row, index) {
        if (row.editing) {
            var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm w-100" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a>';
            return s;
        } else {
            var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm w-100" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a>';
            return e;
        }
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
    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg_request').datagrid('validateRow', editIndex)) {
            $('#dg_request').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
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

    function numberformat(value, row) {
        // if (value != null) {
        //     // Tentukan format berdasarkan mata uang
        //     const formatter = new Intl.NumberFormat(row.currency === 'USD' ? 'en-US' : 'id-ID', {
        //         minimumFractionDigits: row.currency === 'USD' ? 4 : 2
        //     });
        //     return "<b>" + formatter.format(value) + "</b>";
        // }

        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return formatter.format(value);
        }
    }


    function numberformats(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return formatter.format(value);
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

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        } else if (value == 2) {
            return "<b style='color:white;'>COMPLETE</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else if (value == 2) {
            return 'background-color:#4B54E7;';
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

     //CELLSTYLE APPROVE
     function styleApproved(value, row, index) {
        if (value == "" || value === null ) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
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

    // function calculateManually() {
    //     // Ambil semua baris dari datagrid
    //     var rows = $('#dg_request').datagrid('getRows');
    //     var total_subs = 0; // Inisialisasi total_subs

    //     // Iterasi setiap baris untuk menghitung total_subs
    //     for (var i = 0; i < rows.length; i++) {
    //         if (rows[i].total) { // Pastikan nilai rows[i].total ada
    //             total_subs += parseFloat(rows[i].total);
    //         }
    //     }

    //     // Ambil nilai dari numberbox lain
    //     var disc_pr = parseFloat($("#disc_pr").numberbox('getValue')) || 0;
    //     var income_tax = parseFloat($("#income_tax").numberbox('getValue')) || 0;
    //     var total_dp = parseFloat($("#total_dp").numberbox('getValue')) || 0;
    //     var tax = parseFloat(rows[0]?.taxes || 0); // Gunakan optional chaining untuk menghindari error jika rows kosong
    //     var type = rows[0].type ; 

    //     // Set nilai ke #total_sub
    //     $("#total_sub").numberbox('setValue', total_subs);

    //     // Panggil fungsi calculateTotal dengan parameter yang diperoleh
    //     calculateTotal(total_subs, disc_pr, income_tax, total_dp, tax, type);
    // }

    function calculateManually() {
        const rows = $('#dg_request').datagrid('getRows');
        let total_subs = 0;

        for (let i = 0; i < rows.length; i++) {
            if (rows[i].total) {
                total_subs += parseFloat(rows[i].total);
            }
        }

        const disc_pr_raw = $("#disc_pr").numberbox('getValue');
        const discount_total_raw = $("#discount_total").numberbox('getValue');

        const disc_pr = parseFloat(disc_pr_raw) || 0;
        const discount_total = parseFloat(discount_total_raw) || 0;
        const income_tax = parseFloat($("#income_tax").numberbox('getValue')) || 0;
        const total_dp = parseFloat($("#total_dp").numberbox('getValue')) || 0;
        const tax = parseFloat(rows[0]?.taxes || 0);
        const type = rows[0]?.type || "";

        $("#total_sub").numberbox('setValue', total_subs);

        // Penentuan lastChanged berdasarkan mana yang punya input
        if (discount_total_raw !== "") {
            lastChanged = 'discount_total';
        } else if (disc_pr_raw !== "") {
            lastChanged = 'disc_pr';
        } else {
            lastChanged = null; // tidak tentukan, biarkan default
        }

        calculateTotal(
            total_subs,
            lastChanged === 'disc_pr' ? disc_pr : null,
            lastChanged === 'discount_total' ? discount_total : null,
            income_tax,
            total_dp,
            tax,
            type
        );
    }

    function btnHistories(val, row) {
        if (row.item_rm_id && row.item_rm_id !== '') {
            var po_no = row.po_no ? row.po_no : '';
            var item_id = row.item_rm_id ? row.item_rm_id : '';
            var spec = row.specification ? row.specification.replace(/'/g, "\\'").replace(/"/g, '&quot;') : '';
            var history = "viewHistories('" + po_no + "', '" + item_id + "', '" + spec + "')";
            return '<a href="javascript:void(0)" class="btn btn-primary w-100" onClick="' + history + '" style="pointer-events: visible; opacity:1; color:white; text-decoration:none;"><i class="fa fa-eye"></i> View</a>';
        }
        return '';
    }

    function viewHistories(po_no, item_rm_id, specification) {
        $("#dlg_history").dialog('open');
        $('#dg_history').datagrid({
            url: '<?= base_url('purchase/purchase_orders/datatableHistories?po_no=') ?>' + btoa(po_no) + "&item_rm_id=" + btoa(item_rm_id) + "&specification=" + btoa(specification),
            pagination: false,
            rownumbers: true,
        });
    }

</script>
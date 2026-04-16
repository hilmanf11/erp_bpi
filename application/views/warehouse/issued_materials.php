<style>
    .scan {
        width: 100%;
        padding: 12px 20px;
        margin: 8px 0;
        display: inline-block;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 40px !important;
    }
</style>
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <!-- <th rowspan="2" field="ck" checkbox="true"></th> -->
            <th rowspan="2" data-options="field:'request_no',halign:'center',width:120">Supplysheet No</th>
            <th rowspan="2" data-options="field:'period',halign:'center',width:100">Period</th>
            <!-- <th rowspan="2" data-options="field:'wp',width:80,halign:'center'">WP</th> -->
            <th rowspan="2" data-options="field:'workorder',width:150,halign:'center'">WO ID</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_rm_no',width:200,halign:'center'">Component No</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Component Name</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center',align:'right',formatter:numberformat"> Quantity</th>
            <th rowspan="2" data-options="field:'warehouse',width:80,align:'center',formatter:numberformat">Stock WHS</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'other',width:80,align:'center',formatter: btnOther">Other</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
        </tr>
        <tr>
            <th data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat,editor:{options:{precision:2, readonly:true}}">Supply</th>
            <th data-options="field:'qty_req',width:80,halign:'center',align:'right',formatter:numberformat">Actual</th>
            <th data-options="field:'balance',width:80,halign:'center',align:'right',formatter:numberformat,styler:numberStyle">Balance <br> WIP</th>
          
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 320px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; padding: 10px;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Scan Barcode</b></legend>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <span style="display:inline-block;">Trans Date : <b><?= date("d F Y") ?></b> | Receive By : <b><?= $this->session->name ?></b></span>
            </div>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="request_no" name="request_no" class="scan" placeholder="SCAN SUPPLY SHEET HERE">
            </div>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="receipt_id" name="receipt_id" class="scan" placeholder="SCAN LABEL HERE">
            </div>
            <div class="fitem" style="padding:0 200px 10px 200px;">
                <a href="javascript:;" class="easyui-linkbutton" onclick="reload()"><i class="fa fa-rotate-right"></i> Reload</a>
                <!-- <a href="javascript:;" class="easyui-linkbutton" onclick="create()"><i class="fa fa-plus"></i> Save</a> -->
            </div>
        </fieldset>
    </div>
</div>

<div id="toolbar2" style="height: 35px;">
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="add2()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="update2()"><i class="fa fa-edit"></i> Update</a>
    <!-- <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="deleted2()"><i class="fa fa-trash"></i> Delete</a> -->
</div>

<!-- DIALOG -->
<div id="dlg_details" class="easyui-dialog" title="Other Material" data-options="closed: true,modal:true" style="width: 1200px; height: 500px; top: 20px; left: 10px;">
    <table id="dg2" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2">
        <thead>
            <tr>
                <th rowspan="2" field="ck" checkbox="true"></th>
                <th rowspan="2" data-options="field:'request_no',width:120,halign:'center'">Request No</th>
                <th rowspan="2" data-options="field:'item_rm_id',width:150,halign:'center'">Part Id</th>
                <th rowspan="2" data-options="field:'part_no',width:150,halign:'center'">Part No</th>
                <th rowspan="2" data-options="field:'part_name',width:150,halign:'center'">Part name</th>
                <th rowspan="2" data-options="field:'qty',width:80,halign:'center'">Qty</th>
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
</div>

<!-- DIALOG SAVE AND UPDATE CUSTOMER ADDRESS -->
<div id="dlg_insert2" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert2" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Request No</span>
                <input style="width:60%;" name="request_no" id="request_number" readonly="true" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No</span>
                <input style="width:60%;" name="item_rm_id" id="item_rm_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:60%;" name="qty" id="qty" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty SH</span>
                <input style="width:60%;" id="qty_sh" class="easyui-textbox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Item ID</span>
                <input style="width:60%;" name="item_rm_id_sh" id="item_rm_id_sh" class="easyui-textbox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Type</span>
                <input style="width:60%;" name="type" id="type" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<audio id="serialDuplicate">
    <source src="<?= base_url('assets/audio/serial_duplicate.mpeg') ?>" type="audio/mpeg">
</audio>
<audio id="serialSuccess">
    <source src="<?= base_url('assets/audio/serial_success.mpeg') ?>" type="audio/mpeg">
</audio>
<audio id="serialNotFound">
    <source src="<?= base_url('assets/audio/serial_notfound.mpeg') ?>" type="audio/mpeg">
</audio>
<audio id="moreThanQty">
    <source src="<?= base_url('assets/audio/more_than_qty.mp3') ?>" type="audio/mpeg">
</audio>
<script>
    function reload() {
        window.location.reload();
    }

    $(function(){
        var lastIndex;
        var dg = $('#dg').datagrid({
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },

            // onBeginEdit: function(rowIndex, row) {
            //     var editors = $('#dg').datagrid('getEditors', rowIndex);
            //     var qty = $(editors[0].target); 
            //     var qtyReqCrusher = $(editors[1].target); 
                
            //     var initialQty = qty.numberbox('getValue');

            //     qtyReqCrusher.numberbox({
            //         onChange: function(newValue, oldValue){
            //             var qtyValue = parseFloat(qty.numberbox('getValue'));
            //             var newQtyValue = qtyValue - (newValue - oldValue);

            //             if (newQtyValue < 0) {
            //                 toastr.warning('Qty cannot be negatives!');
            //                 qtyReqCrusher.numberbox('setValue', 0);
            //                 qty.numberbox('setValue', initialQty);
            //             } else {
            //                 qty.numberbox('setValue', newQtyValue);
            //             }
            //         }
            //     });
            // }
        });
    });


    $(function() {
        //Audio Config
        var serialDuplicate = document.getElementById("serialDuplicate");
        var serialSuccess = document.getElementById("serialSuccess");
        var serialNotFound = document.getElementById("serialNotFound");
        //Scan Supply Sheet
        $('#request_no').focus();
        $('#request_no').keypress(function(e) {
            if (e.which == 13) {
                // Handle spasi awal dan akhir
                var request_no = $(this).val().trim();

                if (request_no === "") {
                    toastr.warning("Request No cannot be empty!", "Warning");
                    return false;
                }
                
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('warehouse/issued_materials/getSupplySheet') ?>",
                    data: "request_no=" + request_no,
                    dataType: "json",
                    success: function(json) {
                        if (json.total > 0) {
                            var row = json.rows;
                            console.log(row);
                            for (let i = 0; i < json.total; i++) {
                                $.ajax({
                                    type: "POST",
                                    url: "<?= base_url('warehouse/issued_materials/create') ?>",
                                    data: {
                                        item_fg_id: row[i].item_fg_id ? row[i].item_fg_id : null,
                                        item_rm_id: row[i].item_rm_id,
                                        request_no: row[i].request_no,
                                        period: row[i].period,
                                        wp: row[i].wp,
                                        workorder: row[i].workorder,
                                        qty: row[i].qty_req,
                                        qty_crusher: row[i].qty_crusher,
                                        qty_purging: row[i].qty_purging
                                    },
                                    dataType: "json",
                                    success: function(result) {
                                        $('#receipt_id').focus();
                                        // if (result.theme == "success") {
                                        //     $('#receipt_id').focus();
                                        //     toastr.success(result.message, result.title);
                                        // } else {
                                        //     toastr.error(result.message, result.title);
                                        // }
                                    }
                                });
                            }
                            $('#dg').datagrid({
                                url: '<?= base_url('warehouse/issued_materials/datatables?request_no=') ?>' + window.btoa(request_no),
                                rownumbers: true
                            });
                        } else {
                            toastr.warning("Supply Sheet not found!");
                            $("#request_no").val('');
                        }
                    }
                });
            }
        });

        //Scan Label
        $('#receipt_id').keypress(function(e) {
            if (e.which == 13) {
                var receipt_id = $(this).val();
                var request_no = $("#request_no").val();
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('warehouse/issued_materials/getPoReceipt') ?>",
                    data: "receipt_id=" + receipt_id + "&request_no=" + request_no,
                    dataType: "json",
                    success: function(json) {
                        console.log(receipt_id);
                        console.log(request_no);
                        if (json.total > 0) {
                            var row = json.rows;
                            console.log(row);
                            for (let i = 0; i < json.total; i++) {
                                $.ajax({
                                    type: "POST",
                                    url: "<?= base_url('warehouse/issued_materials/create_label') ?>",
                                    data: "request_no=" + request_no +
                                        "&label_no=" + receipt_id +
                                        "&item_rm_id=" + row[i].item_rm_id + //item_fg_id
                                        "&type=" + row[i].type +
                                        "&price=" + row[i].price +
                                        "&currency=" + row[i].currency +
                                        "&qty=" + row[i].qty,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            serialSuccess.play();
                                            toastr.success(result.message, result.title);
                                            $("#receipt_id").val('');
                                            $('#receipt_id').focus();
                                        } else {
                                            if (result.title == "Not Scanned In" || result.title == "Not Registered") {
                                                serialNotFound.play();
                                            }else if (result.title == "More Then Qty") {
                                                moreThanQty.play();
                                            } else {
                                                serialDuplicate.play();
                                            }
                                            // toastr.error(result.message, result.title);

                                            Swal.fire({
                                                icon: 'error',
                                                title: result.title || 'FIFO Violation',
                                                html: `<div style="font-size: 20px; font-weight: bold;">${result.message}</div>`,
                                                confirmButtonText: 'OK',
                                                confirmButtonColor: '#d33',
                                                allowOutsideClick: false
                                            });

                                            $("#receipt_id").val('');
                                            $('#receipt_id').focus();
                                        }
                                    }
                                });
                            }


                            // Validasi Auto Posting Journal Inventory
                            const first_row = json.rows[0];
                            const now_date = new Date().toISOString().split('T')[0];
                            validate_eligibility(request_no, first_row.item_rm_id, now_date);


                            if(request_no != ""){
                                $('#dg').datagrid({
                                    url: '<?= base_url('warehouse/issued_materials/datatables?request_no=') ?>' + window.btoa(request_no),
                                    rownumbers: true
                                });
                            }
                            
                        } else {
                            serialNotFound.play();
                            toastr.warning("Label not found!");
                            $("#receipt_id").val('');
                        }
                    }
                });
            }
        });

    });

    //ADD DATA
    function add2() {
        $('#dlg_insert2').dialog('open');
        var request_number = $("#request_number").textbox('getValue');
        var qty_sh = $("#qty_sh").textbox('getValue');
        var item_rm_id_sh = $("#item_rm_id_sh").textbox('getValue');
       
        url_save2 = '<?= base_url('warehouse/issued_materials/create2') ?>';
        // $('#frm_insert2').form('clear');
        $("#qty").textbox('clear');
        $("#item_rm_id").combogrid('clear')

        $("#request_number").textbox('setValue', request_number);
        $("#qty_sh").textbox('setValue', qty_sh);
        $("#item_rm_id_sh").textbox('setValue', item_rm_id_sh);
        $("#type").textbox('setValue', 'Other');
    }

    //UPDATE DATA
    function update2() {
        var row = $('#dg2').datagrid('getSelected');
        if (row) {
            $('#dlg_insert2').dialog('open');
            $('#frm_insert2').form('load', row);
            url_save2 = '<?= base_url('warehouse/issued_materials/update2') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 3
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberStyle(value, row, index) {
        if (value <= 0) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function btnOther(val, row) {
        var others = "others('" + row.request_no + "', '" + row.item_rm_id + "', '" + row.qty + "')";
        return '<a class="btn btn-primary w-100" onClick="' + others + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-plus"></i> Other</a>';
    }

    function others(request_no, item_rm_id, qty) {
        $("#dlg_details").dialog('open');
        $("#request_number").textbox('setValue', request_no);
        $("#qty_sh").textbox('setValue', qty);
        $("#item_rm_id_sh").textbox('setValue', item_rm_id);

        $.ajax({
            url: '<?= base_url('warehouse/issued_materials/getCRItem') ?>',
            method: 'POST',
            data: { item_rm_id: item_rm_id },
            success: function(response) {
                var data = JSON.parse(response);
                var itemIds = [];

                if (data.cr_item) itemIds.push(data.cr_item.item_rm_id);
                if (data.pl_item) itemIds.push(data.pl_item.item_rm_id);
                if (data.vg_item) itemIds.push(data.vg_item.item_rm_id);

                var encodedIds = btoa(JSON.stringify(itemIds));

                // ✅ SET URL dg2
                $('#dg2').datagrid('options').url =
                    '<?= base_url('warehouse/issued_materials/datatables2/') ?>' +
                    btoa(request_no) + '/' + encodedIds;

                $('#dg2').datagrid('reload');

                // ✅ SET URL combogrid
                $('#item_rm_id')
                    .combogrid('grid')
                    .datagrid({
                        url: '<?= base_url('warehouse/issued_materials/readItemRmByIds') ?>/' + encodedIds
                    });

                $('#item_rm_id').combogrid('clear');
            }
        });
    }

    $('#dlg_insert2').dialog({
        buttons: [{
            text: 'Save',
            iconCls: 'icon-ok',
            handler: function() {
                // Ambil nilai qty dan qty_sh dari input
                var qty = parseFloat($('#qty').textbox('getValue'));
                var qty_sh = parseFloat($('#qty_sh').textbox('getValue'));
                
                // Validasi qty
                if (qty > qty_sh) {
                    toastr.error('Quantity cannot be greater than quantity supply sheet.', 'Validation Error');
                    return; // Hentikan eksekusi jika validasi gagal
                }
                
                $('#frm_insert2').form('submit', {
                    url: url_save2,
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
                        $('#dlg_insert2').dialog('close');
                        $('#dg2').datagrid('reload');
                    }
                });
            }
        }]
    });

    $('#item_rm_id').combogrid({
        panelWidth: 400,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Part Other",
        columns: [[
            { field: 'number', title: 'Part No PL/CR/Equivalent', width: 200 },
            { field: 'name', title: 'Part Name', width: 200 }
        ]]
    });

    $('#dg2').datagrid({
        pagination: true,
        clientPaging: false,
        remoteFilter: true,
        rownumbers: true
    }).datagrid('enableFilter');



    /** --- Auto Posting Journal Inventory --- */

    // Validasi Auto Posting Journal
    function validate_eligibility(request_no, item_rm_id, journal_date) {
        $.ajax({
            type: "POST",
            url: "<?= base_url('finance/journal_inventory/validate_posting_eligibility') ?>",
            data: {
                modul: "SUPPLY SHEETS",
                document_no: request_no,
                item_rm_id: item_rm_id,
                journal_date: journal_date
            },
            dataType: "json",
            success: function(response) {

                if (response.status === true) {                    
                    // Auto posting Journal Inventory tanpa validasi
                    exec_autoposting(request_no);
                } else {
                    // Belum layak posting journal
                    toastr.info(response.message, "Failed to Auto Posting Journal");
                }
            },
            error: function(xhr) {
                Swal.close();
                toastr.error("Failed to connect to Auto Posting server");
            }
        });
    }

    // Helper untuk eksekusi Auto Posting Journal Inventory
    function exec_autoposting(document_no) {
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/journal_inventory/execute_auto_journal/') ?>",
            data: { 
                modul: "SUPPLY SHEETS",
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
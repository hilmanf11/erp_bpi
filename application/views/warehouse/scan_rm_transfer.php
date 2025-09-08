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
            <th rowspan="2" data-options="field:'label_no',halign:'center',width:200">Serial No</th>
            <th rowspan="2" data-options="field:'item_rm_id',halign:'center',width:200">Part Id</th>
            <th rowspan="2" data-options="field:'item_number',width:200,halign:'center'">Part No</th>
            <th rowspan="2" data-options="field:'item_name',width:250,halign:'center'">Part Name</th>
            <th rowspan="2" data-options="field:'qty',width:60,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th rowspan="2" data-options="field:'uom',width:60,align:'center'">UoM</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 450px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; padding: 10px;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Scan Barcode</b></legend>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">From</span>
                <input style="width:20%;" id="transfer_from" class="easyui-combobox">
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">Transfer To</span>
                <input style="width:20%;" id="transfer_to" class="easyui-combobox">
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">Transaction Date</span>
                <input style="width:20%;" id="transaction_date" class="easyui-datebox" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">Cutoff</span>
                <input style="width:20%;" id="cutoff" class="easyui-datebox" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">Division</span>
                <input style="width:20%;" id="division" class="easyui-combobox">
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">Ship By</span>
                <select style="width:20%;" name="ship_by" id="ship_by" class="easyui-combobox" panelHeight="auto">
                    <option value="">Choose Ship By</option>
                    <option value="SEA">SEA</option>
                    <option value="AIR">AIR</option>
                    <option value="TRUCK">TRUCK</option>
                </select>
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">Remarks</span>
                <input style="width:20%;" id="remarks" class="easyui-textbox">
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="label_no" name="label_no" class="scan" placeholder="SCAN LABEL HERE">
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <a href="javascript:;" class="easyui-linkbutton" onclick="reload()"><i class="fa fa-rotate-right"></i> Reload</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="save()"><i class="fa fa-floppy-o"></i> Save</a>
            </div>
        </fieldset>
    </div>
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
<script>
    function reload() {
        window.location.reload();
    }

    var scannedData = [];
    // $('#label_no').keypress(function(e) {
    //     if (e.which == 13) {
    //         var label_no = $("#label_no").val().trim();

    //         // 1. Cek apakah sudah ada di array (scannedData)
    //         var exists = scannedData.some(function(item) {
    //             return item.label_no === label_no;
    //         });

    //         if (exists) {
    //             serialDuplicate.play();
    //             toastr.error("Label has been scanned before!", "Duplicate");
    //             $("#label_no").val('').focus();
    //             return; // hentikan proses
    //         }

    //         // 2. Cek ke database apakah label sudah pernah tersimpan
    //         $.ajax({
    //             type: "POST",
    //             url: "<?= base_url('warehouse/scan_rm_transfer/checkLabel') ?>", // bikin endpoint baru
    //             data: { label_no: label_no },
    //             dataType: "json",
    //             success: function(res) {
    //                 if (res.exists) {
    //                     serialDuplicate.play();
    //                     toastr.error("Label has been scanned before!", "Duplicate");
    //                     $("#label_no").val('').focus();
    //                 } else {
    //                     // Kalau aman, baru ambil datanya
    //                     $.ajax({
    //                         type: "POST",
    //                         url: "<?= base_url('warehouse/scan_rm_transfer/getPoReceipt') ?>",
    //                         data: { label_no: label_no },
    //                         dataType: "json",
    //                         success: function(json) {
    //                             if (json.total > 0) {
    //                                 var row = json.rows;
    //                                 for (let i = 0; i < json.total; i++) {
    //                                     scannedData.push({
    //                                         label_no: label_no,
    //                                         receipt_no: row[i].receipt_no,
    //                                         receipt_id: row[i].receipt_id,
    //                                         po_no: row[i].po_no,
    //                                         item_rm_id: row[i].item_rm_id,
    //                                         item_name: row[i].item_name,
    //                                         item_number: row[i].item_number,
    //                                         uom: row[i].uom,
    //                                         qty: row[i].qty
    //                                     });
    //                                 }

    //                                 // Refresh datagrid
    //                                 $('#dg').datagrid({
    //                                     data: scannedData,
    //                                     rownumbers: true
    //                                 });

    //                                 serialSuccess.play();
    //                                 toastr.success("Label scanned", "Success");
    //                                 $("#label_no").val('').focus();
    //                             } else {
    //                                 serialNotFound.play();
    //                                 toastr.warning("Label not found!");
    //                                 $("#label_no").val('').focus();
    //                             }
    //                         }
    //                     });
    //                 }
    //             }
    //         });
    //     }
    // });

    $('#label_no').keypress(function(e) {
        if (e.which == 13) {
            var label_no = $("#label_no").val().trim();
            var cutoff   = $("#cutoff").datebox('getValue');
            var transfer_from   = $("#transfer_from").combobox('getValue');
            var transfer_to   = $('#transfer_to').combobox('getValue');

            // cegah from==to
            if (transfer_from === transfer_to) {
                toastr.error("Transfer From dan Transfer To Cannot Be Same");
                $("#label_no").val('').focus();
                return;
            }

            // 1. Cek apakah sudah ada di array (scannedData)
            var exists = scannedData.some(function(item) {
                return item.label_no === label_no;
            });

            if (exists) {
                serialDuplicate.play();
                toastr.error("Label has been scanned before!", "Duplicate");
                $("#label_no").val('').focus();
                return;
            }

            // 2. Cek ke database apakah label sudah pernah tersimpan
            $.ajax({
                type: "POST",
                url: "<?= base_url('warehouse/scan_rm_transfer/checkLabel') ?>",
                data: { label_no: label_no,
                    transfer_from: transfer_from
                 },
                dataType: "json",
                success: function(res) {
                    if (!res.valid) {
                        serialDuplicate.play();
                        toastr.error("Label has been scanned before!", "Duplicate");
                        $("#label_no").val('').focus();
                    } else {
                        // 3. Ambil data PO Receipt
                        $.ajax({
                            type: "POST",
                            url: "<?= base_url('warehouse/scan_rm_transfer/getPoReceipt') ?>",
                            data: { label_no: label_no },
                            dataType: "json",
                            success: function(json) {
                                if (json.total > 0) {
                                    var row = json.rows[0];

                                    // 4. Cek ending balance dengan cutoff
                                    $.ajax({
                                        type: "POST",
                                        url: "<?= base_url('warehouse/scan_rm_transfer/checkEndingBalance') ?>",
                                        data: { 
                                            item_rm_id: row.item_rm_id,
                                            cutoff: cutoff
                                        },
                                        dataType: "json",
                                        success: function(stock) {
                                            if (stock.ending_stock <= 0) {
                                                // pastikan dihapus kalau sempat masuk scannedData
                                                scannedData = scannedData.filter(function(item) {
                                                    return item.item_rm_id !== row.item_rm_id;
                                                });

                                                $('#dg').datagrid({
                                                    data: scannedData,
                                                    rownumbers: true
                                                });

                                                // serialNotFound.play();
                                                toastr.warning("Stock for item " + row.item_number + " is 0 at cutoff " + cutoff + ", data cleared!");
                                                $("#label_no").val('').focus();
                                            } else {
                                                scannedData.push({
                                                    label_no: label_no,
                                                    receipt_no: row.receipt_no,
                                                    receipt_id: row.receipt_id,
                                                    po_no: row.po_no,
                                                    item_rm_id: row.item_rm_id,
                                                    item_name: row.item_name,
                                                    item_number: row.item_number,
                                                    uom: row.uom,
                                                    qty: row.qty
                                                });

                                                $('#dg').datagrid({
                                                    data: scannedData,
                                                    rownumbers: true
                                                });

                                                serialSuccess.play();
                                                toastr.success("Label scanned", "Success");
                                                $("#label_no").val('').focus();
                                            }
                                        }
                                    });

                                } else {
                                    serialNotFound.play();
                                    toastr.warning("Label not found!");
                                    $("#label_no").val('').focus();
                                }
                            }
                        });
                    }
                }
            });
        }
    });

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
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

    $('#division').combobox({
        url: '<?= base_url('warehouse/sto_raw_materials/readsDivision/'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: 'Choose Division',
        panelHeight: 'auto',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#transfer_from').combobox({
        url: '<?= base_url('warehouse/scan_rm_transfer/readArea/'); ?>',
        valueField: 'area',
        textField: 'area',
        prompt: 'Choose Transfer From',
        panelHeight: 'auto',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#transfer_to').combobox({
        url: '<?= base_url('warehouse/scan_rm_transfer/readArea/'); ?>',
        valueField: 'area',
        textField: 'area',
        prompt: 'Choose Transfer To',
        panelHeight: 'auto',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    function save() {
        if (scannedData.length === 0) {
            toastr.warning("No data to save!");
            return;
        }

        var headerData = {
            transfer_from: $('#transfer_from').combobox('getValue'),
            transfer_to: $('#transfer_to').combobox('getValue'),
            transaction_date: $('#transaction_date').datebox('getValue'),
            cutoff: $('#cutoff').datebox('getValue'),
            division: $('#division').combobox('getValue'),
            ship_by: $('#ship_by').combobox('getValue'),
            remarks: $('#remarks').textbox('getValue'),
        };

        $.ajax({
            type: "POST",
            url: "<?= base_url('warehouse/scan_rm_transfer/saveTransfer') ?>",
            data: {
                header: headerData,
                details: JSON.stringify(scannedData)
            },
            dataType: "json",
            success: function(result) {
                if (result.status === "success") {
                    toastr.success(result.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                    scannedData = [];
                    $('#dg').datagrid({ data: [] });
                } else {
                    toastr.error(result.message);
                }
            }
        });
    }

</script>
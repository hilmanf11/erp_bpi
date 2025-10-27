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
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'request_no',halign:'center',width:150">Request No</th>
            <th rowspan="2" data-options="field:'request_id',width:200,halign:'center'">Request Id</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Part No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Part Name</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat, styler:numberStyle"> Qty</th>
            <th rowspan="2" data-options="field:'qty_crusher',width:100,halign:'center',align:'right',formatter:numberformat, styler:numberStyle2"> Crusher Qty</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
        </tr>
        <tr>
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
                <input style="width:100%; height: 80px;" type="text" id="request_no" name="request_no" class="scan" placeholder="SCAN DOC NO HERE">
            </div>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="label" name="label" class="scan" placeholder="SCAN LABEL HERE">
            </div>
            <div class="fitem" style="padding:0 200px 10px 200px;">
                <a href="javascript:;" class="easyui-linkbutton" onclick="reload()"><i class="fa fa-rotate-right"></i> Reload</a>
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
<audio id="moreThanQty">
    <source src="<?= base_url('assets/audio/more_than_qty.mp3') ?>" type="audio/mpeg">
</audio>
<script>
    function reload() {
        window.location.reload();
    }
    $(function() {
        // Audio Config
        var serialDuplicate = document.getElementById("serialDuplicate");
        var serialSuccess = document.getElementById("serialSuccess");
        var serialNotFound = document.getElementById("serialNotFound");

        // Scan
        $('#request_no').focus();
        $('#request_no').keypress(function(e) {
            if (e.which == 13) {
                var request_no = $(this).val();

                Swal.fire({
                    title: 'Please Wait Checking Doc No Crusher',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "GET",
                    url: "<?= base_url('warehouse/scan_item_receipt_crusher/getDeliveryOrders') ?>",
                    data: "request_no=" + request_no,
                    dataType: "json",
                    success: function(json) {
                        Swal.close();
                        if (json.total > 0) {

                            $('#dg').datagrid({
                                url: '<?= base_url('warehouse/scan_item_receipt_crusher/getDeliveryOrders?request_no=') ?>' + request_no,
                                rownumbers: true
                            });

                            $("#label").focus();
                        } else {
                            toastr.warning("Doc No Crusher not found!");
                            $("#request_no").val('');
                            $("#request_no").focus();
                        }
                    }
                });
            }
        });

        // Scan Label
        $('#label').keypress(function(e) {
            if (e.which == 13) {
                var label = $(this).val();
                var request_no = $("#request_no").val();

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('warehouse/scan_item_receipt_crusher/getChecksheetLabel') ?>",
                    data: "label=" + label + "&request_no=" + request_no,
                    dataType: "json",
                    success: function(json) {
                        // console.log(json);
                        if (json.total > 0) {
                            var row = json.rows;
                            for (let i = 0; i < json.total; i++) {
                                $.ajax({
                                    type: "POST",
                                    url: "<?= base_url('warehouse/scan_item_receipt_crusher/create') ?>",
                                    data: {
                                        label: label,
                                        request_no: request_no,
                                        item_rm_id: row[i].item_rm_id,
                                        request_id: row[i].request_id,
                                        request_date: row[i].request_date,
                                        qty: row[i].qty
                                    },
                                    dataType: "json",
                                    success: function(result) {
                                        console.log(result);
                                        if (result.theme == "success") {
                                            serialSuccess.play();
                                            toastr.success(result.message, result.title);
                                            $("#label").val('');
                                            $('#label').focus();
                                        } else {
                                            if (result.title == "Not Scanned In" || result.title == "Not Registered") {
                                                serialNotFound.play();
                                            }else if (result.title == "More Then Qty") {
                                                moreThanQty.play();
                                            }else if (result.title == "Available") {
                                                serialDuplicate.play();
                                            } else {
                                                // serialDuplicate.play();
                                            }
                                            toastr.error(result.message, result.title);
                                            $("#label").val('');
                                            $('#label').focus();
                                        }
                                    }
                                });
                            }

                            $('#dg').datagrid({
                                url: '<?= base_url('warehouse/scan_item_receipt_crusher/getDeliveryOrders?request_no=') ?>' + request_no,
                                rownumbers: true
                            });
                        } else {
                            serialNotFound.play();
                            toastr.warning("Label not found!");
                            $("#label").val('');
                            $('#label').focus();
                        }
                    }
                });
            }
        });
    });

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
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

    function numberStyle2(value, row, index) {
        if (row.qty == row.qty_crusher) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }
</script>
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
            <th rowspan="2" data-options="field:'period',halign:'center',width:100">Period</th>
            <th rowspan="2" data-options="field:'wp',width:80,halign:'center'">WP</th>
            <th rowspan="2" data-options="field:'workorder',width:150,halign:'center'">WO ID</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'component_number',width:200,halign:'center'">Component No</th>
            <th rowspan="2" data-options="field:'component_name',width:200,halign:'center'">Component Name</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center',align:'right',formatter:numberformat"> Quantity</th>
            <th rowspan="2" data-options="field:'warehouse',width:80,align:'center',formatter:numberformat">Stock WHS</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
        </tr>
        <tr>
            <th data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">Supply</th>
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
    $(function() {
        //Audio Config
        var serialDuplicate = document.getElementById("serialDuplicate");
        var serialSuccess = document.getElementById("serialSuccess");
        var serialNotFound = document.getElementById("serialNotFound");
        //Scan Supply Sheet
        $('#request_no').focus();
        $('#request_no').keypress(function(e) {
            if (e.which == 13) {
                var request_no = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('warehouse/issued_materials/getSupplySheet') ?>",
                    data: "request_no=" + request_no,
                    dataType: "json",
                    success: function(json) {
                        if (json.total > 0) {
                            var row = json.rows;
                            for (let i = 0; i < json.total; i++) {
                                $.ajax({
                                    type: "POST",
                                    url: "<?= base_url('warehouse/issued_materials/create') ?>",
                                    data: "item_fg_id=" + row[i].item_fg_id +
                                        "&item_rm_id=" + row[i].item_rm_id +
                                        "&request_no=" + row[i].request_no +
                                        "&period=" + row[i].period +
                                        "&wp=" + row[i].wp +
                                        "&workorder=" + row[i].workorder +
                                        "&qty=" + row[i].qty_issued,
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
                        if (json.total > 0) {
                            var row = json.rows;
                            for (let i = 0; i < json.total; i++) {
                                $.ajax({
                                    type: "POST",
                                    url: "<?= base_url('warehouse/issued_materials/create_label') ?>",
                                    data: "request_no=" + request_no +
                                        "&label_no=" + receipt_id +
                                        "&item_fg_id=" + row[i].item_fg_id +
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
                                            } else {
                                                serialDuplicate.play();
                                            }
                                            toastr.error(result.message, result.title);
                                            $("#receipt_id").val('');
                                            $('#receipt_id').focus();
                                        }
                                    }
                                });
                            }

                            $('#dg').datagrid({
                                url: '<?= base_url('warehouse/issued_materials/datatables?request_no=') ?>' + window.btoa(request_no),
                                rownumbers: true
                            });
                            
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
</script>
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
            <th rowspan="2" data-options="field:'checksheet_number',halign:'center',width:150">Label No</th>
            <th rowspan="2" data-options="field:'so_number',width:150,halign:'center'">SO NO</th>
            <th rowspan="2" data-options="field:'workorder',width:150,halign:'center'">Workorder</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat, styler:numberStyle"> Qty</th>
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
                <input style="width:100%; height: 80px;" type="text" id="checksheet_number" name="checksheet_number" class="scan" placeholder="SCAN CHECKSHEET HERE">
            </div>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="checksheet_label" name="checksheet_label" class="scan" placeholder="SCAN LABEL HERE">
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
        $('#checksheet_number').focus();
        $('#checksheet_number').keypress(function(e) {
            if (e.which == 13) {
                var checksheet_number = $(this).val();

                $.ajax({
                    type: "GET",
                    url: "<?= base_url('warehouse/item_receipts_fg/getChecksheets') ?>",
                    data: "checksheet_number=" + checksheet_number,
                    dataType: "json",
                    success: function(json) {
                        if (json.total > 0) {
                            $('#dg').datagrid({
                                url: '<?= base_url('warehouse/item_receipts_fg/getChecksheets?checksheet_number=') ?>' + checksheet_number,
                                rownumbers: true
                            });

                            $("#checksheet_label").focus();
                        } else {
                            toastr.warning("Checksheet not found!");
                            $("#checksheet_number").val('');
                            $("#checksheet_number").focus();
                        }
                    }
                });
            }
        });
        //Scan Label
        $('#checksheet_label').keypress(function(e) {
            if (e.which == 13) {
                var checksheet_label = $(this).val();
                var checksheet_number = $("#checksheet_number").val();
                var split = checksheet_label.split("|");

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('warehouse/item_receipts_fg/getChecksheetLabel') ?>",
                    data: "checksheet_label=" + split[3] + "&checksheet_number=" + checksheet_number,
                    dataType: "json",
                    success: function(json) {
                        if (json.total > 0) {
                            var row = json.rows;
                            for (let i = 0; i < json.total; i++) {
                                $.ajax({
                                    type: "POST",
                                    url: "<?= base_url('warehouse/item_receipts_fg/create') ?>",
                                    data: "checksheet_label=" + split[3] +
                                        "&checksheet_number=" + checksheet_number +
                                        "&so_number=" + row[i].so_number +
                                        "&workorder=" + row[i].workorder +
                                        "&qty=" + row[i].qty,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            serialSuccess.play();
                                            toastr.success(result.message, result.title);
                                            $("#checksheet_label").val('');
                                            $('#checksheet_label').focus();
                                        } else {
                                            if (result.title == "Not Scanned In" || result.title == "Not Registered") {
                                                serialNotFound.play();
                                            } else {
                                                serialDuplicate.play();
                                            }
                                            toastr.error(result.message, result.title);
                                            $("#checksheet_label").val('');
                                            $('#checksheet_label').focus();
                                        }
                                    }
                                });
                            }

                            $('#dg').datagrid({
                                url: '<?= base_url('warehouse/item_receipts_fg/getChecksheets?checksheet_number=') ?>' + checksheet_number,
                                rownumbers: true
                            });
                        } else {
                            serialNotFound.play();
                            toastr.warning("Label not found!");
                            $("#checksheet_label").val('');
                            $('#checksheet_label').focus();
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
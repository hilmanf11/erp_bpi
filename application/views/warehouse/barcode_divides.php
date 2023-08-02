<style>
    #label_no {
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
<div id="p" class="easyui-panel" title="Barcode Divides" style="width:100%;padding:10px;background:#fafafa;" data-options="closable:true,collapsible:true">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Scan Barcode</b></legend>
            <div class="fitem" style="padding:10px 200px 0 200px;">
                <span style="display:inline-block;">Trans Date : <b><?= date("d F Y") ?></b> | Receive By : <b><?= $this->session->name ?></b></span>
            </div>
            <div class="fitem" style="padding:10px 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="label_no" name="label_no" placeholder="SCAN SERIAL HERE">
            </div>
            <div class="fitem" style="padding:10px 200px 10px 200px;">
                <a href="javascript:;" class="easyui-linkbutton" onclick="reload()"><i class="fa fa-rotate-right"></i> Reload</a>
            </div>
        </fieldset>
    </div>
    <div style="width: 45%; float: left; margin-right: 10px;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Parent Serial</b></legend>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Serial No</span>
                <input style="width:70%;" id="label_no_2" class="easyui-textbox" disabled>
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Product No</span>
                <input style="width:70%;" id="item_number" class="easyui-textbox" disabled>
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Product Name</span>
                <input style="width:70%;" id="item_name" class="easyui-textbox" disabled>
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Qty</span>
                <input style="width:70%;" id="qty" value="0" class="easyui-numberbox" data-options="precision:2" disabled>
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Supply</span>
                <input style="width:70%;" id="supply" value="0" class="easyui-numberbox" data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Balance</span>
                <input style="width:70%;" id="balance" value="0" class="easyui-numberbox" data-options="precision:2" disabled>
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="saved()"><i class="fa fa-save"></i> Save </a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print()"><i class="fa fa-print"></i> Print Label </a>
            </div>
        </fieldset>
    </div>
    <div style="width: 53%; float: left;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Preview Barcode Label</b></legend>
            <iframe id="printout" src="" style="width: 100%; height: 250px; border: 0;"></iframe>
        </fieldset>
    </div>
</div>
<script>
    function reload() {
        window.location.reload();
    }
    $(function() {
        $('#label_no').focus();
        $('#label_no').keypress(function(e) {
            if (e.which == 13) {
                var label_no = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('warehouse/barcode_divides/getSerial') ?>",
                    data: "label_no=" + label_no,
                    dataType: "json",
                    success: function(json) {
                        if (json.total > 0) {
                            var row = json.rows;
                            $("#label_no").val('');
                            $("#label_no_2").textbox('setValue', label_no);
                            $("#item_number").textbox('setValue', row[0].number);
                            $("#item_name").textbox('setValue', row[0].name);
                            $("#qty").numberbox('setValue', row[0].qty);
                            $("#supply").numberbox('setValue', row[0].qty);
                            $("#balance").numberbox('setValue', 0);
                            toastr.success("Serial Found");
                        } else {
                            toastr.warning("Purchase Order Receipt not found!");
                            $("#label_no").val('');
                        }
                    }
                });
            }
        });
        $('#supply').numberbox({
            onChange: function(value) {
                var qty = $("#qty").numberbox("getValue");
                var balance = $("#balance").numberbox("getValue");
                if (parseFloat(qty) == 0) {
                    toastr.error("Please Scan Serial First!");
                } else if (parseFloat(value) < 0) {
                    toastr.error("Qty Supply cannot < 0!");
                } else {
                    $("#balance").numberbox('setValue', parseFloat(qty) - parseFloat(value));
                }
            }
        });
    });

    function saved() {
        var label_no = $("#label_no_2").textbox('getValue');
        var supply = $("#supply").numberbox('getValue');
        var balance = $("#balance").numberbox('getValue');
        $.ajax({
            type: "POST",
            url: "<?= base_url('warehouse/barcode_divides/create') ?>",
            data: "label_no=" + label_no +
                "&qty=" + supply +
                "&bal=" + balance,
            dataType: "json",
            success: function(result) {
                if (result.theme == "success") {
                    toastr.success(result.message, result.title);
                } else {
                    toastr.error(result.message, result.title);
                }
                var url = "?label_no=" + window.btoa(label_no);
                $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
                $("#printout").attr('src', '<?= base_url('warehouse/barcode_divides/print') ?>' + url);
            }
        });
    }

    function print() {
        $("#printout").get(0).contentWindow.print();
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }
</script>
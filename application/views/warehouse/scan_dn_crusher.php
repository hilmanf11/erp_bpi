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
<div id="toolbar" style="height: 400px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; padding: 10px;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Scan Barcode</b></legend>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">Customer</span>
                <input style="width:20%;" id="customer_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">Plant</span>
                <input style="width:20%;" id="plant" required="" class="easyui-combogrid">
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">Division</span>
                <input style="width:20%;" id="division" required="" class="easyui-combobox">
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">Transaction Date</span>
                <input style="width:20%;" id="transaction_date" class="easyui-datebox" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
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

    $('#label_no').keypress(function(e) {
        if (e.which == 13) {
            var label_no = $("#label_no").val().trim();

            console.log(label_no);
            
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
                url: "<?= base_url('warehouse/scan_dn_crusher/checkLabel') ?>",
                data: { label_no: label_no },
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
                            url: "<?= base_url('warehouse/scan_dn_crusher/getPoReceipt') ?>",
                            data: { label_no: label_no },
                            dataType: "json",
                            success: function(json) {
                                if (json.total > 0) {
                                    var row = json.rows[0];

                                    // langsung push data tanpa cek ending balance
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

    $('#customer_id').combogrid({
        url: '<?= base_url('master/customers/reads/'); ?>',
        panelWidth: 300,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Customer",
        columns: [
            [{
                field: 'number',
                title: 'Customer Code',
                width: 120
            }, {
                field: 'name',
                title: 'Customer Name',
                width: 250
            }, ]
        ],
        onSelect: function(value, rows) {
            $('#plant').combogrid({
                url: '<?= base_url('master/customer_items/readPlant/'); ?>' + window.btoa(rows.id),
                panelWidth: 420,
                idField: 'plant',
                textField: 'plant',
                mode: 'remote',
                fitColumns: true,
                prompt: 'Choose Plant',
                columns: [
                    [{
                        field: 'plant',
                        title: 'Plant',
                        width: 120
                    }, {
                        field: 'address',
                        title: 'Address',
                        width: 300
                    },]
                ]
            });
        }
    });

    function save() {
        if (scannedData.length === 0) {
            toastr.warning("No data to save!");
            return;
        }

        var headerData = {
            customer_id: $('#customer_id').combogrid('getValue'),
            plant: $('#plant').combogrid('getValue'),
            transaction_date: $('#transaction_date').datebox('getValue'),
            division: $('#division').combobox('getValue'),
            remarks: $('#remarks').textbox('getValue'),
        };

        $.ajax({
            type: "POST",
            url: "<?= base_url('warehouse/scan_dn_crusher/saveTransfer') ?>",
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
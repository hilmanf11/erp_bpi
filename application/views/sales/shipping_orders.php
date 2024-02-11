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
            <th rowspan="2" data-options="field:'delivery_order_no',halign:'center',width:150">Delivery Order No</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer</th>
            <th rowspan="2" data-options="field:'trans_type',width:80,align:'center'">Type</th>
            <th rowspan="2" data-options="field:'item_fg_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'delivery',width:100,halign:'center',align:'right',formatter:numberformat, styler:numberStyle"> Delivery</th>
            <th rowspan="2" data-options="field:'shipping',width:100,halign:'center',align:'right',formatter:numberformat, styler:numberStyle2"> Shipping</th>
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
                <input style="width:100%; height: 80px;" type="text" id="delivery_order_no" name="delivery_order_no" class="scan" placeholder="SCAN DELIVERY ORDER HERE">
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
        $('#delivery_order_no').focus();
        $('#delivery_order_no').keypress(function(e) {
            if (e.which == 13) {
                var delivery_order_no = $(this).val();

                Swal.fire({
                    title: 'Please Wait Checking Delivery Order',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "GET",
                    url: "<?= base_url('sales/shipping_orders/getDeliveryOrders') ?>",
                    data: "delivery_order_no=" + delivery_order_no,
                    dataType: "json",
                    success: function(json) {
                        Swal.close();
                        if (json.total > 0) {
                            $('#dg').datagrid({
                                url: '<?= base_url('sales/shipping_orders/getDeliveryOrders?delivery_order_no=') ?>' + delivery_order_no,
                                rownumbers: true
                            });

                            $("#checksheet_label").focus();
                        } else {
                            toastr.warning("Checksheet not found!");
                            $("#delivery_order_no").val('');
                            $("#delivery_order_no").focus();
                        }
                    }
                });
            }
        });
        //Scan Label
        $('#checksheet_label').keypress(function(e) {
            if (e.which == 13) {
                var checksheet_label = $(this).val();
                var delivery_order_no = $("#delivery_order_no").val();

                toastr.info("Still Maintenance");
                // $.ajax({
                //     type: "POST",
                //     url: "<?= base_url('sales/shipping_orders/getChecksheetLabel') ?>",
                //     data: "checksheet_label=" + checksheet_label + "&delivery_order_no=" + delivery_order_no,
                //     dataType: "json",
                //     success: function(json) {
                //         if (json.total > 0) {
                //             var row = json.rows;
                //             for (let i = 0; i < json.total; i++) {
                //                 $.ajax({
                //                     type: "POST",
                //                     url: "<?= base_url('sales/shipping_orders/create') ?>",
                //                     data: "checksheet_label=" + checksheet_label +
                //                         "&delivery_order_no=" + delivery_order_no +
                //                         "&sales_order_no=" + row[i].sales_order_no +
                //                         "&customer_order_no=" + row[i].customer_order_no +
                //                         "&delivery=" + row[i].delivery +
                //                         "&qty=" + row[i].qty,
                //                     dataType: "json",
                //                     success: function(result) {
                //                         if (result.theme == "success") {
                //                             serialSuccess.play();
                //                             toastr.success(result.message, result.title);
                //                             $("#checksheet_label").val('');
                //                             $('#checksheet_label').focus();
                //                         } else {
                //                             if (result.title == "Not Scanned In" || result.title == "Not Match") {
                //                                 serialNotFound.play();
                //                             } else {
                //                                 serialDuplicate.play();
                //                             }
                //                             toastr.error(result.message, result.title);
                //                             $("#checksheet_label").val('');
                //                             $('#checksheet_label').focus();
                //                         }
                //                     }
                //                 });
                //             }

                //             $('#dg').datagrid({
                //                 url: '<?= base_url('sales/shipping_orders/getDeliveryOrders?delivery_order_no=') ?>' + delivery_order_no,
                //                 rownumbers: true
                //             });
                //         } else {
                //             serialNotFound.play();
                //             toastr.warning("Label not found!");
                //             $("#checksheet_label").val('');
                //             $('#checksheet_label').focus();
                //         }
                //     }
                // });
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
        if (row.delivery > row.shipping) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }
</script>
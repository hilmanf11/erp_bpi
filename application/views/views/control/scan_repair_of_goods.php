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
            <th rowspan="2" data-options="field:'document_no',halign:'center',width:150">Document No</th>
            <th rowspan="2" data-options="field:'checksheet_label',halign:'center',width:150">Serial No</th>
            <!-- <th rowspan="2" data-options="field:'checksheet_label',halign:'center',width:150">Checksheet Label</th> -->
            <th rowspan="2" data-options="field:'lot_no',width:150,halign:'center'">Lot No</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat, styler:numberStyle"> Qty</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <!-- <th rowspan="2" data-options="field:'detail',width:80,align:'center',formatter: btnDetails">Details</th> -->
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
            <div class="fitem" style="padding:0 200px 0 200px;" hidden>
                <input style="width:100%; height: 80px;" type="text" id="checksheet_number" name="checksheet_number" class="scan" placeholder="SCAN DOCUMENT NO HERE">
            </div>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="document_no" name="document_no" class="scan" placeholder="SCAN DOCUMENT NO HERE">
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

<!-- DIALOG DETAILS -->
<div id="dlg_details" class="easyui-dialog" title="Label List" data-options="closed: true,modal:true" style="width: 800px; height: 500px; top: 20px; left: 10px;">
    <table id="dg2" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <!-- <th rowspan="2" field="ck" checkbox="true"></th> -->
                <th rowspan="2" data-options="field:'checksheet_label',width:150,halign:'center'">Label No</th>
                <th rowspan="2" data-options="field:'qty',width:100,halign:'center'">Qty</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            </tr>
            <tr>
                <th data-options="field:'created_by',width:100,align:'center'"> By</th>
                <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            </tr>
        </thead>
    </table>
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

        $('#document_no').focus();
        $('#document_no').keypress(function(e) {
            if (e.which == 13) {
                var document_no = $(this).val();

                $.ajax({
                    type: "GET",
                    url: "<?= base_url('control/scan_repair_of_goods/getDocumentNo') ?>",
                    data: "document_no=" + document_no,
                    dataType: "json",
                    success: function(json) {
                        console.log(json);
                        if (json.rows.length > 0) {
                            // $('#dg').datagrid({
                            //     url: '<?= base_url('warehouse/item_receipts_fg/getDocumentNo?document_no=') ?>' + document_no,
                            //     rownumbers: true
                            // });

                            $("#checksheet_label").focus();
                        } else {
                            toastr.warning("Document No not found!");
                            $("#document_no").val('');
                            $("#document_no").focus();
                        }
                    }
                });
            }
        });

        // $('#document_no').focus();
        // $('#document_no').keypress(function(e) {
        //     if (e.which == 13) {
        //         var document_no = $(this).val();

        //         // Cek terlebih dahulu apakah ada pending document
        //         $.ajax({
        //             type: "GET",
        //             url: "<?= base_url('warehouse/item_receipts_fg/checkPendingDocument') ?>",
        //             data: "document_no=" + document_no,
        //             dataType: "json",
        //             success: function(json) {
        //                 if (json.error) {
        //                     Swal.fire({
        //                         title: 'Please Scan Previous Document No,',
        //                         text: json.error + " Insert the Password to Continue if Urgent:",
        //                         input: 'password',
        //                         inputPlaceholder: 'Input Password',
        //                         showCancelButton: true,
        //                         confirmButtonText: 'Submit',
        //                         preConfirm: (password) => {
        //                             return new Promise((resolve, reject) => {
        //                                 if (!password) {
        //                                     reject('Password Empty!');
        //                                 } else {
        //                                     resolve(password);
        //                                 }
        //                             });
        //                         }
        //                     }).then((result) => {
        //                         if (result.isConfirmed) {
        //                             var password = result.value;
        //                             var encodedPassword = window.btoa(password);
        //                             // Kirim password ke server untuk diverifikasi
        //                             $.ajax({
        //                                 type: 'POST',
        //                                 url: '<?= base_url('warehouse/item_receipts_fg/checkPassword') ?>',
        //                                 data: { password: encodedPassword },
        //                                 dataType: 'json',
        //                                 success: function(response) {
        //                                     if (response.success) {
        //                                         // Jika password benar, lanjutkan memproses getDocumentNo
        //                                         getDocumentDetails(document_no);
        //                                     } else {
        //                                         Swal.fire({
        //                                             icon: 'error',
        //                                             title: 'Password Salah!',
        //                                             text: 'Silakan coba lagi.'
        //                                         });
        //                                     }
        //                                 }
        //                             });
        //                         }
        //                     });
        //                 } else {
        //                     // Tidak ada pending document, lanjutkan langsung ke getDocumentNo
        //                     getDocumentDetails(document_no);
        //                 }
        //             }
        //         });

        //     }
        // });

        // // Fungsi untuk memanggil getDocumentNo
        // function getDocumentDetails(document_no) {
        //     $.ajax({
        //         type: "GET",
        //         url: "<?= base_url('warehouse/item_receipts_fg/getDocumentNo') ?>",
        //         data: "document_no=" + document_no,
        //         dataType: "json",
        //         success: function(json) {
        //             if (json.rows.length > 0) {
        //                 // Tampilkan data pada datagrid
        //                 $('#dg').datagrid({
        //                     url: '<?= base_url('warehouse/item_receipts_fg/getDocumentNo?document_no=') ?>' + document_no,
        //                     rownumbers: true
        //                 });
        //                 $("#checksheet_label").focus();
        //             } else {
        //                 Swal.fire({
        //                     icon: 'warning',
        //                     title: 'Document No not found!',
        //                     text: 'Document No tidak ditemukan, silakan coba lagi.'
        //                 });
        //                 $("#document_no").val('');
        //                 $("#document_no").focus();
        //             }
        //         }
        //     });
        // }

        //Scan Label
        $('#checksheet_label').keypress(function(e) {
            if (e.which == 13) {
                var checksheet_label = $(this).val();
                var document_no = $("#document_no").val();

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('control/scan_repair_of_goods/getChecksheetLabel') ?>",
                    data: "checksheet_label=" + checksheet_label + "&document_no=" + document_no,
                    dataType: "json",
                    success: function(json) {
                        console.log(json);
                        if (json.total > 0) {
                            var row = json.rows;
                            console.log(row);
                            for (let i = 0; i < json.total; i++) {
                                $.ajax({
                                    type: "POST",
                                    url: "<?= base_url('control/scan_repair_of_goods/create') ?>",
                                    data: "checksheet_label=" + checksheet_label +
                                        "&document_no=" + document_no +
                                        "&checksheet_number=" + row[i].checksheet_number +
                                        "&item_fg_id=" + row[i].item_fg_id +
                                        "&packing_date=" + row[i].packing_date +
                                        "&type=" + row[i].type +
                                        "&wo_no=" + row[i].wo_no +
                                        "&lot_no=" + row[i].lot_no +
                                        "&qty=" + row[i].qty,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            serialSuccess.play();
                                            toastr.success(result.message, result.title);
                                            $("#checksheet_label").val('');
                                            $('#checksheet_label').focus();
                                        } else {
                                            if (result.title === "Error" && result.message.includes("Qty Scan > Qty Repair")) {
                                                // serialDuplicate.play(); // Nada untuk qty melebihi
                                                toastr.error(result.message, result.title);
                                            } else if (result.title === "Available" && result.message.includes("Data Receipt FG has been Scanning")) {
                                                serialDuplicate.play();
                                                toastr.error(result.message, result.title);
                                            } else {
                                                if (result.title == "Not Scanned In" || result.title == "Not Registered") {
                                                    serialNotFound.play();
                                                } else {
                                                    serialDuplicate.play();
                                                }
                                                toastr.error(result.message, result.title);
                                            }
                                            $("#checksheet_label").val('');
                                            $('#checksheet_label').focus();
                                        }
                                    }
                                });
                            }

                            $('#dg').datagrid({
                                url: '<?= base_url('control/scan_repair_of_goods/getDocumentNos?document_no=') ?>' + document_no,
                                rownumbers: true
                            });
                            
                        } else {
                            serialNotFound.play();
                            toastr.warning("Label not Found or not match with Item!");
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

    // function btnDetails(val, row) {
    //     console.log(row);
    //     var details = "details('" + row.checksheet_number + "')"; //mengambil id dari customers kemudian di simpan di function details
    //     return '<a class="btn btn-primary w-100" onClick="' + details + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-list"></i> Detail</a>';
    // }

    // function details(checksheet_number) {
    //     $("#dlg_details").dialog('open');
    //     $('#dg2').datagrid({
    //         url: '<?= base_url('warehouse/item_receipts_fg/datatables2/') ?>' + btoa(checksheet_number),
    //         pagination: true,
    //         clientPaging: false,
    //         remoteFilter: true,
    //         rownumbers: true
    //     }).datagrid('enableFilter');
    // }
</script>
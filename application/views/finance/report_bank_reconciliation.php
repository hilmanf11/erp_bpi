<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div id="toolbar" style="padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Filter Data</b></legend>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cut Off Date</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Bank Account Number</span>
                <input style="width:60%;" id="filter_account_number" name="filter_account_number" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Bank Name</span>
                <input style="width:60%;" id="filter_bank_name" name="filter_bank_name" class="easyui-textbox" readonly />
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 50%; float:left;">
        </div>

    </fieldset>
    <!-- <a href="javascript:;" class="easyui-linkbutton" style="padding: 5px;" onclick="reconcile()"> Reconcile </a> -->
    <a href="javascript:;" class="easyui-linkbutton" style="padding: 5px;" onclick="filter()"> Reconcile </a> <!-- bisa langsung di print -->
    <?= $button ?>
</div>

<!-- UPLOAD DATA -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    
    <div style="margin-bottom:10px">
        <div id="p_upload" class="easyui-progressbar" style="width:100%;"></div>
        <div style="margin-top:5px; text-align: center;">
            <span id="p_start">0</span> / <span id="p_finish">0</span>
        </div>
    </div>
    
    <hr>
    
    <div style="margin-bottom:10px;">
        <p>
            Berhasil: <span id="p_success" style="font-weight: bold; color: green;">0</span> | Gagal: <span id="p_failed" style="font-weight: bold; color: red;">0</span>
        </p>
    </div>

    <div style="height: 100px; overflow-y: auto; border: 1px solid #ccc; padding: 5px; font-size: 12px;">
        <div id="p_remarks"></div>
    </div>
</div>

<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;">
    <iframe id="printout" src="" style="width: 100%; height:500px; border: 0;"></iframe>
</div>

<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; text-align: center; color: white; font-size: 20px; padding-top: 20%;">
    <b>Please Wait until Dialog download show up...</b>
</div>

<script>
    $(function() {

        $('#filter_account_number').combogrid({
            url: '<?= base_url('finance/account_banks/reads') ?>',
            panelWidth: 320,
            idField: 'account_number',
            textField: 'account_number',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Account No',
            columns: [
                [{
                    field: 'account_number',
                    title: 'Account No',
                    width: 100
                }, {
                    field: 'bank_name',
                    title: 'Bank Name',
                    width: 300
                }, {
                    field: 'bank_account',
                    title: 'Bank Account No',
                    width: 200
                }, ]
            ],
            dataType: 'json',
            onSelect: function(index, row) {
                // console.log(row);
                $("#filter_bank_name").textbox('setValue', row.bank_name);
            }
        });

        // UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('finance/report_bank_reconciliation/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    // Memastikan form valid sebelum submit
                    if (!$('#frm_upload').form('validate')) {
                        toastr.error("File is required! Please choose file (.xls) before click Upload.");
                        $.messager.alert('Error', 'File is required! Please choose file (.xls) before click Upload.', 'error');    
                        return;
                    }

                    $.messager.progress({
                        title: 'Harap Tunggu',
                        msg: 'Mengimpor Excel ke Database...'
                    });

                    // Submit form upload
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('finance/report_bank_reconciliation/upload') ?>',
                        queryParams: {
                            filter_account_number: $('#filter_account_number').val(),
                            filter_from: $('#filter_from').val(),
                            filter_to: $('#filter_to').val()
                        },
                        success: function(result) {
                            $.messager.progress('close');

                            //Clear File
                            $.ajax({
                                url: "<?= base_url('finance/report_bank_reconciliation/uploadclearFailed') ?>"
                            });

                            // Cek apakah string 'result' tidak kosong dan merupakan JSON
                            if (result && result.trim().startsWith('{') && result.trim().endsWith('}')) {
                                try {
                                    var json = JSON.parse(result);
                                } catch (e) {
                                    console.error("Gagal parse JSON: ", e);
                                    $.messager.alert('Error', 'Gagal memproses data dari server! Format data tidak valid. Silakan update atau ganti <b>browser</b> yang anda gunakan.', 'error');
                                    return;
                                }
                            } else {
                                // Jika respons kosong atau tidak valid, tampilkan pesan error yang jelas
                                $.messager.alert('Error', 'Server mengembalikan respons tidak valid. Silakan update atau ganti <b>browser</b> yang anda gunakan.', 'error');
                                console.error("Server response was empty or invalid JSON:", result);
                                return;
                            }

                            // Validasi Bank Account di Excel dengan di dropdown
                            if (json.title !== "Not Matched") {
                                processData(json.bank, json.data, json.total);
                            } else {
                                toastr.error("Failed! Period in Excel Is Not Match with the selected Date");
                                $.messager.alert('Error', json.message, 'error');
                            }
                        },
                        onLoadError: function() {
                            $.messager.progress('close');
                            $.messager.alert('Error', 'Gagal melakukan upload. Periksa koneksi atau coba lagi.', 'error');
                        }
                    });
                }
            }]
        });

        // Fungsi rekursif untuk memproses data satu per satu
        function processData(bank, data, total, index = 0, successCount = 0, failedCount = 0) {
            if (index >= total) {
                // Proses selesai
                $('#p_upload').progressbar('setValue', 100);
                $.messager.alert('Info', 'Proses upload selesai.', 'info');
                return;
            }

            // Hitung persentase progress
            var progressValue = Math.floor(((index + 1) / total) * 100);
            $('#p_upload').progressbar('setValue', progressValue);
            $('#p_start').html(index + 1);
            $('#p_finish').html(total);

            $.ajax({
                type: "POST",
                async: true,
                url: "<?= base_url('finance/report_bank_reconciliation/uploadCreate') ?>",
                data: {
                    "bank": bank,
                    "data": data[index]
                },
                cache: false,
                dataType: "json",
                success: function(result) {
                    var title;
                    if (result.theme === "success") {
                        successCount++;
                        $('#p_success').html(successCount);
                        title = `<b style='color: green;'>${result.title}</b> | ${result.message}`;
                    } else {
                        // warning berhasil insert tetapi periode berbeda dengan data excel
                        if (result.theme === "warning") {
                            successCount++;
                            $('#p_success').html(successCount);
                            title = `<b style='color: orange;'>${result.title}</b> | ${result.message}`;
                            
                        } else {
                            failedCount++;
                            $('#p_failed').html(failedCount);
                            title = `<b style='color: red;'>${result.title}</b> | ${result.message}`;
                            
                            $.ajax({
                                type: "POST",
                                async: true,
                                url: "<?= base_url('finance/report_bank_reconciliation/uploadcreateFailed') ?>",
                                data: {
                                    bank: bank,
                                    data: data[index],
                                    message: result.message
                                },
                                cache: false
                            });
                        } 
                    }
                    
                    $("#p_remarks").append(title + "<br>");

                    // Lanjutkan ke item berikutnya
                    processData(bank, data, total, index + 1, successCount, failedCount);
                },
                error: function(xhr, status, error) {
                    // Tangani error jika AJAX request gagal
                    failedCount++;
                    $('#p_failed').html(failedCount);
                    var title = `<b style='color: red;'>Error</b> | Gagal mengirim data ke server.`;
                    $("#p_remarks").append(title + "<br>");
                    
                    // Lanjutkan ke item berikutnya meskipun ada error
                    processData(bank, data, total, index + 1, successCount, failedCount);
                }
            });
        }

    });

    // RECONCILE
    function reconcile() {
        Swal.fire({
            title: 'Please Wait...',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "POST",
            async: true,
            url: '<?= base_url('finance/report_bank_reconciliation/reconcile') ?>',
            data: {
                "filter_account_number": $('#filter_account_number').val(),
                "filter_from": $('#filter_from').val(),
                "filter_to": $('#filter_to').val()
            },
            cache: false,
            success: function(result) {
                Swal.close();
                
                $("#printout").contents().find('html').html("<center><br><br><br> " +result+ " </center>");

                // try {
                //     var json = JSON.parse(result);
                // } catch (e) {
                //     console.error("Gagal parse JSON: ", e);
                //     console.log(result);
                //     $.messager.alert('Error', 'Gagal memproses data dari server! Format data tidak valid. Silakan update atau ganti <b>browser</b> yang anda gunakan.', 'error');
                //     return;
                // }
                    
                // if (json.title !== "Error") {
                        
                //     $("#printout").contents().find('html').html("<center><br><br><br> " +result+ " </center>");
                    
                // } else {
                //     var json = JSON.parse(result);
                //     Swal.fire({
                //             title: json.message,
                //         icon: json.theme,
                //         confirmButtonText: 'OK',
                //         allowOutsideClick: false,
                //     }).then(function() {
                //         // window.location.reload();
                //     });
                // }
            },
            onLoadError: function() {
                $.messager.progress('close');
                $.messager.alert('Error', 'Gagal melakukan upload. Periksa koneksi atau coba lagi.', 'error');
            }
        });
        
    }

    //DOWNLOAD TEMPLATE UPLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_bank_reconciliation.xls') ?>');
    }

    function reload() {
        window.location.reload();
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_account_number = $("#filter_account_number").combobox('getValue');

        url = "?filter_from=" + window.btoa(filter_from) + "&filter_to=" + window.btoa(filter_to) + 
        "&filter_account_number=" + window.btoa(filter_account_number);
        
        if (filter_account_number !== "") {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('finance/report_bank_reconciliation/print') ?>' + url);
            
        } else {
            toastr.warning("Please select the Bank Account no!");
            $.messager.alert("Warning", "Please choose the Bank Account first!", 'warning');
        }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_account_number = $("#filter_account_number").combobox('getValue');

        url = "?filter_from=" + window.btoa(filter_from) + "&filter_to=" + window.btoa(filter_to) + 
        "&filter_account_number=" + window.btoa(filter_account_number);

        if (filter_account_number !== "") {
            // Tampilkan overlay
            $("#loadingOverlay").show();

            // Unduh file
            window.location.assign('<?= base_url('finance/report_bank_reconciliation/print/excel') ?>' + url);

            // Sembunyikan overlay setelah beberapa saat
            setTimeout(function () {
                $("#loadingOverlay").hide();
            }, 3000); // Sesuaikan waktu jika perlu
            
        } else {
            toastr.warning("Please select the Bank Account no!");
            $.messager.alert("Warning", "Please choose the Bank Account first!", 'warning');
        }
    }

    //UPLOAD DATA
    function upload() {
        var filter_account_number = $("#filter_account_number").combobox('getValue');

        if (filter_account_number !== "") {
            $('#dlg_upload').dialog('open');
            
        } else {
            toastr.warning("Please select the Bank Account no!");
            $.messager.alert("Warning", "Please choose the Bank Account first!", 'warning');
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
</script>
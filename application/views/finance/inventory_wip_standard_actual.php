<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div id="toolbar" style="padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt Date</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:29%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Division</span>
                <input style="width:60%;" id="filter_division" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_items" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
           
        </div>
        <div style="width: 49%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Shift</span>
                <select style="width:60%;" id="filter_shift" class="easyui-combobox" panelHeight="auto">
                    <option value="">Choose All</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Report Display</span>
                <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">
                    <option value="RECAP">RECAP</option>
                    <option value="DETAIL">DETAIL</option>
                </select>
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Workorder</span>
                <input style="width:60%;" id="filter_workorder" class="easyui-combobox">
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>


<!-- Upload -->
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
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;">
    <iframe id="printout" src="" style="width: 100%; height:530px; border: 0;"></iframe>
</div>

<script>
    function reload() {
        window.location.reload();
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_workorder = $("#filter_workorder").combobox('getValue');

        var yearFrom = filter_from.substring(0, 4);
        var yearTo = filter_to.substring(0, 4);
        if (yearFrom !== yearTo) {
            toastr.warning("Please select the same year for Receipt Date", "Information");
        } else {
            url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_division=" + filter_division + "&filter_shift=" + filter_shift + "&filter_workorder=" + filter_workorder;
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");

            if (filter_display == 'RECAP') {
                $("#printout").attr('src', '<?= base_url('finance/inventory_wip_standard_actual/print') ?>' + url);
            } else {
                $("#printout").attr('src', '<?= base_url('finance/inventory_wip_standard_actual/print_detail') ?>' + url);
            }
        }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_workorder = $("#filter_workorder").combobox('getValue');

        var yearFrom = filter_from.substring(0, 4);
        var yearTo = filter_to.substring(0, 4);
        if (yearFrom !== yearTo) {
            toastr.warning("Please select the same year for Receipt Date", "Information");
        } else {
            url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_division=" + filter_division + "&filter_shift=" + filter_shift + "&filter_workorder=" + filter_workorder;
            
            if (filter_display == 'RECAP') {
                window.location.assign('<?= base_url('finance/inventory_wip_standard_actual/print/excel') ?>' + url);
            } else {
                window.location.assign('<?= base_url('finance/inventory_wip_standard_actual/print_detail/excel') ?>' + url);
            }
        }
    }

    $(function() {

        $('#filter_items').combogrid({
            url: '<?= base_url('master/item_fg/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }, ]
            ]
        });
    });

    $('#filter_division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'id',
        textField: 'number',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
    });

    $('#filter_workorder').combobox({
        url: '<?= base_url('finance/inventory_wip_standard_actual/readWO'); ?>',
        valueField: 'workorder',
        textField: 'workorder',
        prompt: 'Choose Wo No',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_display').combobox({
        panelHeight: 'auto',
        prompt: 'Choose Display',
        onChange: function (newValue, oldValue) {
            if (newValue === 'DETAIL') {
                $('#filter_workorder').combobox('disable'); 
            } else {
                $('#filter_workorder').combobox('enable');
            }
        }
    });


    // Upload Form
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_inventory_wip_standard_actual.xls') ?>');
    }

    // Upload Data
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url("finance/inventory_wip_standard_actual/uploadDownloadFailed") ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                //Clear File
                $.ajax({
                    url: "<?= base_url('finance/inventory_wip_standard_actual/uploadclearFailed') ?>",
                    async: false // Gunakan async false agar log dipastikan clear sebelum submit
                });

                $('#frm_upload').form('submit', {
                    url: '<?= base_url("finance/inventory_wip_standard_actual/upload") ?>',
                    onSubmit: function() {
                        if (!$(this).form('validate')) return false;
                        
                        $.messager.progress({
                            title: 'Please Wait',
                            msg: 'Importing Excel to Database'
                        });
                        return true;
                    },
                    success: function(result) {
                        try {
                            const response = typeof result === 'string' ? JSON.parse(result) : result;
                            
                            if (response.data && Array.isArray(response.data)) {
                                // Reset tampilan progress sebelum mulai
                                resetProgress(response.data.length);
                                processUploadData(response.data);
                            } else {
                                $.messager.alert('Error', response.message || 'Invalid data format.', 'error');
                            }
                        } catch (e) {
                            $.messager.alert('Error', 'Server Error: ' + result, 'error');
                        }
                    }
                });
            }
        }]
    });

    function resetProgress(total) {
        $('#p_upload').progressbar('setValue', 0);
        $('#p_start').html(0);
        $('#p_finish').html(total);
        $('#p_success, #p_failed').html(0);
        $('#p_remarks').empty();
    }

    function processUploadData(dataToUpload) {
        if (!dataToUpload || dataToUpload.length === 0) {
            $.messager.alert('Warning', 'No data to process.', 'warning');
            return;
        }

        const totalItems = dataToUpload.length;
        let successfulCount = 0;
        let failedCount = 0;

        const processItem = (index) => {
            if (index >= totalItems) {
                $.messager.progress('close');
                
                const message = `Upload complete. Success: ${successfulCount}, Failed: ${failedCount}`;
                $.messager.alert('Info', message, 'info');
                return;
            }

            const currentData = dataToUpload[index];
            const excelRow = index + 1; 
            
            $.ajax({
                type: "POST",
                url: "<?= base_url('finance/inventory_wip_standard_actual/uploadCreate') ?>",
                data: { "data": currentData },
                dataType: "json",
                success: function(result) {
                    let statusHtml = "";

                    if (result.theme === "success") {
                        successfulCount++;
                        $('#p_success').html(successfulCount);
                        statusHtml = `<span style='color: green;'><b>${result.title}</b>: ${result.message}</span><br>`;
                        finalizeStep(index, statusHtml);
                    } else {
                        failedCount++;
                        $('#p_failed').html(failedCount);
                        
                        // Pesan log gagal dengan nomor baris
                        const errorMessage = `No.${excelRow}: ${result.message}`;
                        statusHtml = `<span style='color: red;'><b>${result.title}</b>: ${result.message}</span><br>`;
                        
                        // Simpan log gagal
                        saveFailedLog(currentData, errorMessage, function() {
                            finalizeStep(index, statusHtml);
                        });
                    }
                },
                error: function(xhr, status, error) {
                    failedCount++;
                    $('#p_failed').html(failedCount);
                    const errorTitle = `<b style='color: red;'>Error</b> | HTTP Request Failed (${error})`;
                    finalizeStep(index, errorTitle);
                }
            });
        };

        function finalizeStep(index, htmlStatus) {
            $("#p_remarks").append(htmlStatus + "<br>");
            
            // Update Progress Bar & Counter
            const progressValue = Math.floor(((index + 1) / totalItems) * 100);
            $('#p_upload').progressbar('setValue', progressValue);
            $('#p_start').html(index + 1);
            $('#p_finish').html(totalItems); // Pastikan total juga tampil
            
            // Auto scroll ke bawah (cross-browser compatible)
            const d = $('#p_remarks');
            d.scrollTop(d[0].scrollHeight);

            // Rekursi: Proses item selanjutnya
            processItem(index + 1);
        }

        function saveFailedLog(data, errorMessage, callback) {
            $.ajax({
                type: "POST",
                url: "<?= base_url('finance/inventory_wip_standard_actual/uploadcreateFailed') ?>",
                data: { 
                    data: data, 
                    message: errorMessage 
                },
                cache: false,
                complete: function() {
                    // Tetap panggil callback walau simpan log gagal agar proses upload tidak berhenti
                    callback();
                }
            });
        }

        processItem(0);
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

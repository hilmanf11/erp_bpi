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
                <span style="width:35%; display:inline-block;">Category</span>
                <input style="width:60%;" id="filter_item_category" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" id="filter_item_family" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 49%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_items" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Report Display</span>
                <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">
                    <option value="RECAP">RECAP</option>
                    <option value="DETAIL">DETAIL</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Type</span>
                <select style="width:60%;" id="filter_trans_type" class="easyui-combobox" panelHeight="auto" disabled>
                    <option value="">Choose All</option>
                    <option value="RECEIPT">RECEIPT</option>
                    <option value="ISSUED">ISSUED</option>
                    <option value="ADJ IN STO">ADJ IN STO</option>
                    <option value="ADJ OUT STO">ADJ OUT STO</option>
                    <option value="BPM">BPM</option>
                    <option value="BPB">BPB</option>
                    <option value="KANBAN WO">KANBAN WO</option>
                </select>
            </div>
        </div>

    </fieldset>
    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="openUploadList()"><i class="fa fa-table"></i> List Uploaded Data</a>
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

<div id="dlg_upload_list" class="easyui-dialog" title="Uploaded Data List" 
    data-options="closed: true, modal:true, iconCls:'icon-search'" 
    style="width: 1100px; height: 550px; padding:10px;">
    
    <table id="dg_upload_list" class="easyui-datagrid" style="width:100%; height:100%"
           data-options="
                singleSelect: true,
                pagination: true,
                rownumbers: true,
                fitColumns: false, 
                nowrap: true,
                url: '<?php echo base_url('finance/inventory_rm_standard_actual/get_upload_list'); ?>'
           ">
        <thead>
            <tr style="text-align: center;">
                <th field="item_rm_id" rowspan="2" width="150">Item ID</th>
                <th field="part_no" rowspan="2" width="150">Part No</th>
                <th field="uom" rowspan="2" width="60" data-options="align:'center'">UOM</th>
                <th field="qty" rowspan="2" width="100" data-options="align:'center'">Qty</th>
                <th field="price" rowspan="2" width="120" data-options="align:'center'">Price</th>
                <th field="currency" rowspan="2" width="60" data-options="align:'center'">CCY</th>
                <th field="cutoff_date" rowspan="2" width="100" data-options="align:'center'">Cutoff Date</th>
                <th field="upload_date" rowspan="2" width="100" data-options="align:'center'">Upload Date</th>
                
                <th colspan="2">Created</th>
                <th colspan="2">Updated</th>
            </tr>
            <tr>
                <th field="created_by" width="120" data-options="align:'center'">By</th>
                <th field="created_date" width="150" data-options="align:'center'">Date</th>
                <th field="updated_by" width="120" data-options="align:'center'">By</th>
                <th field="updated_date" width="150" data-options="align:'center'">Date</th>
            </tr>
        </thead>
    </table>
</div>

<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;">
    <iframe id="printout" src="" style="width: 100%; height:500px; border: 0;"></iframe>
</div>

<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; text-align: center; color: white; font-size: 20px; padding-top: 20%;">
    <b>Please Wait until Dialog download show up...</b>
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
        var filter_item_category = $("#filter_item_category").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_items = $("#filter_items").combobox('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_trans_type = $("#filter_trans_type").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');

        var yearFrom = filter_from.substring(0, 4);
        var yearTo = filter_to.substring(0, 4);
        if (yearFrom !== yearTo) {
            toastr.warning("Please select the same year for Receipt Date", "Information");
        } else {
            url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_item_category=" + filter_item_category + "&filter_item_family=" + filter_item_family + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_trans_type=" + filter_trans_type + "&filter_division=" + filter_division;
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");

            if (filter_display == 'RECAP') {
                $("#printout").attr('src', '<?= base_url('finance/inventory_rm_standard_actual/print') ?>' + url);
            } else {
                $("#printout").attr('src', '<?= base_url('finance/inventory_rm_standard_actual/print_detail') ?>' + url);
            }
        }

    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_category = $("#filter_item_category").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_items = $("#filter_items").combobox('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_trans_type = $("#filter_trans_type").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        
        var yearFrom = filter_from.substring(0, 4);
        var yearTo = filter_to.substring(0, 4);
        if (yearFrom !== yearTo) {
            toastr.warning("Please select the same year for Receipt Date", "Information");
        } else {
            var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_item_category=" + filter_item_category + "&filter_item_family=" + filter_item_family + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_trans_type=" + filter_trans_type + "&filter_division=" + filter_division;

            // Tampilkan overlay
            $("#loadingOverlay").show();

            // Unduh file
            if (filter_display == 'RECAP') {
                window.location.assign('<?= base_url('finance/inventory_rm_standard_actual/print/excel') ?>' + url);
            } else {
                window.location.assign('<?= base_url('finance/inventory_rm_standard_actual/print_detail/excel') ?>' + url);
            }

            // Sembunyikan overlay setelah beberapa saat
            setTimeout(function() {
                $("#loadingOverlay").hide();
            }, 3000); // Sesuaikan waktu jika perlu
        }
    }

    $(function() {
        $("#filter_item_category").combobox({
            url: '<?= base_url('master/item_categories/readsnotfg') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Categories",
            onSelect: function(category) {
                $("#filter_item_family").combobox({
                    url: '<?= base_url('finance/inventory_rm_standard_actual/readItemFamily/') ?>' + category.id,
                    valueField: 'number',
                    textField: 'name',
                    prompt: "Select Product Family",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(row) {
                        $('#filter_items').combobox({
                            url: '<?= base_url('master/item_rm/read/') ?>' + row.id,
                            valueField: 'id',
                            textField: 'number',
                            prompt: "Select Product No",
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }],
                        });
                    }
                });
            }
        });
    });

    $("#filter_item_family").combobox({
        url: '<?= base_url('finance/inventory_rm_standard_actual/readItemFamilys/') ?>',
        valueField: 'number',
        textField: 'name',
        prompt: "Select Product Family",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onSelect: function(row) {
            $('#filter_items').combobox({
                url: '<?= base_url('master/item_rm/read/') ?>' + row.id,
                valueField: 'id',
                textField: 'number',
                prompt: "Select Product No",
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
            });
        }
    });

    $('#filter_items').combobox({
        url: '<?= base_url('master/item_rm/reads/') ?>',
        valueField: 'id',
        textField: 'number',
        prompt: "Select Product No",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'number',
        textField: 'number',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
    });

    $("#filter_display").combobox({
        onChange: function(display) {
            if (display === 'DETAIL') {
                $('#filter_trans_type').combobox('enable');
            } else {
                $('#filter_trans_type').combobox('disable');
            }
        }
    });


    // Upload Form
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    // Download Template
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_inventory_rm_standard_actual.xls') ?>');
    }

    function openUploadList() {
        $('#dlg_upload_list').dialog('open').dialog('center');
        
        // Aktifkan filter pada datagrid
        $('#dg_upload_list').datagrid('enableFilter', [
            {
                field:'qty',
                type:'numberbox',
                options:{precision:2},
                op:['equal','notequal','less','greater']
            },
            {
                field:'price',
                type:'numberbox',
                options:{precision:4},
                op:['equal','less','greater']
            }
        ]);
    }
    

    // Upload Data
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url("finance/inventory_rm_standard_actual/uploadDownloadFailed") ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                //Clear File
                $.ajax({
                    url: "<?= base_url('finance/inventory_rm_standard_actual/uploadclearFailed') ?>",
                    async: false // Gunakan async false agar log dipastikan clear sebelum submit
                });

                $('#frm_upload').form('submit', {
                    url: '<?= base_url("finance/inventory_rm_standard_actual/upload") ?>',
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
                $('#dg_upload_list').datagrid('reload'); // reload list upload
                
                const message = `Upload complete. Success: ${successfulCount}, Failed: ${failedCount}`;
                $.messager.alert('Info', message, 'info');
                return;
            }

            const currentData = dataToUpload[index];
            const excelRow = index + 1; 
            
            $.ajax({
                type: "POST",
                url: "<?= base_url('finance/inventory_rm_standard_actual/uploadCreate') ?>",
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
                url: "<?= base_url('finance/inventory_rm_standard_actual/uploadcreateFailed') ?>",
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

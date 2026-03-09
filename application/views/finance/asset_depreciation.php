<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'periode',width:80,halign:'center'">Period</th>
            <th rowspan="2" data-options="field:'trans_date',width:80,halign:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'gl_no',width:100,halign:'center'">GL No</th>
            <th rowspan="2" data-options="field:'asset_no',width:150,halign:'center'">Asset No</th>
            <th rowspan="2" data-options="field:'asset_name',width:200,halign:'center'">Asset Name</th>
            <th rowspan="2" data-options="field:'asset_family_name',width:150,halign:'center'">Asset Family</th>
            <th rowspan="2" data-options="field:'asset_category_number',width:150,halign:'center',hidden:true">Asset Category</th>
            <th rowspan="2" data-options="field:'purchase_invoice_number',width:150,halign:'center'">Purchase Invoice No</th>
            <th rowspan="2" data-options="field:'purchase_date',width:120,align:'center'">Purchase Date</th>
            <th rowspan="2" data-options="field:'cost',width:100,halign:'center',align:'right', formatter:priceformat">Asset Cost</th>
            <th rowspan="2" data-options="field:'estimate_year',width:100,align:'center'">Est. Economic<br>(year)</th>
            <th rowspan="2" data-options="field:'estimate_month',width:100,align:'center'">Est. Economic<br>(month)</th>
            <th rowspan="2" data-options="field:'expired_date',width:120,align:'center'">Expired Date</th>
            <th rowspan="2" data-options="field:'account_number',width:100,halign:'center'">Account No</th>
            <th rowspan="2" data-options="field:'account_name',width:150,halign:'center'">Account Name</th>
            <th rowspan="2" data-options="field:'debit',width:100,halign:'center',align:'right', formatter:priceformat">Debit</th>
            <th rowspan="2" data-options="field:'credit',width:100,halign:'center',align:'right', formatter:priceformat">Credit</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- FORM FILTER DATAGRID -->
<div id="toolbar" style="height: 260px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Periode</span>
                <input style="width:30%;" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                <input style="width:30%;" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Year'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Asset Family</span>
                <input style="width:60%;" id="filter_family" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Asset Name</span>
                <input style="width:60%;" id="filter_asset_no" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Process Data</b></legend>
            <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
            <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <div id="p_remarks" class="easyui-panel" style="width:100%; height:100px; padding:10px; margin-top: 10px; overflow: auto;">
                <ul id="remarks">

                </ul>
            </div>
        </fieldset>
        <!-- <fieldset style="width: 43%; height: 210px; overflow: auto; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Journal Lists</b></legend>
            <a href="javascript:;" style="margin-bottom: 10px;" class="easyui-linkbutton" onclick="calculate()"><i class="fa fa-refresh"></i> Calculate Journal</a>
            <table id="dg3" class="easyui-datagrid" style="width: 99%;">
                <thead>
                    <tr>
                        <th field="ck" checkbox="true"></th>
                        <th data-options="field:'account_number',halign:'center',width:90">Account No</th>
                        <th data-options="field:'account_name',halign:'center',width:190">Account Name</th>
                        <th data-options="field:'debit',width:100,halign:'center',align:'right',formatter:priceformat">Debit</th>
                        <th data-options="field:'credit',width:100,halign:'center',align:'right',formatter:priceformat">Credit</th>
                    </tr>
                </thead>
            </table>
            <a href="javascript:;" style="margin-top: 10px; width:100%;" class="easyui-linkbutton c6" onclick="saveJournal()"><i class="fa fa-check"></i> Save Data Journal</a>
        </fieldset> -->
    </div>
    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
</div>

<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>Get data from Modul Purchase Invoicing and <b>Account Category = Fixed Asset</b></li>
                <li><b>Asset Family or Asset Category = Product Family</b> (Master Data > Accounting & Finance > Item Family)</li>
                <li><b>Journal Types</b> (Master Data > Accounting & Finance > Journal Types)</li>
            </ul>
        </div>
        <div title="CONDITIONS" style="padding: 20px;">
            <ul>
                <li><b>Depreciation = Asset Cost/Economic month</b> </li>
            </ul>
        </div>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('finance/asset_depreciation/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    // ADD DATA
    async function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_family = $("#filter_family").combobox('getValue');
        var filter_asset_no = $("#filter_asset_no").combogrid('getValue');
        
        // Clear the remarks box at the start of the process
        $("#p_remarks").html(""); 

        // --- Langkah 1: Tampilkan Loading secara global
        Swal.fire({
            title: 'Please Wait',
            text: 'Fetching data...',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        try {
            // --- Langkah 2: Ambil data dari server
            let getDataResponse = await $.ajax({
                type: "post",
                url: "<?= base_url('finance/asset_depreciation/getData') ?>",
                data: {
                    month: filter_month,
                    year: filter_year,
                    category: filter_family,
                    number: filter_asset_no
                },
                dataType: "json",
            });

            if (getDataResponse.total <= 0) {
                Swal.close();
                toastr.warning("Fixed Asset Data Not Found");
                return;
            }

            // --- Langkah 3: Bersihkan failed records dari server (dilakukan sekali)
            await $.ajax({
                type: "post",
                url: "<?= base_url('finance/asset_depreciation/uploadclearFailed') ?>",
            });

            // --- Langkah 4: Proses setiap record secara berurutan
            let total = getDataResponse.total;
            let json = getDataResponse.rows;
            let success_count = 0;
            let failed_count = 0;

            Swal.update({
                title: 'Processing Data',
                html: `Processing <b id="p_start_swal">1</b> of <b id="p_finish_swal">${total}</b>...<br><br>
                    <div style="width: 80%; margin: 0 auto;"><div id="p_upload_swal" class="easyui-progressbar" style="width: 100%;"></div></div><br>
                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid #fff; padding: 5px;" id="p_remarks_swal"></div>`,
                allowOutsideClick: false
            });

            // Inisialisasi progressbar
            $('#p_upload').progressbar();
            $('#p_upload_swal').progressbar();

            for (let i = 0; i < total; i++) {
                let itemData = json[i];
                let value = Math.floor(((i + 1) / total) * 100);

                // Perbarui UI progressbar
                $('#p_upload').progressbar('setValue', value);
                $('#p_upload_swal').progressbar('setValue', value);

                $('#p_start').html(i + 1);
                $('#p_start_swal').html(i + 1);

                let message_title = '';

                try {
                    // Panggil AJAX untuk setiap record secara berurutan
                    let createResponse = await $.ajax({
                        type: "post",
                        url: '<?= base_url('finance/asset_depreciation/create') ?>',
                        data: {
                            asset_category_number: itemData.asset_category_number,
                            item_family_id: itemData.item_family_id,
                            asset_no: itemData.number,
                            asset_name: itemData.name,
                            account_number: itemData.account_number,
                            depreciation: itemData.depreciation,
                            trans_date: itemData.trans_date,
                            periode: filter_year + "-" + filter_month,
                        },
                        dataType: "json",
                    });

                    if (createResponse.theme === "success") {
                        success_count++;
                        message_title = `<b style='color: green;'>${createResponse.title}</b> | ${createResponse.message}`;
                    } else {
                        failed_count++;
                        message_title = `<b style='color: red;'>${createResponse.title}</b> | ${createResponse.message}`;

                        // Simpan record yang gagal
                        await $.ajax({
                            type: "POST",
                            url: "<?= base_url('finance/asset_depreciation/uploadcreateFailed') ?>",
                            data: {
                                data: itemData,
                                message: createResponse.message
                            },
                            cache: false
                        });
                    }
                } catch (createError) {
                    failed_count++;
                    message_title = `<b style='color: red;'>Error</b> | ${createError.responseJSON?.message || createError.statusText}`;

                    // Simpan record yang gagal jika ada error AJAX
                    await $.ajax({
                        type: "POST",
                        url: "<?= base_url('finance/asset_depreciation/uploadcreateFailed') ?>",
                        data: {
                            data: itemData,
                            message: createError.responseJSON?.message || createError.statusText
                        },
                        cache: false
                    });
                } finally {
                    // Perbarui status counts dan remarks di setiap iterasi
                    $('#p_success').html(success_count);
                    $('#p_failed').html(failed_count);
                    $("#p_remarks").append(message_title + "<br>");
                    $("#p_remarks").scrollTop($("#p_remarks")[0].scrollHeight); // Auto-scroll
                }
            }

            // --- Langkah 5: Tampilkan hasil akhir
            Swal.close();
            let finalTitle = `Process Completed!`;
            let finalMessage = `Successful: ${success_count}<br>Failed: ${failed_count}`;
            Swal.fire({
                title: finalTitle,
                html: finalMessage,
                icon: 'info',
                confirmButtonText: 'OK'
            }).then(() => {
                $("#dg").datagrid('reload');
            });

        } catch (error) {
            Swal.close();
            toastr.error("An error occurred during the process.");
            console.error("Main function error:", error);
        }
    }

    function calculate() {
        var filter_family = $("#filter_family").combobox('getValue');

        var rows = $('#dg').datagrid('getSelections');
        var totalrows = rows.length;

        if (filter_family !== "") {
            if (totalrows > 0) {
                var total = 0;
                
                for (let i = 0; i < totalrows; i++) {
                    var debitValue = parseFloat(rows[i].debit) || 0; 

                    if (filter_family === rows[i].asset_category_number) {
                        total += debitValue;
                    }
                }

                console.log("TOTAL ", total);

                if (!isNaN(total)) {
                    // Pastikan total dikonversi ke string yang aman untuk URL sebelum base64
                    var totalString = total.toFixed(2); // Menggunakan 2 desimal untuk akurasi

                    $('#dg3').datagrid({
                        url: '<?= base_url('finance/asset_depreciation/calculate/') ?>' + window.btoa(filter_family) + "/" + window.btoa(totalString),
                        rownumbers: true,
                    });
                } else {
                    toastr.error("Calculation Error. Result is not a valid number.");
                }

            } else {
                toastr.info("Please Select Data in the Table first");
            }
        } else {
            toastr.info("Please Select Category First");
        }
    }

    function saveJournal() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');

        var rows = $('#dg3').datagrid('getSelections');
        var totalrows = rows.length;

        if (totalrows > 0) {
            for (let i = 0; i < totalrows; i++) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('finance/asset_depreciation/saveJournal') ?>",
                    data: {
                        periode: filter_year + "-" + filter_month,
                        asset_category_number: rows[i].asset_category_number,
                        account_number: rows[i].account_number,
                        account_name: rows[i].account_name,
                        debit: rows[i].debit,
                        credit: rows[i].credit,
                        flag: rows[i].flag,
                    },
                    dataType: "json",
                    success: function(response) {
                        toastr.success(response.message);
                    }
                });
            }
        } else {
            toastr.info("Please Select All Data in the Table Journal List first");
        }
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    requestDataDelete(rows.length, rows);
                    function requestDataDelete(total, json, number = 1, value = 0, success = 1, failed = 1) {
                        if (value < 100) {
                            value = Math.floor((number / total) * 100);
                            $('#p_upload').progressbar('setValue', value);
                            $('#p_start').html(number);
                            $('#p_finish').html(total);
                            var i = (number - 1);

                            $.ajax({
                                type: "post",
                                url: "<?= base_url('closing/locks/checkLock') ?>",
                                data: "period=" + json[i].periode + "-01" + "&menus_id=<?= $menus_id ?>",
                                dataType: "json",
                                success: function (lock) {
                                    if(lock.total > 0){
                                        toastr.error("This period is not active by Accounting");
                                        return false;
                                    }

                                    $.ajax({
                                        method: 'post',
                                        url: '<?= base_url('finance/asset_depreciation/delete') ?>',
                                        data: {
                                            id: json[i].id
                                        },
                                        success: function(result) {
                                            var result = eval('(' + result + ')');

                                            if (result.theme == "success") {
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                requestDataDelete(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                requestDataDelete(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            if (value == 100) {
                                                Swal.fire('Good job!', 'Process Delete Journal Entries Completed!', 'success');
                                                $("#dg").datagrid('reload');
                                            }

                                            $("#p_remarks").append(title + "<br>");
                                        },
                                    });
                                }
                            });
                        }
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //FILTER DATA
    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_family = $("#filter_family").combobox('getValue');
        var filter_asset_no = $("#filter_asset_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_family=" + window.btoa(filter_family) +
            "&filter_asset_no=" + window.btoa(filter_asset_no);

        $('#dg').datagrid({
            url: '<?= base_url('finance/asset_depreciation/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/asset_depreciation/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //EXPORT TO EXCEL
    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_family = $("#filter_family").combobox('getValue');
        var filter_asset_no = $("#filter_asset_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_family=" + window.btoa(filter_family) +
            "&filter_asset_no=" + window.btoa(filter_asset_no);

        window.location.assign('<?= base_url('finance/asset_depreciation/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function downloadFailed() {
        window.open('<?= base_url('finance/asset_depreciation/uploadDownloadFailed') ?>', '_blank');
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

    $(function() {
        $("#add").html("Generate Depreciation");

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            rownumbers: true,
            pagination: true,
            fit: true,
            rowStyler: function(index, row) {
                if (row.asset_no == null) {
                    return 'background-color:#FFC9C9;';
                }
            }
        });

        $('#filter_month').combobox({
            url: '<?php echo base_url('finance/asset_depreciation/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Month',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('finance/asset_depreciation/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Year',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_family").combobox({
            url: '<?= base_url('finance/asset_depreciation/readAssetFamilies') ?>',
            valueField: 'number',
            textField: 'name',
            prompt: "Choose Product Family",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(category) {
                var month = $("#filter_month").combobox('getValue');
                var year = $("#filter_year").combobox('getValue');

                $("#filter_asset_no").combogrid({
                    url: '<?= base_url('finance/asset_depreciation/readAssetNo/') ?>' + window.btoa(category.number) + "/" + month + "/" + year,
                    panelWidth: 450,
                    idField: 'number',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Asset No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'number',
                            title: 'Asset No',
                            width: 150
                        }, {
                            field: 'name',
                            title: 'Asset Name',
                            width: 250
                        }, ]
                    ],
                });
            }
        });
    });

    function priceformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat("id-ID", {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }
</script>

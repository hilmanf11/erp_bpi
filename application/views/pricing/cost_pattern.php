<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar" rownumbers="true" singleSelect="false" fitColumns="false" data-options="onLoadSuccess: onMergeFG">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2"data-options="field:'print',width:60,align:'center', formatter:btnPrint">Print</th>
            <th rowspan="2"data-options="field:'item_fg_number',width:120,halign:'center'">Part No</th>
            <th rowspan="2"data-options="field:'item_fg_name',width:150,halign:'center'">Part Name</th>
            <th rowspan="2"data-options="field:'customer_name',width:150,halign:'center'">Customer Name</th>
            <th rowspan="2"data-options="field:'p_month',width:80,halign:'center'">Month</th>
            <th rowspan="2"data-options="field:'p_year',width:80,halign:'center'">Year</th>
            <th rowspan="2"data-options="field:'revision',width:100,halign:'center'">Revision</th>

            <th colspan="3"data-options="halign:'center'">MATERIAL SPEC</th>
            <th colspan="5"data-options="halign:'center'">MATERIAL USED</th>
            <th colspan="4"data-options="halign:'center'">MATERIAL COST (RP)</th>
            <th colspan="6"data-options="halign:'center'">PROCESS COST</th>
            <th rowspan="2"data-options="field:'sub_total',width:100,halign:'center'">Total</th>
            <th rowspan="2"data-options="field:'ng_ratio_cost',width:100,halign:'center'">NG Ratio</th>
            <th rowspan="2"data-options="field:'adm_foh_cost',width:100,halign:'center'">ADM & FOH</th>
            <th rowspan="2"data-options="field:'mtn_cost',width:100,halign:'center'">MTN</th>
            <th rowspan="2"data-options="field:'total_packing_cost',width:100,halign:'center'">Packaging</th>
            <th rowspan="2"data-options="field:'transportasion_cost_pcs',width:100,halign:'center'">Transport</th>
            <th rowspan="2"data-options="field:'purging_value',width:100,halign:'center'">Purging</th>
            <th rowspan="2"data-options="field:'mold_depreciation',width:100,halign:'center'">Mold <br>Depreciation</th>
            <th rowspan="2"data-options="field:'profit_nominal',width:100,halign:'center'">Profit</th>
            <th rowspan="2"data-options="field:'grand_total',width:100,halign:'center'">Grand Total</th>
            <th rowspan="2"data-options="field:'moq',width:100,halign:'center'">Moq</th>
            <th rowspan="2"data-options="field:'volume',width:100,halign:'center'">Vol/M</th>
            <th rowspan="2"data-options="field:'purging_cost',width:100,halign:'center'">Purging Cost</th>
            <th rowspan="2"data-options="field:'start_setting',width:100,halign:'center'">Start Setting</th>
            <th rowspan="2"data-options="field:'approved_to',width:100,halign:'center',formatter:formatApproved,styler:styleApproved">Status <br>Approve</th>
            <th rowspan="2"data-options="field:'approved_by',width:100,halign:'center'">Approve By</th>
            <th rowspan="2"data-options="field:'approved_date',width:150,halign:'center'">Approve Date</th>
            <th colspan="2"data-options="halign:'center'">Created</th>
            <th colspan="2"data-options="halign:'center'">Updated</th>
        </tr>

        <tr>
            <th data-options="field:'part_no_vg',width:150">Material Name</th>
            <th data-options="field:'part_no_mb',width:150">MB Name</th>
            <th data-options="field:'part_no_cp',width:150">CP Name</th>
            <th data-options="field:'used_vg',width:100">Gross</th>
            <th data-options="field:'nett_vg',width:80">Nett</th>
            <th data-options="field:'uom',width:80">Uom</th>
            <th data-options="field:'used_mb',width:100">Master Batch</th>
            <th data-options="field:'used_cp',width:100">Child Part (Pcs)</th>
            <th data-options="field:'virgin_cost',width:100">Material</th>
            <th data-options="field:'mb_cost',width:100">Master Batch</th>
            <th data-options="field:'child_part_cost',width:100">Child Part</th>
            <th data-options="field:'total_material_cost',width:100">Subtotal 1</th>

            <th data-options="field:'cycle_time',width:80">C.T (Sec)</th>
            <th data-options="field:'cavity_standard',width:80">CAV</th>
            <th data-options="field:'toonage',width:80">MC Tonage</th>
            <th data-options="field:'plain_rate_sec',width:80">Rate (Sec)</th>
            <th data-options="field:'cycle_time_process',width:80">2nd Process</th>
            <th data-options="field:'total_process_cost',width:90">Subtotal 2</th>

            <th data-options="field:'created_by',width:120,align:'center'">By</th>
            <th data-options="field:'created_date',width:120,align:'center'">Date</th>
            <th data-options="field:'updated_by',width:120,align:'center'">By</th>
            <th data-options="field:'updated_date',width:120,align:'center'">Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 280px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:25%; display:inline-block;">Period</span>
                <input style="width:15%;" id="filter_period_month_from" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'From'">
                <span> to </span>
                <input style="width:15%;" id="filter_period_month_to" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'To'">
                
                <input style="width:17%;" id="filter_period_year" value="<?= date("Y") ?>" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:25%; display:inline-block;">Revision</span>
                <select style="width:50%;" id="filter_revision" class="easyui-combobox" panelHeight="auto">
                    <option value="" selected disabled>Choose All</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:25%; display:inline-block;">Product No</span>
                <input style="width:50%;" id="filter_item_fg_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:25%; display:inline-block;">Customer</span>
                <input style="width:50%;" id="filter_customer_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:25%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <!-- <a href="javascript:;" class="easyui-linkbutton" onclick="print_cp()"><i class="fa fa-print"></i> Cost Pattern</a> -->
            </div>
    </fieldset>
    <?= $button ?>
</div>
<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1500px; height: 400px; padding:20px; top: 50px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="display: flex; gap: 20px; align-items: stretch;">
            
            <fieldset style="flex: 1; border:1px solid #d0d0d0; border-radius:4px; padding: 15px;">
                <legend><b>Form Data</b></legend>
                <div class="fitem" style="margin-bottom: 10px;">
                    <span style="width:30%; display:inline-block;">Period</span>
                    <input style="width:33%;" name="p_month" id="p_month" required="" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:33%;" name="p_year" id="p_year" required="" class="easyui-combobox" data-options="prompt:'Year'">
                </div>
                <div class="fitem" style="margin-bottom: 10px;">
                    <span style="width:30%; display:inline-block;">Revision</span>
                    <input style="width:67%;" name="revision" id="revision" value="<?= "0" ?>" class="easyui-combobox" data-options="prompt:'Revision'" panelHeight="auto">
                </div>
                <div class="fitem" style="margin-bottom: 10px;">
                    <span style="width:30%; display:inline-block;">Product No</span>
                    <input style="width:67%;" name="item_fg_id" id="item_fg_id" class="easyui-combogrid">
                </div>
                <div class="fitem" style="margin-bottom: 10px;">
                    <span style="width:30%; display:inline-block;">Customer</span>
                    <input style="width:67%;" name="customer_id" id="customer_id" class="easyui-combogrid">
                </div>
                <div class="fitem" style="margin-bottom: 10px;">
                    <span style="width:30%; display:inline-block;">Model Name</span>
                    <input style="width:67%;" name="model_name" id="model_name" class="easyui-textbox">
                </div>
            </fieldset>
            <fieldset style="flex: 1; border:1px solid #d0d0d0; border-radius:4px; padding: 15px;">
                <legend><b>Component Check</b></legend>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div style="padding: 5px;">
                        <input class="easyui-checkbox" id="check_rate" name="check_rate" value="on"> &nbsp; Rate
                    </div>
                    <div style="padding: 5px;">
                        <input class="easyui-checkbox" id="check_packaging_trans_cost" name="check_packaging_trans_cost" value="on"> &nbsp; Packaging & Trans Cost
                    </div>
                    <div style="padding: 5px;">
                        <input class="easyui-checkbox" id="check_material_cost" name="check_material_cost" value="on"> &nbsp; Material Cost
                    </div>
                    <div style="padding: 5px;">
                        <input class="easyui-checkbox" id="check_process_cost" name="check_process_cost" value="on"> &nbsp; Process Cost
                    </div>
                </div>
            </fieldset>
            <fieldset style="flex: 1; border:1px solid #d0d0d0; border-radius:4px; padding: 15px;">
                <legend><b>Process Generate Data</b></legend>
                <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
                <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
                <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
                <div id="p_remarks" class="easyui-panel" style="width:100%; height:80px; padding:10px; margin-top: 10px; overflow: auto;">
                    <ul id="remarks">
                    </ul>
                </div>

                <div class="fitem" style="text-align:left;">
                    <a href="javascript:;" class="easyui-linkbutton" onclick="downloadFailed()">
                        <i class="fa fa-download"></i> List Failed
                    </a>
                </div>
            </fieldset>
        </div>
    </form>
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
    <span style="float: left; color:green;">SUCCESS : <b id="p_success2">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed2">0</b></span>
    <div id="p_upload2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
    <div id="p_remarks2" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>


<!-- PDF -->
<iframe id="printout" src="<?= base_url('pricing/cost_pattern/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    function onMergeFG(data) {
        if (!data.rows || data.rows.length === 0) return;

        let rows = data.rows;
        let startIndex = 0;
        let rowspan = 1;

        function isSameGroup(a, b) {
            return a.item_fg_id === b.item_fg_id &&
                a.customer_id === b.customer_id &&
                a.p_month === b.p_month &&
                a.p_year === b.p_year &&
                a.revision === b.revision;
        }

        for (let i = 1; i <= rows.length; i++) {

            if (i < rows.length && isSameGroup(rows[i], rows[startIndex])) {
                rowspan++;
            } else {

                if (rowspan > 1) {
                    let fieldsToMerge = [
                        'print',
                        'item_fg_number',
                        'item_fg_name',
                        'p_month',
                        'p_year',
                        'revision',
                        'uom',
                        'cycle_time',
                        'cavity_standard',
                        'toonage',
                        'plain_rate_sec',
                        'cycle_time_process',
                        'total_process_cost',
                        'total_material_cost',
                        'ng_ratio_cost',
                        'adm_foh_cost',
                        'mtn_cost',
                        'total_packing_cost',
                        'transportasion_cost_pcs',
                        'purging_value',
                        'mold_depreciation',
                        'profit_nominal',
                        'grand_total',
                        'sub_total',
                        'moq',
                        'volume',
                        'purging_cost',
                        'start_setting',
                        'created_by',
                        'created_date',
                        'updated_by',
                        'updated_date'
                    ];

                    fieldsToMerge.forEach(function(field) {
                        $('#dg').datagrid('mergeCells', {
                            index: startIndex,
                            field: field,
                            rowspan: rowspan
                        });
                    });
                }

                startIndex = i;
                rowspan = 1;
            }
        }
    }

    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');
    }

    //Delete Data
    function deleted() {
    var rows = $('#dg').datagrid('getSelections');
    
    if (rows.length > 0) {
        $.messager.confirm('Warning', 'Are you sure you want to delete the selected data?', function(r) {
            if (r) {
                var ids = [];
                for (var i = 0; i < rows.length; i++) {
                    ids.push(rows[i].id); 
                }
                if (ids.length === 0) {
                    toastr.warning("Failed to extract data ID!", "Information");
                    return; 
                }

                $.ajax({
                    method: 'post',
                    url: '<?= base_url('pricing/cost_pattern/delete') ?>',
                    data: {
                        ids: ids 
                    },
                    success: function(result) {
                        var res = JSON.parse(result);
                        if(res.success) {
                            toastr.success(res.message);
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        toastr.error(jqXHR.statusText);
                        $.messager.alert("Error", jqXHR.statusText, 'error');
                    },
                    complete: function(data) {
                        $('#dg').datagrid('reload');
                        $('#dg').datagrid('clearSelections');
                    }
                });
            }
        });
    } else {
        toastr.warning("Please select at least one data in the table first!", "Information");
        }
    }

    function filter() {
        var filter_period_month_from = $("#filter_period_month_from").combobox('getValue');
        var filter_period_month_to = $("#filter_period_month_to").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combogrid('getValue');

        var url = "?filter_period_month_from=" + filter_period_month_from + 
                "&filter_period_month_to=" + filter_period_month_to + 
                "&filter_period_year=" + filter_period_year +
                "&filter_item_fg_id=" + filter_item_fg_id + 
                "&filter_revision=" + filter_revision + 
                "&filter_customer_id=" + filter_customer_id;

        $('#dg').datagrid({
            url: '<?= base_url('pricing/cost_pattern/datatables') ?>' + url,
            fit: true,
            pagination: true,
            rownumbers: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('pricing/cost_pattern/print') ?>' + url);
    }

    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_cost_pattern.xls') ?>');
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_period_month_from = $("#filter_period_month_from").combobox('getValue');
        var filter_period_month_to = $("#filter_period_month_to").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combogrid('getValue');

        var url = "?filter_period_month_from=" + filter_period_month_from + 
                "&filter_period_month_to=" + filter_period_month_to + 
                "&filter_period_year=" + filter_period_year +
                "&filter_item_fg_id=" + filter_item_fg_id + 
                "&filter_revision=" + filter_revision + 
                "&filter_customer_id=" + filter_customer_id;;

        window.location.assign('<?= base_url('pricing/cost_pattern/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    function componentCheck(p_month, p_year) {
        var p_month = $("#p_month").combobox('getValue');
        var p_year = $("#p_year").combobox('getValue');
        // var revision = $("#revision").combobox('getValue');

        $.ajax({
            type: "get",
            url: "<?= base_url('pricing/cost_pattern/check_rate') ?>",
            data: "p_month=" + window.btoa(p_month) +
                "&p_year=" + window.btoa(p_year),
            dataType: "json",
            success: function(rate) {
                if (rate.theme == "success") {
                    $('#check_rate').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_rate').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('pricing/cost_pattern/check_packaging_trans_cost') ?>",
            data: "p_month=" + window.btoa(p_month) +
                "&p_year=" + window.btoa(p_year),
            dataType: "json",
            success: function(packaging_trans_cost) {
                if (packaging_trans_cost.theme == "success") {
                    $('#check_packaging_trans_cost').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_packaging_trans_cost').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('pricing/cost_pattern/check_material_cost') ?>",
            data: "p_month=" + window.btoa(p_month) +
                "&p_year=" + window.btoa(p_year),
            dataType: "json",
            success: function(material_cost) {
                if (material_cost.theme == "success") {
                    $('#check_material_cost').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_material_cost').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('pricing/cost_pattern/check_process_cost') ?>",
            data: "p_month=" + window.btoa(p_month) +
                "&p_year=" + window.btoa(p_year),
            dataType: "json",
            success: function(process_cost) {
                if (process_cost.theme == "success") {
                    $('#check_process_cost').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_process_cost').checkbox({
                        checked: false
                    });
                }
            }
        });
    }

    $(function () {
        filter();
        $("#add").html('Generate');

        var p_month = $("#p_month").combobox('getValue');
        var p_year = $("#p_year").combobox('getValue');

        componentCheck(p_month, p_year);

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Generate',
                iconCls: 'icon-ok',
                handler: function () {

                    var p_month = $("#p_month").combobox('getValue');
                    var p_year = $("#p_year").textbox('getValue');
                    var revision = $("#revision").combobox('getValue');
                    var customer_id = $("#customer_id").combobox('getValue');
                    var model_name = $("#model_name").textbox('getValue');
                    var item_fg_id = $("#item_fg_id").combogrid('getValue');

                    var check_rate = $("#check_rate").checkbox('options');
                    var check_packaging_trans_cost = $("#check_packaging_trans_cost").checkbox('options');
                    var check_material_cost = $("#check_material_cost").checkbox('options');
                    var check_process_cost = $("#check_process_cost").checkbox('options');

                    if (
                        check_rate.checked &&
                        check_packaging_trans_cost.checked &&
                        check_material_cost.checked &&
                        check_process_cost.checked
                    ) {

                        $.messager.prompt(
                            'Generate Cost Pattern',
                            'Please input Password Generate',
                            function (r) {

                                if (r !== "GENERATE") return;

                                // === LOADING ===
                                Swal.fire({
                                    title: 'Please Wait 5 - 10 Minutes',
                                    text: 'Generating Cost Pattern...',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                $.ajax({
                                    type: "GET",
                                    url: "<?= base_url('pricing/cost_pattern/getdata') ?>",
                                    data: {
                                        p_month: btoa(p_month),
                                        p_year: btoa(p_year),
                                        revision: btoa(revision),
                                        customer_id: btoa(customer_id),
                                        model_name: btoa(model_name),
                                        item_fg_id: btoa(item_fg_id)
                                    },
                                    dataType: "json",
                                    success: function (rows) {

                                        if (!rows || rows.total === 0) {
                                            Swal.fire('Failed', 'No data to generate', 'error');
                                            return;
                                        }

                                        processSave(rows.total, rows);
                                    },
                                    error: function () {
                                        Swal.fire('Failed!', 'Process Calculating Data is Failed!', 'error');
                                    }
                                });
                            }
                        );

                    } else {
                        toastr.warning("Component Check Not Complete", "Information");
                    }
                }
            }]
        });

        function processSave(total, json, index = 0, success = 0, failed = 0) {

            if (index >= total) {
                localStorage.setItem('task_saved', 'yes');//untuk keperluan npd
                $('#dlg_insert').dialog('close');
                Swal.fire({
                    icon: 'success',
                    title: 'Completed',
                    text: 'Process Save Data Completed!',
                    showCancelButton: true,
                    confirmButtonText: 'Generate Again',
                    cancelButtonText: 'Close'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#dlg_insert').dialog('open');
                    }
                });

                return;
            }

            var progress = Math.floor(((index + 1) / total) * 100);
            $('#p_upload').progressbar('setValue', progress);
            $('#p_start').html(index + 1);
            $('#p_finish').html(total);

            $.post(
                "<?= base_url('pricing/cost_pattern/create') ?>",
                { data: json.rows[index] },
                function (res) {

                    var result = JSON.parse(res);

                    if (result.theme === 'success') {
                        $('#p_success').html(++success);
                    } else {
                        $('#p_failed').html(++failed);

                        $.post(
                            "<?= base_url('pricing/cost_pattern/uploadcreateFailed') ?>",
                            {
                                data: json.rows[index],
                                message: result.message
                            }
                        );
                    }

                    processSave(total, json, index + 1, success, failed);
                }
            ).fail(function () {
                Swal.fire({
                    title: 'Connection Issue',
                    text: 'Retrying...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                processSave(total, json, index, success, failed);
            });
        }
    });

    $('#filter_period_month_from').combobox({
        url: '<?= base_url('pricing/material_costs/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
    });

    $('#filter_period_month_to').combobox({
        url: '<?= base_url('pricing/material_costs/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
    });

    $('#filter_period_year').combobox({
        url: '<?= base_url('pricing/material_costs/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
    });
    
    $('#filter_item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/reads/001') ?>',
        panelWidth: 420,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No",
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

    $('#item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/reads') ?>',
        panelWidth: 500,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product Number.",
        columns: [[
            { field: 'id', title: 'Product ID', width: 200 },
            { field: 'number', title: 'Product No.', width: 150 },
            { field: 'name', title: 'Product Name', width: 150 }
        ]],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target)
                    .combogrid('clear')
                    .combogrid('textbox')
                    .focus();
            }
        }]
    });

    $('#customer_id').combogrid({
        url: '<?= base_url('master/customers/reads/'); ?>',
        panelWidth: 420,
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
            }, {
                field: 'currency',
                title: 'Currency',
                width: 100
            }, ]
        ]
    });

    $('#p_month').combobox({
        url: '<?= base_url('pricing/cost_pattern/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onChange: function(row) {
            var p_month = $("#p_month").combobox('getValue');
            var p_year = $("#p_year").combobox('getValue');
            // var revision = $("#filter_revision").combobox('getValue');

            if (p_year != "") {
                componentCheck(p_month, p_year);
            }

        }
    });

    $('#p_year').combobox({
        url: '<?= base_url('pricing/cost_pattern/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onChange: function(row) {
            var p_month = $("#p_month").combobox('getValue');
            var p_year = $("#p_year").combobox('getValue');
            // var revision = $("#filter_revision").combobox('getValue');

            if (p_month != "") {
                componentCheck(p_month, p_year);
            }

        }
    });

    $('#revision').combobox({
        url: '<?php echo base_url('pricing/cost_pattern/readRevisions'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Revision',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }]
        // ,onChange: function(row) {
        //     var month = $("#filter_month").combobox('getValue');
        //     var year = $("#filter_year").combobox('getValue');
        //     var revision = $("#filter_revision").combobox('getValue');

        //     if (month != "" || year != "") {
        //         componentCheck(month, year, revision);
        //     }
        // }
    });

    $('#filter_customer_id').combogrid({
        url: '<?= base_url('master/customers/reads'); ?>',
        panelWidth: 500,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Customer",
        columns: [
            [{
                field: 'id',
                title: 'Customer ID',
                width: 150
            }, {
                field: 'number',
                title: 'Customer Code',
                width: 150
            }, {
                field: 'name',
                title: 'Customer Name',
                width: 200
            }]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

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

   function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        } else if (value == 2) {
            return "<b style='color:white;'>COMPLETE</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else if (value == 2) {
            return 'background-color:#4B54E7;';
        }
    }

    //Number Format Currency
    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    //CELLSTYLE APPROVE
    function styleApproved(value, row, index) {
        if (value == "" || value === null ) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    //FORMATTER APPROVE
    function formatApproved(value) {
        if (value == "" || value === null ) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    // function print_cp() {
    //     var p_month    = $("#filter_period_month").combobox('getValue');
    //     var p_year     = $("#filter_period_year").combobox('getValue');
    //     var item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
    //     var revision   = $("#filter_revision").combobox('getValue');

    //     if (!p_month || !p_year || !item_fg_id || !revision) {
    //         toastr.warning("Please Choose Data First!", "Information");
    //         return;
    //     }

    //     $.ajax({
    //         type: "POST",
    //         url: "<?= base_url('pricing/cost_pattern/checkApproval') ?>",
    //         data: {
    //             p_month    : p_month,
    //             p_year     : p_year,
    //             item_fg_id : item_fg_id,
    //             revision   : revision
    //         },
    //         dataType: "json",
    //         success: function(response) {
    //             console.log(response);
    //             if (!response.approved_to) {
    //                 toastr.warning("Data has not been approved yet", "Information");
    //                 return;
    //             }

    //             var params = 
    //                 "p_month="    + encodeURIComponent(p_month) +
    //                 "&p_year="     + encodeURIComponent(p_year) +
    //                 "&item_fg_id=" + encodeURIComponent(item_fg_id) +
    //                 "&revision="   + encodeURIComponent(revision);

    //             window.open("<?= base_url('pricing/cost_pattern/print_cp') ?>?" + params,"_blank");
    //         },
    //         error: function() {
    //             toastr.error(
    //                 "An error occurred while checking approval data!",
    //                 "Error"
    //             );
    //         }
    //     });
    // }

    function btnPrint(val, row) {
        var print = "print_cp('" + row.p_month + "', '" + row.p_year + "', '" + row.item_fg_id + "', '" + row.revision + "')"; 
        return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
    }

    function print_cp(p_month, p_year, item_fg_id, revision) {
        if (!p_month || !p_year || !item_fg_id) {
            alert("Data tidak lengkap!");
            return;
        }

        var url = "<?= base_url('pricing/cost_pattern/print_cp/') ?>" + 
                window.btoa(p_month) + '/' + 
                window.btoa(p_year) + '/' + 
                window.btoa(item_fg_id) + '/' + 
                window.btoa(revision);

        window.open(url, "_blank", "width=1200,height=600");
    }

    // UPLOAD
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('pricing/cost_pattern/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('pricing/cost_pattern/upload') ?>',
                    onSubmit: function() {
                        if ($(this).form('validate') == false) {
                            return $(this).form('validate');
                        } else {
                            $.messager.progress({
                                title: 'Please Wait',
                                msg: 'Importing Excel to Database'
                            });
                        }
                    },
                    success: function(result) {
                        $.messager.progress('close');
                        //Clear File
                        $.ajax({
                            url: "<?= base_url('pricing/cost_pattern/uploadclearFailed') ?>"
                        });
                        var json = eval('(' + result + ')');
                        requestData(json.total, json);

                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                            if (value < 100) {
                                value = Math.floor((number / total) * 100);
                                $('#p_upload2').progressbar('setValue', value);
                                $('#p_start2').html(number);
                                $('#p_finish2').html(total);

                                $.ajax({
                                    type: "POST",
                                    async: true,
                                    url: "<?= base_url('pricing/cost_pattern/uploadCreate') ?>",
                                    data: {
                                        "data": json[number - 1]
                                    },
                                    cache: false,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            $('#p_success2').html(success);
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            $('#p_failed2').html(failed);
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                            //Json Failed
                                            $.ajax({
                                                type: "POST",
                                                async: true,
                                                url: "<?= base_url('pricing/cost_pattern/uploadcreateFailed') ?>",
                                                data: {
                                                    data: json[number - 1],
                                                    message: result.message
                                                },
                                                cache: false
                                            });
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }
                                        $("#p_remarks2").append(title + "<br>");
                                    }
                                });
                            }
                        }
                    }
                });
            }
        }]
    });
</script>
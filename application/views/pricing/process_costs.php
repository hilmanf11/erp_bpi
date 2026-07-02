<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATION" style="padding: 20px;">
            <ul>
                <li>The Data Customers is taken from <b>Master Data > Marketing > Customers</b></li>
                <li>The Data Line Production is taken from <b>Master Data > General Master > Line Production</b></li>
                <li>The Data Sales Order No is taken from the results of Customer selection and Get Data <b>Sales Order</b> Module</li>
                <li>The Data Product No is taken from the results of Sales Order No selection</li>
            </ul>
        </div>
        <div title="CONDITION" style="padding: 20px;">
            <ul>
                <li>If Status <b style="color: green">OPEN</b> then data new created in <b>Production Schedules</b></li>
                <li>If Status <b style="color: orange">SUPPLY</b> then data has been created in <b>Supply Sheet</b> when qty balance = 0</li>
                <li>If Status <b style="color: red">CLOSED</b> then data has been Scanned in <b>Scan Receipt FG</b></li>
                <li>If Qty in Production Schedule > Qty in Sales Order then <b style="color: red">ERROR</b></li>
            </ul>
        </div>
    </div>
</div>

<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar"rownumbers="true"singleSelect="true"fitColumns="false">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'p_month',width:60,align:'center'">Month</th>
            <th rowspan="2" data-options="field:'p_year',width:70,align:'center'">Year</th>
            <th rowspan="2" data-options="field:'revision',width:70,align:'center'">Rev</th>
            <th rowspan="2" data-options="field:'item_fg_number',width:140">Product No</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:150">Product Name</th>
            <th rowspan="2" data-options="field:'customer_name',width:250">Customer Name</th>
            <th rowspan="2" data-options="field:'cycle_time',width:90,align:'right'">CT</th>
            <th rowspan="2" data-options="field:'cycle_time_process',width:110,align:'right'">CT Process</th>
            <th rowspan="2" data-options="field:'cavity_standard',width:80,align:'center'">Cavity</th>
            <th rowspan="2" data-options="field:'toonage',width:80,align:'center'">Tonage</th>
            <th rowspan="2" data-options="field:'plain_rate_sec',width:110,align:'right'">Plain Rate</th>
            <th rowspan="2" data-options="field:'labour_cost',width:110,align:'right'">Labour Cost</th>
            <th rowspan="2" data-options="field:'total_process_cost',width:120,align:'right'">Process Cost</th>
            <th rowspan="2" data-options="field:'total_material_cost',width:130,align:'right'">Material Cost</th>
            <th rowspan="2" data-options="field:'ng_ratio',width:90,align:'right'">NG %</th>
            <th rowspan="2" data-options="field:'ng_ratio_cost',width:120,align:'right'">NG Cost</th>
            <th rowspan="2" data-options="field:'adm_foh',width:90,align:'right'">ADM %</th>
            <th rowspan="2" data-options="field:'adm_foh_cost',width:120,align:'right'">ADM Cost</th>
            <th rowspan="2" data-options="field:'mtn',width:90,align:'right'">MTN %</th>
            <th rowspan="2" data-options="field:'mtn_cost',width:120,align:'right'">MTN Cost</th>
            <th rowspan="2" data-options="field:'profit',width:90,align:'right'">Profit %</th>
            <th rowspan="2" data-options="field:'profit_nominal',width:130,align:'right'">Profit</th>
            <th rowspan="2" data-options="field:'purging',width:90,align:'center'">Purging</th>
            <th rowspan="2" data-options="field:'purging_value',width:120,align:'right'">Purging Value</th>
            <th rowspan="2" data-options="field:'start_setting',width:120,align:'right'">Start Setting</th>
            <th rowspan="2" data-options="field:'purging_cost',width:120,align:'right'">Purging Cost</th>
            <th rowspan="2" data-options="field:'moq',width:90,align:'right'">MOQ</th>
            <th rowspan="2" data-options="field:'depreciation',width:100,align:'center'">Depreciation</th>
            <th rowspan="2" data-options="field:'mold_depreciation',width:140,align:'right'">Mold Dep.</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th> 
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>

        <tr>
            <th data-options="field:'created_by',width:100,align:'center'">By</th>
            <th data-options="field:'created_date',width:150,align:'center'">Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'">By</th>
            <th data-options="field:'updated_date',width:150,align:'center'">Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 240px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:25%; display:inline-block;">Period</span>
                <input style="width:20%;" id="filter_period_month" value="<?= date("m") ?>" class="easyui-combobox">
                <input style="width:20%;" id="filter_period_year" value="<?= date("Y") ?>" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:25%; display:inline-block;">Revision</span>
                <select style="width:40%;" id="filter_revision" class="easyui-combobox" panelHeight="auto">
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
                <input style="width:40%;" id="filter_item_fg_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:25%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
    </fieldset>
    <?= $button ?>
    <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="close_ps()"><i class="fa fa-times"></i> Complete/Open</a> -->
</div>
<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1400px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
                <div style="width: 30%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Period</span>
                        <input style="width:30%;" name="p_month" id="p_month" required="" class="easyui-combobox">
                        <input style="width:30%;" name="p_year" id="p_year" required="" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Revision</span>
                        <select style="width:60%;" name="revision" id="revision" required="" class="easyui-combobox" panelHeight="auto">
                            <option value="" selected disabled>Choose Revision</option>
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Product Id</span>
                        <input style="width:60%;" name="item_fg_id" id="item_fg_id" readonly class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product No</span>
                        <input style="width:60%;" name="item_fg_number" id="item_fg_number" required="" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product Name</span>
                        <input style="width:60%;" name="item_fg_name" id="item_fg_name" readonly class="easyui-textbox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Customer id</span>
                        <input style="width:60%;" name="customer_id" id="customer_id" readonly class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Customer</span>
                        <input style="width:60%;" name="customer_name" id="customer_name" required="" class="easyui-combogrid">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Cycle Time (sec)</span>
                        <input style="width:60%;" name="cycle_time" id="cycle_time" precision="2" readonly class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">CT Process (sec)</span>
                        <input style="width:60%;" name="cycle_time_process" id="cycle_time_process" precision="2" readonly class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Cavity</span>
                        <input style="width:60%;" name="cavity_standard" id="cavity_standard" readonly class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Tonage</span>
                        <input style="width:60%;" name="toonage" id="toonage" readonly class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Rate Year</span>
                        <input style="width:60%;" name="rate_year" id="rate_year" required="" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Plain Rate</span>
                        <input style="width:60%;" name="plain_rate_sec" id="plain_rate_sec" readonly precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Total Process Cost</span>
                        <input style="width:60%;" name="total_process_cost" id="total_process_cost" readonly precision ="2" class="easyui-numberbox">
                    </div>
                </div>
                <div style="width: 30%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Material Cost Year</span>
                        <input style="width:60%;" name="mat_year" id="mat_year" required="" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Material Cost Month</span>
                        <input style="width:60%;" name="mat_month" id="mat_month" required="" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Total Material Cost</span>
                        <input style="width:60%;" name="total_material_cost" id="total_material_cost" readonly precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Total Material Cost Virgin</span>
                        <input style="width:60%;" name="total_material_cost_virgin" id="total_material_cost_virgin" readonly precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">NG Ratio %</span>
                        <input style="width:60%;" name="ng_ratio" id="ng_ratio" required="" precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">NG Ratio Cost</span>
                        <input style="width:60%;" name="ng_ratio_cost" id="ng_ratio_cost" readonly precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">ADM $ FOH %</span>
                        <input style="width:60%;" name="adm_foh" id="adm_foh" required="" precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">ADM $ FOH Cost</span>
                        <input style="width:60%;" name="adm_foh_cost" id="adm_foh_cost" readonly precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">MTN %</span>
                        <input style="width:60%;" name="mtn" id="mtn" required="" precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">MTN Cost</span>
                        <input style="width:60%;" name="mtn_cost" id="mtn_cost" readonly precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Profit %</span>
                        <input style="width:60%;" name="profit" id="profit" required="" precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Profit</span>
                        <input style="width:60%;" name="profit_nominal" id="profit_nominal" readonly precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Depreciation</span>
                        <select style="width:60%;" name="depreciation" id="depreciation" required="" class="easyui-combobox" panelHeight="auto">
                            <option value="YES">YES</option>
                            <option value="NO">NO</option>
                        </select>
                    </div>
                </div>
                <div style="width: 30%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purging</span>
                        <select style="width:60%;" name="purging" id="purging" required="" class="easyui-combobox" panelHeight="auto">
                            <option value="YES">YES</option>
                            <option value="NO">NO</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Volume</span>
                        <input style="width:60%;" name="machine_volume" id="machine_volume" readonly precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Qty Max Purging</span>
                        <input style="width:60%;" name="qty_max_purging" id="qty_max_purging" required="" precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purging</span>
                        <input style="width:60%;" name="purging_value" id="purging_value" readonly precision ="4" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Start Setting</span>
                        <input style="width:60%;" name="start_setting" id="start_setting" readonly precision ="4" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purging Cost</span>
                        <input style="width:60%;" name="purging_cost" id="purging_cost" readonly precision ="4" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">MOQ</span>
                        <input style="width:60%;" name="moq" id="moq" readonly precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Mold Price</span>
                        <input style="width:60%;" name="mold_price" id="mold_price" readonly precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem" >
                        <span style="width:35%; display:inline-block;">Mold Id</span>
                        <input style="width:60%;" name="mold_id" id="mold_id" readonly class="easyui-textbox">
                    </div>
                    <div class="fitem" >
                        <span style="width:35%; display:inline-block;">Mold Name</span>
                        <input style="width:60%;" name="mold_name" id="mold_name" readonly class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Lifetime</span>
                        <input style="width:60%;" name="lifetime" id="lifetime" readonly precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Vol/m</span>
                        <input style="width:60%;" name="volume" id="volume" required="" precision ="2" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Mold Depreciation</span>
                        <input style="width:60%;" name="mold_depreciation" id="mold_depreciation" readonly precision ="2" class="easyui-numberbox">
                    </div>
                </div>
        </fieldset>
    </form>
</div>

<!-- Upload -->
<!-- <div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
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
</div> -->

<!-- PDF -->
<iframe id="printout" src="<?= base_url('pricing/process_costs/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //HELP
    function helps() {
        $('#dlg_help').dialog('open');
    }
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('pricing/process_costs/create') ?>';
        $('#frm_insert').form('clear');
        $("#item_fg_id").combogrid('enable');
        $("#item_fg_name").textbox('enable');
    }
    //Edit Data
    // function update() {
    //     var row = $('#dg').datagrid('getSelected');
    //     console.log(row);
    //     if (row) {
    //         $('#dlg_insert').dialog('open');
    //         $("#item_fg_id").combogrid('disable');
    //         $("#item_fg_name").textbox('disable');
    //         url_save = '<?= base_url('pricing/process_costs/update') ?>?id=' + btoa(row.id);

    //         $('#item_fg_id').combogrid({
    //             url: '<?= base_url('pricing/process_costs/readItems/') ?>',
    //             panelWidth: 420,
    //             idField: 'id',
    //             textField: 'number',
    //             mode: 'remote',
    //             fitColumns: true,
    //             prompt: "Choose Item",
    //             icons: [{
    //                 iconCls: 'icon-clear',
    //                 handler: function(e) {
    //                     $(e.data.target).combogrid('clear').combogrid('textbox').focus();
    //                 }
    //             }],
    //             columns: [[{
    //                 field: 'number',
    //                 title: 'Product No',
    //                 width: 200
    //             }, {
    //                 field: 'name',
    //                 title: 'Product Name',
    //                 width: 200
    //             }]],
    //                 onLoadSuccess: function(){
    //                     $("#item_fg_id").textbox('setValue', row.item_fg_id);
    //                     $("#item_fg_name").textbox('setValue', row.item_name);
    //                     $("#color").textbox('setValue', row.color);
    //                     $("#qty").textbox('setValue', row.qty);
    //                     $("#status_subcont").textbox('setValue', row.status_subcont);
    //                     $("#subcont_type").textbox('setValue', row.subcont_type);
    //                     $("#machine_id").combobox('setValue', row.machine_id);

    //                     // $.ajax({
    //                     //     url: '<?= base_url('pricing/process_costs/readPurging/') ?>' + window.btoa(row.machine_id) + "/" + row.color,
    //                     //     type: 'GET',
    //                     //     dataType: 'json',
    //                     //     success: function(response) {
    //                     //         if(response.length > 0) {
    //                     //             $("#total_purging").textbox('setValue', response[0].total);
    //                     //         } else {
    //                     //             $("#total_purging").textbox('setValue', 0);
    //                     //         }
    //                     //     }
    //                     // });
                            
    //                 }
    //         });

    //         $("#machine_id").combobox({
    //             url: '<?= base_url('pricing/process_costs/readMachines/') ?>',
    //             valueField: 'id',
    //             textField: 'number',
    //             prompt: "Choose Machine No",
    //             onSelect: function(machine){
    //                 $.ajax({
    //                     url: '<?= base_url('pricing/process_costs/readPurging/') ?>' + window.btoa(machine.id) + "/" + row.color,
    //                     type: 'GET',
    //                     dataType: 'json',
    //                     success: function(response) {
    //                         if(response.length > 0) {
    //                             $("#total_purging").textbox('setValue', response[0].total);
    //                         } else {
    //                             $("#total_purging").textbox('setValue', 0);
    //                         }
    //                     }
    //                 });
    //             }
    //         });

    //         $('#frm_insert').form('load', row);

            
    //     } else {
    //         toastr.warning("Please select one of the data in the table first!", "Information");
    //     }
    // }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        console.log(rows);
        if (rows.length > 0) {
                $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                    if (r) {
                        for (var i = 0; i < rows.length; i++) {
                            var row = rows[i];
                            if(row.status == 0){
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('pricing/process_costs/delete') ?>',
                                    data: {
                                        id: row.id,
                                        item_fg_id: row.item_fg_id
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error(jqXHR.statusText);
                                        $.messager.alert("Error", jqXHR.statusText, 'error');
                                    },
                                    complete: function(data) {
                                        $('#dg').datagrid('reload');
                                    }
                                });
                            } else {
                                toastr.warning("Production Schedule is Closed!", "Information");
                            }
                        }
                    }
                });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter() {
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

        var url = "?filter_period_month=" + filter_period_month + "&filter_period_year=" + filter_period_year +
            "&filter_item_fg_id=" + filter_item_fg_id;

        $('#dg').datagrid({
            url: '<?= base_url('pricing/process_costs/datatables') ?>' + url,
            fit: true,
            pagination: true,
            rownumbers: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('pricing/process_costs/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

        var url = "?filter_period_month=" + filter_period_month + "&filter_period_year=" + filter_period_year +
            "&filter_item_fg_id=" + filter_item_fg_id;


        window.location.assign('<?= base_url('pricing/process_costs/print/excel') ?>' + url);
    }

    // UPLOAD DATA
    // function upload() {
    //     $('#dlg_upload').dialog('open');
    // }
    // // DOWNLOAD
    // function download_excel() {
    //     window.location.assign('<?= base_url('template/tmp_production_schedules.xls') ?>');
    // }

    function reload() {
        window.location.reload();
    }
    $(function() {
        filter();

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                                localStorage.setItem('task_saved', 'yes');//untuk keperluan npd
                            } else {
                                toastr.error(result.message, result.title);
                            }
                            $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });
       
    });

    $('#filter_period_month').combobox({
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

    $('#p_month').combobox({
        url: '<?= base_url('pricing/process_costs/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
        onChange: function(newValue, oldValue) {
            getPackagingVolume();
        }
    });

    $('#p_year').combobox({
        url: '<?= base_url('pricing/process_costs/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
        onChange: function(newValue, oldValue) {
            getPackagingVolume();
        }
    });

    $('#item_fg_number').combogrid({
        url: '<?= base_url('pricing/process_costs/readItems'); ?>',
        panelWidth: 500,
        idField: 'number',
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
        }],
        onSelect: function(index, row) {
            $('#item_fg_id').textbox('setValue', row.id);
            $('#item_fg_name').textbox('setValue', row.name);
            $('#cycle_time').numberbox('setValue', row.cycle_time);
            $('#cycle_time_process').numberbox('setValue', row.cycle_time_process);
            $('#cavity_standard').numberbox('setValue', row.cavity_standard);
            $('#toonage').numberbox('setValue', row.toonage);
            $('#machine_volume').numberbox('setValue', row.machine_volume);
            $('#mold_price').numberbox('setValue', row.mold_price);
            $('#mold_id').textbox('setValue', row.mold_id);
            $('#mold_name').textbox('setValue', row.mold_name);
            $('#lifetime').numberbox('setValue', row.lifetime);

            let item_fg_id = row.id;
            initRateYear(row.toonage);
            initMatYear(item_fg_id);
            initMatMonth(item_fg_id);

            getPackagingVolume();
        }
    });

    function getPackagingVolume() {
        let p_month = $('#p_month').combobox('getValue');
        let p_year  = $('#p_year').combobox('getValue');
        let item_fg_id = $('#item_fg_id').textbox('getValue');

        if (p_month !== "" && p_year !== "" && item_fg_id !== "") {
            $.ajax({
                url: '<?= base_url('pricing/process_costs/getPackagingVolume'); ?>',
                type: 'POST',
                data: {
                    item_fg_id: item_fg_id,
                    p_month: p_month,
                    p_year: p_year
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#volume').numberbox('setValue', response.volume);
                    } else {
                        $('#volume').numberbox('setValue', 0);
                    }
                },
                error: function() {
                    console.log("Gagal mengambil data volume.");
                }
            });
        }
    }

    function initRateYear(toonage) {
        $('#rate_year').combobox({
            url: '<?= base_url('pricing/process_costs/readPeriod/year'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Years',
            panelHeight: 'auto',
            onSelect: function(row) {
                getPlainRate(toonage, row.id);
            }
        });

        $('#plain_rate_sec').numberbox('setValue', 0);
    }

    function initMatYear(item_fg_id) {
        $('#mat_year').combobox({
            url: '<?= base_url('pricing/process_costs/readPeriod/year'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Year',
            panelHeight: 'auto',
            onSelect: function(row) {
                let year = row.id;
                let month = $('#mat_month').combobox('getValue');

                if (month) {
                    getTotalMat(item_fg_id, year, month);
                }
            }
        });

        $('#total_material_cost').numberbox('setValue', 0);
        $('#total_material_cost_virgin').numberbox('setValue', 0);
    }

    function initMatMonth(item_fg_id) {
        $('#mat_month').combobox({
            url: '<?= base_url('pricing/process_costs/readPeriod/month'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Month',
            panelHeight: 'auto',
            onSelect: function(row) {
                let month = row.id;
                let year  = $('#mat_year').combobox('getValue');

                if (year) {
                    getTotalMat(item_fg_id, year, month);
                }
            }
        });
    }

    function getPlainRate(toonage, year) {
        if (!toonage || !year) {
            $('#plain_rate_sec').numberbox('setValue', 0);
            return;
        }

        $.ajax({
            type: 'POST',
            url: '<?= base_url('pricing/process_costs/get_plain_rate'); ?>',
            dataType: 'json',
            data: {
                toonage: toonage,
                year: year
            },
            success: function(data) {
                if (data.status === 'success') {
                    $('#plain_rate_sec').numberbox('setValue', data.plain_rate_sec);
                    calculateTotalProcessCost();
                } else {
                    $('#plain_rate_sec').numberbox('setValue', 0);
                    $.messager.show({
                        title: 'Info',
                        msg: 'Plain rate tidak ditemukan.'
                    });
                }
            },
            error: function() {
                $('#plain_rate_sec').numberbox('setValue', 0);
                $.messager.alert('Error', 'Gagal mengambil plain rate', 'error');
            }
        });
    }

    function getTotalMat(item_fg_id, year, month) {
        if (!item_fg_id || !year || !month) {
            $('#total_material_cost').numberbox('setValue', 0);
            $('#total_material_cost_virgin').numberbox('setValue', 0);
            return;
        }

        $.ajax({
            type: 'POST',
            url: '<?= base_url('pricing/process_costs/get_total_mat'); ?>',
            dataType: 'json',
            data: {
                item_fg_id: item_fg_id,
                year: year,
                month: month
            },
            success: function(data) {
                if (data.status === 'success') {
                    $('#total_material_cost').numberbox('setValue', data.total_material_cost);
                    $('#total_material_cost_virgin').numberbox('setValue', data.total_material_cost_virgin);
                } else {
                    $('#total_material_cost').numberbox('setValue', 0);
                    $('#total_material_cost_virgin').numberbox('setValue', 0);
                }

                calculateCostAndProfit();
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                $.messager.alert('Error', 'Gagal mengambil Total Material Cost', 'error');
            }
        });
    }

    $('#customer_name').combogrid({
        url: '<?= base_url('master/customers/reads/'); ?>',
        panelWidth: 420,
        idField: 'name',
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
        ],
        onSelect: function(value, rows) {
            $('#customer_id').textbox('setValue', rows.id);
        }
    });

    function calculateTotalProcessCost() {
        var plain_rate_sec     = parseFloat($('#plain_rate_sec').numberbox('getValue')) || 0;
        var cycle_time         = parseFloat($('#cycle_time').numberbox('getValue')) || 0;
        var cavity_standard    = parseFloat($('#cavity_standard').numberbox('getValue')) || 0;
        var cycle_time_process = parseFloat($('#cycle_time_process').numberbox('getValue')) || 0;

        if (cavity_standard === 0) {
            $('#total_process_cost').numberbox('setValue', 0);
            calculateCostAndProfit();
            return;
        }

        var total = (plain_rate_sec * (cycle_time / cavity_standard)) + cycle_time_process;

        $('#total_process_cost').numberbox('setValue', total);

        calculateCostAndProfit();
    }

    function calculateCostAndProfit() {
        var total_material_cost = parseFloat($('#total_material_cost').numberbox('getValue')) || 0;
        var total_process_cost  = parseFloat($('#total_process_cost').numberbox('getValue')) || 0;

        var base_cost = total_material_cost + total_process_cost;

        var ng_ratio = parseFloat($('#ng_ratio').numberbox('getValue')) || 0;
        var adm_foh  = parseFloat($('#adm_foh').numberbox('getValue')) || 0;
        var mtn      = parseFloat($('#mtn').numberbox('getValue')) || 0;
        var profit   = parseFloat($('#profit').numberbox('getValue')) || 0;

        $('#ng_ratio_cost').numberbox('setValue', (ng_ratio / 100) * base_cost);
        $('#adm_foh_cost').numberbox('setValue', (adm_foh / 100) * base_cost);
        $('#mtn_cost').numberbox('setValue', (mtn / 100) * base_cost);
        $('#profit_nominal').numberbox('setValue', (profit / 100) * base_cost);
    }

    function calculatePurgingValue() {
        var purging = $('#purging').combobox('getValue');

        var machine_volume             = parseFloat($('#machine_volume').numberbox('getValue')) || 0;
        var qty_max_purging            = parseFloat($('#qty_max_purging').numberbox('getValue')) || 0;
        var total_material_cost_virgin = parseFloat($('#total_material_cost_virgin').numberbox('getValue')) || 0;
        var plain_rate_sec             = parseFloat($('#plain_rate_sec').numberbox('getValue')) || 0;

        var start_setting = 10 * (total_material_cost_virgin / 1000) / 100;

        var purging_value = 0;

        if (purging === 'YES') {
            purging_value =
                (machine_volume * qty_max_purging * (total_material_cost_virgin / 1000)) +
                ((qty_max_purging * 10 * plain_rate_sec) * 2);
        }

        var purging_cost = purging_value + start_setting;

        $('#purging_value').numberbox('setValue', purging_value);
        $('#start_setting').numberbox('setValue', start_setting);
        $('#purging_cost').numberbox('setValue', purging_cost);
    }

    function calculateMOQ() {
        var cavity_standard = parseFloat($('#cavity_standard').numberbox('getValue')) || 0;
        var cycle_time      = parseFloat($('#cycle_time').numberbox('getValue')) || 0;

        if (cavity_standard === 0 || cycle_time === 0) {
            $('#moq').numberbox('setValue', 0);
            return;
        }

        var moq = (3600 / cavity_standard) * cycle_time * 7 * 0.85;

        $('#moq').numberbox('setValue', moq);
    }

    function calculateMoldDepreciation() {
        var depreciation = $('#depreciation').combobox('getValue');

        var mold_price = parseFloat($('#mold_price').numberbox('getValue')) || 0;
        var volume     = parseFloat($('#volume').numberbox('getValue')) || 0;
        var lifetime   = parseFloat($('#lifetime').numberbox('getValue')) || 0;

        var mold_depreciation = 0;

        if (depreciation === 'YES') {
            if (volume > 0 && lifetime > 0) {
                mold_depreciation = mold_price / (volume * lifetime);
            }
        }

        $('#mold_depreciation').numberbox('setValue', mold_depreciation);
    }


    $('#plain_rate_sec').numberbox({
        onChange: function () {
            calculateTotalProcessCost();
            calculatePurgingValue();
        }
    });

    $('#cycle_time').numberbox({
        onChange: function () {
            calculateTotalProcessCost();
            calculateMOQ();
        }
    });

    $('#cavity_standard').numberbox({
        onChange: function () {
            calculateTotalProcessCost();
            calculateMOQ();
        }
    });

    $('#cycle_time_process').numberbox({
        onChange: function () {
            calculateTotalProcessCost();
        }
    });

    $('#ng_ratio, #adm_foh, #mtn, #profit').numberbox({
        onChange: function () {
            calculateCostAndProfit();
        }
    });

    $('#purging').combobox({
        onChange: function () {
            calculatePurgingValue();
        }
    });

    $('#machine_volume, #qty_max_purging, #total_material_cost_virgin').numberbox({
        onChange: function () {
            calculatePurgingValue();
        }
    });

    $('#depreciation').combobox({
        onChange: function () {
            calculateMoldDepreciation();
        }
    });

    $('#mold_price, #volume, #lifetime').numberbox({
        onChange: function () {
            calculateMoldDepreciation();
        }
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

    // UPLOAD DATA
    // $('#dlg_upload').dialog({
    //     buttons: [{
    //         text: 'List Failed',
    //         handler: function() {
    //             window.open('<?= base_url('pricing/process_costs/uploadDownloadFailed') ?>', '_blank');
    //         }
    //     }, {
    //         text: 'Upload',
    //         iconCls: 'icon-ok',
    //         handler: function() {
    //             $('#frm_upload').form('submit', {
    //                 url: '<?= base_url('pricing/process_costs/upload') ?>',
    //                 onSubmit: function() {
    //                     if ($(this).form('validate') == false) {
    //                         return $(this).form('validate');
    //                     } else {
    //                         $.messager.progress({
    //                             title: 'Please Wait',
    //                             msg: 'Importing Excel to Database'
    //                         });
    //                     }
    //                 },
    //                 success: function(result) {
    //                     $.messager.progress('close');
    //                     //Clear File
    //                     $.ajax({
    //                         url: "<?= base_url('pricing/process_costs/uploadclearFailed') ?>"
    //                     });
    //                     var json = eval('(' + result + ')');
    //                     requestData(json.total, json);

    //                     function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
    //                         if (value < 100) {
    //                             value = Math.floor((number / total) * 100);
    //                             $('#p_upload').progressbar('setValue', value);
    //                             $('#p_start').html(number);
    //                             $('#p_finish').html(total);

    //                             $.ajax({
    //                                 type: "POST",
    //                                 async: true,
    //                                 url: "<?= base_url('pricing/process_costs/uploadCreate') ?>",
    //                                 data: {
    //                                     "data": json[number - 1]
    //                                 },
    //                                 cache: false,
    //                                 dataType: "json",
    //                                 success: function(result) {
    //                                     if (result.theme == "success") {
    //                                         $('#p_success').html(success);
    //                                         var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
    //                                         requestData(total, json, number + 1, value, success + 1, failed + 0);
    //                                     } else {
    //                                         $('#p_failed').html(failed);
    //                                         var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
    //                                         //Json Failed
    //                                         $.ajax({
    //                                             type: "POST",
    //                                             async: true,
    //                                             url: "<?= base_url('pricing/process_costs/uploadcreateFailed') ?>",
    //                                             data: {
    //                                                 data: json[number - 1],
    //                                                 message: result.message
    //                                             },
    //                                             cache: false
    //                                         });
    //                                         requestData(total, json, number + 1, value, success + 0, failed + 1);
    //                                     }
    //                                     $("#p_remarks").append(title + "<br>");
    //                                 }
    //                             });
    //                         }
    //                     }
    //                 }
    //             });
    //         }
    //     }]
    // });

    //untuk kebutuhan NPD
    $(document).ready(function() {
        if (localStorage.getItem('trigger_add') === 'yes') {
            localStorage.removeItem('trigger_add');

            $('<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:#ffffff;z-index:8999;"></div>').appendTo('body');

            setTimeout(function() {
                if (typeof add === "function") {
                    add(); 

                    var checkClose = setInterval(function() {
                        var isHidden = $('#dlg_insert').closest('.window').is(':hidden');
                        if (isHidden) {
                            clearInterval(checkClose); // Hentikan monitoring
                            if (window.parent.$('#dlg_outer_wrapper').length) {
                                window.parent.$('#dlg_outer_wrapper').dialog('close');
                            }
                        }
                    }, 500);
                }
            }, 1000); 
        }

        // ==========================================
        // SKRIP TRIGGER UPLOAD
        // ==========================================
        var urlParams = new URLSearchParams(window.location.search);
        var action = urlParams.get('action');

        if (action === 'upload') {
            $('<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:#ffffff;z-index:8999;"></div>').appendTo('body');

            setTimeout(function() {
                if (typeof upload === 'function') {
                    upload(); 
                    var checkCloseUpload = setInterval(function() {
                        var isHidden = $('#dlg_upload').closest('.window').is(':hidden'); 
                        if (isHidden) {
                            clearInterval(checkCloseUpload); 
                            if (window.parent.$('#dlg_upload_wrapper').length) {
                                window.parent.$('#dlg_upload_wrapper').dialog('close');
                            }
                        }
                    }, 500);
                }
            }, 500);
        }
    });
</script>
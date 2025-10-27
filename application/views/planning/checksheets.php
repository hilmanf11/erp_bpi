<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'" sortable="true">Checksheet ID</th>
            <th rowspan="2" data-options="field:'wo_no',width:150,halign:'center'" sortable="true">WO/DOC No</th>
            <th rowspan="2" data-options="field:'division',width:80,halign:'center'" sortable="true">Division</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'" sortable="true">Trans Date</th>
            <!-- <th rowspan="2" data-options="field:'wp',width:80,align:'center'" sortable="true">WP</th> -->
            <!-- <th rowspan="2" data-options="field:'product_id',width:150,align:'center'" sortable="true">Product Id</th> -->
            <th rowspan="2" data-options="field:'product_no',width:150,halign:'center'" sortable="true">Product No</th>
            <th rowspan="2" data-options="field:'product_name',width:200,halign:'center'" sortable="true">Product Name</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'" sortable="true">Uom</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat" sortable="true">Qty</th>
            <th rowspan="2" data-options="field:'receipt',width:80,halign:'center',align:'right',formatter:numberformat" sortable="true">Receipt</th>
            <th rowspan="2" data-options="field:'accumulate',width:80,halign:'center',align:'right',formatter:numberformat" sortable="true">Accumulate</th>
            <th rowspan="2" data-options="field:'balance',width:80,halign:'center',align:'right',formatter:numberformat" sortable="true">Balance</th>
            <th rowspan="2" data-options="field:'prod_date',width:80,align:'center'" sortable="true">Prod Date</th>
            <th rowspan="2" data-options="field:'packing_date',width:100,align:'center'" sortable="true">Packing Date</th>
            <th rowspan="2" data-options="field:'qc_1',width:100,align:'center'" sortable="true">QC 1</th>
            <th rowspan="2" data-options="field:'qc_2',width:100,align:'center'" sortable="true">QC 2</th>
            <th rowspan="2" data-options="field:'op_1',width:100,align:'center'" sortable="true">Operator 1</th>
            <th rowspan="2" data-options="field:'op_2',width:100,align:'center'" sortable="true">Operator 2</th>
            <th rowspan="2" data-options="field:'shift',width:80,align:'center'" sortable="true">Shift</th>
            <th rowspan="2" data-options="field:'packing',width:80,align:'center',formatter:packingformat" sortable="true">Packing</th>
            <th rowspan="2" data-options="field:'packing_qty',width:80,align:'center'" sortable="true">Packing Qty</th>
            <th rowspan="2" data-options="field:'label',width:80,align:'center'">Qty Label</th>
            <th rowspan="2" data-options="field:'print',width:80,align:'center',formatter:BtnPrint">Print</th>
            <th rowspan="2" data-options="field:'recreate',width:80,align:'center',formatter:BtnReCreate">ReCreate</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformat,styler:statusStyle" sortable="true">Status</th>
            <th rowspan="2" data-options="field:'total_scan',width:80,align:'center',formatter:statusFormatScan,styler:statusStyleScan">Status<br>Label</th>
            <th rowspan="2" data-options="field:'document_no',width:160,align:'center'" sortable="true">WIP No</th>
            <th rowspan="2" data-options="field:'remarks',width:160,align:'left'" sortable="true">Remark</th>
            <th rowspan="2" data-options="field:'status_subcont',width:100,align:'left'" sortable="true">Status Subcont</th>
            <th rowspan="2" data-options="field:'subcont_type',width:100,align:'left'" sortable="true">Subcont Type</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'" sortable="true"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'" sortable="true"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'" sortable="true"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'" sortable="true"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 230px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">    
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="filter_division" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Wo No</span>
                    <input style="width:60%;" id="filter_wo_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Checksheet</span>
                    <input style="width:60%;" id="filter_checksheet" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
                </div>
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
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">Select All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status Subcont</span>
                    <select style="width:60%;" id="filter_status_subcont" class="easyui-combobox" panelHeight="auto">
                        <option value="">Select All</option>
                        <option value="YES">YES</option>
                        <option value="NO">NO</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Subcont TYpe</span>
                    <select style="width:60%;" id="filter_subcont_type" class="easyui-combobox" panelHeight="auto">
                        <option value="">Select All</option>
                        <option value="Jasa">Jasa</option>
                        <option value="Finished Good">Finished Good</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="close_fc()"><i class="fa fa-times"></i> Close/Open</a>
    </div>
</div>
<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 900px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Final Checksheet Type</span>
                        <select style="width:60%;" name="checksheet_type" id="checksheet_type" required="" panelHeight="auto" class="easyui-combobox">
                            <option value="">Choose Type</option>
                            <option value="Output Production">Output Production</option>
                            <option value="Output Repair">Output Repair</option>
                        </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:60%;" name="trans_date" id="trans_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Checksheet ID</span>
                    <input style="width:60%;" name="number" id="number" class="easyui-textbox" data-options="prompt:'Automatic'" readonly>
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="product_no" class="easyui-combogrid">
                </div>
                <div class="fitem" id="lotno">
                    <span style="width:35%; display:inline-block;">Lot No</span>
                    <input style="width:60%;" name="lot_no" id="lot_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">WO/PO No</span>
                    <input style="width:60%;" name="wo_no" id="wo_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product ID</span>
                    <input style="width:60%;" id="item_fg_id" name="item_fg_id" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Name</span>
                    <input style="width:60%;" id="product_name" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status Subcont</span>
                    <input style="width:60%;" name="status_subcont" id="status_subcont" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Subcont Type</span>
                    <input style="width:60%;" name="subcont_type" id="subcont_type" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="division" name="division" readonly="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">WO Qty</span>
                    <input style="width:30%;" name="qty" id="qty" required="" readonly="" data-options="precision:'2'" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt Qty</span>
                    <input style="width:30%;" name="receipt" id="receipt"  required="" data-options="precision:'2'" class="easyui-numberbox">
                </div>
            </div>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Accumulate</span>
                    <input style="width:30%;" name="accumulate" id="accumulate" readonly data-options="precision:'2'" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Balance Qty</span>
                    <input style="width:30%;" name="balance" id="balance"  readonly data-options="precision:'2'" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Prod Date</span>
                    <input style="width:60%;" name="prod_date" id="prod_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Packing Date</span>
                    <input style="width:60%;" name="packing_date" id="packing_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">QC 1</span>
                    <input style="width:60%;" name="qc_1" id="qc_1"  required="" class="easyui-combobox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">QC 1</span>
                    <input style="width:60%;" name="qcnumber_1" id="qcnumber_1" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">QC 2</span>
                    <input style="width:60%;" name="qc_2" id="qc_2" class="easyui-combobox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">QC 2</span>
                    <input style="width:60%;" name="qcnumber_2" id="qcnumber_2" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Operator 1</span>
                    <input style="width:60%;" name="op_1" id="op_1" required="" class="easyui-combobox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">OP 1</span>
                    <input style="width:60%;" name="opnumber_1" id="opnumber_1" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Operator 2</span>
                    <input style="width:60%;" name="op_2" id="op_2" class="easyui-combobox">
                </div> 
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">OP 2</span>
                    <input style="width:60%;" name="opnumber_2" id="opnumber_2" class="easyui-textbox">
                </div>
                <div class="fitem">
                <span style="width:35%; display:inline-block;">Shift</span>
                    <select style="width:60%;" name="shift" id="shift" required="" panelHeight="auto" class="easyui-combobox" editable="false">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
                <div class="fitem">
                <span style="width:35%; display:inline-block;">Packing Qty</span>
                    <select style="width:40%;" name="packing" id="packing" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="1">MPQ</option>
                        <option value="2">Qty per BOX</option>
                        <option value="3">User Entry</option>
                    </select>
                    <input style="width:20%;" name="packing_qty" id="packing_qty" class="easyui-textbox">
                </div>
                <div class="fitem"hidden>
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty label</span>
                    <input style="width:30%;" name="label" id="label" required="" readonly class="easyui-numberbox">
                </div>
            </div>
        </fieldset>
    </form>
</div>
<!-- DIALOG REPRINT -->
<div id="dlg_reprint" class="easyui-dialog" title="Reprint Reason" data-options="closed: true,modal:true" style="width: 300px; padding:10px; top: 20px;">
    <form id="frm_insert_reprint" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Reprint Reason</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Reprint Reason</span>
                <input style="width:60%;" name="reason" id="reason" class="easyui-textbox" multiline="true">
            </div>
            <div style="margin-top: 10px;">
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" onclick="saveReprintReason()">Save</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="closeDialog()">Cancel</a>
            </div>
        </fieldset>
    </form>
</div>
<!-- DIALOG LABEL -->
<div id="dlg_label" class="easyui-dialog" title="Create Data Label" data-options="closed: true,modal:true,closable: true" style="width: 500px; padding:10px; top: 20px;">
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Create Label" class="easyui-panel" style="width:100%; height:300px; padding:10px; margin-top: 10px;">
        <ul id="remarks">

        </ul>
    </div>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/checksheets/print') ?>" style="width: 100%;" hidden></iframe>

<audio id="balanceMinus">
    <source src="<?= base_url('assets/audio/balance_minus.mp3') ?>" type="audio/mp3">
</audio>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('planning/checksheets/create') ?>';
        $('#frm_insert').form('clear');
        $("#trans_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#prod_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#packing_date").datebox('setValue', "<?= date("Y-m-d") ?>");

        $("#lotno").hide();
        $("#trans_date").datebox('enable');
        $("#number").textbox('enable');
        $("#wo_no").textbox('enable');
        $("#item_fg_id").textbox('enable');
        $("#product_name").textbox('enable');
        $("#product_no").combogrid('enable');
        $("#qty").numberbox('enable');
        $("#receipt").numberbox('enable');
        $("#accumulate").numberbox('enable');
        $("#balance").numberbox('enable');
        $("#packing").combobox('enable');
        $("#packing_qty").textbox('enable');
        $("#label").numberbox('enable');

        $("#checksheet_type").combobox({
            onChange: function(val) {
                var type = $("#checksheet_type").combobox('getValue');

                if(type == "Output Production"){
                    $("#lotno").hide();

                    $('#product_no').combogrid({
                        url: '<?= base_url('planning/checksheets/readWoNo') ?>',
                        panelWidth: 550,
                        idField: 'product_no',
                        textField: 'product_no',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: "Choose Product No",
                        columns: [
                            [{
                                field: 'period',
                                title: 'Period',
                                width: 150
                            }, {
                                field: 'lot_no',
                                title: 'Lot No',
                                width: 100,
                                align: 'left'
                            }, {
                                field: 'wo_no',
                                title: 'Wo No',
                                width: 100,
                                align: 'left'
                            }, {
                                field: 'product_no',
                                title: 'Product No',
                                width: 200,
                                align: 'left'
                            }]
                        ],
                        onSelect: function(val, row) {
                            console.log(row);
                            $("#period").textbox('setValue', row.period);
                            $("#item_fg_id").textbox('setValue', row.item_fg_id);
                            $("#product_name").textbox('setValue', row.product_name);
                            $("#qty").numberbox('setValue', row.qty);
                            $("#wo_no").textbox('setValue', row.wo_no);
                            $("#balance").textbox('setValue', '0');
                            $("#division").textbox('setValue', row.division);
                            $("#status_subcont").textbox('setValue', row.status_subcont);
                            $("#subcont_type").textbox('setValue', row.subcont_type);

                            var wo_no = row.wo_no;
                            console.log(wo_no);
                            $.ajax({
                                url: '<?= base_url("planning/checksheets/checkWo_no/") ?>' + window.btoa(wo_no), 
                                method: 'GET',
                                dataType: 'json',
                                success: function(data) {
                                    console.log(data);
                                    accumulateAjax = data[0].qty;
                                    $("#accumulate").numberbox('setValue', data[0].qty);
                                }
                            });

                            $('#receipt').numberbox({
                                onChange: function(value) {
                                    if(value != ""){
                                        var qty = $("#qty").numberbox("getValue");
                                        var receipt = $("#receipt").numberbox('getValue');

                                        var calculate = parseFloat(receipt) + parseFloat(accumulateAjax);
                                        var result = parseFloat(qty) - parseFloat(calculate);

                                        var balance = $("#balance").numberbox('setValue', result);
                                        var accumulate_total = $("#accumulate").numberbox('setValue', calculate);

                                        if (result < 0) {
                                            toastr.warning("Balance minus, please correct your Receipt!");
                                            $("#receipt").numberbox('setValue', 0);
                                            $("#accumulate").numberbox('setValue', accumulate);
                                            balanceMinus.play();
                                        } else {
                                            return result;
                                        }
                                    }else{
                                        $("#receipt").numberbox('setValue', 0);
                                    }
                                }
                            });
                        }
                    });
                }else{
                    $("#lotno").show();
                    $('#product_no').combogrid({
                        url: '<?= base_url('planning/checksheets/readRepairNo') ?>',
                        panelWidth: 550,
                        idField: 'product_no',
                        textField: 'product_no',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: "Choose Document No",
                        columns: [
                            [{
                                field: 'period',
                                title: 'Period',
                                width: 150
                            }, {
                                field: 'lot_no',
                                title: 'Lot No',
                                width: 100,
                                align: 'left'
                            }, {
                                field: 'wo_no',
                                title: 'Wo No',
                                width: 100,
                                align: 'left'
                            }, {
                                field: 'product_no',
                                title: 'Product No',
                                width: 200,
                                align: 'left'
                            }]
                        ],
                        onSelect: function(val, row) {
                            console.log(row);
                            $("#item_fg_id").textbox('setValue', row.item_fg_id);
                            $("#wo_no").textbox('setValue', row.wo_no);
                            $("#product_name").textbox('setValue', row.product_name);
                            $("#qty").numberbox('setValue', row.qty);
                            $("#lot_no").textbox('setValue', row.lot_no);
                            $("#balance").textbox('setValue', '0');
                            $("#division").textbox('setValue', row.division);
                            $("#status_subcont").textbox('setValue', row.status_subcont);
                            $("#subcont_type").textbox('setValue', row.subcont_type);
                          
                            var wo_no = row.wo_no;
                            console.log(wo_no);
                            $.ajax({
                                url: '<?= base_url("planning/checksheets/checkWo_no/") ?>' + window.btoa(wo_no), 
                                method: 'GET',
                                dataType: 'json',
                                success: function(data) {
                                    console.log(data);
                                    accumulateAjax = data[0].qty;
                                    $("#accumulate").numberbox('setValue', data[0].qty);
                                }
                            });

                            $('#receipt').numberbox({
                                onChange: function(value) {
                                    if(value != ""){
                                        var qty = $("#qty").numberbox("getValue");
                                        var receipt = $("#receipt").numberbox('getValue');

                                        var calculate = parseFloat(receipt) + parseFloat(accumulateAjax);
                                        var result = parseFloat(qty) - parseFloat(calculate);

                                        var balance = $("#balance").numberbox('setValue', result);
                                        var accumulate_total = $("#accumulate").numberbox('setValue', calculate);

                                        if (result < 0) {
                                            toastr.warning("Balance minus, please correct your Receipt!");
                                            $("#receipt").numberbox('setValue', 0);
                                            $("#accumulate").numberbox('setValue', accumulate);
                                            balanceMinus.play();
                                        } else {
                                            return result;
                                        }
                                    }else{
                                        $("#receipt").numberbox('setValue', 0);
                                    }
                                }
                            });
                        }
                    });
                }
            }
        });
    }

     //EDIT DATA
    // function update() {
    //     var row = $('#dg').datagrid('getSelected');
    //     console.log(row);
    //     if (row) {
    //         if(row.status == 0){
    //             $('#dlg_insert').dialog('open');
    //             $('#frm_insert').form('load', row);

    //             $("#trans_date").datebox('disable');
    //             $("#number").textbox('disable');
    //             $("#wo_no").combogrid('disable');
    //             $("#item_fg_id").textbox('disable');
    //             $("#product_name").textbox('disable');
    //             $("#product_no").textbox('disable');
    //             $("#qty").numberbox('disable');
    //             $("#receipt").numberbox('disable');
    //             $("#accumulate").numberbox('disable');
    //             $("#balance").numberbox('disable');
    //             $("#packing").combobox('disable');
    //             $("#packing_qty").textbox('disable');
    //             $("#label").numberbox('disable');


    //             $('#qc_1').combobox({
    //                 url: '<?= base_url('planning/checksheets/readEmployesQC'); ?>',
    //                 valueField: 'name',
    //                 textField: 'name',
    //                 prompt: 'Choose Employees',
    //                 onSelect: function(qc) {
    //                     $("#qcnumber_1").textbox('setValue', qc.number);
    //                 }
    //             });
    //             $('#qc_2').combobox({
    //                 url: '<?= base_url('planning/checksheets/readEmployesQC'); ?>',
    //                 valueField: 'name',
    //                 textField: 'name',
    //                 prompt: 'Choose Employees',
    //                 onSelect: function(qc) {
    //                     $("#qcnumber_2").textbox('setValue', qc.number);
    //                 }
    //             });
    //             $('#op_1').combobox({
    //                 url: '<?= base_url('planning/checksheets/readEmployesOP'); ?>',
    //                 valueField: 'name',
    //                 textField: 'name',
    //                 prompt: 'Choose Employees',
    //                 onSelect: function(qc) {
    //                     $("#opnumber_1").textbox('setValue', qc.number);
    //                 }
    //             });
    //             $('#op_2').combobox({
    //                 url: '<?= base_url('planning/checksheets/readEmployesOP'); ?>',
    //                 valueField: 'name',
    //                 textField: 'name',
    //                 prompt: 'Choose Employees',
    //                 onSelect: function(qc) {
    //                     $("#opnumber_2").textbox('setValue', qc.number);
    //                 }
    //             });


    //             setTimeout(function() { 
    //                 $('#product_name').textbox('setValue', row.product_name);
    //                 $('#product_no').textbox('setValue', row.product_no);
    //                 $('#qc_1').textbox('setValue', row.qc_1);
    //                 $('#qc_2').textbox('setValue', row.qc_2);
    //                 $('#op_1').textbox('setValue', row.op_1);
    //                 $('#op_2').textbox('setValue', row.op_1);
    //             }, 500);

    //             url_save = '<?= base_url('planning/checksheets/update') ?>?id=' + id;
    //         }else{
    //             toastr.warning("Checksheet is Closed!", "Information");
    //         }
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
                                url: '<?= base_url('planning/checksheets/delete') ?>',
                                data: {
                                    id: row.id,
                                    wo_no: row.wo_no,
                                    number: row.number,
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
                        } else{
                            toastr.warning("This Data close!", "Information");
                        } 
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_wo_no = $("#filter_wo_no").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_checksheet = $("#filter_checksheet").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_status_subcont = $("#filter_status_subcont").combobox('getValue');
        var filter_subcont_type = $("#filter_subcont_type").combobox('getValue');


        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + 
        "&filter_wo_no=" + filter_wo_no + "&filter_checksheet=" + filter_checksheet + 
        "&filter_shift=" + filter_shift + "&filter_item_fg_id=" + filter_item_fg_id + 
        "&filter_status_subcont=" + filter_status_subcont + "&filter_subcont_type=" + filter_subcont_type + 
        "&filter_division=" + filter_division + "&filter_status=" + filter_status;

        $('#dg').datagrid({
            url: '<?= base_url('planning/checksheets/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/checksheets/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_wo_no = $("#filter_wo_no").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_checksheet = $("#filter_checksheet").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_status_subcont = $("#filter_status_subcont").combobox('getValue');
        var filter_subcont_type = $("#filter_subcont_type").combobox('getValue');


        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + 
        "&filter_wo_no=" + filter_wo_no + "&filter_checksheet=" + filter_checksheet + 
        "&filter_status_subcont=" + filter_status_subcont + "&filter_subcont_type=" + filter_subcont_type + 
        "&filter_shift=" + filter_shift + "&filter_item_fg_id=" + filter_item_fg_id + 
        "&filter_division=" + filter_division + "&filter_status=" + filter_status;
        
        window.location.assign('<?= base_url('planning/checksheets/print/excel') ?>' + url);
    }

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
                            var label = $("#label").numberbox('getValue');
                            //var number = $("#number").textbox('getValue');
                            if (!label || label == 0) {
                                toastr.error('Qty label empty.', 'Error');
                                return false; // Batalkan submit jika validasi gagal
                            }

                            // if (!number) {
                            //     toastr.error('Checksheet Number Null', 'Error');
                            //     return false; // Batalkan submit jika validasi gagal
                            // }
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);

                                var checksheet_number = result.checksheet_number;
                                var qty = $("#receipt").numberbox('getValue');
                                var packing_qty = $("#packing_qty").textbox('getValue');
                                var packing = $("#packing").combobox('getValue');
                                var label = $("#label").numberbox('getValue');

                                var totalData = Math.ceil(qty / packing_qty); // Menghitung total data
                                var jmlData = 0; // Inisialisasi jumlah data yang telah diproses

                                function requestDataBox(total, qty, number, value, success, failed) {
                                    if (value < 100) {
                                        value = Math.floor((number / total) * 100);
                                        $('#p_upload').progressbar('setValue', value);
                                        $('#p_start').html(number);
                                        $('#p_finish').html(total);

                                        var qty_final = (parseInt(qty) > parseInt(packing_qty)) ? packing_qty : qty;

                                        $.ajax({
                                            type: "POST",
                                            async: true,
                                            url: "<?= base_url('planning/checksheets/create_label_box') ?>",
                                            data: {
                                                "checksheet_number": checksheet_number,
                                                "qty": qty_final,
                                            },
                                            cache: false,
                                            dataType: "json",
                                            success: function(result) {
                                                var qty_balance = (parseInt(qty) - parseInt(packing_qty));
                                                if (result.theme == "success") {
                                                    $('#p_success').html(success);
                                                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                    jmlData++; // Update jumlah data yang telah diproses
                                                    requestDataBox(total, qty_balance, number + 1, value, success + 1, failed);
                                                } else {
                                                    $('#p_failed').html(failed);
                                                    var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                    jmlData++; // Update jumlah data yang telah diproses
                                                    requestDataBox(total, qty_balance, number + 1, value, success, failed + 1);
                                                }
                                                $("#p_remarks").append(title + "<br>");
                                                if (jmlData === totalData) {
                                                    $("#dlg_label").dialog('close');
                                                    showPrintConfirmation();
                                                } else {
                                                    $("#dlg_label").dialog('open');
                                                }
                                            }
                                        }).fail(function(jqXHR, textStatus) {
                                            toastr.error("Connection Time Out, Please Wait");
                                            requestDataBox(total, qty, number, value, success, failed);
                                        });
                                    }
                                }

                                function requestDataLabel(total, qty, number, value, success, failed) {
                                    if (total > 0) {
                                        if (value < 100) {
                                            value = Math.floor((number / total) * 100);
                                            $('#p_upload').progressbar('setValue', value);
                                            $('#p_start').html(number);
                                            $('#p_finish').html(total);

                                            var qty_final = (parseInt(qty) > parseInt(packing_qty)) ? packing_qty : qty;

                                            $.ajax({
                                                type: "POST",
                                                async: true,
                                                url: "<?= base_url('planning/checksheets/create_label') ?>",
                                                data: {
                                                    "checksheet_number": checksheet_number,
                                                    "qty": qty_final,
                                                },
                                                cache: false,
                                                dataType: "json",
                                                success: function(result) {
                                                    var qty_balance = (parseInt(qty) - parseInt(packing_qty));
                                                    if (result.theme == "success") {
                                                        $('#p_success').html(success);
                                                        var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                        jmlData++; // Update jumlah data yang telah diproses
                                                        requestDataLabel(total, qty_balance, number + 1, value, success + 1, failed);
                                                    } else {
                                                        $('#p_failed').html(failed);
                                                        var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                        jmlData++; // Update jumlah data yang telah diproses
                                                        requestDataLabel(total, qty_balance, number + 1, value, success, failed + 1);
                                                    }
                                                    $("#p_remarks").append(title + "<br>");
                                                    if (jmlData === totalData) {
                                                        $("#dlg_label").dialog('close');
                                                        showPrintConfirmation();
                                                    } else {
                                                        $("#dlg_label").dialog('open');
                                                    }
                                                }
                                            }).fail(function(jqXHR, textStatus) {
                                                toastr.error("Connection Time Out, Please Wait");
                                                requestDataLabel(total, qty, number, value, success, failed);
                                            });
                                        }
                                    } else {
                                        toastr.error("Qty Label is Zero, Please Add Qty Sub Box in Item Finish Good");
                                        requestData(totalData, jsonData, jmlData + 1, valueData);
                                    }
                                }

                                function showPrintConfirmation() {
                                    Swal.fire({
                                        title: "Do you want to print the barcode?",
                                        showDenyButton: true,
                                        confirmButtonText: "Yes",
                                        denyButtonText: "No"
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // var checksheet_number = $("#number").textbox('getValue');
                                            var packing = $("#packing").combobox('getValue');

                                            var cs = {
                                                checksheet_number: checksheet_number,
                                                packing: packing
                                            };

                                            print_cs(cs);
                                            window.location.reload();
                                        } else if (result.isDenied) {
                                            Swal.fire("You can print QR Code in Datagrid", "", "info").then((result) => {
                                                if (result.isConfirmed) {
                                                    window.location.reload();
                                                }
                                            });
                                        }
                                    });
                                }

                                if (packing == 2) {
                                    requestDataBox(label, qty, 1, 0, 1, 1);
                                } else {
                                    requestDataLabel(label, qty, 1, 0, 1, 1);
                                }

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

    $('#qc_1').combobox({
        url: '<?= base_url('planning/checksheets/readEmployesQC'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Employees',
        onSelect: function(qc) {
            $("#qcnumber_1").textbox('setValue', qc.nik);
        }
    });
    $('#qc_2').combobox({
        url: '<?= base_url('planning/checksheets/readEmployesQC'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Employees',
        onSelect: function(qc) {
            $("#qcnumber_2").textbox('setValue', qc.nik);
        }
    });
    $('#op_1').combobox({
        url: '<?= base_url('planning/checksheets/readEmployesOP'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Employees',
        onSelect: function(qc) {
            $("#opnumber_1").textbox('setValue', qc.nik);
        }
    });
    $('#op_2').combobox({
        url: '<?= base_url('planning/checksheets/readEmployesOP'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Employees',
        onSelect: function(qc) {
            $("#opnumber_2").textbox('setValue', qc.nik);
        }
    });

    $('#filter_division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'number',
        textField: 'name',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
    });
        
    $(document).ready(function() {
        $('#receipt').numberbox({
            onChange: function(value) {
                if(value != ""){
                    var qty = $("#qty").numberbox("getValue");
                    var receipt = $("#receipt").numberbox('getValue');

                    var calculate = parseFloat(receipt) + parseFloat(accumulateAjax);
                    var result = parseFloat(qty) - parseFloat(calculate);

                    var balance = $("#balance").numberbox('setValue', result);
                    var accumulate_total = $("#accumulate").numberbox('setValue', calculate);

                    if (result < 0) {
                        toastr.warning("Balance minus, please correct your Receipt!");
                        $("#receipt").numberbox('setValue', 0);
                        $("#accumulate").numberbox('setValue', accumulate);
                    } else {
                        return result;
                    }
                }else{
                    $("#receipt").numberbox('setValue', 0);
                }
            }
        });

        $('#packing').combobox({
            onSelect: function(record) {
                var item_fg_id = $("#item_fg_id").textbox("getValue");
                $.ajax({
                    url: '<?= base_url("planning/checksheets/readItems/") ?>' + window.btoa(item_fg_id),
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);
                        var packingQty = '';
                        if (record.value == 1) {
                            packingQty = data[0].mpq;
                        } else if (record.value == 2) {
                            packingQty = data[0].qty_box;
                        }
                        $('#packing_qty').textbox('setValue', packingQty);

                        calculateLabel();
                    }
                });
            }
        });

        function calculateLabel() {
            var receipt = $("#receipt").numberbox('getValue'); // Ambil nilai receipt
            var packing_qty = $("#packing_qty").textbox('getValue'); // Ambil nilai packing_qty

            if (receipt != "" && packing_qty != "") {
                var label_value = Math.ceil(receipt / packing_qty);
                $("#label").numberbox('setValue', label_value);
            }
        }

        $('#packing_qty').textbox({
            onChange: function(value) {
                calculateLabel();
            }
        });

        $('#receipt').numberbox({
            onChange: function(value) {
                calculateLabel();
            }
        });
    });

    // $("#trans_date").datebox({
    //     onChange: function(newValue, oldValue) {
    //         var trans_date = $("#trans_date").datebox('getValue');
    //         number(trans_date);
            
    //     }
    // });

    // //NOMOR AUTOMATIC
    // function number(trans_date) {
    //     $.ajax({
    //         type: "post",
    //         url: "<?= base_url('planning/checksheets/checksheet_id/') ?>" + window.btoa(trans_date),
    //         dataType: "html",
    //         success: function(result) {
    //             $("#number").textbox('setValue', result);
    //         }
    //     });
    // }

    $('#filter_wo_no').combobox({
        url: '<?= base_url('planning/checksheets/readWoNos'); ?>',
        valueField: 'wo_no',
        textField: 'wo_no',
        prompt: 'Choose Wo No'
    });

    $('#filter_checksheet').combobox({
        url: '<?= base_url('planning/checksheets/readChecksheet'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: 'Choose Wo No'
    });

    $('#filter_item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/reads'); ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No",
        columns: [
            [{
                field: 'number',
                title: 'Product No',
                width: 150
            }, {
                field: 'name',
                title: 'Product Name',
                width: 250
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    // function filter_workorder() {
    //     //Get Product
    //     $('#filter_workorder').combogrid({
    //         url: '<?= base_url('planning/checksheets/readWorkorder/filter') ?>',
    //         panelWidth: 300,
    //         idField: 'workorder',
    //         textField: 'workorder',
    //         mode: 'remote',
    //         fitColumns: true,
    //         prompt: "Choose Workorder",
    //         icons: [{
    //             iconCls: 'icon-clear',
    //             handler: function(e) {
    //                 $(e.data.target).combogrid('clear').combogrid('textbox').focus();
    //             }
    //         }],
    //         columns: [
    //             [{
    //                 field: 'workorder',
    //                 title: 'Workorder',
    //                 width: 150
    //             }, {
    //                 field: 'wp',
    //                 title: 'WP',
    //                 width: 80,
    //                 align: 'center'
    //             }]
    //         ],
    //     });
    // }

    // function BtnPrint(val, row) {
    //     return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('planning/checksheets/print_label/') ?>' + window.btoa(row.id) + '"><i class="fa fa-print"></i> Print</a>';
    // }

    // function BtnPrint(val, row) {
    //     if (row.packing == 1 || row.packing == 3) {
    //         return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('planning/checksheets/print_label/') ?>' + window.btoa(row.number) + '"><i class="fa fa-print"></i> Print</a>';
    //     } else {
    //         return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('planning/checksheets/print_label_box/') ?>' + window.btoa(row.number) + '"><i class="fa fa-print"></i> Print</a>';
    //     }
    // }

    function BtnPrint(val, row) {
         return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onclick="reprint(\'' + row.number + '\' , \'' + row.packing + '\')"><i class="fa fa-print"></i> Print</a>';
    }

    function reprint(number, packing) {
        swal.fire({
            title: 'Confirmation',
            text: 'Please Input Reprint Reason:',
            input: 'text',
            inputLabel: 'Reprint Reason',
            inputPlaceholder: 'Type here...',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'YES',
            cancelButtonText: 'CANCEL',
            preConfirm: (inputValue) => {
                if (!inputValue) {
                    swal.showValidationMessage('Please Input Reprint Reason');
                }
                return inputValue;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const additionalInfo = result.value;

                // Kirim data ke server untuk disimpan ke database
                $.ajax({
                    url: '<?= base_url('planning/checksheets/save_reprint_reason') ?>', // Endpoint untuk simpan ke database
                    method: 'POST',
                    data: {
                        number: number,
                        reason: additionalInfo
                    },
                    success: function(response) {
                        // Jika penyimpanan berhasil, lakukan pencetakan
                        if (packing == 1 || packing == 3) {
                            window.open('<?= base_url('planning/checksheets/print_label/') ?>' + window.btoa(number));
                        } else {
                            window.open('<?= base_url('planning/checksheets/print_label_box/') ?>' + window.btoa(number)); 
                        }
                    },
                    error: function(xhr, status, error) {
                        swal.fire('Error', 'Failed to save reason: ' + error, 'error');
                    }
                });
            } else {
                window.location.reload();
            }
        });
    }

    function BtnReCreate(val, row) {
         return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onclick="recreate(\'' + row.number + '\' , \'' + row.packing + '\' , \'' + row.receipt + '\' , \'' + row.packing_qty + '\')"><i class="fa fa-refresh"></i></a>';
    }

    function recreate(number, packing, receipt, packing_qty) {
        Swal.fire({
            title: 'Recreate Label?',
            text: 'Are you sure recreate label for ' + number + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Delete Old label.',
                    text: 'Please Wait...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                let deleteUrl = '';
                if (packing == 1 || packing == 3) {
                    deleteUrl = '<?= base_url('planning/checksheets/recreate_all_labels') ?>';
                } else {
                    deleteUrl = '<?= base_url('planning/checksheets/recreate_all_label_boxs') ?>';
                }

                $.ajax({
                    type: "POST",
                    url: deleteUrl,
                    data: {
                        checksheet_number: number,
                        qty: receipt,
                        packing_qty: packing_qty
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.theme == 'success') {
                            Swal.fire({
                                title: 'Recreate label...',
                                html: '<b id="progress-label">Label No-1 Create...</b>',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            recreateLabels(number, packing, receipt, packing_qty);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    }

    function recreateLabels(number, packing, qty, packing_qty) {
        var totalData = Math.ceil(qty / packing_qty);
        var counter = 1;

        function createNext(qty_balance, index) {
            if (index <= totalData && qty_balance > 0) {
                var qty_final = (qty_balance > packing_qty) ? packing_qty : qty_balance;

                let url = '';
                if (packing == 1 || packing == 3) {
                    url = '<?= base_url('planning/checksheets/create_label') ?>';
                } else {
                    url = '<?= base_url('planning/checksheets/create_label_box') ?>';
                }

                $.ajax({
                    type: "POST",
                    url: url,
                    data: {
                        checksheet_number: number,
                        qty: qty_final
                    },
                    dataType: "json",
                    success: function() {
                        $('#progress-label').html(`Label No-${index} created...`);

                        createNext(qty_balance - qty_final, index + 1);
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal membuat label ke-' + index, 'error');
                    }
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Finish',
                    text: 'All label finish recreate (' + (index - 1) + ' label).'
                });
            }
        }

        createNext(qty, counter);
    }


    function close_fc() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                var actionText = row.status == "0" ? "Close" : "Open";
                var reasonLabel = row.status == "0" ? "Closing Reason" : "Opening Reason";
                var ajaxUrl = row.status == "0" ? "<?= base_url('planning/checksheets/closeFc') ?>" : "<?= base_url('planning/checksheets/openFc') ?>";

                Swal.fire({
                    title: "Are you sure?",
                    text: "You want to " + actionText + " this data?",
                    icon: "warning",
                    input: "text",
                    inputLabel: reasonLabel,
                    inputPlaceholder: "Type here...",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes",
                    preConfirm: (inputValue) => {
                        if (!inputValue) {
                            Swal.showValidationMessage("Please enter a " + reasonLabel);
                        }
                        return inputValue;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            method: 'post',
                            url: ajaxUrl,
                            data: {
                                id: row.id,
                                remark: result.value  // Simpan reason ke remark
                            },
                            success: function(response) {
                                var result = eval('(' + response + ')');
                                toastr.success(result.message);
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
                            },
                            complete: function() {
                                $('#dg').datagrid('reload');
                            }
                        });
                    }
                });
            }
        } else {
            toastr.error("Please select at least one row.");
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

    //Number Format Currency
    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        }
    }

    function packingformat(value, row) {
        if (value == 1) {
            return "MPQ";
        } else if (value == 2) {
            return "Qty per Box";
        } else if (value == 3) {
            return " ";
        }
    }

    function statusFormatScan(value, row) {
        if (value != null) {
            if (row.total_scan == row.label) {
                return "<b style='color:red;'>CLOSED</b>";
            } else {
                return "<b style='color:green;'>OPEN</b>";
            }
        }
    }

    function statusStyleScan(value, row, index) {
        if (value != null) {
        console.log(row.total_scan);
            if (row.total_scan == row.label) {
                return 'background-color:#FFC8C8;';
            } else {
                return 'background-color:#C8FFCC;';
            }
        }
    }

    function print_cs(cs) {
        console.log(cs);
        var url = '<?= base_url('planning/checksheets/print_label_cs/') ?>' + window.btoa(cs.checksheet_number) + "/" + cs.packing ;
        window.open(url, '_blank');
    }
</script>
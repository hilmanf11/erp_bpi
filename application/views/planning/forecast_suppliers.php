<!-- TABLE DATAGRID -->
<!-- <table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    
</table> -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead data-options="frozen:true">
        <tr>
            <th field="ck" checkbox="true" rowspan="2"></th>
            <th data-options="field:'id',width:80,align:'center',hidden:true" rowspan="2">ID</th>
            <th data-options="field:'item_rm_id',width:80,align:'center',hidden:true" rowspan="2">ITEM ID</th>
            <th data-options="field:'part_no',width:120,halign:'center'" rowspan="2">PART NO</th>
            <th data-options="field:'part_name',width:180,halign:'center'" rowspan="2">PART NAME</th>
            <th data-options="field:'product_family',width:120,halign:'center'" rowspan="2">PRODUCT FAMILY</th>
        </tr>
        <tr>
            </tr>
    </thead>

    <thead>
        <tr>
            <th data-options="field:'supplier_name',width:180,halign:'center'" rowspan="2">SUPPLIER NAME</th>
            <th data-options="field:'class_abc',width:80,align:'center',halign:'center'" rowspan="2">CLASS A/B/C</th>
            <th data-options="field:'leadtime',width:70,align:'right',halign:'center',formatter:numberFormat" rowspan="2">LEADTIME</th>
            <th data-options="field:'mpq',width:70,align:'right',halign:'center',formatter:numberFormat" rowspan="2">MPQ</th>
            <th data-options="field:'moq',width:70,align:'right',halign:'center',formatter:numberFormat" rowspan="2">MOQ</th>
            
            <th colspan="4" data-options="halign:'center'">Material</th>
            <th data-options="field:'os_po',width:70,align:'right',halign:'center',formatter:numberFormat" rowspan="2">OS PO</th>
            
            <th colspan="3" data-options="halign:'center'"><span id="main_title_m1">Month 1</span></th>
            <th colspan="3" data-options="halign:'center'"><span id="main_title_m2">Month 2</span></th>
            <th colspan="3" data-options="halign:'center'"><span id="main_title_m3">Month 3</span></th>
            
            <th colspan="3" data-options="halign:'center'">Approved</th>
            <th colspan="2" data-options="halign:'center'">Created</th>
        </tr>
        <tr>
            <th data-options="field:'used_1',width:80,align:'right',halign:'center',formatter:numberFormat">USED 1</th>
            <th data-options="field:'used_2',width:80,align:'right',halign:'center',formatter:numberFormat">USED 2</th>
            <th data-options="field:'used_3',width:80,align:'right',halign:'center',formatter:numberFormat">USED 3</th>
            <th data-options="field:'average',width:80,align:'right',halign:'center',formatter:numberFormat">AVERAGE</th>

            <th data-options="field:'need_1',width:70,align:'right',halign:'center',formatter:numberFormat">NEED</th>
            <th data-options="field:'balance_1',width:70,align:'right',halign:'center',formatter:numberFormat">BAL</th>
            <th data-options="field:'month1_fc',width:70,align:'right',halign:'center',formatter:numberFormat">FC</th>
            
            <th data-options="field:'need_2',width:70,align:'right',halign:'center',formatter:numberFormat">NEED</th>
            <th data-options="field:'balance_2',width:70,align:'right',halign:'center',formatter:numberFormat">BAL</th>
            <th data-options="field:'month2_fc',width:70,align:'right',halign:'center',formatter:numberFormat">FC</th>
            
            <th data-options="field:'need_3',width:70,align:'right',halign:'center',formatter:numberFormat">NEED</th>
            <th data-options="field:'balance_3',width:70,align:'right',halign:'center',formatter:numberFormat">BAL</th>
            <th data-options="field:'month3_fc',width:70,align:'right',halign:'center',formatter:numberFormat">FC</th>
            
            <th data-options="field:'approved_to',width:100,halign:'center',formatter:formatApproved,styler:styleApproved">Status</th>
            <th data-options="field:'approved_by',width:100,align:'center'">By</th>
            <th data-options="field:'approved_date',width:150,align:'center'">Date</th>
            
            <th data-options="field:'created_by',width:90,align:'center',halign:'center'">By</th>
            <th data-options="field:'created_date',width:140,align:'center',halign:'center'">Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding: 10px;">
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" name="filter_month" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Year'">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <input style="width:60%;" name="filter_revision" id="filter_revision" class="easyui-combobox" data-options="prompt:'Revision'" panelHeight="auto">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="print_forecast()"><i class="fa fa-print"></i> Print Forecast</a>
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>

                    <input style="width:60%;" id="filter_product_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_items" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>

        <?php if(isset($button)) echo $button; ?>
    </div>
</div>

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1300px; height: 600px; padding:10px; top: 20px; left: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" name="p_month" id="p_month" required="" data-options="valueField:'id',textField:'name'" class="easyui-combobox">
                    <input style="width:30%;" name="p_year" id="p_year" required="" data-options="valueField:'id',textField:'name'" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <input style="width:60%;" name="revision" id="revision" class="easyui-combobox" data-options="prompt:'Revision'" panelHeight="auto">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <!-- <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remark" id="remark" class="easyui-textbox">
                </div>
            </div> -->
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%; height:400px;" title="Forecast Supplier Lists" idField="item_number"></table>
        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #d0d0d0; text-align: right;">
            <a href="javascript:void(0)" class="easyui-linkbutton" onclick="submitForecast()"><i class="fa fa-save"></i> Save All</a>
            <!-- <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#dlg_insert').dialog('close')"><i class="fa fa-times"></i> Cancel</a> -->
        </div>
    </form>
</div>

<!-- Update -->
<div id="dlg_insert2" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 70%; padding:10px; top: 10px;">
    <form id="frm_insert2" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float:left; width:50%;">
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">ID</span>
                    <input style="width:60%;" name="id" id="id" class="easyui-textbox" readonly>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Part Id</span>
                    <input style="width:60%;" name="item_rm_id" id="item_rm_id" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part no</span>
                    <input style="width:60%;" name="part_no" id="part_no" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part Name</span>
                    <input style="width:60%;" name="part_name" id="part_name" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" name="product_family" id="product_family" class="easyui-textbox" readonly>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Supplier ID</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" class="easyui-textbox" readonly>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Supplier Name</span>
                    <input style="width:60%;" name="supplier_name" id="supplier_name" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Class ABC</span>
                    <input style="width:60%;" name="class_abc" id="class_abc" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Leadtime</span>
                    <input style="width:60%;" name="leadtime" id="leadtime" class="easyui-numberbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Mpq</span>
                    <input style="width:60%;" name="mpq" id="mpq" class="easyui-numberbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Moq</span>
                    <input style="width:60%;" name="moq" id="moq" class="easyui-numberbox" readonly>
                </div>
            </div>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Used 1</span>
                    <input style="width:60%;" name="used_1" id="used_1" class="easyui-numberbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Used 2</span>
                    <input style="width:60%;" name="used_2" id="used_2" class="easyui-numberbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Used 3</span>
                    <input style="width:60%;" name="used_3" id="used_3" class="easyui-numberbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Average</span>
                    <input style="width:60%;" name="average" id="average" class="easyui-numberbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 1</span>
                    <input style="width:60%;" name="month1_fc" id="month1_fc" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 2</span>
                    <input style="width:60%;" name="month2_fc" id="month2_fc" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month 3</span>
                    <input style="width:60%;" name="month3_fc" id="month3_fc" class="easyui-numberbox">
                </div>
            </div>
        </fieldset>
    </form>
</div>

<!-- Detail Histories -->
<!-- <div id="dlg_history" class="easyui-dialog" title="Forecast Histories" data-options="closed: true,modal:true" style="width: 1300px; height: 500px; top: 20px; left: 20px;">
    <table id="dg_history" class="easyui-datagrid" style="width:100%;"></table>
</div> -->

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; height: 650px; padding:10px; top: 20px;">
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

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/forecast_suppliers/print') ?>" style="width: 100%;" hidden></iframe>

<script>

     //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');

        var dg = $('#dg2').datagrid({
            onBeforeEdit: function(index, row) {
                row.editing = true;
                $(this).datagrid('refreshRow', index);
            },
            onAfterEdit: function(index, row) {
                row.editing = false;
                $(this).datagrid('refreshRow', index);
            },
            onCancelEdit: function(index, row) {
                row.editing = false;
                $(this).datagrid('refreshRow', index);
            },
        });
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        console.log(row);
        if (row) {
            $('#dlg_insert2').dialog('open');
            $('#frm_insert2').form('load', row);
            url_save = '<?= base_url('planning/forecast_suppliers/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function reload() {
        window.location.reload();
    }

    function preview() {
        var p_month     = $("#p_month").combobox('getValue');
        var p_year      = $("#p_year").combobox('getValue');
        var revision    = $("#revision").combobox('getValue');

        if (p_month == "" || p_year == "" || revision == "") {
            toastr.info('Please completed your data');
        } else {
            var lastIndex;
            if (p_month != "" || p_year != "" || revision != "") {
                var dg = $('#dg2').datagrid({
                    url: '<?= base_url('planning/generate_mrp/datatables_forecast_supplier') ?>?p_month=' + encodeURIComponent(p_month) 
                    + '&p_year=' + encodeURIComponent(p_year)
                    + '&revision=' + encodeURIComponent(revision),
                });
            } else {
                toastr.info('Please completed your data');
            }
        }
    }

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) { return true; }
        if ($('#dg2').datagrid('validateRow', editIndex)) {
            $('#dg2').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function append() {
        if (endEditing()) {
            $('#dg2').datagrid('appendRow', {});
            editIndex = $('#dg2').datagrid('getRows').length - 1;
            $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
        }
    }

    function deleterow() {
        var row = $('#dg2').datagrid('getSelected');
        
        if (row) {
            $.messager.confirm('Confirm', 'Are you sure you want to delete this record?', function(r) {
                if (r) {
                    var rowIndex = $('#dg2').datagrid('getRowIndex', row);
                    
                    if (row.id) {
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('warehouse/wip_receipts/deleteSingle') ?>',
                            data: { id: row.id },
                            success: function(result) {
                                var res = eval('(' + result + ')');
                                toastr.success(res.message);
                            },
                            error: function(jqXHR) {
                                toastr.error(jqXHR.statusText);
                            }
                        });
                    }
                    
                    $('#dg2').datagrid('deleteRow', rowIndex);
                    
                    editIndex = undefined; 
                }
            });
        } else {
            toastr.warning("Please select a row first to delete!");
        }
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function myformatter(date) {
        if (!date) return '';
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

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

    function numberFormat(value, row) {
    // Jika value null, undefined, atau string kosong → tampilkan kosong
        if (value === null || value === undefined || value === "") {
            return "<b></b>";
        }


        var formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    $(function() {
    // Kolom didefinisikan dalam array 2 dimensi untuk 2 baris header
        // $('#dg').datagrid({
        //     singleSelect:true,
        //     columns: [
        //         [
        //             // Baris 1: Header Utama
        //             { field: 'no', title: 'No', rowspan: 2, width: 40, align: 'center', halign: 'center' },
        //             { field: 'part_no', title: 'PART NO', rowspan: 2, width: 120, halign: 'center' },
        //             { field: 'part_name', title: 'PART NAME', rowspan: 2, width: 180, halign: 'center' },
        //             { field: 'product_family', title: 'PRODUCT FAMILY', rowspan: 2, width: 100, halign: 'center' },
        //             { field: 'supplier_name', title: 'SUPPLIER NAME', rowspan: 2, width: 180, halign: 'center' },
        //             { field: 'class_abc', title: 'CLASS A/B/C', rowspan: 2, width: 80, align: 'center', halign: 'center' },
        //             { field: 'leadtime', title: 'LEADTIME', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'mpq', title: 'MPQ', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'moq', title: 'MOQ', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
        //             // Group Stock of Raw Material
        //             { title: 'STOCK OF RAW MATERIAL', colspan: 3, halign: 'center' },
                    
        //             { field: 'used1', title: 'USED 1', rowspan: 2, width: 80, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'material_used2', title: 'MATERIAL USED 2', rowspan: 2, width: 100, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'average', title: 'AVERAGE', rowspan: 2, width: 80, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'os_po', title: 'OS PO', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'os_supply', title: 'OS Supply', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'os_wo', title: 'OS WO', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
        //             // Group Bulanan
        //             { title: 'Month 1', colspan: 3, halign: 'center' },
        //             { title: 'Month 2', colspan: 3, halign: 'center' },
        //             { title: 'Month 3', colspan: 3, halign: 'center' },
                    
        //             { field: 'status', title: 'Status', rowspan: 2, width: 80, align: 'center', halign: 'center' },
                    
        //             // Group Approved & Created
        //             { title: 'Approved', colspan: 2, halign: 'center' },
        //             { title: 'Created', colspan: 2, halign: 'center' }
        //         ],
        //         [
        //             // Baris 2: Sub-Header (Hanya field yang tidak pake rowspan di Baris 1)
                    
        //             // Sub dari STOCK OF RAW MATERIAL
        //             { field: 'stock_whs', title: 'WHS', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'stock_wip', title: 'WIP', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'stock_total', title: 'TOTAL', width: 80, align: 'right', halign: 'center', formatter: numberFormat },
                    
        //             // Sub dari Month 1
        //             { field: 'month1_need', title: 'NEED', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'month1_bal', title: 'BAL', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'month1_fc', title: 'FC', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
        //             // Sub dari Month 2
        //             { field: 'month2_need', title: 'NEED', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'month2_bal', title: 'BAL', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'month2_fc', title: 'FC', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
        //             // Sub dari Month 3
        //             { field: 'month3_need', title: 'NEED', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'month3_bal', title: 'BAL', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
        //             { field: 'month3_fc', title: 'FC', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
        //             // Sub dari Approved
        //             { field: 'approved_by', title: 'By', width: 80, halign: 'center' },
        //             { field: 'approved_date', title: 'Date', width: 120, align: 'center', halign: 'center' },
                    
        //             // Sub dari Created
        //             { field: 'created_by', title: 'By', width: 80, halign: 'center' },
        //             { field: 'created_date', title: 'Date', width: 120, align: 'center', halign: 'center' }
        //         ]
        //     ]
        // });

        $('#dg').datagrid({
            singleSelect: true,
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            onLoadSuccess: function(data) {
                if (data.period_1) {
                    $('#main_title_m1').html(data.period_1);
                    $('#main_title_m2').html(data.period_2);
                    $('#main_title_m3').html(data.period_3);
                }
            }
        });
    });

    $(function(){
        $('#dg2').datagrid({
            fitColumns: false,
            singleSelect: true,
            
            frozenColumns: [
                [
                    { field: 'no', title: 'No', width: 40, align: 'center', halign: 'center', rowspan: 2 },
                    { field: 'item_rm_id', title: 'ID', width: 120, halign: 'center', rowspan: 2, hidden: true },
                    { field: 'part_no', title: 'PART NO', width: 120, halign: 'center', rowspan: 2 },
                    { field: 'part_name', title: 'PART NAME', width: 180, halign: 'center', rowspan: 2 },
                    { field: 'product_family', title: 'PRODUCT FAMILY', width: 120, halign: 'center', rowspan: 2 },
                    { field: 'product_family_id', title: 'PRODUCT FAMILY ID', width: 120, halign: 'center', rowspan: 2, hidden: true }
                ],
                [
                ]
            ],

            columns: [
                [
                    // Baris 1: Header Utama
                    { field: 'supplier_name', title: 'SUPPLIER NAME', rowspan: 2, width: 180, halign: 'center' },
                    { field: 'supplier_id', title: 'SUPPLIER ID', rowspan: 2, width: 180, halign: 'center', hidden: true },
                    { field: 'class_abc', title: 'CLASS A/B/C', rowspan: 2, width: 80, align: 'center', halign: 'center' },
                    { field: 'leadtime', title: 'LEADTIME', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'mpq', title: 'MPQ', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'moq', title: 'MOQ', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
                    { title: 'Material', colspan: 4, halign: 'center' },
                    
                    { field: 'os_po', title: 'OS PO', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
                    // Group Bulanan
                    { title: '<span id="title_m1">Month 1</span>', colspan: 3, halign: 'center' },
                    { title: '<span id="title_m2">Month 2</span>', colspan: 3, halign: 'center' },
                    { title: '<span id="title_m3">Month 3</span>', colspan: 3, halign: 'center' },
                    
                    // { field: 'remarks', title: 'Remarks', width: 150, editor: {type: 'textbox', options: {required: true}}, rowspan: 2 },
                    // { field: 'status', title: 'Status', rowspan: 2, width: 80, align: 'center', halign: 'center' },
                    
                    // Group Log Status
                    { title: 'Approved', colspan: 2, halign: 'center' },
                    { title: 'Created', colspan: 2, halign: 'center' }
                ],
                [
                    // Baris 2: Sub-Header
                    { field: 'used_1', title: 'USED 1', width: 100, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'used_2', title: 'USED 2', width: 100, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'used_3', title: 'USED 3', width: 100, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'average', title: 'AVERAGE', width: 80, align: 'right', halign: 'center', formatter: numberFormat },

                    { field: 'need_1', title: 'NEED', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'balance_1', title: 'BAL', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'need_1', title: 'FC', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
                    { field: 'need_2', title: 'NEED', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'balance_2', title: 'BAL', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'need_2', title: 'FC', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
                    { field: 'need_3', title: 'NEED', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'balance_3', title: 'BAL', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'need_3', title: 'FC', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
                    { field: 'approved_by', title: 'By', width: 80, halign: 'center' },
                    { field: 'approved_date', title: 'Date', width: 120, align: 'center', halign: 'center' },
                    
                    { field: 'created_by', title: 'By', width: 80, halign: 'center' },
                    { field: 'created_date', title: 'Date', width: 120, align: 'center', halign: 'center' }
                ]
            ],

            onClickRow: function(index, row) {
                if (editIndex != index) {
                    if (endEditing()) {
                        $('#dg2').datagrid('selectRow', index);
                        $('#dg2').datagrid('beginEdit', index);
                        editIndex = index;
                    } else {
                        $('#dg2').datagrid('selectRow', editIndex);
                    }
                }
            },
            onLoadSuccess: function(data) {
                if (data.period_1) {
                    $('#title_m1').html(data.period_1);
                    $('#title_m2').html(data.period_2);
                    $('#title_m3').html(data.period_3);
                }
            }
        });
    });

    //SAVE DATA2
    $('#dlg_insert2').dialog({
        buttons: [{
            text: 'Save',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_insert2').form('submit', {
                    url: url_save,
                    onSubmit: function() {
                        return $(this).form('validate');
                    },
                    success: function(result) {
                        var result = eval('(' + result + ')');
                        if (result.theme == "success") {
                            toastr.success(result.message, result.title);
                        } else {
                            toastr.error(result.message, result.title);
                        }
                        $('#dlg_insert2').dialog('close');
                        $('#dg').datagrid('reload');
                    }
                });
            }
        }]
    });

    $('#filter_month').combobox({
        url: '<?php echo base_url('planning/forecast_suppliers/readMonths'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Select Month',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
        }],
        onSelect: function(month){
            var year = $("#filter_year").combobox('getValue');
            loadRevisionFilter(month.id, year);
        }
    });

    $('#filter_year').combobox({
        url: '<?php echo base_url('planning/forecast_suppliers/readYears'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Select Year',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
        }],
        onSelect: function(year){
            var month = $("#filter_month").combobox('getValue');
            loadRevisionFilter(month, year.id);
        }
    });

    function loadRevisionFilter(month, year) {
        if(!month || !year) return;
        $('#filter_revision').combobox({
            url: '<?php echo base_url('planning/forecast_suppliers/readRevisions?filter_month='); ?>' + btoa(month) + "&filter_year=" + btoa(year),
            valueField: 'revision',
            textField: 'revision',
            prompt: 'Select Revision',
            formatter: function(val){ return "Revision " + val.revision; },
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
            }]
        });
    }

    $('#p_month').combobox({
        url: '<?php echo base_url('planning/forecast_suppliers/readMonths'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Select Month',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
        }],
        onSelect: function(month){
            var year = $("#p_year").combobox('getValue');
            loadRevisionForm(month.id, year);
        }
    });

    $('#p_year').combobox({
        url: '<?php echo base_url('planning/forecast_suppliers/readYears'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Select Year',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
        }],
        onSelect: function(year){
            var month = $("#p_month").combobox('getValue');
            loadRevisionForm(month, year.id);
        }
    });

    function loadRevisionForm(month, year) {
        if(!month || !year) return;
        $('#revision').combobox({
            url: '<?php echo base_url('planning/forecast_suppliers/readRevisions?filter_month='); ?>' + btoa(month) + "&filter_year=" + btoa(year),
            valueField: 'revision',
            textField: 'revision',
            prompt: 'Select Revision',
            formatter: function(val){ return "Revision " + val.revision; },
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) { $(e.data.target).combobox('clear').combobox('textbox').focus(); }
            }]
        });
    }

    $('#filter_supplier_id').combogrid({
        url: '<?= base_url('master/suppliers/reads'); ?>',
        panelWidth: 550,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Supplier",
        columns: [
            [{
                field: 'id',
                title: 'Supplier ID',
                width: 110
            }, {
                field: 'number',
                title: 'Supplier Code',
                width: 110
            }, {
                field: 'name',
                title: 'Supplier Name',
                width: 300
            }]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $('#filter_items').combogrid({
        url: '<?= base_url('master/item_rm/reads/') ?>',
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

    $('#filter_product_family').combobox({
        url: '<?= base_url('master/item_familys/reads/C01'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Select Product Family',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
                $('#filter_items').combogrid('clear');
            }
        }],
        onSelect: function(row){
            $('#filter_items').combogrid({
                url: '<?= base_url('master/item_rm/reads/') ?>' + row.id,
                panelWidth: 400,
                idField: 'id',
                textField: 'number',
                mode: 'remote',
                fitColumns: true,
                prompt: "Select Part No",
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                    }
                }],
                columns: [
                    [{
                        field: 'number',
                        title: 'Part No',
                        width: 200
                    }, {
                        field: 'name',
                        title: 'Part Name',
                        width: 200
                    }]
                ]
            });
        }
    });

    $('#supplier_id').combogrid({
        url: '<?= base_url('master/suppliers/reads'); ?>',
        panelWidth: 550,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Supplier",
        columns: [
            [{
                field: 'id',
                title: 'Supplier ID',
                width: 110
            }, {
                field: 'number',
                title: 'Supplier Code',
                width: 110
            }, {
                field: 'name',
                title: 'Supplier Name',
                width: 300
            }]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    function filter() {
        var filter_month = $('#filter_month').combobox('getValue');
        var filter_year = $('#filter_year').combobox('getValue');
        var filter_revision = $('#filter_revision').combobox('getValue');
        var filter_supplier_id = $('#filter_supplier_id').combogrid('getValue');
        var filter_product_family = $('#filter_product_family').combobox('getValue');
        var filter_items = $('#filter_items').combogrid('getValue');

        // Buat parameter query string saja, jangan gabung base_url di sini
        var queryString = '?filter_month=' + encodeURIComponent(window.btoa(filter_month))
            + '&filter_year=' + encodeURIComponent(window.btoa(filter_year))
            + '&filter_revision=' + encodeURIComponent(window.btoa(filter_revision))
            + '&filter_supplier_id=' + encodeURIComponent(window.btoa(filter_supplier_id))
            + '&filter_product_family=' + encodeURIComponent(window.btoa(filter_product_family))
            + '&filter_items=' + encodeURIComponent(window.btoa(filter_items));

        // Reload datagrid dengan URL yang benar
        $('#dg').datagrid({
            url: '<?= base_url('planning/forecast_suppliers/datatables') ?>' + queryString
        });

        // Update Iframe Printout
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/forecast_suppliers/print') ?>' + queryString);
    }

    //PRINT EXCEL
    function excel() {
        var filter_month = $('#filter_month').combobox('getValue');
        var filter_year = $('#filter_year').combobox('getValue');
        var filter_revision = $('#filter_revision').combobox('getValue');
        var filter_supplier_id = $('#filter_supplier_id').combogrid('getValue');
        var filter_product_family = $('#filter_product_family').combobox('getValue');
        var filter_items = $('#filter_items').combogrid('getValue');

         var queryString = '?filter_month=' + encodeURIComponent(window.btoa(filter_month))
            + '&filter_year=' + encodeURIComponent(window.btoa(filter_year))
            + '&filter_revision=' + encodeURIComponent(window.btoa(filter_revision))
            + '&filter_supplier_id=' + encodeURIComponent(window.btoa(filter_supplier_id))
            + '&filter_product_family=' + encodeURIComponent(window.btoa(filter_product_family))
            + '&filter_items=' + encodeURIComponent(window.btoa(filter_items));

        window.location.assign('<?= base_url('planning/forecast_suppliers/print/excel') ?>' + queryString);
    }

    function print_forecast() {
        var filter_month = $('#filter_month').combobox('getValue');
        var filter_year = $('#filter_year').combobox('getValue');
        var filter_revision = $('#filter_revision').combobox('getValue');
        var filter_supplier_id = $('#filter_supplier_id').combogrid('getValue');
        var filter_product_family = $('#filter_product_family').combobox('getValue');
        var filter_items = $('#filter_items').combogrid('getValue');

        var queryString = '?filter_month=' + encodeURIComponent(window.btoa(filter_month))
            + '&filter_year=' + encodeURIComponent(window.btoa(filter_year))
            + '&filter_revision=' + encodeURIComponent(window.btoa(filter_revision))
            + '&filter_supplier_id=' + encodeURIComponent(window.btoa(filter_supplier_id))
            + '&filter_product_family=' + encodeURIComponent(window.btoa(filter_product_family))
            + '&filter_items=' + encodeURIComponent(window.btoa(filter_items));

        var printUrl = '<?= base_url('planning/forecast_suppliers/print_forecast') ?>' + queryString;
        window.open(printUrl, '_blank');
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    
    // function append() {
    //     $('#dlg_insert').dialog('open').dialog('setTitle', 'Add New Forecast');
    //     $('#frm_insert').form('clear');
    //     $('#dg2').datagrid('loadData', {total: 0, rows: []});
    // }

    // function appendDetail() {
    //     $('#dg2').datagrid('appendRow', {
    //         id: '',
    //         number: '',
    //         name: '',
    //         quantity: '0'
    //     });
    // }

    // function removeDetail() {
    //     var row = $('#dg2').datagrid('getSelected');
    //     if (row) {
    //         var index = $('#dg2').datagrid('getRowIndex', row);
    //         $('#dg2').datagrid('deleteRow', index);
    //     } else {
    //         $.messager.alert('Warning', 'Please select a row to delete');
    //     }
    // }

    // function loadDetailItems(supplier_id) {
    //     $('#dg2').datagrid({
    //         url: '<?= base_url('planning/forecasts/getSupplierItems'); ?>',
    //         queryParams: {
    //             supplier_id: supplier_id
    //         }
    //     });
    // }

    function submitForecast() {
        if (editIndex !== undefined) {
            $('#dg2').datagrid('endEdit', editIndex);
        }

        var rows = $('#dg2').datagrid('getRows');
        if (rows.length === 0) {
            toastr.warning('No data to save!');
            return;
        }

        var postData = {
            p_month: $('#p_month').combobox('getValue'),
            p_year: $('#p_year').combobox('getValue'),
            revision: $('#revision').combobox('getValue'),
            forecast_data: JSON.stringify(rows)
        };

        $.ajax({
            url: '<?= base_url('planning/forecast_suppliers/saveAll') ?>',
            method: 'POST',
            data: postData,
            success: function(response) {
                var res = JSON.parse(response);
                if (res.success) {
                    toastr.success(res.message);
                    
                    $('#dlg_insert').dialog('close');
                    
                    $('#dg').datagrid('reload');
                    
                } else {
                    toastr.error(res.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                toastr.error('Failed to connect to the server: ' + textStatus);
            }
        });
    }

    function removeit() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Confirm', 'Delete selected records?', function(r) {
                if (r) {
                    var ids = [];
                    for (var i = 0; i < rows.length; i++) {
                        ids.push(rows[i].id);
                    }
                    $.ajax({
                        url: '<?= base_url('planning/forecasts/delete'); ?>',
                        type: 'POST',
                        data: { ids: ids.join(',') },
                        dataType: 'json',
                        success: function(result) {


                            if (result.success) {
                                $('#dg').datagrid('reload');
                                $.messager.show({ title: 'Success', msg: 'Records deleted successfully' });
                            } else {
                                $.messager.alert('Error', result.message || 'Failed to delete records');
                            }
                        },


                        error: function(xhr, status, error) {
                            $.messager.show({ title: 'Error', msg: 'Failed to delete records: ' + error });
                        }
                    });
                }
            });
        } else {
            $.messager.alert('Warning', 'Please select records to delete');
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

    //CELLSTYLE APPROVE
    function styleApproved(value, row, index) {
        if (value == "" || value === null ) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

</script>
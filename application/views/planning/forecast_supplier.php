<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                
        </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding: 10px;">
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Periode Cutoff</span>
                    <input style="width:30%;" id="filter_period_month" value="" class="easyui-combobox">
                    <input style="width:30%;" id="filter_period_year" value="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <select style="width:30%;" id="filter_revision" class="easyui-combobox" panelHeight="auto"></select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
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
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
    </div>
</div>

<div id="toolbar2">


    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="appendDetail()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeDetail()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
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
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>

                    <input style="width:60%;" name="document_no" id="document_no" class="easyui-textbox" readonly>
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Issued Date</span>
                    <input style="width:30%;" name="issued_date" id="issued_date" required="" data-options="formatter:myformatter,parser:myparser" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <select style="width:30%;" name="revision" id="revision" class="easyui-combobox" panelHeight="auto">
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remark" id="remark" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Customer Item Lists" toolbar="#toolbar2"></table>
        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #d0d0d0; text-align: right;">
            <a href="javascript:void(0)" class="easyui-linkbutton" onclick="submitForecast()"><i class="fa fa-save"></i> Save</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#dlg_insert').dialog('close')"><i class="fa fa-times"></i> Cancel</a>
        </div>
    </form>
</div>

<!-- Detail Histories -->
<div id="dlg_history" class="easyui-dialog" title="Forecast Histories" data-options="closed: true,modal:true" style="width: 1300px; height: 500px; top: 20px; left: 20px;">
    <table id="dg_history" class="easyui-datagrid" style="width:100%;"></table>
</div>

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
<iframe id="printout" src="<?= base_url('planning/forecasts/print') ?>" style="width: 100%;" hidden></iframe>

<script>

    function reload() {
        window.location.reload();
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
        $('#dg').datagrid({
            
            url: '<?= base_url('planning/Generate_mrp/datatables_forecast_supplier') ?>',
            singleSelect:true,
            columns: [
                [
                    // Baris 1: Header Utama
                    { field: 'no', title: 'No', rowspan: 2, width: 40, align: 'center', halign: 'center' },
                    { field: 'part_no', title: 'PART NO', rowspan: 2, width: 120, halign: 'center' },
                    { field: 'part_name', title: 'PART NAME', rowspan: 2, width: 180, halign: 'center' },
                    { field: 'product_family', title: 'PRODUCT FAMILY', rowspan: 2, width: 100, halign: 'center' },
                    { field: 'supplier_name', title: 'SUPPLIER NAME', rowspan: 2, width: 180, halign: 'center' },
                    { field: 'class_abc', title: 'CLASS A/B/C', rowspan: 2, width: 80, align: 'center', halign: 'center' },
                    { field: 'leadtime', title: 'LEADTIME', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'mpq', title: 'MPQ', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'moq', title: 'MOQ', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
                    // Group Stock of Raw Material
                    { title: 'STOCK OF RAW MATERIAL', colspan: 3, halign: 'center' },
                    
                    { field: 'used1', title: 'USED 1', rowspan: 2, width: 80, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'material_used2', title: 'MATERIAL USED 2', rowspan: 2, width: 100, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'average', title: 'AVERAGE', rowspan: 2, width: 80, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'os_po', title: 'OS PO', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'os_supply', title: 'OS Supply', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'os_wo', title: 'OS WO', rowspan: 2, width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
                    // Group Bulanan
                    { title: 'Month 1', colspan: 3, halign: 'center' },
                    { title: 'Month 2', colspan: 3, halign: 'center' },
                    { title: 'Month 3', colspan: 3, halign: 'center' },
                    
                    { field: 'status', title: 'Status', rowspan: 2, width: 80, align: 'center', halign: 'center' },
                    
                    // Group Approved & Created
                    { title: 'Approved', colspan: 2, halign: 'center' },
                    { title: 'Created', colspan: 2, halign: 'center' }
                ],
                [
                    // Baris 2: Sub-Header (Hanya field yang tidak pake rowspan di Baris 1)
                    
                    // Sub dari STOCK OF RAW MATERIAL
                    { field: 'stock_whs', title: 'WHS', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'stock_wip', title: 'WIP', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'stock_total', title: 'TOTAL', width: 80, align: 'right', halign: 'center', formatter: numberFormat },
                    
                    // Sub dari Month 1
                    { field: 'month1_need', title: 'NEED', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'month1_bal', title: 'BAL', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'month1_fc', title: 'FC', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
                    // Sub dari Month 2
                    { field: 'month2_need', title: 'NEED', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'month2_bal', title: 'BAL', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'month2_fc', title: 'FC', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
                    // Sub dari Month 3
                    { field: 'month3_need', title: 'NEED', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'month3_bal', title: 'BAL', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    { field: 'month3_fc', title: 'FC', width: 70, align: 'right', halign: 'center', formatter: numberFormat },
                    
                    // Sub dari Approved
                    { field: 'approved_by', title: 'By', width: 80, halign: 'center' },
                    { field: 'approved_date', title: 'Date', width: 120, align: 'center', halign: 'center' },
                    
                    // Sub dari Created
                    { field: 'created_by', title: 'By', width: 80, halign: 'center' },
                    { field: 'created_date', title: 'Date', width: 120, align: 'center', halign: 'center' }
                ]
            ]
        });

        $('#filter_period_month').combobox({
            url: '<?= base_url('planning/forecasts/readPeriod/month'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Months',
            value: '<?= date('m') ?>',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_period_year').combobox({
            url: '<?= base_url('planning/forecasts/readPeriod/year'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Years',
            value: '<?= date('Y') ?>',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_revision').combobox({
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Revision',
            panelHeight: 'auto'
        });


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
                    panelWidth: 550,
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
                            field: 'id',
                            title: 'Part No EBWS',
                            width: 150
                        },{
                            field: 'number',
                            title: 'Part No Supplier',
                            width: 200
                        }, {
                            field: 'name',
                            title: 'Description',
                            width: 200
                        }]
                    ]
                });
            }
        });

        // Initialize period combobox for insert dialog
        $('#p_month').combobox({
            url: '<?= base_url('planning/forecasts/readPeriod/month'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Months'
        });

        $('#p_year').combobox({
            url: '<?= base_url('planning/forecasts/readPeriod/year'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Years'
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
            onSelect: function(row) {
                // Load document_no and detail items when supplier is selected
                loadDocumentNo(row.id);
                loadDetailItems(row.id);
            }
        });



    });

    function filter() {
        var p_month = $('#filter_period_month').combobox('getValue');
        var p_year = $('#filter_period_year').combobox('getValue');
        var revision = $('#filter_revision').combobox('getValue');
        var supplier_id = $('#filter_supplier_id').combogrid('getValue');
        var product_family = $('#filter_product_family').combobox('getValue');
        var items = $('#filter_items').combogrid('getValue');

        var url = '<?= base_url('planning/Generate_mrp/datatables_forecast_supplier') ?>'
            + '?p_month=' + encodeURIComponent(p_month)
            + '&p_year=' + encodeURIComponent(p_year)
            + '&revision=' + encodeURIComponent(revision)
            + '&supplier_id=' + encodeURIComponent(supplier_id)
            + '&product_family=' + encodeURIComponent(product_family)
            + '&items=' + encodeURIComponent(items);

            console.log(url);

        $('#dg').datagrid({
            url: url,
            method: 'get'
        });
    }

    function append() {
        $('#dlg_insert').dialog('open').dialog('setTitle', 'Add New Forecast');
        $('#frm_insert').form('clear');
        $('#dg2').datagrid('loadData', {total: 0, rows: []});
    }

    function appendDetail() {
        $('#dg2').datagrid('appendRow', {
            id: '',
            number: '',
            name: '',
            quantity: '0'
        });
    }

    function removeDetail() {
        var row = $('#dg2').datagrid('getSelected');
        if (row) {
            var index = $('#dg2').datagrid('getRowIndex', row);
            $('#dg2').datagrid('deleteRow', index);
        } else {
            $.messager.alert('Warning', 'Please select a row to delete');
        }
    }

    function loadDocumentNo(supplier_id) {
        $.ajax({
            url: '<?= base_url('planning/forecasts/getDocumentNo'); ?>',
            type: 'POST',
            data: { supplier_id: supplier_id },
            dataType: 'json',
            success: function(result) {
                if (result.document_no) {
                    $('#document_no').textbox('setValue', result.document_no);
                }
            },
            error: function() {
                $.messager.show({ title: 'Info', msg: 'Could not load document number' });
            }
        });
    }

    function loadDetailItems(supplier_id) {
        $('#dg2').datagrid({
            url: '<?= base_url('planning/forecasts/getSupplierItems'); ?>',
            queryParams: {
                supplier_id: supplier_id
            }
        });
    }

    function submitForecast() {
        if (!$('#frm_insert').form('validate')) {
            return;
        }

        var rows = $('#dg2').datagrid('getRows');
        if (rows.length === 0) {
            $.messager.alert('Warning', 'Please add at least one item');
            return;
        }

        var formData = {
            p_month: $('#p_month').combobox('getValue'),
            p_year: $('#p_year').combobox('getValue'),
            supplier_id: $('#supplier_id').combogrid('getValue'),
            issued_date: $('#issued_date').datebox('getValue'),
            revision: $('#revision').combobox('getValue'),
            remark: $('#remark').textbox('getValue'),
            items: JSON.stringify(rows)
        };

        $.ajax({
            url: '<?= base_url('planning/forecasts/create'); ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    $.messager.show({ title: 'Success', msg: 'Forecast saved successfully' });
                    $('#dlg_insert').dialog('close');
                    $('#dg').datagrid('reload');
                } else {
                    $.messager.alert('Error', result.message || 'Failed to save forecast');
                }
            },
            error: function(xhr, status, error) {
                $.messager.show({ title: 'Error', msg: 'Failed to save forecast: ' + error });
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

</script>
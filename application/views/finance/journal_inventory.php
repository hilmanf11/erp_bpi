<div id="toolbar" style="padding: 10px;">
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width:50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Journal Date</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Journal Type</span>
                    <input style="width:60%;" id="filter_journal_type" class="easyui-combogrid">
                </div>

                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="filter_division" class="easyui-combobox">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="addModul()"><i class="fa fa-plus"></i> Add Modul</a>
                </div>
            </div>
            <div style="width:50%; float:left;">
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Type</span>
                    <select style="width:60%;" id="filter_type" class="easyui-combobox" data-options="editable:false, panelHeight:'auto'">
                        <option value="">Choose All</option>
                        <option value="IN">IN</option>
                        <option value="OUT">OUT</option>
                    </select>
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Modul</span>
                    <select style="width:60%;" id="filter_modul" class="easyui-combobox" 
                        data-options="editable:true, valueField:'id', textField:'text', 
                            groupField:'group',panelHeight:'auto'">
                    </select>
                </div>

                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" id="filter_item_category" class="easyui-combobox">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Voucher No</span>
                    <input style="width:60%;" id="filter_voucher" class="easyui-combobox" />
                </div>

            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- Main Data -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'details',width:90,align:'center', formatter:btnDetails">Detail</th>
            <th rowspan="2" data-options="field:'document_no',width:150,align:'center'">Document No.</th>
            <th rowspan="2" data-options="field:'journal_date',width:100,align:'center'">Journal Date</th>
            <th rowspan="2" data-options="field:'number',width:100,align:'center'">GLINV No</th>
            <th rowspan="2" data-options="field:'modul',width:150,halign:'center'">Modul</th>
            <th rowspan="2" data-options="field:'journal_type_name',width:200,halign:'center'">Journal Type</th>
            <th colspan="3" data-options="field:'',width:200,halign:'center'">Original Currency</th>
            <th colspan="3" data-options="field:'',width:200,halign:'center'">Local Currency</th>
            <th rowspan="2" data-options="field:'remarks',width:100,halign:'center'">Remarks</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th data-options="field:'original_debit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Debit</th>
            <th data-options="field:'original_credit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Credit</th>
            <th data-options="field:'rates',width:80,halign:'center',align:'right',formatter:numberformatDefault">Rates</th>
            <th data-options="field:'local_debit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Debit</th>
            <th data-options="field:'local_credit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Credit</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>


<!-- Journal Inventory Modul -->
<div id="dlg_modul" class="easyui-dialog" title="Master Data Journal Inventory Module" 
    data-options="closed:true, modal:true, resizable:true" 
    style="width: 600px; height: auto; padding:15px;">

    <form id="frm_modul" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; box-sizing: border-box;">
            <legend style="padding: 0 10px;"><b>Module Configuration</b></legend>
            
            <div style="padding: 10px;">
                <div class="fitem" style="margin-bottom: 10px;">
                    <label style="width:35%; display:inline-block;">Category <span style="color:red">*</span></label>
                    <select style="width:60%;" name="category" id="modul_category" class="easyui-combobox" 
                            data-options="required:true, editable:false, panelHeight:'auto'">
                        <option value="RM">RAW MATERIAL (RM)</option>
                        <option value="WIP">WORK IN PROCESS (WIP)</option>
                        <option value="FG">FINISHED GOODS (FG)</option>
                    </select>
                </div>

                <div class="fitem" style="margin-bottom: 10px;">
                    <label style="width:35%; display:inline-block;">Process Type <span style="color:red">*</span></label>
                    <select style="width:60%;" name="process_type" id="modul_process_type" class="easyui-combobox" 
                            data-options="required:true, editable:false, panelHeight:'auto'">
                        <option value="IN">IN</option>
                        <option value="OUT">OUT</option>
                    </select>
                </div>

                <div class="fitem" style="margin-bottom: 10px;">
                    <label style="width:35%; display:inline-block;">Module Name <span style="color:red">*</span></label>
                    <input style="width:60%;" name="name" id="modul_name" class="easyui-textbox" data-options="required:true">
                </div>

            </div>
        </fieldset>

        <div style="text-align:right; margin-top:10px;">
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" onclick="saveModul()" style="width:90px">Save</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg_modul').dialog('close')" style="width:90px">Cancel</a>
        </div>
    </form>
</div>



<!-- Detail -->
<div id="dlg_detail" class="easyui-window" title="Journal Detail" data-options="closed: true,modal:true" style="width: 80%; height: 500px; top: 20px; left:10px;">
    <table id="dg3" class="easyui-datagrid" style="width:100%;" showFooter="true">
        <thead>
            <tr>
                <th rowspan="2" data-options="field:'trans_date',width:100,halign:'center'">Trans Date</th>
                <th rowspan="2" data-options="field:'document_no',width:150,halign:'center'">Document No.</th>
                <th rowspan="2" data-options="field:'invoice_no',width:150,halign:'center'">Invoice No.</th>
                <th rowspan="2" data-options="field:'company_name',width:200,halign:'center'">Company Name</th>
                <th rowspan="2" data-options="field:'account_number',width:100,halign:'center'">Account No</th>
                <th rowspan="2" data-options="field:'account_name',width:200,halign:'center'">Account Name</th>
                <th rowspan="2" data-options="field:'description',width:600,halign:'center'">Description</th>
                <th colspan="3" data-options="field:'',width:100">Original Currency</th>
                <th colspan="3" data-options="field:'',width:100">Local Currency</th>
            </tr>
            <tr>
                <th data-options="field:'currency',width:80,align:'center'">Currency</th>
                <th data-options="field:'original_debit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Debit</th>
                <th data-options="field:'original_credit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Credit</th>
                <th data-options="field:'rates',width:100,halign:'center',align:'right',formatter:numberformatDefault">Rates</th>
                <th data-options="field:'local_debit',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Debit</th>
                <th data-options="field:'local_credit',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Credit</th>
            </tr>
        </thead>
    </table>
</div>


<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add New Posting Journal Inventory" 
    data-options="closed:true, modal:true, resizable:true" 
    style="width: 98%; height: 520px; padding:15px; overflow-y: auto;">

    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 15px; border-radius:4px; box-sizing: border-box;">
            <legend style="padding: 0 10px;"><b>Form Data</b></legend>
            
            <div style="width:50%; float:left; padding-right: 10px; box-sizing: border-box;">
                <div class="fitem" style="margin-bottom: 8px;">
                    <label style="width:35%; display:inline-block;">Journal Date <span style="color:red">*</span></label>
                    <input style="width:60%;" name="journal_date" id="journal_date" 
                           class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false, required:true">
                </div>

                <div class="fitem" style="margin-bottom: 8px;">
                    <label style="width:35%; display:inline-block;">Division <span style="color:red">*</span></label>
                    <input style="width:60%;" id="division" class="easyui-combobox" 
                           data-options="required:true, editable:false, panelHeight:'auto'">
                </div>
                
                <div class="fitem" style="margin-bottom: 8px;" hidden>
                    <label style="width:35%; display:inline-block;">Type</label>
                    <select style="width:60%;" id="type" name="type" class="easyui-combobox" 
                            data-options="editable:false, panelHeight:'auto'">
                        <option value="">Choose All</option>
                        <option value="IN">IN</option>
                        <option value="OUT">OUT</option>
                    </select>
                </div>

                <div class="fitem" style="margin-bottom: 8px;">
                    <label style="width:35%; display:inline-block;">Modul <span style="color:red">*</span></label>
                    <select style="width:60%;" id="modul" name="modul" class="easyui-combobox" 
                        data-options="editable:true, valueField:'id', textField:'text', 
                                    groupField:'group', panelHeight:'auto', required:true">
                    </select>
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" id="preview" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>

            <div style="width:50%; float:left; padding-left: 10px; box-sizing: border-box;">
                <div class="fitem" style="margin-bottom: 8px;">
                    <label style="width:35%; display:inline-block;">Company Name <span style="color:red">*</span></label>
                    <input style="width:60%;" name="company_name" id="company_name" class="easyui-combogrid" 
                           data-options="required:true">
                </div>
                
                <div class="fitem" style="margin-bottom: 8px;">
                    <label style="width:35%; display:inline-block;">Document No. <span style="color:red">*</span></label>
                    <input style="width:60%;" name="document_no" id="document_no" class="easyui-combogrid" 
                           data-options="required:true">
                </div>
                
                <div class="fitem" style="margin-bottom: 8px;">
                    <label style="width:35%; display:inline-block;">Voucher No</label>
                    <input style="width:60%;" name="number" id="number" class="easyui-textbox" 
                        data-options="readonly:true, prompt:'Auto-generated after preview'">
                </div>

                <div class="fitem">
                    <label style="width:35%; display:inline-block;">Remarks</label>
                    <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
                </div>

                <input type="hidden" id="local_debit" class="easyui-numberbox">
                <input type="hidden" id="local_credit" class="easyui-numberbox">
            </div>
            
            <div style="clear:both;"></div>
        </fieldset>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Journal Posting List" data-options="singleSelect: false" toolbar="#toolbar2" rownumbers="true" , idField="number" showFooter="true">
            <thead>
                <tr>
                    <th rowspan="2" field="ck" checkbox="true">Posting</th>
                    <th rowspan="2" data-options="field:'remove',width:120, formatter:removebtn">Action</th>
                    <th hidden rowspan="2" data-options="field:'flag',width:100,editor: {type: 'textbox'}">Flag</th>
                    <th hidden rowspan="2" data-options="field:'id',width:150,editor: {type: 'textbox'}">ID</th>
                    <th rowspan="2" data-options="field:'trans_date',width:100,editor: {type: 'datebox',options: {formatter: myformatter,parser: myparser}}">Trans Date</th>

                    <th rowspan="2" data-options="field:'document_no',width:160,editor: {type: 'textbox', options: {required: true}}">Document No.</th>
                    <th rowspan="2" data-options="field:'invoice_no',width:120,editor: {type: 'textbox', options: {required: true}}">Invoice No</th>
                    <th rowspan="2" data-options="field:'journal_type_id',width:200,editor: {type: 'textbox', options: {required: true}}">Journal Type ID</th>

                    <th rowspan="2" data-options="field:'company_name',width:200, editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('master/customers/reads') ?>',
                            editable:false,
                            valueField: 'name',
                            textField: 'name',
                            prompt: 'Choose Company Name'
                        }}">Company Name</th>
                    <th rowspan="2" data-options="field:'account_number',width:100, halign:'center', editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('finance/account_coa/reads') ?>',
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
                                    field: 'account_name',
                                    title: 'Account Name',
                                    width: 200
                                }, ]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'account_name'
                                });

                                $(ed.target).textbox('setValue', rows.account_name);
                            }
                        }}">Account No</th>
                    <th rowspan="2" data-options="field:'account_name',width:250, editor: {type: 'textbox', options: {readonly: true}}">Account Name</th>
                    <th rowspan="2" data-options="field:'description',width:500,editor: {type: 'textbox', options: {required: true}}">Description</th>
                    <th colspan="3" data-options="field:'',width:100">Original Currency</th>
                    <th colspan="3" data-options="field:'',width:100">Local Currency</th>
                </tr>
                <tr>
                    <th data-options="field:'original_debit',width:120,halign:'center',align:'right',formatter:numberformatDefault,editor: {type: 'numberbox', options: {required: true, precision:2}}">Debit</th>
                    <th data-options="field:'original_credit',width:120,halign:'center',align:'right',formatter:numberformatDefault,editor: {type: 'numberbox', options: {required: true, precision:2}}">Credit</th>
                    <th data-options="field:'currency',width:80, editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('master/currencies/reads') ?>',
                            editable:false,
                            valueField: 'number',
                            textField: 'number',
                            panelHeight: 'auto',
                            prompt: 'Choose Currency',
                            onChange: function(val){
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'original_debit'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'original_credit'
                                });

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'rates'
                                });

                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'local_debit'
                                });

                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'local_credit'
                                });

                                var original_debit = $(ed.target).numberbox('getValue');
                                var original_credit = $(ed2.target).numberbox('getValue');

                                if(val == 'IDR'){
                                    $(ed3.target).numberbox('setValue', 1);
                                    $(ed4.target).numberbox('setValue', original_debit);
                                    $(ed5.target).numberbox('setValue', original_credit);
                                }
                            }
                        }}">Currency</th>
                    <th data-options="field:'rates',width:100,halign:'center',align:'right',formatter:numberformatDefault,editor: {type: 'numberbox', options: {required: true, precision:2}}">Rates</th>
                    <th data-options="field:'local_debit',width:120,halign:'center',align:'right',formatter:numberformatDefaultIdr,editor: {type: 'numberbox', options: {required: true, precision:2}}">Debit</th>
                    <th data-options="field:'local_credit',width:120,halign:'center',align:'right',formatter:numberformatDefaultIdr,editor: {type: 'numberbox', options: {required: true, precision:2}}">Credit</th>
                </tr>
            </thead>
        </table>
    </form>
</div>
<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
</div>


<!-- PDF -->
<iframe id="printout" src="<?= base_url('finance/journal_inventory/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    function getFilterParams() {
        var params = {
            filter_from: $("#filter_from").datebox('getValue'),
            filter_to: $("#filter_to").datebox('getValue'),
            filter_journal_type: $("#filter_journal_type").combogrid('getValue'),
            filter_type: $("#filter_type").combobox('getValue'),
            filter_modul: $("#filter_modul").combobox('getValue'),
            filter_division: $("#filter_division").combobox('getValue'),
            filter_item_category: $("#filter_item_category").combobox('getValue'),
            filter_voucher: $("#filter_voucher").textbox('getValue')
        };

        var queryString = "";
        for (var key in params) {
            // Pastikan nilai adalah string dan tidak null/undefined sebelum btoa
            var val = (params[key] || "").toString(); 
            queryString += "&" + key + "=" + window.btoa(val);
        }
        return queryString;
    }

    function filter() {
        var urlParams = getFilterParams();

        $('#dg').datagrid({
            url: '<?= base_url('finance/journal_inventory/datatables') ?>?' + urlParams,
            pagination: true,
            rownumbers: true,
            fit:true,
        });

        // Update preview printout
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Loading Preview...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/journal_inventory/print') ?>?' + urlParams);
    }

    function pdf() {
        // Pastikan iframe sudah ter-load dengan filter terakhir
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var urlParams = getFilterParams();
        window.location.assign('<?= base_url('finance/journal_inventory/print/excel') ?>?' + urlParams);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }



    // Onload Page
    $(function() {
        // --- JOURNAL TYPE ---
        $("#filter_journal_type").combogrid({
            url: '<?= base_url('finance/journal_inventory/readJournalType') ?>',
            panelWidth: 450,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            delay: 300,
            fitColumns: true,
            prompt: "Choose Journal Type",
            onBeforeLoad: function(param) {
                var selectedModul = $("#filter_modul").combobox('getValue');
                if (selectedModul) {
                    param.modul = window.btoa(selectedModul);
                }
            },
            columns: [[
                { field: 'number', title: 'Code', width: 90, halign: 'center', align: 'center' },
                { field: 'name', title: 'Journal Type Name', width: 250, halign: 'center' },
                { field: 'module', title: 'Modul', width: 200, halign: 'center' },
            ]],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });

        // --- CATEGORY ---
        $("#filter_item_category").combobox({
            url: '<?= base_url('master/item_categories/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Categories",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        // --- DIVISION ---
        $('#division, #filter_division').combobox({
            url: '<?= base_url('master/divisions/reads'); ?>',
            valueField: 'number',
            textField: 'number',
            panelHeight: 'auto',
            prompt: 'Choose Division',
            editable: false,
        });

        // --- MODUL ---
        $("#modul, #filter_modul").combogrid({
            url: '<?= base_url("finance/journal_inventory/readModul"); ?>',
            panelWidth: 500,
            idField: 'name',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Modul",
            columns: [[
                {field: 'process_type', title: 'Type', width: 60, align: 'center', 
                    formatter: function(value){
                        return value == 'IN' ? '<b style="color:green">IN</b>' : '<b style="color:red">OUT</b>';
                    }
                },
                {field: 'name', title: 'Module Name', width: 200},
                {field: 'category_number', title: 'Category', width: 150},
            ]],
            onBeforeLoad: function(param) {
                if($(this).attr('id') === 'modul'){
                    var cat  = $("#modul_category_number").combobox('getValue');
                    var proc = $("#modul_process_type").combobox('getValue');
                    if(cat) param.category_number = cat;
                    if(proc) param.process_type = proc;
                }
            },
            onChange: function(newValue) {
                if($(this).attr('id') === 'modul'){
                    $("#company_name").combogrid('clear');
                    $("#document_no").combogrid('clear');
                    
                    var valDate = $("#journal_date").datebox('getValue');
                    if(valDate && typeof number === "function") number(valDate);
                }
            }
        });

        // --- COMPANY NAME ---
        $("#company_name").combogrid({
            url: '<?= base_url('finance/journal_inventory/readCompany') ?>',
            idField: 'company_id',
            textField: 'company_name',
            mode: 'remote',
            fitColumns: true,
            panelWidth: 400,
            prompt: "Choose Company Name",
            columns: [[
                {field: 'company_id', title: 'ID', width: 100},
                {field: 'company_name', title: 'Company Name', width: 250}
            ]],
            onSelect: function(index, row) {
                const valModul = $("#modul").combogrid('getValue');
                const valDate  = $("#journal_date").datebox('getValue');

                if (!valModul || !valDate) {
                    toastr.warning("Please select Journal Date and Modul first!");
                    $(this).combogrid('clear');
                    return;
                }

                // Reload Document No Grid
                const g = $("#document_no").combogrid('grid');
                g.datagrid('load', {
                    modul: btoa(valModul),
                    journal_date: btoa(valDate),
                    company_id: btoa(row.company_id)
                });
            }
        });

        // --- DOCUMENT NO ---
        $("#document_no").combogrid({
            url: '<?= base_url('finance/journal_inventory/readDocumentNo') ?>',
            method: 'POST',
            panelWidth: 450,
            idField: 'document_no',
            textField: 'document_no',
            multiple: true,
            mode: 'remote', // Penting agar bisa menerima parameter dari load
            fitColumns: true,
            prompt: "Choose Document No.",
            columns: [[
                { field: 'ck', checkbox: true },
                { field: 'document_no', title: 'Document No.', width: 250 },
            ]]
        });

        // Initial Data On Load Page
        filter();

        // Input Modul Name
        $('#modul_name').textbox('textbox').bind('keyup', function(e) {
            var val = $(this).val();
            var newVal = val.toUpperCase();
            $('#modul_name').textbox('setValue', newVal);
        });

    });

    // DETAILS
    function btnDetails(val, row) {
        var details = "viewDetails('" + row.number + "')";
        return '<a class="btn btn-primary w-100" onClick="' + details + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewDetails(number) {
        $("#dlg_detail").window('open');
        $("#dlg_detail").window('setTitle', "Detail of " + number);

        $('#dg3').datagrid({
            url: '<?= base_url('finance/journal_inventory/datatableDetails?number=') ?>' + btoa(number),
            pagination: false,
            rownumbers: true,
            remoteFilter: true,
        }).datagrid('enableFilter');
    }

    // FORMAT DATE PERIOD
    function myformatter(date) {
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

    // FORMAT COLUMNS MAIN DATATABLE
    function numberformatDefault(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformatDefaultIdr(value, row){
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function approvedFormat(value, row) {
        if (row.approved_to == "" || row.approved_to == null) {
            return "<b style='color:green;'>APPROVED</b>";
        } else {
            return "<b style='color:red;'>CHECKING</b>";
        }
    }

    function approvedStyle(value, row, index) {
        if (row.approved_to == "" || row.approved_to == null) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function addModul() {
        $('#dlg_modul').dialog('open').dialog('center');
        $('#frm_modul').form('clear');
        // Set default value jika perlu
        $('#modul_category').combobox('setValue', 'RM');
        $('#modul_tr_type').combobox('setValue', 'IN');
    }

    function saveModul() {
        $('#frm_modul').form('submit', {
            url: '<?= base_url('finance/journal_inventory/saveModul') ?>',
            onSubmit: function() {
                return $(this).form('validate'); // Cek field required
            },
            success: function(result) {
                var res = JSON.parse(result);
                if (res.status === 'success') {
                    toastr.success(res.message, 'Success');
                    $('#dlg_modul').dialog('close');
                    // Refresh combogrid modul di dlg_insert jika sedang terbuka
                    $('#modul').combogrid('grid').datagrid('reload');
                } else {
                    toastr.error(res.message, 'Error');
                }
            }
        });
    }



    // FORM INSERT OR UPDATE
    function add() {
        $('#frm_insert').form('clear');        
        $('#dlg_insert').dialog('open').dialog('center');

        $("#journal_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#journal_date").datebox('enable');
        
        if ($("#modul").length) $("#modul").combobox('enable'); 

        $("#company_name").combogrid('enable');  
        $("#document_no").combogrid('enable');  
        $("#number").textbox('enable');         
        $("#preview").linkbutton('enable');
        $("#journal_date").datebox('textbox').focus();
        
        // Journal List (dg2)
        $('#dg2').datagrid({
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

    function preview() {
        const params = {
            journal_date: $("#journal_date").datebox('getValue'),
            division:     $("#division").combobox('getValue'),
            type:         $("#type").combobox('getValue'),
            modul:        $("#modul").combobox('getValue'),
            company_id:   $("#company_name").combogrid('getValue'),
            document_no:  $("#document_no").combogrid('getValues'), // Ini mengembalikan Array [DOC1, DOC2]
        };

        // Mapping untuk validasi
        const requiredFields = [
            { val: params.journal_date, label: 'Journal Date' },
            { val: params.division,     label: 'Division' },
            { val: params.modul,        label: 'Modul' },
            { val: params.company_id,   label: 'Company' },
            { val: params.document_no,  label: 'Document No.' },
        ];

        // Validasi menggunakan loop agar pesan toastr lebih spesifik
        let isValid = true;
        for (let item of requiredFields) {
            if (!item.val || (Array.isArray(item.val) && item.val.length === 0)) {
                toastr.info(`Please choose ${item.label} first!`);
                isValid = false;
                break;
            }
        }

        if (isValid) {
            // GL No.
            $.ajax({
                type: "post",
                url: "<?= base_url('finance/journal_inventory/number/') ?>" + window.btoa(params.journal_date),
                dataType: "html",
                success: function(result) {
                    if ($("#number").length > 0) {
                        $("#number").textbox('setValue', result.trim());
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching number: ", error);
                }
            });

            // Konversi Multiple document_no menjadi string dipisah koma
            const docNoStr = params.document_no.join(',');

            $.ajax({
                method: 'post',
                url: '<?= base_url('finance/journal_inventory/datatablesCheck') ?>',
                data: {
                    journal_date: params.journal_date,
                    modul: params.modul,
                    company_id: params.company_id,
                    document_no: docNoStr,
                },
                success: function(result) {
                    // Gunakan == 0 jika response dari server adalah string/int 0
                    if (result == 0) {
                        $('#dg2').datagrid({
                            url: '<?= base_url('finance/journal_inventory/datatablesTemp') ?>',
                            method: 'post',
                            queryParams: {
                                journal_date: window.btoa(params.journal_date),
                                modul: window.btoa(params.modul),
                                company_id: window.btoa(params.company_id),
                                document_no: window.btoa(docNoStr)
                            },
                            onLoadSuccess: function(data) {
                                if (data && data > 0 && data.footer && data.footer.length > 0) {
                                    $("#local_debit").numberbox('setValue', data.footer[0].local_debit);
                                    $("#local_credit").numberbox('setValue', data.footer[0].local_credit);
                                } else {
                                    toastr.warning("Requirements not met", "Failed");

                                    $.messager.alert('Warning', 'Requirements not met: Cannot generate journal for this Document No. Please check again.', 'warning');
                                    return;
                                }
                            }
                        });
                    } else {
                        toastr.error("This Document No. in Modul " + params.modul + " has been created");
                    }
                },
                error: function(jqXHR) {
                    toastr.error("Error: " + jqXHR.statusText);
                },
            });
        }
    }

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg2').datagrid('validateRow', editIndex)) {
            $('#dg2').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function onClickCell(index, field) {
        var modul = $("#modul").combobox('getValue');

        if(modul == "ADJUSTMENT"){
            if (editIndex != index) {
                if (endEditing()) {
                    $('#dg2').datagrid('selectRow', index).datagrid('beginEdit', index);
                    editIndex = index;
                } else {
                    setTimeout(function() {
                        $('#dg2').datagrid('selectRow', editIndex);
                    }, 0);
                }
            }
        }else{
            toastr.warning("Not Available for Modul " + modul);
        }
    }

    function append() {
        var modul = $("#modul").combobox('getValue');
        var journal_date = $("#journal_date").datebox('getValue');

        if(modul == "ADJUSTMENT"){
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    "flag": 1,
                    "trans_date": journal_date
                });

                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        }else{
            toastr.warning("Not Available for Modul " + modul);
        }
    }

    function removebtn(value, row, index) {
        if (row.editing) {
            var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a> ';
            var c = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="cancelrow(this)">Cancel</a>';
            return s + c;
        } else {
            if(row.currency != "TOTAL"){
                var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a> ';
                if(row.id != null){
                    var d = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="deleterow('+row.id+')">Delete</a>';
                }else{
                    var d = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="deleterowedit(this)">Delete</a>';
                }

                return e + d;
            }
        }
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function editrow(target) {
        var modul = $("#modul").combobox('getValue');

        if(modul == "ADJUSTMENT"){
            $('#dg2').datagrid('selectRow', getRowIndex(target));
            $('#dg2').datagrid('beginEdit', getRowIndex(target));
        }else{
            toastr.warning("Not Available for Modul " + modul);
        }
    }

    function saverow(target) {
        $('#dg2').datagrid('endEdit', getRowIndex(target));

        var rows = $('#dg2').datagrid('getRows');
        var totalrows = rows.length;

        var original_debit = 0;
        var original_credit = 0;
        var local_debit = 0;
        var local_credit = 0;
        for (let i = 0; i < totalrows; i++) {
            original_debit += parseFloat(rows[i].original_debit);
            original_credit += parseFloat(rows[i].original_credit);
            local_debit += parseFloat(rows[i].local_debit);
            local_credit += parseFloat(rows[i].local_credit);
        }

        $('#dg2').datagrid('reloadFooter', [{
            currency: "TOTAL",
            original_debit: original_debit,
            original_credit: original_credit,
            local_debit: local_debit,
            local_credit:local_credit
        }]);

        $("#local_debit").numberbox('setValue', local_debit);
        $("#local_credit").numberbox('setValue', local_credit);
    }

    function cancelrow(target) {
        $('#dg2').datagrid('cancelEdit', getRowIndex(target));
    }

    function deleterowedit(target){
        $('#dg2').datagrid('deleteRow', getRowIndex(target));

        var rows = $('#dg2').datagrid('getRows');
        var totalrows = rows.length;

        var original_debit = 0;
        var original_credit = 0;
        var local_debit = 0;
        var local_credit = 0;
        for (let i = 0; i < totalrows; i++) {
            original_debit += parseFloat(rows[i].original_debit);
            original_credit += parseFloat(rows[i].original_credit);
            local_debit += parseFloat(rows[i].local_debit);
            local_credit += parseFloat(rows[i].local_credit);
        }

        $('#dg2').datagrid('reloadFooter', [{
            currency: "TOTAL",
            original_debit: original_debit,
            original_credit: original_credit,
            local_debit: local_debit,
            local_credit:local_credit
        }]);

        $("#local_debit").numberbox('setValue', local_debit);
        $("#local_credit").numberbox('setValue', local_credit);
    }

    function deleterow(id) {
        $.messager.confirm('Confirm', 'Are you sure you want to delete this data?', function(r) {
            if (r) {
                $.ajax({
                    method: 'post',
                    url: '<?= base_url('finance/journal_inventory/delete') ?>',
                    data: {
                        id: id,
                    },
                    success: function(result) {
                        var result = eval('(' + result + ')');
                        toastr.success(result.message);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        toastr.error(jqXHR.statusText);
                    },
                    complete: function(data) {
                        $('#dg2').datagrid('reload');
                    }
                });
            }
        });
    }

    // CREATE
    $('#dlg_insert').dialog({
        buttons: [{
            text: 'Save All',
            iconCls: 'icon-ok',
            handler: function() {
                // Get data Header
                const voucher_no   = $("#number").textbox('getValue');
                const journal_date = $("#journal_date").datebox('getValue');
                const modul        = $("#modul").combobox('getValue');
                const company_id   = $("#company_name").combogrid('getValue');
                const remarks      = $("#remarks").textbox('getValue');
                const local_debit  = $("#local_debit").numberbox('getValue');
                const local_credit = $("#local_credit").numberbox('getValue');

                // Get semua data Posting dari Datagrid Preview (#dg2)
                const rows = $('#dg2').datagrid('getRows');
                if (rows.length === 0) {
                    toastr.warning("No data to save. Please preview first.");
                    return false;
                }

                // Validasi Balance
                if (parseFloat(local_debit).toFixed(2) !== parseFloat(local_credit).toFixed(2)) {
                    $.messager.alert('Warning', '<b>Balance Error!</b><br>Total Debit (' + local_debit + ') must be equal to Total Credit (' + local_credit + ').', 'warning');
                    return false;
                }

                // Confirm Save
                $.messager.confirm('Confirm', 'Are you sure you want to save ' + rows.length + ' rows of journal?', function(r) {
                    if (r) {
                        Swal.fire({
                            title: 'Saving Data...',
                            html: 'Please wait while we process your request',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        // Send ALL data dalam satu request (tidak recursive satu per satu)
                        $.ajax({
                            type: "post",
                            url: '<?= base_url('finance/journal_inventory/create') ?>',
                            data: {
                                voucher_no: voucher_no,
                                journal_date: journal_date,
                                modul: modul,
                                company_id: company_id,
                                remarks: remarks,
                                details: JSON.stringify(rows)
                            },
                            dataType: 'json',
                            success: function(res) {
                                Swal.close();
                                if (res.theme === 'success') {
                                    toastr.success(res.message);
                                    Swal.fire('Success', res.message, 'success');

                                    $('#dlg_insert').dialog('close');
                                    $('#dg').datagrid('reload');
                                } else {
                                    Swal.fire('Error', res.message, 'error');
                                }

                                $('#dlg_insert').dialog('close');
                            },
                            error: function(xhr) {
                                Swal.close();
                                toastr.error("Server Error: " + xhr.statusText);
                            }
                        });
                    }
                });
            }
        }]
    });

    // DELETE
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete ' + rows.length + ' selected data?', function(r) {
                if (r) {
                    // Get all nomor voucher dan cek locking period
                    var numbers = [];
                    var journal_dates = [];
                    
                    for (var i = 0; i < rows.length; i++) {
                        numbers.push(rows[i].number);
                        journal_dates.push(rows[i].journal_date);
                    }

                    // Check Lock Period
                    $.ajax({
                        type: "post",
                        url: "<?= base_url('closing/locks/checkLock') ?>",
                        data: {
                            period: journal_dates[0],       // cek tanggal baris pertama saja
                            menus_id: "<?= $menus_id ?>"
                        },
                        dataType: "json",
                        success: function (lock) {
                            if (lock.total > 0) {
                                toastr.error("This period is locked by Accounting. Cannot delete data.");
                                return false;
                            }

                            // Proses Delete Massal jika tidak ter-lock
                            Swal.fire({
                                title: 'Deleting Data...',
                                allowOutsideClick: false,
                                didOpen: () => { Swal.showLoading(); }
                            });

                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('finance/journal_inventory/delete') ?>',
                                data: { 
                                    voucher_numbers: numbers,
                                },
                                dataType: 'json',
                                success: function(result) {
                                    Swal.close();
                                    if (result.theme === 'success') {
                                        toastr.success(result.message);
                                        $('#dg').datagrid('reload');
                                        $('#dg').datagrid('clearSelections');
                                    } else {
                                        Swal.fire("Error", result.message, "error");
                                    }
                                },
                                error: function(jqXHR) {
                                    Swal.close();
                                    toastr.error("Server Error: " + jqXHR.statusText);
                                }
                            });
                        }
                    });
                }
            });
        } else {
            toastr.warning("Please select at least one data in the table first!", "Information");
        }
    }

</script>

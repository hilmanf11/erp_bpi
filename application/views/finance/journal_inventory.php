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

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="filter_division" class="easyui-combobox">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width:50%; float:left;">
                <div class="fitem">
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
                        data-options="editable:false, valueField:'id', textField:'text', 
                            groupField:'group',panelHeight:'auto'">
                    </select>
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


<!-- Detail -->
<div id="dlg_detail" class="easyui-window" title="Journal Detail" data-options="closed: true,modal:true" style="width: 800px; height: 500px; top: 20px; left:10px;">
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
    style="width: 98%; height: 520px; padding:10px; overflow-y: auto;">

    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:70%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;" id="fieldset">
            <legend><b>Form Data</b></legend>
            <div style="width:50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Journal Date</span>
                    <input style="width:30%;" name="journal_date" id="journal_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="division" class="easyui-combobox" required>
                </div>
                
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Type</span>
                    <select style="width:60%;" id="type" name="type" class="easyui-combobox" data-options="editable:false, panelHeight:'auto'" required>
                        <option value="">Choose All</option>
                        <option value="IN">IN</option>
                        <option value="OUT">OUT</option>
                    </select>
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Modul</span>
                    <select style="width:60%;" id="modul" name="modul" class="easyui-combobox" 
                        data-options="editable:false, valueField:'id', textField:'text', 
                            groupField:'group',panelHeight:'auto'" required>
                    </select>
                </div>

                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Journal Type</span>
                    <input style="width:60%;" name="journal_type_id" id="journal_type" class="easyui-combogrid">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" id="preview" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="width:50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Company Name</span>
                    <input style="width:60%;" name="company_name" id="company_name" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No.</span>
                    <input style="width:60%;" name="document_no" id="document_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Vourcher No</span>
                    <input style="width:60%;" name="number" id="number" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
                </div>

                <!-- Buat Validasi -->
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Local Debit</span>
                    <input style="width:60%;" id="local_debit" disabled class="easyui-numberbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Local Credit</span>
                    <input style="width:60%;" id="local_credit" disabled class="easyui-numberbox">
                </div>
            </div>
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

    //NOMOR AUTOMATIC
    function number(journal_date) {
        var dateValue = journal_date ? journal_date : "<?= date('Y-m-d') ?>";
        
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/journal_inventory/number/') ?>" + window.btoa(dateValue),
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
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }



    $(function() {
        let masterModules = [];

        // Load JSON Module
        $.getJSON('<?= base_url("json/journal_inventory_module.json"); ?>', function(data) {
            masterModules = data;
            refreshModuleCombo(''); 
        });

        function refreshModuleCombo(selectedGroup) {
            const filtered = !selectedGroup 
                ? masterModules 
                : masterModules.filter(item => item.group === selectedGroup);

            const finalData = [{ id: '', text: 'Choose All', group: '' }, ...filtered];
            
            // Update kedua id (filter_modul dan modul) sekaligus
            $('#filter_modul, #modul').each(function() {
                if ($(this).length) {
                    $(this).combobox('loadData', finalData).combobox('setValue', '');
                }
            });
        }

        // Listener untuk Type (Grouping IN/OUT)
        $('#filter_type, #type').combobox({
            onSelect: (rec) => refreshModuleCombo(rec.value),
            onChange: (val) => { if(!val) refreshModuleCombo(''); }
        });

        // Inisialisasi awal
        filter();
        
        // Listener Tanggal Jurnal
        $("#journal_date").datebox({
            onChange: (val) => number(val)
        });

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
                { field: 'status', title: 'D/C', width: 60, halign: 'center', align: 'center' }
            ]],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });

        // Get Division
        const divisionConfig = {
            url: '<?= base_url('master/divisions/reads'); ?>',
            valueField: 'number',
            textField: 'number',
            panelHeight: 'auto',
            prompt: 'Choose Division',
            editable: false,
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        };
        $('#filter_division, #division').combobox(divisionConfig);

        $("#document_no").combogrid({
            panelWidth: 450,
            idField: 'document_no',
            textField: 'document_no',
            multiple: true,
            selectOnCheck: true,
            checkOnSelect: true,
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Document No.",
            columns: [[
                { field: 'ck', checkbox: true },
                { field: 'document_no', title: 'Document No.', width: 250 },
            ]]
        });

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
            onSelect: function(index, rowcom) {
                // console.log("Index:", index, "Data:", rowcom);
                var valModul = $("#modul").combobox('getValue');
                var valDate  = $("#journal_date").datebox('getValue');
                var valJType = $("#journal_type").val() || ""; 

                if (!valModul || valModul === "") {
                    toastr.warning("Please select Modul first!");
                    
                    var target = $(this);
                    setTimeout(function(){
                        target.combogrid('clear');
                    }, 100);
                    
                    return false;
                }

                $("#document_no").combogrid('clear');
                var targetUrl = '<?= base_url('finance/journal_inventory/readModul') ?>';
                var g = $("#document_no").combogrid('grid');
                
                g.datagrid({
                    url: targetUrl,
                    method: 'POST',
                    idField: 'document_no',
                    singleSelect: false,
                    selectOnCheck: true,
                    checkOnSelect: true,
                    queryParams: {
                        modul: btoa(valModul),
                        journal_date: btoa(valDate),
                        company_id: btoa(rowcom.company_id),
                        journal_type: btoa(valJType),
                    },
                    onLoadSuccess: function(data) {
                        if (data && data.theme === "error") {
                            toastr.error(data.message, data.title);
                            $("#company_name").combogrid('clear');
                        }
                    }
                });
            }
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


    // FORM INSERT OR UPDATE
    function add() {
        $('#frm_insert').form('clear');        
        $('#dlg_insert').dialog('open').dialog('center');

        $("#journal_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#journal_date").datebox('enable');
        
        if ($("#modul").length) $("#modul").combobox('enable'); 

        $("#journal_type").combogrid('enable'); 
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
            journal_type: ($("#journal_type").val() || ""),
        };

        // Mapping untuk validasi
        const requiredFields = [
            { val: params.journal_date, label: 'Journal Date' },
            { val: params.division,     label: 'Division' },
            { val: params.type,         label: 'Type' },
            { val: params.modul,        label: 'Module' },
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
            // Konversi Multiple document_no menjadi string dipisah koma
            const docNoStr = params.document_no.join(',');

            $.ajax({
                method: 'post',
                url: '<?= base_url('finance/journal_inventory/datatablesCheck') ?>',
                data: {
                    journal_date: params.journal_date,
                    modul: params.modul,
                    journal_type: params.journal_type,
                    company_id: params.company_id,
                    document_no: docNoStr,
                },
                success: function(result) {
                    // Gunakan == 0 jika response dari server adalah string/int 0
                    if (result == 0) {
                        $('#dg2').datagrid({
                            // Gunakan method POST pada datagrid agar parameter tidak bocor di URL 
                            // dan tidak terkena limitasi karakter URL (karena DOC NO bisa banyak)
                            url: '<?= base_url('finance/journal_inventory/datatablesTemp') ?>',
                            method: 'post',
                            queryParams: {
                                journal_date: window.btoa(params.journal_date),
                                modul: window.btoa(params.modul),
                                journal_type: window.btoa(params.journal_type),
                                company_id: window.btoa(params.company_id),
                                document_no: window.btoa(docNoStr)
                            },
                            onLoadSuccess: function(data) {
                                if (data && data.footer && data.footer.length > 0) {
                                    $("#local_debit").numberbox('setValue', data.footer[0].local_debit);
                                    $("#local_credit").numberbox('setValue', data.footer[0].local_credit);
                                }
                            }
                        });
                    } else {
                        toastr.error("This Modul " + params.modul + " has been created");
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

</script>

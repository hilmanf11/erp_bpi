<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'details',width:90,align:'center', formatter:btnDetails">Detail</th>
            <th rowspan="2" data-options="field:'journal_date',width:100,align:'center'">Journal Date</th>
            <th rowspan="2" data-options="field:'number',width:100,align:'center'">GL No</th>
            <th rowspan="2" data-options="field:'journal_type_name',width:200,halign:'center'">Journal Type</th>
            <th rowspan="2" data-options="field:'modul',width:150,halign:'center'">Modul</th>
            <th colspan="3" data-options="field:'',width:200,halign:'center'">Original Currency</th>
            <th colspan="3" data-options="field:'',width:200,halign:'center'">Local Currency</th>
            <th rowspan="2" data-options="field:'remarks',width:100,halign:'center'">Remarks</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center'"> Approval</th>
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
            <th data-options="field:'approved_by',width:100,align:'center'"> By</th>
            <th data-options="field:'approved',width:100,align:'center', styler:approvedStyle, formatter:approvedFormat"> Status</th>
            <th data-options="field:'approved_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
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
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="print_voucher()"><i class="fa fa-print"></i> Print Voucher</a>
                </div>
            </div>
            <div style="width:50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Modul</span>
                    <select style="width:60%;" id="filter_modul" class="easyui-combobox">
                        <option value="">Choose All</option>
                        <option value="PURCHASE INVOICING">PURCHASE INVOICING</option>
                        <option value="SALES INVOICING">SALES INVOICING</option>
                        <option value="AP PAYMENT">AP PAYMENT</option>
                        <option value="AR RECEIPT">AR RECEIPT</option>
                        <option value="SUPPLY MATERIAL">SUPPLY MATERIAL</option>
                        <option value="FINISH GOOD IN">FINISH GOOD IN</option>
                        <option value="FINISH GOOD OUT">FINISH GOOD OUT</option>
                        <option value="DIRECT LABOUR">DIRECT LABOUR</option>
                        <option value="FOH">FOH</option>
                        <option value="ASSET">ASSET</option>
                        <option value="CURRENCY REVALUATION">CURRENCY REVALUATION</option>
                        <option value="ADJUSTMENT">ADJUSTMENT</option>
                        <option value="CLOSING JOURNAL">CLOSING JOURNAL</option>
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

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
</div>

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add New Posting Journal" data-options="closed: true,modal:true, fit:true" style="width: 1400px; padding:10px; top: 0; left: 0;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:70%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;" id="fieldset">
            <legend><b>Form Data</b></legend>
            <div style="width:50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Journal Date</span>
                    <input style="width:30%;" name="journal_date" id="journal_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Date</span>
                    <input style="width:30%;" id="transaction_from" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="transaction_to" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Modul</span>
                    <select style="width:60%;" name="modul" id="modul" class="easyui-combobox" required>
                        <option value="PURCHASE INVOICING">PURCHASE INVOICING</option>
                        <option value="SALES INVOICING">SALES INVOICING</option>
                        <option value="AP PAYMENT">AP PAYMENT</option>
                        <option value="AR RECEIPT">AR RECEIPT</option>
                        <option value="SUPPLY MATERIAL">SUPPLY MATERIAL</option>
                        <option value="FINISH GOOD IN">FINISH GOOD IN</option>
                        <option value="FINISH GOOD OUT">FINISH GOOD OUT</option>
                        <option value="DIRECT LABOUR">DIRECT LABOUR</option>
                        <option value="FOH">FOH</option>
                        <option value="ASSET">ASSET</option>
                        <option value="CURRENCY REVALUATION">CURRENCY REVALUATION</option>
                        <option value="ADJUSTMENT">ADJUSTMENT</option>
                        <option value="CLOSING JOURNAL">CLOSING JOURNAL</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Journal Type</span>
                    <input style="width:60%;" name="journal_type_id" id="journal_type" class="easyui-combogrid" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" id="preview" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="width:50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Company Name</span>
                    <input style="width:60%;" name="company_name" id="company_name" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
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
                    <th rowspan="2" data-options="field:'document_no',width:160,editor: {type: 'textbox', options: {required: true}}">Document No</th>
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

<div id="dlg_detail" class="easyui-window" title="Journal Detail" data-options="closed: true,modal:true" style="width: 800px; height: 500px; top: 20px; left:10px;">
    <table id="dg3" class="easyui-datagrid" style="width:100%;" showFooter="true">
        <thead>
            <tr>
                <th rowspan="2" data-options="field:'trans_date',width:100,halign:'center'">Trans Date</th>
                <th rowspan="2" data-options="field:'document_no',width:150,halign:'center'">Document No</th>
                <th rowspan="2" data-options="field:'invoice_no',width:150,halign:'center'">Invoice No</th>
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

<div id="dlg_generate" class="easyui-dialog" title="Save Data" data-options="closed: true,modal:true,closable: false" style="width: 500px; padding:10px; top: 20px;">
    <div class="alert alert-warning" role="alert">
        Please wait until the save process is complete
    </div>
    <div id="p_upload" class="easyui-progressbar" style="width:460px; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:460px; height:200px; padding:10px; margin-top: 10px;">
        <p>History Save Data</p>
        <ul id="remarks">

        </ul>
    </div>
</div>

<!-- UPLOAD DATA -->
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
<iframe id="printout" src="<?= base_url('finance/journal_postings/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $("#fieldset").show();
        $('#frm_insert').form('clear');

        // $("#transaction_from").datebox('setValue', "<?= date("Y-m-01") ?>");
        // $("#transaction_to").datebox('setValue', "<?= date("Y-m-t") ?>");
        $("#journal_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#journal_date").datebox('enable');
        // $("#transaction_from").datebox('enable');
        // $("#transaction_to").datebox('enable');
        $("#modul").datebox('enable');
        $("#journal_type").datebox('enable');
        $("#company_name").datebox('enable');
        $("#document_no").datebox('enable');
        $("#number").datebox('enable');
        $("#preview").linkbutton('enable');

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

    //NOMOR AUTOMATIC
    function number(journal_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/journal_postings/number/') ?>" + window.btoa(journal_date),
            dataType: "html",
            success: function(result) {
                $("#number").textbox('setValue', result);
            }
        });
    }

    function preview() {
        var journal_date = $("#journal_date").datebox('getValue');
        // var transaction_to = $("#transaction_to").datebox('getValue');
        var modul = $("#modul").combobox('getValue');
        var company_id = $("#company_name").combobox('getValue');
        var document_no = $("#document_no").combogrid('getText');

        if (modul == "" || (jQuery.inArray(modul, ['PURCHASE INVOICING','SALES INVOICING','AP PAYMENT','AR RECEIPT']) >= 0 && document_no == "")) {
            toastr.info('Please Choose Modul and Document No');
        } else {

            $.ajax({
                method: 'post',
                url: '<?= base_url('finance/journal_postings/datatablesCheck') ?>',
                data: {
                    journal_date: journal_date,
                    modul: modul,
                    company_id: company_id,
                    document_no: document_no,
                },
                success: function(result) {
                    if(result == 0){
                        var lastIndex;
                        var dg = $('#dg2').datagrid({
                            url: '<?= base_url('finance/journal_postings/datatablesTemp') ?>?journal_date=' + window.btoa(journal_date) +
                                "&modul=" + window.btoa(modul) +
                                "&company_id=" + window.btoa(company_id) +
                                "&document_no=" + window.btoa(document_no),
                            onLoadSuccess: function(data){
                                $("#local_debit").numberbox('setValue', data['footer'][0].local_debit);
                                $("#local_credit").numberbox('setValue', data['footer'][0].local_credit);
                            }
                        });
                    }else{
                        toastr.error("This Modul " + modul + " has been created");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    toastr.error(jqXHR.statusText);
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
        var modul = $("#modul").combobox('getValue')

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
                    url: '<?= base_url('finance/journal_postings/delete') ?>',
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

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            // toastr.warning("Not Available");
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            // $("#transaction_from").datebox('setValue', "<?= date("Y-m-01") ?>");
            // $("#transaction_to").datebox('setValue', "<?= date("Y-m-t") ?>");

            $("#journal_date").datebox('disable');
            // $("#transaction_from").datebox('disable');
            // $("#transaction_to").datebox('disable');
            $("#modul").datebox('disable');
            $("#journal_type").datebox('disable');
            $("#company_name").datebox('disable');
            $("#document_no").datebox('disable');
            $("#number").datebox('disable');
            $("#preview").linkbutton('disable');

            // $("#fieldset").hide();
            setTimeout(function() {
                $("#number").textbox('setValue', row.number);
                $("#modul").textbox('setValue', row.modul);
                $("#journal_type").textbox('setValue', row.journal_type_id);
            }, 2000);


            var lastIndex;
            $('#dg2').datagrid({
                url: '<?= base_url('finance/journal_postings/datatableUpdates') ?>?number=' + window.btoa(row.number),
                onLoadSuccess: function(data){
                    $("#local_debit").numberbox('setValue', data['footer'][0].local_debit);
                    $("#local_credit").numberbox('setValue', data['footer'][0].local_credit);
                },
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

        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('closing/locks/checkLock') ?>",
                            data: "period=" + row.journal_date + "&menus_id=<?= $menus_id ?>",
                            dataType: "json",
                            success: function (lock) {
                                if(lock.total > 0){
                                    toastr.error("This period is not active by Accounting");
                                    return false;
                                }

                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('finance/journal_postings/delete') ?>',
                                    data: {
                                        number: row.number
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
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_journal_type = $("#filter_journal_type").combogrid('getValue');
        var filter_modul = $("#filter_modul").combobox('getValue');
        var filter_voucher = $("#filter_voucher").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_journal_type=" + window.btoa(filter_journal_type) +
            "&filter_modul=" + window.btoa(filter_modul) +
            "&filter_voucher=" + window.btoa(filter_voucher);

        $('#dg').datagrid({
            url: '<?= base_url('finance/journal_postings/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit:true,
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/journal_postings/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_journal_type = $("#filter_journal_type").combogrid('getValue');
        var filter_modul = $("#filter_modul").combobox('getValue');
        var filter_voucher = $("#filter_voucher").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_journal_type=" + window.btoa(filter_journal_type) +
            "&filter_modul=" + window.btoa(filter_modul) +
            "&filter_voucher=" + window.btoa(filter_voucher);

        window.location.assign('<?= base_url('finance/journal_postings/print/excel') ?>' + url);
    }

    //UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    //DOWNLOAD TEMPLATE UPLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_journal_postings.xls') ?>');
    }

    function print_voucher() {
        var row = $('#dg').datagrid('getSelected');

        if (row) {
            window.open("<?= base_url('finance/journal_postings/print_voucher/') ?>" + window.btoa(row.number), "_blank", 'location=yes,height=600,width=1200,scrollbars=yes,status=yes');
        } else {
            toastr.warning("Please select GL No in the table first!");
        }
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        filter();

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var journal_date = $("#journal_date").datebox('getValue');
                    var modul = $("#modul").combobox('getValue');
                    var journal_type = $("#journal_type").combogrid('getValue');
                    var number = $("#number").textbox('getValue');
                    var remarks = $("#remarks").textbox('getValue');

                    var local_debit = $("#local_debit").numberbox('getValue');
                    var local_credit = $("#local_credit").numberbox('getValue');

                    endEditing();
                    //var rows = $('#dg2').datagrid('getSelections');
                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('closing/locks/checkLock') ?>",
                        data: "period=" + journal_date + "&menus_id=<?= $menus_id ?>",
                        dataType: "json",
                        success: function (lock) {
                            if(lock.total > 0){
                                toastr.error("This period is not active by Accounting");
                                return false;
                            }

                            if (rows.length > 0) {
                                if(local_debit == local_credit){
                                    $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                                        if (r) {
                                            Swal.fire({
                                                title: 'Please Wait for Saving Data',
                                                showConfirmButton: false,
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                didOpen: () => {
                                                    Swal.showLoading();
                                                },
                                            });

                                            if (totalrows > 0) {
                                                requestData(totalrows, rows);
                                                $('#dlg_insert').dialog('close');
                                                $('#dlg_generate').dialog('open');

                                                function requestData(total, json, jml = 1, value = 0) {
                                                    if (value < 100) {
                                                        value = Math.floor((jml / total) * 100);
                                                        var i = (jml - 1);

                                                        $('#p_upload').progressbar('setValue', value);
                                                        $('#p_start').html(jml);
                                                        $('#p_finish').html(totalrows);

                                                        $.ajax({
                                                            type: "post",
                                                            url: '<?= base_url('finance/journal_postings/create') ?>',
                                                            data: {
                                                                journal_date: journal_date,
                                                                modul: modul,
                                                                journal_type_id: journal_type,
                                                                number: number,
                                                                remarks: remarks,
                                                                id: json[i].id,
                                                                trans_date: json[i].trans_date,
                                                                document_no: json[i].document_no,
                                                                invoice_no: json[i].invoice_no,
                                                                company_name: json[i].company_name,
                                                                account_number: json[i].account_number,
                                                                account_name: json[i].account_name,
                                                                description: json[i].description,
                                                                currency: json[i].currency,
                                                                original_debit: json[i].original_debit,
                                                                original_credit: json[i].original_credit,
                                                                rates: json[i].rates,
                                                                local_debit: json[i].local_debit,
                                                                local_credit: json[i].local_credit
                                                            },
                                                            dataType: "json",
                                                            success: function(result) {
                                                                requestData(total, json, jml + 1, value);

                                                                if (result.theme == "success") {
                                                                    var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                                } else {
                                                                    var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                                }

                                                                $("#p_remarks").append(title + "<br>");

                                                                if (i == (totalrows - 1)) {
                                                                    Swal.close();
                                                                    $('#dlg_generate').dialog('close');

                                                                    Swal.fire({
                                                                        title: result.message,
                                                                        icon: result.theme,
                                                                        confirmButtonText: 'Ok',
                                                                        allowOutsideClick: false,
                                                                    }).then((result) => {
                                                                        if (result.isConfirmed) {
                                                                            window.location.reload();
                                                                        }
                                                                    });
                                                                }
                                                            }
                                                        });
                                                    }
                                                }
                                            }
                                        }
                                    });
                                } else {
                                    toastr.error("Debit & Credit Not Balance");
                                }
                            } else {
                                toastr.info("Please Checklist Posting");
                            }
                        }
                    });
                }
            }]
        });

        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('finance/journal_postings/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('finance/journal_postings/upload') ?>',
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
                                url: "<?= base_url('finance/journal_postings/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('finance/journal_postings/uploadCreate') ?>",
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
                                                    url: "<?= base_url('finance/journal_postings/uploadcreateFailed') ?>",
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

        $("#filter_journal_type").combogrid({
            url: '<?= base_url('finance/journal_postings/readJournalType?modul=') ?>',
            panelWidth: 360,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Journal Type",
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 250
                }]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });

        $("#modul").combobox({
            onChange: function(modul) {
                var journal_date = $("#journal_date").datebox('getValue');

                if (journal_date == "") {
                    toastr.info('Please Select Transaction Date');
                    $("#modul").combobox('clear');
                } else {
                    $("#journal_type").combogrid({
                        url: '<?= base_url('finance/journal_postings/readJournalType?journal_date=') ?>' + btoa(journal_date) + "&modul=" + btoa(modul),
                        panelWidth: 360,
                        idField: 'id',
                        textField: 'name',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: "Choose Journal Type",
                        columns: [
                            [{
                                field: 'number',
                                title: 'Code',
                                width: 100
                            }, {
                                field: 'name',
                                title: 'Name',
                                width: 250
                            }]
                        ],
                        onSelect: function(val, row) {
                            $("#company_name").combobox({
                                url: '<?= base_url('finance/journal_postings/readCompany?modul=') ?>' + btoa(modul) + "&journal_date=" + btoa(journal_date) + "&journal_type=" + btoa(row.id),
                                valueField: 'company_id',
                                textField: 'company_name',
                                prompt: "Choose Company Name",
                                onSelect: function(rowcom) {
                                    $("#document_no").combogrid({
                                        url: '<?= base_url('finance/journal_postings/readModul?modul=') ?>' + btoa(modul) + "&journal_date=" + btoa(journal_date) + "&journal_type=" + btoa(row.id) + "&company_id=" + btoa(rowcom.company_id),
                                        panelWidth: 200,
                                        idField: 'number',
                                        textField: 'number',
                                        multiple: true,
                                        mode: 'remote',
                                        fitColumns: true,
                                        prompt: "Choose Document No",
                                        columns: [
                                            [{
                                                field:'ck',
                                                checkbox:true
                                            },{
                                                field: 'number',
                                                title: 'Document No',
                                                width: 150
                                            }]
                                        ],
                                    });
                                }
                            });
                        }
                    });
                }
            }
        });

        $("#journal_date").datebox({
            onChange: function(val) {
                number(val);
            }
        });
    });

    function btnDetails(val, row) {
        var details = "viewDetails('" + row.number + "')";
        return '<a class="btn btn-primary w-100" onClick="' + details + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewDetails(number) {
        $("#dlg_detail").window('open');
        $("#dlg_detail").window('setTitle', "Detail of " + number);

        $('#dg3').datagrid({
            url: '<?= base_url('finance/journal_postings/datatableDetails?number=') ?>' + btoa(number),
            pagination: false,
            rownumbers: true,
            remoteFilter: true,
        }).datagrid('enableFilter');
    }

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
</script>
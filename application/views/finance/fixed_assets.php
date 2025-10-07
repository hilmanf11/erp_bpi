<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'number',width:150,halign:'center'">Asset No</th>
            <th data-options="field:'name',width:350,halign:'center',resizable:true">Asset Name</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'item_rm_id',width:200,align:'center',hidden:true">Item RM ID</th>
            <th rowspan="2" data-options="field:'asset_family_name',width:200,halign:'center'">Asset Family</th>
            <th rowspan="2" data-options="field:'asset_category_type',width:200,halign:'center'">Asset Type</th>
            <th rowspan="2" data-options="field:'purchase_invoice_number',width:150,halign:'center'">Purchase Invoice No</th>
            <th rowspan="2" data-options="field:'supplier_name',width:200,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Purchase Date</th>
            <th rowspan="2" data-options="field:'usage_date',width:100,align:'center'">Usage Date</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right'">Qty</th>
            <th rowspan="2" data-options="field:'cost',width:100,halign:'center',align:'right', formatter:priceformat">Cost</th>
            <th colspan="2" data-options="field:'',width:100,align:'center'">Estimated</th>
            <th rowspan="2" data-options="field:'expired_date',width:100,align:'center'">Expired Date</th>
            <th rowspan="2" data-options="field:'depreciation',width:100,halign:'center',align:'right', formatter:priceformat">Depreciation</th>
            <th rowspan="2" data-options="field:'depreciation_acc',width:100,halign:'center',align:'right', formatter:priceformat">Accumulation<br>Depreciation</th>
            <th rowspan="2" data-options="field:'book_value',width:100,halign:'center',align:'right', formatter:priceformat">Book<br>Value</th>
            <th rowspan="2" data-options="field:'method',width:100,halign:'center'">Depreciation<br>Method</th>
            <th rowspan="2" data-options="field:'department',width:100,halign:'center'">Current Department</th>
            <th rowspan="2" data-options="field:'location',width:100,halign:'center'">Current Location</th>
            <th rowspan="2" data-options="field:'previous_department',width:150,halign:'center'">Previous Department</th>
            <th rowspan="2" data-options="field:'previous_location',width:150,halign:'center'">Previous Location</th>
            <th rowspan="2" data-options="field:'status_expired',width:100,align:'center',formatter:statusformat,styler:statusStyle">Status</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'estimate_year',width:80,align:'center'"> Year</th>
            <th data-options="field:'estimate_month',width:80,align:'center'"> Month</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 225px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Date</span>
                    <input style="width:28%;" id="filter_from" value="<?= $filter_from ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Family</span>
                    <input style="width:60%;" id="filter_category" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset No</span>
                    <input style="width:60%;" id="filter_number" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Estimate Economic</span>
                    <select style="width:60%;" id="filter_estimate" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose Estimate Economic</option>
                        <option value="1">1 Year</option>
                        <option value="2">2 Year</option>
                        <option value="3">3 Year</option>
                        <option value="4">4 Year</option>
                        <option value="5">5 Year</option>
                        <option value="6">6 Year</option>
                        <option value="7">7 Year</option>
                        <option value="8">8 Year</option>
                        <option value="9">9 Year</option>
                        <option value="10">10 Year</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Invoice No</span>
                    <input style="width:60%;" id="filter_purchase_invoice_number" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
    </div>
</div>

<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>Get data from Modul Purchase Invoicing and <b>Account Category = Fixed Asset</b></li>
                <li><b>Asset Type = Account Name</b> (Master Data > Accounting & Finance > Chart of Account)</li>
                <li><b>Asset Family = Product Family</b> (Master Data > Accounting & Finance > Item Family)</li>
            </ul>
        </div>
        <div title="CONDITIONS" style="padding: 20px;">
            <ul>
                <li><b>Depreciation = Asset Cost/Economic month</b> </li>
            </ul>
        </div>
    </div>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;  margin-bottom: 20px;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Invoice No</span>
                    <input style="width:60%;" name="purchase_invoice_number" id="purchase_invoice_number" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Name</span>
                    <input style="width:60%;" name="name" id="name" required="" class="easyui-combogrid">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Item RM ID</span>
                    <input style="width:60%;" name="item_rm_id" id="item_rm_id" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset No</span>
                    <input style="width:60%;" name="number" id="number" readonly class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Asset Family Code</span>
                    <input style="width:60%;" name="item_family_id" id="item_family_id" required="" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Family</span>
                    <input style="width:60%;" name="asset_family_name" id="asset_family_name" required="" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Date</span>
                    <input style="width:60%;" name="trans_date" id="trans_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="supplier_name" id="supplier_name" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty</span>
                    <input style="width:30%;" name="qty" id="qty" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Asset Cost</span>
                    <input style="width:30%;" name="cost" id="cost" readonly class="easyui-numberbox" data-options="groupSeparator:'.',decimalSeparator:','">
                    <input style="width:30%;" name="currency" id="currency" readonly class="easyui-textbox" data-options="prompt:'Curency'">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Estimated Economic</span>
                    <input style="width:30%;" name="estimate_year" id="estimate_year" required class="easyui-numberbox" data-options="prompt:'Year'">
                    <input style="width:30%;" name="estimate_month" id="estimate_month" required readonly class="easyui-numberbox" data-options="prompt:'Month'">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Expired Date</span>
                    <input style="width:60%;" name="expired_date" id="expired_date" class="easyui-datebox" required readonly data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Depreciation</span>
                    <input style="width:40%;" name="depreciation" id="depreciation" readonly required class="easyui-numberbox" data-options="groupSeparator:'.',decimalSeparator:','">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%; height: 80px;" name="remarks" id="remarks" class="easyui-textbox" multiline="true">
                </div>
            </div>
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>General Information</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Depreciation Method</span>
                        <input style="width:50%;" name="method" id="method" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Previous Department</span>
                        <input style="width:50%;" readonly name="previous_department" id="previous_department" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Previous Location</span>
                        <input style="width:50%;" readonly name="previous_location" id="previous_location" class="easyui-textbox">
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Current Department</span>
                        <input style="width:50%;" name="department" id="department" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:45%; display:inline-block;">Current Location</span>
                        <input style="width:50%;" name="location" id="location" class="easyui-textbox">
                    </div>
                </div>
            </fieldset>
            <div style="width: 30%; float: right;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Total Asset</span>
                    <input style="width:60%;" name="total" id="total" readonly class="easyui-numberbox" data-options="groupSeparator:'.',decimalSeparator:','">
                </div>
            </div>
        </fieldset>
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
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('finance/fixed_assets/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $("#dlg_insert").window('setTitle', "Add New Data");
        $('#purchase_invoice_number').combogrid('readonly', false);
        $('#name').combogrid('readonly', false);

        url_save = '<?= base_url('finance/fixed_assets/create') ?>';
        $('#frm_insert').form('clear');
       
        // Depreciation Method : auto fill = Straightline
        $('#method').textbox('setValue', 'Straightline');

    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $("#dlg_insert").window('setTitle', "Update Data");
            $('#frm_insert').form('load', row);

            $('#purchase_invoice_number').combogrid('readonly', true);
            $('#number').textbox('readonly', true);
            $('#name').combogrid('readonly', true);
            
            url_save = '<?= base_url('finance/fixed_assets/update') ?>?id=' + btoa(row.id);
        } else {
            // toastr.warning("Please select one of the data in the table first!", "Information");
            $.messager.alert('Information', "Please select one of the data in the table first!", 'warning');
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    Swal.fire({
                        title: 'Please Wait for Deleting Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        // $.ajax({
                        //     type: "post",
                        //     url: "<?= base_url('closing/locks/checkLock') ?>",
                        //     data: "period=" + row.trans_date + "&menus_id=<?= $menus_id ?>",
                        //     dataType: "json",
                        //     success: function (lock) {
                        //         if(lock.total > 0){
                        //             toastr.error("This period is not active by Accounting");
                        //             return false;
                        //         }

                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('finance/fixed_assets/delete') ?>',
                                    data: {
                                        id: row.id
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');

                                        if (i == rows.length) {
                                            Swal.close();
                                            $('#dg').datagrid('reload');
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error(jqXHR.statusText);
                                        $.messager.alert("Error", jqXHR.statusText, 'error');
                                    },
                                    complete: function(data) {
                                        //$('#dg').datagrid('reload');
                                    }
                                });
                        //     }
                        // });
                    }

                    $('#dg').datagrid('reload');
                }
            });
        } else {
            // toastr.warning("Please select one of the data in the table first!", "Information");
            $.messager.alert('Information', "Please select one of the data in the table first!", 'warning');
        }
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_number = $("#filter_number").combobox('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_estimate = $("#filter_estimate").combobox('getValue');
        var filter_purchase_invoice_number = $("#filter_purchase_invoice_number").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_number=" + window.btoa(filter_number) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_estimate=" + window.btoa(filter_estimate) +
            "&filter_purchase_invoice_number=" + window.btoa(filter_purchase_invoice_number) +
            "&filter_supplier=" + window.btoa(filter_supplier);

        $('#dg').datagrid({
            url: '<?= base_url('finance/fixed_assets/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/fixed_assets/print') ?>' + url);
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_asset_fixeds.xls') ?>');
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_number = $("#filter_number").combobox('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_estimate = $("#filter_estimate").combobox('getValue');
        var filter_purchase_invoice_number = $("#filter_purchase_invoice_number").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_number=" + window.btoa(filter_number) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_estimate=" + window.btoa(filter_estimate) +
            "&filter_purchase_invoice_number=" + window.btoa(filter_purchase_invoice_number) +
            "&filter_supplier=" + window.btoa(filter_supplier);

        window.location.assign('<?= base_url('finance/fixed_assets/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    // Windows Ready
    $(function() {
        // Load List of Fixed Assets
        $('#dg').datagrid({
            url: '<?= base_url('finance/fixed_assets/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true
        });

        //GET PURCHASE INVOICING
        $('#purchase_invoice_number').combogrid({
            url: '<?= base_url('finance/fixed_assets/readPi') ?>',
            panelWidth: 450,
            idField: 'number',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Purchase Invoice",
            columns: [
                [{
                    field: 'no',
                    title: 'No',
                    width: 30
                }, {
                    field: 'number',
                    title: 'Purchase Invoice',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Account Name',
                    width: 150
                }, ]
            ],
            onSelect: function(val, row) {
                // Setelah memilih purchase invoice, muat data produk/item yang terkait
                $('#name').combogrid({
                    url: '<?= base_url('finance/fixed_assets/readProductPi/') ?>' + window.btoa(row.number),
                    panelWidth: 400,
                    idField: 'item_no',
                    textField: 'item_no',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Asset",
                    columns: [
                        [{
                            field: 'item_no',
                            title: 'Asset No',
                            width: 200
                        }, {
                            field: 'item_name',
                            title: 'Asset Name',
                            width: 150
                        }, ]
                    ],
                    // Fungsi onSelect untuk item aset
                    onSelect: function(val2, row2) {
                        // Get Asset Family
                        $.ajax({
                            type: "post",
                            url: "<?= base_url('finance/fixed_assets/readAssetFamily/') ?>" + window.btoa(row2.item_rm_id),
                            dataType: "json",
                            success: function(assetCategory) {
                                $("#item_family_id").textbox('setValue', assetCategory.item_family_id);
                                $("#asset_family_name").textbox('setValue', assetCategory.family_name);
                                $("#estimate_year").textbox('setValue', assetCategory.asset_year);
                            }
                        });

                        // Get Asset No
                        $.ajax({
                            type: "post",
                            url: "<?= base_url('finance/fixed_assets/getAssetNo/') ?>" + window.btoa(row.number) + "/" + window.btoa(row2.item_rm_id),
                            dataType: "json",
                            success: function(assetNo) {
                                $("#number").textbox('setValue', assetNo);
                            }
                        });

                        // Get Department 
                        $.ajax({
                            type: "post",
                            url: "<?= base_url('finance/fixed_assets/readProductDepartment?number=') ?>" + window.btoa(row.number) + "&item=" + window.btoa(row2.item_rm_id),
                            dataType: "json",
                            success: function(productDep) {
                                $("#previous_department").textbox('setValue', productDep.department);
                                $("#department").textbox('setValue', productDep.department);
                            }
                        });

                        // Cek mata uang dan hitung total
                        if (row2.currency === 'IDR') {
                            $("#cost").numberbox('setValue', row2.price);
                            $("#total").numberbox('setValue', (parseFloat(row2.qty) * parseFloat(row2.price)));
                        } else {
                            // Jika bukan IDR, lakukan panggilan AJAX untuk mendapatkan kurs
                            $.ajax({
                                type: "post",
                                url: "<?= base_url('finance/fixed_assets/readExchangeRates') ?>",
                                data: "trans_date=" + row2.trans_date + "&currency=" + row2.currency,
                                dataType: "json",
                                success: function(exchange) 
                                {
                                    // Pastikan data exchange ada sebelum diakses
                                    if (exchange && exchange.length > 0 && exchange[0].middle) {
                                        const rate = parseFloat(exchange[0].middle);
                                        $("#cost").numberbox('setValue', parseFloat(row2.price * rate));
                                        $("#total").numberbox('setValue', (parseFloat(row2.qty) * parseFloat(row2.price * rate)));
                                    } else {
                                        // Fallback jika data kurs tidak ditemukan
                                        // $("#cost").numberbox('setValue', row2.price);
                                        // $("#total").numberbox('setValue', (parseFloat(row2.qty) * parseFloat(row2.price)));
                                        // $.messager.alert('Warning', 'Exchange rate not found. Using original price.', 'warning');

                                        $('#dlg_insert').dialog('close');                                       
                                        Swal.fire({
                                            title: "Exchange rate not found for the transaction date! Cannot save the Fixed Asset.",
                                            icon: "error",
                                            confirmButtonText: 'Ok',
                                            allowOutsideClick: false,
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                window.location.reload();
                                            }
                                        });
                                    }
                                },
                                error: function() {
                                    // $.messager.alert('Error', 'Failed to load exchange rates.', 'error');
                                    // $("#cost").numberbox('setValue', row2.price);
                                    // $("#total").numberbox('setValue', (parseFloat(row2.qty) * parseFloat(row2.price)));
                                    
                                    $('#dlg_insert').dialog('close');
                                    Swal.fire({
                                        title: "Failed to load exchange rates. Cannot save the Fixed Asset.",
                                        icon: "error",
                                        confirmButtonText: 'Ok',
                                        allowOutsideClick: false,
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.reload();
                                        }
                                    });
                                }
                            });
                        }

                        $("#name").textbox('setValue', row2.item_name);
                        $("#item_rm_id").textbox('setValue', row2.item_rm_id);
                        $("#trans_date").datebox('setValue', row2.trans_date);
                        $("#supplier_name").textbox('setValue', row2.supplier_name);
                        $("#qty").numberbox('setValue', row2.qty);
                        $("#currency").textbox('setValue', row2.currency); // Gunakan currency yang asli

                        $("#estimate_year").numberbox({
                            onChange: function(val) {
                                var cost = $("#cost").numberbox('getValue');
                                var trans_date = row2.trans_date;

                                if(trans_date != ""){
                                    $("#estimate_month").numberbox('setValue', (parseInt(val) * 12));
                                    $("#depreciation").numberbox('setValue', (cost / (parseInt(val) * 12)));

                                    $.ajax({
                                        type: "post",
                                        url: "<?= base_url('finance/fixed_assets/readExpired/') ?>" + (parseInt(val) * 12) + "/" + btoa(trans_date),
                                        dataType: "html",
                                        success: function (response) {
                                            $("#expired_date").datebox('setValue', response);
                                        }
                                    });
                                }else{
                                    toastr.error("Please Select Purchase Date First");
                                    $("#estimate_year").numberbox('clear');
                                }
                            }
                        });

                    }
                });
            }
        });

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    // Langsung memproses submit form tanpa cek Lock Accounting
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            // Hentikan submit jika validasi form easyUI gagal
                            if (!$(this).form('validate')) {
                                return false;
                            }
                            
                            $.messager.progress({
                                title: 'Please Wait',
                                msg: 'Saving data to database...'
                            });
                        },
                        success: function(result) {
                            $.messager.progress('close');
                            $('#dlg_insert').dialog('close');

                            try {
                                // Gunakan JSON.parse() untuk parsing yang lebih aman
                                const jsonResult = JSON.parse(result);

                                if (jsonResult.theme === "success") {
                                    toastr.success(jsonResult.message, jsonResult.title);
                                    
                                    $.messager.alert(jsonResult.title, jsonResult.message, 'info', function() {
                                        window.location.reload();
                                    });
                                } else {
                                    toastr.error(jsonResult.message, jsonResult.title);
                                    $.messager.alert(jsonResult.title, jsonResult.message, 'error');
                                }
                            } catch (e) {
                                toastr.error("Invalid JSON response from server.", "Error");
                                $.messager.alert('Error', "Invalid JSON response from server.", 'error');
                            }

                            $('#dg').datagrid('reload');
                        },
                        error: function() {
                            // Tutup loading progress dan tampilkan pesan error
                            $.messager.progress('close');
                            toastr.error("An error occurred while saving the data.", "Error");
                            $.messager.alert('Error', "An error occurred while saving the data.", 'error');
                        }
                    });
                }
            }]
        });

        $('#dlg_insert_backup').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var trans_date = $("#trans_date").datebox('getValue');

                    // --- CHECK LOCK SETTING OFF
                    // $.ajax({
                    //     type: "post",
                    //     url: "<?= base_url('closing/locks/checkLock') ?>",
                    //     data: "period=" + trans_date + "&menus_id=<?= $menus_id ?>",
                    //     dataType: "json",
                    //     success: function (lock) {
                    //         if(lock.total > 0){
                    //             toastr.error("This period is not active by Accounting");
                    //             return false;
                    //         }

                            $('#frm_insert').form('submit', {
                                url: url_save,
                                onSubmit: function() {
                                    if ($(this).form('validate') == true) {
                                        $('#dlg_insert').dialog('close');
                                        Swal.fire({
                                            title: 'Please Wait for Saving Data',
                                            showConfirmButton: false,
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            },
                                        });
                                    }
                                },
                                success: function(result) {
                                    Swal.close();
                                    var result = eval('(' + result + ')');
                                    
                                    if (result.theme == "success") {
                                        toastr.success(result.message, result.title);
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

                                    } else {
                                        toastr.error(result.message, result.title);
                                    }

                                    $('#dg').datagrid('reload');
                                }
                            });
                    // } }); // -- CHECK LOCK SETTING OFF

                }
            }]
        });

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('finance/fixed_assets/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('finance/fixed_assets/upload') ?>',
                        onSubmit: function() {
                            // Periksa validasi form secara langsung.
                            if (!$(this).form('validate')) {
                                return false;
                            }
                            $.messager.progress({
                                title: 'Please Wait',
                                msg: 'Importing Excel to Database'
                            });
                            // Kembalikan true untuk melanjutkan submit form.
                            return true;
                        },
                        success: function(result) {
                            $.messager.progress('close');

                            // Gunakan JSON.parse() untuk parsing yang lebih aman.
                            try {
                                const response = JSON.parse(result);
                                
                                // Periksa apakah respons memiliki format yang diharapkan
                                if (response.data && response.total !== undefined) {
                                    // Panggil fungsi proses data yang baru
                                    processUploadData(response.data);
                                } else {
                                    $.messager.alert('Error', 'Invalid data format from server.', 'error');
                                }
                            } catch (e) {
                                $.messager.alert('Error', 'Invalid JSON response from server.', 'error');
                            }
                        }
                    });
                }
            }]
        });
        // Fungsi terpisah untuk memproses data
        function processUploadData(dataToUpload) {
            const totalItems = dataToUpload.length;
            let successfulCount = 0;
            let failedCount = 0;

            const processItem = (index) => {
                if (index >= totalItems) {
                    // Semua item sudah diproses, tampilkan hasil akhir
                    const message = `Upload complete. Successful: ${successfulCount}, Failed: ${failedCount}`;
                    $.messager.alert('Info', message, 'info');
                    return;
                }

                const currentData = dataToUpload[index];
                const progressValue = Math.floor(((index + 1) / totalItems) * 100);

                // Perbarui progress bar dan status
                $('#p_upload').progressbar('setValue', progressValue);
                $('#p_start').html(index + 1);
                $('#p_finish').html(totalItems);

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('finance/fixed_assets/uploadCreate') ?>",
                    data: { "data": currentData },
                    dataType: "json",
                    success: function(result) {
                        let title;
                        if (result.theme === "success") {
                            successfulCount++;
                            $('#p_success').html(successfulCount);
                            title = `<b style='color: green;'>${result.title}</b> | ${result.message}`;
                        } else {
                            failedCount++;
                            $('#p_failed').html(failedCount);
                            title = `<b style='color: red;'>${result.title}</b> | ${result.message}`;
                            
                            // Simpan data gagal
                            $.ajax({
                                type: "POST",
                                url: "<?= base_url('finance/fixed_assets/uploadcreateFailed') ?>",
                                data: {
                                    data: currentData,
                                    message: result.message
                                },
                                cache: false
                            });
                        }
                        $("#p_remarks").append(title + "<br>");
                        // Panggil rekursi untuk item berikutnya
                        processItem(index + 1);
                    },
                    error: function() {
                        failedCount++;
                        $('#p_failed').html(failedCount);
                        const title = "<b style='color: red;'>Error</b> | Failed to process item.";
                        $("#p_remarks").append(title + "<br>");
                        // Lanjutkan ke item berikutnya meskipun gagal
                        processItem(index + 1);
                    }
                });
            };
            
            // Mulai proses
            processItem(0);
        }

        $('#dlg_upload_backup').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('finance/fixed_assets/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('finance/fixed_assets/upload') ?>',
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
                                url: "<?= base_url('finance/fixed_assets/uploadclearFailed') ?>"
                            });
                            var json = eval('(' + result + ')');
                            requestData(json.total, json);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);
                                    $.ajax({
                                        type: "POST",
                                        async: true,
                                        url: "<?= base_url('finance/fixed_assets/uploadCreate') ?>",
                                        data: {
                                            "data": json[number - 1]
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                //Json Failed
                                                $.ajax({
                                                    type: "POST",
                                                    async: true,
                                                    url: "<?= base_url('finance/fixed_assets/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });
                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }
                                            $("#p_remarks").append(title + "<br>");
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

        $("#filter_category").combobox({
            url: '<?= base_url('finance/fixed_assets/readAssetCategories') ?>',
            valueField: 'number',
            textField: 'name',
            prompt: "Choose Family",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(category) {
                $("#filter_number").combogrid({
                    url: '<?= base_url('finance/fixed_assets/readNumber/') ?>' + btoa(category.number),
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

        // Dropdown Asset No
        $("#filter_number").combogrid({
            url: '<?= base_url('finance/fixed_assets/readNumber') ?>',
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

        $("#filter_purchase_invoice_number").combobox({
            url: '<?= base_url('finance/fixed_assets/readPurchaseInvoiceNumber') ?>',
            valueField: 'purchase_invoice_number',
            textField: 'purchase_invoice_number',
            prompt: "Choose Purchase Invoice",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_supplier").combobox({
            url: '<?= base_url('finance/fixed_assets/readSupplier') ?>',
            valueField: 'supplier_name',
            textField: 'supplier_name',
            prompt: "Choose Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#estimate_year").numberbox({
            onChange: function(val) {
                var cost = $("#cost").numberbox('getValue');
                var trans_date = $("#trans_date").datebox('getValue');

                if(trans_date != ""){
                    $("#estimate_month").numberbox('setValue', (parseInt(val) * 12));
                    $("#depreciation").numberbox('setValue', (cost / (parseInt(val) * 12)));

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('finance/fixed_assets/readExpired/') ?>" + (parseInt(val) * 12) + "/" + btoa(trans_date),
                        dataType: "html",
                        success: function (response) {
                            $("#expired_date").datebox('setValue', response);
                        }
                    });
                }else{
                    toastr.error("Please Select Purchase Date First");
                    $("#estimate_year").numberbox('clear');
                }
            }
        });

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
            return "<b style='color:green;'>ACTIVE</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>EXPIRED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        }
    }

    function priceformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat("id-ID", {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }
</script>

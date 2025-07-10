<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'request_no',width:200,halign:'center'">Request No</th>
            <th rowspan="2" data-options="field:'status',width:120,align:'center',formatter:statusformat,styler:statusStyle">Status</th>
            <th rowspan="2" data-options="field:'request_date',width:100,halign:'center'">Request Date</th>
            <th rowspan="2" data-options="field:'expected_date',width:100,halign:'center'">Expected Date</th>
            <th rowspan="2" data-options="field:'request_name',width:150,halign:'center'">Request Name</th>
            <th rowspan="2" data-options="field:'division',width:150,halign:'center'">Division</th>
            <th rowspan="2" data-options="field:'department',width:150,halign:'center'">Department</th>
            <th rowspan="2" data-options="field:'sub_department',width:150,halign:'center'">Sub Department</th>
            <th rowspan="2" data-options="field:'item_number',width:170,halign:'center'">Part No</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'">Part Name</th>
            <th rowspan="2" data-options="field:'category_name',width:150,halign:'center'">Part Family</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right'">Total Qty</th>
            <th rowspan="2" data-options="field:'remarks',width:100,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'attachment',width:80,align:'center',formatter: btnDetails">Attachment</th>
            <th rowspan="2" data-options="field:'po_no',width:150,align:'left',halign:'center'">Po No</th>
            <th rowspan="2" data-options="field:'status_po',width:150,align:'left',halign:'center',formatter:statusformatpo,styler:statusStylepo">Status PO</th>
            <th rowspan="2" data-options="field:'approved_to',width:100,halign:'center',formatter:formatApproved,styler:styleApproved">Status <br>Approve</th>
            <th rowspan="2" data-options="field:'approved_by',width:100,halign:'center'">Approve By</th>
            <th rowspan="2" data-options="field:'approved_date',width:100,halign:'center'">Approve Date</th>
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

<div id="toolbar" style="height: 200px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:28%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request No</span>
                    <input style="width:60%;" id="filter_request_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="print_pr()"><i class="fa fa-print"></i> Purchase Request</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" id="filter_category_id" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_item_familys" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="0">UNCONVERTED</option>
                        <option value="1">CONVERTED</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1100px; height: 100%; padding:10px; left:5px; top: 0;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request No</span>
                    <input style="width:60%;" name="request_no" id="request_no" readonly class="easyui-textbox" data-options="prompt: 'Automatic'" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Date</span>
                    <input style="width:60%;" name="request_date" id="request_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Name</span>
                    <input style="width:60%;" name="request_name" id="request_name" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" name="division" id="division" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Department</span>
                    <input style="width:60%;" name="department" id="department" class="easyui-textbox" readonly>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sub Department</span>
                    <input style="width:60%;" name="sub_department" id="sub_department" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Expected Date</span>
                    <input style="width:60%;" name="expected_date" id="expected_date" value="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Category</span>
                    <input style="width:60%;" name="item_category_id" id="item_category_id" class="easyui-combobox" required>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Category Number</span>
                    <input style="width:60%;" name="category_number" id="category_number" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" name="item_family_id" id="item_family_id" class="easyui-combobox" required>
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Purchase Request List" toolbar="#toolbar2"></table>
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
<iframe id="printout" src="<?= base_url('purchase/purchase_requests/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        // $('#dlg_insert').dialog('open');
        $('#dlg_insert').dialog({
            onClose: function() {
                $("#frm_insert").form('clear');
                $("#item_category_id").combobox('enable');
            }
        }).dialog('open');
    
        $('#dg2').datagrid('loadData', []);
        $("#frm_insert").form('clear');

        $("#item_family_id").combobox('enable');
        $("#item_category_id").combobox('enable');
        $("#request_date").combobox('enable');
        $("#request_no").combobox('enable');
        $("#expected_date").combobox('enable');

        $("#item_category_id").combobox({
            url: '<?= base_url('master/item_categories/readsnotfg') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Categories",
            onSelect: function(category) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('purchase/purchase_requests/request_no/') ?>" + category.number,
                    dataType: "html",
                    success: function(result) {
                        $("#request_no").textbox('setValue', result);
                        $("#category_number").textbox('setValue', category.number);
                        setTimeout(function() {
                            $("#item_family_id").combobox('enable');
                        }, 500);
                        $("#item_category_id").combobox('disable');
                    }
                });

                $("#item_family_id").combobox({
                    url: '<?= base_url('master/item_familys/reads/') ?>' + category.id,
                    valueField: 'id',
                    textField: 'name',
                    multiple:true,
                    prompt: "Select Product Family",
                    onChange: function(row) {
                        var selectedRows = $("#item_family_id").combobox('getValues');

                        addTable(selectedRows); 
                    }
                });
            }
        });


        // $("#expected_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#request_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#request_name").textbox('setValue', "<?= $this->session->name ?>");

        $("#division").textbox('setValue', "<?= $this->session->division ?>");
        $("#department").textbox('setValue', "<?= $this->session->department ?>");
        $("#sub_department").textbox('setValue', "<?= $this->session->sub_department ?>");

        url_save= '<?= base_url('purchase/purchase_requests/create') ?>';
        methode= "add";
    }

    function addTable(item_family_id, link = "") {
        var dg = $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'id',
                    width: 150,
                    readonly: true,
                    hidden: true,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox'
                    }
                },{
                    field: 'item_number',
                    width: 250,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('master/item_rm/readItems?item_family_id=') ?>' + item_family_id,
                            required: true,
                            panelWidth: 650,
                            idField: 'item_number',
                            textField: 'item_number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product',
                            columns: [
                                [{
                                    field: 'item_number',
                                    title: 'Product No',
                                    width: 450
                                }, {
                                    field: 'item_name',
                                    title: 'Product Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, row) {
                                var dg = $('#dg2');
                                var allRows = dg.datagrid('getRows');
                                var isDuplicate = allRows.some(function(r) {
                                    return r.item_number === row.item_number;
                                });

                                if (isDuplicate) {
                                    toastr.warning('Item Has Been Add!');
                                    var rowIndex = dg.datagrid('getRowIndex', row);
                                    dg.datagrid('cancelEdit', rowIndex);
                                    return;
                                }

                                var rowIndex = dg.datagrid('getRowIndex', dg.datagrid('getSelected'));

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_id'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_name'
                                });

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'stock'
                                });

                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'po'
                                });

                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });

                                $(ed.target).textbox('setValue', row.id);
                                $(ed2.target).textbox('setValue', row.item_name);
                                $(ed5.target).textbox('setValue', row.uom);

                                $.ajax({
                                    type: "post",
                                    url: "<?= base_url('purchase/purchase_requests/readTotalPo') ?>",
                                    data: {
                                        item_rm_id: window.btoa(row.id),
                                        item_number: window.btoa(row.item_number),
                                    },
                                    dataType: "json",
                                    success: function(jsonpo) {
                                        if (jsonpo.length > 0) {
                                            // jika status outstanding open maka isi qty
                                            if (jsonpo[0].os_status == "OPEN") {
                                                $(ed4.target).numberbox('setValue', jsonpo[0].os_qty);
                                            } else {
                                                $(ed4.target).numberbox('setValue', 0);
                                            }
                                        } else {
                                            toastr.warning('Item tidak ada di Outstanding PO');
                                            $(ed4.target).numberbox('setValue', 0);
                                        }
                                    }
                                });

                            }
                        }
                    }
                }, {
                    field: 'item_name',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Product Name",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'item_rm_id',
                    hidden: true,
                    width: 100,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'qty',
                    width: 80,
                    halign: 'center',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'uom',
                    width: 80,
                    halign: 'center',
                    title: "Uom",
                    editor: {
                        type: 'textbox',
                    }
                }, {
                    field: 'stock',
                    width: 80,
                    halign: 'center',
                    title: "Stock",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'po',
                    width: 80,
                    halign: 'center',
                    title: "PO",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'remarks',
                    width: 200,
                    halign: 'center',
                    title: "Remarks",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'attachment_upload',
                    width: 200,
                    halign: 'center',
                    title: "Attachment",
                    editor:{
                        type:'filebox',
                        options:{
                            buttonText:'Browse File',
                            accept:'.jpg, .png, .pdf',
                            onChange: function(){
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'attachment'
                                });

                                var files = $(this).filebox('files');
                                var formData = new FormData();
                                for(var i=0; i<files.length; i++){
                                    var file = files[i];
                                    formData.append('file',file,file.name);
                                }
                                $.ajax({
                                    url: '<?= base_url('purchase/purchase_requests/uploadatt') ?>',
                                    type:'post',
                                    data: formData,
                                    contentType:false,
                                    processData:false,
                                    dataType: 'json',
                                    success:function(data){
                                        if(data.success == true){
                                            toastr.success(data.message);
                                            $(ed.target).textbox('setValue', data.filename);
                                        }else{
                                            toastr.error(data.message);
                                        }
                                    }
                                });
                            }
                        }
                    }
                }, {
                    field: 'attachment',
                    width: 200,
                    hidden: true,
                    halign: 'center',
                    title: "Attachment",
                    editor: {
                        type: 'textbox'
                    }
                }]
            ],
            onClickCell: onClickCell
        });
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
    }

    function append() {
        var item_family_id = $("#item_family_id").combobox('getValue');
        if (item_family_id != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: ''
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Product Family first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return true;
        }
        
        var dg = $('#dg2');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: editIndex,
            field: 'id'
        });

        var id = $(ed.target).textbox('getValue');

        if(id != ""){
            $.ajax({
                method: 'post',
                url: '<?= base_url('purchase/purchase_requests/delete') ?>',
                data: {
                    id: id
                },
                success: function(result) {
                    var result = eval('(' + result + ')');
                    toastr.success(result.message);
                },
                complete: function(data) {
                    $('#dg2').datagrid('reload');
                }
            });
        }

        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if (row.datatable == "1") {
                if (row.status == "0") {
                    $('#dlg_insert').dialog('open');
                    
                    // $("#item_family_id").combobox('disable');
                    $("#item_category_id").combobox('disable');
                    $("#request_date").combobox('disable');
                    $("#request_no").combobox('disable');
                    $("#expected_date").combobox('disable');
                    console.log(row);

                    $("#item_category_id").combobox({
                        url: '<?= base_url('master/item_categories/readsnotfg') ?>',
                        valueField: 'id',
                        textField: 'name',
                        prompt: "Select Categories",
                        onSelect: function(category) {
                            $("#category_number").textbox('setValue', category.number);
                            // $("#item_family_id").combobox({
                            //     url: '<?= base_url('master/item_familys/reads/') ?>' + category.id,
                            //     valueField: 'id',
                            //     textField: 'name',
                            //     multiple:true,
                            //     prompt: "Select Product Family",
                            //     onLoadSuccess: function(){
                            //         $("#item_family_id").combobox('setValue',row.item_family_id);
                                    
                            //     }
                            // });

                            $("#item_family_id").combobox({
                                url: '<?= base_url('master/item_familys/reads/') ?>' + category.id,
                                valueField: 'id',
                                textField: 'name',
                                multiple:true,
                                prompt: "Select Product Family",
                                onChange: function(row) {
                                    var selectedRows = $("#item_family_id").combobox('getValues');

                                    addTable(selectedRows); 
                                }
                            });
                        }
                    });

                    $('#frm_insert').form('load', row);

                    url_save= '<?= base_url('purchase/purchase_requests/create') ?>';
                    methode= "update";

                    var itemFamilyId = $("#item_family_id").combobox('getValue');

                    $('#department').textbox('setValue', row.department);
                    $('#sub_department').textbox('setValue', row.sub_department);


                    addTable(row.item_family_id, '<?= base_url('purchase/purchase_requests/datatable_updates?request_no=') ?>' + window.btoa(row.request_no));
                } else {
                    toastr.error("You cannot update this data, because status Purchase Request is CONVERTED");
                }
            } else {
                toastr.error("Please Select Header of PR <br>" + row.request_no);
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if (row.state == "closed") {
                            toastr.error("Please Select Detail of PR <br>" + row.id);
                        } else {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('purchase/purchase_requests/delete') ?>',
                                data: {
                                    id: row.id
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    toastr.error(jqXHR.statusText);
                                    $.messager.alert("Error", jqXHR.statusText, 'error');
                                },
                                complete: function(data) {
                                    $('#dg').treegrid('reload');
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

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_purchase_requests.xls') ?>');
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_item_familys = $("#filter_item_familys").combogrid('getValue');
        var filter_category_id = $("#filter_category_id").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_request_no=" + filter_request_no + "&filter_item_familys=" + filter_item_familys + "&filter_category_id=" + filter_category_id  + "&filter_status=" + filter_status;

        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_requests/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'request_no',
            singleSelect: false,
            fit: true,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('purchase/purchase_requests/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_item_familys = $("#filter_item_familys").combobox('getValue');
        var filter_category_id = $("#filter_category_id").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_request_no=" + filter_request_no + "&filter_item_familys=" + filter_item_familys + "&filter_category_id=" + filter_category_id  + "&filter_status=" + filter_status;
        window.location.assign('<?= base_url('purchase/purchase_requests/print/excel') ?>' + url);
    }

    function print_pr() {
        var request_no = $("#filter_request_no").combobox('getValue');
        if (request_no == "") {
            toastr.warning("Please select Request No!", "Information");
        } else {
            window.open("<?= base_url('purchase/purchase_requests/print_request/') ?>" + window.btoa(request_no), "_blank");
        }
    }

    function reload() {
        window.location.reload();
    }

    $("#filter_from").datebox({
        onChange: function(filter_from) {
            var filter_to = $("#filter_to").datebox('getValue');
            updateComboboxURL(filter_from, filter_to);
        }
    });

    $("#filter_to").datebox({
        onChange: function(filter_to) {
            var filter_from = $("#filter_from").datebox('getValue');
            updateComboboxURL(filter_from, filter_to);
        }
    });

    $("#filter_request_no").combobox({
            url: '<?= base_url('purchase/purchase_requests/readRequestnumbers/') ?>',
            valueField: 'request_no',
            textField: 'request_no',
            prompt: "Select Request No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

    function updateComboboxURL(filter_from, filter_to) {
        $("#filter_request_no").combobox({
            url: '<?= base_url('purchase/purchase_requests/readRequestno/') ?>' + btoa(filter_from) + '/' + btoa(filter_to),
            valueField: 'request_no',
            textField: 'request_no',
            prompt: "Select Request No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    }

    $(function() {
        filter();
        // $("#expected_date").datebox({
        //     onChange: function() {
        //         var request_date = $("#request_date").datebox('getValue');
        //         var expected_date = $("#expected_date").datebox('getValue');
        //         if (expected_date < request_date) {
        //             $("#expected_date").datebox('clear');
        //             toastr.warning("Request Date > Expected Date");
        //         }
        //     }
        // });
        // $("#request_date").datebox({
        //     onChange: function() {
        //         var request_date = $("#request_date").datebox('getValue');
        //         var expected_date = $("#expected_date").datebox('getValue');
        //         if (expected_date < request_date) {
        //             $("#request_date").datebox('clear');
        //             toastr.warning("Request Date < Expected Date");
        //         }
        //     }
        // });

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var categoryid = $("#item_category_id").combobox('getValue');
                    var prodfam = $("#item_family_id").combobox('getValue');

                    if (categoryid == "" || prodfam == "") {
                        toastr.warning("Please Choose Category and Product Family First!", "Information");
                    } else {
                        // // Fetch the latest request number from the server
                        var category = $("#category_number").textbox('getValue');
                        var request_no = $("#request_no").textbox('getValue');

                        $.ajax({
                            type: "get",
                            url: '<?= base_url('purchase/purchase_requests/request_no/')?>'+ category + "/" + btoa(request_no) + "/" + methode,
                            success: function(data) {               
                                console.log(data);
                                var request_no = data; // Use the response from the server as the new request number

                                var request_date = $("#request_date").datebox('getValue');
                                var request_name = $("#request_name").textbox('getValue');
                                var expected_date = $("#expected_date").datebox('getValue');
                                var division = $("#division").textbox('getValue');
                                var department = $("#department").textbox('getValue');
                                var sub_department = $("#sub_department").textbox('getValue');

                                $("#dg2").datagrid('acceptChanges');
                                var rows = $('#dg2').datagrid('getRows');
                                var totalrows = rows.length;
                                endEditing();


                                for (let i = 0; i < totalrows; i++) {
                                    if (rows[i].item_rm_id) {
                                        $.ajax({
                                            type: "post",
                                            url: url_save,
                                            data: {
                                                id: rows[i].id,
                                                item_rm_id: rows[i].item_rm_id,
                                                request_no: request_no,
                                                request_date: request_date,
                                                request_name: request_name,
                                                division: division,
                                                department: department,
                                                sub_department: sub_department,
                                                qty: rows[i].qty,
                                                expected_date: expected_date,
                                                remarks: rows[i].remarks
                                            },
                                            dataType: "json",
                                            success: function(result) {
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
                                        });
                                    }
                                }

                                $('#dg').treegrid('reload');
                                $('#dlg_insert').dialog('close');
                            }
                        });
                    }
                }
            }]
        });

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('purchase/purchase_requests/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('purchase/purchase_requests/upload') ?>',
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
                                url: "<?= base_url('purchase/purchase_requests/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('purchase/purchase_requests/uploadCreate') ?>",
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
                                                    url: "<?= base_url('purchase/purchase_requests/uploadcreateFailed') ?>",
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

        // $('#division').combobox({
        //     url: '<?= base_url('master/divisions/reads'); ?>',
        //     valueField: 'number',
        //     textField: 'number',
        //     panelHeight: 'panelHeight',
        //     prompt: 'Choose Division',
        // }); 

        $('#filter_item_familys').combogrid({
            url: '<?= base_url('master/item_familys/readNotFg/') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product Family",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Product Family ID',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Product Family Name',
                    width: 250
                }, ]
            ]
        });

        $("#filter_category_id").combobox({
            url: '<?= base_url('master/item_categories/readsnotfg') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Categories"
        });

        //Get Customer
        $("#filter_category_id").combobox({
            url: '<?= base_url('master/item_categories/readsnotfg') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Categories",
            onSelect: function(category) {
                $('#filter_item_familys').combogrid({
                    url: '<?= base_url('master/item_familys/reads/') ?>' + category.id,
                    panelWidth: 420,
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Product Family",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'number',
                            title: 'Product Family ID',
                            width: 120
                        }, {
                            field: 'name',
                            title: 'Product Family Name',
                            width: 250
                        }, ]
                    ]
                });
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

    function numberformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:red;'>UNCONVERTED</b>";
        } else if (value == 1) {
            return "<b style='color:green;'>CONVERTED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#FFC8C8;';
        } else if (value == 1) {
            return 'background-color:#C8FFCC;';
        }
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

    function statusformatpo(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        } else if (value == 2) {
            return "<b style='color:white;'>COMPLETE</b>";
        }
    }

    function statusStylepo(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else if (value == 2) {
            return 'background-color:#4B54E7;';
        }
    }

    function btnDetails(val, row, index) {
        var attachment = row.attachment;
        
        if (attachment != null && attachment != "") {
            return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('assets/image/purchase_requests/') ?>'+row.attachment+'" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
        } else {
            return '';
        }
    }
</script>
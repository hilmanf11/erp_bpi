<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'document_no',width:150,align:'left'">Document No</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,align:'left'">Customer</th>
            <th rowspan="2" data-options="field:'division',width:100,halign:'center'">Division</th>
            <th rowspan="2" data-options="field:'transaction_dates',width:200,halign:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'approved_to',width:100,halign:'center',formatter:formatApproved,styler:styleApproved">Status <br>Approve</th>
            <th rowspan="2" data-options="field:'approved_by',width:100,halign:'center'">Approve By</th>
            <th rowspan="2" data-options="field:'approved_date',width:150,halign:'center'">Approve Date</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:120,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:120,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 70%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" id="filter_document_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="print_note()"><i class="fa fa-print"></i> Print Note</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Box Name</span>
                    <input style="width:60%;" id="filter_box_name" class="easyui-combogrid">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Status Approval</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="approve">Approve</option>
                        <option value="checking">Checking</option>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Transaction Date</span>
                    <input style="width:60%;" name="transaction_date" id="transaction_date" class="easyui-datebox" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Box List</span>
                    <select style="width:60%;" id="box_list" class="easyui-combobox" required="" panelHeight="auto">
                        <option value="Customer">Customer</option>
                        <option value="All">All</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required="" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Division</span>
                    <input style="width:60%;" name="division" id="division" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Plant</span>
                    <input style="width:60%;" name="plant" id="plant" required="" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%; height: 410px;" title="Customer Item Lists" toolbar="#toolbar2"></table>
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
<iframe id="printout" src="<?= base_url('warehouse/dn_boxs/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('warehouse/dn_boxs/create') ?>';
        $('#frm_insert').form('clear');
        $('#transaction_date').datebox('setValue', '<?= date("Y-m-d") ?>');
    }

    function addTable(link = "", customer_id = "", plant = "", box_list = "") {
        var dg =  $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'item_box_name',
                    width: 200,
                    halign: 'center',
                    title: "Box Name",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('warehouse/dn_boxs/readItemBox/'); ?>'+ window.btoa(customer_id) + '/'+ window.btoa(plant) + '/'+ window.btoa(box_list),
                            required: true,
                            panelWidth: 600,
                            idField: 'name',
                            textField: 'name',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Box No',
                            columns: [
                                [{
                                    field: 'code',
                                    title: 'Box Code',
                                    width: 80
                                }, {
                                    field: 'name',
                                    title: 'Box Name',
                                    width: 200
                                }, {
                                    field: 'size',
                                    title: 'Box Size',
                                    width: 200
                                }, {
                                    field: 'color',
                                    title: 'Box Color',
                                    width: 80
                                }]
                            ],
                            onSelect: function(value, row) {
                                var dg = $('#dg2');
                                var allRows = dg.datagrid('getRows');
                                var isDuplicate = allRows.some(function(r) {
                                    return r.item_box_id === row.id;
                                });

                                if (isDuplicate) {
                                    toastr.warning('Box Has Been Add!');
                                    var rowIndex = dg.datagrid('getRowIndex', row);
                                    dg.datagrid('cancelEdit', rowIndex);
                                    return;
                                }

                                var rowIndex = dg.datagrid('getRowIndex', dg.datagrid('getSelected'));

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_box_id'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_box_code'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_box_size'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_box_color'
                                });

                                var item_box_id = $(ed.target).textbox('setValue', row.id);
                                var item_box_code = $(ed2.target).textbox('setValue', row.code);
                                var uom = $(ed3.target).textbox('setValue', row.uom);
                                var itam_box_size = $(ed4.target).textbox('setValue', row.size);
                                var item_box_color = $(ed5.target).textbox('setValue', row.color);
                            }
                        }
                    }
                }, {
                    field: 'item_box_id',
                    width: 150,
                    hidden: true,
                    halign: 'center',
                    title: "Part ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'item_box_code',
                    width: 80,
                    halign: 'center',
                    title: "Code",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'item_box_size',
                    width: 200,
                    halign: 'center',
                    title: "Size",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                 }, {
                    field: 'item_box_color',
                    width: 80,
                    halign: 'center',
                    title: "Color",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty',
                    width: 100,
                    align: 'center',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2
                        }
                    }
                }, {
                    field: 'uom',
                    width: 80,
                    align: 'center',
                    title: "Uom",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
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
        var customer_id = $("#customer_id").combogrid('getValue');
        if (customer_id != "") {
            if (endEditing()) {
                // Mendapatkan indeks baris pertama
                var firstRowIndex = $('#dg2').datagrid('getRows').length > 0 ? 0 : undefined;
                // Menyisipkan baris baru di indeks baris pertama
                $('#dg2').datagrid('insertRow', {
                    index: firstRowIndex,
                    row: {
                        qty: '0'
                    }
                });
                // Memulai edit pada baris baru
                $('#dg2').datagrid('selectRow', firstRowIndex).datagrid('beginEdit', firstRowIndex);
            }
        } else {
            toastr.error("Please Choose Customer first");
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
            field: 'item_box_id'
        });

        var customer_id = $("#customer_id").combogrid('getValue');
        var division = $("#division").combobox('getValue');
        var plant = $("#plant").combogrid('getValue');
        var item_box_id = $(ed.target).textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('warehouse/dn_boxs/delete') ?>',
            data: {
                customer_id: row.customer_id,
                division: division,
                plant: plant,
                item_box_id: item_box_id
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                toastr.success(result.message);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                toastr.error(jqXHR.statusText);
                $.messager.alert("Error", jqXHR.statusText, 'error');
            },
            complete: function(data) {
                $('#dg').datagrid('reload');
            }
        });

        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#item_box_id").combogrid('disable');

            // Set nilai customer_id lebih dulu
            $('#customer_id').combogrid('setValue', row.customer_id);

            // Load plant berdasarkan customer_id
            $('#plant').combogrid({
                url: '<?= base_url('warehouse/dn_boxs/readPlant/'); ?>' + window.btoa(row.customer_id) ,
                panelWidth: 420,
                idField: 'id',
                textField: 'plant',
                mode: 'remote',
                fitColumns: true,
                prompt: 'Choose Plant',
                columns: [
                    [{
                        field: 'plant',
                        title: 'Plant',
                        width: 120
                    }, {
                        field: 'address',
                        title: 'Address',
                        width: 300
                    }]
                ],
                onLoadSuccess: function(data) {
                    console.log("Loaded Data:", data); // Debugging apakah data masuk
                    console.log("Row Data:", row); // Debugging data row yang dipilih
                    console.log("Setting Value:", row.plant);
                    $('#plant').combogrid('setValue', row.plant);
                }
            });

            // Load tabel terkait
            addTable('<?= base_url('warehouse/dn_boxs/datatableUpdates?customer_id=') ?>' + window.btoa(row.customer_id) + "&division=" + window.btoa(row.division) + "&plant=" + window.btoa(row.plant));
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        console.log(rows);
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('warehouse/dn_boxs/delete') ?>',
                            data: {
                                document_no: row.document_no
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
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }
    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_customer_items.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_document_no = $("#filter_document_no").combobox('getValue');
        var filter_box_name = $("#filter_box_name").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_division=" + filter_division +
            "&filter_document_no=" + filter_document_no + "&filter_box_name=" + filter_box_name + "&filter_status=" + filter_status;

        $('#dg').datagrid({
            url: '<?= base_url('warehouse/dn_boxs/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/dn_boxs/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_document_no = $("#filter_document_no").combobox('getValue');
        var filter_box_name = $("#filter_box_name").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_division=" + filter_division +
            "&filter_document_no=" + filter_document_no + "&filter_box_name=" + filter_box_name + "&filter_status=" + filter_status;

        window.location.assign('<?= base_url('warehouse/dn_boxs/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //ADD DATA
        addTable();

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/dn_boxs/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            fitColumns: true,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.document_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_box_name = $("#filter_box_name").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('warehouse/dn_boxs/datatableDetails?number=') ?>' + window.btoa(row.document_no) + "&filter_box_name=" + window.btoa(filter_box_name),
                    singleSelect: true,
                    rownumbers: true,
                    fitColumns: true,
                    columns: [
                        [{
                            field: 'item_box_id',
                            title: 'Box ID',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_box_name',
                            title: 'Box Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_box_code',
                            title: 'Box Code',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_box_size',
                            title: 'Box Size',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_box_color',
                            title: 'Part Color',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'uom',
                            title: 'UOM',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'qty',
                            title: 'Qty',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberformat
                        }, {
                            field: 'remarks',
                            title: 'Remarks',
                            width: 200,
                            halign: 'center',
                        }]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var transaction_date = $("#transaction_date").datebox('getValue');
                    var customer_id      = $("#customer_id").combogrid('getValue');
                    var division         = $("#division").combobox('getValue');
                    var plant            = $("#plant").combogrid('getValue');

                    $("#dg2").datagrid('acceptChanges');
                    var rows = $('#dg2').datagrid('getRows');
                    endEditing();

                    if (rows.length === 0) {
                        Swal.fire({
                            title: "Tidak ada data detail!",
                            icon: "warning",
                            confirmButtonText: "Ok"
                        });
                        return;
                    }

                    // ambil hanya field yg perlu disimpan
                    var detailsToSave = rows.map(function(r) {
                        return {
                            item_box_id: r.item_box_id,
                            qty: r.qty,
                            remarks: r.remarks
                        };
                    });

                    var headerData = {
                        transaction_date: transaction_date,
                        customer_id: customer_id,
                        division: division,
                        plant: plant
                    };

                    $.ajax({
                        type: "POST",
                        url: "<?= base_url('warehouse/dn_boxs/create') ?>",
                        data: {
                            header: headerData,
                            details: JSON.stringify(detailsToSave)
                        },
                        dataType: "json",
                        success: function(result) {
                            $('#dlg_insert').dialog('close');
                            Swal.fire({
                                title: result.message,
                                icon: result.theme,
                                confirmButtonText: 'Ok',
                                allowOutsideClick: false,
                            }).then((res) => {
                                if (res.isConfirmed) {
                                    $('#dg').datagrid('reload');
                                    window.location.reload();
                                }
                            });
                        }
                    });
                }
            }]
        });
    });

    // Variabel global untuk simpan nilai pilihan
    var selected_box_list = '';
    var selected_customer_id = '';
    var selected_plant_id = '';

    $('#box_list').combobox({
        onSelect: function(rec) {
            selected_box_list = rec.value;

            if (selected_customer_id && selected_plant_id) {
                addTable("", selected_customer_id, selected_plant_id, selected_box_list);
            }
        }
    });

    $('#customer_id').combogrid({
        url: '<?= base_url('master/customers/reads/'); ?>',
        panelWidth: 400,
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
            }, ]
        ],
        onSelect: function(value, rows) {
            selected_customer_id = rows.id;

            if (!selected_box_list) {
                toastr.warning("Please select Box List first!");
                return;
            }

            $('#plant').combogrid({
                url: '<?= base_url('warehouse/dn_boxs/readPlant/'); ?>' + window.btoa(rows.id),
                panelWidth: 420,
                idField: 'plant',
                textField: 'plant',
                mode: 'remote',
                fitColumns: true,
                prompt: 'Choose Plant',
                columns: [
                    [{
                        field: 'plant',
                        title: 'Plant',
                        width: 120
                    }, {
                        field: 'address',
                        title: 'Address',
                        width: 300
                    },]
                ],
                onSelect: function(value, plantRow) {
                    selected_plant_id = plantRow.plant;
                    addTable("", selected_customer_id, selected_plant_id, selected_box_list);
                }
            });
        }
    });

    $('#filter_customer_id').combogrid({
        url: '<?= base_url('master/customers/reads'); ?>',
        panelWidth: 750,
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
            }, {
                field: 'type',
                title: 'Type',
                width: 100
            }, {
                field: 'currency',
                title: 'Currency',
                width: 100
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        onSelect: function(value, rows) {
            $('#filter_plant').combogrid({
                url: '<?= base_url('warehouse/dn_boxs/readPlant/'); ?>' + window.btoa(rows.id),
                panelWidth: 420,
                idField: 'id',
                textField: 'plant',
                mode: 'remote',
                fitColumns: true,
                prompt: 'Choose Plant',
                columns: [
                    [{
                        field: 'plant',
                        title: 'Plant',
                        width: 120
                    }, {
                        field: 'address',
                        title: 'Address',
                        width: 300
                    },]
                ]
            });
        }
    });

    $('#filter_box_name').combogrid({
        url: '<?= base_url('master/item_fg/reads'); ?>',
        panelWidth: 500,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No.",
        columns: [
            [{
                field: 'id',
                title: 'Product ID',
                width: 180
            }, {
                field: 'number',
                title: 'Product No.',
                width: 150
            }, {
                field: 'name',
                title: 'Product Name',
                width: 150
            }, {
                field: 'number_customer',
                title: 'Product Customer',
                width: 180
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $('#division').combobox({
        url: '<?= base_url('master/divisions/reads/'); ?>',
        valueField: 'number',
        textField: 'name',
        prompt: 'Choose Division'
    });

    $('#filter_division').combobox({
        url: '<?= base_url('master/divisions/reads/'); ?>',
        valueField: 'number',
        textField: 'name',
        prompt: 'Choose Division'
    });

    $('#filter_document_no').combobox({
        url: '<?= base_url('warehouse/dn_boxs/documentNo'); ?>',
        valueField: 'document_no',
        textField: 'document_no',
        panelHeight: 'panelHeight',
        prompt: 'Choose Document No',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_box_name').combogrid({
        url: '<?= base_url('warehouse/transaction_boxs/readItemBox/') ?>',
        panelWidth: 600,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Box Name",
        columns: [
            [{
                field: 'code',
                title: 'Box Code',
                width: 80
            }, {
                field: 'name',
                title: 'Box Name',
                width: 200
            }, {
                field: 'size',
                title: 'Box Size',
                width: 200
            }, {
                field: 'color',
                title: 'Box Color',
                width: 80
            }]
        ],
            icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 0) {
            return 'Active';
        } else {
            return 'Not Active';
        }
    };

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
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

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('warehouse/dn_boxs/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('warehouse/dn_boxs/upload') ?>',
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
                            url: "<?= base_url('warehouse/dn_boxs/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('warehouse/dn_boxs/uploadCreate') ?>",
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
                                                url: "<?= base_url('warehouse/dn_boxs/uploadcreateFailed') ?>",
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

     function print_note() {
        var document_no = $("#filter_document_no").combobox('getValue');
        console.log(document_no);
        if (document_no == "") {
            toastr.warning("Please select Document No First!", "Information");
        } else {
            $.ajax({
                type: "POST",
                url: "<?= base_url('warehouse/dn_boxs/checkNote') ?>",
                data: {
                    document_no: document_no
                },
                dataType: "json",
                success: function(response) {
                    console.log(response);
                    if (response == 'NO') {
                        toastr.warning("Note has not been approved", "Information");
                    } else {
                        window.open("<?= base_url('warehouse/dn_boxs/print_note/') ?>" + window.btoa(document_no), "_blank");
                    }
                },
                error: function() {
                    toastr.error("An error occurred while checking Memo for selected Memo No!", "Error");
                }
            });
        }
    }
</script>
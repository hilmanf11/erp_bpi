<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'request_no',halign:'center',width:190">Transaction No</th>
            <th rowspan="2" data-options="field:'request_date',width:120,halign:'center'">Transaction Date</th>
            <th rowspan="2" data-options="field:'request_name',width:120,halign:'center'">Requester</th>
            <th rowspan="2" data-options="field:'transaction_type',width:150,halign:'center',align:'center'">Type</th>
            <th rowspan="2" data-options="field:'item_box_name',width:150,halign:'center'">Box Name</th>
            <th rowspan="2" data-options="field:'item_box_code',width:100,halign:'center'">Box Code</th>
            <th rowspan="2" data-options="field:'item_box_size',width:150,halign:'center'">Size</th>
            <th rowspan="2" data-options="field:'item_box_color',width:80,halign:'center'">Color</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatQpa">Qty</th>
            <th rowspan="2" data-options="field:'remarks',width:100,align:'center'">Remarks</th>
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
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction No</span>
                    <input style="width:60%;" id="filter_request_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Box Name</span>
                    <input style="width:60%;" id="filter_box_name" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Date</span>
                    <input style="width:29%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:27%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Type</span>
                    <input style="width:60%;" id="filter_transaction_type" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <?= $button ?>

    </div>
</div>
<!-- TOOLBAR DATAGRID -->
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
                    <span style="width:35%; display:inline-block;">Request Date</span>
                    <input style="width:60%;" name="request_date" id="request_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request No</span>
                    <input style="width:60%;" name="request_no" id="request_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Name</span>
                    <input style="width:60%;" name="request_name" id="request_name" value="<?= $this->session->name ?>" readonly class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Type</span>
                    <input style="width:60%;" name="transaction_id" id="transaction_id" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction From</span>
                    <input style="width:60%;" name="transaction_from" id="transaction_from" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction To</span>
                    <input style="width:60%;" name="transaction_to" id="transaction_to" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Transaction ID</span>
                    <input style="width:60%;" name="transaction_type" id="transaction_type" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Transaction Boxs" toolbar="#toolbar2" data-options="singleSelect: true">
        </table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('warehouse/transaction_boxs/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        request_no();

        $("#request_date").datebox('enable');
        $("#request_no").textbox('enable');
        // $("#period").combobox({
        //     url: '<?= base_url('planning/production_schedules/readPeriodAll') ?>',
        //     valueField: 'period',
        //     textField: 'period',
        //     prompt: "Choose Period",
        //     onSelect: function(rowPeriod) {
        //         $("#workorder").combobox({
        //             url: '<?= base_url('planning/production_schedules/readWpAll?period=') ?>' + window.btoa(rowPeriod.period),
        //             valueField: 'wo_no',
        //             textField: 'wo_no',
        //             prompt: "Choose Workorder",
        //             onSelect: function(rowWP) {
        //                 $("#item_fg_id").textbox("setValue", rowWP.item_fg_id);
        //                 $("#item_fg_number").textbox("setValue", rowWP.item_fg_number);
        //             }
        //         });
        //     }
        // });
    }

    function request_no(reqDate = "") {
        if (reqDate == "") {
            var request_date = $("#request_date").datebox('getValue');
        } else {
            var request_date = reqDate;
        }
        $.ajax({
            type: "post",
            url: "<?= base_url('warehouse/transaction_boxs/request_no') ?>/" + window.btoa(request_date),
            dataType: "html",
            success: function(result) {
                $("#request_no").textbox('setValue', result);
            }
        });
    }
    //INSERT ADD ROW
    function addTable(url = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            url: url,
            columns: [
                [{
                    field: 'item_box_name',
                    width: 200,
                    halign: 'center',
                    title: "Box Name",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('warehouse/transaction_boxs/readItemBox') ?>',
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
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
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

                                var item_box_id = $(ed.target).textbox('setValue', rows.id);
                                var item_box_code = $(ed2.target).textbox('setValue', rows.code);
                                var uom = $(ed3.target).textbox('setValue', rows.uom);
                                var itam_box_size = $(ed4.target).textbox('setValue', rows.size);
                                var item_box_color = $(ed5.target).textbox('setValue', rows.color);
                            }
                        }
                    }
                }, {
                    field: 'item_box_id',
                    width: 150,
                    hidden: true,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
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
                    width: 80,
                    halign: 'center',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 4
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
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
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

    function append() {
        if (endEditing()) {
            $('#dg2').datagrid('appendRow', {
                qty: '0'
            });
            editIndex = $('#dg2').datagrid('getRows').length - 1;
            $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
        }
    }

    function removeit() {
        if (endEditing()) {
            var row = $('#dg2').datagrid('getSelected'); // Dapatkan baris yang dipilih
            if (row) {
                var rowIndex = $('#dg2').datagrid('getRowIndex', row); // Dapatkan index baris
                $('#dg2').datagrid('deleteRow', rowIndex); // Hapus baris yang dipilih
            }
            editIndex = undefined; // Reset editIndex
        }
    }


    //Update Data
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if(row.state == "closed"){
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                $("#request_date").datebox('disable');
                $("#request_no").textbox('disable');
                
                setTimeout(function() {
                    $("#request_no").textbox('setValue', row.request_no);
                }, 1000);
                
                $("#transaction_id").combobox({
                    url: '<?= base_url('warehouse/transaction_boxs/readType/') ?>',
                    valueField: 'id',
                    textField: 'trans_type',
                    prompt: "Choose Type",
                    onLoadSuccess: function(data){
                        if (data.length > 0) {
                            $("#transaction_id").combobox('setValue', row.transaction_id);
                        }
                    },
                    onSelect: function(record) {
                        $("#transaction_type").textbox('setValue', record.trans_type);
                    }
                });

                addTable('<?= base_url('warehouse/transaction_boxs/datatableUpdate/') ?>' + window.btoa(row.request_no));
            }else{
                toastr.warning("Please Select Header of Table", "Information");
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
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('warehouse/transaction_boxs/delete') ?>',
                            data: {
                                id: row.id,
                                request_no: row.request_no,
                                item_box_id: row.item_box_id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                                $('#dg').treegrid('reload');
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
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter() {
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_box_name = $("#filter_box_name").combogrid('getValue');
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_transaction_type = $("#filter_transaction_type").combobox('getValue');

        url = "?&filter_request_no=" + filter_request_no + "&filter_box_name=" + btoa(filter_box_name) + "&filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_transaction_type=" + filter_transaction_type;

        $('#dg').treegrid({
            url: '<?= base_url('warehouse/transaction_boxs/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/transaction_boxs/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_box_name = $("#filter_box_name").combogrid('getValue');
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_transaction_type = $("#filter_transaction_type").combobox('getValue');

        url = "?&filter_request_no=" + filter_request_no + "&filter_box_name=" + btoa(filter_box_name) + "&filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_transaction_type=" + filter_transaction_type;
        window.location.assign('<?= base_url('warehouse/transaction_boxs/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        addTable();
        $('#dg').treegrid({
            url: '<?= base_url('warehouse/transaction_boxs/datatables') ?>',
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'request_no',
            singleSelect: false,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
            // onClickRow: function(index) {
            //     if (index != 1) {
            //         $(this).datagrid('unselectRow', index).datagrid('selectRow', 1);
            //     }
            // }
            // rowStyler: function(row) {
            //     if (row.state != "closed") {
            //         return 'background-color:#CFE6FF;font-weight:bold;';
            //     }
            // },
        });

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var request_no = $("#request_no").textbox('getValue');
                    var request_date = $("#request_date").datebox('getValue');
                    var request_name = $("#request_name").textbox('getValue');
                    var transaction_type = $("#transaction_type").textbox('getValue');
                    var transaction_id = $("#transaction_id").combobox('getValue');
                    var transaction_from = $("#transaction_from").combobox('getValue');
                    var transaction_to = $("#transaction_to").combobox('getValue');
                    var remarks = $("#remarks").textbox('getValue');
                    
                    if (totalrows <= 0) {
                        toastr.error("please complete your input data");
                    } else {

                        $("#dg2").datagrid('acceptChanges');
                        var rows = $('#dg2').datagrid('getRows');
                        var totalrows = rows.length;
                        endEditing();

                        for (let i = 0; i < totalrows; i++) {
                            if (rows[i].item_box_id) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('warehouse/transaction_boxs/create') ?>',
                                    data: {
                                        request_date: request_date,
                                        request_no: request_no,
                                        request_name: request_name,
                                        transaction_type: transaction_type,
                                        transaction_id: transaction_id,
                                        transaction_from: transaction_from,
                                        transaction_to: transaction_to,
                                        remarks: remarks,
                                        item_box_id: rows[i].item_box_id,
                                        qty: rows[i].qty
                                    },
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "error") {
                                            toastr.warning(result.message, "Error");
                                        }
                                    }
                                });
                            }
                        }

                        Swal.fire({
                            title: "Data Saved Successfully",
                            icon: "success",
                            confirmButtonText: 'Ok',
                            allowOutsideClick: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                        $('#dg').treegrid('reload');
                        $('#dlg_insert').dialog('close');
                    }
                }
            }]
        });

        $("#filter_request_no").combobox({
            url: '<?= base_url('warehouse/transaction_boxs/readRequestNos/') ?>',
            valueField: 'request_no',
            textField: 'request_no',
            prompt: "Choose Request No",
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

        $("#request_date").datebox({
            onChange: function(val) {
                request_no(val);
            }
        });
    });

    // $("#transaction_id").combobox({
    //     url: '<?= base_url('warehouse/transaction_boxs/readType/') ?>',
    //     valueField: 'id',
    //     textField: 'trans_type',
    //     prompt: "Choose Type",
    //     onSelect: function(record){
    //         $("#transaction_type").textbox('setValue', record.trans_type);
    //         $("#transaction_from").combobox('setValue', record.trans_from);
    //         $("#transaction_to").combobox('setValue', record.trans_to);
    //     }
    // });

    $("#transaction_id").combobox({
        url: '<?= base_url('warehouse/transaction_boxs/readType/') ?>',
        valueField: 'id',
        textField: 'trans_type',
        prompt: "Choose Type",
        onSelect: function(record) {
            $("#transaction_type").textbox('setValue', record.trans_type);

            if (record.id === 'TB003' || record.id === 'TB004' || record.id === 'TB008' || record.id === 'TB011') {
                $('#transaction_from').combobox('enable');
                $('#transaction_to').combobox('setValue', record.trans_to);
                $('#transaction_from').combobox('clear');
                $('#transaction_to').combobox('disable');
            } else {
                $('#transaction_from').combobox('setValue', record.trans_from);
                $('#transaction_to').combobox('setValue', record.trans_to);
                $('#transaction_from').combobox('disable');
                $('#transaction_to').combobox('disable');
            }
        }
    });

    $("#transaction_from").combobox({
        url: '<?= base_url('warehouse/transaction_boxs/readCust/') ?>',
        valueField: 'name',
        textField: 'name',
        prompt: "Choose Customer",
    });

    $("#transaction_to").combobox({
        url: '<?= base_url('warehouse/transaction_boxs/readCust/') ?>',
        valueField: 'name',
        textField: 'name',
        prompt: "Choose Customer",
    });

    $("#filter_transaction_type").combobox({
        url: '<?= base_url('warehouse/transaction_boxs/readType/') ?>',
        valueField: 'trans_type',
        textField: 'trans_type',
        prompt: "Choose Type",
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
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatQpa(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
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

    function BtnPrintLabel(val, row) {
        return '<a style="text-decoration: none; font-weight:bold;" target="_blank" href="<?= base_url('warehouse/transaction_boxs/print_label/') ?>' + window.btoa(row.id) + '"><i class="fa fa-print"></i> Print</a>';
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

    function statusformatFinance(value, row) {
        if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        } else {
            return "<b style='color:green;'>OPEN</b>";
        }
    }

    function statusStyleFinance(value, row, index) {
        if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }
</script>
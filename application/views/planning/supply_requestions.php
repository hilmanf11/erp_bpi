<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'request_no',halign:'center',width:190">Kanban No</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status</th>
            <th rowspan="2" data-options="field:'request_date',width:120,halign:'center'">Kanban Date</th>
            <th rowspan="2" data-options="field:'request_name',width:120,halign:'center'">Requester</th>
            <th rowspan="2" data-options="field:'period',width:100,halign:'center'">Period</th>
            <th rowspan="2" data-options="field:'workorder',width:120,halign:'center'">Work Order</th>
            <th rowspan="2" data-options="field:'document',width:120,halign:'center'">Document</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatQpa">Qty</th>
            <th rowspan="2" data-options="field:'issued',width:80,halign:'center',align:'right',formatter:numberformatQpa">Issued</th>
            <th rowspan="2" data-options="field:'outstanding',width:80,halign:'center',align:'right',formatter:numberformatQpa">Outstanding</th>
            <!-- <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status</th> -->
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
        <fieldset style="width: 90%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width: 30%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" id="filter_period" value="<?= date("Ym") ?>" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Workorder</span>
                    <input style="width:60%;" id="filter_workorder" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="float: left; width: 30%;">
            <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request No</span>
                    <input style="width:60%;" id="filter_request_no" class="easyui-combobox">
                </div>
            </div>
            <div style="float: left; width: 30%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">Select All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="print_kanban()"><i class="fa fa-print"></i> Print Kanban</a>
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

                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>

            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" id="period" required="" class="easyui-combobox">
                </div>

                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">WP</span>
                    <input style="width:60%;" id="wp" required="" class="easyui-combobox">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Type</span>
                    <select style="width:60%;" id="type" name="type" class="easyui-combobox" panelHeight="auto" required>
                        <option value="">Choose Type</option>
                        <option value="SCP">SCRAP</option>
                        <option value="PRG">PURGING</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Workorder</span>
                    <input style="width:60%;" name="workorder" id="workorder" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Scarp No</span>
                    <input style="width:60%;" name="document" id="document" class="easyui-combobox">
                </div>
            </div>
        </fieldset>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Material Requestion" idField="item_number"><!-- OK -->
            <thead>
                <tr>
                    <th field="ck" checkbox="true"></th>
                    <th data-options="field:'action',width:120,formatter:buttonEdit">Action</th>
                    <th hidden data-options="field:'id',width:100">ID</th>
                    <th data-options="field:'item_rm_id',width:150">Product id</th>
                    <th data-options="field:'number',width:150">Product No</th>
                    <th data-options="field:'name',width:100">Product Name</th>
                    <th data-options="field:'qty',width:100,editor: {type: 'numberbox', options: {required: true}}">Qty</th>
                    <!-- <th data-options="field:'balance',width:100,formatter:balanceFormatter">Balance</th> -->
                    <th data-options="field:'balance',width:100">Balance Wip</th>
                    <th data-options="field:'warehouse',width:100">Warehouse</th>
                    <th data-options="field:'uom',width:80">Uom</th>
                    <th data-options="field:'description',width:150,editor: {type: 'textbox', options: {required: true}}">Description</th>
                </tr>
            </thead>
        </table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/supply_requestions/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        request_no();
        $("#period").combobox({
            url: '<?= base_url('planning/production_schedules/readPeriodAll') ?>',
            valueField: 'period',
            textField: 'period',
            prompt: "Choose Period",
            onSelect: function(rowPeriod) {
                $("#type").combobox({
                    onChange: function(type){
                        $("#workorder").combobox({
                            url: "<?= base_url('planning/supply_requestions/readWorkorders/') ?>" + rowPeriod.period + "/" + type,
                            valueField: 'wo_no',
                            textField: 'wo_no',
                            prompt: 'Choose Workorder',
                            onSelect: function(row) {
                                $("#document").combobox({
                                    url: "<?= base_url('planning/supply_requestions/readScrapNo/') ?>" + btoa(row.wo_no),
                                    valueField: 'document',
                                    textField: 'document',
                                    prompt: 'Choose Document',
                                    // onSelect: function(row) {
                                        
                                    // }
                                });
                            }
                        });
                    }
                });
            }
        });

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

    function request_no(reqDate = "") {
        if (reqDate == "") {
            var request_date = $("#request_date").datebox('getValue');
        } else {
            var request_date = reqDate;
        }
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/supply_requestions/request_no') ?>/" + window.btoa(request_date),
            dataType: "html",
            success: function(result) {
                $("#request_no").textbox('setValue', result);
            }
        });
    }
    
    function preview() {
        var workorder = $("#workorder").combobox('getValue');
        var type = $("#type").combobox('getValue');
        var document = $("#document").combobox('getValue');
        console.log(workorder);
        console.log(type);

        if (workorder == "") {
            toastr.info('Please completed your data');
        } else {
            var lastIndex;
            if (workorder != "") {
                if (type === "SCP") {
                    var dg = $('#dg2').datagrid({
                        url: '<?= base_url('planning/supply_requestions/datatablesTemp') ?>?workorder=' + window.btoa(workorder) + '&type=' + window.btoa(type) + '&document=' + window.btoa(document),
                    });
                } else {
                    var dg = $('#dg2').datagrid({
                        url: '<?= base_url('planning/supply_requestions/datatablesTemp') ?>?workorder=' + window.btoa(workorder) + '&type=' + window.btoa(type),
                    });
                }
            } else {
                toastr.info('Please completed your data');
            }
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

    function append() {
        if (endEditing()) {
            $('#dg2').datagrid('appendRow', {
                "action": 0
            });
            editIndex = $('#dg2').datagrid('getRows').length - 1;
            $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);

            var dg = $('#dg2');
            var row = dg.datagrid('getSelected');
            var rowIndex = dg.datagrid('getRowIndex', row);
        }
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function editrow(target) {
        $('#dg2').datagrid('selectRow', getRowIndex(target));
        $('#dg2').datagrid('beginEdit', getRowIndex(target));
    }

    function deleterow(target) {
        $.messager.confirm('Confirm', 'Are you sure?', function(r) {
            if (r) {
                var dg = $('#dg2');
                var row = dg.datagrid('getRows');
                var rowIndex = dg.datagrid('getRowIndex', row);

                var ed = dg.datagrid('getEditor', {
                    index: editIndex,
                    field: 'id'
                });

                $.ajax({
                    method: 'post',
                    url: '<?= base_url('warehouse/wip_receipts/deleteSingle') ?>',
                    data: {
                        id: row.id,
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

                $('#dg2').datagrid('deleteRow', getRowIndex(target));
            }
        });
    }

    function saverow(target) {
        $('#dg2').datagrid('endEdit', getRowIndex(target));
    }

    function cancelrow(target) {
        $('#dg2').datagrid('cancelEdit', getRowIndex(target));
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        console.log(row);
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('planning/supply_requestions/delete') ?>',
                            data: {
                                id: row.id,
                                request_no: row.request_no,
                                document: row.document,
                                item_rm_id: row.item_rm_id
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
        var filter_period = $("#filter_period").combobox('getValue');
        var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_period=" + filter_period + "&filter_workorder=" + filter_workorder + "&filter_request_no=" + filter_request_no + "&filter_status=" + filter_status;

        $('#dg').treegrid({
            url: '<?= base_url('planning/supply_requestions/datatables') ?>' + url,
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
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/supply_requestions/print') ?>' + url);

    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_period = $("#filter_period").combobox('getValue');
        var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        url = "?filter_period=" + filter_period + "&filter_workorder=" + filter_workorder + "&filter_request_no=" + filter_request_no + "&filter_status=" + filter_status;
        window.location.assign('<?= base_url('planning/supply_requestions/print/excel') ?>' + url);
    }

    function print_kanban() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            window.open("<?= base_url('planning/supply_requestions/print_kanban/') ?>" + window.btoa(row.request_no));// + "/" + window.btoa(operation), "_blank"
        }else{
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        filter();

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var request_no = $("#request_no").textbox('getValue');
                    var request_date = $("#request_date").datebox('getValue');
                    var request_name = $("#request_name").textbox('getValue');
                    var period = $("#period").combobox('getValue');
                    var type = $("#type").combobox('getValue');
                    var workorder = $("#workorder").combobox('getValue');
                    var document = $("#document").combobox('getValue');

                    if (period == "" || totalrows <= 0) {
                        toastr.error("please complete your input data");
                    } else {
                        $('#dg2').datagrid('acceptChanges');
                        var rows = $('#dg2').datagrid('getRows');
                        var totalrows = rows.length;
                        endEditing();

                        for (let i = 0; i < totalrows; i++) {
                            if (rows[i].item_rm_id) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('planning/supply_requestions/create') ?>',
                                    data: {
                                        request_date: request_date,
                                        request_no: request_no,
                                        request_name: request_name,
                                        period: period,
                                        type: type,
                                        workorder: workorder,
                                        document: document,
                                        item_rm_id: rows[i].item_rm_id,
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

        $("#filter_period").combobox({
            url: '<?= base_url('planning/supply_requestions/readPeriod') ?>',
            valueField: 'period',
            textField: 'period',
            prompt: "Choose period",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(period) {
                $("#filter_workorder").combobox({
                    url: '<?= base_url('planning/supply_requestions/readWo/') ?>' + period.period,
                    valueField: 'workorder',
                    textField: 'workorder',
                    prompt: "Choose Workorder",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(wo) {
                        $("#filter_request_no").combobox({
                            url: '<?= base_url('planning/supply_requestions/readRequestNo/') ?>' + period.period + '/' + window.btoa(wo.workorder),
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
                    }
                });
            }
        });
        $("#request_date").datebox({
            onChange: function(val) {
                request_no(val);
            }
        });
    });

    $('#dg2').datagrid({
        onLoadSuccess: function(data) {
            // Loop untuk setiap baris dalam DataGrid
            for (var i = 0; i < data.rows.length; i++) {
                (function(i) {
                    var row = data.rows[i];

                    // Cek stok warehouse
                    $.ajax({
                        type: "post",
                        url: "<?= base_url('warehouse/report_history_transactions/readEndingStock') ?>",
                        data: { item_rm_id: row.item_rm_id },
                        dataType: "json",
                        success: function(stockWarehouse) {
                            $('#dg2').datagrid('updateRow', {
                                index: i,
                                row: {
                                    warehouse: stockWarehouse[0].end_stock // Update kolom warehouse
                                }
                            });
                        },
                        error: function() {
                            console.log("Gagal mendapatkan stok warehouse untuk item_rm_id " + row.item_rm_id);
                        }
                    });

                    // Cek balance WIP
                    $.ajax({
                        type: "post",
                        url: "<?= base_url('warehouse/report_history_transactions/readBalanceWip') ?>",
                        data: { item_rm_id: row.item_rm_id },
                        dataType: "json",
                        success: function(balanceWip) {
                            $('#dg2').datagrid('updateRow', {
                                index: i,
                                row: {
                                    balance: balanceWip.balance // Update kolom balance
                                }
                            });
                        },
                        error: function() {
                            console.log("Gagal mendapatkan balance WIP untuk item_rm_id " + row.item_rm_id);
                        }
                    });

                })(i);
            }
        }
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
        if (value !== null && value !== undefined) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
        return "<b>0.00</b>"; // Atur agar 0 tetap ditampilkan dengan format yang sama
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
        return '<a style="text-decoration: none; font-weight:bold;" target="_blank" href="<?= base_url('planning/supply_requestions/print_label/') ?>' + window.btoa(row.id) + '"><i class="fa fa-print"></i> Print</a>';
    }

    function buttonEdit(value, row, index) {
        if (row.editing) {
            var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a> ';
            var c = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="cancelrow(this)">Cancel</a>';
            return s + c;
        } else {
            var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a> ';
            var d = '<a href="javascript:void(0)" class="btn btn-danger btn-sm" style="pointer-events:auto; opacity:1;" onclick="deleterow(this)">Delete</a>';
            return e + d;
        }
    }

</script>
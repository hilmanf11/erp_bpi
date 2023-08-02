<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'request_no',halign:'center',width:160" sortable="true">Supply No</th>
            <th rowspan="2" data-options="field:'request_date',width:120,halign:'center'" sortable="true">Supply Date</th>
            <th rowspan="2" data-options="field:'request_name',width:120,halign:'center'" sortable="true">Requester</th>
            <th rowspan="2" data-options="field:'period',width:100,halign:'center'" sortable="true">Period</th>
            <th rowspan="2" data-options="field:'wp',width:50,halign:'center'" sortable="true">WP</th>
            <th rowspan="2" data-options="field:'workorder',width:150,halign:'center'" sortable="true">Work Order</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'" sortable="true">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'" sortable="true">Product Name</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'" sortable="true">UoM</th>
            <th rowspan="2" data-options="field:'qpa',width:80,halign:'center',align:'right',formatter:numberformatQpa" sortable="true">QPA</th>
            <th rowspan="2" data-options="field:'mpq',width:80,halign:'center',align:'right',formatter:numberformatQpa" sortable="true">MPQ</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatQpa" sortable="true">WO Qty</th>
            <th rowspan="2" data-options="field:'qty_req',width:80,halign:'center',align:'right',formatter:numberformatQpa" sortable="true">Req Qty</th>
            <th rowspan="2" data-options="field:'qty_act',width:80,halign:'center',align:'right',formatter:numberformatQpa" sortable="true">Actual Qty</th>
            <th rowspan="2" data-options="field:'qty_issued',width:80,halign:'center',align:'right',formatter:numberformatQpa" sortable="true">Issued</th>
            <th rowspan="2" data-options="field:'qty_issued_bal',width:80,halign:'center',align:'right',formatter:numberformatQpa" sortable="true">O/S Qty</th>
            <th rowspan="2" data-options="field:'supply_type',width:80,align:'center',formatter:issuedformat,styler:statusIssued" sortable="true">Supply<br>Type</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'" sortable="true"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'" sortable="true"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'" sortable="true"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'" sortable="true"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 190px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supply Type</span>
                    <input style="width:60%;" id="filter_supply_type" class="easyui-combobox" panelHeight="auto">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" id="filter_period" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="print_kanban()"><i class="fa fa-print"></i> Print Supply Sheet</a>
                </div>
            </div>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">WP</span>
                    <input style="width:60%;" id="filter_wp" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supply No</span>
                    <input style="width:60%;" id="filter_request_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Operation</span>
                    <input style="width:60%;" id="filter_operation" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>
<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1300px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supply Date</span>
                    <input style="width:60%;" name="request_date" id="request_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supply No</span>
                    <input style="width:60%;" name="request_no" id="request_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Name</span>
                    <input style="width:60%;" name="request_name" id="request_name" value="<?= $this->session->name ?>" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" id="period" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">WP</span>
                    <input style="width:60%;" id="wp" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Work Order ID</span>
                    <input style="width:60%;" name="workorder" id="workorder" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Order ID</span>
                    <input style="width:60%;" name="so_number" id="so_number" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_id" id="item_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Operation</span>
                    <input style="width:60%;" id="operation" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Supply Sheet List" data-options="rownumbers: true, singleSelect: false" idField="component_number">

        </table>
    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/supply_sheets/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg_request').datagrid('loadData', []);
        request_no();
        $("#period").combobox({
            url: '<?= base_url('planning/production_schedules/readPeriod') ?>',
            valueField: 'period',
            textField: 'period',
            prompt: "Select Period",
            onSelect: function(rowPeriod) {
                $("#wp").combobox({
                    url: '<?= base_url('planning/production_schedules/readWp?period=') ?>' + window.btoa(rowPeriod.period),
                    valueField: 'wp',
                    textField: 'wp',
                    prompt: "Select WP",
                    onSelect: function(rowWP) {
                        $("#workorder").combobox({
                            url: '<?= base_url('planning/production_schedules/readWorkorder?period=') ?>' + window.btoa(rowPeriod.period) + '&wp=' + window.btoa(rowWP.wp),
                            valueField: 'workorder',
                            textField: 'workorder',
                            prompt: "Select Workorder",
                            onSelect: function(rowWorkorder) {
                                $("#so_number").textbox('setValue', rowWorkorder.so_number);
                                $("#customer_id").combogrid({
                                    url: '<?= base_url('planning/production_schedules/readCustomer?wp=') ?>' + window.btoa(rowWP.wp) + "&period=" + window.btoa(rowPeriod.period) + "&workorder=" + window.btoa(rowWorkorder.workorder),
                                    panelWidth: 420,
                                    idField: 'customer_id',
                                    textField: 'customer_name',
                                    mode: 'remote',
                                    fitColumns: true,
                                    prompt: "Select Customer No",
                                    columns: [
                                        [{
                                            field: 'customer_number',
                                            title: 'Customer No',
                                            width: 120
                                        }, {
                                            field: 'customer_name',
                                            title: 'Customer Name',
                                            width: 250
                                        }, ]
                                    ],
                                    onSelect: function(val, rowCust) {
                                        $("#item_id").combogrid({
                                            url: '<?= base_url('planning/production_schedules/readItems?wp=') ?>' + window.btoa(rowWP.wp) + "&period=" + window.btoa(rowPeriod.period) + "&customer_id=" + rowCust.customer_id + "&workorder=" + window.btoa(rowWorkorder.workorder),
                                            panelWidth: 420,
                                            idField: 'item_id',
                                            textField: 'item_number',
                                            mode: 'remote',
                                            fitColumns: true,
                                            prompt: "Select Product No",
                                            columns: [
                                                [{
                                                    field: 'item_number',
                                                    title: 'Product No',
                                                    width: 120
                                                }, {
                                                    field: 'item_name',
                                                    title: 'Product Name',
                                                    width: 250
                                                }, ]
                                            ]
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            }
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
            url: "<?= base_url('planning/supply_sheets/request_no') ?>/" + window.btoa(request_date),
            dataType: "html",
            success: function(result) {
                $("#request_no").textbox('setValue', result);
            }
        });
    }

    function preview() {
        var item_id = $("#item_id").combogrid('getValue');
        var workorder = $("#workorder").textbox('getValue');
        var operation = $("#operation").combobox('getValue');
        if (workorder == "" || item_id == "") {
            toastr.warning('Please select Product No', 'Required');
        } else {
            var lastIndex;
            var dg = $('#dg_request').datagrid({
                url: '<?= base_url('planning/supply_sheets/datatablesTemp') ?>?workorder=' + workorder + '&operation=' + operation,
                singleSelect: true,
                idField: 'component_id',
                columns: [
                    [{
                        field: 'component_id',
                        width: 250,
                        hidden: true,
                        halign: 'center',
                        title: "Component ID",
                        editor: {
                            type: 'textbox',
                            options: {
                                readonly: true
                            }
                        }
                    }, {
                        field: 'component_number',
                        width: 150,
                        halign: 'center',
                        title: "Component No",
                    }, {
                        field: 'component_name',
                        width: 150,
                        halign: 'center',
                        title: "Component Name",
                    }, {
                        field: 'operation',
                        width: 80,
                        halign: 'center',
                        title: "Operation",
                    }, {
                        field: 'qpa',
                        width: 80,
                        halign: 'center',
                        title: "QPA",
                    }, {
                        field: 'mpq',
                        width: 150,
                        halign: 'center',
                        title: "MPQ",
                        editor: {
                            type: 'combogrid',
                            options: {
                                required: true
                            }
                        }
                    }, {
                        field: 'qty',
                        width: 80,
                        halign: 'center',
                        title: "WO Qty",
                    }, {
                        field: 'qty_req',
                        width: 80,
                        halign: 'center',
                        title: "Qty",
                    }, {
                        field: 'qty_act',
                        width: 100,
                        halign: 'center',
                        title: "Req Qty",
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
                    }, {
                        field: 'action',
                        title: 'Action',
                        width: 120,
                        align: 'center',
                        formatter: function(value, row, index) {
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
                    }]
                ],
                onBeginEdit: function(rowIndex, row) {
                    var editors = $('#dg_request').datagrid('getEditors', rowIndex);

                    var item_id = $(editors[0].target).textbox('getValue');
                    $(editors[1].target).combogrid({
                        url: '<?= base_url('planning/supply_sheets/readMPQ/') ?>' + btoa(item_id),
                        panelWidth: 420,
                        idField: 'mpq',
                        textField: 'mpq',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: "Select MPQ",
                        columns: [
                            [{
                                field: 'name',
                                title: 'Supplier Name',
                                width: 250
                            }, {
                                field: 'mpq',
                                title: 'MPQ',
                                width: 100
                            }, ]
                        ],
                    });
                },
                onEndEdit: function(index, row) {
                    var ed = $(this).datagrid('getEditor', {
                        index: index,
                        field: 'component_id'
                    });
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
        }
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function editrow(target) {
        $('#dg_request').datagrid('beginEdit', getRowIndex(target));
    }

    function deleterow(target) {
        $.messager.confirm('Confirm', 'Are you sure?', function(r) {
            if (r) {
                $('#dg_request').datagrid('deleteRow', getRowIndex(target));
            }
        });
    }

    function saverow(target) {
        $('#dg_request').datagrid('endEdit', getRowIndex(target));
    }

    function cancelrow(target) {
        $('#dg_request').datagrid('cancelEdit', getRowIndex(target));
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
                            url: '<?= base_url('planning/supply_sheets/delete') ?>',
                            data: {
                                id: row.id,
                                workorder: row.workorder,
                                item_id: row.item_id,
                                request_no: row.request_no,
                                component_id: row.component_id
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
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter() {
        var filter_supply_type = $("#filter_supply_type").combobox('getValue');
        var filter_period = $("#filter_period").combobox('getValue');
        var filter_wp = $("#filter_wp").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_operation = $("#filter_operation").combobox('getValue');
        url = "?filter_period=" + filter_period + "&filter_wp=" + filter_wp + "&filter_request_no=" + filter_request_no + "&filter_operation=" + filter_operation + "&filter_supply_type=" + filter_supply_type;
        $('#dg').treegrid({
            url: '<?= base_url('planning/supply_sheets/datatables') ?>' + url,
            rownumbers: true,
            idField: 'id',
            treeField: 'request_no',
            singleSelect: false,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/supply_sheets/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_supply_type = $("#filter_supply_type").combobox('getValue');
        var filter_period = $("#filter_period").combobox('getValue');
        var filter_wp = $("#filter_wp").combobox('getValue');
        var filter_operation = $("#filter_operation").combobox('getValue');
        url = "?filter_period=" + filter_period + "&filter_wp=" + filter_wp + "&filter_request_no=" + filter_request_no + "&filter_operation=" + filter_operation + "&filter_supply_type=" + filter_supply_type;
        window.location.assign('<?= base_url('planning/supply_sheets/print/excel') ?>' + url);
    }

    function print_kanban() {
        var request_no = $("#filter_request_no").combobox('getValue');
        var operation = $("#filter_operation").combobox('getValue');
        if (request_no == "" || operation == "") {
            toastr.warning("Please select Supply No and Operation!", "Information");
        } else {
            window.open("<?= base_url('planning/supply_sheets/print_kanban/') ?>" + window.btoa(request_no) + "/" + window.btoa(operation), "_blank");
        }
    }

    function reload() {
        window.location.reload();
    }
    // function supply_no() {
    //     $("#filter_request_no").combobox({
    //         url: '<?= base_url('planning/supply_sheets/readRequestNo') ?>',
    //         valueField: 'request_no',
    //         textField: 'request_no',
    //         prompt: "Select Supply No",
    //         icons: [{
    //             iconCls: 'icon-clear',
    //             handler: function(e) {
    //                 $(e.data.target).combobox('clear').combobox('textbox').focus();
    //             }
    //         }],
    //     });
    // }
    $(function() {
        $('#dg').treegrid({
            url: '<?= base_url('planning/supply_sheets/datatables') ?>',
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'request_no',
            singleSelect: false,
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
                    var workorder = $("#workorder").textbox('getValue');
                    var item_id = $("#item_id").combogrid('getValue');

                    var rows = $('#dg_request').datagrid('getRows');
                    if (rows.length > 0) {
                        $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                            if (r) {
                                for (var i = 0; i < rows.length; i++) {
                                    var row = rows[i];
                                    $.ajax({
                                        type: "post",
                                        url: '<?= base_url('planning/supply_sheets/create') ?>',
                                        data: 'item_id=' + item_id +
                                            '&component_id=' + row.component_id +
                                            '&workorder=' + workorder +
                                            '&request_date=' + request_date +
                                            '&request_no=' + request_no +
                                            '&request_name=' + request_name +
                                            '&qty_req=' + row.qty_req +
                                            '&qty_act=' + row.qty_act +
                                            '&mpq=' + row.mpq +
                                            '&qty_bal=' + (parseInt(row.qty_req) - parseInt(row.qty_act)),
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "error") {
                                                toastr.warning(result.message, "Error");
                                            }
                                        }
                                    });
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
                        });
                    } else {
                        toastr.warning("Please select one of the data in the table first!", "Information");
                    }
                }
            }]
        });

        $("#filter_supply_type").combobox({
            data: [{
                "text" : "OPEN",
            },{
                "text" : "CLOSE",
            }],
            valueField: 'text',
            textField: 'text',
            prompt: "Select Supply Type",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(rowType) {
                $("#filter_period").combobox({
                    url: '<?= base_url('planning/supply_sheets/readPeriod?type=') ?>' + window.btoa(rowType.text),
                    valueField: 'period',
                    textField: 'period',
                    prompt: "Select Period",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(rowPeriod) {
                        $("#filter_wp").combobox({
                            url: '<?= base_url('planning/supply_sheets/readWp?period=') ?>' + window.btoa(rowPeriod.period) + "&type=" + window.btoa(rowType.text),
                            valueField: 'wp',
                            textField: 'wp',
                            prompt: "Select WP",
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }],
                            onSelect: function(rowWp) {
                                $("#filter_request_no").combobox({
                                    url: '<?= base_url('planning/supply_sheets/readRequestNo?period=') ?>' + window.btoa(rowPeriod.period) + "&wp=" + window.btoa(rowWp.wp) + "&type=" + window.btoa(rowType.text),
                                    valueField: 'request_no',
                                    textField: 'request_no',
                                    prompt: "Select Supply No",
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
            }
        });
        
        $("#filter_operation").combobox({
            url: '<?= base_url('master/bom/readOperations') ?>',
            valueField: 'operation',
            textField: 'operation',
            prompt: "Select Operation",
            panelHeight: 'auto',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
        $("#operation").combobox({
            url: '<?= base_url('master/bom/readOperations') ?>',
            valueField: 'operation',
            textField: 'operation',
            prompt: "All Operation",
            panelHeight: 'auto',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
        $("#request_date").datebox({
            onChange: function(val) {
                request_no(val);
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
        if (value) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
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
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function statusIssued(value, row, index) {
        if (value == "OPEN") {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function issuedformat(value, row) {
        if (value == "OPEN") {
            return "<b style='color:green;'>OPEN</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function BtnPrintLabel(val, row) {
        return '<a style="text-decoration: none; font-weight:bold;" target="_blank" href="<?= base_url('planning/supply_sheets/print_label/') ?>' + window.btoa(row.id) + '"><i class="fa fa-print"></i> Print</a>';
    }
</script>
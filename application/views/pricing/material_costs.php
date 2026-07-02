<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'item_fg_number',halign:'center',width:150" sortable="true">Product Number</th>
            <th rowspan="2" data-options="field:'item_fg_name',halign:'center',width:150" sortable="true">Product Name</th>
            <th rowspan="2" data-options="field:'revision',width:80,halign:'center'" sortable="true">Revision</th>
            <th rowspan="2" data-options="field:'p_month',width:120,halign:'center'" sortable="true">Month</th>
            <th rowspan="2" data-options="field:'p_year',width:120,halign:'center'" sortable="true">Year</th>
            <th colspan="2" data-options="field:'',width:150,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:150,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'" sortable="true"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'" sortable="true"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'" sortable="true"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'" sortable="true"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 230px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" id="filter_period_month" value="<?= date("m") ?>" class="easyui-combobox">
                <input style="width:30%;" id="filter_period_year" value="<?= date("Y") ?>" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <select style="width:60%;" id="filter_revision" class="easyui-combobox" panelHeight="auto">
                    <option value="" selected disabled>Choose All</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item_fg_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
        <!-- <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="print_kanban()"><i class="fa fa-print"></i> Print Supply Sheet</a>
        <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="print_label_supply()"><i class="fa fa-print"></i> Print Label Supply</a>
        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="close_sh()"><i class="fa fa-times"></i> Complete/Open</a> -->

    </div>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 80%; height: 500px; padding:10px; top: 20px; left: 10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:15%; display:inline-block;">Period</span>
                <input style="width:20%;" name="p_month" id="p_month" required="" class="easyui-combobox">
                <input style="width:20%;" name="p_year" id="p_year" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:15%; display:inline-block;">Revision</span>
                <select style="width:40%;" name="revision" id="revision" required="" class="easyui-combobox" panelHeight="auto">
                    <option value="" selected disabled>Choose Revision</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:15%; display:inline-block;">Division</span>
                <input style="width:40%;" id="division_id" class="easyui-combobox">
            </div>
            <div class="fitem" hidden>
                <span style="width:15%; display:inline-block;">Product Id</span>
                <input style="width:40%;" name="item_fg_id" id="item_fg_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:15%; display:inline-block;">Product No</span>
                <input style="width:40%;" name="item_fg_number" id="item_fg_number" required="" class="easyui-combogrid">
            </div>
            <div class="fitem" hidden>
                <span style="width:15%; display:inline-block;">Product Name</span>
                <input style="width:40%;" name="item_fg_name" id="item_fg_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:15%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
            </div>
        </fieldset>
        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Component List" data-options="rownumbers: true, singleSelect: false" idField="component_number">

        </table>
    </form>
</div>
<!-- PDF -->
<iframe id="printout" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg_request').datagrid('loadData', []);
    }

    function preview() {
        var item_fg_id = $("#item_fg_id").textbox('getValue');
        var item_fg_number = $("#item_fg_number").textbox('getValue');

        if (item_fg_id == "") {
            toastr.warning('Please select Product No', 'Required');
        } else {
            var dg = $('#dg_request').datagrid({
                url: '<?= base_url('pricing/material_costs/datatablesTemp/') ?>?item_fg_id=' + item_fg_id,
                // singleSelect: true,
                idField: 'item_rm_id',
                columns: [
                    [{
                        field: 'ck',
                        checkbox: true,
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
                    },{
                        field: 'id',
                        width: 100,
                        hidden: true,
                        halign: 'center',
                        title: "ID",
                    }, {
                        field: 'item_rm_id',
                        width: 100,
                        hidden: true,
                        halign: 'center',
                        title: "Part ID",
                    }, {
                        field: 'part_no',
                        width: 150,
                        halign: 'center',
                        title: "Part No",
                    }, {
                        field: 'part_name',
                        width: 150,
                        halign: 'center',
                        title: "Part Name",
                    }, {
                        field: 'product_family',
                        width: 120,
                        halign: 'center',
                        title: "Product Family",
                    }, {
                        field: 'used',
                        width: 80,
                        halign: 'center',
                        title: "Used",
                    }, {
                        field: 'uom',
                        width: 80,
                        halign: 'center',
                        title: "UOM",
                    }, {
                        field: 'price',
                        width: 100,
                        halign: 'center',
                        align: 'right',
                        title: "Price",
                        editor: {
                            type: 'numberbox',
                            options: {
                                precision: 2,
                            }
                        }
                     }, {
                        field: 'currency',
                        width: 80,
                        halign: 'center',
                        title: "Currency",
                    }, {
                        field: 'adj',
                        width: 100,
                        halign: 'center',
                        align: 'right',
                        title: "Adjust (%)",
                        editor: {
                            type: 'numberbox',
                            options: {
                                precision: 2,
                            }
                        }
                    }, {
                        field: 'adj_price_nominal',
                        width: 100,
                        halign: 'center',
                        align: 'right',
                        title: "Price Adjust",
                        editor: {
                            type: 'numberbox',
                            options: {
                                precision: 2,
                                readonly: true,
                            }
                        }
                    }, {
                        field: 'material_cost',
                        width: 100,
                        halign: 'center',
                        align: 'right',
                        title: "Material <br>Cost",
                        editor: {
                            type: 'numberbox',
                            options: {
                                precision: 4,
                            }
                        }
                    }]
                ],

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
                onBeginEdit: function(index, row) {
                    var dg = $(this);
                    var edPrice = dg.datagrid('getEditor', { index: index, field: 'price' });
                    var edAdj = dg.datagrid('getEditor', { index: index, field: 'adj' });
                    var edAdjNominal = dg.datagrid('getEditor', { index: index, field: 'adj_price_nominal' });
                    var edMatCost = dg.datagrid('getEditor', { index: index, field: 'material_cost' });

                    var calculateRow = function() {
                        var price = parseFloat($(edPrice.target).numberbox('getValue')) || 0;
                        var adj = parseFloat($(edAdj.target).numberbox('getValue')) || 0;
                        var used = parseFloat(row.used) || 0;

                        // 1. adj_price_nominal = price + (price * adj / 100)
                        var nominal = price + (price * adj / 100);
                        $(edAdjNominal.target).numberbox('setValue', nominal);

                        // 2. material_cost = used * adj_price_nominal
                        var cost = used * nominal;
                        $(edMatCost.target).numberbox('setValue', cost);
                    };

                    $(edPrice.target).numberbox({ onChange: calculateRow });
                    $(edAdj.target).numberbox({ onChange: calculateRow });
                }
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
        var rows = $('#dg').datagrid('getSelections');
        console.log(rows);
        if (rows.length > 0) {
            Swal.fire({
                title: 'Warning',
                text: 'Are you sure you want to delete this data?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('pricing/material_costs/delete') ?>',
                            data: {
                                item_fg_id: row.item_fg_id,
                                revision: row.revision,
                                p_month: row.p_month,
                                p_year: row.p_year
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                Swal.fire({
                                    title: 'Error',
                                    text: jqXHR.statusText,
                                    icon: 'error',
                                    confirmButtonText: 'Ok'
                                });
                            },
                            complete: function(data) {
                                window.location.reload();
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
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');

        var url = "?filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_item_fg_id=" + window.btoa(filter_item_fg_id) +
            "&filter_revision=" + window.btoa(filter_revision);

        $('#dg').datagrid({
            url: '<?= base_url('pricing/material_costs/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><div class="loading-detail" style="text-align:center;padding:20px;">Loading data...</div><table class="ddv" title="Detail Of ' + row.item_fg_number + '" style="display:none;"></table></div>';
            },
            onExpandRow: function(index, row) {
                var rowDetail = $(this).datagrid('getRowDetail', index);
                var ddv = rowDetail.find('table.ddv');
                var loading = rowDetail.find('.loading-detail');

                ddv.datagrid({
                    url: '<?= base_url('pricing/material_costs/datatableDetails/') ?>' + 
                        window.btoa(row.item_fg_id) + "/" + 
                        window.btoa(row.revision) + "/" + 
                        window.btoa(row.p_month) + "/" + 
                        window.btoa(row.p_year),
                    method: 'get',
                    singleSelect: true,
                    rownumbers: true,
                    fitColumns: false,
                    loadMsg: 'Loading...',
                    columns: [
                        [{
                            field: 'part_no',
                            title: 'Part No',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'part_name',
                            title: 'Part Name.',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'product_family',
                            title: 'Product Family.',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'used',
                            title: 'Used',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'price',
                            title: 'Price',
                            halign: 'center',
                            width: 80,
                            formatter: numberformatQpa
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'adj',
                            title: 'Adj %',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'adj_price_nominal',
                            title: 'Adj Price',
                            halign: 'center',
                            width: 100,
                            formatter: numberformatQpa
                        }, {
                            field: 'material_cost',
                            title: 'Material Cost',
                            halign: 'center',
                            width: 100,
                            formatter: numberformatQpa
                        }, {
                            field: 'total_material_cost',
                            title: 'Total',
                            halign: 'center',
                            width: 100,
                            formatter: numberformatQpa
                        }]
                    ],
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                        // Sembunyikan loading dan tampilkan tabel
                        loading.hide();
                        ddv.show();
                    },
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    }
                });

                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });


        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('pricing/material_costs/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');

        var url = "?filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_item_fg_id=" + window.btoa(filter_item_fg_id) +
            "&filter_revision=" + window.btoa(filter_revision);

        window.location.assign('<?= base_url('pricing/material_costs/print/excel') ?>' + url);
    }

    // function print_kanban() {
    //     var row = $('#dg').datagrid('getSelected');
    //     console.log(row);
    //     if (row) {
    //         window.open("<?= base_url('pricing/material_costs/print_kanban/') ?>" + window.btoa(row.request_no));// + "/" + window.btoa(operation), "_blank"
    //     }else{
    //         toastr.warning("Please select one of the data in the table first!", "Information");
    //     }
    // }

    // function print_label_supply() {
    //     var row = $('#dg').datagrid('getSelected');
    //     console.log(row);
    //     if (row) {
    //         window.open("<?= base_url('pricing/material_costs/label_supply/') ?>" + window.btoa(row.request_no));// + "/" + window.btoa(operation), "_blank"
    //     }else{
    //         toastr.warning("Please select one of the data in the table first!", "Information");
    //     }
    // }

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
                    var header = {
                        item_fg_id: $("#item_fg_id").textbox('getValue'),
                        item_fg_number: $("#item_fg_number").combogrid('getValue'),
                        item_fg_name: $("#item_fg_name").textbox('getValue'),
                        division_id: $("#division_id").combobox('getValue'),
                        p_month: $("#p_month").combobox('getValue'),
                        p_year: $("#p_year").combobox('getValue'),
                        revision: $("#revision").combobox('getValue')
                    };

                    var rows = $('#dg_request').datagrid('getSelections');
                    
                    if (rows.length > 0) {
                        $.messager.confirm('Warning', 'Are you sure you want to save all selected data?', function(r) {
                            if (r) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('pricing/material_costs/create_batch') ?>',
                                    data: {
                                        header: header,
                                        details: rows
                                    },
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.success) {
                                            $('#dlg_insert').dialog('close');
                                            Swal.fire({
                                                title: "Success",
                                                text: result.message + " (Total Cost: " + result.total_cost + ")",
                                                icon: "success",
                                                confirmButtonText: 'Ok',
                                                allowOutsideClick: false
                                            }).then((res) => {
                                                if (res.isConfirmed) {
                                                    window.location.reload();
                                                    $('#dg').datagrid('reload');
                                                }
                                            });
                                            
                                            localStorage.setItem('task_saved', 'yes');//untuk keperluan npd
                                        } else {
                                            toastr.error(result.message, "Error");
                                        }
                                    },
                                    error: function() {
                                        toastr.error("An error occurred while processing.", "Error");
                                    }
                                });
                            }
                        });
                    } else {
                        toastr.warning("Please select at least one row!", "Information");
                    }
                }
            }]
        });

        $('#filter_item_fg_id').combogrid({
            url: '<?= base_url('master/item_fg/reads/'); ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product No",
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 250
                }, ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });

        $('#p_month').combobox({
            url: '<?= base_url('pricing/material_costs/readPeriod/month'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Months',
        });

        $('#p_year').combobox({
            url: '<?= base_url('pricing/material_costs/readPeriod/year'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Years',
        });

        $('#filter_period_month').combobox({
            url: '<?= base_url('pricing/material_costs/readPeriod/month'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Months',
        });

        $('#filter_period_year').combobox({
            url: '<?= base_url('pricing/material_costs/readPeriod/year'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Years',
        });

        $('#division_id').combobox({
            url: '<?= base_url('master/divisions/reads'); ?>',
            valueField: 'id',
            textField: 'number',
            panelHeight: 'panelHeight',
            prompt: 'Choose Division',
            onSelect: function(div) {
                $('#item_fg_number').combogrid({
                    url: '<?= base_url('pricing/material_costs/readItems/') ?>' + window.btoa(div.id),
                    panelWidth: 500,
                    idField: 'number',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Product Number.",
                    columns: [
                        [{
                            field: 'id',
                            title: 'Product ID',
                            width: 200
                        }, {
                            field: 'number',
                            title: 'Product No.',
                            width: 150
                        }, {
                            field: 'name',
                            title: 'Product Name',
                            width: 150
                        }]
                    ],
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    onSelect: function(value, rows) {
                        $('#item_fg_id').textbox('setValue', rows.id);
                        $('#item_fg_name').textbox('setValue', rows.name);
                    }
                });
            }
        });
    });

    // $("#filter_lot_no").combobox({
    //     url: '<?= base_url('pricing/material_costs/bpiWps/') ?>',
    //     valueField: 'lot_no',
    //     textField: 'lot_no',
    //     prompt: "Select Lot No",
    //     icons: [{
    //         iconCls: 'icon-clear',
    //         handler: function(e) {
    //             $(e.data.target).combobox('clear').combobox('textbox').focus();
    //         }
    //     }],
    // });

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
        } else if (value == 2) {
            return "<b style='color:white;'>COMPLETE</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else if (value == 2) {
            return 'background-color:#4B54E7;';
        }
    }

    function statusissued(value, row, index) {
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
        return '<a style="text-decoration: none; font-weight:bold;" target="_blank" href="<?= base_url('pricing/material_costs/print_label/') ?>' + window.btoa(row.id) + '"><i class="fa fa-print"></i> Print</a>';
    }

    function close_sh(){
        var rows = $('#dg').datagrid('getSelections');
        console.log(rows);
        if (rows.length > 0) {
            // $.messager.confirm('Warning', 'Are you sure you want to completed this data?', function(r) {
            //     if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        console.log(row);
                        if (row.status == "0") {
                            Swal.fire({
                                title: "Are you sure?",
                                text: "You want to Complete this data?",
                                icon: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#3085d6",
                                cancelButtonColor: "#d33",
                                confirmButtonText: "Yes",
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        method: 'post',
                                        url: '<?= base_url('pricing/material_costs/closeSh') ?>',
                                        data: {
                                            request_no: row.request_no,
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
                                }
                            });
                        } else {
                            // toastr.error("this data has been Completed");
                            Swal.fire({
                                title: "Are you sure?",
                                text: "You want to Open this data!",
                                icon: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#3085d6",
                                cancelButtonColor: "#d33",
                                confirmButtonText: "Yes",
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        method: 'post',
                                        url: '<?= base_url('pricing/material_costs/openSh') ?>',
                                        data: {
                                            request_no: row.request_no,
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
                                }
                            });
                        }
                    }
            //     }
            // });
        }
    }

    //untuk kebutuhan NPD
    $(document).ready(function() {
        if (localStorage.getItem('trigger_add') === 'yes') {
            localStorage.removeItem('trigger_add');

            $('<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:#ffffff;z-index:8999;"></div>').appendTo('body');

            setTimeout(function() {
                if (typeof add === "function") {
                    add(); 

                    var checkClose = setInterval(function() {
                        var isHidden = $('#dlg_insert').closest('.window').is(':hidden');
                        if (isHidden) {
                            clearInterval(checkClose); // Hentikan monitoring
                            if (window.parent.$('#dlg_outer_wrapper').length) {
                                window.parent.$('#dlg_outer_wrapper').dialog('close');
                            }
                        }
                    }, 500);
                }
            }, 1000); 
        }

        // ==========================================
        // SKRIP TRIGGER UPLOAD
        // ==========================================
        var urlParams = new URLSearchParams(window.location.search);
        var action = urlParams.get('action');

        if (action === 'upload') {
            $('<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:#ffffff;z-index:8999;"></div>').appendTo('body');

            setTimeout(function() {
                if (typeof upload === 'function') {
                    upload(); 
                    var checkCloseUpload = setInterval(function() {
                        var isHidden = $('#dlg_upload').closest('.window').is(':hidden'); 
                        if (isHidden) {
                            clearInterval(checkCloseUpload); 
                            if (window.parent.$('#dlg_upload_wrapper').length) {
                                window.parent.$('#dlg_upload_wrapper').dialog('close');
                            }
                        }
                    }, 500);
                }
            }, 500);
        }
    });
</script>
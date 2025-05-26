<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'print',width:59,align:'center', formatter:btnPrint">Print</th>
            <th rowspan="2" data-options="field:'status2',width:80,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
            <th rowspan="2" data-options="field:'document_no',width:150,halign:'center'">Document No</th>
            <th rowspan="2" data-options="field:'trans_date',width:150,halign:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'remarks',width:80,align:'center'">Remarks</th>          
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
<div id="toolbar" style="height: 230px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 50%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" id="filter_document_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
                <div class="fitem">
                    <span style="width:15%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" name="trans_date" id="trans_date" required="" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:15%; display:inline-block;">Document No</span>
                    <input style="width:30%;" name="document_no" id="document_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:15%; display:inline-block;">Division</span>
                    <input style="width:30%;" name="division" id="division" required="" class="easyui-combobox">
                </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Sales Order Lists" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('control/repair_of_goods/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        // url_save = '<?= base_url('control/repair_of_goods/create') ?>';
        $('#frm_insert').form('clear');
        $('#trans_date').datebox('setValue', '<?= date("Y-m-d") ?>');

        $("#document_no").textbox('enable');
        $("#trans_date").datebox('enable');
        number();
    }

    function number(date= "") {
        $.ajax({
            type: "post",
            url: "<?= base_url('control/repair_of_goods/number/') ?>" + window.btoa(date),
            dataType: "html",
            success: function(result) {
                $("#document_no").textbox('setValue', result);
            }
        });
    }

    function addTable(division, link = "") {
        $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'item_fg_number',
                    width: 200,
                    halign: 'center',
                    title: "Product No.",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('control/repair_of_goods/reads/'); ?>' + division,
                            required: true,
                            panelWidth: 400,
                            idField: 'number',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product No.',
                            columns: [
                                [{
                                    field: 'id',
                                    title: 'Product ID',
                                    width: 200
                                }, {
                                    field: 'number',
                                    title: 'Product No.',
                                    width: 200
                                }, {
                                    field: 'name',
                                    title: 'Product Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_number'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_id'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_name'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });

                                $(ed.target).textbox('setValue', rows.number);
                                $(ed2.target).textbox('setValue', rows.id);
                                $(ed3.target).textbox('setValue', rows.name);
                                $(ed4.target).textbox('setValue', rows.uom);
                            }
                        }
                    }
                }, {
                    field: 'item_fg_id',
                    width: 150,
                    hidden: true,
                    halign: 'center',
                    title: "Product ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'item_fg_name',
                    width: 150,
                    halign: 'center',
                    title: "Product Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'uom',
                    width: 100,
                    align: 'center',
                    title: "Uom",
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
                            precision: 4
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
        var document_no = $("#document_no").textbox('getValue');
        if (document_no != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0',
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Customer Name first");
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
            field: 'item_fg_id'
        });

        var item_fg_id = $(ed.target).textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('control/repair_of_goods/delete') ?>',
            data: {
                item_fg_id: item_fg_id
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                toastr.success(result.message);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                toastr.error(jqXHR.statusText);
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
            $("#document_no").textbox('disable');
            $("#trans_date").datebox('disable');

            addTable(row.division, '<?= base_url('control/repair_of_goods/datatableUpdates?document_no=') ?>' + window.btoa(row.document_no));
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
                            method: 'post',
                            url: '<?= base_url('control/repair_of_goods/delete') ?>',
                            data: {
                                sales_order_no: row.sales_order_no
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
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

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_document_no = $("#filter_document_no").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_document_no=" + window.btoa(filter_document_no) +
            "&filter_item_fg_id=" + window.btoa(filter_item_fg_id);

        $('#dg').datagrid({
            url: '<?= base_url('control/repair_of_goods/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/repair_of_goods/print') ?>' + url);
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
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');


        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_document_no=" + window.btoa(filter_document_no) +
            "&filter_item_fg_id=" + window.btoa(filter_item_fg_id);

        window.location.assign('<?= base_url('control/repair_of_goods/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        addTable();

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('control/repair_of_goods/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.document_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('control/repair_of_goods/datatableDetails?document_no=') ?>' + window.btoa(row.document_no),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'item_fg_id',
                            title: 'Product ID',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_number',
                            title: 'Product No.',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'qty',
                            title: 'Qty Repair',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'qty_scan',
                            title: 'Scan Out',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'remarks',
                            title: 'Remarks',
                            halign: 'center',
                            align: 'right',
                            width: 100
                        }, {
                            field: 'status',
                            title: 'Status',
                            halign: 'center',
                            align: 'right',
                            width: 100,
                            formatter: cellFormatter,
                            styler: cellStyler
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
                    var trans_date = $("#trans_date").datebox('getValue');
                    var document_no = $("#document_no").textbox('getValue');
                    var division = $("#division").combobox('getValue');

                    $('#dg2').datagrid('acceptChanges');
                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    if (document_no != "") {
                        for (let i = 0; i < totalrows; i++) {
                            if (rows[i].item_fg_id) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('control/repair_of_goods/create') ?>',
                                    data: {
                                        trans_date: trans_date,
                                        document_no: document_no,
                                        division: division,
                                        item_fg_id: rows[i].item_fg_id,
                                        item_fg_name: rows[i].item_fg_name,
                                        qty: rows[i].qty,
                                        remarks: rows[i].remarks,
                                    },
                                    dataType: "json",
                                    success: function(result) {
                                        if (i == (totalrows - 1)) {
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

                        $('#dg').datagrid('reload');
                        $('#dlg_insert').dialog('close');
                    } else {
                        toastr.error("Please Completed your input");
                    }
                }
            }]
        });
    });

    $("#trans_date").datebox({
        onSelect: function(date) {
            number(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate());
        }
    });

    $('#filter_document_no').combobox({
        url: '<?= base_url('control/sales_orders/readDocNo'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose All',
    });

    $('#filter_item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/reads'); ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No.",
        columns: [
            [{
                field: 'number',
                title: 'Product No.',
                width: 150
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

    $('#division').combobox({ 
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'number',
        textField: 'name',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
        onSelect: function(division){
            addTable(division.number);
            console.log(division.number);
        }
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
            return 'OPEN';
        } else {
            return 'CLOSE';
        }
    };

    // FORMAT tahun-bulan-tanggal
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

    function numberFormat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function btnPrint(val, row) {
        var print = "print_rod('" + row.document_no + "')"; 
        return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
    }

    function print_rod(document_no) {
        window.open("<?= base_url('control/repair_of_goods/print_rod/') ?>" + window.btoa(document_no), "_blank", "width=1200,height=600");
    }

</script>
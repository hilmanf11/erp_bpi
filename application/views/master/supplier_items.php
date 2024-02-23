<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Supplier is taken from <b>Master Data > Material Control > Suppliers</b></li>
                <li>The Data Part No is taken from <b>Master Data > Engineering > Item Raw Materials</b></li>
                <li>The Data Currency is taken from <b>Master Data > Material Control > Suppliers > Currency</b></li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'supplier_name',width:550,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'type',width:100,halign:'center'">Type</th>
            <th rowspan="2" data-options="field:'currency',width:100,halign:'center'">Currency</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
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
        <fieldset style="width: 45%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier</span>
                <input style="width:60%;" id="filter_supplier_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No</span>
                <input style="width:60%;" id="filter_item_rm_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1400px; height: 500px; padding:10px; top: 20px; left: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:15%; display:inline-block;">Supplier</span>
                <input style="width:40%;" name="supplier_id" id="supplier_id" required="" class="easyui-combogrid">
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Supplier Item Lists" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- Detail Histories -->
<div id="dlg_history" class="easyui-dialog" title="Price Histories" data-options="closed: true,modal:true" style="width: 400px; height: 300px; top: 20px;">
    <table id="dg_history" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'price',width:100,halign:'center',formatter: priceformat">Price</th>
                <th data-options="field:'valid_date',width:100,halign:'center'">Valid Date</th>
            </tr>
        </thead>
    </table>
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
<iframe id="printout" src="<?= base_url('master/supplier_items/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('master/supplier_items/create') ?>';
        $('#frm_insert').form('clear');
    }

    function addTable(link = "") {
        $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'item_rm_number',
                    width: 200,
                    halign: 'center',
                    title: "Part No.",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('master/item_rm/reads'); ?>',
                            required: true,
                            panelWidth: 400,
                            idField: 'number',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Part No.',
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Part No.',
                                    width: 150
                                }, {
                                    field: 'name',
                                    title: 'Part Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_number'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_id'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_family_name'
                                });

                                $(ed.target).textbox('setValue', rows.number);
                                $(ed2.target).textbox('setValue', rows.id);
                                $(ed3.target).textbox('setValue', rows.item_family_name);
                            }
                        }
                    }
                }, {
                    field: 'item_rm_id',
                    width: 150,
                    hidden: true,
                    halign: 'center',
                    title: "Part ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'maker',
                    width: 150,
                    halign: 'center',
                    title: "Maker",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'item_supplier',
                    width: 150,
                    halign: 'center',
                    title: "Supplier Product",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'item_family_name',
                    width: 150,
                    halign: 'center',
                    title: "Product Family",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'mpq',
                    width: 100,
                    align: 'center',
                    title: "MPQ",
                    editor: {
                        type: 'numberbox'
                    }
                }, {
                    field: 'moq',
                    width: 100,
                    align: 'center',
                    title: "MOQ",
                    editor: {
                        type: 'numberbox'
                    }
                }, {
                    field: 'share_order',
                    width: 100,
                    align: 'center',
                    title: "% Share Order",
                    editor: {
                        type: 'numberbox'
                    }
                }, {
                    field: 'leadtime',
                    width: 120,
                    align: 'center',
                    title: "Lead Time (Days)",
                    editor: {
                        type: 'numberbox'
                    }
                }, {
                    field: 'price',
                    width: 100,
                    align: 'center',
                    title: "Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2
                        }
                    }
                }, {
                    field: 'valid_date',
                    width: 180,
                    halign: 'center',
                    align: 'right',
                    title: "Valid Date Until",
                    editor: {
                        type: 'datebox',
                        options: {
                            formatter: myformatter,
                            parser: myparser
                        }
                    }
                }, {
                    field: 'safety_stock',
                    width: 100,
                    align: 'center',
                    title: "Safety Stock %",
                    editor: {
                        type: 'numberbox'
                    }
                }, {
                    field: 'calculate',
                    width: 120,
                    halign: 'center',
                    title: "Calculate MPQ",
                    editor: {
                        type: 'combobox',
                        options: {
                            valueField: 'name',
                            textField: 'name',
                            prompt: 'Choose Calculate MPQ',
                            panelHeight: true,
                            data: [{
                                    name: "YES"
                                },
                                {
                                    name: "NO"
                                },
                            ]
                        }
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
        var supplier_id = $("#supplier_id").combogrid('getValue');
        if (supplier_id != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Supplier first");
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
            field: 'item_rm_id'
        });

        var supplier_id = $("#supplier_id").combogrid('getValue');
        var item_rm_id = $(ed.target).textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('master/supplier_items/delete') ?>',
            data: {
                supplier_id: row.supplier_id,
                item_rm_id: item_rm_id
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
            $("#item_rm_id").combogrid('disable');

            addTable('<?= base_url('master/supplier_items/datatableUpdates?supplier_id=') ?>' + window.btoa(row.supplier_id));
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
                            url: '<?= base_url('master/supplier_items/delete') ?>',
                            data: {
                                supplier_id: row.supplier_id
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
        window.location.assign('<?= base_url('template/tmp_supplier_items.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_supplier_id = $("#filter_supplier_id").combogrid('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

        var url = "?filter_supplier_id=" + window.btoa(filter_supplier_id) +
            "&filter_item_rm_id=" + window.btoa(filter_item_rm_id);

        $('#dg').datagrid({
            url: '<?= base_url('master/supplier_items/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('master/supplier_items/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_supplier_id = $("#filter_supplier_id").combogrid('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

        var url = "?filter_supplier_id=" + window.btoa(filter_supplier_id) +
            "&filter_item_rm_id=" + window.btoa(filter_item_rm_id);

        window.location.assign('<?= base_url('master/supplier_items/print/excel') ?>' + url);
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
            url: '<?= base_url('master/supplier_items/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.supplier_name + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('master/supplier_items/datatableDetails?number=') ?>' + window.btoa(row.supplier_number) + "&filter_item_rm_id=" + window.btoa(filter_item_rm_id),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'item_rm_id',
                            title: 'Part ID',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_rm_number',
                            title: 'Part No.',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_rm_name',
                            title: 'Part Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'maker',
                            title: 'Maker',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_supplier',
                            title: 'Supplier Product',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_family_name',
                            title: 'Product Family',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'mpq',
                            title: 'MPQ',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'moq',
                            title: 'MOQ',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'share_order',
                            title: 'Share Order %',
                            halign: 'center',
                            width: 100
                        }, {
                            field: 'leadtime',
                            title: 'Leadtime',
                            halign: 'center',
                            width: 100
                        }, {
                            field: 'price',
                            title: 'Price',
                            halign: 'center',
                            align: 'right',
                            width: 100,
                            formatter: priceformat
                        }, {
                            field: 'btn',
                            title: 'History',
                            halign: 'center',
                            width: 80,
                            formatter: btnHistories
                        }, {
                            field: 'valid_date',
                            title: 'Valid Date',
                            halign: 'center',
                            width: 100
                        }, {
                            field: 'safety_stock',
                            title: 'Safet Stock %',
                            width: 100,
                            halign: 'center',
                        }, {
                            field: 'calculate',
                            title: 'Calculate MPQ',
                            width: 100,
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
                    var supplier_id = $("#supplier_id").combogrid('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_rm_id) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('master/supplier_items/create') ?>',
                                data: {
                                    supplier_id: supplier_id,
                                    item_rm_id: rows[i].item_rm_id,
                                    maker: rows[i].maker,
                                    item_supplier: rows[i].item_supplier,
                                    mpq: rows[i].mpq,
                                    moq: rows[i].moq,
                                    share_order: rows[i].share_order,
                                    leadtime: rows[i].leadtime,
                                    price: rows[i].price,
                                    valid_date: rows[i].valid_date,
                                    safety_stock: rows[i].safety_stock,
                                    calculate: rows[i].calculate
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
                }
            }]
        });
    });

    $('#supplier_id').combogrid({
        url: '<?= base_url('master/suppliers/reads/'); ?>',
        panelWidth: 420,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Supplier",
        columns: [
            [{
                field: 'number',
                title: 'Supplier Code',
                width: 120
            }, {
                field: 'name',
                title: 'Supplier Name',
                width: 250
            }, {
                field: 'currency',
                title: 'Currency',
                width: 100
            }, ]
        ]
    });

    $('#filter_supplier_id').combogrid({
        url: '<?= base_url('master/suppliers/reads'); ?>',
        panelWidth: 750,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Supplier",
        columns: [
            [{
                field: 'id',
                title: 'Supplier ID',
                width: 150
            }, {
                field: 'number',
                title: 'Supplier Code',
                width: 150
            }, {
                field: 'name',
                title: 'Supplier Name',
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
    });

    $('#filter_item_rm_id').combogrid({
        url: '<?= base_url('master/item_rm/reads'); ?>',
        panelWidth: 500,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Part No.",
        columns: [
            [{
                field: 'id',
                title: 'Part ID',
                width: 180
            }, {
                field: 'number',
                title: 'Part No.',
                width: 150
            }, {
                field: 'name',
                title: 'Part Name',
                width: 150
            }, {
                field: 'item_family_id',
                title: 'Product Family',
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

    // function priceformat(value, row) {
    //     if (row.currency == "USD") {
    //         var digits = 4;
    //         var currency = 'USD';
    //         var format = "en-IN";
    //     } else if (row.currency == "JPY") {
    //         var digits = 2;
    //         var currency = 'JPY';
    //         var format = "ja-JP";
    //     } else if (row.currency == "EUR") {
    //         var digits = 2;
    //         var currency = 'EUR';
    //         var format = "de-DE";
    //     } else {
    //         var digits = 0;
    //         var currency = 'IDR';
    //         var format = "id-ID";
    //     }

    //     if (value != null) {
    //         const formatter = new Intl.NumberFormat(format, {
    //             style: 'currency',
    //             currency: currency,
    //             minimumFractionDigits: digits
    //         });
    //         return "<b>" + formatter.format(value) + "</b>";
    //     }
    // }

    function priceformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function btnHistories(val, row) {
        var history = "viewHistories('" + row.supplier_id + "','" + row.item_rm_id + "')";
        return '<a class="btn btn-primary w-100" onClick="' + history + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewHistories(supplier_id, item_rm_id) {
        $("#dlg_history").dialog('open');
        $('#dg_history').datagrid({
            url: '<?= base_url('master/supplier_items/datatableHistories?supplier_id=') ?>' + btoa(supplier_id) + "&item_rm_id=" + btoa(item_rm_id),
            pagination: false,
            rownumbers: true,
        });
    }

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/supplier_items/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/supplier_items/upload') ?>',
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
                            url: "<?= base_url('master/supplier_items/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('master/supplier_items/uploadCreate') ?>",
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
                                                url: "<?= base_url('master/supplier_items/uploadcreateFailed') ?>",
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
</script>
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status</th>
            <th rowspan="2" data-options="field:'receipt_no',width:150,halign:'center'">Document No</th>
            <th rowspan="2" data-options="field:'trans_date',width:150,halign:'center'">Transaction Date</th>
            <!-- <th rowspan="2" data-options="field:'state',width:80,align:'center',formatter:BtnPrintLabel">Label</th> -->
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
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Date</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt No</span>
                    <input style="width:60%;" id="filter_receipt" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; height: 100%; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt Date</span>
                    <input style="width:60%;" name="trans_date" id="trans_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt No</span>
                    <input style="width:60%;" name="receipt_no" id="receipt_no" readonly class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Receive Crusher" toolbar="#toolbar2"></table>
    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('purchase/purchase_order_receipt_crushers/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');
        $('#trans_date').datebox('setValue', '<?= date("Y-m-d") ?>');
        $('#dg2').datagrid('loadData', []);
        receipt_no();
        // lotno();
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
                            url: '<?= base_url('purchase/purchase_order_receipt_crushers/readRM'); ?>',
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
                                    field: 'item_rm_name'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'mpq'
                                });

                                $(ed.target).textbox('setValue', rows.number);
                                $(ed2.target).textbox('setValue', rows.id);
                                $(ed3.target).textbox('setValue', rows.name);
                                $(ed4.target).textbox('setValue', rows.uom);
                                $(ed5.target).textbox('setValue', rows.mpq);
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
                    field: 'item_rm_name',
                    width: 150,
                    halign: 'center',
                    title: "Part Name",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'uom',
                    width: 80,
                    halign: 'center',
                    title: "UoM",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'qty',
                    width: 100,
                    align: 'center',
                    title: "QTY",
                editor: {
                    type: 'numberbox',
                    options: {
                        precision: 2,
                        required: true,
                        onChange: function(newValue, oldValue) {
                            var dg = $('#dg2');
                            var row = dg.datagrid('getSelected');
                            var rowIndex = dg.datagrid('getRowIndex', row);

                            var mpq = dg.datagrid('getEditor', {
                                index: rowIndex,
                                field: 'mpq'
                            }).target.numberbox('getValue');

                            if (mpq && newValue) {
                                var qty_label = parseFloat(newValue) / parseFloat(mpq);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty_label'
                                });
                                $(ed.target).numberbox('setValue', qty_label);
                            }
                        }
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
                field: 'qty_label',
                width: 100,
                align: 'center',
                title: "QTY Label",
                editor: {
                    type: 'numberbox'
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
        var receipt_no = $("#receipt_no").textbox('getValue');
        if (receipt_no != "") {
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

        var receipt_no = $("#receipt_no").textbox('getValue');
        var item_rm_id = $(ed.target).textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('master/supplier_items/delete') ?>',
            data: {
                receipt_no: row.receipt_no,
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

    function receipt_no(date = "") {
        $.ajax({
            type: "post",
            url: "<?= base_url('purchase/purchase_order_receipt_crushers/receipt_no/') ?>" + window.btoa(date),
            dataType: "html",
            success: function(result) {
                $("#receipt_no").textbox('setValue', result);
            }
        });
    }

    //EDIT DATA
    // function update() {
    //     var row = $('#dg').treegrid('getSelected');
    //     if (row) {
    //         $('#dlg_insert').dialog('open');
    //         $('#frm_insert').form('load', row);
    //         $("#trans_date").datebox('disable');
    //         $("#receipt_no").textbox('disable');

    //         addTable('<?= base_url('purchase/purchase_order_receipt_crushers/datatableUpdates?receipt_no=') ?>' + window.btoa(row.receipt_no));
    //     } else {
    //         toastr.warning("Please select one of the data in the table first!", "Information");
    //     }
    // }

     //DELETE DATA
     function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        console.log(row);
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('purchase/purchase_order_receipt_crushers/delete') ?>',
                            data: {
                                receipt_id: row.receipt_id
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

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_receipt = $("#filter_receipt").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_receipt=" + window.btoa(filter_receipt);
        $('#dg').datagrid({
            url: '<?= base_url('purchase/purchase_order_receipt_crushers/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('purchase/purchase_order_receipt_crushers/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_receipt = $("#filter_receipt").combobox('getValue');
      
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_receipt=" + filter_receipt;
        window.location.assign('<?= base_url('purchase/purchase_order_receipt_crushers/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    // function readReceiptNo() {
    //     $("#filter_receipt").combobox({
    //         url: '<?= base_url('purchase/purchase_order_receipt_crushers/readReceiptNo') ?>',
    //         valueField: 'receipt_no',
    //         textField: 'receipt_no',
    //         prompt: "Select Receipt No",
    //         icons: [{
    //             iconCls: 'icon-clear',
    //             handler: function(e) {
    //                 $(e.data.target).combobox('clear').combobox('textbox').focus();
    //             }
    //         }],
    //     });
    // }

    $("#filter_receipt").combobox({
        url: '<?= base_url('purchase/purchase_order_receipt_crushers/readReceiptNo') ?>',
        valueField: 'receipt_no',
        textField: 'receipt_no',
        prompt: "Select Receipt No",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $(function() {
        //ADD DATA
        addTable();

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('purchase/purchase_order_receipt_crushers/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                console.log(row);
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.receipt_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                // var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('purchase/purchase_order_receipt_crushers/datatableDetails?receipt_no=') ?>' + window.btoa(row.receipt_no),
                    singleSelect: true,
                    rownumbers: true,
                    width: '1600px',
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
                            field: 'uom',
                            title: 'UoM',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'qty',
                            title: 'Qty',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'qty_label',
                            title: 'Qty Label',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'mpq',
                            title: 'MPQ',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'btn',
                            title: 'Label',
                            halign: 'center',
                            width: 80,
                            formatter: BtnPrintLabel
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

        $("#trans_date").datebox({
            onSelect: function(date) {
                receipt_no(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate());
            }
        });

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var receipt_no = $("#receipt_no").textbox('getValue');
                    var trans_date = $("#trans_date").datebox('getValue');

                    $("#dg2").datagrid('acceptChanges');
                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_rm_id) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('purchase/purchase_order_receipt_crushers/create') ?>',
                                data: {
                                    receipt_no: receipt_no,
                                    trans_date: trans_date,
                                    item_rm_id: rows[i].item_rm_id,
                                    item_supplier: rows[i].item_supplier,
                                    mpq: rows[i].mpq,
                                    qty: rows[i].qty,
                                    qty_label: rows[i].qty_label
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
        if (value != null) {
            if (row.total_scan == row.qty_label) {
                return "<b style='color:red;'>CLOSED</b>";
            } else {
                return "<b style='color:green;'>OPEN</b>";
            }
        }
    }

    function statusStyle(value, row, index) {
        if (value != null) {
            if (row.total_scan == row.qty_label) {
                return 'background-color:#FFC8C8;';
            } else {
                return 'background-color:#C8FFCC;';
            }
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

    function BtnPrintLabel(val, row) {
        console.log(row.receipt_id);
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onclick="printConfirmation(\'' + row.receipt_id + '\')"><i class="fa fa-print"></i> Print</a>';
    }

    function printConfirmation(receipt_id) {
        swal.fire({
            title: 'Confirmation',
            text: 'Are you sure want print this Label',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'YES',
            cancelButtonText: 'CANCEL'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika pengguna menekan tombol "Ya", lakukan pencetakan
                window.open('<?= base_url('purchase/purchase_order_receipt_crushers/print_label/') ?>' + window.btoa(receipt_id), '_blank');
            } else {
                window.location.reload();
            }
        });
    }



    function print_po(po) {
        console.log(po);
        var url = '<?= base_url('purchase/purchase_order_receipt_crushers/print_label_po/') ?>' + window.btoa(po.receipt_no);
        window.open(url, '_blank');
    }
</script>
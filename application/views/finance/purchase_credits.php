<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Document No</th>
            <th rowspan="2" data-options="field:'pr_no',width:150,halign:'center'">PR No</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'po_no',width:140,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'supplier_name',width:200,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'remarks',width:100,halign:'center',align:'right'">Remarks</th>
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

<!-- FORM FILTER DATAGRID -->
<div id="toolbar" style="height: 230px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->

    <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Transaction Date</span>
                <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Return</span>
                <input style="width:60%;" id="filter_purchase_return" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier</span>
                <input style="width:60%;" id="filter_supplier" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Order</span>
                <input style="width:60%;" id="filter_purchase_order" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1300px; height: 700px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purchase Return</span>
                        <input style="width:60%;" id="pr_no" name="pr_no" required class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Transaction Date</span>
                        <input style="width:60%;" id="trans_date" name="trans_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Supplier Name</span>
                        <input style="width:60%;" id="supplier_name" class="easyui-textbox" disabled data-options="prompt:'Automatic From Purchase Return'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Purchase Order</span>
                        <input style="width:60%;" id="po_no" name="po_no" class="easyui-textbox" readonly data-options="prompt:'Automatic From Purchase Return'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Document No</span>
                        <input style="width:60%;" id="number" name="number" class="easyui-textbox" readonly>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Remarks</span>
                        <input style="width:60%; height: 50px;" id="remarks" name="remarks" class="easyui-textbox" multiline="true">
                    </div>
                </div>
            </fieldset>
        </div>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="list Purchase Invoicing" data-options="singleSelect: false" idField="item_number">
            <thead>
                <tr>
                    <th field="ck" checkbox="true"></th>
                    <th data-options="field:'delete',width:50, formatter:removebtn">#</th>
                    <th data-options="field:'item_number',width:150">Product No</th>
                    <th data-options="field:'item_name',width:200">Product Name</th>
                    <th data-options="field:'currency',align:'center',width:80">Currency</th>
                    <th data-options="field:'price',halign:'center',align:'right',width:80">Unit Price</th>
                    <th data-options="field:'uom',align:'center',width:80">UoM</th>
                    <th data-options="field:'qty',width:80,halign:'center',align:'right', formatter:numberformat">Qty PO</th>
                    <th data-options="field:'returned',width:80,halign:'center',align:'right', formatter:numberformat">Qty Return</th>
                    <th data-options="field:'total',width:100,halign:'center', align:'right', formatter:numberformat">Sub Total</th>
                    <th data-options="field:'account_number',width:140, halign:'center', editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('finance/account_coa/reads') ?>',
                            valueField: 'account_number',
                            textField: 'account_name',
                            prompt: 'Choose Account No',
                        }}">Account No</th>
                    <th data-options="field:'account_type',width:120, halign:'center', editor: {
                        type: 'combobox',
                        options: {
                            data: [{
                                'id':'DEBIT'
                            },{
                                'id':'CREDIT'
                            }],
                            valueField: 'id',
                            textField: 'id',
                            prompt: 'Choose Debit/Credit',
                            panelHeight: 'auto'
                        }}">Debit/Credit</th>
                </tr>
            </thead>
        </table>

        <div style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: right; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <div style="width: 100%; float: left;">
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">GRAND TOTAL</b>
                        <input style="width:60%;" id="total_sub" name="total_sub" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                </div>
            </fieldset>
        </div>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');

        $("#trans_date").datebox({
            onChange: function(val) {
                number(val);
            }
        });

        $("#pr_no").combobox('enable');
        $("#trans_date").datebox('enable');
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#pr_no").combobox('disable');
            $("#trans_date").datebox('disable');

            var lastIndex;
            var dg = $('#dg2').datagrid({
                url: '<?= base_url('finance/purchase_credits/reads/') ?>' + window.btoa(row.number),
                onClickRow: function(rowIndex) {
                    if (lastIndex != rowIndex) {
                        $(this).datagrid('endEdit', lastIndex);
                        $(this).datagrid('beginEdit', rowIndex);
                    }
                    lastIndex = rowIndex;
                },
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //NOMOR AUTOMATIC
    function number(trans_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/purchase_credits/number/') ?>" + window.btoa(trans_date),
            dataType: "html",
            success: function(result) {
                $("#number").textbox('setValue', result);
            }
        });
    }

    function preview() {
        var pr_no = $("#pr_no").combobox('getValue');

        if (pr_no == "") {
            toastr.info('Please select purchase return');
        } else {
            var lastIndex;
            var dg = $('#dg2').datagrid({
                url: '<?= base_url('finance/purchase_credits/datatablesTemp') ?>?pr_no=' + window.btoa(pr_no),
                onLoadSuccess: function(row) {
                    $("#total_sub").numberbox('setValue', row.total_sub);
                },
                onClickRow: function(rowIndex) {
                    if (lastIndex != rowIndex) {
                        $(this).datagrid('endEdit', lastIndex);
                        $(this).datagrid('beginEdit', rowIndex);
                    }
                    lastIndex = rowIndex;
                },
            });
        }
    }

    function removebtn(value, row, index) {
        return "<a href='#' onclick='removeit(" + index + "," + row.total + ")' style='pointer-events:auto !important; opacity:1;' class='btn btn-sm btn-danger w-100'><i class='fa fa-times'></i></a>";
    }

    function removeit(indexs, total) {
        toastr.success('Deleted Success');
        var total_sub = $("#total_sub").numberbox('getValue');
        $("#total_sub").numberbox('setValue', (parseFloat(total_sub) - parseFloat(total)));
        $("#dg2").datagrid("deleteRow", indexs);
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
                            url: '<?= base_url('finance/purchase_credits/delete') ?>',
                            data: {
                                number: row.number
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
        var filter_purchase_return = $("#filter_purchase_return").combobox('getText');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_purchase_order = $("#filter_purchase_order").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_purchase_return=" + window.btoa(filter_purchase_return) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_purchase_order=" + window.btoa(filter_purchase_order) +
            "&filter_product_no=" + window.btoa(filter_product_no);

        $('#dg').datagrid({
            url: '<?= base_url('finance/purchase_credits/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/purchase_credits/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //EXPORT TO EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_purchase_return = $("#filter_purchase_return").combobox('getText');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_purchase_order = $("#filter_purchase_order").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_purchase_return=" + window.btoa(filter_purchase_return) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_purchase_order=" + window.btoa(filter_purchase_order) +
            "&filter_product_no=" + window.btoa(filter_product_no);

        window.location.assign('<?= base_url('finance/purchase_credits/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
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

    function numberformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('finance/purchase_credits/datatables') ?>',
            pagination: true,
            rownumbers: true,
            height: '810px',
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.number + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                ddv.datagrid({
                    url: '<?= base_url('finance/purchase_credits/datatables/details?number=') ?>' + window.btoa(row.number),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'item_number',
                            title: 'Product No',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 300
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'qty',
                            title: 'Qty PO',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'returned',
                            title: 'Returned',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'price',
                            title: 'Unit Price',
                            width: 120,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
                        }, {
                            field: 'total',
                            title: 'Total',
                            width: 150,
                            halign: 'center',
                            align: 'right',
                            formatter: priceformat
                        }, {
                            field: 'account_number',
                            title: 'Account',
                            width: 100,
                            align: 'center'
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
                    var pr_no = $("#pr_no").combobox('getValue');
                    var trans_date = $("#trans_date").datebox('getValue');
                    var number = $("#number").textbox('getValue');
                    var po_no = $("#po_no").textbox('getValue');
                    var remarks = $("#remarks").textbox('getValue');

                    var total_sub = $("#total_sub").numberbox('getValue');

                    if (pr_no == "" || trans_date == "") {
                        toastr.error("please complete your input data");
                    } else {
                        $('#dg2').datagrid('acceptChanges');
                        var rows = $('#dg2').datagrid('getSelections');
                        var totalrows = rows.length;

                        if (totalrows > 0) {
                            for (let i = 0; i < totalrows; i++) {
                                if (rows[i].item_id) {
                                    $.ajax({
                                        type: "post",
                                        url: '<?= base_url('finance/purchase_credits/create') ?>',
                                        data: {
                                            pr_no: pr_no,
                                            trans_date: trans_date,
                                            number: number,
                                            po_no: po_no,
                                            remarks: remarks,
                                            total_sub: total_sub,
                                            supplier_id: rows[i].supplier_id,
                                            item_id: rows[i].item_id,
                                            uom: rows[i].uom,
                                            currency: rows[i].currency,
                                            qty: rows[i].qty,
                                            price: rows[i].price,
                                            returned: rows[i].returned,
                                            total: rows[i].total,
                                            total_idr: rows[i].total_local,
                                            account_number: rows[i].account_number,
                                            account_type: rows[i].account_type,
                                        },
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                toastr.success(result.message, result.title);
                                            } else {
                                                toastr.error(result.message, result.title);
                                            }
                                        }
                                    });
                                }
                            }

                            $('#dg').datagrid('reload');
                            $('#dlg_insert').dialog('close');
                        } else {
                            toastr.warning("please selections your data in table first");
                        }
                    }
                }
            }]
        });

        //GET PURCHASE RETURN
        $("#pr_no").combobox({
            url: '<?= base_url('purchase/purchase_returns/readReturnNo') ?>',
            valueField: 'return_no',
            textField: 'return_no',
            prompt: "Choose Purchase Return",
            onSelect: function(pr) {
                $("#po_no").textbox('setValue', pr.po_no);
                $("#supplier_name").textbox('setValue', pr.supplier_name);
            }
        });

        //GET PURCHASE RETURN
        $("#filter_purchase_return").combobox({
            url: '<?= base_url('finance/purchase_credits/readReturnNo') ?>',
            valueField: 'pr_no',
            textField: 'pr_no',
            prompt: "Choose All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        //GET SUPPLIER
        $("#filter_supplier").combobox({
            url: '<?= base_url('master/suppliers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        //GET PURCHASE RETURN
        $("#filter_purchase_order").combobox({
            url: '<?= base_url('finance/purchase_credits/readPurchaseOrder') ?>',
            valueField: 'po_no',
            textField: 'po_no',
            prompt: "Choose All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row){
                $('#filter_product_no').combogrid({
                    url: '<?= base_url('finance/purchase_credits/readItems/') ?>' + btoa(row.po_no),
                    panelWidth: 420,
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose All",
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
            }
        });
    });

    function priceformat(value, row) {
        if (row.currency == "USD") {
            var digits = 2;
            var currency = 'USD';
            var format = "en-IN";
        } else if (row.currency == "JPY") {
            var digits = 2;
            var currency = 'JPY';
            var format = "ja-JP";
        } else if (row.currency == "EUR") {
            var digits = 2;
            var currency = 'EUR';
            var format = "de-DE";
        } else {
            var digits = 0;
            var currency = 'IDR';
            var format = "id-ID";
        }

        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: digits
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function priceformatlocal(value, row) {
        var digits = 0;
        var currency = 'IDR';
        var format = "id-ID";

        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: digits
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }
</script>
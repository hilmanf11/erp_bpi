<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Sales Tax No</th>
            <th rowspan="2" data-options="field:'sales_invoice_number',width:100,align:'center'">Sales Invoice No</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'taxes',width:80,halign:'center',align:'right'">Taxes %</th>
            <th rowspan="2" data-options="field:'due_date',width:100,align:'center'">Payment Due</th>
            <th rowspan="2" data-options="field:'currency',width:100,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'total_sub',width:120,halign:'center',align:'right',formatter: priceformat">Sub Total</th>
            <th rowspan="2" data-options="field:'total_tax',width:120,halign:'center',align:'right',formatter: priceformat">Tax Base</th>
            <th rowspan="2" data-options="field:'total_vat',width:120,halign:'center',align:'right',formatter: priceformat">VAT</th>
            <th rowspan="2" data-options="field:'total_pph',width:120,halign:'center',align:'right',formatter: priceformat">PPH</th>
            <th rowspan="2" data-options="field:'total_grand',width:120,halign:'center',align:'right',formatter: priceformat">Grand Total</th>
            <th rowspan="2" data-options="field:'total_grand_idr',width:120,halign:'center',align:'right',formatter: priceformat">Grand Total IDR</th>
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center'">Remarks</th>
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
<div id="toolbar" style="height: 190px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->

    <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Tax Date</span>
                <input style="width:30%;" id="filter_trans_date_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_trans_date_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Tax No</span>
                <input style="width:60%;" name="filtar_sales_tax" id="filtar_sales_tax" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Invoice No</span>
                <input style="width:60%;" name="filter_sales_invoice" id="filter_sales_invoice" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" name="filter_customer" id="filter_customer" class="easyui-combobox">
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1200px; height: 700px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <legend><b>Form Data</b></legend>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Sales Tax Date</span>
                        <input style="width:60%;" id="trans_date" name="trans_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Sales Tax No</span>
                        <input style="width:60%;" readonly id="number" name="number" class="easyui-textbox" data-options="prompt:'Automatic From Purchase Invoce Date'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                    </div>
                </div>
                <div style="width: 50%; float: left;">
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Sales Tax Type</span>
                        <select style="width:60%;" required="" id="sales_type_tax" name="sales_type_tax" class="easyui-combobox" panelHeight="auto">
                            <option value="NEW">NEW</option>
                            <option value="REVISE">REVISE</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Sales Invoice No</span>
                        <input style="width:60%;" required="" id="sales_invoice_no" name="sales_invoice_no" class="easyui-combobox">
                    </div>
                </div>
            </fieldset>
        </div>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="list Sales Invoicing" data-options="singleSelect: false" idField="customer_po">
            <thead>
                <tr>
                    <th field="ck" checkbox="true"></th>
                    <th data-options="field:'delete',width:50, formatter:removebtn">#</th>
                    <th data-options="field:'si_number',width:150">Sales Invoice No</th>
                    <th data-options="field:'dn_number',width:160">Delivery Note</th>
                    <th data-options="field:'item_id',width:150" hidden>Product Id</th>
                    <th data-options="field:'item_number',width:150">Product No</th>
                    <th data-options="field:'item_name',width:200">Product Name</th>
                    <th data-options="field:'uom',width:80">UoM</th>
                    <th data-options="field:'currency',width:80">Currency</th>
                    <th data-options="field:'qty',width:80, formatter:numberformat">Qty</th>
                    <th data-options="field:'price',width:80, halign:'center', align:'right', formatter:priceformat">Price</th>
                    <th data-options="field:'total',width:120, formatter:priceformat, halign:'center', align:'right'">Amount</th>
                </tr>
            </thead>
        </table>

        <div style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: right; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <div style="width: 100%; float: left;">
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">SUB TOTAL</b>
                        <input style="width:60%;" id="total_sub" name="total_sub" readonly class="easyui-numberbox" data-options="precision:4,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">VAT</b>
                        <input style="width:60%;" id="total_vat" name="total_vat" readonly class="easyui-numberbox" data-options="precision:4,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">PPH</b>
                        <input style="width:30%;" id="total_pph" name="total_pph" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                        <select style="width:30%;" id="pph" name="pph" class="easyui-combobox" required data-options="prompt: 'PPH'" panelHeight="auto">
                            <option value="0">NON PPH</option>
                            <option value="5">PPH 21</option>
                            <option value="2">PPH 23</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">GRAND TOTAL</b>
                        <input style="width:60%;" id="total_grand" name="total_grand" readonly required class="easyui-numberbox" data-options="precision:4,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">CONVERT IDR</b>
                        <input style="width:60%;" id="total_local" name="total_local" disabled class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
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
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //NOMOR AUTOMATIC
    function number(trans_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/sales_invoice_taxs/number/') ?>" + window.btoa(trans_date),
            dataType: "html",
            success: function(result) {
                $("#number").textbox('setValue', result);
            }
        });
    }

    function preview() {
        var dn_number = $("#dn_number").combobox('getValue');
        var trans_date = $("#trans_date").datebox('getValue');
        var due_date = $("#due_date").datebox('getValue');
        var taxes = $("#taxes").combobox('getValue');

        if (dn_number == "" || trans_date == "" || due_date == "" || taxes == "") {
            toastr.info('Please completed your data');
        } else {
            var lastIndex;
            var dg = $('#dg2').datagrid({
                url: '<?= base_url('finance/sales_invoice_taxs/datatablesTemp') ?>?dn_number=' + window.btoa(dn_number),
                onLoadSuccess: function(row) {
                    $("#total_sub").numberbox('setValue', row.total_sub);
                    var disc_tax = parseFloat(row.total_sub * (taxes / 100));
                    $("#total_vat").numberbox('setValue', disc_tax);

                    $("#pph").combobox('clear');
                    $("#total_pph").numberbox('clear');
                    $("#total_grand").numberbox('clear');
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

        var taxes = $("#taxes").combobox('getValue');
        var disc_tax = parseFloat((parseFloat(total_sub) - parseFloat(total)) * parseFloat(taxes / 100));
        $("#total_vat").numberbox('setValue', disc_tax);

        $("#pph").combobox('clear');
        $("#total_pph").numberbox('clear');
        $("#total_grand").numberbox('clear');

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
                            url: '<?= base_url('finance/sales_invoice_taxs/delete') ?>',
                            data: {
                                number: row.number,
                                dn_number: row.dn_number,
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
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_due_date_from = $("#filter_due_date_from").datebox('getValue');
        var filter_due_date_to = $("#filter_due_date_to").datebox('getValue');
        var filter_sales_invoice = $("#filter_sales_invoice").combobox('getValue');
        var filter_dn_number = $("#filter_dn_number").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_sales_invoice=" + window.btoa(filter_sales_invoice) +
            "&filter_dn_number=" + window.btoa(filter_dn_number) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_status=" + window.btoa(filter_status);

        $('#dg').datagrid({
            url: '<?= base_url('finance/sales_invoice_taxs/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/sales_invoice_taxs/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //EXPORT TO EXCEL
    function excel() {
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_trans_date_from = $("#filter_trans_date_from").datebox('getValue');
        var filter_trans_date_to = $("#filter_trans_date_to").datebox('getValue');
        var filter_due_date_from = $("#filter_due_date_from").datebox('getValue');
        var filter_due_date_to = $("#filter_due_date_to").datebox('getValue');
        var filter_sales_invoice = $("#filter_sales_invoice").combobox('getValue');
        var filter_dn_number = $("#filter_dn_number").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_trans_date_from=" + window.btoa(filter_trans_date_from) +
            "&filter_trans_date_to=" + window.btoa(filter_trans_date_to) +
            "&filter_due_date_from=" + window.btoa(filter_due_date_from) +
            "&filter_due_date_to=" + window.btoa(filter_due_date_to) +
            "&filter_sales_invoice=" + window.btoa(filter_sales_invoice) +
            "&filter_dn_number=" + window.btoa(filter_dn_number) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('finance/sales_invoice_taxs/print/excel') ?>' + url);
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
            url: '<?= base_url('finance/sales_invoice_taxs/datatables') ?>',
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
                    url: '<?= base_url('finance/sales_invoice_taxs/datatables/details?number=') ?>' + window.btoa(row.number),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'dn_number',
                            title: 'Delivery Note',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'so_number',
                            title: 'Sales Order',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'customer_po',
                            title: 'Customer PO',
                            halign: 'center',
                            width: 120
                        },{
                            field: 'item_no',
                            title: 'Product No',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 300
                        }, {
                            field: 'qty',
                            title: 'Qty',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                            formatter: numberformat
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'price',
                            title: 'Unit Price',
                            width: 150,
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
                    var number = $("#number").textbox('getValue');
                    var customer_id = $("#customer_id").combogrid('getValue');
                    var taxes = $("#taxes").combobox('getValue');
                    var payment_term = $("#payment_term").numberbox('getValue');
                    var due_date = $("#due_date").datebox('getValue');
                    var remarks = $("#remarks").textbox('getValue');

                    var total_sub = $("#total_sub").numberbox('getValue');
                    var total_vat = $("#total_vat").numberbox('getValue');
                    var total_pph = $("#total_pph").numberbox('getValue');
                    var total_grand = $("#total_grand").numberbox('getValue');
                    var total_local = $("#total_local").numberbox('getValue');

                    if (due_date == "" || trans_date == "" || customer_id == "" || total_local == "") {
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
                                        url: '<?= base_url('finance/sales_invoice_taxs/create') ?>',
                                        data: {
                                            trans_date: trans_date,
                                            number: number,
                                            customer_id: customer_id,
                                            taxes: taxes,
                                            payment_term: payment_term,
                                            due_date: due_date,
                                            remarks: remarks,
                                            total_sub: total_sub,
                                            total_vat: total_vat,
                                            total_pph: total_pph,
                                            total_grand: total_grand,
                                            total_local: total_local,
                                            dn_number: rows[i].dn_number,
                                            so_number: rows[i].so_number,
                                            customer_po: rows[i].customer_po,
                                            item_id: rows[i].item_id,
                                            item_no: rows[i].item_number,
                                            item_name: rows[i].item_name,
                                            uom: rows[i].uom,
                                            currency: rows[i].currency,
                                            qty: rows[i].qty,
                                            price: rows[i].price,
                                            total: rows[i].total,
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

                            toastr.success("Save data success", "Good Job");
                            $('#dg').datagrid('reload');
                            $('#dlg_insert').dialog('close');
                        } else {
                            toastr.warning("please selections your data in table first");
                        }
                    }
                }
            }]
        });

        $("#filter_customer").combobox({
            url: '<?= base_url('master/customers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Customers",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_sales_invoice").combobox({
            url: '<?= base_url('finance/sales_invoice_taxs/readSalesInvoices') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Choose Sales Invoice No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_dn_number").combobox({
            url: '<?= base_url('finance/sales_invoice_taxs/readDeliveryNote') ?>',
            valueField: 'dn_number',
            textField: 'dn_number',
            prompt: "Choose Delivery Note",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#pph").combobox({
            onChange: function(e){
                var customer_id = $("#customer_id").combogrid('getValue');
                var total_sub = $("#total_sub").numberbox('getValue');
                var total_vat = $("#total_vat").numberbox('getValue');
                var pph = $("#pph").combobox('getValue');
                var total_pph = parseFloat(total_sub * (pph / 100));
                $("#total_pph").numberbox('setValue', total_pph);

                var grand_total = parseFloat(total_sub - total_vat - total_pph);
                $("#total_grand").numberbox('setValue', grand_total);

                $.ajax({
                    type: "post",
                    url: "<?= base_url('finance/sales_invoice_taxs/readExchangeRates?customer_id=') ?>" + customer_id,
                    dataType: "json",
                    success: function (exchange) {
                        $("#total_local").numberbox('setValue', (grand_total * exchange[0].selling));
                    }
                });
            }
        })

    });

    function priceformat(value, row) {
        if (row.currency == "USD") {
            var digits = 4;
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
            const formatter = new Intl.NumberFormat('id-ID', {
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
</script>
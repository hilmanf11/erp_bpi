<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:180,halign:'center'">PL No</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">PL Date</th>
            <th rowspan="2" data-options="field:'dn_number',width:150,align:'center'">DN No</th>
            <th rowspan="2" data-options="field:'si_number',width:150,align:'center'">Sales Invoice No</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatDefault">Qty</th>
            <th rowspan="2" data-options="field:'pallet_no',width:80,halign:'center',align:'right',formatter:numberformatDefault">Pallet<br>No</th>
            <th rowspan="2" data-options="field:'carton',width:80,halign:'center',align:'right',formatter:numberformatDefault">Qty<br>Carton</th>
            <th rowspan="2" data-options="field:'net_weight',width:80,halign:'center',align:'right',formatter:numberformatDefault">Net<br>Weight</th>
            <th rowspan="2" data-options="field:'gross_weight',width:80,halign:'center',align:'right',formatter:numberformatDefault">Gross<br>Weight</th>
            <th rowspan="2" data-options="field:'measure',width:80,halign:'center',align:'right',formatter:numberformatDefault">Measure</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
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
<div id="toolbar" style="height: 235px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 35%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:28%;" id="filter_from" value="<?= date("Y-m-d") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" value="<?= date("Y-m-d") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" id="filter_customer" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Packing List</span>
                <input style="width:60%;" id="filter_packing_list" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_dn()"><i class="fa fa-print"></i> Packing List</a>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add Packing List" data-options="closed: true,modal:true" style="width: 1200px; height: 500px; padding:10px; top: 10px; left: 10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width: 40%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PL Date</span>
                    <input style="width:60%;" name="trans_date" id="trans_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PL No</span>
                    <input style="width:60%;" name="number" id="number" required="" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Note</span>
                    <input style="width:60%;" name="dn_number" id="dn_number" required class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" id="preview" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
        </fieldset>
        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Packing List" data-options="fitColumns: true, rownumbers: true">
        </table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('sales/packing_lists/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg_request').datagrid('loadData', []);

        $("#trans_date").datebox('enable');
        $("#preview").linkbutton('enable');

        $("#dn_number").combobox({
            url: '<?= base_url('sales/packing_lists/readDeliveryNote') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Choose Delivery Note",
            onSelect: function(row){
                var trans_date = $("#trans_date").datebox('getValue');
                number(trans_date, row.nickname);
            }
        });
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if (row.state == "closed") {
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                $("#trans_date").datebox('disable');
                $("#preview").linkbutton('disable');

                preview('<?= base_url('sales/packing_lists/datatableUpdate') ?>?number=' + window.btoa(row.number));
            } else {
                toastr.warning("Please select header the data in the table first!");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //NOMOR AUTOMATIC
    function number(trans_date, customer_id) {
        $.ajax({
            type: "post",
            url: "<?= base_url('sales/packing_lists/number/') ?>" + window.btoa(trans_date) + "/" + customer_id,
            dataType: "html",
            success: function(result) {
                $("#number").textbox('setValue', result);
            }
        });
    }

    function preview(link = "") {
        var dn_number = $("#dn_number").combobox('getValue');

        if (link == "") {
            var links = '<?= base_url('sales/delivery_notes/reads') ?>?number=' + dn_number;
        } else {
            var links = link;
        }

        if (dn_number == "") {
            toastr.warning('Please select Delivery Note No', 'Required');
        } else {
            var lastIndex;
            $('#dg_request').datagrid({
                url: links,
                columns: [
                    [{
                        field: 'ck',
                        checkbox: true,
                    }, {
                        field: 'customer_po',
                        width: 150,
                        hidden: true,
                        halign: 'center',
                        title: "Customer PO"
                    }, {
                        field: 'item_id',
                        width: 150,
                        hidden: true,
                        halign: 'center',
                        title: "Product ID"
                    }, {
                        field: 'item_number',
                        width: 150,
                        halign: 'center',
                        title: "Product No",
                    }, {
                        field: 'item_name',
                        width: 150,
                        halign: 'center',
                        title: "Product Name"
                    }, {
                        field: 'uom',
                        width: 80,
                        halign: 'center',
                        title: "UoM",
                    }, {
                        field: 'qty',
                        width: 100,
                        halign: 'center',
                        align: 'right',
                        title: "Qty",
                    }, {
                        field: 'pallet_no',
                        width: 100,
                        halign: 'center',
                        align: 'right',
                        title: "Pallet Qty",
                        editor: {
                            type: 'numberbox',
                            options: {
                                precision: 2
                            }
                        }
                    }, {
                        field: 'carton',
                        width: 100,
                        halign: 'center',
                        align: 'right',
                        title: "Carton Qty",
                        editor: {
                            type: 'numberbox',
                            options: {
                                precision: 2
                            }
                        }
                    }, {
                        field: 'net_weight',
                        width: 100,
                        halign: 'center',
                        align: 'right',
                        title: "Net Weight",
                        editor: {
                            type: 'numberbox',
                            options: {
                                precision: 2
                            }
                        }
                    }, {
                        field: 'gross_weight',
                        width: 100,
                        halign: 'center',
                        align: 'right',
                        title: "Gross Weight",
                        editor: {
                            type: 'numberbox',
                            options: {
                                precision: 2
                            }
                        }
                    }, {
                        field: 'measure',
                        width: 100,
                        halign: 'center',
                        title: "Measure",
                        editor: {
                            type: 'numberbox',
                            options: {
                                precision: 2
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
                onClickRow: function(rowIndex) {
                    $(this).datagrid('beginEdit', rowIndex);
                },
            });
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
                        if (row.state != "closed") {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('sales/packing_lists/delete') ?>',
                                data: {
                                    id: row.id,
                                    dn_number: row.dn_number,
                                    item_id: row.item_id,
                                    customer_id: row.customer_id
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
                        } else {
                            toastr.error("Please Select Detail of Data Packing Lists " + row.number);
                        }
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
        var filter_packing_list = $("#filter_packing_list").combogrid('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_packing_list=" + filter_packing_list + "&filter_customer=" + filter_customer;

        $('#dg').treegrid({
            url: '<?= base_url('sales/packing_lists/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'number',
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
        $("#printout").attr('src', '<?= base_url('sales/packing_lists/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_packing_list = $("#filter_packing_list").combogrid('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_packing_list=" + filter_packing_list + "&filter_customer=" + filter_customer;
        window.location.assign('<?= base_url('sales/packing_lists/print/excel') ?>' + url);
    }

    function print_dn() {
        var dn_no = $("#filter_packing_list").combogrid('getValue');
        if (dn_no == "") {
            toastr.warning("Please select Packing List!", "Information");
        } else {
            window.open("<?= base_url('sales/packing_lists/print_dn/') ?>" + window.btoa(dn_no), "_blank");
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $("#add").html("Create Packing List");

        filter();

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#dg_request').datagrid('acceptChanges');
                    var rows = $('#dg_request').datagrid('getSelections');
                    if (rows.length > 0) {
                        $.messager.confirm('Warning', 'Are you sure you want to Create Packing Lists?', function(r) {
                            if (r) {
                                for (var i = 0; i < rows.length; i++) {
                                    var row = rows[i];
                                    var editors = $('#dg_request').datagrid('getEditors', i);
                                    var number = $("#number").textbox('getValue');
                                    var trans_date = $("#trans_date").datebox('getValue');
                                    var dn_number = $("#dn_number").combobox('getValue');

                                    $.ajax({
                                        type: "post",
                                        url: '<?= base_url('sales/packing_lists/create') ?>',
                                        data: 'number=' + number +
                                            '&trans_date=' + trans_date +
                                            '&dn_number=' + dn_number +
                                            '&customer_id=' + row.customer_id +
                                            '&customer_po=' + row.customer_po +
                                            '&item_id=' + row.item_id +
                                            '&pallet_no=' + row.pallet_no +
                                            '&carton=' + row.carton +
                                            '&net_weight=' + row.net_weight +
                                            '&gross_weight=' + row.gross_weight +
                                            '&measure=' + row.measure +
                                            '&qty=' + row.qty,
                                        dataType: "json",
                                        success: function(result) {
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
                                    });
                                }

                                $('#dg').treegrid('reload');
                                $('#dlg_insert').dialog('close');
                            }
                        });
                    } else {
                        toastr.warning("Please select one of the data in the table first!", "Information");
                    }
                }
            }]
        });

        $("#filter_customer").combobox({
            url: '<?= base_url('master/customers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Customer",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(customer) {
                $("#filter_packing_list").combobox({
                    url: '<?= base_url('sales/packing_lists/readPackingLists/') ?>' + customer.id,
                    valueField: 'number',
                    textField: 'number',
                    prompt: "Select Packing List",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
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
    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg_request').datagrid('validateRow', editIndex)) {
            $('#dg_request').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function numberformatDefault(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformat(value, row) {
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
            const formatter = new Intl.NumberFormat(format, {
                style: 'currency',
                currency: currency,
                minimumFractionDigits: digits
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
</script>
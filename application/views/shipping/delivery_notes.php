<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:180,halign:'center'">DN No</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center',formatter:statusformat,styler:statusStyle">Status</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">DN Date</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer</th>
            <th rowspan="2" data-options="field:'customer_po',width:120,halign:'center'">Customer PO</th>
            <th rowspan="2" data-options="field:'do_number',width:150,halign:'center'">DO No</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatDefault">Qty</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'remarks',width:100,halign:'center'">Note</th>
            <th rowspan="2" data-options="field:'bc_kind',width:100,halign:'center'">Customs Type</th>
            <th rowspan="2" data-options="field:'bc_no',width:150,halign:'center'">Customs No</th>
            <th rowspan="2" data-options="field:'bc_date',width:120,halign:'center'">Customs Date</th>
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
<div id="toolbar" style="height: 225px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 35%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" id="filter_customer" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery Note</span>
                <input style="width:60%;" id="filter_dn_no" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_dn()"><i class="fa fa-print"></i> Delivery Note</a>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>
<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add Delivery Note" data-options="closed: true,modal:true" style="width: 1200px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width: 40%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">DN Date</span>
                    <input style="width:60%;" id="trans_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">DN No</span>
                    <input style="width:60%;" id="number" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order No</span>
                    <input style="width:60%;" name="do_number" id="do_number" required class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="float: left; width: 30%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Type</span>
                    <input style="width:60%;" name="trans_type" id="trans_type" readonly required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Country of Origin</span>
                    <input style="width:60%;" name="origin" id="origin" value="INDONESIA" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sailing On</span>
                    <input style="width:60%;" name="sailing" id="sailing" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Ship By</span>
                    <select style="width:60%;" name="ship" id="ship" class="easyui-combobox" panelHeight="auto">
                        <option value="SEA">SEA</option>
                        <option value="AIR">AIR</option>
                        <option value="TRUCK">TRUCK</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Incoterms</span>
                    <select style="width:60%;" name="incoterm" id="incoterm" class="easyui-combobox" panelHeight="auto">
                        <option value="NONE">NONE</option>
                        <option value="CIF">CIF</option>
                        <option value="EXW">EXW</option>
                        <option value="FOB">FOB</option>
                    </select>
                </div>
            </div>
            <div style="float: left; width: 30%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customs Kind</span>
                    <input style="width:60%;" name="bc_kind" id="bc_kind" required class="easyui-combobox" panelHeight="auto">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customs No</span>
                    <input style="width:60%;" name="bc_no" id="bc_no" required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customs Date</span>
                    <input style="width:60%;" name="bc_date" id="bc_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Notes</span>
                    <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Delivery Note List" data-options="fitColumns: true, rownumbers: true">
        </table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('shipping/delivery_notes/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg_request').datagrid('loadData', []);

        $("#bc_kind").combobox({
            url: '<?= base_url('master/bc_kind/reads/OUTGOING') ?>',
            valueField: 'name',
            textField: 'name',
            prompt: "Select Custom Kind",
            panelHeight: "auto"
        });

        $("#customer_id").combobox({
            url: '<?= base_url('master/customers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Customer",
            onSelect: function(customer) {
                $("#do_number").combobox({
                    url: '<?= base_url('shipping/delivery_notes/readDeliveryOrder/') ?>' + customer.id,
                    valueField: 'number',
                    textField: 'number',
                    multiple: true,
                    prompt: "Choose Delivery No",
                    onSelect: function(do_number) {
                        $("#trans_type").textbox('setValue', do_number.trans_type);
                        $("#remarks").textbox('setValue', do_number.note);
                    }
                });
            }
        });
    }

    //NOMOR AUTOMATIC
    function number(trans_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('shipping/delivery_notes/number/') ?>" + window.btoa(trans_date),
            dataType: "html",
            success: function(result) {
                $("#number").textbox('setValue', result);
            }
        });
    }

    function preview() {
        var do_number = $("#do_number").combobox('getText');
        if (do_number == "") {
            toastr.warning('Please select Delivery Order No', 'Required');
        } else {
            var lastIndex;
            $('#dg_request').datagrid({
                url: '<?= base_url('shipping/delivery_orders/reads') ?>?number=' + window.btoa(do_number),
                columns: [
                    [{
                        field: 'ck',
                        checkbox: true,
                    }, {
                        field: 'customer_po',
                        width: 120,
                        halign: 'center',
                        title: "Customer PO",
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
                    }]
                ],
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
                        if (row.status == "0") {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('shipping/delivery_notes/delete') ?>',
                                data: {
                                    id: row.id,
                                    do_number: row.do_number,
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
                            toastr.error("You cannot update this data, because status PO is closed");
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
        var filter_dn_no = $("#filter_dn_no").combogrid('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_dn_no=" + filter_dn_no + "&filter_customer=" + filter_customer;
        $('#dg').treegrid({
            url: '<?= base_url('shipping/delivery_notes/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('shipping/delivery_notes/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_dn_no = $("#filter_dn_no").combogrid('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_dn_no=" + filter_dn_no + "&filter_customer=" + filter_customer;
        window.location.assign('<?= base_url('shipping/delivery_notes/print/excel') ?>' + url);
    }

    function print_dn() {
        var dn_no = $("#filter_dn_no").combogrid('getValue');
        if (dn_no == "") {
            toastr.warning("Please select Delivery Note!", "Information");
        } else {
            window.open("<?= base_url('shipping/delivery_notes/print_dn/') ?>" + window.btoa(dn_no), "_blank");
        }
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        $("#add").html("Create Shipping");
        number("<?= date("Y-m-d") ?>");
        $("#trans_date").datebox({
            onChange: function(date) {
                var trans_date = $("#trans_date").datebox('getValue');
                number(trans_date);
            }
        });

        $('#dg').treegrid({
            url: '<?= base_url('shipping/delivery_notes/datatables') ?>',
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'number',
            singleSelect: false,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
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
                    $('#dg_request').datagrid('acceptChanges');
                    var bc_kind = $("#bc_kind").combobox('getValue');
                    var bc_no = $("#bc_no").textbox('getValue');
                    var bc_date = $("#bc_date").datebox('getValue');

                    var rows = $('#dg_request').datagrid('getSelections');
                    if (rows.length > 0) {
                        if(bc_kind != "" && bc_no != ""){
                            $.messager.confirm('Warning', 'Are you sure you want to Create Shipping?', function(r) {
                                if (r) {
                                    for (var i = 0; i < rows.length; i++) {
                                        var row = rows[i];
                                        var editors = $('#dg_request').datagrid('getEditors', i);
                                        var number = $("#number").textbox('getValue');
                                        var trans_date = $("#trans_date").datebox('getValue');
                                        var customer_id = $("#customer_id").combobox('getValue');
                                        var do_number = $("#do_number").combobox('getValue');
                                        var trans_type = $("#trans_type").textbox('getValue');
                                        var origin = $("#origin").textbox('getValue');
                                        var sailing = $("#sailing").textbox('getValue');
                                        var ship = $("#ship").combobox('getValue');
                                        var incoterm = $("#incoterm").combobox('getValue');
                                        var remarks = $("#remarks").textbox('getValue');

                                        $.ajax({
                                            type: "post",
                                            url: '<?= base_url('shipping/delivery_notes/create') ?>',
                                            data: 'number=' + number +
                                                '&remarks=' + remarks +
                                                '&trans_date=' + trans_date +
                                                '&customer_id=' + customer_id +
                                                '&do_number=' + do_number +
                                                '&trans_type=' + trans_type +
                                                '&customer_po=' + row.customer_po +
                                                '&item_id=' + row.item_id +
                                                '&origin=' + origin +
                                                '&sailing=' + sailing +
                                                '&ship=' + ship +
                                                '&incoterm=' + incoterm +
                                                '&bc_kind=' + bc_kind +
                                                '&bc_no=' + bc_no +
                                                '&bc_date=' + bc_date +
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
                            toastr.warning("Please completed your data");
                        }
                    } else {
                        toastr.warning("Please select one of the data in the table first!");
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
                $("#filter_dn_no").combobox({
                    url: '<?= base_url('shipping/delivery_notes/readDeliveryNote/') ?>' + customer.id,
                    valueField: 'number',
                    textField: 'number',
                    prompt: "Select Delivery Note",
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
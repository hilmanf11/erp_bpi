<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:150,align:'center'" sortable="true">Checksheet ID</th>
            <th rowspan="2" data-options="field:'wo_no',width:120,align:'center'" sortable="true">Wo No</th>
            <th rowspan="2" data-options="field:'trans_date',width:80,align:'center'" sortable="true">Trans Date</th>
            <!-- <th rowspan="2" data-options="field:'wp',width:80,align:'center'" sortable="true">WP</th> -->
            <th rowspan="2" data-options="field:'product_id',width:150,align:'center'" sortable="true">Product Id</th>
            <th rowspan="2" data-options="field:'product_no',width:150,align:'center'" sortable="true">Product No</th>
            <th rowspan="2" data-options="field:'product_name',width:200,align:'center'" sortable="true">Product Name</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'" sortable="true">Uom</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat" sortable="true">Qty</th>
            <th rowspan="2" data-options="field:'receipt',width:80,halign:'center',align:'right',formatter:numberformat" sortable="true">Receipt</th>
            <th rowspan="2" data-options="field:'accumulate',width:80,halign:'center',align:'right',formatter:numberformat" sortable="true">Accumulate</th>
            <th rowspan="2" data-options="field:'balance',width:80,halign:'center',align:'right',formatter:numberformat" sortable="true">Balance</th>
            <th rowspan="2" data-options="field:'prod_date',width:80,align:'center'" sortable="true">Prod Date</th>
            <th rowspan="2" data-options="field:'packing_date',width:100,align:'center'" sortable="true">Packing Date</th>
            <th rowspan="2" data-options="field:'qc_1',width:100,align:'center'" sortable="true">QC 1</th>
            <th rowspan="2" data-options="field:'qc_2',width:100,align:'center'" sortable="true">QC 2</th>
            <th rowspan="2" data-options="field:'op_1',width:100,align:'center'" sortable="true">Operator 1</th>
            <th rowspan="2" data-options="field:'op_2',width:100,align:'center'" sortable="true">Operator 2</th>
            <th rowspan="2" data-options="field:'shift',width:80,align:'center'" sortable="true">Shift</th>
            <th rowspan="2" data-options="field:'packing',width:80,align:'center',formatter:packingformat" sortable="true">Packing</th>
            <th rowspan="2" data-options="field:'packing_qty',width:80,align:'center'" sortable="true">Packing Qty</th>
            <th rowspan="2" data-options="field:'label',width:80,align:'center',formatter:BtnPrint">Print</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformat,styler:statusStyle" sortable="true">Status</th>
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
<div id="toolbar" style="height: 230px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 35%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <!-- <div class="fitem">
                <span style="width:35%; display:inline-block;">Workorder</span>
                <input style="width:60%;" id="filter_workorder" class="easyui-combogrid">
            </div> -->
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Wo No</span>
                <input style="width:60%;" id="filter_wo_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Checksheet</span>
                <input style="width:60%;" id="filter_checksheet" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>
<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:60%;" name="trans_date" id="trans_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Wo No</span>
                    <input style="width:60%;" name="wo_no" id="wo_no" class="easyui-combogrid">
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">WP</span>
                    <input style="width:20%;" name="wp" id="wp" required="" readonly="" class="easyui-textbox">
                    <input style="width:40%;" name="period" id="period" required="" disabled="" class="easyui-textbox">
                </div> -->
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Order No</span>
                    <input style="width:60%;" id="so_number" disabled class="easyui-textbox">
                </div> -->
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" id="customer" disabled class="easyui-textbox">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product ID</span>
                    <input style="width:60%;" id="item_fg_id" name="item_fg_id" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Name</span>
                    <input style="width:60%;" id="product_name" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="product_no" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">WO Qty</span>
                    <input style="width:30%;" name="qty" id="qty" required="" readonly="" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt Qty</span>
                    <input style="width:30%;" name="receipt" id="receipt"  required="" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Accumulate</span>
                    <input style="width:30%;" name="accumulate" id="accumulate" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Balance Qty</span>
                    <input style="width:30%;" name="balance" id="balance"  readonly class="easyui-numberbox">
                </div>
            </div>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Prod Date</span>
                    <input style="width:60%;" name="prod_date" id="prod_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Packing Date</span>
                    <input style="width:60%;" name="packing_date" id="packing_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">QC 1</span>
                    <input style="width:60%;" name="qc_1" id="qc_1"  required="" class="easyui-combobox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">QC 1</span>
                    <input style="width:60%;" name="qcnumber_1" id="qcnumber_1" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">QC 2</span>
                    <input style="width:60%;" name="qc_2" id="qc_2" class="easyui-combobox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">QC 2</span>
                    <input style="width:60%;" name="qcnumber_2" id="qcnumber_2" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Operator 1</span>
                    <input style="width:60%;" name="op_1" id="op_1" required="" class="easyui-combobox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">OP 1</span>
                    <input style="width:60%;" name="opnumber_1" id="opnumber_1" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Operator 2</span>
                    <input style="width:60%;" name="op_2" id="op_2" class="easyui-combobox">
                </div> 
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">OP 2</span>
                    <input style="width:60%;" name="opnumber_2" id="opnumber_2" class="easyui-textbox">
                </div>
                <div class="fitem">
                <span style="width:35%; display:inline-block;">Shift</span>
                    <select style="width:60%;" name="shift" id="shift" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
                <div class="fitem">
                <span style="width:35%; display:inline-block;">Packing Qty</span>
                    <select style="width:40%;" name="packing" id="packing" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="1">MPQ</option>
                        <option value="2">Qty per BOX</option>
                        <option value="3">User Entry</option>
                    </select>
                    <input style="width:20%;" name="packing_qty" id="packing_qty" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/checksheets/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('planning/checksheets/create') ?>';
        $('#frm_insert').form('clear');
        $("#trans_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#prod_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#packing_date").datebox('setValue', "<?= date("Y-m-d") ?>");

        $('#wo_no').combogrid({
            url: '<?= base_url('planning/checksheets/readWoNo') ?>',
            panelWidth: 350,
            idField: 'wo_no',
            textField: 'wo_no',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Wo No",
            columns: [
                [{
                    field: 'period',
                    title: 'Period',
                    width: 150
                }, {
                    field: 'wo_no',
                    title: 'Wo No',
                    width: 200,
                    align: 'left'
                }]
            ],
            onSelect: function(val, row) {
                console.log(row);
                $("#period").textbox('setValue', row.period);
                $("#item_fg_id").textbox('setValue', row.item_fg_id);
                $("#product_no").textbox('setValue', row.product_no);
                $("#product_name").textbox('setValue', row.product_name);
                $("#qty").numberbox('setValue', row.qty);
                // $("#receipt").numberbox('setValue', row.balance);
                $("#balance").textbox('setValue', '0');

                var wo_no = row.wo_no;
                console.log(wo_no);
                $.ajax({
                    url: '<?= base_url("planning/checksheets/checkWo_no/") ?>' + window.btoa(wo_no), 
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);
                        accumulateAjax = data[0].qty;
                        $("#accumulate").numberbox('setValue', data[0].qty);
                    }
                });
            }
        });
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('planning/checksheets/delete') ?>',
                            data: {
                                id: row.id,
                                so_number: row.so_number
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
        var filter_wo_no = $("#filter_wo_no").combobox('getValue');
        var filter_checksheet = $("#filter_checksheet").combobox('getValue');

        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_wo_no=" + filter_wo_no + "&filter_checksheet=" + filter_checksheet;
        $('#dg').datagrid({
            url: '<?= base_url('planning/checksheets/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/checksheets/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_wo_no = $("#filter_wo_no").combobox('getValue');
        var filter_checksheet = $("#filter_checksheet").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_wo_no=" + filter_wo_no + "&filter_checksheet=" + filter_checksheet;

        window.location.assign('<?= base_url('planning/checksheets/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        filter();

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }
                            $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#receipt').numberbox({
            onChange: function(value) {
                if(value != ""){
                    var qty = $("#qty").numberbox("getValue");
                    var receipt = $("#receipt").numberbox('getValue');

                    var calculate = parseInt(receipt) + parseInt(accumulateAjax);
                    var result = parseInt(qty) - parseInt(calculate);

                    var balance = $("#balance").numberbox('setValue', result);
                    var accumulate_total = $("#accumulate").numberbox('setValue', calculate);

                    if (result < 0) {
                        toastr.warning("Balance minus, please correct your Receipt!");
                        $("#receipt").numberbox('setValue', 0);
                        $("#accumulate").numberbox('setValue', accumulate);
                    } else {
                        return result;
                    }
                }else{
                    $("#receipt").numberbox('setValue', 0);
                }
            }
        });
    });

    $('#qc_1').combobox({
        url: '<?= base_url('planning/checksheets/readEmployesQC'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Employees',
        onSelect: function(qc) {
            $("#qcnumber_1").textbox('setValue', qc.number);
        }
    });
    $('#qc_2').combobox({
        url: '<?= base_url('planning/checksheets/readEmployesQC'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Employees',
        onSelect: function(qc) {
            $("#qcnumber_2").textbox('setValue', qc.number);
        }
    });
    $('#op_1').combobox({
        url: '<?= base_url('planning/checksheets/readEmployesOP'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Employees',
        onSelect: function(qc) {
            $("#opnumber_1").textbox('setValue', qc.number);
        }
    });
    $('#op_2').combobox({
        url: '<?= base_url('planning/checksheets/readEmployesOP'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Employees',
        onSelect: function(qc) {
            $("#opnumber_2").textbox('setValue', qc.number);
        }
    });
        
    $(document).ready(function() {
        $('#packing').combobox({
            onSelect: function(record) {
                var item_fg_id = $("#item_fg_id").textbox("getValue");
                $.ajax({
                    url: '<?= base_url("planning/checksheets/readItems/") ?>' + window.btoa(item_fg_id),
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);
                        var packingQty = '';
                        if (record.value == 1) {
                            packingQty = data[0].mpq;
                        } else if (record.value == 2) {
                            packingQty = data[0].qty_box;
                        }
                        $('#packing_qty').textbox('setValue', packingQty);
                    }
                });
            }
        });
    });

    $('#filter_wo_no').combobox({
        url: '<?= base_url('planning/checksheets/readWoNos'); ?>',
        valueField: 'wo_no',
        textField: 'wo_no',
        prompt: 'Choose Wo No'
    });

    $('#filter_checksheet').combobox({
        url: '<?= base_url('planning/checksheets/readChecksheet'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: 'Choose Wo No'
    });

    // function filter_workorder() {
    //     //Get Product
    //     $('#filter_workorder').combogrid({
    //         url: '<?= base_url('planning/checksheets/readWorkorder/filter') ?>',
    //         panelWidth: 300,
    //         idField: 'workorder',
    //         textField: 'workorder',
    //         mode: 'remote',
    //         fitColumns: true,
    //         prompt: "Choose Workorder",
    //         icons: [{
    //             iconCls: 'icon-clear',
    //             handler: function(e) {
    //                 $(e.data.target).combogrid('clear').combogrid('textbox').focus();
    //             }
    //         }],
    //         columns: [
    //             [{
    //                 field: 'workorder',
    //                 title: 'Workorder',
    //                 width: 150
    //             }, {
    //                 field: 'wp',
    //                 title: 'WP',
    //                 width: 80,
    //                 align: 'center'
    //             }]
    //         ],
    //     });
    // }

    function BtnPrint(val, row) {
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" target="_blank" href="<?= base_url('planning/checksheets/print_label/') ?>' + window.btoa(row.id) + '"><i class="fa fa-print"></i> Print</a>';
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

    //Number Format Currency
    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
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

    function packingformat(value, row) {
        if (value == 1) {
            return "MPQ";
        } else if (value == 2) {
            return "Qty per Box";
        } else if (value == 3) {
            return " ";
        }
    }
</script>
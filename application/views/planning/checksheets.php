<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:120,align:'center'" sortable="true">Checksheet ID</th>
            <th rowspan="2" data-options="field:'workorder',width:120,align:'center'" sortable="true">Workorder</th>
            <th rowspan="2" data-options="field:'trans_date',width:80,align:'center'" sortable="true">Trans Date</th>
            <th rowspan="2" data-options="field:'wp',width:80,align:'center'" sortable="true">WP</th>
            <th rowspan="2" data-options="field:'product_no',width:150" sortable="true">Product No</th>
            <th rowspan="2" data-options="field:'product_name',width:200" sortable="true">Product Name</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'" sortable="true">Uom</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat" sortable="true">Qty</th>
            <th rowspan="2" data-options="field:'receipt',width:80,halign:'center',align:'right',formatter:numberformat" sortable="true">Receipt</th>
            <th rowspan="2" data-options="field:'accumulate',width:80,halign:'center',align:'right',formatter:numberformat" sortable="true">Accumulate</th>
            <th rowspan="2" data-options="field:'balance',width:80,halign:'center',align:'right',formatter:numberformat" sortable="true">Balance</th>
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
<div id="toolbar" style="height: 200px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 35%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Workorder</span>
                <input style="width:60%;" id="filter_workorder" class="easyui-combogrid">
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:60%;" name="trans_date" id="trans_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Workorder</span>
                <input style="width:60%;" name="workorder" id="workorder" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">WP</span>
                <input style="width:20%;" name="wp" id="wp" required="" readonly="" class="easyui-textbox">
                <input style="width:40%;" name="period" id="period" required="" disabled="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Order No</span>
                <input style="width:60%;" id="so_number" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" id="customer" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="product_no" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Name</span>
                <input style="width:60%;" id="product_name" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">WO Qty</span>
                <input style="width:30%;" name="qty" id="qty" required="" readonly="" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt Qty</span>
                <input style="width:30%;" name="receipt" id="receipt" onchange="receiptQty(this.value)" required="" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Accumulate</span>
                <input style="width:30%;" name="accumulate" id="accumulate" required="" readonly="" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Balance Qty</span>
                <input style="width:30%;" name="balance" id="balance" required="" readonly="" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
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

        $('#workorder').combogrid({
            url: '<?= base_url('planning/checksheets/readWorkorder') ?>',
            panelWidth: 350,
            idField: 'workorder',
            textField: 'workorder',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Workorder",
            columns: [
                [{
                    field: 'workorder',
                    title: 'Workorder',
                    width: 150
                }, {
                    field: 'wp',
                    title: 'WP',
                    width: 80,
                    align: 'center'
                }, {
                    field: 'balance',
                    title: 'Balance',
                    width: 80,
                    halign: 'center',
                    align: 'right'
                }]
            ],
            onSelect: function(val, row) {
                $("#wp").textbox('setValue', row.wp);
                $("#period").textbox('setValue', row.period);
                $("#so_number").textbox('setValue', row.so_number);
                $("#product_no").textbox('setValue', row.product_no);
                $("#product_name").textbox('setValue', row.product_name);
                $("#customer").textbox('setValue', row.customer_name);
                $("#qty").numberbox('setValue', row.qty);
                $("#receipt").numberbox('setValue', row.balance);
                $("#accumulate").numberbox('setValue', row.accumulate);
                $("#balance").textbox('setValue', '0');
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
        var filter_workorder = $("#filter_workorder").combogrid('getValue');

        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_workorder=" + filter_workorder;
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
        var filter_workorder = $("#filter_workorder").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_workorder=" + filter_workorder;

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
                            filter_workorder();
                        }
                    });
                }
            }]
        });

        filter_workorder();

        $('#receipt').numberbox({
            onChange: function(value) {
                var qty = $("#qty").numberbox("getValue");
                var accumulate = $("#accumulate").numberbox("getValue");
                var receipt = $("#receipt").numberbox('getValue');
                var result = parseInt(qty) - (parseInt(receipt) + parseInt(accumulate));
                var balance = $("#balance").numberbox('setValue', result);

                if (result < 0) {
                    toastr.warning("Receipt Qty not minus");
                    $("#receipt").numberbox('setValue', 0);
                } else {
                    return result;
                }
            }
        });
    });

    function filter_workorder() {
        //Get Product
        $('#filter_workorder').combogrid({
            url: '<?= base_url('planning/checksheets/readWorkorder/filter') ?>',
            panelWidth: 300,
            idField: 'workorder',
            textField: 'workorder',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Workorder",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'workorder',
                    title: 'Workorder',
                    width: 150
                }, {
                    field: 'wp',
                    title: 'WP',
                    width: 80,
                    align: 'center'
                }]
            ],
        });
    }

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
</script>
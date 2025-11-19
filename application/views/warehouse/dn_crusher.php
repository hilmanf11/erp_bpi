<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'document_no',width:150,align:'left'">Document No</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,align:'left'">Customer</th>
            <th rowspan="2" data-options="field:'division',width:100,halign:'center'">Division</th>
            <th rowspan="2" data-options="field:'transaction_dates',width:200,halign:'center'">Trans Date</th>
            <th rowspan="2" data-options="field:'approved_to',width:100,halign:'center',formatter:formatApproved,styler:styleApproved">Status <br>Approve</th>
            <th rowspan="2" data-options="field:'approved_by',width:100,halign:'center'">Approve By</th>
            <th rowspan="2" data-options="field:'approved_date',width:150,halign:'center'">Approve Date</th>
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
<div id="toolbar" style="height: 240px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 70%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" id="filter_document_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="filter_division" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="print_note()"><i class="fa fa-print"></i> Print Note</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" id="filter_item_category" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_item_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_rm_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status Approval</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="approve">Approve</option>
                        <option value="checking">Checking</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('warehouse/dn_crusher/print') ?>" style="width: 100%;" hidden></iframe>

<script>

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_document_no = $("#filter_document_no").combobox('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');
        var filter_item_category = $("#filter_item_category").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_division=" + filter_division +
            "&filter_document_no=" + filter_document_no + 
            "&filter_item_rm_id=" + filter_item_rm_id + "&filter_item_category=" + filter_item_category + "&filter_item_family=" + filter_item_family +
            "&filter_status=" + filter_status;

        $('#dg').datagrid({
            url: '<?= base_url('warehouse/dn_crusher/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/dn_crusher/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
       var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_document_no = $("#filter_document_no").combobox('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');
        var filter_item_category = $("#filter_item_category").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_division=" + filter_division +
            "&filter_document_no=" + filter_document_no +
            "&filter_item_rm_id=" + filter_item_rm_id + "&filter_item_category=" + filter_item_category + "&filter_item_family=" + filter_item_family +
            "&filter_status=" + filter_status;

        window.location.assign('<?= base_url('warehouse/dn_crusher/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //ADD DATA
        // addTable();

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/dn_crusher/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            fitColumns: true,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.document_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('warehouse/dn_crusher/datatableDetails?number=') ?>' + window.btoa(row.document_no) + "&filter_item_rm_id=" + window.btoa(filter_item_rm_id),
                    singleSelect: true,
                    rownumbers: true,
                    fitColumns: true,
                    columns: [
                        [{
                            field: 'item_rm_id',
                            title: 'Part ID',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_number',
                            title: 'Part Number',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_name',
                            title: 'Part Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'category_name',
                            title: 'Category',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'family_name',
                            title: 'Product Family',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'uom',
                            title: 'UOM',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'qtys',
                            title: 'Qty',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberformat
                        }, {
                            field: 'remarks',
                            title: 'Remarks',
                            width: 200,
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
    });

    $('#filter_division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'number',
        textField: 'number',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $('#filter_item_rm_id').combogrid({
        url: '<?= base_url('master/item_rm/reads'); ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Part No",
        columns: [
            [{
                field: 'number',
                title: 'Part No',
                width: 150
            }, {
                field: 'name',
                title: 'Part Name',
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

    $('#filter_document_no').combobox({
        url: '<?= base_url('warehouse/dn_crusher/documentNo'); ?>',
        valueField: 'document_no',
        textField: 'document_no',
        panelHeight: 'panelHeight',
        prompt: 'Choose Document No',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $("#filter_item_category").combobox({
        url: '<?= base_url('master/item_categories/readsnotfg') ?>',
        valueField: 'id',
        textField: 'name',
        prompt: "Choose Categories",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $("#filter_item_family").combobox({
        url: '<?= base_url('finance/inventory_rm/readItemFamilys/') ?>',
        valueField: 'number',
        textField: 'name',
        prompt: "Choose Product Family",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
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
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    //FORMATTER APPROVE
    function formatApproved(value) {
        if (value == "" || value === null ) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    //CELLSTYLE APPROVE
    function styleApproved(value, row, index) {
        if (value == "" || value === null ) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function print_note() {
        var document_no = $("#filter_document_no").combobox('getValue');
        console.log(document_no);
        if (document_no == "") {
            toastr.warning("Please select Document No First!", "Information");
        } else {
            $.ajax({
                type: "POST",
                url: "<?= base_url('warehouse/dn_crusher/checkNote') ?>",
                data: {
                    document_no: document_no
                },
                dataType: "json",
                success: function(response) {
                    console.log(response);
                    if (response == 'NO') {
                        toastr.warning("Note has not been approved", "Information");
                    } else {
                        window.open("<?= base_url('warehouse/dn_crusher/print_note/') ?>" + window.btoa(document_no), "_blank");
                    }
                },
                error: function() {
                    toastr.error("An error occurred while checking Memo for selected Memo No!", "Error");
                }
            });
        }
    }
</script>
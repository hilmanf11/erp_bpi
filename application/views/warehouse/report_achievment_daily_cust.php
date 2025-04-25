<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar" data-options="fit: true">
    <thead data-options="frozen:true">
        <tr>
            <th field="ck" checkbox="false"></th>
            <th data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th data-options="field:'item_name',width:180,halign:'center'">Product Name</th>
            <th data-options="field:'division',width:100,halign:'center'">Division</th>
            <th data-options="field:'customer_name',width:250,halign:'center'">Customer</th>
        </tr>
    </thead>
    <thead>
        <tr>
        <th rowspan="2" data-options="field:'qty_so',width:80,halign:'center'">Qty SO</th>
        <th rowspan="2" data-options="field:'qty_dn',width:80,halign:'center'">Delivered</th>
        <th rowspan="2" data-options="field:'qty_ds',width:80,halign:'center'">Schedule</th>
        <th rowspan="2" data-options="field:'ost_so',width:80,halign:'center'">OST SO</th>
        <th rowspan="2" data-options="field:'type',width:80,halign:'center'">Type</th>
            <?php 
                if ($this->input->get('filter_from') && $this->input->get('filter_to')) {
                    $filter_from = base64_decode($this->input->get('filter_from'));
                    $filter_to = base64_decode($this->input->get('filter_to'));
                    $filter_customer = base64_decode($this->input->get('filter_customer'));
                    $filter_item_fg = base64_decode($this->input->get('filter_item_fg'));
                } else {
                    $filter_from = date("Y-m-01");
                    $filter_to = date("Y-m-t");
                    $filter_customer = "";
                    $filter_item_fg = "";
                }
            
                $currentDate = $filter_from;
                $tgl = 1;
                while (strtotime($currentDate) <= strtotime($filter_to)) {
                    $working_date = date('d M', strtotime($currentDate));
            
                    if (date('w', strtotime($currentDate)) !== '0') {
                        $hstyle = "";
                    } else {
                        $hstyle = "background-color:#FFB0B0";
                    }
            ?>
            <th rowspan="2" data-options="field:'qty_<?= $tgl ?>',width:80,halign:'center', align:'right',hstyler: function(value, row, index) { return '<?= $hstyle ?>'; }"><?= $working_date ?></th>
            <!-- <th rowspan="2" data-options="field:'sales_order_qty_<?= $tgl ?>',width:80,halign:'center', align:'right',hstyler: function(value, row, index) { return '<?= $hstyle ?>'; }"><?= $working_date ?></th> -->
            <?php
                    // $tgl++;
                    // $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));

                    $tgl++;
                    $currentDate = date("Y-m-d", strtotime("+1 day", strtotime($currentDate)));
                }
            ?>
            <th colspan="2" data-options="field:'',width:100,align:'center'">Created</th>
            <th colspan="2" data-options="field:'',width:100,align:'center'">Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,halign:'center'">By</th>
            <th data-options="field:'created_date',width:150,halign:'center'">Date</th>
            <th data-options="field:'updated_by',width:100,halign:'center'">By</th>
            <th data-options="field:'updated_date',width:150,halign:'center'">Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 160px; padding: 10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <!-- <input style="width:30%;" name="filter_month" id="filter_month" value="<?= $filter_month ?>" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="filter_year" id="filter_year" value="<?= $filter_year ?>" class="easyui-combobox" data-options="prompt:'Year'"> -->
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:29%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" id="filter_customer" value="<?= $filter_customer ?>" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 20%; float: left;">
               
            </div>
            <div style="width: 20%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" value="<?= $filter_item_fg ?>" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<iframe id="printout" src="" style="width: 100%; height:90%; border: 0;" hidden></iframe>

<script>

    function filter() {
        // var filter_month = $("#filter_month").combobox('getValue');
        // var filter_year = $("#filter_year").textbox('getValue');
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        // var url = "?filter_month=" + window.btoa(filter_month) +
        //     "&filter_year=" + window.btoa(filter_year) +
        //     "&filter_customer=" + window.btoa(filter_customer) +
        //     "&filter_item_fg=" + window.btoa(filter_item_fg);

        // if (filter_month == "" || filter_year == "") {
        //         toastr.warning("Please select Period!", "Information");
        // } else {
        //     window.location.assign(url);
        // }

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        if (filter_from == "" || filter_to == "") {
                toastr.warning("Please select Period!", "Information");
        } else {
            window.location.assign(url);
        }
    }

    // Fungsi untuk mengonversi query string menjadi objek
    function getQueryParams() {
        var params = {};
        var url = window.location.href;
        var queryString = url.substring(url.indexOf('?') + 1);
        var urlParams = new URLSearchParams(queryString);

        urlParams.forEach(function(value, key) {
            params[key] = window.atob(value); // Mendecode base64
        });

        return params;
    }

    // Setelah halaman dimuat, set nilai input
    $(document).ready(function() {
        var params = getQueryParams();
        if (params.filter_from && params.filter_to) {
            $('#filter_from').datebox('setValue', params.filter_from);
            $('#filter_to').datebox('setValue', params.filter_to);
        }
    });

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        // var filter_month = $("#filter_month").combobox('getValue');
        // var filter_year = $("#filter_year").textbox('getValue');
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        // var url = "?filter_month=" + window.btoa(filter_month) +
        //     "&filter_year=" + window.btoa(filter_year) +
        //     "&filter_customer=" + window.btoa(filter_customer) +
        //     "&filter_item_fg=" + window.btoa(filter_item_fg);

        // if (filter_month == "" || filter_year == "") {
        //         toastr.warning("Please select Period!", "Information");
        // } else {
        //     $.messager.alert('Info','Please Wait to Export to Excel');
        //     window.location.assign('<?= base_url('warehouse/report_achievment_daily_cust/print/excel') ?>' + url);
        // }

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

            if (filter_from == "" || filter_to == "") {
                toastr.warning("Please select Period!", "Information");
        } else {
            $.messager.alert('Info','Please Wait to Export to Excel');
            window.location.assign('<?= base_url('warehouse/report_achievment_daily_cust/print/excel') ?>' + url);
        }
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_sales_order_deliveries.xls') ?>');
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        if (filter_from != "" && filter_to != "") {
            $('#dg').datagrid({
                url: '<?= base_url('warehouse/report_achievment_daily_cust/datatables') ?>' + url,
                pagination: true,
                rownumbers: true,
                singleSelect: true,
                fit: true,
                pageList: [20, 50, 100, 500, 1000],
                pageSize: 20,
                onLoadSuccess: function(data){
                    var z = 0;
                    for(var i=0; i < (data.total); i++){

                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'item_number',
                            rowspan: 3,
                        });
                        
                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'item_name',
                            rowspan: 3,
                        });

                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'division',
                            rowspan: 3,
                        });

                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'customer_name',
                            rowspan: 3,
                        });

                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'qty_so',
                            rowspan: 3,
                        });

                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'qty_dn',
                            rowspan: 3,
                        });

                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'qty_ds',
                            rowspan: 3,
                        });

                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'ost_so',
                            rowspan: 3,
                        });

                        z += 3;
                    }
                }
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('warehouse/report_achievment_daily_cust/print') ?>' + url);
        }

        $('#filter_month').combobox({
            url: '<?php echo base_url('warehouse/report_achievment_daily_cust/readMonth'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Month',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('warehouse/report_achievment_daily_cust/readYear'); ?>',
            valueField: 'number',
            textField: 'number',
            prompt: 'Select Year',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_customer').combobox({
            url: '<?php echo base_url('master/customers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Customer',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_item_fg').combogrid({
            url: '<?= base_url("master/item_fg/reads") ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }]
            ],
        });

        $('#p_month').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Month',
            onSelect: function(m){
                var y = $("#p_year").combobox('getValue');
                calendars(m.id, y);
            }
        });

        $('#p_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Year',
            onSelect: function(y){
                var m = $("#p_month").combobox('getValue');
                calendars(m, y.id);
            }
        });

        $('#customer_id').combobox({
            url: '<?php echo base_url('master/customers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Customer',
            onSelect: function(row){
                $('#plant').combobox({
                    url: '<?php echo base_url('master/customers/readAddress/'); ?>' + btoa(row.id),
                    valueField: 'plant',
                    textField: 'plant',
                    prompt: 'Select plant'
                });

                $('#item_fg_id').combogrid({
                    url: '<?= base_url('master/customer_items/reads/') ?>' + btoa(row.id),
                    panelWidth: 550,
                    idField: 'id',
                    textField: 'number_customer',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Product No",
                    columns: [
                        [{
                            field: 'number',
                            title: 'Product No EBWS',
                            width: 150
                        }, {
                            field: 'number_customer',
                            title: 'Product No Customer',
                            width: 200
                        }, {
                            field: 'name',
                            title: 'Description',
                            width: 200
                        }]
                    ]
                });
            }
        });
    });

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function cellStyler(value, row, index) {
        if (value >= 0) {
            return 'font-weight:bold;';
        } else {
            return 'color:#FF5F5F; font-weight:bold;';
        }
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
</script>
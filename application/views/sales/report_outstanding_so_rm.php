<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">SO Date</span>
                    <input style="width:30%;" id="filter_so_date_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser" class="easyui-datebox">
                    <input style="width:30%;" id="filter_so_date_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" id="filter_customer_name" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Order No</span>
                    <input style="width:60%;" id="filter_customer_order_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Order No</span>
                    <input style="width:60%;" id="filter_sales_order_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="filter_division" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Report Display</span>
                    <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">
                        <option value="RECAP">RECAP</option>
                        <option value="DETAIL">DETAIL</option>
                    </select>
                </div>
            </div>
        </fieldset>
    </div>
    <div style="margin-left: 10px; margin-bottom:5px;">
        <?= $button ?>
    </div>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 450px; border: 0;"></iframe>
</div>

<script>
    function filter() {
        var filter_so_date_from = $("#filter_so_date_from").datebox("getValue");
        var filter_so_date_to = $("#filter_so_date_to").datebox("getValue");
        var filter_customer_name = $("#filter_customer_name").combobox("getValue");
        var filter_customer_order_no = $("#filter_customer_order_no").combobox("getValue");
        var filter_sales_order_no = $("#filter_sales_order_no").combobox("getValue");
        var filter_item_fg = $("#filter_item_fg").combobox("getValue");
        var filter_division = $("#filter_division").combobox("getValue");
        var filter_display = $("#filter_display").combobox("getValue");

        var url = "?filter_so_date_from=" + window.btoa(filter_so_date_from) +
            "&filter_so_date_to=" + window.btoa(filter_so_date_to) +
            "&filter_customer_name=" + window.btoa(filter_customer_name) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_division=" + window.btoa(filter_division) +
            "&filter_display=" + window.btoa(filter_display);

        if (filter_so_date_from == "" && filter_so_date_to == "") {
            toastr.warning("Please Select Trans Date");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('sales/report_outstanding_so_rm/print') ?>' + url);
        }
    }

    function excel() {
        var filter_so_date_from = $("#filter_so_date_from").datebox("getValue");
        var filter_so_date_to = $("#filter_so_date_to").datebox("getValue");
        var filter_customer_name = $("#filter_customer_name").combobox("getValue");
        var filter_customer_order_no = $("#filter_customer_order_no").combobox("getValue");
        var filter_sales_order_no = $("#filter_sales_order_no").combobox("getValue");
        var filter_item_fg = $("#filter_item_fg").combobox("getValue");
        var filter_division = $("#filter_division").combobox("getValue");
        var filter_display = $("#filter_display").combobox("getValue");

        var url = "?filter_so_date_from=" + window.btoa(filter_so_date_from) +
            "&filter_so_date_to=" + window.btoa(filter_so_date_to) +
            "&filter_customer_name=" + window.btoa(filter_customer_name) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_division=" + window.btoa(filter_division) +
            "&filter_display=" + window.btoa(filter_display);

        if (filter_so_date_from == "" && filter_so_date_to == "") {
            toastr.warning("Please Select Trans Date");
        } else {
            window.location.assign('<?= base_url('sales/report_outstanding_so_rm/print/excel') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#filter_customer_name').combobox({
            url: '<?php echo base_url('master/customers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Customer Name',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(cus) {
                var filter_so_date_from = $("#filter_so_date_from").datebox("getValue");
                var filter_so_date_to = $("#filter_so_date_to").datebox("getValue");

                $('#filter_customer_order_no').combobox({
                    url: '<?php echo base_url('sales/report_outstanding_so_rm/readCustomerOrder?customer_id='); ?>' + cus.id + "&filter_so_date_from=" + window.btoa(filter_so_date_from) + "&filter_so_date_to=" + window.btoa(filter_so_date_to),
                    valueField: 'customer_order_no',
                    textField: 'customer_order_no',
                    prompt: 'Select Customer Order No.',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $('#filter_sales_order_no').combobox({
                    url: '<?php echo base_url('sales/report_outstanding_so_rm/readCustomerOrder?customer_id='); ?>' + cus.id + "&filter_so_date_from=" + window.btoa(filter_so_date_from) + "&filter_so_date_to=" + window.btoa(filter_so_date_to),
                    valueField: 'sales_order_no',
                    textField: 'sales_order_no',
                    prompt: "Select Sales Order No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(so) {
                        $('#filter_item_fg').combogrid({
                            url: '<?php echo base_url('sales/report_outstanding_so_rm/readItems?customer_id='); ?>' + cus.id +
                                "&filter_so_date_from=" + window.btoa(filter_so_date_from) +
                                "&filter_so_date_to=" + window.btoa(filter_so_date_to) +
                                "&filter_sales_order_no=" + window.btoa(so.sales_order_no),
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
                    }
                });
            }
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

        $('#filter_division').combobox({
            url: '<?= base_url('master/divisions/reads'); ?>',
            valueField: 'name',
            textField: 'name',
            prompt: 'Choose Division',
        });
    });

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
</script>
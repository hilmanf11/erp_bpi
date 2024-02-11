<div id="f" class="easyui-panel" style="width:100%; padding:10px; background: #F4F4F4;">
    <fieldset style="width: 98%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" id="filter_customer" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery Order</span>
                <input style="width:60%;" id="filter_do_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery Note</span>
                <input style="width:60%;" id="filter_dn_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Serial No</span>
                <input style="width:60%;" id="filter_serial_no" class="easyui-textbox">
            </div>
        </div>
    </fieldset>
    <div style="margin-left: 10px; margin-bottom:5px;">
        <?= $button ?>
    </div>
</div>
<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 400px; border: 0;"></iframe>
</div>

<script>
    function filter() {
        var filter_customer = $("#filter_customer").combobox("getValue");
        var filter_product_no = $("#filter_product_no").combogrid("getValue");
        var filter_do_no = $("#filter_do_no").combobox("getValue");
        var filter_dn_no = $("#filter_dn_no").combobox("getValue");
        var filter_serial_no = $("#filter_serial_no").textbox("getValue");

        var url = "?filter_customer=" + filter_customer +
            "&filter_product_no=" + filter_product_no +
            "&filter_do_no=" + window.btoa(filter_do_no) +
            "&filter_dn_no=" + window.btoa(filter_dn_no) +
            "&filter_serial_no=" + window.btoa(filter_serial_no);

        if (filter_customer == "" && filter_serial_no == "") {
            toastr.warning("Please select Customer or Serial No!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('warehouse/report_serial_controls/print') ?>' + url);
        }
    }

    function excel() {
        var filter_customer = $("#filter_customer").combobox("getValue");
        var filter_product_no = $("#filter_product_no").combogrid("getValue");
        var filter_do_no = $("#filter_do_no").combobox("getValue");
        var filter_dn_no = $("#filter_dn_no").combobox("getValue");
        var filter_serial_no = $("#filter_serial_no").textbox("getValue");

        var url = "?filter_customer=" + filter_customer +
            "&filter_product_no=" + filter_product_no +
            "&filter_do_no=" + window.btoa(filter_do_no) +
            "&filter_dn_no=" + window.btoa(filter_dn_no) +
            "&filter_serial_no=" + window.btoa(filter_serial_no);

        if (filter_customer == "" && filter_serial_no == "") {
            toastr.warning("Please select Customer or Serial No!");
        } else {
            window.location.assign('<?= base_url('warehouse/report_serial_controls/print/excel') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
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
            onSelect: function(cust) {
                $('#filter_product_no').combogrid({
                    url: '<?= base_url('master/customer_items/readItems?customer_id=') ?>' + cust.id,
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
                    onSelect: function(val, item) {
                        $('#filter_do_no').combobox({
                            url: '<?php echo base_url('warehouse/report_serial_controls/readDeliveryOrder?customer_id='); ?>' + cust.id + "&item_id=" + item.id,
                            valueField: 'number',
                            textField: 'number',
                            prompt: 'Select Delivery Order',
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }],
                            onSelect: function(val2, item2){
                                $('#filter_dn_no').combobox({
                                    url: '<?php echo base_url('warehouse/report_serial_controls/readDeliveryNote?do_number='); ?>' + window.btoa(val2.number),
                                    valueField: 'number',
                                    textField: 'number',
                                    prompt: 'Select Delivery Note',
                                    icons: [{
                                        iconCls: 'icon-clear',
                                        handler: function(e) {
                                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                                        }
                                    }]
                                });
                            }
                        });
                    }
                });
            },
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
</script>
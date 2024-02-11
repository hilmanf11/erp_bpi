<div id="f" class="easyui-panel" style="width:100%; padding:10px; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Serial No</span>
                    <input style="width:60%;" id="filter_serial_no" class="easyui-textbox" data-options="prompt:'Input Serial No'">
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
                    <span style="width:35%; display:inline-block;">Receipt No</span>
                    <input style="width:60%;" id="filter_receipt" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Component No</span>
                    <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="-">Select ALL</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
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
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_serial_no = $("#filter_serial_no").textbox("getValue");
        var filter_supplier = $("#filter_supplier").combobox("getValue");
        var filter_receipt = $("#filter_receipt").combobox("getValue");
        var filter_product_no = $("#filter_product_no").combogrid("getValue");
        var filter_status = $("#filter_status").combobox("getValue");
        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_serial_no=" + window.btoa(filter_serial_no) +
            "&filter_supplier=" + filter_supplier +
            "&filter_receipt=" + window.btoa(filter_receipt) +
            "&filter_product_no=" + filter_product_no +
            "&filter_status=" + filter_status;
        if (filter_from == "" || filter_to == "" || (filter_supplier == "" && filter_serial_no == "")) {
            toastr.warning("Please select Trans Date & Supplier!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('warehouse/report_check_serialno/print') ?>' + url);
        }
    }
    function excel() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_serial_no = $("#filter_serial_no").textbox("getValue");
        var filter_supplier = $("#filter_supplier").combobox("getValue");
        var filter_receipt = $("#filter_receipt").combobox("getValue");
        var filter_product_no = $("#filter_product_no").combogrid("getValue");
        var filter_status = $("#filter_status").combobox("getValue");
        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_serial_no=" + window.btoa(filter_serial_no) +
            "&filter_supplier=" + filter_supplier +
            "&filter_receipt=" + window.btoa(filter_receipt) +
            "&filter_product_no=" + filter_product_no +
            "&filter_status=" + filter_status;
        if (filter_from == "" || filter_to == "" || (filter_supplier == "" && filter_serial_no == "")) {
            toastr.warning("Please select Trans Date & Supplier!");
        } else {
            window.location.assign('<?= base_url('warehouse/report_check_serialno/print') ?>' + url);
        }
    }
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    function reload() {
        window.location.reload();
    }
    $(function() {
        $("#filter_supplier").combobox({
            url: '<?= base_url('master/suppliers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(rowSupplier) {
                $("#filter_receipt").combobox({
                    url: '<?= base_url('warehouse/report_check_serialno/readReceiptNo?supplier=') ?>' + rowSupplier.id,
                    valueField: 'receipt_no',
                    textField: 'receipt_no',
                    prompt: "Select Receipt No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(rowReceipt) {
                        $("#filter_product_no").combogrid({
                            url: '<?= base_url('warehouse/report_check_serialno/readItems?supplier=') ?>' + rowSupplier.id + "&receipt_no=" + window.btoa(rowReceipt.receipt_no),
                            panelWidth: 420,
                            idField: 'item_id',
                            textField: 'item_number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: "Select Component No",
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                                }
                            }],
                            columns: [
                                [{
                                    field: 'item_number',
                                    title: 'Component No',
                                    width: 120
                                }, {
                                    field: 'item_name',
                                    title: 'Component Name',
                                    width: 250
                                }, ]
                            ]
                        });
                    }
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
</script>
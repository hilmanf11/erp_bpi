<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 70%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Report Display</span>
                    <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">
                        <option value="RECAP">RECAP</option>
                        <option value="DETAIL">DETAIL</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:28%;" id="filter_period" class="easyui-combobox"> &nbsp;
                    <span style="width:8%; display:inline-block;">WP</span>
                    <input style="width:22%;" id="filter_wp" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" id="filter_customer" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
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
        var filter_display = $("#filter_display").combobox("getValue");
        var filter_period = $("#filter_period").combobox("getValue");
        var filter_wp = $("#filter_wp").combobox("getValue");
        var filter_customer = $("#filter_customer").combogrid("getValue");
        var filter_product_no = $("#filter_product_no").combogrid("getValue");
        var filter_status = $("#filter_status").combobox("getValue");
        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_display=" + filter_display +
            "&filter_period=" + filter_period +
            "&filter_wp=" + window.btoa(filter_wp) +
            "&filter_customer=" + filter_customer +
            "&filter_product_no=" + filter_product_no +
            "&filter_status=" + filter_status;
        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Trans Date!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/report_outstanding_wo/print') ?>' + url);
        }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_display = $("#filter_display").combobox("getValue");
        var filter_period = $("#filter_period").combobox("getValue");
        var filter_wp = $("#filter_wp").combobox("getValue");
        var filter_customer = $("#filter_customer").combogrid("getValue");
        var filter_product_no = $("#filter_product_no").combogrid("getValue");
        var filter_status = $("#filter_status").combobox("getValue");
        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_display=" + filter_display +
            "&filter_period=" + filter_period +
            "&filter_wp=" + window.btoa(filter_wp) +
            "&filter_customer=" + filter_customer +
            "&filter_product_no=" + filter_product_no +
            "&filter_status=" + filter_status;
        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Trans Date!");
        } else {
            window.location.assign('<?= base_url('planning/report_outstanding_wo/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        $("#filter_period").combobox({
            url: '<?= base_url('planning/report_outstanding_wo/readPeriod') ?>',
            valueField: 'period',
            textField: 'period',
            prompt: "Select Period",
            onSelect: function(rowPeriod) {
                $("#filter_wp").combobox({
                    url: '<?= base_url('planning/report_outstanding_wo/readWp?period=') ?>' + window.btoa(rowPeriod.period),
                    valueField: 'wp',
                    textField: 'wp',
                    prompt: "Select WP",
                    onSelect: function(rowWP) {
                        $("#filter_workorder").textbox('setValue', rowWP.workorder);
                        $("#filter_customer").combogrid({
                            url: '<?= base_url('planning/report_outstanding_wo/readCustomer?wp=') ?>' + window.btoa(rowWP.wp) + "&period=" + window.btoa(rowPeriod.period),
                            panelWidth: 420,
                            idField: 'customer_id',
                            textField: 'customer_name',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: "Select Customer No",
                            columns: [
                                [{
                                    field: 'customer_number',
                                    title: 'Customer No',
                                    width: 120
                                }, {
                                    field: 'customer_name',
                                    title: 'Customer Name',
                                    width: 250
                                }, ]
                            ],
                            onSelect: function(val, rowCust) {
                                $("#filter_product_no").combogrid({
                                    url: '<?= base_url('planning/report_outstanding_wo/readItems?wp=') ?>' + window.btoa(rowWP.wp) + "&period=" + window.btoa(rowPeriod.period) + "&customer_id=" + rowCust.customer_id,
                                    panelWidth: 420,
                                    idField: 'item_id',
                                    textField: 'item_name',
                                    mode: 'remote',
                                    fitColumns: true,
                                    prompt: "Select Product No",
                                    columns: [
                                        [{
                                            field: 'item_number',
                                            title: 'Product No',
                                            width: 120
                                        }, {
                                            field: 'item_name',
                                            title: 'Product Name',
                                            width: 250
                                        }, ]
                                    ]
                                });
                            }
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
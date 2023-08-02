<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Report Display</span>
                <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">
                    <option value="RECAP">RECAP</option>
                    <option value="DETAIL">DETAIL</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" id="filter_item_family" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Request No</span>
                <input style="width:60%;" id="filter_purchase_request" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
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
        var filter_item_family = $("#filter_item_family").combobox("getValue");
        var filter_product_no = $("#filter_product_no").combogrid("getValue");
        var filter_purchase_request = $("#filter_purchase_request").combobox("getValue");
        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_display=" + filter_display +
            "&filter_item_family=" + filter_item_family +
            "&filter_product_no=" + filter_product_no +
            "&filter_purchase_request=" + window.btoa(filter_purchase_request);
        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Trans Date!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('purchase/report_outstanding_pr/print') ?>' + url);
        }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_display = $("#filter_display").combobox("getValue");
        var filter_item_family = $("#filter_item_family").combobox("getValue");
        var filter_product_no = $("#filter_product_no").combogrid("getValue");
        var filter_purchase_request = $("#filter_purchase_request").combobox("getValue");
        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_display=" + filter_display +
            "&filter_item_family=" + filter_item_family +
            "&filter_product_no=" + filter_product_no +
            "&filter_purchase_request=" + window.btoa(filter_purchase_request);
        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Trans Date!");
        } else {
            window.location.assign('<?= base_url('purchase/report_outstanding_pr/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        $("#filter_item_family").combobox({
            url: '<?= base_url('master/item_familys/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Product Family",
            onSelect: function(prod) {
                $('#filter_product_no').combogrid({
                    url: '<?= base_url('master/supplier_items/readItems?item_family_id=') ?>' + prod.id,
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
                        var filter_from = $("#filter_from").datebox("getValue");
                        var filter_to = $("#filter_to").datebox("getValue");
                        $('#filter_purchase_request').combobox({
                            url: '<?php echo base_url('purchase/report_outstanding_pr/readPurchaseRequest?item_family_id='); ?>' + prod.id + "&item_id=" + item.id + "&filter_from=" + window.btoa(filter_from) + "&filter_to=" + window.btoa(filter_to),
                            valueField: 'number',
                            textField: 'number',
                            prompt: 'Select Purchase Request',
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }],
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
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div style="width: 98%; margin: 10px;">
    <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt Date</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Category</span>
                <input style="width:60%;" id="filter_item_family" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt No</span>
                <input style="width:60%;" id="filter_receipt_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Custom AJU No</span>
                <input style="width:60%;" id="filter_aju" class="easyui-combobox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">AWB No</span>
                <input style="width:60%;" id="filter_awb" class="easyui-combobox">
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>
<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;">
    <iframe id="printout" src="" style="width: 100%; height:500px; border: 0;"></iframe>
</div>
<script>
    function reload() {
        window.location.reload();
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_receipt_no = $("#filter_receipt_no").combobox('getValue');
        var filter_aju = $("#filter_aju").combobox('getValue');
        var filter_awb = $("#filter_awb").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_item_family=" + filter_item_family + "&filter_receipt_no=" + filter_receipt_no + "&filter_aju=" + filter_aju + "&filter_awb=" + filter_awb;
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('beacukai/pemasukan_pabean/print') ?>' + url);
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_receipt_no = $("#filter_receipt_no").combobox('getValue');
        var filter_aju = $("#filter_aju").combobox('getValue');
        var filter_awb = $("#filter_awb").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_item_family=" + filter_item_family + "&filter_receipt_no=" + filter_receipt_no + "&filter_aju=" + filter_aju + "&filter_awb=" + filter_awb;
        window.location.assign('<?= base_url('beacukai/pemasukan_pabean/print/excel') ?>' + url);
    }

    $(function() {
        $("#filter_item_family").combobox({
            url: '<?= base_url('beacukai/pemasukan_pabean/readItemFamily/001') ?>',
            valueField: 'number',
            textField: 'name',
            prompt: "Select Product Category",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(itemfam) {
                $("#filter_receipt_no").combobox({
                    url: '<?= base_url('beacukai/pemasukan_pabean/readReceiptNo/') ?>' + itemfam.number,
                    valueField: 'receipt_no',
                    textField: 'receipt_no',
                    prompt: "Select Receipt No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(receipt) {
                        $("#filter_aju").combobox({
                            url: '<?= base_url('beacukai/pemasukan_pabean/readAjuNo/') ?>' + itemfam.number + "/" + window.btoa(receipt.receipt_no),
                            valueField: 'bc_aju',
                            textField: 'bc_aju',
                            prompt: "Select Custom AJU No",
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }],
                        });
                        $("#filter_awb").combobox({
                            url: '<?= base_url('beacukai/pemasukan_pabean/readAwbNo/') ?>' + itemfam.number + "/" + window.btoa(receipt.receipt_no),
                            valueField: 'awb_no',
                            textField: 'awb_no',
                            prompt: "Select AWB No",
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function(e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }],
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
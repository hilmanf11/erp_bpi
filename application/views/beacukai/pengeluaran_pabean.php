<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div style="width: 98%; margin: 10px;">
    <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery Date</span>
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
                <span style="width:35%; display:inline-block;">Delivery Note</span>
                <input style="width:60%;" id="filter_delivery_note" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Custom No</span>
                <input style="width:60%;" id="filter_aju" class="easyui-combobox">
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
        var filter_delivery_note = $("#filter_delivery_note").combobox('getValue');
        var filter_aju = $("#filter_aju").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_item_family=" + filter_item_family + "&filter_delivery_note=" + filter_delivery_note + "&filter_aju=" + filter_aju;

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('beacukai/pengeluaran_pabean/print') ?>' + url);
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_delivery_note = $("#filter_delivery_note").combobox('getValue');
        var filter_aju = $("#filter_aju").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_item_family=" + filter_item_family + "&filter_delivery_note=" + filter_delivery_note + "&filter_aju=" + filter_aju;

        window.location.assign('<?= base_url('beacukai/pengeluaran_pabean/print/excel') ?>' + url);
    }

    $(function() {
        $("#filter_item_family").combobox({
            url: '<?= base_url('beacukai/pengeluaran_pabean/readItemFamily/001') ?>',
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
                if(itemfam.number == "001"){
                    $("#filter_delivery_note").combobox({
                        url: '<?= base_url('beacukai/pengeluaran_pabean/readDeliveryNote/') ?>',
                        valueField: 'number',
                        textField: 'number',
                        prompt: "Select Delivery Note",
                        icons: [{
                            iconCls: 'icon-clear',
                            handler: function(e) {
                                $(e.data.target).combobox('clear').combobox('textbox').focus();
                            }
                        }],
                    });
                }
            }
        });

        $("#filter_aju").combobox({
            url: '<?= base_url('beacukai/pengeluaran_pabean/readBcNo') ?>',
            valueField: 'bc_no',
            textField: 'bc_no',
            prompt: "Select Custom No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
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
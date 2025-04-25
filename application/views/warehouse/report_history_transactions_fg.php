<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div id="toolbar" style="padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt Date</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Division</span>
                <input style="width:60%;" name="filter_division" id="filter_division" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter_lsb()"><i class="fa fa-search"></i> LSB</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter_detail_transaction()"><i class="fa fa-search"></i> Detail Transaction</a>
            </div>
        </div>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_items" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Report Display</span>
                <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">
                    <option value="RECAP">RECAP</option>
                    <option value="DETAIL">DETAIL</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Type</span>
                <select style="width:60%;" id="filter_trans_type" class="easyui-combobox" panelHeight="auto" disabled>
                    <option value="">Choose All</option>
                    <option value="RECEIPT FG">RECEIPT FG</option>
                    <option value="NEW BARCODE">NEW BARCODE</option>
                    <option value="DELIVERY NOTE">DELIVERY NOTE</option>
                    <option value="ADJ IN STO">ADJ IN STO</option>
                    <option value="ADJ OUT STO">ADJ OUT STO</option>
                    <option value="BPB">BPB</option>
                    <option value="REPAIR OF GOODS">REPAIR OF GOODS</option>
                </select>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="excel_lsb()"><i class="fa fa-file"></i> Export LSB</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="excel_detail_transaction()"><i class="fa fa-file"></i> Export Detail Transaction</a>
</div>
<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;">
    <iframe id="printout" src="" style="width: 100%; height:500px; border: 0;"></iframe>
</div>

<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; text-align: center; color: white; font-size: 20px; padding-top: 20%;">
    <b>Please Wait until Dialog download show up...</b>
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
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_trans_type = $("#filter_trans_type").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_trans_type=" + filter_trans_type + "&filter_division=" + filter_division;
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/report_history_transactions_fg/print') ?>' + url);
    }

    function filter_lsb() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_trans_type = $("#filter_trans_type").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_trans_type=" + filter_trans_type + "&filter_division=" + filter_division;
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/report_history_transactions_fg/lsb') ?>' + url);
    }

    function filter_detail_transaction() {
       var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_trans_type = $("#filter_trans_type").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_trans_type=" + filter_trans_type + "&filter_division=" + filter_division;
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/report_history_transactions_fg/detail_transaction') ?>' + url);
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_trans_type = $("#filter_trans_type").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_trans_type=" + filter_trans_type + "&filter_division=" + filter_division;

        // Tampilkan overlay
        $("#loadingOverlay").show();

        // Unduh file
        window.location.assign('<?= base_url('warehouse/report_history_transactions_fg/print/excel') ?>' + url);

        // Sembunyikan overlay setelah beberapa saat
        setTimeout(function () {
            $("#loadingOverlay").hide();
        }, 3000); // Sesuaikan waktu jika perlu
    }

    function excel_lsb() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_trans_type = $("#filter_trans_type").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_trans_type=" + filter_trans_type + "&filter_division=" + filter_division;
        
        // Tampilkan overlay
        $("#loadingOverlay").show();
    
        window.location.assign('<?= base_url('warehouse/report_history_transactions_fg/lsb/excel') ?>' + url);

         // Sembunyikan overlay setelah beberapa saat
         setTimeout(function () {
            $("#loadingOverlay").hide();
        }, 3000); // Sesuaikan waktu jika perlu
    }

    function excel_detail_transaction() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_trans_type = $("#filter_trans_type").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_trans_type=" + filter_trans_type + "&filter_division=" + filter_division;
        
        // Tampilkan overlay
        $("#loadingOverlay").show();
    
        window.location.assign('<?= base_url('warehouse/report_history_transactions_fg/detail_transaction/excel') ?>' + url);

         // Sembunyikan overlay setelah beberapa saat
         setTimeout(function () {
            $("#loadingOverlay").hide();
        }, 3000); // Sesuaikan waktu jika perlu
    }

    $(function() {
        $('#filter_items').combogrid({
            url: '<?= base_url('master/item_fg/reads/') ?>',
            panelWidth: 420,
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
                    width: 100
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }, ]
            ]
        });
    });

    $("#filter_display").combobox({
        onChange: function(display){
            if(display === 'DETAIL'){
                $('#filter_trans_type').combobox('enable');
            } else {
                $('#filter_trans_type').combobox('disable');
            }
        }
    });

    $('#filter_division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'id',
        textField: 'name',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
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
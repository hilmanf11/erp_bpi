<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div id="toolbar" style="padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Division</span>
                <input style="width:60%;" id="filter_division" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Deviation</span>
                <select style="width:60%;" id="filter_deviation" class="easyui-combobox" panelHeight="auto">
                    <option value="">Choose All</option>
                    <option value="deviationplus">Deviation +</option>
                    <option value="deviationminus">Deviation -</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_items" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cutt of Stock</span>
                <input style="width:60%;" id="filter_cut_of_stock" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Cutt of STO</span>
                <input style="width:60%;" id="filter_cut_of_sto" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Periode STO</span>
                <input style="width:29%;" id="filter_from_sto" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to_sto" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
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
        var filter_cut_of_stock = $("#filter_cut_of_stock").datebox('getValue');
        // var filter_cut_of_sto = $("#filter_cut_of_sto").datebox('getValue');
        var filter_from_sto = $("#filter_from_sto").datebox('getValue');
        var filter_to_sto = $("#filter_to_sto").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_deviation = $("#filter_deviation").combobox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        url = "?filter_cut_of_stock=" + filter_cut_of_stock + "&filter_from_sto=" + filter_from_sto + "&filter_to_sto=" + filter_to_sto + "&filter_division=" + filter_division + "&filter_deviation=" + filter_deviation + "&filter_items=" + filter_items;
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/report_sto_finish_goods/print') ?>' + url);
    }

    function excel() {
        var filter_cut_of_stock = $("#filter_cut_of_stock").datebox('getValue');
        // var filter_cut_of_sto = $("#filter_cut_of_sto").datebox('getValue');
        var filter_from_sto = $("#filter_from_sto").datebox('getValue');
        var filter_to_sto = $("#filter_to_sto").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_deviation = $("#filter_deviation").combobox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');

        url = "?filter_cut_of_stock=" + filter_cut_of_stock + "&filter_from_sto=" + filter_from_sto + "&filter_to_sto=" + filter_to_sto + "&filter_division=" + filter_division + "&filter_deviation=" + filter_deviation + "&filter_items=" + filter_items;

        // Tampilkan overlay
        $("#loadingOverlay").show();

        // Unduh file
        window.location.assign('<?= base_url('warehouse/report_sto_finish_goods/print/excel') ?>' + url);

        // Sembunyikan overlay setelah beberapa saat
        setTimeout(function () {
            $("#loadingOverlay").hide();
        }, 3000); // Sesuaikan waktu jika perlu
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

    $('#filter_division').combobox({
        url: '<?= base_url('warehouse/report_sto_finish_goods/readsDivision/'); ?>',
        valueField: 'id',
        textField: 'number',
        prompt: 'Choose Division',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

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
</script>
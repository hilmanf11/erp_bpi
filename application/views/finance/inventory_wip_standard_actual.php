<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div id="toolbar" style="height: 200px; padding: 10px;">
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
                <input style="width:60%;" id="filter_division" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 49%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_items" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Shift</span>
                <select style="width:60%;" id="filter_shift" class="easyui-combobox" panelHeight="auto">
                    <option value="">Choose All</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>
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
    <?= $button ?>
</div>

<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;">
    <iframe id="printout" src="" style="width: 100%; height:520px; border: 0;"></iframe>
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
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');

        var yearFrom = filter_from.substring(0, 4);
        var yearTo = filter_to.substring(0, 4);
        if (yearFrom !== yearTo) {
            toastr.warning("Please select the same year for Receipt Date", "Information");
        } else {
            url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_division=" + filter_division + "&filter_shift=" + filter_shift;
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('finance/inventory_wip_standard_actual/print') ?>' + url);
        }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');

        var yearFrom = filter_from.substring(0, 4);
        var yearTo = filter_to.substring(0, 4);
        if (yearFrom !== yearTo) {
            toastr.warning("Please select the same year for Receipt Date", "Information");
        } else {
            url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_division=" + filter_division + "&filter_shift=" + filter_shift;
            window.location.assign('<?= base_url('finance/inventory_wip_standard_actual/print/excel') ?>' + url);
        }
    }

    $(function() {
        $("#add").html("Save Inventory WIP");

        $('#filter_items').combogrid({
            url: '<?= base_url('master/item_fg/reads') ?>',
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

    $('#filter_division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'id',
        textField: 'number',
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
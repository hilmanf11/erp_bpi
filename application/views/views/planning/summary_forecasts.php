<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="CONDITIONS" style="padding: 20px;">
            <ul>
                <li><b>Filter Data</b> is to show Summary Forecast base on <b>Filter Period</b>, The data is based on <b>Order Management > Forecasting > Forecast Customer</b> that were previously input.</li>
                <li><b>The Grand Total</b> is the sum from each data filtered through the <b>Filter Period</b> or <b>Filter Product No.</b> </li>
            </ul>
        </div>
    </div>
</div>

<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" id="filter_period_year" value="<?= date("Y") ?>" class="easyui-combobox">
                <input style="width:30%;" id="filter_period_month" value="<?= date("m") ?>" class="easyui-combobox"> 
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
    </div>
    <div style="margin-left: 10px; margin-bottom:5px;">
        <?= $button ?>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
    </div>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 450px; border: 0;"></iframe>
</div>

<script>
    function filter() {
        var filter_period_year = $("#filter_period_year").datebox("getValue");
        var filter_period_month = $("#filter_period_month").datebox("getValue");
        var filter_item_fg = $("#filter_item_fg").combobox("getValue");
        var url = "?filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_item_fg=" + filter_item_fg;
        if (filter_period_year == "" && filter_period_month == "") {
            toastr.warning("Please select Periode & Product No.!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/summary_forecasts/print') ?>' + url);
        }
    }

    function excel() {
        var filter_period_year = $("#filter_period_year").datebox("getValue");
        var filter_period_month = $("#filter_period_month").datebox("getValue");
        var filter_item_fg = $("#filter_item_fg").combogrid("getValue");
        var url = "?filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_item_fg=" + filter_item_fg;
        if (filter_period_year == "" && filter_period_month == "") {
            toastr.warning("Please select Periode & Product No.!");
        } else {
            window.location.assign('<?= base_url('planning/summary_forecasts/print/excel') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }

    $('#filter_period_year').combobox({
        url: '<?= base_url('planning/summary_forecasts/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_period_month').combobox({
        url: '<?= base_url('planning/summary_forecasts/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_item_fg').combogrid({
        url: '<?= base_url("master/item_fg/reads") ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Product No.",
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

    $(function() {
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        
    });
</script>
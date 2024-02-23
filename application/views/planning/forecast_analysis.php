<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="CONDITIONS" style="padding: 20px;">
            <ul>
                <li><b>Filter Data</b> is to show Forecast Analyst base on <b>Filter Customer</b> or <b>Filter Product No</b>, The data is based on <b>Order Management > Forecasting > Forecast Customer</b> that were previously input.</li>
                <li><b>The Data Forecast</b> is based on <b>Order Management > Forecasting > Forecast Customer</b> that were previously input.</li>
                <li><b>The Data 4 Month</b> is based on <b>Calculating</b></li>
                    <ul>
                        <li><b>The Data 4 month table for the 4th month</b>: Data (1st Month + 2nd Month + 3rd Month + 4th Month) / 4</b></li>
                        <li><b>The Data 4 month table for the 5th month</b>: Data (2nd Month + 3rd Month + 4th Month + 5th Month) / 4</b></li>
                        <li><b>The Data 4 month table for the 6th month</b>: Data (3rd Month + 4th Month + 5th Month + 6th Month) / 4</b></li>
                        <li><b>The Data 4 month table for the 7th month</b>: Data (4th Month + 5th Month + 6th Month + 7th Month) / 4</b></li>
                        <li><b>The Data 4 month table for the 8th month</b>: Data (5th Month + 6th Month + 7th Month + 8th Month) / 4</b></li>
                        <li><b>The Data 4 month table for the 9th month</b>: Data (6th Month + 7th Month + 8th Month + 9th Month) / 4</b></li>
                        <li><b>The Data 4 month table for the 10th month</b>: Data (7th Month + 8th Month + 9th Month + 10th Month) / 4</b></li>
                        <li><b>The Data 4 month table for the 11th month</b>: Data (8th Month + 9th Month + 10th Month + 11th Month) / 4</b></li>
                        <li><b>The Data 4 month table for the 12th month</b>: Data (9th Month + 10th Month + 11th Month + 12th Month) / 4</b></li>
                    </ul>
                <li><b>The Data 6 Month</b> is based on <b>Calculating</b></li>
                    <ul>
                        <li><b>The Data 6 month table for the 6th month</b>: Data (1st Month + 2nd Month + 3rd Month + 4th Month + 5th Month + 6th Month) / 6</b></li>
                        <li><b>The Data 6 month table for the 7th month</b>: Data (2nd Month + 3rd Month + 4th Month + 5th Month + 6th Month + 7th Month) / 6</b></li>
                        <li><b>The Data 6 month table for the 8th month</b>: Data (3rd Month + 4th Month + 5th Month + 6th Month + 7th Month + 8th Month) / 6</b></li>
                        <li><b>The Data 6 month table for the 9th month</b>: Data (4th Month + 5th Month + 6th Month + 7th Month + 8th Month + 9th Month) / 6</b></li>
                        <li><b>The Data 6 month table for the 10th month</b>: Data (5th Month + 6th Month + 7th Month + 8th Month + 9th Month + 10th Month) / 6</b></li>
                        <li><b>The Data 6 month table for the 11th month</b>: Data (6th Month + 7th Month + 8th Month + 9th Month + 10th Month + 11th Month) / 6</b></li>
                        <li><b>The Data 6 month table for the 12th month</b>: Data (7th Month + 8th Month + 9th Month + 10th Month + 11th Month + 12th Month) / 6</b></li>
                    </ul>
                <li><b>The Data 12 Month</b> is based on <b>Calculating</b></li>
                    <ul>
                        <li><b>The Data 12 month table for the 12th month</b>: Data (1st Month + 2nd Month + 3rd Month + 4th Month + 5th Month + 6th Month + 7th Month + 8th Month + 9th Month + 10th Month + 11th Month + 12th Month) / 12</b></li>
                    </ul>
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
                <span style="width:35%; display:inline-block;">Customer Name</span>
                <input style="width:60%;" id="filter_customer_name" class="easyui-combogrid">
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
        var filter_customer_name = $("#filter_customer_name").combobox("getValue");
        var filter_item_fg = $("#filter_item_fg").combobox("getValue");
        var url = "?filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_customer_name=" + filter_customer_name +
            "&filter_item_fg=" + filter_item_fg;
        if (filter_period_year == "" && filter_period_month == "") {
            toastr.warning("Please select Periode, Customer, and Product No.!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/forecast_analysis/print') ?>' + url);
        }
    }

    function excel() {
        var filter_period_year = $("#filter_period_year").datebox("getValue");
        var filter_period_month = $("#filter_period_month").datebox("getValue");
        var filter_customer_name = $("#filter_customer_name").combobox("getValue");
        var filter_item_fg = $("#filter_item_fg").combogrid("getValue");
        var url = "?filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_customer_name=" + filter_customer_name +
            "&filter_item_fg=" + filter_item_fg;
        if (filter_period_year == "" && filter_period_month == "") {
            toastr.warning("Please select Periode, Customer, and Product No.!");
        } else {
            window.location.assign('<?= base_url('planning/forecast_analysis/print/excel') ?>' + url);
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

    $('#filter_customer_name').combogrid({
        url: '<?= base_url("master/customers/reads") ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Customer",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        columns: [
            [{
                field: 'number',
                title: 'Code',
                width: 100
            }, {
                field: 'name',
                title: 'Customer Name',
                width: 300
            }]
        ],
    });

    

    // $(function() {
    //     var filter_period_year = $("#filter_period_year").combobox('getValue');
    //     var filter_period_month = $("#filter_period_month").combobox('getValue');
        
    // });
</script>
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div id="toolbar" style="padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_items" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Division</span>
                <input style="width:60%;" name="filter_division" id="filter_division" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" id="filter_customer_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                    <option value="0">Active</option>
                    <option value="1">Inactive</option>
                </select>
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
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combogrid('getValue');

        url = "?&filter_items=" + filter_items + "&filter_division=" + filter_division + "&filter_status=" + filter_status  + "&filter_customer_id=" + filter_customer_id;
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('master/report_master_data_eng/print') ?>' + url);
    }

    function excel() {
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combogrid('getValue');

        url = "?&filter_items=" + filter_items + "&filter_division=" + filter_division + "&filter_status=" + filter_status  + "&filter_customer_id=" + filter_customer_id;

        // Tampilkan overlay
        $("#loadingOverlay").show();

        // Unduh file
        window.location.assign('<?= base_url('master/report_master_data_eng/print/excel') ?>' + url);

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

    $('#filter_customer_id').combogrid({
        url: '<?= base_url('master/customers/reads/'); ?>',
        panelWidth: 420,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Customer",
        columns: [
            [{
                field: 'number',
                title: 'Customer Code',
                width: 120
            }, {
                field: 'name',
                title: 'Customer Name',
                width: 250
            }, ]
        ],
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
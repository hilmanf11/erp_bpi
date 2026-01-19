<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div id="toolbar" style="padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery Date</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Division</span>
                <input style="width:60%;" id="filter_division" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Category</span>
                <input style="width:60%;" id="filter_category_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier Name</span>
                <input style="width:60%;" id="filter_supplier_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Display By</span>
                <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">
                    <option value="DETAIL">DETAIL</option>
                    <option value="SUMMARY">SUMMARY</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part Name</span>
                <input style="width:60%;" id="filter_item_rm_name" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part Number</span>
                <input style="width:60%;" id="filter_item_rm_number" class="easyui-combogrid">
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
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_supplier_id = $("#filter_supplier_id").combobox('getValue');
        var filter_category_id = $("#filter_category_id").combobox('getValue');
        var filter_item_rm_name = $("#filter_item_rm_name").combogrid('getValue');
        var filter_item_rm_number = $("#filter_item_rm_number").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + 
        "&filter_division=" + filter_division + "&filter_display=" + filter_display + 
        "&filter_supplier_id=" + filter_supplier_id + "&filter_category_id=" + filter_category_id +
        "&filter_item_rm_name=" + filter_item_rm_name + "&filter_item_rm_number=" + filter_item_rm_number;
        
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/purchase_report/print') ?>' + url);
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_supplier_id = $("#filter_supplier_id").combobox('getValue');
        var filter_category_id = $("#filter_category_id").combobox('getValue');
        var filter_item_rm_name = $("#filter_item_rm_name").combogrid('getValue');
        var filter_item_rm_number = $("#filter_item_rm_number").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + 
        "&filter_division=" + filter_division + "&filter_display=" + filter_display + 
        "&filter_supplier_id=" + filter_supplier_id + "&filter_category_id=" + filter_category_id +
        "&filter_item_rm_name=" + filter_item_rm_name + "&filter_item_rm_number=" + filter_item_rm_number;

        // Tampilkan overlay
        $("#loadingOverlay").show();

        // Unduh file
        window.location.assign('<?= base_url('finance/purchase_report/print/excel') ?>' + url);

        // Sembunyikan overlay setelah beberapa saat
        setTimeout(function () {
            $("#loadingOverlay").hide();
        }, 3000); // Sesuaikan waktu jika perlu
    }

    $('#filter_division').combobox({
        url: '<?= base_url('finance/purchase_report/readsDivision/'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: 'Choose Division',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $("#filter_supplier_id").combobox({
        url: '<?= base_url('master/suppliers/reads') ?>',
        valueField: 'id',
        textField: 'name',
        prompt: "Select Supplier",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }]
    });

    $("#filter_category_id").combobox({
        url: '<?= base_url('master/item_categories/readsnotfg') ?>',
        valueField: 'id',
        textField: 'name',
        prompt: "Select Categories"
    });

    $('#filter_item_rm_name').combogrid({
        url: '<?= base_url('master/item_rm/reads/') ?>',
        panelWidth: 420,
        idField: 'name',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Part Name",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],

        columns: [
            [{
                field: 'number',
                title: 'Part No',
                width: 100
            }, {
                field: 'name',
                title: 'Part Name',
                width: 200
            }, ]
        ]
    });

    $('#filter_item_rm_number').combogrid({
        url: '<?= base_url('master/item_rm/reads/') ?>',
        panelWidth: 420,
        idField: 'number',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Part Number",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],

        columns: [
            [{
                field: 'number',
                title: 'Part No',
                width: 100
            }, {
                field: 'name',
                title: 'Part Name',
                width: 200
            }, ]
        ]
    });

    $("#filter_display").combobox({
        onChange: function(display){
            if(display === 'DETAIL'){
                $('#filter_item_rm_name').combogrid('enable');
                $('#filter_item_rm_number').combogrid('enable');
            } else {
                $('#filter_item_rm_name').combogrid('enable');
                $('#filter_item_rm_number').combogrid('enable');
            }
        }
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
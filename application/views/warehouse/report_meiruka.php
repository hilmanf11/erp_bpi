<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div class="easyui-accordion" style="width:100%;">
    <div title="Hide Menu" data-options="selected:true" style="padding:10px; background:#F4F4F4;">
        <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <div style="width: 50%; float: left;">
                <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                    <legend><b></b></legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Period Date</span>
                        <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                        <input style="width:29%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Division</span>
                        <input style="width:60%;" id="filter_division" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Category</span>
                        <input style="width:60%;" id="filter_item_category" class="easyui-combobox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    </div>
                </fieldset>
            </div>
            <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                <legend><b></b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_item_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_items" class="easyui-combobox">
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose Status</option>
                        <option value="OK">OK</option>
                        <option value="OVER">DELIVERY</option>
                        <option value="UNDER">UNDER</option>
                    </select>
                </div> -->
            </fieldset>
        </div>
        <?= $button ?>
    </div>
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

    // function filter() {
    //     var filter_from = $("#filter_from").datebox('getValue');
    //     var filter_to = $("#filter_to").datebox('getValue');
    //     var filter_item_category = $("#filter_item_category").combobox('getValue');
    //     var filter_item_family = $("#filter_item_family").combobox('getValue');
    //     var filter_items = $("#filter_items").combobox('getValue');
    //     var filter_division = $("#filter_division").combobox('getValue');
    //     var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_item_category=" + filter_item_category + "&filter_item_family=" + filter_item_family + "&filter_items=" + filter_items + "&filter_division=" + filter_division;
    //     $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
    //     $("#printout").attr('src', '<?= base_url('warehouse/report_meiruka/print') ?>' + url);
    // }

    var auto_filter_values = {};
    var intervalId = null;

    function filter() {
        auto_filter_values = {
            filter_from: $("#filter_from").datebox('getValue'),
            filter_to: $("#filter_to").datebox('getValue'),
            filter_item_category: $("#filter_item_category").combobox('getValue'),
            filter_item_family: $("#filter_item_family").combobox('getValue'),
            filter_items: $("#filter_items").combobox('getValue'),
            filter_division: $("#filter_division").combobox('getValue')
            // filter_status: $("#filter_status").combobox('getValue')
        };

        console.log("Filter triggered", auto_filter_values); // debug log
        loadReport(auto_filter_values);

        if (intervalId) clearInterval(intervalId);

        intervalId = setInterval(function () {
            console.log("Auto-refresh triggered", auto_filter_values);
            loadReport(auto_filter_values);
        }, 60000); // refresh tiap 1 menit
    }

    function loadReport(filters) {
        var url = "?filter_from=" + filters.filter_from +
                "&filter_to=" + filters.filter_to +
                "&filter_item_category=" + filters.filter_item_category +
                "&filter_item_family=" + filters.filter_item_family +
                "&filter_items=" + filters.filter_items +
                "&filter_division=" + filters.filter_division;
                // "&filter_status=" + filters.filter_status;

        const iframe = document.getElementById("printout");

        if (iframe) {
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            if (doc && doc.body) {
                doc.body.innerHTML = "<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>";
            }
            iframe.src = '<?= base_url('warehouse/report_meiruka/print') ?>' + url;
        } else {
            console.error("Iframe #printout not found!");
        }
    }

    // Debug: Cek kalau iframe berhasil load
    document.getElementById("printout").onload = function() {
        console.log("Iframe loaded successfully");
    };

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_category = $("#filter_item_category").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_items = $("#filter_items").combobox('getValue');
        var filter_division = $("#filter_division").combobox('getValue');
        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_item_category=" + filter_item_category + "&filter_item_family=" + filter_item_family + "&filter_items=" + filter_items + "&filter_division=" + filter_division;

        // Tampilkan overlay
        $("#loadingOverlay").show();

        // Unduh file
        window.location.assign('<?= base_url('warehouse/report_meiruka/print/excel') ?>' + url);

        // Sembunyikan overlay setelah beberapa saat
        setTimeout(function () {
            $("#loadingOverlay").hide();
        }, 3000); // Sesuaikan waktu jika perlu
    }

    $(function() {
        $("#filter_item_category").combobox({
            url: '<?= base_url('master/item_categories/readsnotfg') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Categories",
            onSelect: function(category) {
                $("#filter_item_family").combobox({
                    url: '<?= base_url('warehouse/report_history_transactions/readItemFamily/') ?>' + category.id,
                    valueField: 'number',
                    textField: 'name',
                    prompt: "Select Product Family",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                    onSelect: function(row) {
                        $('#filter_items').combobox({
                            url: '<?= base_url('master/item_rm/read/') ?>' + row.id,
                            valueField: 'id',
                            textField: 'number',
                            prompt: "Select Product No",
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

    $("#filter_item_family").combobox({
        url: '<?= base_url('warehouse/report_history_transactions/readItemFamilys/') ?>',
        valueField: 'number',
        textField: 'name',
        prompt: "Select Product Family",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onSelect: function(row) {
            $('#filter_items').combobox({
                url: '<?= base_url('master/item_rm/read/') ?>' + row.id,
                valueField: 'id',
                textField: 'number',
                prompt: "Select Product No",
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
            });
        }
    });

    $('#filter_items').combobox({
        url: '<?= base_url('master/item_rm/reads/') ?>',
        valueField: 'id',
        textField: 'number',
        prompt: "Select Product No",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'number',
        textField: 'number',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
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
<div id="f" class="easyui-panel" style="width:100%; padding:10px; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Label No</span>
                    <input style="width:60%;" id="filter_label_no" class="easyui-textbox" data-options="prompt:'Input Label No'">
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
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" id="filter_item_category" class="easyui-combobox">
                </div>
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
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="-">Select ALL</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div> -->
            </div>
        </fieldset>
    </div>
    <div style="margin-left: 10px; margin-bottom:5px;">
        <?= $button ?>
    </div>
</div>
<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 450px; border: 0;"></iframe>
</div>
<script>
    function filter() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_label_no = $("#filter_label_no").textbox("getValue");
        var filter_division = $("#filter_division").combobox("getValue");
        var filter_item_category = $("#filter_item_category").combobox("getValue");
        var filter_item_family = $("#filter_item_family").combobox("getValue");
        var filter_items = $("#filter_items").combobox("getValue");

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_label_no=" + window.btoa(filter_label_no) +
            "&filter_division=" + window.btoa(filter_division) +
            "&filter_item_category=" + window.btoa(filter_item_category) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_items=" + window.btoa(filter_items);

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Trans Date & Supplier!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('warehouse/report_check_serialno_sto_rm/print') ?>' + url);
        }
    }
    function excel() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_label_no = $("#filter_label_no").textbox("getValue");
        var filter_division = $("#filter_division").combobox("getValue");
        var filter_item_category = $("#filter_item_category").combobox("getValue");
        var filter_item_family = $("#filter_item_family").combobox("getValue");
        var filter_items = $("#filter_items").combobox("getValue");
        
        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_label_no=" + window.btoa(filter_label_no) +
            "&filter_division=" + window.btoa(filter_division) +
            "&filter_item_category=" + window.btoa(filter_item_category) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_items=" + window.btoa(filter_items);

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Trans Date & Supplier!");
        } else {
            window.location.assign('<?= base_url('warehouse/report_check_serialno_sto_rm/print/excel') ?>' + url);
        }
    }
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    function reload() {
        window.location.reload();
    }
    $(function() {
        $("#filter_item_category").combobox({
            url: '<?= base_url('warehouse/report_sto_raw_materials/readsNotfg') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Categories",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(category) {
                $("#filter_item_family").combobox({
                    url: '<?= base_url('warehouse/report_sto_raw_materials/readItemFamily/') ?>' + category.id,
                    valueField: 'id',
                    textField: 'name',
                    prompt: "Choose Product Family",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $("#filter_item_category").combobox({
            url: '<?= base_url('warehouse/report_sto_raw_materials/readsNotfg') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Categories",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_item_family").combobox({
            url: '<?= base_url('warehouse/report_sto_raw_materials/readItemFamilys') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Product Family",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_division').combobox({
            url: '<?= base_url('warehouse/sto_raw_materials/readsDivision/'); ?>',
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
<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:40%;" id="filter_date" value="<?= date("Y-m-d") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Foreign Gain/Loss</span>
                <input style="width:60%;" id="filter_foreign" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Journal Type</span>
                <select style="width:60%;" id="filter_journal" class="easyui-combobox">
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Revalue Currencies</b></legend>

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
        var filter_date = $("#filter_from").datebox("getValue");
        var filter_foreign = $("#filter_foreign").combobox("getValue");
        var filter_journal = $("#filter_journal").combobox("getValue");
        
        var url = "?filter_date=" + window.btoa(filter_date) +
            "&filter_foreign=" + filter_foreign +
            "&filter_journal=" + filter_journal;
        if (filter_date == "" || filter_foreign == "" || filter_journal == "") {
            toastr.warning("Please select Foreign and Journal Type!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('finance/foreign_currencies/print') ?>' + url);
        }
    }

    function excel() {
        var filter_date = $("#filter_from").datebox("getValue");
        var filter_foreign = $("#filter_foreign").combobox("getValue");
        var filter_journal = $("#filter_journal").combobox("getValue");
        
        var url = "?filter_date=" + window.btoa(filter_date) +
            "&filter_foreign=" + filter_foreign +
            "&filter_journal=" + filter_journal;
        if (filter_date == "" || filter_foreign == "" || filter_journal == "") {
            toastr.warning("Please select Foreign and Journal Type!");
        } else {
            window.location.assign('<?= base_url('finance/foreign_currencies/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }
    
    $(function() {
        $('#filter_foreign').combobox({
            url: '<?php echo base_url('finance/account_coas/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Account ID',
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
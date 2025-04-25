<div id="f" class="easyui-panel" style="width:100%; padding: 10px; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="float: left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Account No</span>
                    <input style="width:60%;" id="filter_account" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:30%;" id="filter_currency" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
    </div>
    
    <div style="margin-left: 10px; margin-bottom:5px;">
        <?= $button ?>
    </div>
</div>

<div id="p" class="easyui-panel" title="Print Preview" data-options="fit:true" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 70%; border: 0;"></iframe>
</div>

<script>
    function filter() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_account = $("#filter_account").combogrid("getValue");
        var filter_currency = $("#filter_currency").combobox("getValue");

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_currency=" + window.btoa(filter_currency) +
            "&filter_account=" + window.btoa(filter_account);

        if (filter_from == "" || filter_to == "" || filter_account == "") {
            toastr.warning("Please select Trans Date & Account No!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('finance/report_bank_statements/print') ?>' + url);
        }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_account = $("#filter_account").combogrid("getValue");
        var filter_currency = $("#filter_currency").combobox("getValue");

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_currency=" + window.btoa(filter_currency) +
            "&filter_account=" + window.btoa(filter_account);

        if (filter_from == "" || filter_to == "" || filter_account == "") {
            toastr.warning("Please select Trans Date & Account No!");
        } else {
            window.location.assign('<?= base_url('finance/report_bank_statements/print/excel') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#filter_account').combogrid({
            url: '<?= base_url('finance/account_coa/readBanks') ?>',
            panelWidth: 320,
            idField: 'account_number',
            textField: 'account_number',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Account No',
            columns: [
                [{
                    field: 'account_number',
                    title: 'Account No',
                    width: 100
                }, {
                    field: 'account_name',
                    title: 'Account Name',
                    width: 200
                }, ]
            ],
        });

        $("#filter_currency").combobox({
            url: '<?= base_url('master/currencies/reads') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Choose Currencies",
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
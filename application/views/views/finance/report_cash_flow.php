<div id="f" class="easyui-panel" style="width:100%; padding:10px; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cutoff</span>
                <input style="width:60%;" id="filter_date" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Cutoff Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
    </div>
    
    <div style="margin-left: 10px; margin-bottom:5px;">
        <?= $button ?>
    </div>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 530px; border: 0;"></iframe>
</div>

<script>
    function filter() {
        var filter_date = $("#filter_date").datebox("getValue");

        var url = "?filter_date=" + window.btoa(filter_date);

        if (filter_date == "") {
            toastr.warning("Please select Trans Date!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('finance/report_cash_flow/print') ?>' + url);
        }
    }

    function excel() {
        var filter_date = $("#filter_date").datebox("getValue");

        var url = "?filter_date=" + window.btoa(filter_date);

        if (filter_date == "") {
            toastr.warning("Please select Trans Date!");
        } else {
            window.location.assign('<?= base_url('finance/report_cash_flow/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
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
</script>
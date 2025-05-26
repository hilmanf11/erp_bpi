<div id="p" class="easyui-panel" title="Working Calendar" data-options="fit:true" style="width:100%; background: #F4F4F4; padding: 10px;">
    <form id="frm_insert" method="post">
        <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px; float: left;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <select style="width:30%;" name="filter_month" id="filter_month" class="easyui-combobox" data-options="prompt:'Month'">
                    <option value="01">January</option>
                    <option value="02">February</option>
                    <option value="03">March</option>
                    <option value="04">April</option>
                    <option value="05">May</option>
                    <option value="06">June</option>
                    <option value="07">July</option>
                    <option value="08">August</option>
                    <option value="09">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
                <input style="width:30%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" class="easyui-numberbox" data-options="prompt:'Year', validType: 'length[0,4]'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="add()"><i class="fa fa-save"></i> Save Data</a>
            </div>
        </fieldset>
        <fieldset style="width: 20%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px; float: left;">
            <legend><b>HKW</b></legend>
            <p style="text-align: center; font-size: 49px !important; margin:0;" id="hkw">0</p>
        </fieldset>
        <div id="calendar">

        </div>
    </form>
</div>
<script>
    //Add Data
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');

        $('#frm_insert').form('submit', {
            url: '<?= base_url('master/calendars/create') ?>',
            method: 'POST',
            onSubmit: function() {
                return $(this).form('validate');
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                if (result.theme == "success") {
                    toastr.success(result.message, result.title);

                    calendars(filter_month, filter_year);
                    hkw(filter_month, filter_year);
                } else {
                    toastr.error(result.message, result.title);
                }
            }
        });
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            calendars(filter_month, filter_year);
            hkw(filter_month, filter_year);
        }
    }

    function calendars(month = "", year = "") {
        $.ajax({
            type: "post",
            url: "<?= base_url('master/calendars/calendars') ?>",
            data: "month=" + month + "&year=" + year,
            dataType: "html",
            success: function(response) {
                $("#calendar").html(response);
            }
        });
    }

    function hkw(month = "", year = "") {
        $.ajax({
            type: "post",
            url: "<?= base_url('master/calendars/hkw') ?>",
            data: "month=" + month + "&year=" + year,
            dataType: "html",
            success: function(response) {
                $("#hkw").html(response);
            }
        });
    }

    $(function() {
        $("#filter_month").combobox('setValue', '<?= date("m") ?>');
        calendars();
        hkw();
    });
</script>
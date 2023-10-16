<div id="f" class="easyui-panel" style="width:100%; background: #F4F4F4; padding: 10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 35%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" name="filter_month" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                <input style="width:30%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Year'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <input style="width:60%;" name="filter_revision" id="filter_revision" value="<?= "0" ?>" class="easyui-combobox" data-options="prompt:'Revision'" panelHeight="auto">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" id="filter_customer" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="filter_product_no" id="filter_product_no" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="formula()"><i class="fa fa-list"></i> Formula</a>
            </div>
        </fieldset>
        <fieldset style="width: 15%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Component Check</b></legend>
            <div style="margin:12px;">
                <input class="easyui-checkbox" id="check_forecast" value="on" readonly="true"> &nbsp; Forecast
            </div>
            <div style="margin:12px;">
                <input class="easyui-checkbox" id="check_fg" value="on" readonly="true"> &nbsp; Stock Finish Good
            </div>
            <div style="margin:12px;">
                <input class="easyui-checkbox" id="check_wip" value="on" readonly="true"> &nbsp; Stock WIP
            </div>
            <div style="margin:12px;">
                <input class="easyui-checkbox" id="check_so" value="on" readonly="true"> &nbsp; Sales Order
            </div>
            <div style="margin:12px;">
                <input class="easyui-checkbox" id="check_ost_so" value="on" readonly="true"> &nbsp; OST Sales Order
            </div>
            <div style="margin:12px;">
                <input class="easyui-checkbox" id="check_ost_mpp" value="on" readonly="true"> &nbsp; OST MPP
            </div>
        </fieldset>
        <fieldset style="width: 15%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Working Calendar</b></legend>
            <div id="showWorkingCalendar">

            </div>
        </fieldset>
        <fieldset style="width: 33%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Process Generate Data</b></legend>
            <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
            <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <div id="p_remarks" class="easyui-panel" style="width:100%; height:120px; padding:10px; margin-top: 10px; overflow: auto;">
                <ul id="remarks">

                </ul>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<div id="dlg-formula" class="easyui-dialog" title="Formula" style="width: 600px; padding:10px; top: 20px;" data-options="closed: true, modal:false">
    <ul>
        <li>TOTAL STOCK = <b>(WIP + FG + OST MPP)</b></li>
        <li>BALANCE AWAL = <b>(TOTAL STOCK - OST SO)</b></li>
        <li>ITO = <b>(BALANCE AWAL / DELIVERY RATE)</b></li>
        <li>DELIVERY RATE = <b>(FC / HKW)</b></li>
        <li>SAFETY STOCK = <b>((LEADTIME + FG SS) / HKW(next month) x FC(next month))</b></li>
        <li>PROD PLAN = <b>(FC + SAFETY STOCK - BALANCE AWAL)</b></li>
    </ul>

    <i>*) Next month everything is the same</i>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 450px; border: 0;"></iframe>
</div>

<script>
    function formula() {
        $("#dlg-formula").dialog('open');
    }

    //Add Data
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var check_forecast = $("#check_forecast").checkbox('options');
        var check_fg = $("#check_fg").checkbox('options');
        var check_wip = $("#check_wip").checkbox('options');
        var check_ost_so = $("#check_ost_so").checkbox('options');
        var check_ost_mpp = $("#check_ost_mpp").checkbox('options');
        var check_so = $("#check_so").checkbox('options');

        if (check_forecast.checked == true && check_fg.checked == true && check_wip.checked == true && check_ost_so.checked == true && check_ost_mpp.checked == true == check_so.checked == true) {
            $.messager.prompt('Generate MPS', 'Please input Password Generate', function(r) {
                if (r == "GENERATEMPS") {
                    Swal.fire({
                        title: 'Please Wait for Push Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });
                    Swal.fire({
                        title: 'Please Wait 5 - 10 Minutes for Generating Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $.ajax({
                        type: "get",
                        url: "<?= base_url('planning/generate_mps/getdata') ?>",
                        data: "filter_month=" + window.btoa(filter_month) +
                            "&filter_year=" + window.btoa(filter_year) +
                            "&filter_revision=" + window.btoa(filter_revision) +
                            "&filter_customer=" + window.btoa(filter_customer) +
                            "&filter_product_no=" + window.btoa(filter_product_no),
                        dataType: "json",
                        success: function(rows) {
                            Swal.close();
                            requestData(rows['total'], rows);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.post('<?= base_url('planning/generate_mps/create') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');
                                        if (result.theme == "success") {
                                            Swal.close();
                                            $('#p_success').html(success);
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            $('#p_failed').html(failed);
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                            //Json Failed
                                            $.ajax({
                                                type: "POST",
                                                async: true,
                                                url: "<?= base_url('planning/generate_mps/uploadcreateFailed') ?>",
                                                data: {
                                                    data: json[number - 1],
                                                    message: result.message
                                                },
                                                cache: false
                                            });

                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }

                                        if (value == 100) {
                                            Swal.fire('Good job!', 'Process Save Data Completed!', 'success');
                                        }

                                        $("#p_remarks").append(title + "<br>");
                                    }).fail(function(jqXHR, textStatus) {
                                        if (textStatus == "error") {
                                            Swal.fire({
                                                title: 'Connection Time Out, Check Your Connection',
                                                showConfirmButton: false,
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                didOpen: () => {
                                                    Swal.showLoading();
                                                },
                                            });

                                            requestData(total, json, number, value, success + 0, failed + 0);
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            });
        } else {
            toastr.warning("Component Check Not Complete ", "Information");
        }
    }

    function downloadFailed() {
        window.open('<?= base_url('planning/generate_mps/uploadDownloadFailed') ?>', '_blank');
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_product_no=" + window.btoa(filter_product_no);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/generate_mps/print') ?>' + url);
        }
    }

    function revisionSelected(filter_month, filter_year) {
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/generate_mps/revision') ?>",
            data: "filter_month=" + filter_month + "&filter_year=" + filter_year,
            dataType: "html",
            success: function(response) {
                $("#filter_revision").combobox('setValue', response);
            }
        });
    }

    function componentCheck(filter_month, filter_year, filter_revision) {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mps/checkForecast') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision),
            dataType: "json",
            success: function(ost_so) {
                if (ost_so.theme == "success") {
                    $('#check_forecast').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_forecast').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mps/checkFg') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision),
            dataType: "json",
            success: function(ost_so) {
                if (ost_so.theme == "success") {
                    $('#check_fg').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_fg').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mps/checkOs') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision),
            dataType: "json",
            success: function(ost_so) {
                if (ost_so.theme == "success") {
                    $('#check_so').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_so').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mps/checkOstSo') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision),
            dataType: "json",
            success: function(ost_so) {
                if (ost_so.theme == "success") {
                    $('#check_ost_so').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_ost_so').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mps/checkStockWip') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision),
            dataType: "json",
            success: function(ost_so) {
                if (ost_so.theme == "success") {
                    $('#check_wip').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_wip').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mps/checkOstMpp') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision),
            dataType: "json",
            success: function(ost_so) {
                if (ost_so.theme == "success") {
                    $('#check_ost_mpp').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_ost_mpp').checkbox({
                        checked: false
                    });
                }
            }
        });
    }

    function calendarCheck(filter_month, filter_year, filter_revision) {
        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_mps/checkCalendar') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision),
            dataType: "html",
            success: function(html) {
                $("#showWorkingCalendar").html(html);
            }
        });
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_line_no = $("#filter_line_no").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_line_no=" + window.btoa(filter_line_no) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_product_no=" + window.btoa(filter_product_no);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/generate_mps/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $("#add").html('Generate');
        var month = $("#filter_month").combobox('getValue');
        var year = $("#filter_year").combobox('getValue');
        var revision = $("#filter_revision").combobox('getValue');

        componentCheck(month, year, revision);
        calendarCheck(month, year, revision);
        revisionSelected(month, year);

        $('#dg').datagrid({
            url: '<?= base_url('planning/generate_mps/datatables') ?>',
            rownumbers: true
        });

        $('#filter_month').combobox({
            url: '<?php echo base_url('planning/generate_mps/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Month',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onChange: function(row) {
                var month = $("#filter_month").combobox('getValue');
                var year = $("#filter_year").combobox('getValue');
                var revision = $("#filter_revision").combobox('getValue');

                if (year != "" || revision != "") {
                    componentCheck(month, year, revision);
                }

                if (year != "" || revision != "") {
                    calendarCheck(month, year, revision);
                }

                revisionSelected(month, year);
            }
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('planning/generate_mps/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Year',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onChange: function(row) {
                var month = $("#filter_month").combobox('getValue');
                var year = $("#filter_year").combobox('getValue');
                var revision = $("#filter_revision").combobox('getValue');

                if (month != "" || revision != "") {
                    componentCheck(month, year, revision);
                }

                if (month != "" || revision != "") {
                    calendarCheck(month, year, revision);
                }

                revisionSelected(month, year);
            }
        });

        $('#filter_revision').combobox({
            url: '<?php echo base_url('planning/generate_mps/readRevisions'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Revision',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onChange: function(row) {
                var month = $("#filter_month").combobox('getValue');
                var year = $("#filter_year").combobox('getValue');
                var revision = $("#filter_revision").combobox('getValue');

                if (month != "" || year != "") {
                    componentCheck(month, year, revision);
                }
            }
        });

        $('#filter_customer').combobox({
            url: '<?php echo base_url('master/customers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Customer',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(customer) {
                $('#filter_product_no').combogrid({
                    url: '<?= base_url('master/customer_items/reads/') ?>' + btoa(customer.id),
                    panelWidth: 400,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Product No",
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
                    ]
                });
            }
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
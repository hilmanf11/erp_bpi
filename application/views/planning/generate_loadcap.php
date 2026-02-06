<!-- <div id="dlg_check" class="easyui-dialog" title="Items Not in Menu Loading" style="width:400px;height:300px;padding:10px"
    data-options="modal:true,closed:true">
    <ul id="missing_items_list"></ul>
</div> -->
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
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Cut Off</span>
                <input style="width:60%;" id="filter_cutoff" class="easyui-datebox" value="<?= date("Y-m-d") ?>" required data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" id="filter_customer" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="filter_item_fg" id="filter_item_fg" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter_recap_machine()"><i class="fa fa-search"></i> Recap Machine</a>
            </div>
        </fieldset>
        <fieldset style="width: 15%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Working Calendar</b></legend>
            <div id="showWorkingCalendar">
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Process Generate Data</b></legend>
            <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
            <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <div id="p_remarks" class="easyui-panel" style="width:100%; height:120px; padding:10px; margin-top: 10px; overflow: auto;">
                <ul id="remarks">
                </ul>
            </div>

            <div class="fitem" style="text-align:left;">
                <a href="javascript:;" class="easyui-linkbutton" onclick="downloadFailed()">
                    <i class="fa fa-download"></i> List Failed
                </a>
            </div>
        </fieldset>
        <!-- <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Over List</b></legend>
            <div id="overList"></div>
        </fieldset> -->
    </div>
    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="excel_recap_machine();"><i class="fa fa-file"></i> Export Recap Machine</a>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 550px; border: 0;"></iframe>
</div>

<script>
    function formula(){
        Swal.fire({
            width: 600,
            html: `<div style="text-align:left;">
                    <center><b style="font-size:16px !important;">COMPONENT CHECK</b><hr></center>
                    <ul>
                        <li><b>Forecast</b> is taken from <b>Order Management > Forecasting > Forecast Customer</b></li>
                        <li><b>Stock Finish Good</b> is taken from <b>Stock FG Based on Cutoff</b></li>
                        <li><b>Stock WIP</b> is taken from <b>Production Schedule - RFG (start from M-1 to Cutoff)</b></li>
                        <li><b>Sales Order</b> is taken from <b>Sales Orders on Cutoff</b></li>
                        <li><b>OST Sales Order</b> is taken from <b>Sales Orders in Previous Month</b></li>
                        <li><b>OST MPP</b> is taken from <b>MPP Qty - Production Schedule Based on Cutoff</b></li>
                    </ul>
                    <center><b style="font-size:16px !important;">FORMULA</b><hr></center>
                    <ul>
                        <li>TOTAL STOCK = <b>(WIP + FG + OST MPP)</b></li>
                        <li>BEGIN BALANCE = <b>(TOTAL STOCK - OST SO)</b></li>
                        <li>BEGIN BALANCE NEXT MONTH = <b>((PRODPLAN + BEGIN BALANCE) - (FC or SO))</b></li>
                        <li>ITO = <b>(BEGIN BALANCE / DELIVERY RATE)</b></li>
                        <li>DELIVERY RATE = <b>((FC or SO) / HKW)</b></li>
                        <li>SAFETY STOCK = taken from Item Finish Good <b>% Safety Stock</b></li>
                        <li>PROD PLAN = <b>((FC or SO) + SAFETY STOCK - BEGIN BALANCE)</b></li>
                    </ul>

                    <i>*) Next month everything is the same</i>
                    </div>`,
        });
    }

    //Add Data
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_cutoff = $("#filter_cutoff").datebox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        $.messager.prompt('Generate Loadcap', 'Please input Password Generate', function(r) {
            if (r == "GENERATELOADCAP") {
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
                    url: "<?= base_url('planning/generate_loadcap/getdata') ?>",
                    data: "filter_month=" + window.btoa(filter_month) +
                        "&filter_year=" + window.btoa(filter_year) +
                        "&filter_revision=" + window.btoa(filter_revision) +
                        "&filter_cutoff=" + window.btoa(filter_cutoff) +
                        "&filter_customer=" + window.btoa(filter_customer) +
                        "&filter_item_fg=" + window.btoa(filter_item_fg),
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

                                $.post('<?= base_url('planning/generate_loadcap/create') ?>', {
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
                                            url: "<?= base_url('planning/generate_loadcap/uploadcreateFailed') ?>",
                                            data: {
                                                data: json[number - 1],
                                                message: result.message
                                            },
                                            cache: false
                                        });

                                        requestData(total, json, number + 1, value, success + 0, failed + 1);
                                    }

                                    if (value == 100) {
                                        $.ajax({
                                            type: "get",
                                            url: "<?= base_url('planning/generate_loadcap/rekapMachine') ?>",
                                            data: "filter_month=" + window.btoa(filter_month) +
                                                "&filter_year=" + window.btoa(filter_year) +
                                                "&filter_revision=" + window.btoa(filter_revision),
                                            beforeSend: function() {
                                                Swal.fire({
                                                    title: 'Summarizing Load per Machine...',
                                                    text: 'Please wait while calculating load data.',
                                                    allowOutsideClick: false,
                                                    showConfirmButton: false,
                                                    didOpen: () => Swal.showLoading()
                                                });
                                            },
                                            success: function(res) {
                                                Swal.fire({
                                                    title: 'Summarizing Load per Tonnage...',
                                                    text: 'Please wait...',
                                                    allowOutsideClick: false,
                                                    showConfirmButton: false,
                                                    didOpen: () => Swal.showLoading()
                                                });

                                                $.ajax({
                                                    type: "get",
                                                    url: "<?= base_url('planning/generate_loadcap/rekapTonnage') ?>",
                                                    data: {
                                                        filter_month: window.btoa(filter_month),
                                                        filter_year: window.btoa(filter_year),
                                                        filter_revision: window.btoa(filter_revision)
                                                    },
                                                    success: function(res) {
                                                        Swal.fire({
                                                            title: 'Summarizing Manpower...',
                                                            text: 'Please wait...',
                                                            allowOutsideClick: false,
                                                            showConfirmButton: false,
                                                            didOpen: () => Swal.showLoading()
                                                        });

                                                        $.ajax({
                                                            type: "get",
                                                            url: "<?= base_url('planning/generate_loadcap/rekapManPower') ?>",
                                                            data: {
                                                                filter_month: window.btoa(filter_month),
                                                                filter_year: window.btoa(filter_year),
                                                                filter_revision: window.btoa(filter_revision)
                                                            },
                                                            success: function() {
                                                                Swal.close();
                                                                Swal.fire('Good job!', 'Machine,tonnage and manpower recaps completed!', 'success');
                                                            }
                                                        });
                                                    }
                                                });
                                            }
                                        });
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
                    },
                    error: function(){
                        Swal.fire('Failed!', 'Process Calculating Data is Failed, Please Try Again!', 'error');
                    }
                });
            }
        });
    }
    
    function downloadFailed() {
        window.open('<?= base_url('planning/generate_loadcap/uploadDownloadFailed') ?>', '_blank');
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_cutoff = $("#filter_cutoff").datebox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_cutoff=" + window.btoa(filter_cutoff) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/generate_loadcap/print') ?>' + url);
        }
    }

    function filter_recap_machine() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_cutoff = $("#filter_cutoff").datebox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_cutoff=" + window.btoa(filter_cutoff) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/generate_loadcap/recap_machine') ?>' + url);
        }
    }

    function revisionSelected(filter_month, filter_year) {
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/generate_loadcap/revision') ?>",
            data: "filter_month=" + filter_month + "&filter_year=" + filter_year,
            dataType: "html",
            success: function(response) {
                $("#filter_revision").combobox('setValue', response);
            }
        });
    }

    function calendarCheck(filter_month, filter_year, filter_revision) {
        $.ajax({
            type: "get",
            url: "<?= base_url('planning/generate_loadcap/checkCalendar') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_revision=" + window.btoa(filter_revision),
            dataType: "html",
            success: function(html) {
                $("#showWorkingCalendar").html(html);
            }
        });
    }

    // function overList(filter_month, filter_year, filter_revision) {
    //     $("#overList").html('<small style="color:gray;">Loading over list...</small>');

    //     $.ajax({
    //         type: "get",
    //         url: "<?= base_url('planning/generate_loadcap/overList') ?>",
    //         data: {
    //             filter_month: window.btoa(filter_month),
    //             filter_year: window.btoa(filter_year),
    //             filter_revision: window.btoa(filter_revision)
    //         },
    //         dataType: "html",
    //         success: function(html) {
    //             $("#overList").html(html);

    //             // Penting: aktifkan kembali EasyUI parser agar datagrid bekerja
    //             $.parser.parse("#overList");

    //             // Optional: tambahkan auto-fit untuk tinggi kolom
    //             $('#dgOverList').datagrid('resize');
    //         },
    //         error: function() {
    //             $("#overList").html('<small style="color:red;">Failed to load over list.</small>');
    //         }
    //     });
    // }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/generate_loadcap/print/excel') ?>' + url);
        }
    }

    function excel_recap_machine() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/generate_loadcap/recap_machine/excel') ?>' + url);
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

        // Tunggu semua combobox selesai load sebelum menjalankan fungsi-fungsi awal
        setTimeout(function() {
            var month = $("#filter_month").combobox('getValue');
            var year = $("#filter_year").combobox('getValue');
            var revision = $("#filter_revision").combobox('getValue');

            calendarCheck(month, year, revision);
            // overList(month, year, revision);
            revisionSelected(month, year);
        }, 800); // beri jeda 0.8 detik agar combobox sudah siap

        $('#dg').datagrid({
            url: '<?= base_url('planning/generate_loadcap/datatables') ?>',
            rownumbers: true
        });

        $('#filter_month').combobox({
            url: '<?php echo base_url('planning/generate_loadcap/readMonths'); ?>',
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

                // if (year != "" || revision != "") {
                //     componentCheck(month, year, revision);
                // }

                if (year != "" || revision != "") {
                    calendarCheck(month, year, revision);
                    // overList(month, year, revision);
                }

                revisionSelected(month, year);
            }
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('planning/generate_loadcap/readYears'); ?>',
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

                // if (month != "" || revision != "") {
                //     componentCheck(month, year, revision);
                // }

                if (month != "" || revision != "") {
                    calendarCheck(month, year, revision);
                    // overList(month, year, revision);
                }

                revisionSelected(month, year);
            }
        });

        $('#filter_revision').combobox({
            url: '<?php echo base_url('planning/generate_loadcap/readRevisions'); ?>',
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

                calendarCheck(month, year, revision);
                // overList(month, year, revision);
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
                $('#filter_item_fg').combogrid({
                    url: '<?= base_url('master/customer_items/reads/') ?>' + btoa(customer.id),
                    panelWidth: 600,
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
                            width: 150
                        },{
                            field: 'number_customer',
                            title: 'Product Customer',
                            width: 200
                        }, {
                            field: 'name',
                            title: 'Description',
                            width: 200
                        }]
                    ]
                });
            }
        });

        $('#filter_item_fg').combogrid({
            url: '<?= base_url('master/item_fg/reads') ?>',
            panelWidth: 600,
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
                    width: 150
                },{
                    field: 'number_customer',
                    title: 'Product Customer',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 200
                }]
            ]
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

    // function checkMenuLoading() {
    //     $.ajax({
    //         url: '<?= base_url('planning/generate_loadcap/check_menu_loading') ?>',
    //         type: 'GET',
    //         dataType: 'json',
    //         success: function(response) {
    //             if (response.length > 0) {
    //                 var listHtml = '';
    //                 response.forEach(function(item) {
    //                     listHtml += '<li>' + item.item_number + '</li>';
    //                 });
    //                 $('#missing_items_list').html(listHtml);
    //             } else {
    //                 $('#missing_items_list').html('<li>All items are available in menu loading.</li>');
    //             }
    //             $('#dlg_check').dialog('open');
    //         },
    //         error: function() {
    //             toastr.error('Error fetching data');
    //         }
    //     });
    // }
</script>
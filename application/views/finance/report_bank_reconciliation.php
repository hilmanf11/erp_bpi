<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div id="toolbar" style="padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Filter Data</b></legend>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cut Off Date</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Bank Account</span>
                <input style="width:60%;" id="filter_bank_account" name="filter_bank_account" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Bank Name</span>
                <input style="width:60%;" id="filter_bank_name" name="filter_bank_name" class="easyui-textbox" readonly />
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 50%; float:left;">
        </div>

    </fieldset>
    <a href="javascript:;" class="easyui-linkbutton" style="padding: 5px;" onclick=""> Reconcile </a>
    <?= $button ?>
</div>

<!-- UPLOAD DATA -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success2">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed2">0</b></span>
    <div id="p_upload2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
    <div id="p_remarks2" title="Generating Process" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;">
    <iframe id="printout" src="" style="width: 100%; height:500px; border: 0;"></iframe>
</div>

<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; text-align: center; color: white; font-size: 20px; padding-top: 20%;">
    <b>Please Wait until Dialog download show up...</b>
</div>

<script>
    $(function() {

        $('#filter_bank_account').combogrid({
            url: '<?= base_url('finance/account_banks/reads') ?>',
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
                    field: 'bank_name',
                    title: 'Bank Name',
                    width: 200
                }, ]
            ],
            dataType: 'json',
            onSelect: function(index, row) {
                // console.log(row);
                $("#filter_bank_name").textbox('setValue', row.bank_name);
            }
        });

        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('finance/report_bank_reconciliation/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    alert('upload');

                    // $('#frm_upload').form('submit', {
                    //     url: '<?= base_url('finance/journal_postings/upload') ?>',
                    //     onSubmit: function() {
                    //         if ($(this).form('validate') == false) {
                    //             return $(this).form('validate');
                    //         } else {
                    //             $.messager.progress({
                    //                 title: 'Please Wait',
                    //                 msg: 'Importing Excel to Database'
                    //             });
                    //         }
                    //     },
                    //     success: function(result) {
                    //         $.messager.progress('close');
                    //         //Clear File
                    //         $.ajax({
                    //             url: "<?= base_url('finance/journal_postings/uploadclearFailed') ?>"
                    //         });
                    //         var json = eval('(' + result + ')');
                    //         requestData(json.total, json);

                    //         function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                    //             if (value < 100) {
                    //                 value = Math.floor((number / total) * 100);
                    //                 $('#p_upload2').progressbar('setValue', value);
                    //                 $('#p_start2').html(number);
                    //                 $('#p_finish2').html(total);
                    //                 $.ajax({
                    //                     type: "POST",
                    //                     async: true,
                    //                     url: "<?= base_url('finance/journal_postings/uploadCreate') ?>",
                    //                     data: {
                    //                         "data": json[number - 1]
                    //                     },
                    //                     cache: false,
                    //                     dataType: "json",
                    //                     success: function(result) {
                    //                         if (result.theme == "success") {
                    //                             $('#p_success2').html(success);
                    //                             var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                    //                             requestData(total, json, number + 1, value, success + 1, failed + 0);
                    //                         } else {
                    //                             $('#p_failed2').html(failed);
                    //                             var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                    //                             //Json Failed
                    //                             $.ajax({
                    //                                 type: "POST",
                    //                                 async: true,
                    //                                 url: "<?= base_url('finance/journal_postings/uploadcreateFailed') ?>",
                    //                                 data: {
                    //                                     data: json[number - 1],
                    //                                     message: result.message
                    //                                 },
                    //                                 cache: false
                    //                             });
                    //                             requestData(total, json, number + 1, value, success + 0, failed + 1);
                    //                         }
                    //                         $("#p_remarks2").append(title + "<br>");
                    //                     }
                    //                 });
                    //             }
                    //         }
                    //     }
                    // });
                }
            }]
        });


    });

    //DOWNLOAD TEMPLATE UPLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_bank_reconciliation.xls') ?>');
    }

    function reload() {
        window.location.reload();
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_bank_account = $("#filter_bank_account").combobox('getValue');

        url = "?filter_from=" + window.btoa(filter_from) + "&filter_to=" + window.btoa(filter_to) + 
        "&filter_bank_account=" + window.btoa(filter_bank_account);
        
        if (filter_bank_account !== "") {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('finance/report_bank_reconciliation/print') ?>' + url);
            
        } else {
            toastr.warning("Please select the Bank Account no!");
            $.messager.alert("Warning", "Please choose the Bank Account first!", 'warning');
        }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_bank_account = $("#filter_bank_account").combobox('getValue');

        url = "?filter_from=" + window.btoa(filter_from) + "&filter_to=" + window.btoa(filter_to) + 
        "&filter_bank_account=" + window.btoa(filter_bank_account);

        // Tampilkan overlay
        $("#loadingOverlay").show();

        // Unduh file
        window.location.assign('<?= base_url('finance/report_bank_reconciliation/print/excel') ?>' + url);

        // Sembunyikan overlay setelah beberapa saat
        setTimeout(function () {
            $("#loadingOverlay").hide();
        }, 3000); // Sesuaikan waktu jika perlu
    }

    //UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
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
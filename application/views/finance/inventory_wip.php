<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div id="toolbar" style="height: 235px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt Date</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Report Display</span>
                <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">
                    <option value="RECAP">RECAP</option>
                    <option value="DETAIL">DETAIL</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<div id="dlg_generate" class="easyui-dialog" title="Save Data" data-options="closed: true,modal:true,closable: false" style="width: 500px; padding:10px; top: 20px;">
    <div class="alert alert-warning" role="alert">
        Please wait until the save process is complete
    </div>
    <div id="p_upload" class="easyui-progressbar" style="width:460px; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:460px; height:200px; padding:10px; margin-top: 10px;">
        <p>History Save Data</p>
        <ul id="remarks">

        </ul>
    </div>
</div>

<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;">
    <iframe id="printout" src="" style="width: 100%; height:520px; border: 0;"></iframe>
</div>

<script>
    function add(){
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');

        $.ajax({
            type: "post",
            url: "<?= base_url('closing/locks/checkLock') ?>",
            data: "period=" + filter_from + "&menus_id=<?= $menus_id ?>",
            dataType: "json",
            success: function (lock) {
                if(lock.total > 0){
                    toastr.error("This period is not active by Accounting");
                    return false;
                }

                if(filter_from != "" && filter_to != ""){
                    $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                        if (r) {
                            Swal.fire({
                                title: 'Please Wait for Save Inventory Data',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            $("#p_remarks").html("");

                            $.ajax({
                                type: "post",
                                url: '<?= base_url('finance/inventory_wip/getData') ?>',
                                data: {
                                    filter_from: filter_from,
                                    filter_to: filter_to,
                                    filter_item_fg: filter_item_fg,
                                },
                                dataType: "json",
                                success: function(data) {
                                    Swal.close();

                                    if(data.total > 0){
                                        requestData(data.total, data.rows);
                                        $('#dlg_generate').dialog('open');

                                        function requestData(total, json, jml = 1, value = 0) {
                                            if (value < 100) {
                                                value = Math.floor((jml / total) * 100);
                                                var i = (jml - 1);

                                                $('#p_upload').progressbar('setValue', value);
                                                $('#p_start').html(jml);
                                                $('#p_finish').html(data.total);

                                                $.ajax({
                                                    type: "post",
                                                    url: '<?= base_url('finance/inventory_wip/create') ?>',
                                                    data: {
                                                        data: json[i]
                                                    },
                                                    dataType: "json",
                                                    success: function(result) {
                                                        requestData(total, json, jml + 1, value);

                                                        if (result.theme == "success") {
                                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                        } else {
                                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                        }

                                                        $("#p_remarks").append(title + "<br>");

                                                        if (i == (data.total - 1)) {
                                                            $('#dlg_generate').dialog('close');

                                                            Swal.fire({
                                                                title: result.message,
                                                                icon: result.theme,
                                                                confirmButtonText: 'Ok',
                                                                allowOutsideClick: false,
                                                            }).then((result) => {
                                                                // if (result.isConfirmed) {
                                                                //     window.location.reload();
                                                                // }
                                                            });
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    }else{
                                        toastr.warning("Data not Found!");
                                    }
                                }
                            });
                        }
                    });
                }else{
                    toastr.warning("Please select Trans Date!");
                }
            }
        });
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
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_item_fg=" + filter_item_fg + "&filter_display=" + filter_display;
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/inventory_wip/print') ?>' + url);
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_item_fg=" + filter_item_fg + "&filter_display=" + filter_display;
        window.location.assign('<?= base_url('finance/inventory_wip/print/excel') ?>' + url);
    }

    $(function() {
        $("#add").html("Save Inventory WIP");

        $('#filter_item_fg').combogrid({
            url: '<?= base_url('master/item_fg/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product No",
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
                    width: 100
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }, ]
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
</script>
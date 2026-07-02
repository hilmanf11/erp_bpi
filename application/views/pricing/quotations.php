<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'print',width:80,align:'center', formatter:btnPrint">Print</th>
            <th rowspan="2" data-options="field:'quotation_number',width:200,align:'left'">Quotation Number</th>
            <th rowspan="2" data-options="field:'quotation_date',width:100,halign:'center'">Quotation Date</th>
            <th rowspan="2" data-options="field:'quotation_to',width:200,halign:'center'">Quotation To</th>
            <th rowspan="2" data-options="field:'quotation_attn',width:200,halign:'center'">Quotation Attn</th>
            <th rowspan="2" data-options="field:'quotation_cc',width:200,halign:'center'">Quotation CC</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:120,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:120,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 45%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Quotation Number</span>
                <input style="width:60%;" id="filter_quotation_number" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1200px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Quotation date</span>
                    <input style="width:60%;" name="quotation_date" id="quotation_date" required ="" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Quotation Number</span>
                    <input style="width:60%;" name="quotation_number" id="quotation_number" class="easyui-textbox" readonly>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Customer id</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Quotation To</span>
                    <input style="width:60%;" name="quotation_to" id="quotation_to" required ="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Quotation Attn</span>
                    <input style="width:60%;" name="quotation_attn" id="quotation_attn" required ="" class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float:left;">
                
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Quotation CC</span>
                    <input style="width:60%;" name="quotation_cc" id="quotation_cc" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Notes</span>
                    <input style="width:60%;" name="notes" id="notes" class="easyui-textbox" multiline="true" data-options="height:110">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Quotations List" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- Upload -->
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
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('pricing/quotations/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('pricing/quotations/create') ?>';
        $('#frm_insert').form('clear');
    }

    function addTable(link = "") {
        $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'item_fg_number',
                    width: 150,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('pricing/quotations/readItems'); ?>',
                            required: true,
                            panelWidth: 450,
                            idField: 'item_fg_number',
                            textField: 'item_fg_number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product No',
                            columns: [
                                [{
                                    field: 'item_fg_number',
                                    title: 'Product No',
                                    width: 150
                                }, {
                                    field: 'quotation_number',
                                    title: 'Quotation Number',
                                    width: 250
                                }, {
                                    field: 'revision_quotation_number',
                                    title: 'Rev',
                                    width: 80
                                }, ]
                            ],
                            onSelect: function (value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'id'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_id'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_name'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'quotation_number2'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'revision_quotation_number'
                                });
                                var ed6 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'moq'
                                });
                                var ed7 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'mpq'
                                });
                                var ed8 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'price'
                                });
                                

                                $(ed.target).textbox('setValue', rows.id);
                                $(ed2.target).textbox('setValue', rows.item_fg_id);
                                $(ed3.target).textbox('setValue', rows.item_fg_name);
                                $(ed4.target).textbox('setValue', rows.quotation_number);
                                $(ed5.target).textbox('setValue', rows.revision_quotation_number);
                                $(ed6.target).textbox('setValue', rows.moq);
                                $(ed7.target).textbox('setValue', rows.mpq);
                                $(ed8.target).numberbox('setValue', rows.price);
                            }
                        }
                    }
                }, {
                    field: 'id',
                    width: 150,
                    hidden: true,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'item_fg_id',
                    width: 150,
                    halign: 'center',
                    title: "Product ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'item_fg_name',
                    width: 150,
                    halign: 'center',
                    title: "Product Name",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'quotation_number2',
                    width: 170,
                    halign: 'center',
                    title: "Breakdown Number",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'revision_quotation_number',
                    width: 80,
                    halign: 'center',
                    title: "Revision",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'price',
                    width: 120,
                    halign: 'center',
                    title: "Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'moq',
                    width: 80,
                    halign: 'center',
                    title: "MOQ",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'mpq',
                    width: 80,
                    halign: 'center',
                    title: "MPQ",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                 }, {
                    field: 'remark',
                    width: 100,
                    halign: 'center',
                    title: "Remark",
                    editor: {
                        type: 'textbox',
                    }
                }]
            ],
            onClickCell: onClickCell
        });
    }

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg2').datagrid('validateRow', editIndex)) {
            $('#dg2').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function onClickCell(index, field) {
        if (editIndex != index) {
            if (endEditing()) {
                $('#dg2').datagrid('selectRow', index).datagrid('beginEdit', index);
                editIndex = index;
            } else {
                setTimeout(function() {
                    $('#dg2').datagrid('selectRow', editIndex);
                }, 0);
            }
        }
    }

    function append() {
        var quotation_number = $("#quotation_number").textbox('getValue');
        if (quotation_number != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Quotation Date first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return true;
        }

        var dg = $('#dg2');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: editIndex,
            field: 'item_fg_id'
        });

        var quotation_number = $("#quotation_number").combogrid('getValue');
        var item_fg_id = $(ed.target).textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('pricing/quotations/delete') ?>',
            data: {
                quotation_number: quotation_number,
                item_fg_id: item_fg_id
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                toastr.success(result.message);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                toastr.error(jqXHR.statusText);
                $.messager.alert("Error", jqXHR.statusText, 'error');
            },
            complete: function(data) {
                $('#dg').datagrid('reload');
            }
        });

        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('pricing/quotations/create') ?>';
            $("#quotation_date").datebox('disable');
            $("#quotation_number").textbox('disable');

            addTable('<?= base_url('pricing/quotations/datatableUpdates?quotation_number=') ?>' + window.btoa(row.quotation_number));
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('pricing/quotations/delete') ?>',
                            data: {
                                quotation_number: row.quotation_number
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
                            },
                            complete: function(data) {
                                $('#dg').datagrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }
    // UPLOAD DATA
    // function upload() {
    //     $('#dlg_upload').dialog('open');
    // }
    // // DOWNLOAD
    // function download_excel() {
    //     window.location.assign('<?= base_url('template/tmp_bom.xls') ?>');
    // }

    //FILTER DATA
    function filter() {
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_quotation_number = $("#filter_quotation_number").combobox('getValue');

        var url = "?filter_item_fg_id=" + window.btoa(filter_item_fg_id) +
            "&filter_quotation_number=" + window.btoa(filter_quotation_number);

        $('#dg').datagrid({
            url: '<?= base_url('pricing/quotations/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('pricing/quotations/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_quotation_number = $("#filter_quotation_number").combobox('getValue');

        var url = "?filter_item_fg_id=" + window.btoa(filter_item_fg_id) +
            "&filter_quotation_number=" + window.btoa(filter_quotation_number);

        window.location.assign('<?= base_url('pricing/quotations/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //ADD DATA
        addTable();

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('pricing/quotations/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.quotation_number + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_item_fg_id = $("#filter_quotation_number").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('pricing/quotations/datatableDetails?number=') ?>' + window.btoa(row.quotation_number) + "&filter_item_fg_id=" + window.btoa(filter_item_fg_id),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'item_fg_id',
                            title: 'Product ID',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_fg_number',
                            title: 'Product Number',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'quotation_number2',
                            title: 'Breakdown Number',
                            halign: 'center',
                            align: 'center',
                            width: 120
                        }, {
                            field: 'price',
                            title: 'Price',
                            halign: 'center',
                            align: 'center',
                            width: 100
                        }, {
                            field: 'moq',
                            title: 'Moq',
                            halign: 'center',
                            align: 'center',
                            width: 100
                        }, {
                            field: 'mpq',
                            title: 'Mpq',
                            halign: 'center',
                            align: 'center',
                            width: 100
                        }, {
                            field: 'remark',
                            title: 'Remarks',
                            width: 200,
                            halign: 'center',
                        }, {
                            field: 'created_by',
                            title: 'Created By',
                            width: 100,
                            halign: 'center',
                        }, {
                            field: 'created_date',
                            title: 'Created Date',
                            width: 120,
                            halign: 'center',
                         }, {
                            field: 'updated_by',
                            title: 'Update By',
                            width: 100,
                            halign: 'center',
                        }, {
                            field: 'updated_date',
                            title: 'Update Date',
                            width: 120,
                            halign: 'center',
                        }]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        //SAVE DATA
        // $('#dlg_insert').dialog({
        //     buttons: [{
        //         text: 'Save All',
        //         iconCls: 'icon-ok',
        //         handler: function() {
        //             var quotation_date = $("#quotation_date").datebox('getValue');
        //             var quotation_number = $("#quotation_number").textbox('getValue');
        //             var customer_id = $("#customer_id").textbox('getValue');
        //             var quotation_to = $("#quotation_to").combogrid('getValue');
        //             var quotation_attn = $("#quotation_attn").textbox('getValue');
        //             var quotation_cc = $("#quotation_cc").textbox('getValue');

        //             var rows = $('#dg2').datagrid('getRows');
        //             var totalrows = rows.length;
        //             endEditing();

        //             for (let i = 0; i < totalrows; i++) {
        //                 if (rows[i].item_fg_id) {
                            
        //                     var dataFinal = {
        //                         quotation_date: quotation_date,
        //                         quotation_number: quotation_number,
        //                         customer_id: customer_id,
        //                         quotation_to: quotation_to,
        //                         quotation_attn: quotation_attn,
        //                         quotation_cc: quotation_cc,
        //                         id: rows[i].id,
        //                         item_fg_id: rows[i].item_fg_id,
        //                         item_fg_number: rows[i].item_fg_number,
        //                         item_fg_name: rows[i].item_fg_name,
        //                         quotation_number2: rows[i].quotation_number2,
        //                         revision_quotation_number: rows[i].revision_quotation_number,
        //                         moq: rows[i].moq,
        //                         mpq: rows[i].mpq,
        //                         price: rows[i].price,
        //                         remark: rows[i].remark
        //                     };
                          
        //                     $.ajax({
        //                         type: "post",
        //                         url: url_save,
        //                         data: dataFinal,
        //                         dataType: "json",
        //                         success: function(result) {
        //                             if (i == (totalrows - 1)) {
        //                                 Swal.fire({
        //                                     title: result.message,
        //                                     icon: result.theme,
        //                                     confirmButtonText: 'Ok',
        //                                     allowOutsideClick: false,
        //                                 }).then((result) => {
        //                                     if (result.isConfirmed) {
        //                                         window.location.reload();
        //                                     }
        //                                 });
        //                             }
        //                         }
        //                     });
        //                 }
        //             }

        //             $('#dg').datagrid('reload');
        //             $('#dlg_insert').dialog('close');
        //         }
        //     }]
        // });

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var quotation_date = $("#quotation_date").datebox('getValue');
                    var quotation_number = $("#quotation_number").textbox('getValue');
                    var customer_id = $("#customer_id").textbox('getValue');
                    var quotation_to = $("#quotation_to").combogrid('getValue');
                    var quotation_attn = $("#quotation_attn").textbox('getValue');
                    var quotation_cc = $("#quotation_cc").textbox('getValue');

                    endEditing();
                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    
                    var requestsCompleted = 0;
                    var totalRequests = 0;

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_fg_id) {
                            totalRequests++;
                        }
                    }

                    if (totalRequests === 0) {
                        Swal.fire('Info', 'Tidak ada data valid untuk disimpan.', 'info');
                        return;
                    }

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_fg_id) {
                            
                            var dataFinal = {
                                quotation_date: quotation_date,
                                quotation_number: quotation_number,
                                customer_id: customer_id,
                                quotation_to: quotation_to,
                                quotation_attn: quotation_attn,
                                quotation_cc: quotation_cc,
                                id: rows[i].id,
                                item_fg_id: rows[i].item_fg_id,
                                item_fg_number: rows[i].item_fg_number,
                                item_fg_name: rows[i].item_fg_name,
                                quotation_number2: rows[i].quotation_number2,
                                revision_quotation_number: rows[i].revision_quotation_number,
                                moq: rows[i].moq,
                                mpq: rows[i].mpq,
                                price: rows[i].price,
                                remark: rows[i].remark
                            };
                        
                            $.ajax({
                                type: "post",
                                url: url_save,
                                data: dataFinal,
                                dataType: "json",
                                success: function(result) {
                                    requestsCompleted++; // Tambah counter jika sukses
                                    
                                    if (requestsCompleted === totalRequests) {
                                        localStorage.setItem('task_saved', 'yes');//untuk keperluan npd
                                        $('#dg').datagrid('reload');
                                        $('#dlg_insert').dialog('close');
                                        
                                        Swal.fire({
                                            title: 'Success!',
                                            text: 'Semua data berhasil disimpan.',
                                            icon: 'success', 
                                            confirmButtonText: 'Ok',
                                            allowOutsideClick: false,
                                        }).then((swalResult) => {
                                            if (swalResult.isConfirmed) {
                                                // window.location.reload();
                                            }
                                        });
                                        
                                    }
                                },
                                error: function() {
                                    requestsCompleted++;
                                }
                            });
                        }
                    }
                }
            }]
        });
    });

    $('#item_fg_id').combogrid({
        url: '<?= base_url('pricing/quotations/readItems/'); ?>',
        panelWidth: 450,
        idField: 'item_fg_id',
        textField: 'item_fg_number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No",
        columns: [
            [{
                field: 'item_fg_number',
                title: 'Product No',
                width: 120
            }, {
                field: 'quotation_number',
                title: 'Quotation Number',
                width: 250
            }, {
                field: 'revision_quotation_number',
                title: 'Quotation Revision',
                width: 80
            }, ]
        ]
    });

    $('#filter_item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/reads'); ?>',
        panelWidth: 750,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No",
        columns: [
            [{
                field: 'id',
                title: 'Product ID',
                width: 150
            }, {
                field: 'number',
                title: 'Product No',
                width: 150
            }, {
                field: 'number_customer',
                title: 'Product Customer',
                width: 150
            }, {
                field: 'name',
                title: 'Product Name',
                width: 250
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $('#filter_quotation_number').combobox({
        url: '<?= base_url('pricing/quotations/readQuotation'); ?>',
        valueField: 'quotation_number',
        textField: 'quotation_number',
        prompt: 'Choose Quotation Number',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#quotation_to').combogrid({
        url: '<?= base_url('master/customers/reads/'); ?>',
        panelWidth: 370,
        idField: 'name',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Data",
        columns: [
            [{
                field: 'number',
                title: 'Customer Code',
                width: 120
            }, {
                field: 'name',
                title: 'Customer Name',
                width: 250
            }, ]
        ],
        onSelect: function(value, rows) {
            $('#customer_id').textbox('setValue', rows.id);
        }
    });

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('pricing/quotations/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('pricing/quotations/upload') ?>',
                    onSubmit: function() {
                        if ($(this).form('validate') == false) {
                            return $(this).form('validate');
                        } else {
                            $.messager.progress({
                                title: 'Please Wait',
                                msg: 'Importing Excel to Database'
                            });
                        }
                    },
                    success: function(result) {
                        $.messager.progress('close');
                        //Clear File
                        $.ajax({
                            url: "<?= base_url('pricing/quotations/uploadclearFailed') ?>"
                        });
                        var json = eval('(' + result + ')');
                        requestData(json.total, json);

                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                            if (value < 100) {
                                value = Math.floor((number / total) * 100);
                                $('#p_upload').progressbar('setValue', value);
                                $('#p_start').html(number);
                                $('#p_finish').html(total);

                                $.ajax({
                                    type: "POST",
                                    async: true,
                                    url: "<?= base_url('pricing/quotations/uploadCreate') ?>",
                                    data: {
                                        "data": json[number - 1]
                                    },
                                    cache: false,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
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
                                                url: "<?= base_url('pricing/quotations/uploadcreateFailed') ?>",
                                                data: {
                                                    data: json[number - 1],
                                                    message: result.message
                                                },
                                                cache: false
                                            });
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }
                                        $("#p_remarks").append(title + "<br>");
                                    }
                                });
                            }
                        }
                    }
                });
            }
        }]
    });

    $('#quotation_date').datebox({
        onSelect: function(date) {
            var y = date.getFullYear();
            var m = date.getMonth() + 1;
            var d = date.getDate();
            var dateStr = y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);

            $.post('<?= base_url('pricing/quotations/get_quotation_number'); ?>', {date: dateStr}, function(res) {
                $('#quotation_number').textbox('setValue', res.number);
            }, 'json');
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

    function btnPrint(val, row) {
        var print = "print_quotation('" + row.quotation_number + "')"; 
        return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
    }

    function print_quotation(quotation_number) {
        if (!quotation_number) {
            alert("Data not Found!");
            return;
        }
        var url = "<?= base_url('pricing/quotations/print_quotation/') ?>" + window.btoa(quotation_number);
        window.open(url, "_blank");
    }

    //untuk kebutuhan NPD
    $(document).ready(function() {
        if (localStorage.getItem('trigger_add') === 'yes') {
            localStorage.removeItem('trigger_add');

            $('<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:#ffffff;z-index:8999;"></div>').appendTo('body');

            setTimeout(function() {
                if (typeof add === "function") {
                    add(); 

                    var checkClose = setInterval(function() {
                        var isHidden = $('#dlg_insert').closest('.window').is(':hidden');
                        if (isHidden) {
                            clearInterval(checkClose); // Hentikan monitoring
                            if (window.parent.$('#dlg_outer_wrapper').length) {
                                window.parent.$('#dlg_outer_wrapper').dialog('close');
                            }
                        }
                    }, 500);
                }
            }, 1000); 
        }

        // ==========================================
        // SKRIP TRIGGER UPLOAD
        // ==========================================
        var urlParams = new URLSearchParams(window.location.search);
        var action = urlParams.get('action');

        if (action === 'upload') {
            $('<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:#ffffff;z-index:8999;"></div>').appendTo('body');

            setTimeout(function() {
                if (typeof upload === 'function') {
                    upload(); 
                    var checkCloseUpload = setInterval(function() {
                        var isHidden = $('#dlg_upload').closest('.window').is(':hidden'); 
                        if (isHidden) {
                            clearInterval(checkCloseUpload); 
                            if (window.parent.$('#dlg_upload_wrapper').length) {
                                window.parent.$('#dlg_upload_wrapper').dialog('close');
                            }
                        }
                    }, 500);
                }
            }, 500);
        }
    });

</script>
<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%; height: 630px;" toolbar="#toolbar" showFooter="true">
    <thead>
        <tr>
            <th rowspan="3" field="ck" checkbox="true"></th>
            <th rowspan="3" data-options="field:'history',width:100,halign:'center',formatter:btnWorkorder">Detail</th>
            <th rowspan="3" data-options="field:'item_no',width:150,halign:'center'">Product No</th>
            <th rowspan="3" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="3" data-options="field:'total_qty',width:100,halign:'center', align:'right',formatter:priceformat">Quantity</th>
            <th rowspan="3" data-options="field:'umh',width:100,halign:'center', align:'right',formatter:priceformat">UMH</th>
            <th colspan="10" data-options="field:'',width:100,halign:'center'">Total Manufacture Cost</th>
            <th rowspan="3" data-options="field:'direct_total',width:100,halign:'center', align:'right',formatter:priceformat">Cost Product</th>
            <th rowspan="2" colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th rowspan="2" colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th colspan="4" data-options="field:'',width:100,halign:'center'"> Direct Material</th>
            <th colspan="4" data-options="field:'',width:100,halign:'center'"> Direct Labor</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> FOH</th>
        </tr>
        <tr>
            <th data-options="field:'direct_material',width:100,halign:'center',align:'right',formatter:priceformat">Supply</th>
            <th data-options="field:'direct_requestion',width:100,halign:'center',align:'right',formatter:priceformat">Material<br>Requestion</th>
            <th data-options="field:'direct_material_total',width:100,halign:'center',align:'right',formatter:priceformat">Total</th>
            <th data-options="field:'direct_material_pcs',width:100,halign:'center',align:'right',formatter:priceformat">Pcs</th>
            <th data-options="field:'direct_labor',width:100,halign:'center',align:'right',formatter:priceformat">Regular</th>
            <th data-options="field:'direct_overtime',width:100,halign:'center',align:'right',formatter:priceformat">Overtime</th>
            <th data-options="field:'direct_labor_total',width:100,halign:'center',align:'right',formatter:priceformat">Total</th>
            <th data-options="field:'direct_labor_pcs',width:100,halign:'center',align:'right',formatter:priceformat">Pcs</th>
            <th data-options="field:'direct_foh',width:100,halign:'center',align:'right',formatter:priceformat">FOH</th>
            <th data-options="field:'direct_foh_pcs',width:100,halign:'center',align:'right',formatter:priceformat">Pcs</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- FORM FILTER DATAGRID -->
<div id="toolbar" style="height: 225px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Periode</span>
                <input style="width:30%;" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                <input style="width:30%;" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Year'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <fieldset style="width: 28%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Process Generate Data</b></legend>
            <!-- <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a> -->
            <b>Calculate Workorder</b>
            <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <b>Calculate Product No</b>
            <div id="p_upload2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
            <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
            <!-- <div id="p_remarks" class="easyui-panel" style="width:100%; height:100px; padding:10px; margin-top: 10px; overflow: auto;">
                <ul id="remarks">

                </ul>
            </div> -->
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Modul Check (Posting Journal)</b></legend>
            <div style="width:50%; float: left;">
                <div style="margin:12px;">
                    <input class="easyui-checkbox" id="check_pi" value="on" readonly="true"> &nbsp; Purchase Invoicing
                </div>
                <div style="margin:12px;">
                    <input class="easyui-checkbox" id="check_si" value="on" readonly="true"> &nbsp; Sales Invoicing
                </div>
                <div style="margin:12px;">
                    <input class="easyui-checkbox" id="check_ap" value="on" readonly="true"> &nbsp; AP Payment
                </div>
            </div>
            <div style="width:50%; float: left;">
                <div style="margin:12px;">
                    <input class="easyui-checkbox" id="check_ar" value="on" readonly="true"> &nbsp; AR Receipt
                </div>
                <div style="margin:12px;">
                    <input class="easyui-checkbox" id="check_as" value="on" readonly="true"> &nbsp; Assets
                </div>
                <div style="margin:12px;">
                    <input class="easyui-checkbox" id="check_cr" value="on" readonly="true"> &nbsp; Currency Revaluation
                </div>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<!-- Detail Workorder -->
<div id="dlg_workorder" class="easyui-window" title="Detail Workorder" data-options="closed: true,modal:true" style="width: 800px; height: 400px; top: 20px;">
    <table id="dg_workorder" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'details',width:80,halign:'center',formatter:btnDetails">Detail</th>
                <th data-options="field:'periode',width:80,halign:'center'">Period</th>
                <th data-options="field:'wp',width:40,halign:'center'">WP</th>
                <th data-options="field:'workorder',width:150,halign:'center'">Workorder</th>
                <th data-options="field:'item_number',width:150,halign:'center'">Product No</th>
                <th data-options="field:'item_name',width:150,halign:'center'">Product Name</th>
                <th data-options="field:'qty',width:100,halign:'center',align:'right',formatter:priceformat">WO Qty</th>
                <th data-options="field:'umh',width:60,halign:'center',align:'right',formatter:priceformat">UMH</th>
                <th data-options="field:'total_umh',width:100,halign:'center',align:'right',formatter:priceformat">Total UMH</th>
                <th data-options="field:'direct_labor',width:100,halign:'center',align:'right',formatter:priceformat">Direct Labor</th>
                <th data-options="field:'direct_overtime',width:100,halign:'center',align:'right',formatter:priceformat">Direct Overtime</th>
                <th data-options="field:'direct_foh',width:100,halign:'center',align:'right',formatter:priceformat">Direct FOH</th>
                <th data-options="field:'direct_material',width:100,halign:'center',align:'right',formatter:priceformat">Direct Material</th>
                <th data-options="field:'direct_requestion',width:100,halign:'center',align:'right',formatter:priceformat">Direct Requestion</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Detail Details -->
<div id="dlg_details" class="easyui-window" title="Detail Part No" data-options="closed: true,modal:true" style="width: 800px; height: 400px; top: 20px;">
    <table id="dg_details" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'item_number',width:150,halign:'center'">Part No</th>
                <th data-options="field:'item_name',width:150,halign:'center'">Part Name</th>
                <th data-options="field:'uom',width:80,align:'center'">Uom</th>
                <th data-options="field:'supply_type',width:150,halign:'center'">Supply Type</th>
                <th data-options="field:'supply_date',width:100,halign:'center'">Supply Date</th>
                <th data-options="field:'supply_no',width:150,halign:'center'">Supply No</th>
                <th data-options="field:'issued',width:100,halign:'center',align:'right',formatter:priceformat">Issued</th>
                <th data-options="field:'price',width:100,halign:'center',align:'right',formatter:priceformat">Price</th>
                <th data-options="field:'amount',width:100,halign:'center',align:'right',formatter:priceformat">Amount</th>
            </tr>
        </thead>
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('finance/costing_products/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    function componentCheck() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');

        $.ajax({
            type: "post",
            url: "<?= base_url('finance/costing_products/checkPurchaseInvoice') ?>",
            data: "filter_month=" + filter_month +
                "&filter_year=" + filter_year,
            dataType: "json",
            success: function(pi) {
                if (pi > 0) {
                    $('#check_pi').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_pi').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url('finance/costing_products/checkSalesInvoice') ?>",
            data: "filter_month=" + filter_month +
                "&filter_year=" + filter_year,
            dataType: "json",
            success: function(pi) {
                if (pi > 0) {
                    $('#check_si').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_si').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url('finance/costing_products/checkApPayment') ?>",
            data: "filter_month=" + filter_month +
                "&filter_year=" + filter_year,
            dataType: "json",
            success: function(pi) {
                if (pi > 0) {
                    $('#check_ap').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_ap').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url('finance/costing_products/checkArReceipt') ?>",
            data: "filter_month=" + filter_month +
                "&filter_year=" + filter_year,
            dataType: "json",
            success: function(pi) {
                if (pi > 0) {
                    $('#check_ar').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_ar').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url('finance/costing_products/checkAsset') ?>",
            data: "filter_month=" + filter_month +
                "&filter_year=" + filter_year,
            dataType: "json",
            success: function(pi) {
                if (pi > 0) {
                    $('#check_as').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_as').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "post",
            url: "<?= base_url('finance/costing_products/checkCurrency') ?>",
            data: "filter_month=" + filter_month +
                "&filter_year=" + filter_year,
            dataType: "json",
            success: function(pi) {
                if (pi > 0) {
                    $('#check_cr').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_cr').checkbox({
                        checked: false
                    });
                }
            }
        });
    };

    //ADD DATA
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_item = $("#filter_item").combogrid('getValue');

        var check_pi = $("#check_pi").checkbox('options');
        var check_si = $("#check_si").checkbox('options');
        var check_ap = $("#check_ap").checkbox('options');
        var check_ar = $("#check_ar").checkbox('options');
        var check_as = $("#check_as").checkbox('options');
        var check_cr = $("#check_cr").checkbox('options');

        $.ajax({
            type: "post",
            url: "<?= base_url('closing/locks/checkLock') ?>",
            data: "period=" + filter_year + "-" + filter_month + "-01" + "&menus_id=<?= $menus_id ?>",
            dataType: "json",
            success: function (lock) {
                if(lock.total > 0){
                    toastr.error("This period is not active by Accounting");
                    return false;
                }

                if (check_pi.checked == true && check_si.checked == true && check_ap.checked == true && check_ar.checked == true && check_as.checked == true == check_cr.checked == true) {
                    $.messager.confirm('Warning', 'Are you sure you want to Generate this data?', function(r) {
                        if (r) {
                            Swal.fire({
                                title: 'Please Wait for Generate Data',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            $.ajax({
                                type: "post",
                                url: "<?= base_url('finance/costing_products/getData') ?>",
                                data: "month=" + filter_month + "&year=" + filter_year + "&item_fg_id=" + filter_item,
                                dataType: "json",
                                success: function(get) {
                                    Swal.close();
                                    
                                    if (get.total > 0) {
                                        requestData(get.total, get.rows);

                                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                            if (value < 100) {
                                                value = Math.floor((number / total) * 100);
                                                $('#p_upload').progressbar('setValue', value);
                                                $('#p_start').html(number);
                                                $('#p_finish').html(total);

                                                var i = (number - 1);

                                                $.ajax({
                                                    type: "post",
                                                    url: '<?= base_url('finance/costing_products/create') ?>',
                                                    data: json[i],
                                                    dataType: "json",
                                                    success: function(result) {
                                                        if (result.theme == "success") {
                                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                                        } else {
                                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                                        }

                                                        if (value == 100) {
                                                            // Swal.fire('Good job!', 'Process Add Journal Entries Completed!', 'success');
                                                            // $("#dg").datagrid('reload');

                                                            calculateProducts();
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    } else {
                                        Swal.fire('Not Found!', 'Data Not Found, Please Generate First in Inventory RM', 'error');
                                    }
                                }
                            });
                        }
                    });
                } else {
                    toastr.info("Modul Check Not Complete ");
                }
            }
        });
    }

    function calculateProducts(){
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_item = $("#filter_item").combogrid('getValue');

        Swal.fire({
            title: 'Please Wait for Finishing Data',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            type: "post",
            url: "<?= base_url('finance/costing_products/getDataProducts') ?>",
            data: "month=" + filter_month + "&year=" + filter_year + "&item_fg_id=" + filter_item,
            dataType: "json",
            success: function(get) {
                Swal.close();
                
                if (get.total > 0) {
                    requestData2(get.total, get.rows);

                    function requestData2(total, json, number = 1, value = 0, success = 1, failed = 1) {
                        if (value < 100) {
                            value2 = Math.floor((number / total) * 100);
                            $('#p_upload2').progressbar('setValue', value2);
                            $('#p_start2').html(number);
                            $('#p_finish2').html(total);

                            var i = (number - 1);

                            $.ajax({
                                type: "post",
                                url: '<?= base_url('finance/costing_products/createProducts') ?>',
                                data: json[i],
                                dataType: "json",
                                success: function(result) {
                                    if (result.theme == "success") {
                                        requestData2(total, json, number + 1, value2, success + 1, failed + 0);
                                    } else {
                                        requestData2(total, json, number + 1, value2, success + 0, failed + 1);
                                    }

                                    if (value2 == 100) {
                                        Swal.fire('Good job!', 'Generate Costing Products Completed!', 'success');
                                        $("#dg").datagrid('reload');
                                    }
                                }
                            });
                        }
                    }
                } else {
                    Swal.fire('Not Found!', 'Data Not Found, Please Generate First in Inventory RM', 'error');
                }
            }
        });
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    Swal.fire({
                        title: 'Please Wait for Deleting Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        $.ajax({
                            type: "post",
                            url: "<?= base_url('closing/locks/checkLock') ?>",
                            data: "period=" + row.periode + "&menus_id=<?= $menus_id ?>",
                            dataType: "json",
                            success: function (lock) {
                                if(lock.total > 0){
                                    toastr.error("This period is not active by Accounting");
                                    return false;
                                }

                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('finance/costing_products/delete') ?>',
                                    data: {
                                        id: row.id
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                        Swal.close();
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
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //FILTER DATA
    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_item = $("#filter_item").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_item=" + window.btoa(filter_item);

        $('#dg').datagrid({
            url: '<?= base_url('finance/costing_products/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/costing_products/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //EXPORT TO EXCEL
    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_item = $("#filter_item").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_item=" + window.btoa(filter_item);

        window.location.assign('<?= base_url('finance/costing_products/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function downloadFailed() {
        window.open('<?= base_url('finance/costing_products/uploadDownloadFailed') ?>', '_blank');
    }

    function btnWorkorder(val, row) {
        if(val != "Grand Total"){
            var workorder = "viewWorkorder('" + row.periode + "','" + row.item_fg_id + "')";
            return '<a class="btn btn-primary w-100" onClick="' + workorder + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
        }else{
            return val;
        }
    }

    function btnDetails(val, row) {
        var details = "viewDetails('" + row.periode + "','" + row.workorder + "')";
        return '<a class="btn btn-primary w-100" onClick="' + details + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewWorkorder(periode, item_fg_id) {
        $("#dlg_workorder").window('open');
        $('#dg_workorder').datagrid({
            url: '<?= base_url('finance/costing_products/datatableWorkorders?periode=') ?>' + btoa(periode) + "&item_fg_id=" + btoa(item_fg_id),
            pagination: false,
            rownumbers: true,
            fit:true,
            showFooter: true
        });
    }

    function viewDetails(periode, workorder) {
        $("#dlg_details").dialog('open');
        $('#dg_details').datagrid({
            url: '<?= base_url('finance/costing_products/datatableDetails?periode=') ?>' + btoa(periode) + "&workorder=" + btoa(workorder),
            pagination: false,
            rownumbers: true,
            fit:true,
            showFooter: true
        });
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

    $(function() {
        componentCheck();
        $("#add").html("Generate Data Cost");

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            rownumbers: true,
            fit:true
        });

        $('#filter_month').combobox({
            url: '<?php echo base_url('finance/costing_products/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Month',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onChange: function(row){
                componentCheck();
            }
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('finance/costing_products/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Year',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onChange: function(row){
                componentCheck();
            }
        });

        $('#filter_item').combogrid({
            url: '<?= base_url('master/item_fg/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product",
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
                }, ]
            ]
        });
    });

    function priceformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat("id-ID", {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }
</script>
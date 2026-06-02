<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'print',width:80,align:'center', formatter:btnPrint">Print</th>
            <th rowspan="2" data-options="field:'approved_to',width:100,halign:'center',formatter:formatApproved,styler:styleApproved">Status <br>Approve</th>
            <th rowspan="2" data-options="field:'approved_by',width:100,halign:'center'">Approve By</th>
            <th rowspan="2" data-options="field:'approved_date',width:150,halign:'center'">Approve Date</th>
            <th rowspan="2" data-options="field:'item_fg_number',halign:'center',width:150" sortable="true">Product Number</th>
            <th rowspan="2" data-options="field:'item_fg_name',halign:'center',width:150" sortable="true">Product Name</th>
            <th rowspan="2" data-options="field:'revision',width:80,halign:'center'" sortable="true">Rev Cost <br>Pattern</th>
            <th rowspan="2" data-options="field:'p_month',width:80,halign:'center'" sortable="true">Month Cost <br>Pattern</th>
            <th rowspan="2" data-options="field:'p_year',width:80,halign:'center'" sortable="true">Year Cost <br>Pattern</th>
            <th rowspan="2" data-options="field:'order_estimation',width:100,halign:'center'" sortable="true">Order <br>Estimation</th>
            <th rowspan="2" data-options="field:'model_life_time',width:100,halign:'center'" sortable="true">Model Life <br>Time</th>
            <th rowspan="2" data-options="field:'start_mass_pro',width:100,halign:'center'" sortable="true">Start Mass <br>Pro</th>
            <th rowspan="2" data-options="field:'l_t_dies_actual',width:100,halign:'center'" sortable="true">L/T Dies <br>Actual</th>
            <th rowspan="2" data-options="field:'supplier',width:200,halign:'center'" sortable="true">VENDOR/SUPPLIER/MAKER</th>
            <th rowspan="2" data-options="field:'quotation_date',width:100,halign:'center'" sortable="true">Quotation <br>Date</th>
            <th rowspan="2" data-options="field:'quotation_number',width:150,halign:'center'" sortable="true">Breakdown <br>Number</th>
            <th rowspan="2" data-options="field:'revision_quotation_number',width:100,halign:'center'" sortable="true">Rev Brakdown</th>
            <th rowspan="2" data-options="field:'price_cond',width:100,halign:'center'" sortable="true">Price Cond</th>
            <th colspan="2" data-options="field:'',width:150,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:150,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'" sortable="true"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'" sortable="true"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'" sortable="true"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'" sortable="true"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 240px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" id="filter_period_month" value="<?= date("m") ?>" class="easyui-combobox">
                <input style="width:30%;" id="filter_period_year" value="<?= date("Y") ?>" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <select style="width:60%;" id="filter_revision" class="easyui-combobox" panelHeight="auto">
                    <option value="" selected disabled>Choose All</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item_fg_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
        <!-- <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="print_kanban()"><i class="fa fa-print"></i> Print Supply Sheet</a>
        <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="print_label_supply()"><i class="fa fa-print"></i> Print Label Supply</a>
        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="close_sh()"><i class="fa fa-times"></i> Complete/Open</a> -->

    </div>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 80%; height: 500px; padding:10px; top: 20px; left: 10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Period</span>
                    <input style="width:30%;" name="p_month" id="p_month" required="" class="easyui-combobox">
                    <input style="width:30%;" name="p_year" id="p_year" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Cost Pattern Revision</span>
                    <select style="width:60%;" name="revision" id="revision" required="" class="easyui-combobox" panelHeight="auto">
                        <option value="" selected disabled>Choose Revision</option>
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <!-- <div class="fitem">
                    <span style="width:30%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="division_id" class="easyui-combobox">
                </div> -->
                <div class="fitem" hidden>
                    <span style="width:30%; display:inline-block;">Part Id</span>
                    <input style="width:60%;" name="item_fg_id" id="item_fg_id" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Part No</span>
                    <input style="width:60%;" name="item_fg_number" id="item_fg_number" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Part Name</span>
                    <input style="width:60%;" name="item_fg_name" id="item_fg_name" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Model Name</span>
                    <input style="width:60%;" name="model_name" id="model_name" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Order Estimation</span>
                    <input style="width:60%;" name="order_estimation" id="order_estimation" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Model Life Time</span>
                    <input style="width:60%;" name="model_life_time" id="model_life_time" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Start Mass Pro</span>
                    <input style="width:60%;" name="start_mass_pro" id="start_mass_pro" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">L/T Dies Actual</span>
                    <input style="width:60%;" name="l_t_dies_actual" id="l_t_dies_actual" class="easyui-numberbox">
                </div>
                <!-- <div class="fitem">
                    <span style="width:30%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div> -->
            </div>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">VENDOR/SUPPLIER/MAKER</span>
                    <input style="width:60%;" name="supplier" id="supplier" value="PT. BANSHU PLASTIC INDONESIA" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Quotation date</span>
                    <input style="width:60%;" name="quotation_date" id="quotation_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Quotation Number</span>
                    <input style="width:60%;" name="quotation_number" id="quotation_number" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Price Cond</span>
                    <select style="width:60%;" name="price_cond" id="price_cond" class="easyui-combobox" panelHeight="auto">
                        <option value="" selected disabled>Choose Price Cond</option>
                        <option value="FOB">FOB</option>
                        <option value="CNF">CNF</option>
                        <option value="CIF">CIF</option>
                        <option value="FRANCO">FRANCO</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Price Unit in</span>
                    <input style="width:60%;" name="currency" id="currency" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Mold</span>
                    <input style="width:30%;" name="mold_unit" id="mold_unit" class="easyui-numberbox" data-options="prompt:'Unit'">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Dies</span>
                    <input style="width:30%;" name="dies_unit" id="dies_unit" class="easyui-numberbox" data-options="prompt:'Unit'">
                    <input style="width:30%;" name="dies_price" id="dies_price" class="easyui-numberbox" data-options="prompt:'Price'">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Jig</span>
                    <input style="width:30%;" name="jig_unit" id="jig_unit" class="easyui-numberbox" data-options="prompt:'Unit'">
                    <input style="width:30%;" name="jig_price" id="jig_price" class="easyui-numberbox" data-options="prompt:'Price'">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Tooling</span>
                    <input style="width:30%;" name="tooling_unit" id="tooling_unit" class="easyui-numberbox" data-options="prompt:'Unit'">
                    <input style="width:30%;" name="tooling_price" id="tooling_price" class="easyui-numberbox" data-options="prompt:'Price'">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Fixture Cost</span>
                    <input style="width:30%;" name="fixture_cost_unit" id="fixture_cost_unit" class="easyui-numberbox" data-options="prompt:'Unit'">
                    <input style="width:30%;" name="fixture_cost_price" id="fixture_cost_price" class="easyui-numberbox" data-options="prompt:'Price'">
                </div>
            </div>
        </fieldset>
        <!-- <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Component List" data-options="rownumbers: true, singleSelect: false" idField="component_number">

        </table> -->
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
<iframe id="printout" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        // $('#dg_request').datagrid('loadData', []);
        url_save = '<?= base_url('pricing/breakdown_prices/create') ?>';
        $('#mold_unit').numberbox('setValue', 1);
        $('#dies_unit').numberbox('setValue', 1);
        $('#jig_unit').numberbox('setValue', 1);
        $('#tooling_unit').numberbox('setValue', 1);
        $('#fixture_cost_unit').numberbox('setValue', 1);
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        console.log(row);

        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('pricing/breakdown_prices/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        console.log(rows);
        if (rows.length > 0) {
            Swal.fire({
                title: 'Warning',
                text: 'Are you sure you want to delete this data?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('pricing/breakdown_prices/delete') ?>',
                            data: {
                                item_fg_id: row.item_fg_id,
                                revision: row.revision,
                                p_month: row.p_month,
                                p_year: row.p_year
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                Swal.fire({
                                    title: 'Error',
                                    text: jqXHR.statusText,
                                    icon: 'error',
                                    confirmButtonText: 'Ok'
                                });
                            },
                            complete: function(data) {
                                window.location.reload();
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter() {
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');

        var url = "?filter_period_month=" + filter_period_month +
            "&filter_period_year=" + filter_period_year +
            "&filter_item_fg_id=" + filter_item_fg_id +
            "&filter_revision=" + filter_revision;

        $('#dg').datagrid({
            url: '<?= base_url('pricing/breakdown_prices/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('pricing/breakdown_prices/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');

        var url = "?filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_item_fg_id=" + window.btoa(filter_item_fg_id) +
            "&filter_revision=" + window.btoa(filter_revision);

        window.location.assign('<?= base_url('pricing/breakdown_prices/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }
    
    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_breakdown_prices.xls') ?>');
    }

    $(function() {
        filter();

        //ADD DATA
        $('#dg').datagrid({
            url: '<?= base_url('pricing/breakdown_prices/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        })

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }
                            $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#dlg_insert').dialog({
            onOpen: function () {
                $.parser.parse('#dlg_insert');
            }
        });

        $('#filter_item_fg_id').combogrid({
            url: '<?= base_url('master/item_fg/reads/'); ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product No",
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 120
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

        $('#p_month').combobox({
            url: '<?= base_url('pricing/breakdown_prices/readPeriod/month'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Months',
        });

        $('#p_year').combobox({
            url: '<?= base_url('pricing/breakdown_prices/readPeriod/year'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Years',
        });

        $('#filter_period_month').combobox({
            url: '<?= base_url('pricing/breakdown_prices/readPeriod/month'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Months',
        });

        $('#filter_period_year').combobox({
            url: '<?= base_url('pricing/breakdown_prices/readPeriod/year'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Years',
        });

        $('#supplier_id').combogrid({
            url: '<?= base_url('master/suppliers/reads/'); ?>',
            panelWidth: 370,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            columns: [
                [{
                    field: 'number',
                    title: 'Supplier Code',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 250
                }, ]
            ]
        });

        $('#item_fg_number').combogrid({
            url: '<?= base_url('pricing/breakdown_prices/readItems'); ?>',
            panelWidth: 300,
            idField: 'item_fg_number',
            textField: 'item_fg_number',
            mode: 'remote',
            queryParams: {
                p_month: $('#p_month').combobox('getValue'),
                p_year: $('#p_year').combobox('getValue'),
                revision: $('#revision').combobox('getValue')
            },
            fitColumns: true,
            prompt: "Choose Part Number.",
            columns: [
                [{
                    field: 'item_fg_number',
                    title: 'Part No.',
                    width: 150
                }, {
                    field: 'item_fg_name',
                    title: 'Part Name',
                    width: 150
                }]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            onSelect: function(value, row) {
                $('#item_fg_id').textbox('setValue', row.item_fg_id);
                $('#item_fg_name').textbox('setValue', row.item_fg_name);
                $('#order_estimation').numberbox('setValue', row.volume);
                $('#model_name').textbox('setValue', row.model_name);
            }
        });
       
        $('#quotation_date').datebox({
            onSelect: function(date) {
                var y = date.getFullYear();
                var m = date.getMonth() + 1;
                var d = date.getDate();
                var dateStr = y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);

                $.post('<?= base_url('pricing/breakdown_prices/get_quotation_number'); ?>', {date: dateStr}, function(res) {
                    $('#quotation_number').textbox('setValue', res.number);
                }, 'json');
            }
        });

        $("#currency").combobox({
            url: '<?= base_url('master/currencies/reads') ?>',
            valueField: 'name',
            textField: 'name',
            prompt: "Choose Price Unit in",
            panelHeight: 'auto'
        });
    });

    function refreshProductGrid() {
        var m = $('#p_month').combobox('getValue');
        var y = $('#p_year').combobox('getValue');
        var r = $('#revision').combobox('getValue');

        $('#item_fg_number').combogrid('grid').datagrid('load', {
            p_month: m,
            p_year: y,
            revision: r
        });
    }

    $('#p_month, #p_year, #revision').combobox({
        onChange: function() {
            refreshProductGrid();
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

    function numberformat(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformatQpa(value, row) {
        if (value !== null && value !== undefined) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
        return "<b>0.00</b>"; // Atur agar 0 tetap ditampilkan dengan format yang sama
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        } else if (value == 2) {
            return "<b style='color:white;'>COMPLETE</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else if (value == 2) {
            return 'background-color:#4B54E7;';
        }
    }

    function statusissued(value, row, index) {
        if (value == "OPEN") {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function issuedformat(value, row) {
        if (value == "OPEN") {
            return "<b style='color:green;'>OPEN</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    //CELLSTYLE APPROVE
    function styleApproved(value, row, index) {
        if (value == "" || value === null ) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    //FORMATTER APPROVE
    function formatApproved(value) {
        if (value == "" || value === null ) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    function btnPrint(val, row) {
        var print = "print_breakdown('" + row.id + "')"; 
        return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
    }

    function print_breakdown(id) {
        if (!id) {
            alert("Data tidak lengkap!");
            return;
        }

        var url = "<?= base_url('pricing/breakdown_prices/print_breakdown/') ?>" + window.btoa(id);

        window.open(url, "_blank", "width=1200,height=600");
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

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('pricing/breakdown_prices/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('pricing/breakdown_prices/upload') ?>',
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
                            url: "<?= base_url('pricing/breakdown_prices/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('pricing/breakdown_prices/uploadCreate') ?>",
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
                                                url: "<?= base_url('pricing/breakdown_prices/uploadcreateFailed') ?>",
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
</script>
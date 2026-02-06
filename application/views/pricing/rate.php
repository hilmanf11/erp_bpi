<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Product No is taken from <b>Master Data > Engineering > Item Finish Good</b></li>
                <li>The Data Part No is taken from <b>Master Data > Engineering > Item Raw Material</b></li>
                <li>The Data Weight is taken from <b>Master Data > Engineering > Item Finish Good</b></li>
                <li>The Data Runner is taken from <b>Master Data > Engineering > Menu Loading</b></li>
                <li>The Data Cavity Standard is taken from <b>Master Data > Engineering > Master Mold</b></li>
            </ul>
        </div>
        <div title="CONDITIONS" style="padding: 20px;">
            <ul>
                <li><b>Composition</b> if Product Family is <b>VIRGIN</b> then ((Weight + (Runner / Cavity Standard)) / 1000)</li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'year',width:200,align:'center'">Year</th>
            <th rowspan="2" data-options="field:'efisiensi',width:200,halign:'center',align:'center'">Efficiency</th>
            <th colspan="2" data-options="field:'',width:150,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:150,halign:'center'"> Updated</th>
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
<div id="toolbar" style="height: 240px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 45%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Division</span>
                <input style="width:60%;" id="filter_division_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Year</span>
                <input style="width:60%;" id="filter_year" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Efficiency</span>
                <input style="width:60%;" id="filter_eficiency" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
        <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a> -->
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1300px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:15%; display:inline-block;">Year</span>
                <input style="width:40%;" id="year" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:15%; display:inline-block;">Eficiency %</span>
                <input style="width:40%;" id="eficiency" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:15%; display:inline-block;"></span>
                <a href="javascript:void(0)" class="easyui-linkbutton" id="btnPreview" iconCls="fa fa-search" onclick="previewData()">Preview All Tonage</a>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Bill of Material Lists" toolbar="#toolbar2"></table>
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
<iframe id="printout" src="<?= base_url('pricing/rate/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('pricing/rate/create') ?>';
        $('#frm_insert').form('clear');
    }

    function previewData() {
        var year = $("#year").combobox('getValue');
        var eficiency = $("#eficiency").combobox('getValue');

        if (!year || !eficiency) {
            toastr.error("Please Choose Year and Efficiency first");
            return;
        }

        $.messager.progress({ title: 'Please waiting', msg: 'Generating data...' });

        // Step 1: Ambil data Komponen berdasarkan Tahun (Salary, Electricity, dll)
        $.ajax({
            type: "post",
            url: "<?= base_url('pricing/rate/readYear'); ?>",
            data: { year: year },
            dataType: "json",
            success: function (comp) {
                if (!comp) {
                    $.messager.progress('close');
                    toastr.error("Component data for this year not found!");
                    return;
                }

                // Step 2: Ambil semua data Tonage
                $.ajax({
                    type: "post",
                    url: "<?= base_url('pricing/rate/readTonage'); ?>",
                    dataType: "json",
                    success: function (tonnageList) {
                        var finalRows = [];
                        var eff = parseFloat(eficiency) || 1;

                        // Step 3: Looping & Hitung Otomatis
                        $.each(tonnageList, function(index, row) {
                            var price = parseFloat(row.price) || 0;
                            var dep   = parseFloat(row.depretiation) || 1; // hindari pembagian nol
                            var intv  = parseFloat(row.interest) || 0;
                            var salary = parseFloat(comp.salary) || 0;

                            // Rumus Anda
                            var interest_amount = price * dep * (intv / 100);
                            var result_val = (price + interest_amount);
                            
                            // Perhitungan biaya (Sesuai logic di onSelect Anda)
                            var mach_dep_cost = result_val / dep / 12 / 21 / 3 / 8 / 3600 / (eff / 100);
                            var labour_cost = salary / 21 / 8 / 3600 / (eff / 100);

                            finalRows.push({
                                toonage: row.toonage,
                                price: price,
                                depretiation: dep,
                                interest: intv,
                                shift: comp.shift,
                                overhead: comp.overhead,
                                electricity: comp.electricity,
                                labour: row.manpower,
                                jumlah_machine: row.jumlah_machine,
                                machine_depretiation_cost: mach_dep_cost.toFixed(2),
                                labour_cost: labour_cost.toFixed(2),
                                // Set default 0 untuk field lainnya
                                plain_rate_sec: 0,
                                plain_rate_hour: 0,
                                foh: 0,
                                energy: 0
                            });
                        });

                        // Step 4: Masukkan ke DataGrid
                        $('#dg2').datagrid('loadData', finalRows);
                        $.messager.progress('close');
                        toastr.success(finalRows.length + " Tonage loaded.");
                    }
                });
            },
            error: function() {
                $.messager.progress('close');
                toastr.error("Failed to fetch data.");
            }
        });
    }

    function addTable(link = "") {
        $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'toonage',
                    width: 150,
                    halign: 'center',
                    title: "Tonage",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('pricing/rate/readTonage'); ?>',
                            required: true,
                            panelWidth: 250,
                            idField: 'toonage',
                            textField: 'toonage',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Toonage',
                            columns: [[
                                { field: 'toonage', title: 'Tonage', width: 100 },
                                { field: 'price', title: 'Price', width: 150 }
                            ]],

                            onBeforeSelect: function (index, row) {

                                var dg = $('#dg2');
                                var selectedRow = dg.datagrid('getSelected');
                                var selectedIndex = dg.datagrid('getRowIndex', selectedRow);
                                var rows = dg.datagrid('getRows');

                                for (var i = 0; i < rows.length; i++) {
                                    if (i !== selectedIndex && rows[i].toonage == row.toonage) {

                                        toastr.warning('Tonage already choose!', 'Duplicate');

                                        return false; 
                                    }
                                }
                                return true;
                            },
                            onSelect: function (value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var year = $("#year").combobox('getValue');
                                var eficiency = $("#eficiency").combobox('getValue');

                                var shift,salary,electricity,overhead;

                                $.when(
                                    $.ajax({
                                        type: "post",
                                        url: "<?= base_url('pricing/rate/readYear'); ?>",
                                        data: "year=" + year,
                                        dataType: "json",
                                        success: function (data) {
                                            shift = data.shift,
                                            salary= data.salary,
                                            electricity = data.electricity,
                                            overhead = data.overhead;
                                        }
                                    })
                                ).then(function () {

                                    var ed_price    = dg.datagrid('getEditor', {index: rowIndex, field: 'price'});
                                    var ed_dep      = dg.datagrid('getEditor', {index: rowIndex, field: 'depretiation'});
                                    var ed_int      = dg.datagrid('getEditor', {index: rowIndex, field: 'interest'});
                                    var ed_shift    = dg.datagrid('getEditor', {index: rowIndex, field: 'shift'});
                                    var ed_mach     = dg.datagrid('getEditor', {index: rowIndex, field: 'machine_depretiation_cost'});
                                    var ed_lab_cost = dg.datagrid('getEditor', {index: rowIndex, field: 'labour_cost'});
                                    var ed_labour   = dg.datagrid('getEditor', {index: rowIndex, field: 'labour'});
                                    var ed_overhead = dg.datagrid('getEditor', {index: rowIndex, field: 'overhead'});
                                    var ed_electricity = dg.datagrid('getEditor', {index: rowIndex, field: 'electricity'});
                                    var ed_jumlah_machine = dg.datagrid('getEditor', {index: rowIndex, field: 'jumlah_machine'});

                                    $(ed_price.target).textbox('setValue', rows.price);
                                    $(ed_dep.target).textbox('setValue', rows.depretiation);
                                    $(ed_int.target).textbox('setValue', rows.interest);
                                    $(ed_shift.target).textbox('setValue', shift);
                                    $(ed_labour.target).textbox('setValue', rows.manpower);
                                    $(ed_overhead.target).textbox('setValue', overhead);
                                    $(ed_electricity.target).textbox('setValue', electricity);
                                    $(ed_jumlah_machine.target).textbox('setValue', rows.jumlah_machine);

                                    var price = parseFloat(rows.price) || 0;
                                    var dep   = parseFloat(rows.depretiation) || 0;
                                    var intv  = parseFloat(rows.interest) || 0;
                                    var eff   = parseFloat(eficiency) || 1;

                                    var interest_amount = price * dep * (intv / 100);
                                    var result = (price + interest_amount);
                                    var mach_dep_cost = result / dep / 12 / 21 / 3 / 8 / 3600 / (eff / 100);
                                    var labour_cost = Math.round(salary) / 21 / 8 / 3600 / (eff / 100);

                                    $(ed_mach.target).numberbox('setValue', mach_dep_cost.toFixed(8));
                                    $(ed_lab_cost.target).numberbox('setValue', labour_cost.toFixed(8));
                                });
                            }
                        }
                    }
                }, {
                    field: 'id',
                    width: 100,
                    halign: 'center',
                    hidden: true,
                    title: "ID",
                    editor: {
                        type: 'textbox'
                    }
                 }, {
                    field: 'plain_rate_sec',
                    width: 100,
                    align: 'center',
                    title: "Plain Rate / <br> Sec",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                        }
                    }
                }, {
                    field: 'plain_rate_hour',
                    width: 100,
                    align: 'center',
                    title: "Plain Rate / <br> Hour",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                        }
                    }
                }, {
                    field: 'foh',
                    width: 100,
                    align: 'center',
                    title: "FOH",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                        }
                    }
                }, {
                    field: 'energy',
                    width: 100,
                    align: 'center',
                    title: "Energy",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                        }
                    }
                }, {
                    field: 'labour',
                    width: 100,
                    align: 'center',
                    title: "Labour/ <br>Machine",
                    editor: {
                        type: 'numberbox',
                    }
                }, {
                    field: 'labour_cost',
                    width: 100,
                    align: 'center',
                    title: "Labour <br>Cost",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                        }
                    }
                }, {
                    field: 'machine_depretiation_cost',
                    width: 100,
                    align: 'center',
                    title: "Machine <br>Deprc Cost",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                        }
                    }
                }, {
                    field: 'price',
                    width: 100,
                    align: 'center',
                    title: "Machine <br>Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                        }
                    }
                }, {
                    field: 'depretiation',
                    width: 100,
                    halign: 'center',
                    title: "Depretiation",
                    editor: {
                        type: 'numberbox'
                    }
                }, {
                    field: 'interest',
                    width: 100,
                    halign: 'center',
                    title: "Interest (%)",
                    editor: {
                        type: 'numberbox'
                    }
                }, {
                    field: 'electricity',
                    width: 100,
                    halign: 'center',
                    hidden: true,
                    title: "Electricity",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                        }
                    }
                 }, {
                    field: 'overhead',
                    width: 100,
                    halign: 'center',
                    hidden: true,
                    title: "Overhead",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                        }
                    }
                 }, {
                    field: 'shift',
                    width: 80,
                    halign: 'center',
                    title: "Shift",
                    editor: {
                        type: 'numberbox'
                    }
                 }, {
                    field: 'jumlah_machine',
                    width: 100,
                    halign: 'center',
                    hidden: true,
                    title: "Total MC",
                    editor: {
                        type: 'numberbox',
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
        var year = $("#year").combobox('getValue');
        var eficiency = $("#eficiency").combobox('getValue');
        if (year != "" && eficiency != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Year and Eficiency first");
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
            field: 'toonage'
        });

        var toonage = $(ed.target).combogrid('getValue');
        var year = $("#year").combobox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('pricing/rate/delete') ?>',
            data: {
                toonage: toonage,
                year: year
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
            url_save = '<?= base_url('pricing/rate/create') ?>';
            $("#year").combobox('disable');
            console.log(row);

            $("#year").combobox('setValue', row.year).combobox('disable');
            $("#eficiency").combobox('setValue', row.efisiensi);

            addTable('<?= base_url('pricing/rate/datatableUpdates?year=') ?>' + window.btoa(row.year)  + "&eficiency=" + window.btoa(row.efisiensi));
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
                            url: '<?= base_url('pricing/rate/delete') ?>',
                            data: {
                                efisiensi: row.efisiensi,
                                year: row.year
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
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_rate.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_eficiency = $("#filter_eficiency").combobox('getValue');

        var url = "?filter_year=" + window.btoa(filter_year) +
            "&filter_eficiency=" + window.btoa(filter_eficiency);

        $('#dg').datagrid({
            url: '<?= base_url('pricing/rate/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('pricing/rate/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_eficiency = $("#filter_eficiency").combobox('getValue');

        var url = "?filter_year=" + window.btoa(filter_year) +
            "&filter_eficiency=" + window.btoa(filter_eficiency);

        window.location.assign('<?= base_url('pricing/rate/print/excel') ?>' + url);
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
            url: '<?= base_url('pricing/rate/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.year + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_year = $("#filter_year").combogrid('getValue');
                var filter_eficiency = $("#filter_eficiency").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('pricing/rate/datatableDetails?year=') ?>' + window.btoa(row.year) + "&eficiency=" + window.btoa(row.efisiensi) + "&filter_year=" + window.btoa(filter_year) + "&filter_eficiency=" + window.btoa(filter_eficiency),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'toonage',
                            title: 'Tonage',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'plain_rate_sec',
                            title: 'Plain Rate / <br>Second',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'plain_rate_hour',
                            title: 'Plain Rate / <br>Hour',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'foh',
                            title: 'FOH',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'energy',
                            title: 'Energy',
                            halign: 'center',
                            width: 80
                        }, {
                            field: 'labour',
                            title: 'Labour',
                            halign: 'center',
                            width: 100,
                            align: 'right',
                        }, {
                            field: 'labour_cost',
                            title: 'Labour Cost',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'machine_depretiation_cost',
                            title: 'M/C Deprc. <br>Cost',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'price',
                            title: 'M/C Price',
                            width: 150,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'depretiation',
                            title: 'Depretiation',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'interest',
                            title: 'Interest',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                         }, {
                            field: 'shift',
                            title: 'Shift',
                            width: 100,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'created_by',
                            title: 'Created By',
                            hidden: true,
                            width: 120,
                            halign: 'center',
                         }, {
                            field: 'created_date',
                            title: 'Created Date',
                            hidden: true,
                            width: 120,
                            halign: 'center',
                         }, {
                            field: 'updated_by',
                            title: 'Update By',
                            hidden: true,
                            width: 100,
                            halign: 'center',
                        }, {
                            field: 'updated_date',
                            title: 'Update Date',
                            hidden: true,
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
        //             var year = $("#year").combobox('getValue');
        //             var efisiensi = $("#eficiency").combobox('getValue');

        //             var rows = $('#dg2').datagrid('getRows');
        //             var totalrows = rows.length;
        //             endEditing();

        //             for (let i = 0; i < totalrows; i++) {
        //                 if (rows[i].toonage) {
        //                     var dataFinal = {
        //                         year            : year,
        //                         efisiensi       : efisiensi,
        //                         toonage         : rows[i].toonage,
        //                         id              : rows[i].id,
        //                         plain_rate_sec  : rows[i].plain_rate_sec,
        //                         plain_rate_hour : rows[i].plain_rate_hour,
        //                         foh             : rows[i].foh,
        //                         energy          : rows[i].energy,
        //                         labour          : rows[i].labour,
        //                         labour_cost     : rows[i].labour_cost,
        //                         machine_depretiation_cost: rows[i].machine_depretiation_cost,
        //                         price           : rows[i].price,
        //                         depretiation    : rows[i].depretiation,
        //                         interest        : rows[i].interest,
        //                         electricity     : rows[i].electricity,
        //                         overhead        : rows[i].overhead,
        //                         shift           : rows[i].shift
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

                    endEditing();

                    var year       = $("#year").combobox('getValue');
                    var efisiensi  = $("#eficiency").combobox('getValue');
                    var rows       = $('#dg2').datagrid('getRows');
                    
                    var payload = {
                        year: year,
                        efisiensi: efisiensi,
                        rows: rows
                    };

                    // === SHOW LOADING ===
                    Swal.fire({
                        title: 'Saving...',
                        text: 'Please wait, saving all data.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: url_save,
                        type: "POST",
                        data: JSON.stringify(payload),
                        contentType: "application/json",
                        dataType: "json",
                        success: function(result) {

                            Swal.close(); // close loading

                            Swal.fire({
                                title: (result.message || "Saved"),
                                icon: (result.theme || "success"),
                                confirmButtonText: 'Ok',
                                allowOutsideClick: false,
                            }).then((x) => {
                                if (x.isConfirmed) {
                                    window.location.reload();
                                }
                            });
                        },
                        error: function(xhr) {

                            Swal.close(); // close loading

                            console.log(xhr.responseText);
                            Swal.fire("Error", "Gagal menyimpan data", "error");
                        }
                    });

                    $('#dlg_insert').dialog('close');
                }
            }]
        });
    });

    $('#filter_year').combobox({
        url: '<?= base_url('pricing/rate/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#year').combobox({
        url: '<?= base_url('pricing/rate/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#eficiency').combobox({
        url: '<?= base_url('pricing/rate/readEficiency'); ?>',
        valueField: 'efisiensi',
        textField: 'efisiensi',
        panelHeight:'auto',
        prompt: 'Choose or Input Eficiency',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_division_id').combobox({
        url: '<?= base_url('master/divisions/reads/'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Division'
    });

    $('#filter_eficiency').combobox({
        url: '<?= base_url('pricing/rate/readsEficiency'); ?>',
        valueField: 'efisiensi',
        textField: 'efisiensi',
        panelHeight:'auto',
        prompt: 'Choose or Eficiency',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    // UPLOAD DATA
    // $('#dlg_upload').dialog({
    //     buttons: [{
    //         text: 'List Failed',
    //         handler: function() {
    //             window.open('<?= base_url('pricing/rate/uploadDownloadFailed') ?>', '_blank');
    //         }
    //     }, {
    //         text: 'Upload',
    //         iconCls: 'icon-ok',
    //         handler: function() {
    //             $('#frm_upload').form('submit', {
    //                 url: '<?= base_url('pricing/rate/upload') ?>',
    //                 onSubmit: function() {
    //                     if ($(this).form('validate') == false) {
    //                         return $(this).form('validate');
    //                     } else {
    //                         $.messager.progress({
    //                             title: 'Please Wait',
    //                             msg: 'Importing Excel to Database'
    //                         });
    //                     }
    //                 },
    //                 success: function(result) {
    //                     $.messager.progress('close');
    //                     //Clear File
    //                     $.ajax({
    //                         url: "<?= base_url('pricing/rate/uploadclearFailed') ?>"
    //                     });
    //                     var json = eval('(' + result + ')');
    //                     requestData(json.total, json);

    //                     function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
    //                         if (value < 100) {
    //                             value = Math.floor((number / total) * 100);
    //                             $('#p_upload').progressbar('setValue', value);
    //                             $('#p_start').html(number);
    //                             $('#p_finish').html(total);

    //                             $.ajax({
    //                                 type: "POST",
    //                                 async: true,
    //                                 url: "<?= base_url('pricing/rate/uploadCreate') ?>",
    //                                 data: {
    //                                     "data": json[number - 1]
    //                                 },
    //                                 cache: false,
    //                                 dataType: "json",
    //                                 success: function(result) {
    //                                     if (result.theme == "success") {
    //                                         $('#p_success').html(success);
    //                                         var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
    //                                         requestData(total, json, number + 1, value, success + 1, failed + 0);
    //                                     } else {
    //                                         $('#p_failed').html(failed);
    //                                         var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
    //                                         //Json Failed
    //                                         $.ajax({
    //                                             type: "POST",
    //                                             async: true,
    //                                             url: "<?= base_url('pricing/rate/uploadcreateFailed') ?>",
    //                                             data: {
    //                                                 data: json[number - 1],
    //                                                 message: result.message
    //                                             },
    //                                             cache: false
    //                                         });
    //                                         requestData(total, json, number + 1, value, success + 0, failed + 1);
    //                                     }
    //                                     $("#p_remarks").append(title + "<br>");
    //                                 }
    //                             });
    //                         }
    //                     }
    //                 }
    //             });
    //         }
    //     }]
    // });
</script>
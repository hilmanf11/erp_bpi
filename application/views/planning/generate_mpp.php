<!-- <div id="dlg_check" class="easyui-dialog" title="Items Not in Menu Loading" style="width:400px;height:300px;padding:10px"
    data-options="modal:true,closed:true">
    <ul id="missing_items_list"></ul>
</div> -->
<style>
    /* Memaksa menu dropdown EasyUI agar tidak memunculkan scrollbar vertikal */
    .menu {
        overflow-y: hidden !important;
    }
</style>
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
                <a href="javascript:;" class="easyui-linkbutton" onclick="loadGrid()"><i class="fa fa-table"></i> Edit in DataGrid</a>
            </div>
        </fieldset>
        <!-- <fieldset style="width: 15%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Working Calendar</b></legend>
            <div id="showWorkingCalendar">
            </div>
        </fieldset> -->
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
</div>

<div id="section_grid" style="margin-top:5px; height: 500px; width: 100%; display: none;">
    <table id="dg_mpp" style="width:100%; height:100%;"></table>
</div>

<div id="section_print" style="margin-top:5px; width:100%;">
    <div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
        <iframe id="printout" src="" style="width: 100%; height: 550px; border: 0;"></iframe>
    </div>
</div>

<div id="menu_kanan" class="easyui-menu" style="width:150px; overflow: hidden;">
    <div id="btn_print" data-options="iconCls:'icon-print'">Print Schedule</div>
    <div class="menu-sep"></div>
    <div id="btn_cancel" data-options="iconCls:'icon-cancel'">Cancel</div>
</div>

<script>
    //Add Data
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_cutoff = $("#filter_cutoff").datebox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        $.messager.prompt('Generate Loadcap', 'Please input Password Generate', function(r) {
            if (r == "GENERATEMPP") {
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
                    url: "<?= base_url('planning/generate_mpp/getdata') ?>",
                    data: "filter_month=" + window.btoa(filter_month) +
                        "&filter_year=" + window.btoa(filter_year) +
                        "&filter_revision=" + window.btoa(filter_revision) +
                        "&filter_cutoff=" + window.btoa(filter_cutoff) +
                        "&filter_customer=" + window.btoa(filter_customer) +
                        "&filter_item_fg=" + window.btoa(filter_item_fg),
                    dataType: "json",
                    success: function(rows) {
                        Swal.close();
                        requestData(rows['total'], rows.rows);

                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                            if (value < 100) {
                                value = Math.floor((number / total) * 100);
                                $('#p_upload').progressbar('setValue', value);
                                $('#p_start').html(number);
                                $('#p_finish').html(total);

                                $.post('<?= base_url('planning/generate_mpp/create') ?>', {
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
                                            url: "<?= base_url('planning/generate_mpp/uploadcreateFailed') ?>",
                                            data: {
                                                data: json[number - 1],
                                                message: result.message
                                            },
                                            cache: false
                                        });

                                        requestData(total, json, number + 1, value, success + 0, failed + 1);
                                    }

                                    // if (value == 100) {
                                    //     $.ajax({
                                    //         type: "get",
                                    //         url: "<?= base_url('planning/generate_mpp/rekapMachine') ?>",
                                    //         data: "filter_month=" + window.btoa(filter_month) +
                                    //             "&filter_year=" + window.btoa(filter_year) +
                                    //             "&filter_revision=" + window.btoa(filter_revision),
                                    //         beforeSend: function() {
                                    //             Swal.fire({
                                    //                 title: 'Summarizing Load per Machine...',
                                    //                 text: 'Please wait while calculating load data.',
                                    //                 allowOutsideClick: false,
                                    //                 showConfirmButton: false,
                                    //                 didOpen: () => Swal.showLoading()
                                    //             });
                                    //         },
                                    //         success: function(res) {
                                    //             Swal.fire({
                                    //                 title: 'Summarizing Load per Tonnage...',
                                    //                 text: 'Please wait...',
                                    //                 allowOutsideClick: false,
                                    //                 showConfirmButton: false,
                                    //                 didOpen: () => Swal.showLoading()
                                    //             });

                                    //             $.ajax({
                                    //                 type: "get",
                                    //                 url: "<?= base_url('planning/generate_mpp/rekapTonnage') ?>",
                                    //                 data: {
                                    //                     filter_month: window.btoa(filter_month),
                                    //                     filter_year: window.btoa(filter_year),
                                    //                     filter_revision: window.btoa(filter_revision)
                                    //                 },
                                    //                 success: function(res) {
                                    //                     Swal.fire({
                                    //                         title: 'Summarizing Manpower...',
                                    //                         text: 'Please wait...',
                                    //                         allowOutsideClick: false,
                                    //                         showConfirmButton: false,
                                    //                         didOpen: () => Swal.showLoading()
                                    //                     });

                                    //                     $.ajax({
                                    //                         type: "get",
                                    //                         url: "<?= base_url('planning/generate_mpp/rekapManPower') ?>",
                                    //                         data: {
                                    //                             filter_month: window.btoa(filter_month),
                                    //                             filter_year: window.btoa(filter_year),
                                    //                             filter_revision: window.btoa(filter_revision)
                                    //                         },
                                    //                         success: function() {
                                    //                             Swal.close();
                                    //                             Swal.fire('Good job!', 'Machine,tonnage and manpower recaps completed!', 'success');
                                    //                         }
                                    //                     });
                                    //                 }
                                    //             });
                                    //         }
                                    //     });
                                    // }

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
        window.open('<?= base_url('planning/generate_mpp/uploadDownloadFailed') ?>', '_blank');
    }

    // Fungsi khusus untuk merapikan kembali baris yang pecah
    function reMergeCells(target) {
        var rows = $(target).datagrid('getRows');
        var rowspan = 1;
        var startIndex = 0;

        for (var i = 0; i < rows.length; i++) {
            if (i === rows.length - 1 || rows[i].machine_number !== rows[i+1].machine_number) {
                if (rowspan > 1) {
                    $(target).datagrid('mergeCells', {
                        index: startIndex,
                        field: 'machine_number', // Tambahkan field lain di sini jika ada yg di-merge juga
                        rowspan: rowspan
                    });
                }
                startIndex = i + 1; 
                rowspan = 1;                
            } else {
                rowspan++; 
            }
        }
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
            $("#section_grid").hide();
            $("#section_print").show();

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/generate_mpp/print') ?>' + url);
        }
    }

    var editIndex = undefined;
    var isInvalidEdit = false;

    function endEditing() {
        if (editIndex == undefined) { return true; }
        if ($('#dg_mpp').datagrid('validateRow', editIndex)) {
            $('#dg_mpp').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function loadGrid() {
        // 1. Ambil nilai filter dari form input
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_cutoff = $("#filter_cutoff").datebox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please select Period!", "Information");
            return;
        }

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_cutoff=" + window.btoa(filter_cutoff) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        $("#section_print").hide();
        $("#section_grid").show();

        // =========================================================
        // 2. PEMBENTUKAN ARRAY KOLOM (DIPISAH FROZEN & SCROLL)
        // =========================================================
        
        var daysInMonth = new Date(filter_year, filter_month, 0).getDate();
        
        // --- KOLOM FROZEN ---
        var frozenCols = [
            {field: 'machine_number', title: 'MC No', width: 80, align: 'center'},
            {field: 'customer_name', title: 'Customer', width: 150},
            {field: 'custom_no', title: 'No', width: 40, align: 'center'},
            {field: 'number', title: 'Product No', width: 120},
            {field: 'name', title: 'Product Name', width: 200}
        ];

        // --- KOLOM SCROLL ---
        // Sisa kolom statis yang bisa digeser
        var scrollCols = [
            {field: 'status_subcont', title: 'Subcont', width: 80, align: 'center'},
            {field: 'mold_name', title: 'Mold No', width: 120},
            {field: 'color', title: 'Color', width: 80},
            
            {field: 'manpower', title: 'Manpower', width: 70, align: 'right'},
            {field: 'cavity', title: 'Cav Std', width: 60, align: 'right'},
            {field: 'cycle_time', title: 'C/T', width: 60, align: 'right'},
            
            {field: 'mpq', title: 'Mpq', width: 60, align: 'right'},
            {field: 'qty_box', title: 'Box', width: 60, align: 'right'},
            {field: 'default_packing', title: 'Default Packing', width: 100, align: 'right'},
            
            {field: 'cap_hour', title: 'Cap/Hour', width: 80, align: 'right', formatter: function(val){ return Number(val).toFixed(0); }},
            {field: 'cap_shift', title: 'Cap/Shift', width: 80, align: 'right', formatter: function(val){ return Number(val).toFixed(0); }},
            {field: 'cap_day', title: 'Cap/Day', width: 80, align: 'right', formatter: function(val){ return Number(val).toFixed(0); }},
            
            {field: 'hour_req', title: 'Hour Req', width: 70, align: 'right'},
            {field: 'day_req', title: 'Day Req', width: 70, align: 'right'},
            
            {field: 'os_so', title: 'OS SO', width: 80, align: 'right'},
            {field: 'so', title: 'SO', width: 80, align: 'right'},
            {field: 'fg', title: 'Stock FG', width: 80, align: 'right'},
            {field: 'wip', title: 'Stock WIP', width: 80, align: 'right'},
            {field: 'ito', title: 'ITO', width: 60, align: 'right'},
            
            {field: 'prodplan', title: 'Prodplan MPS', width: 90, align: 'right'},
            {field: 'prodplan_mpp', title: 'Prodplan MPP', width: 90, align: 'right'},
            
            {field: 'overload', title: 'Overload', width: 80, align: 'right', styler: function(value,row,index){
                if (value > 0) { return 'color:red; font-weight:bold;'; } 
            }},
            
            {field: 'forecast', title: 'Forecast', width: 80, align: 'right'},
            {field: 'total_mpp', title: 'Total MPP', width: 80, align: 'right', styler: function(){ return 'font-weight:bold; color:black;'; }},
            
            {field: 'load_vs_cap', title: 'Load Vs Cap', width: 80, align: 'right', styler: function(){ return 'font-weight:bold;'; }, formatter: function(val){
                return val ? val + '%' : '0%';
            }}
        ];

        var dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        for (var i = 1; i <= daysInMonth; i++) {
            var dateObj = new Date(filter_year, filter_month - 1, i);
            var dayName = dayNames[dateObj.getDay()];

            (function(dayIndex) {
                scrollCols.push({
                    field: 'day_' + dayIndex,
                    title: dayName + '<br>' + dayIndex, 
                    width: 50,
                    align: 'right',
                    
                    // ==========================================
                    // UBAH EDITOR MENJADI TEXT BIASA (SUPER RINGAN)
                    // ==========================================
                    editor: {
                        type: 'text' 
                    },
                    // ==========================================

                    formatter: function(val) {
                        return (val == 0 || val == null || val === '') ? '-' : val;
                    },
                    styler: function(value, row, index) {
                        if (row['day_' + dayIndex + '_printed'] == 1) {
                            return 'background-color: #c8e6c9; color: #000; font-weight: bold;'; 
                        }
                    }
                });
            })(i);
        }

        // =========================================================
        // 3. INISIALISASI DATAGRID EASYUI
        // =========================================================
        $('#dg_mpp').datagrid({
            url: '<?= base_url('planning/generate_mpp/datatables') ?>' + url,
            rownumbers: false, 
            singleSelect: true,
            fitColumns: false,
            frozenColumns: [frozenCols], 
            columns: [scrollCols], 

            // --- LOGIKA INLINE EDITING (SINGLE CLICK) ---
            onClickRow: function(index, row) {
                if (editIndex != index) {
                    if (endEditing()) { 
                        
                        if (isInvalidEdit) {
                            isInvalidEdit = false;
                            // Hilangkan pemanggilan selectRow di sini, biarkan saja
                            return; 
                        }

                        editIndex = index; // Set index lebih awal
                        
                        // Jeda super singkat (10ms) hanya untuk melepas antrean main-thread browser
                        // sehingga warna baris (highlight selected) muncul duluan sebelum browser freeze merender editor
                        setTimeout(function() {
                            $('#dg_mpp').datagrid('beginEdit', index); 
                        }, 10);

                    } else {
                        // Kembalikan seleksi ke baris yang sedang diedit jika gagal validasi
                        setTimeout(function() {
                            $('#dg_mpp').datagrid('selectRow', editIndex); 
                        }, 10);
                    }
                }
            },

            // =======================================================
            // KUNCI KOTAK INPUT (MATIKAN EDITOR) & PASANG TOMBOL ENTER
            // =======================================================
            onBeginEdit: function(index, row) {
                var editors = $(this).datagrid('getEditors', index);

                for (var i = 0; i < editors.length; i++) {
                    var ed = editors[i];
                    
                    // KARENA KITA PAKAI 'TEXT', ed.target SUDAH LANGSUNG BERUPA INPUT
                    var tbox = $(ed.target);

                    // 1. SIHIR TOMBOL ENTER UNTUK SAVE
                    tbox.bind('keydown', function(e) {
                        if (e.keyCode === 13) { 
                            e.preventDefault(); 
                            $('#dg_mpp').datagrid('endEdit', index);
                            editIndex = undefined;
                        }
                    });

                    // 2. OPTIMASI READONLY (HARI YANG SUDAH DIPRINT)
                    if (ed.field.indexOf('day_') === 0) {
                        var dayNum = ed.field.split('_')[1]; 
                        
                        if (row['day_' + dayNum + '_printed'] == 1) {
                            tbox.attr('readonly', true).css({
                                'background-color': '#eeeeee',
                                'color': '#888888',
                                'cursor': 'not-allowed'
                            });
                        } else {
                            // Opsional: Agar teks rata kanan saat sedang diketik (seperti numberbox)
                            tbox.css('text-align', 'right');
                        }
                    }
                }
            },

            onAfterEdit: function(index, row, changes) {
                if (!$.isEmptyObject(changes)) {
                    var newTotalMpp = 0;
                    for (var i = 1; i <= 31; i++) {
                        var val = parseInt(row['day_' + i]) || 0;
                        newTotalMpp += val;
                    }

                    var prodplanMpp = parseInt(row.prodplan_mpp) || 0;

                    if (newTotalMpp > prodplanMpp) {
                        toastr.warning(
                            "Total MPP (" + newTotalMpp + ") > Prodplan Mpp (" + prodplanMpp + ")!", 
                            "Data Returned",
                            { timeOut: 5000 } 
                        );
                        $('#dg_mpp').datagrid('rejectChanges'); 
                        isInvalidEdit = true; 
                        return; 
                    }

                    isInvalidEdit = false; 

                    // Update lokal agar user melihat angka langsung berubah
                    $('#dg_mpp').datagrid('updateRow', {
                        index: index,
                        row: { total_mpp: newTotalMpp }
                    });
                    $('#dg_mpp').datagrid('acceptChanges');

                    reMergeCells('#dg_mpp');

                    // kirim ke server 
                    $.ajax({
                        url: '<?= base_url('planning/generate_mpp/update_inline') ?>', 
                        type: 'POST',
                        dataType: 'json',
                        data: row, 
                        success: function(response) {
                            if (response.status === 'success') {
                                toastr.success("Data successfully saved.!");
                            } else {
                                toastr.error("Failed to update data in the database.");
                                $('#dg_mpp').datagrid('rejectChanges'); 
                            }
                        },
                        error: function() {
                            toastr.error("A connection error occurred while saving!");
                            $('#dg_mpp').datagrid('rejectChanges'); 
                        }
                    });
                }
            },

            // --- LOGIKA MERGE CELLS (ROWSPAN) ---
            onLoadSuccess: function(data) {
                reMergeCells(this); 
                editIndex = undefined;
            }
        });

        // 4. TRIK MASTER: TANGKAP KLIK KANAN SECARA GLOBAL DI PANEL DATAGRID
        $('#dg_mpp').datagrid('getPanel').on('contextmenu', function(e) {
            window.selectedRowForPrint = row;
            window.selectedDayForPrint = dayNumber;
            window.selectedQtyForPrint = qtyAtDay;
            var td = $(e.target).closest('td[field^="day_"]');

            if (td.length > 0) {
                e.preventDefault(); 

                var field = td.attr('field'); 
                var tr = td.closest('tr.datagrid-row'); 
                var index = parseInt(tr.attr('datagrid-row-index')); 

                var isEditing = td.find('input').length > 0;

                if (isEditing) {
                    $('#dg_mpp').datagrid('endEdit', index);
                    editIndex = undefined; 
                }

                var row = $('#dg_mpp').datagrid('getRows')[index];
                var dayNumber = field.split('_')[1]; 
                var qtyAtDay = row[field]; 

                // ========================================================
                // VALIDASI: BLOKIR JIKA SUDAH DIPRINT (HIJAU)
                // ========================================================
                if (row['day_' + dayNumber + '_printed'] == 1) {
                    toastr.warning("Schedule for " + dayNumber + " it has already been sent to production and cannot be reprinted!", "Lock");
                    return; // STOP DI SINI! Menu tidak akan muncul.
                }

                $('#dg_mpp').datagrid('selectRow', index);

                selectedRowForPrint = row;
                selectedDayForPrint = dayNumber;
                selectedQtyForPrint = qtyAtDay;

                // TAMPILKAN MENU PADA KURSOR (Tanpa onClick di dalamnya!)
                $('#menu_kanan').menu('show', {
                    left: e.pageX,
                    top: e.pageY
                });
            }
        });
    }

    $(document).ready(function() {

        // BINDING MENU HANYA 1 KALI
        $('#menu_kanan').menu({
            onClick: function(item) {
                if (item.id === 'btn_print') {
                    
                    // Pastikan ada data yang dipilih
                    if (window.selectedRowForPrint !== null && window.selectedDayForPrint !== null) {
    
                        // Gunakan window.selectedRowForPrint dst
                        if (window.selectedRowForPrint['day_' + window.selectedDayForPrint + '_printed'] == 1) {
                            toastr.error("Tindakan dicegah!...", "Blokir");
                            return;
                        }

                        prosesPrintSchedule(window.selectedRowForPrint, window.selectedDayForPrint, window.selectedQtyForPrint);
                        
                        // Reset
                        window.selectedRowForPrint = null;
                        window.selectedDayForPrint = null;
                        window.selectedQtyForPrint = null;
                    }

                } 
            }
        });
    });

    function revisionSelected(filter_month, filter_year) {
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/generate_mpp/revision') ?>",
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
            url: "<?= base_url('planning/generate_mpp/checkCalendar') ?>",
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
            window.location.assign('<?= base_url('planning/generate_mpp/print/excel') ?>' + url);
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
            window.location.assign('<?= base_url('planning/generate_mpp/recap_machine/excel') ?>' + url);
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
            url: '<?= base_url('planning/generate_mpp/datatables') ?>',
            rownumbers: true
        });

        $('#filter_month').combobox({
            url: '<?php echo base_url('planning/generate_mpp/readMonths'); ?>',
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
            url: '<?php echo base_url('planning/generate_mpp/readYears'); ?>',
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
            url: '<?php echo base_url('planning/generate_mpp/readRevisions'); ?>',
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

    function prosesPrintSchedule(row, dayNumber, qtyAtDay) {
        // Validasi dasar
        if (qtyAtDay == null || qtyAtDay == 0 || qtyAtDay === '-') {
            toastr.warning("Tidak bisa diprint! Qty pada tanggal " + dayNumber + " masih kosong.", "Peringatan");
            return;
        }

        // Ambil nilai Bulan dan Tahun dari filter atas
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');

        // Rapikan format tanggal menjadi YYYY-MM-DD (contoh: 2026-04-02)
        var padMonth = filter_month.toString().padStart(2, '0');
        var padDay = dayNumber.toString().padStart(2, '0');
        var trans_date = filter_year + '-' + padMonth + '-' + padDay;

        // Kumpulkan data yang menyerupai form inputan user
        var payload = {
            month: padMonth,
            year: filter_year,
            trans_date: trans_date,
            item_fg_id: row.item_fg_id,
            item_fg_name: row.name,
            mold_id: row.mold_id,
            machine_id: row.machine_id,
            qty: qtyAtDay,
            meta_data: 'MPP'
        };

        // Kirim data ke PHP
        $.ajax({
            url: '<?= base_url('planning/generate_mpp/create_from_mpp') ?>',
            type: 'POST',
            dataType: 'json',
            data: payload,
            beforeSend: function() {
                toastr.info("Processing...", "Please Wait!");
            },
            success: function(response) {
                if (response.theme === 'success') {
                    // 1. Munculkan Notif Sukses Utama
                    toastr.success("Production Schedule has been saved", "Success");
                    
                    // 2. Reload Datagrid agar sel menjadi hijau
                    $('#dg_mpp').datagrid('reload');

                    // 3. Cek apakah ID berhasil ditangkap
                    if (response.id) {
                        // Munculkan notif loading tambahan agar user tahu sistem sedang bekerja
                        toastr.info("Membuka dokumen Print...", "Mohon Tunggu");
                        
                        // ======================================================
                        // JEDA WAKTU (DELAY) SEBELUM BUKA TAB BARU
                        // ======================================================
                        setTimeout(function() {
                            var printUrl = '<?= base_url("planning/generate_mpp/print_wo/") ?>' + response.id;
                            window.open(printUrl, '_blank'); 
                        }, 5000); 
                        // ======================================================

                    } else {
                        toastr.warning("Data tersimpan, tapi gagal membuka halaman print (ID tidak ditemukan).");
                    }

                } else {
                    toastr.error("Failed to process data", "Error");
                    $('#dg_mpp').datagrid('reload');
                }
            },
        });
    }

    // function checkMenuLoading() {
    //     $.ajax({
    //         url: '<?= base_url('planning/generate_mpp/check_menu_loading') ?>',
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
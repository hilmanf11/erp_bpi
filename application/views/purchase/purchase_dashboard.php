<div class="dashboard-wrapper">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <style>
        .dashboard-wrapper { background-color: #f8fafc; padding: 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Header Section */
        .section-header {
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 15px;
            padding-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1e293b;
            font-weight: 600;
        }

        /* Filter Container - Pill Style */
        .filter-bar {
            background: white;
            padding: 10px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }

        .pill-group {
            display: flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 50px;
            gap: 2px;
        }

        .pill-btn {
            border: none;
            background: transparent;
            padding: 6px 18px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: 0.3s;
        }

        .pill-btn.active {
            background: #244b82;
            color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* KPI Grid & Cards */
        .kpi-row { display: flex; gap: 20px; margin-bottom: 25px; }
        .kpi-container { flex: 3; display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        
        .kpi-card {
            border-radius: 15px;
            padding: 20px;
            color: white;
            position: relative;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .kpi-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.2);
            padding: 2px 10px;
            border-radius: 10px;
            font-size: 11px;
        }

        .kpi-icon-bg {
            position: absolute;
            left: 15px;
            top: 20px;
            font-size: 30px;
            opacity: 0.8;
        }

        .kpi-content { margin-top: auto; }
        .kpi-label { font-size: 12px; font-weight: 500; text-transform: uppercase; opacity: 0.9; margin-bottom: 5px;}
        .kpi-value { font-size: 28px; font-weight: 700; }

        /* Chart Side Card */
        .defect-ratio-card {
            flex: 1;
            background: white;
            border-radius: 15px;
            padding: 15px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        /* Colors */
        .bg-blue { background: linear-gradient(135deg, #244b82 0%, #729dcb 100%); }
        .bg-green { background: linear-gradient(135deg, #508758 0%, #88c396 100%); }
        .bg-red { background: linear-gradient(135deg, #d15e29 0%, #feae88 100%); }
        .bg-blue-grad { background: linear-gradient(135deg, #244b82 0%, #729dcb 100%); }
        .bg-green-grad { background: linear-gradient(135deg, #508758 0%, #88c396 100%); }
        .bg-orange-grad { background: linear-gradient(135deg, #ff6016 0%, #ff9868 100%); }
        .bg-red-grad { background: linear-gradient(135deg, #cb2d3e 0%, #ea7777 100%); }

        /* Chart Section Custom */
        .chart-section {
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .chart-header {
            background: #244b82;
            color: white;
            padding: 12px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            letter-spacing: 1px;
        }
        .week-selector {
            display: block;
            margin-top: 8px;
            padding: 5px 12px;
            border-radius: 20px;
            border: 1px solid #ddd;
            font-size: 14px;
            outline: none;
            width: fit-content;
        }

        .pill-group {
            position: relative;
            display: inline-flex;
        }

        /* Styling dropdown agar melayang di bawah tombol */
        .floating-filter {
            position: absolute;
            top: 45px;
            left: 0;
            z-index: 999;
            background: #fff;
            padding: 5px;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* Dropdown Chart Download */
        .apexcharts-menu {
            min-width: 155px !important;
            padding: 10px 0 !important;
            border-radius: 8px !important;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2) !important;
        }

        .apexcharts-menu-item {
            padding: 10px 20px !important;
            font-size: 14px !important;
            transition: background 0.2s ease;
        }

        .apexcharts-menu-item:hover {
            background-color: #f1f1f1 !important;
            color: #0000FF !important;
        }

        /* --- FULL SCREEN STYLE --- */
        .chart-section:fullscreen {
            padding: 20px !important;
            background: white !important;
            width: 100vw;
            height: 100vh;
        }

        /* --- PRINT STYLE --- */
        @media print {
            /* Sembunyikan SEMUA elemen jika sedang mode printing-chart */
            body.printing-chart * {
                visibility: hidden;
                margin: 0;
            }

            /* Tampilkan HANYA elemen yang memiliki class printable-area dan anak-anaknya */
            body.printing-chart .printable-area,
            body.printing-chart .printable-area * {
                visibility: visible;
            }

            /* Posisikan elemen yang dicetak di pojok kiri atas kertas */
            body.printing-chart .printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                border: none !important; /* Hapus border saat dicetak agar bersih */
            }

            /* Sembunyikan ikon tombol saat dicetak agar tidak mengganggu visual */
            .custom-tools {
                display: none !important;
            }
            
            /* Sembunyikan toolbar bawaan ApexCharts saat dicetak */
            .apexcharts-toolbar {
                display: none !important;
            }
        }
    </style>

    <div class="section-header">
        <i class="fa-solid fa-square-poll-vertical"></i> <span>PURCHASE TRENDS</span>
    </div>

    <div class="filter-bar">
        <form id="form_purchase_trends" method="POST">
            <div class="pill-group">
                <!-- <button type="button" class="pill-btn active" onclick="togglePill(this, 'all')"><i class="fa fa-list"></i> All</button> -->
                <button type="button" class="pill-btn" onclick="togglePill(this, 'daily', 'form_purchase_trends')"><i class="fa fa-calendar-day"></i> Daily</button>
                <button type="button" class="pill-btn" onclick="togglePill(this, 'weekly', 'form_purchase_trends')"><i class="fa fa-calendar-week"></i> Weekly</button>
                <button type="button" class="pill-btn" onclick="togglePill(this, 'monthly', 'form_purchase_trends')"><i class="fa fa-calendar"></i> Monthly</button>
                <button type="button" class="pill-btn" onclick="togglePill(this, 'yearly', 'form_purchase_trends')"><i class="fa fa-calendar"></i> Yearly</button>

                <input id="filter_supplier_id" name="filter_supplier_id" class="easyui-combobox" style="width:150px; height:32px;" prompt="Supplier">
                <input id="filter_division" name="filter_division" class="easyui-combobox" style="width:150px; height:32px;" prompt="Division">
                
            </div>
        </form>

        <a href="javascript:;" class="easyui-linkbutton" onclick="submitFilter('form_purchase_trends')" data-options="iconCls:'icon-search'" style="height:32px; padding:0 15px;">Filter</a>
        <a href="javascript:;" class="easyui-linkbutton" onclick="reload()" data-options="iconCls:'icon-reload'" style="height:32px; padding:0 15px;">Reload</a>
    </div>

    <!-- di UI Purchase Dashboard tidak ada
    <div class="kpi-row">
        <div class="kpi-container">
            <div class="kpi-card bg-blue-grad">
                <i class="fa fa-money kpi-icon-bg"></i>
                <div class="kpi-content">
                    <div class="kpi-label">Total Purchase Amount</div>
                    <div id="kpi_total_amt" class="kpi-value">0</div>
                </div>
            </div>
            <div class="kpi-card bg-green-grad">
                <i class="fa fa-check-circle kpi-icon-bg"></i>
                <div class="kpi-content">
                    <div class="kpi-label">Total PO Issued</div>
                    <div id="kpi_total_po" class="kpi-value">0</div>
                </div>
            </div>
            <div class="kpi-card bg-orange-grad">
                <i class="fa fa-building kpi-icon-bg"></i>
                <div class="kpi-content">
                    <div class="kpi-label">Active Suppliers</div>
                    <div id="kpi_total_supp" class="kpi-value">0</div>
                </div>
            </div>
        </div>

    </div>
    -->

    <div style="display: flex; gap: 15px; height: 550px; align-items: stretch;"> 
        <div id="purchaseChartSection" class="chart-section" style="flex: 1; display: flex; flex-direction: column; overflow: hidden; border: 1px solid #ddd; background: white;">
            <div class="chart-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span>Purchase Amount (IDR)</span>
                
                <div class="custom-tools" style="display: flex; gap: 15px;">
                    <a href="javascript:void(0)" onclick="exportToExcel('purchaseChart')" title="Export Excel" style="color: white;">
                        <i class="fa fa-file-excel"></i>
                    </a>
                    <a href="javascript:void(0)" onclick="printSpecificChart('purchaseChartSection')" title="Print Chart" style="color: white;">
                        <i class="fa fa-print"></i>
                    </a>
                    <a href="javascript:void(0)" onclick="toggleFullScreen('purchaseChartSection')" title="Full Screen" style="color: white;">
                        <i class="fa fa-expand"></i>
                    </a>
                </div>
            </div>
            <div style="padding: 10px; flex: 1; position: relative; min-height: 0;">
                <div id="purchaseChart" style="height: 100%; width: 100%;"></div>
            </div>
        </div>

        <div id="supplierChartSection" class="chart-section" style="flex: 1; display: flex; flex-direction: column; overflow: hidden; border: 1px solid #ddd;">
            <div class="chart-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span>
                Purchase Amount (IDR) - TOP 10 Supplier
                </span>

                <div class="custom-tools" style="display: flex; gap: 15px;">
                    <a href="javascript:void(0)" onclick="exportToExcel('supplierChart')" title="Export Excel" style="color: white;">
                        <i class="fa fa-file-excel"></i>
                    </a>
                    <a href="javascript:void(0)" onclick="printSpecificChart('supplierChartSection')" title="Print Chart" style="color: white;">
                        <i class="fa fa-print"></i>
                    </a>
                    <a href="javascript:void(0)" onclick="toggleFullScreen('supplierChartSection')" title="Full Screen" style="color: white;">
                        <i class="fa fa-expand"></i>
                    </a>
                </div>
                
            </div>
            <div style="padding: 10px; flex: 1; position: relative; min-height: 0;">
                <div id="supplierChart" style="height: 100%; width: 100%;"></div>
            </div>
        </div>
    </div>





    <div class="section-header">
        <i class="fa-solid fa-bar-chart"></i> <span>PURCHASE PLAN VS ACTUAL</span>
    </div>

    <div class="filter-bar">
        <form id="form_plan_vs_actual" method="POST">
            <div class="pill-group">
                <!-- <button type="button" class="pill-btn active" onclick="togglePill(this, 'all', 'form_plan_vs_actual')"><i class="fa fa-list"></i> All</button> -->
                <button type="button" class="pill-btn" onclick="togglePill(this, 'daily', 'form_plan_vs_actual')"><i class="fa fa-calendar-day"></i> Daily</button>
                <button type="button" class="pill-btn" onclick="togglePill(this, 'weekly', 'form_plan_vs_actual')"><i class="fa fa-calendar-week"></i> Weekly</button>
                <button type="button" class="pill-btn" onclick="togglePill(this, 'monthly', 'form_plan_vs_actual')"><i class="fa fa-calendar"></i> Monthly</button>
                <button type="button" class="pill-btn" onclick="togglePill(this, 'yearly', 'form_plan_vs_actual')"><i class="fa fa-calendar"></i> Yearly</button>

                <input id="filter_supplier_id" name="filter_supplier_id" class="easyui-combobox" style="width:150px; height:32px;" prompt="Supplier">
                <input id="filter_division" name="filter_division" class="easyui-combobox" style="width:150px; height:32px;" prompt="Division">
                
            </div>
        </form>

        <a href="javascript:;" class="easyui-linkbutton" onclick="submitFilter('form_plan_vs_actual')" data-options="iconCls:'icon-search'" style="height:32px; padding:0 15px;">Filter</a>
        <a href="javascript:;" class="easyui-linkbutton" onclick="reload()" data-options="iconCls:'icon-reload'" style="height:32px; padding:0 15px;">Reload</a>
    </div>

    <div id="planActualChartSection" class="chart-section" style="width: 100%; height: 500px; display: flex; flex-direction: column; border: 1px solid #ddd; margin-top: 20px;">

        <div class="chart-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-weight: bold;">
                <i class="fa fa-chart-bar"></i> Purchase Plan VS Actual by QTY
            </div>
            
            <div class="custom-tools" style="display: flex; gap: 15px;">
                <a href="javascript:void(0)" onclick="exportToExcel('planActualChart')" title="Export Excel" style="color: white;">
                    <i class="fa fa-file-excel"></i>
                </a>
                <a href="javascript:void(0)" onclick="printSpecificChart('planActualChartSection')" title="Print Chart" style="color: white;">
                    <i class="fa fa-print"></i>
                </a>
                <a href="javascript:void(0)" onclick="toggleFullScreen('planActualChartSection')" title="Full Screen" style="color: white;">
                    <i class="fa fa-expand"></i>
                </a>
            </div>
        </div>

        <div style="padding: 20px; flex: 1; position: relative; background-color: white;">
            <div id="planActualChart" style="height: 100%; width: 100%;"></div>
        </div>
    </div>




    <div class="section-header">
        <i class="fa-solid fa-bar-chart"></i> <span>PURCHASE AMOUNT (IDR) BY PRODUCT FAMILY</span>
    </div>

    <div class="filter-bar">
        <form id="form_purchase_by_family" method="POST">
            <div class="pill-group">
                <button type="button" class="pill-btn" onclick="togglePill(this, 'daily', 'form_purchase_by_family')"><i class="fa fa-calendar-day"></i> Daily</button>
                <button type="button" class="pill-btn" onclick="togglePill(this, 'weekly', 'form_purchase_by_family')"><i class="fa fa-calendar-week"></i> Weekly</button>
                <button type="button" class="pill-btn" onclick="togglePill(this, 'monthly', 'form_purchase_by_family')"><i class="fa fa-calendar"></i> Monthly</button>
                <button type="button" class="pill-btn" onclick="togglePill(this, 'yearly', 'form_purchase_by_family')"><i class="fa fa-calendar"></i> Yearly</button>

                <input id="filter_supplier_id" name="filter_supplier_id" class="easyui-combobox" style="width:150px; height:32px;" prompt="Supplier">
                <input id="filter_division" name="filter_division" class="easyui-combobox" style="width:150px; height:32px;" prompt="Division">
                
            </div>
        </form>

        <a href="javascript:;" class="easyui-linkbutton" onclick="submitFilter('form_purchase_by_family')" data-options="iconCls:'icon-search'" style="height:32px; padding:0 15px;">Filter</a>
        <a href="javascript:;" class="easyui-linkbutton" onclick="reload()" data-options="iconCls:'icon-reload'" style="height:32px; padding:0 15px;">Reload</a>
    </div>

    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
        <div class="chart-section" style="flex: 1;">
            <div class="chart-header">CHILD PART</div>
            <div style="padding: 10px; height: 300px;">
                <div id="childPartChart"></div>
            </div>
        </div>
        <div class="chart-section" style="flex: 1;">
            <div class="chart-header">VIRGIN</div>
            <div style="padding: 10px; height: 300px;">
                <div id="virginChart"></div>
            </div>
        </div>
        <div class="chart-section" style="flex: 1;">
            <div class="chart-header">CONSUMABLE</div>
            <div style="padding: 10px; height: 300px;">
                <div id="consumableChart"></div>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 15px;">
        <div class="chart-section" style="flex: 1;">
            <div class="chart-header">MASTER BATCH</div>
            <div style="padding: 10px; height: 300px;">
                <div id="masterBatchChart"></div>
            </div>
        </div>
        <div class="chart-section" style="flex: 1;">
            <div class="chart-header">STAMPING</div>
            <div style="padding: 10px; height: 300px;">
                <div id="stampingChart"></div>
            </div>
        </div>
        <div class="chart-section" style="flex: 1;">
            <div class="chart-header">SUBCONT</div>
            <div style="padding: 10px; height: 300px;">
                <div id="subcontChart"></div>
            </div>
        </div>
    </div>

    <div class="section-header">
        <i class="fa-solid fa-files-o"></i> <span>CONCLUSION AND IMPACT</span>
    </div>

    <div style="display: flex; gap: 15px;">
        <div class="chart-section" style="flex: 1; width: 50%;">
            <div class="chart-header">CONCLUSION</div>
            <div style="padding: 20px; overflow-y: auto;">
                <div id="conclusion" style="min-height: 100px;">
                    &nbsp;
                </div>
            </div>
        </div>

        <div class="chart-section" style="flex: 1; width: 50%;">
            <div class="chart-header">IMPACT</div>
            <div style="padding: 20px; overflow-y: auto;">
                <div id="impact" style="min-height: 100px;">
                    &nbsp;
                </div>
            </div>
        </div>
    </div>
    
</div>


<script>
function reload() {
    window.location.reload();
}

function pdf() {
    $("#printout").get(0).contentWindow.print();
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
    // show on load
    submitFilter('form_purchase_trends');
    submitFilter('form_plan_vs_actual');
    submitFilter('form_purchase_by_family');
    
    $('#filter_division').combobox({
        url: '<?= base_url('finance/purchase_report/readsDivision/'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: '<Division> ALL',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $("#filter_category_id").combobox({
        url: '<?= base_url('master/item_categories/readsnotfg') ?>',
        valueField: 'id',
        textField: 'name',
        prompt: "Select Categories",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $("#filter_supplier_id").combobox({
        url: '<?= base_url('master/suppliers/reads') ?>',
        valueField: 'id',
        textField: 'name',
        prompt: "<Supplier> ALL",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }]
    });
    
});

// Button Period
function togglePill(btn, type, formId) {
    if (window.event) window.event.preventDefault();

    const $form = $('#' + formId);
    const $group = $form.find('.pill-group');

    // Reset state tombol hanya di dalam form ini
    $group.find('.pill-btn').removeClass('active');
    $(btn).addClass('active');

    // Bersihkan filter lama di dalam form ini
    $group.find('.floating-filter').remove();

    if (type === 'all') return;

    // Buat wrapper filter baru
    const $wrapper = $('<div class="floating-filter"></div>');
    $group.append($wrapper);

    // Inisialisasi EasyUI berdasarkan tipe
    if (type === 'daily') {
        $wrapper.html('<input class="current-period-input easyui-datebox" style="width:150px; height:32px;">');
        $wrapper.find('.easyui-datebox').datebox({
            editable: false,
            value: '<?= date("Y-m-d") ?>'
        });
    } 
    else if (type === 'weekly') {
        $wrapper.html('<input class="current-period-input easyui-combobox" style="width:280px; height:32px;">');
        $wrapper.find('.easyui-combobox').combobox({
            url: '<?= base_url("purchase/purchase_dashboard/get_iso_weeks") ?>',
            valueField: 'id',
            textField: 'text',
            editable: false
        });
    } 
    else if (type === 'monthly') {
        $wrapper.html('<input class="current-period-input easyui-combobox" style="width:180px; height:32px;">');
        $wrapper.find('.easyui-combobox').combobox({
            valueField: 'id',
            textField: 'text',
            editable: false,
            panelHeight: 'auto',
            data: [
                <?php for($m=1; $m<=12; $m++): ?>
                { id: '<?= date("Y-").sprintf("%02d", $m) ?>', text: '<?= date("F Y", mktime(0,0,0,$m, 1)) ?>' },
                <?php endfor; ?>
            ],
            onLoadSuccess: function() {
                $(this).combobox('setValue', '<?= date("Y-m") ?>');
            }
        });
    } 
    else if (type === 'yearly') {
        $wrapper.html('<input class="current-period-input easyui-combobox" style="width:180px; height:32px;">');
        $wrapper.find('.easyui-combobox').combobox({
            url: '<?= base_url("purchase/purchase_dashboard/get_years") ?>',
            valueField: 'id',
            textField: 'text',
            editable: false,
            panelHeight: 'auto',
            onLoadSuccess: function() {
                const currentYear = new Date().getFullYear().toString();
                $(this).combobox('setValue', currentYear);
            },
        });
    }
}

// Fungsi Full Screen
function toggleFullScreen(id) {
    const el = document.getElementById(id);
    if (!document.fullscreenElement) {
        if (el.requestFullscreen) {
            el.requestFullscreen();
        } else if (el.webkitRequestFullscreen) { /* Safari */
            el.webkitRequestFullscreen();
        }
        // Pastikan background tetap putih saat fullscreen
        el.classList.add('is-fullscreen');
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
        el.classList.remove('is-fullscreen');
    }
}

// Fungsi Print Khusus Elemen
function printSpecificChart(id) {
    const el = document.getElementById(id);
    document.body.classList.add('printing-chart');
    el.classList.add('printable-area');

    window.print();

    // Hapus kembali setelah dialog print selesai (tertutup)
    document.body.classList.remove('printing-chart');
    el.classList.remove('printable-area');
}

// Fungsi Export Excel per Chart
function exportToExcel(chartId) {
    if (typeof XLSX === 'undefined') {
        alert('The XLSX library has not been loaded. Please check your internet connection or CDN.');
        return;
    }

    const chart = ApexCharts.getChartByID(chartId);
    let currentChart = chart;

    if (!currentChart || !currentChart.w) {
        alert('Data chart not found.');
        return;
    }

    // Get Kategori (Labels) dari Globals
    // Menggunakan labels yang sudah ter-render di sumbu X
    const categories = currentChart.w.globals.labels;

    // console.log(currentChart.w.globals);

    // Get Data (Values) dari Globals
    // globals.series berisi array data murni (tanpa objek name)
    // globals.seriesNames berisi nama-nama seriesnya
    const seriesData = currentChart.w.globals.series;
    const seriesNames = currentChart.w.globals.seriesNames;

    let dataRows = [];
    
    // Susun Header
    let header = ["Category"];
    seriesNames.forEach(name => {
        header.push(name);
    });
    dataRows.push(header);

    // Susun Baris Data
    categories.forEach((cat, index) => {
        // Gabungkan label jika itu array (seperti pada Weekly)
        let label = Array.isArray(cat) ? cat.join(" ") : cat;
        let row = [label];
        
        // Get nilai dari setiap series di index yang sama
        seriesData.forEach(dataArray => {
            row.push(dataArray[index] || 0);
        });
        
        dataRows.push(row);
    });

    // Generate dan Download Excel
    const worksheet = XLSX.utils.aoa_to_sheet(dataRows);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet1");

    const fileName = "Export_" + chartId + "_" + new Date().getTime() + ".xlsx";
    XLSX.writeFile(workbook, fileName);
}



/** ---- CHART ---- */

var myPurchaseChart;
var mySupplierChart;
var myPlanActualChart;

// Gunakan object untuk menyimpan instance multiple chart
let chartInstances = {};


// Chart Purchase 
function updateTrendChart(labels, values, period, title, avgValues) {
    // Get rata-rata dari array
    const averageValue = avgValues.length > 0 ? avgValues[0] : 0;

    const options = {
        // Get dua series agar muncul dua item di Legend
        series: [
            {
                name: 'Purchase Amount',
                type: 'bar',
                data: values,
            },
            {
                name: 'Average',
                type: 'line',
                data: avgValues,
            }
        ],
        chart: {
            id: 'purchaseChart',
            height: '100%',
            type: 'line',
            toolbar: {
                show: true,
                tools: {
                    download: true, // Tombol download untuk PNG, SVG, CSV
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false,
                    customIcons: [] // Kosongkan ini agar tombol kotak hilang
                }
            },
        },
        stroke: {
            width: [0, 3],
            curve: 'smooth',
            dashArray: [0, 5],
        },
        colors: ['#0000FF', '#FF4560'], // Biru untuk Bar, Merah untuk Average
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: '45%',
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            enabledOnSeries: [0], // Hanya tampilkan angka di atas BAR (series index 0)
            formatter: function (val) {
                return val.toLocaleString('id-ID');
            },
            offsetY: -20,
            style: { fontSize: '11px', colors: ["#304758"], fontWeight: 'bold' }
        },
        legend: {
            show: true,
            position: 'bottom',
            horizontalAlign: 'center',
            offsetY: 8,
            itemMargin: { horizontal: 15, vertical: 5 },
            markers: {
                width: 12,
                height: 12,
                radius: 2,
            },
        },
        title: {
            text: title,
            align: 'center',
            style: { fontSize: '18px', color: '#444' }
        },
        subtitle: {
            text: period,
            align: 'center',
            style: { fontSize: '13px', color: '#707070' }
        },
        xaxis: {
            categories: labels,
            labels: {
                show: true,
                style: {
                    fontSize: '11px',
                    cssClass: 'apexcharts-xaxis-label',
                },
                hideOverlappingLabels: false,
                trim: false,
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            show: false, // Sesuai UI
        },
        annotations: {
            yaxis: [{
                y: averageValue,
                borderColor: 'transparent',
                label: {
                    borderColor: '#FF4560',
                    style: { color: '#fff', background: '#FF4560', fontWeight: 'bold' },
                    text: 'Avg: ' + averageValue.toLocaleString('id-ID'),
                    position: 'right',
                    dx: -10
                }
            }]
        },
        tooltip: {
            shared: true, // saat hover muncul info Bar & Average sekaligus
            intersect: false,
            y: {
                formatter: function (val) {
                    return "Rp " + val.toLocaleString('id-ID');
                }
            }
        }
    };

    if (myPurchaseChart) {
        myPurchaseChart.updateOptions(options);
    } else {
        myPurchaseChart = new ApexCharts(document.querySelector("#purchaseChart"), options);
        myPurchaseChart.render();
    }
}


// Supplier Bar Chart
function updateSupplierChart(labels, values, period, title) {
    const safeLabels = (labels && labels.length > 0) ? labels : ['No Data'];
    const safeValues = (values && values.length > 0) ? values : [0];

    const options = {
        series: [{
            name: 'Purchase per Supplier',
            data: safeValues
        }],
        chart: {
            type: 'bar',
            height: '100%',
            toolbar: {
                show: true,
                tools: {
                    download: true, // Tombol download untuk PNG, SVG, CSV
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false,
                }
            },
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true,
                barHeight: '60%',
                dataLabels: { position: 'top' }
            }
        },
        colors: ['#36a2eb'],
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val > 0 ? "Rp " + val.toLocaleString('id-ID') : "";
            },
            offsetX: 80, // Sesuaikan agar label nominal tidak menabrak bar
            style: {
                fontSize: '11px',
                colors: ["#333"]
            }
        },
        xaxis: {
            categories: safeLabels,
            labels: { show: false },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        grid: {
            padding: {
                right: 100, 
            }
        },
        title: {
            text: title,
            align: 'center',
            style: { fontSize: '18px', color: '#444' }
        },
        subtitle: {
            text: period,
            align: 'center',
            style: { fontSize: '13px', color: '#707070' }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return "Rp " + val.toLocaleString('id-ID');
                }
            }
        }
    };

    const chartElement = document.querySelector("#supplierChart");
    
    // Logic untuk destroy dan create ulang (Re-render)
    if (mySupplierChart) {
        mySupplierChart.updateOptions(options);
        mySupplierChart.updateSeries([{ data: safeValues }]);
    } else {
        mySupplierChart = new ApexCharts(document.querySelector("#supplierChart"), options);
        mySupplierChart.render();
    }
}


// Plan VS Actual Chart
function updatePlanActualChart(labels, planValues, period, title, actualValues) {
    const options = {
        series: [
            {
                name: 'Plan',
                data: planValues
            },
            {
                name: 'Actual',
                data: actualValues
            }
        ],
        chart: {
            id: 'planActualChart', // ID unik untuk export
            height: '100%',
            type: 'bar', // Gunakan bar untuk perbandingan side-by-side
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false
                }
            }
        },
        colors: ['#0000FF', '#ed533b'], // Biru untuk Plan, Merah-Orange untuk Actual
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 4,
                dataLabels: {
                    position: 'top', // Nilai di atas batang
                }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val.toLocaleString('id-ID');
            },
            offsetY: -20,
            style: {
                fontSize: '10px',
                colors: ["#304758"]
            }
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: labels,
            labels: {
                style: {
                    fontSize: '11px'
                }
            }
        },
        yaxis: {
            show: false, // Sesuai UI
        },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            offsetY: 8
        },
        title: {
            text: title,
            align: 'center',
            style: { fontSize: '18px', color: '#444' }
        },
        subtitle: {
            text: period,
            align: 'center',
            style: { fontSize: '13px', color: '#707070' }
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: {
                formatter: function (val) {
                    return val.toLocaleString('id-ID');
                }
            }
        }
    };

    if (myPlanActualChart) {
        myPlanActualChart.updateOptions(options);
    } else {
        myPlanActualChart = new ApexCharts(document.querySelector("#planActualChart"), options);
        myPlanActualChart.render();
    }
}

// Global Function Chart per Item Family
function createFamilyApexChart(containerId, period, title, labels, dataPlan, dataActual) {
    const options = {
        series: [
            { name: 'Plan', data: dataPlan },
            { name: 'Actual', data: dataActual }
        ],
        chart: {
            type: 'bar',
            height: '100%',
            toolbar: {
                show: true,
                tools: {
                    download: true, // Tombol download untuk PNG, SVG, CSV
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false,
                    customIcons: [] // Kosongkan ini agar tombol kotak hilang
                }
            },
            parentHeightOffset: 0
        },
        colors: ['#244b82', '#ed533b'], // Biru Navy & Merah Orange
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '60%',
                borderRadius: 2,
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: (val) => val > 0 ? val.toLocaleString('id-ID') : '',
            offsetY: -20,
            style: { fontSize: '9px', colors: ["#304758"] }
        },
        title: {
            text: title,
            align: 'center',
            style: { fontSize: '18px', color: '#444' }
        },
        subtitle: {
            text: period,
            align: 'center',
            style: { fontSize: '13px', color: '#707070' }
        },
        xaxis: {
            categories: labels,
            labels: {
                rotate: -45, // Rotasi label agar tidak tabrakan tanpa overflow
                rotateAlways: false,
                hideOverlappingLabels: true, // Sembunyikan jika terlalu rapat
                style: { fontSize: '10px' }
            }
        },
        yaxis: {
            show: false, // Sesuai UI
        },
        legend: { position: 'bottom', fontSize: '11px' },
        grid: { padding: { top: 20 } }, // Ruang untuk dataLabels di atas bar
        tooltip: {
            shared: true,
            intersect: false,
            y: { formatter: (val) => val.toLocaleString('id-ID') + " unit" }
        }
    };

    // Logic Re-render: Jika sudah ada, update. Jika belum, create.
    if (chartInstances[containerId]) {
        chartInstances[containerId].updateOptions({
            series: options.series,
            xaxis: { categories: labels }
        });
    } else {
        chartInstances[containerId] = new ApexCharts(document.querySelector("#" + containerId), options);
        chartInstances[containerId].render();
    }
}


function submitFilter(formId) {
    const $form = $('#' + formId);
    let payload = {};

    $form.serializeArray().forEach(item => payload[item.name] = item.value);

    let periodType = $form.find('.pill-btn.active').text().trim().toLowerCase() || 'daily';
    payload.filter_period_type = periodType;

    const $input = $form.find('.current-period-input');
    let periodValue = $input.length ? $input.combo('getValue') : '';
    
    if (!periodValue && periodType === 'daily') {
        periodValue = new Date().toISOString().split('T')[0];
    }
    payload.filter_period_value = periodValue;

    // --- Pemisahan Request ---
    if (formId === 'form_purchase_trends') {
        // Hanya update grafik Purchase Trends & Supplier
        $.post('<?= base_url("purchase/purchase_dashboard/get_dashboard_data") ?>', payload, function(res) {
            const data = JSON.parse(res);

            updateTrendChart(data.trend_labels, data.trend_values, data.period, data.title, data.avg_values);
            updateSupplierChart(data.supplier_labels, data.supplier_values, data.period, data.title);

            if(data.conclusion) $('#conclusion').html(data.conclusion);
            if(data.impact) $('#impact').html(data.impact);
        });

    } else if (formId === 'form_plan_vs_actual') {
        // Hanya update grafik Plan VS Actual
        $.post('<?= base_url("purchase/purchase_dashboard/get_plan_actual_data") ?>', payload, function(res) {
            const data = JSON.parse(res);
            updatePlanActualChart(data.labels, data.plan_values, data.period, data.title, data.actual_values);
        });

    } else if (formId === 'form_purchase_by_family') {
        // Hanya update grafik Purchase by Product Family
        $.post('<?= base_url("purchase/purchase_dashboard/get_purchase_by_family_data") ?>', payload, function(res) {
            const data = JSON.parse(res);
            
            const families = {
                'childPartChart': 'child_part',
                'virginChart': 'virgin',
                'consumableChart': 'consumable',
                'masterBatchChart': 'master_batch',
                'stampingChart': 'stamping',
                'subcontChart': 'subcont'
            };

            Object.keys(families).forEach(id => {
                const key = families[id];
                createFamilyApexChart(
                    id, 
                    data.period, 
                    data.title, 
                    data.labels, 
                    data[key + '_plan'], 
                    data[key + '_actual']
                );
            });
        });
    }
}
</script>

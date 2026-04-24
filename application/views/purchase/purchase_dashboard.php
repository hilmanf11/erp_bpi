<div class="dashboard-wrapper">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
    </style>

    <div class="section-header">
        <i class="fa-solid fa-square-poll-vertical"></i> <span>FILTER & SUMMARY</span>
    </div>

    <div class="filter-bar">
        <div class="pill-group">
            <button class="pill-btn active" onclick="togglePill(this, 'all')"><i class="fa fa-list"></i> All</button>
            <button class="pill-btn" onclick="togglePill(this, 'daily')"><i class="fa fa-calendar-day"></i> Daily</button>
            <button class="pill-btn" onclick="togglePill(this, 'weekly')"><i class="fa fa-calendar-week"></i> Weekly</button>
            <button class="pill-btn" onclick="togglePill(this, 'monthly')"><i class="fa fa-calendar"></i> Monthly</button>
        </div>

        <div id="dynamic_input" style="display: flex; gap: 8px;">
            
            <select id="filter_display" class="easyui-combobox" style="width:120px; height:32px;">
                <option value="DAILY">DAILY</option>
                <option value="WEEKLY">WEEKLY</option>
                <option value="MONTHLY">MONTHLY</option>
            </select>
            
            <input id="filter_from" class="easyui-datebox" style="width:130px; height:32px;" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            <span>to</span>
            <input id="filter_to" class="easyui-datebox" style="width:130px; height:32px;" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">


            <input id="filter_supplier_id" class="easyui-combobox" style="width:150px; height:32px;" prompt="Supplier">
            <input id="filter_division" class="easyui-combobox" style="width:150px; height:32px;" prompt="Division">
            
            <div class="fitem" hidden>
            <input id="filter_category_id" class="easyui-combobox" style="width:150px; height:32px;" prompt="Category">
            </div>

        </div>

        <a href="javascript:;" class="easyui-linkbutton" onclick="loadDashboard()" data-options="iconCls:'icon-search'" style="height:32px; padding:0 15px;">Filter</a>
        <a href="javascript:;" class="easyui-linkbutton" onclick="reload()" data-options="iconCls:'icon-reload'" style="height:32px; padding:0 15px;">Reload</a>
    </div>

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

    <div class="section-header">
        <i class="fa-solid fa-chart-line"></i> <span>PURCHASE TRENDS</span>
    </div>

    <div style="display: flex; gap: 15px;">
        <div class="chart-section" style="flex: 1; width: 50%;">
            <div class="chart-header">Purchase by Amount</div>
            <div style="padding: 20px; overflow-x: auto;">
                <div id="purchaseChartParent" style="min-width: 1000px; height: 500px;">
                    <canvas id="purchaseChart"></canvas>
                </div>
            </div>
        </div>

        <div class="chart-section" style="flex: 1; width: 50%;">
            <div class="chart-header">Purchase by Supplier</div>
            <div style="padding: 20px; overflow-y: auto; max-height: 500px;">
                <div id="supplierChartParent" style="height: 800px;">
                    <canvas id="supplierChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="section-header">
        <i class="fa-solid fa-bar-chart"></i> <span>PURCHASE PLAN VS ACTUAL</span>
    </div>

    <div class="chart-section" style="width: 100%;">
        <div class="chart-header">Purchase Plan VS Actual by QTY</div>
        <div style="padding: 20px; overflow-x: auto;">
            <div id="planActualChartParent" style="min-width: 1000px; height: 250px;">
                <canvas id="planActualChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ITEM FAMILY -->
    <div style="display: flex; gap: 15px;">
        <div class="chart-section" style="flex: 1; width: 35%;">
            <div class="chart-header">
                CHILD PART
            </div>
            <div style="padding: 20px; overflow-x: auto;">
                <div id="childPartChartParent" style="min-width: 1000px; height: 250px;">
                    <canvas id="childPartChart"></canvas>
                </div>
            </div>
        </div>

        <div class="chart-section" style="flex: 1; width: 35%;">
            <div class="chart-header">
                VIRGIN
            </div>
            <div style="padding: 20px; overflow-x: auto;">
                <div id="virginChartParent" style="min-width: 1000px; height: 250px;">
                    <canvas id="virginChart"></canvas>
                </div>
            </div>
        </div>

        <div class="chart-section" style="flex: 1; width: 35%;">
            <div class="chart-header">
                CONSUMABLE
            </div>
            <div style="padding: 20px; overflow-x: auto;">
                <div id="consumableChartParent" style="min-width: 1000px; height: 250px;">
                    <canvas id="consumableChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 15px;">
        <div class="chart-section" style="flex: 1; width: 35%;">
            <div class="chart-header">
                MASTER BATCH
            </div>
            <div style="padding: 20px; overflow-x: auto;">
                <div id="masterBatchChartParent" style="min-width: 1000px; height: 250px;">
                    <canvas id="masterBatchChart"></canvas>
                </div>
            </div>
        </div>

        <div class="chart-section" style="flex: 1; width: 35%;">
            <div class="chart-header">
                STAMPING
            </div>
            <div style="padding: 20px; overflow-x: auto;">
                <div id="stampingChartParent" style="min-width: 1000px; height: 250px;">
                    <canvas id="stampingChart"></canvas>
                </div>
            </div>
        </div>

        <div class="chart-section" style="flex: 1; width: 35%;">
            <div class="chart-header">
                SUBCONT
            </div>
            <div style="padding: 20px; overflow-x: auto;">
                <div id="subcontChartParent" style="min-width: 1000px; height: 250px;">
                    <canvas id="subcontChart"></canvas>
                </div>
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
    loadDashboard();
    
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





/** ---- CHART ---- */

var myPurchaseChart;
var mySupplierChart;

// Gunakan object untuk menyimpan instance multiple chart
let chartInstances = {};

function loadDashboard() {
    const params = {
        from: $('#filter_from').datebox('getValue'),
        to: $('#filter_to').datebox('getValue'),
        display: $('#filter_display').combobox('getValue'),
        division: $('#filter_division').combobox('getValue'),
        supplier_id: $('#filter_supplier_id').combobox('getValue'),
        category_id: $('#filter_category_id').combobox('getValue'),
    };

    $.post('<?= base_url("purchase/purchase_dashboard/get_dashboard_data") ?>', params, function(res) {
        const data = JSON.parse(res);

        // Update KPI
        $('#kpi_total_amt').html(data.total_amount_formatted);
        $('#kpi_total_po').html(data.total_po);
        $('#kpi_total_supp').html(data.supp_labels.length); // Menghitung jumlah supplier unik dari data labels

        // Render/Update Chart Purchase
        updateTrendChart(data.trend_labels, data.trend_values, data.subtitle, data.avg_values);

        // Render/Update Chart Supplier
        updateSupplierChart(data.supp_labels, data.supp_values, data.subtitle);

        // Conclusion and Impact
        if (data.conclusion) {
            $('#conclusion').html(data.conclusion);
        } else {
            $('#conclusion').html('<span class="text-muted">Not available yet.</span>');
        }

        if (data.impact) {
            $('#impact').html(data.impact);
        } else {
            $('#impact').html('<span class="text-muted">Not available yet.</span>');
        }

    });


    $.post('<?= base_url("purchase/purchase_dashboard/get_plan_actual_data") ?>', params, function(res) {
        const data = JSON.parse(res);

        // Set ID Canvas dan Key Data dari Response
        const chartMapping = {
            'planActualChart': ['qty_plan', 'qty_actual'],
            'childPartChart': ['child_part_plan', 'child_part_actual'],
            'virginChart': ['virgin_plan', 'virgin_actual'],
            'consumableChart': ['consumable_plan', 'consumable_actual'],
            'masterBatchChart': ['master_batch_plan', 'master_batch_actual'],
            'stampingChart': ['stamping_plan', 'stamping_actual'],
            'subcontChart': ['subcont_plan', 'subcont_actual']
        };

        // Mapping Chart with Loop
        Object.keys(chartMapping).forEach(canvasId => {
            const [planKey, actualKey] = chartMapping[canvasId];
            createPlanActualChart(
                canvasId, 
                data.period, 
                data.week_labels, 
                data[planKey], 
                data[actualKey]
            );
        });
    });
}


// Chart Purchase 
function updateTrendChart_purchase_only(labels, values, subtitle) {
    const ctx = document.getElementById('purchaseChart').getContext('2d');

    // Jika data lebih dari 15, lebarkan bar
    const parent = document.getElementById('purchaseChartParent');
    if (labels.length > 15) {
        parent.style.minWidth = (labels.length * 50) + "px"; 
    } else {
        parent.style.minWidth = "100%";
    }
    
    // Pastikan destroy chart sebelumnya agar tidak tumpang tindih saat update
    if (window.myPurchaseChart) {
        window.myPurchaseChart.destroy();
    }
    
    window.myPurchaseChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Purchase Amount',
                data: values,
                backgroundColor: '#3498db',
                borderColor: '#2980b9',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        // Format angka ke format ribuan agar rapi
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: subtitle,
                    padding: {
                        bottom: 25,
                    }
                },
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        padding: 20,
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Amount: Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            animation: {
                // Menggunakan onProgress agar angka mengikuti pergerakan bar
                onProgress: function() {
                    var chartInstance = this;
                    var ctx = chartInstance.ctx;
                    ctx.font = "bold 10px Arial";
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillStyle = '#333';

                    this.data.datasets.forEach(function(dataset, i) {
                        var meta = chartInstance.getDatasetMeta(i);
                        meta.data.forEach(function(bar, index) {
                            var data = dataset.data[index];
                            if (data !== null && !bar.hidden) {
                                // bar.y adalah koordinat ujung atas bar
                                ctx.fillText(data.toLocaleString('id-ID'), bar.x, bar.y - 5);
                            }
                        });
                    });
                }
            }
        }
    });
}


// Chart Purchase Stacked
function updateTrendChart(labels, values, avgValues, subtitle) {
    const ctx = document.getElementById('purchaseChart').getContext('2d');
    
    if (window.myPurchaseChart) {
        window.myPurchaseChart.destroy();
    }

    window.myPurchaseChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Purchase',
                    data: values,
                    backgroundColor: '#3b6ccf', // Warna biru sesuai gambar
                    stack: 'Stack 0',
                },
                {
                    label: 'AVG Period',
                    data: avgValues,
                    backgroundColor: '#92d050', // Warna hijau muda sesuai gambar
                    stack: 'Stack 0',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    stacked: true, // Aktifkan stacking
                },
                y: {
                    stacked: true, // Aktifkan stacking
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => 'Rp ' + value.toLocaleString('id-ID')
                    }
                }
            },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            animation: {
                onProgress: function() {
                    var chartInstance = this;
                    var ctx = chartInstance.ctx;
                    ctx.font = "bold 9px Arial";
                    ctx.textAlign = 'center';
                    ctx.fillStyle = '#fcffcd';

                    this.data.datasets.forEach(function(dataset, i) {
                        var meta = chartInstance.getDatasetMeta(i);
                        meta.data.forEach(function(bar, index) {
                            var data = dataset.data[index];
                            if (data > 0) {
                                // Menghitung posisi teks di tengah-tengah setiap bagian stack
                                ctx.fillText(data.toLocaleString('id-ID'), bar.x, bar.y + (bar.height / 2));
                            }
                        });
                    });
                }
            }
        }
    });
}


// Supplier Bar Chart
function updateSupplierChart(labels, values, subtitle) {
    const ctx = document.getElementById('supplierChart').getContext('2d');
    if (mySupplierChart) mySupplierChart.destroy();

    mySupplierChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Amount',
                data: values,
                backgroundColor: '#36a2eb',
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    right: 70 
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: subtitle,
                    padding: {
                        bottom: 25,
                    }
                },
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { display: false },
                    ticks: { display: false } 
                }
            },
            // Tampilkan angka di ujung bar
            animation: {
                onComplete: function() {
                    var chartInstance = this,
                        ctx = chartInstance.ctx;
                    ctx.font = Chart.helpers.fontString(Chart.defaults.font.size, 'bold', Chart.defaults.font.family);
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#333';

                    this.data.datasets.forEach(function(dataset, i) {
                        var meta = chartInstance.getDatasetMeta(i);
                        meta.data.forEach(function(bar, index) {
                            var data = dataset.data[index];
                            // Format angka ke IDR (Rp 1.000.000)
                            var label = 'Rp ' + data.toLocaleString('id-ID');
                            // Posisi: tepat di sebelah kanan bar
                            ctx.fillText(label, bar.x + 5, bar.y);
                        });
                    });
                }
            }
        }
    });
}

// Plan VS Actual Chart
function createPlanActualChart(canvasId, period, labels, dataPlan, dataActual) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return; // Guard clause jika ID tidak ditemukan

    const ctx = canvas.getContext('2d');

    // Pastikan destroy chart sebelumnya agar tidak tumpang tindih saat update
    if (chartInstances[canvasId]) {
        chartInstances[canvasId].destroy();
    }

    chartInstances[canvasId] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Plan',
                    data: dataPlan,
                    backgroundColor: '#5376af',
                    borderWidth: 1
                },
                {
                    label: 'Actual',
                    data: dataActual,
                    backgroundColor: '#395279',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { 
                padding: { top: 10, bottom: 10 } 
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { 
                        display: true,
                        font: { size: 10 }
                    }
                },
                y: {
                    grace: '15%',
                }
            },
            plugins: {
                tooltip: { enabled: true },
                title: {
                    display: true,
                    text: period,
                    padding: { bottom: 30 }
                },
                legend: {
                    display: true,
                    position: 'bottom'
                }
            },
            animation: {
                // Menggunakan onProgress agar angka mengikuti pergerakan bar
                onProgress: function() {
                    var chartInstance = this;
                    var ctx = chartInstance.ctx;
                    ctx.font = "bold 10px Arial";
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillStyle = '#333';

                    this.data.datasets.forEach(function(dataset, i) {
                        var meta = chartInstance.getDatasetMeta(i);
                        meta.data.forEach(function(bar, index) {
                            var data = dataset.data[index];
                            if (data !== null && !bar.hidden) {
                                // bar.y adalah koordinat ujung atas bar
                                ctx.fillText(data.toLocaleString('id-ID'), bar.x, bar.y - 5);
                            }
                        });
                    });
                }
            }
        }
    });

    return chartInstances[canvasId];
}

</script>

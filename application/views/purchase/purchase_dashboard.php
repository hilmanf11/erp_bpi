<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .dashboard-wrapper { background-color: #f0f2f5; padding: 15px; font-family: 'Inter', sans-serif; }
    
    /* Section Header */
    .section-title { 
        display: flex; align-items: center; gap: 8px;
        font-size: 14px; font-weight: 700; color: #475569;
        margin-bottom: 12px; border-left: 4px solid #3b82f6; padding-left: 10px;
    }

    /* Filter Bar dengan Pill Buttons */
    .filter-container {
        background: white; border-radius: 10px; padding: 12px 20px;
        display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
        margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    /* KPI Cards Grid */
    .kpi-grid { 
        display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 15px; margin-bottom: 20px; 
    }
    .kpi-card {
        padding: 20px; border-radius: 12px; color: white; position: relative; overflow: hidden;
        min-height: 100px; display: flex; flex-direction: column; justify-content: center;
    }
    .kpi-card i { position: absolute; right: -10px; bottom: -10px; font-size: 80px; opacity: 0.15; }
    .kpi-label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; }
    .kpi-value { font-size: 24px; font-weight: 800; margin-top: 5px; }

    /* Chart Container */
    .chart-section {
        background: white; border-radius: 12px; padding: 0; overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;
    }
    .chart-header {
        background: #244b82; color: white; padding: 10px 20px;
        font-size: 13px; font-weight: 600; text-align: center;
    }

    /* Warna KPI Card */
    .bg-blue { background: linear-gradient(135deg, #244b82 0%, #729dcb 100%); }
    .bg-green { background: linear-gradient(135deg, #508758 0%, #88c396 100%); }
    .bg-red { background: linear-gradient(135deg, #d15e29 0%, #feae88 100%); }
</style>



<div class="dashboard-wrapper">
    
    <div class="section-title"><i class="fa fa-search"></i> FILTER & SUMMARY</div>

    <div class="filter-container">
        <div style="flex: 1; display: flex; gap: 10px; align-items: center;">
            <select id="filter_display" class="easyui-combobox" style="width:120px; height:32px;">
                <option value="DAILY">DAILY</option>
                <option value="WEEKLY">WEEKLY</option>
                <option value="WEEKLY_ISO8601">WEEKLY (ISO-8601)</option>
                <option value="MONTHLY">MONTHLY</option>
            </select>
            
            <input id="filter_from" class="easyui-datebox" style="width:130px; height:32px;" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            <span>to</span>
            <input id="filter_to" class="easyui-datebox" style="width:130px; height:32px;" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">


            <input id="filter_supplier_id" class="easyui-combobox" style="width:150px; height:32px;" prompt="Supplier">
            <input id="filter_division" class="easyui-combobox" style="width:150px; height:32px;" prompt="Division">
            <input id="filter_category_id" class="easyui-combobox" style="width:150px; height:32px;" prompt="Category">

        </div>
        <a href="javascript:;" class="easyui-linkbutton" onclick="loadDashboard()" data-options="iconCls:'icon-search'" style="height:32px; padding:0 15px;">Filter</a>
        <a href="javascript:;" class="easyui-linkbutton" onclick="reload()" data-options="iconCls:'icon-reload'" style="height:32px; padding:0 15px;">Reload</a>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card bg-blue">
            <div class="kpi-label">Total Purchase Amount</div>
            <div id="kpi_total_amt" class="kpi-value">Rp 0</div>
            <i class="fa fa-wallet"></i>
        </div>
        <div class="kpi-card bg-green">
            <div class="kpi-label">Total PO Issued</div>
            <div id="kpi_total_po" class="kpi-value">0</div>
            <i class="fa fa-file-invoice"></i>
        </div>
        <div class="kpi-card bg-red">
            <div class="kpi-label">Active Suppliers</div>
            <div id="kpi_total_supp" class="kpi-value">0</div>
            <i class="fa fa-truck"></i>
        </div>
    </div>

    <div class="section-title"><i class="fa fa-chart-line"></i> PURCHASE ANALISYS</div>

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
        prompt: 'Choose Division',
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
        prompt: "Select Supplier",
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
        updateTrendChart(data.trend_labels, data.trend_values);

        // Render/Update Chart Supplier
        updateSupplierChart(data.supp_labels, data.supp_values);
    });
}

// Chart Purchase Stacked
function updateTrendChart(labels, values) {
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
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Amount: Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
}


// Supplier Bar Chart
function updateSupplierChart(labels, values) {
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
</script>

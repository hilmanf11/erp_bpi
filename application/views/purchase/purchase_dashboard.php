<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .dashboard-wrapper { background-color: #f8fafc; padding: 20px; font-family: 'Inter', sans-serif; }
    
    .dashboard-card { 
        background: white; border-radius: 12px; border: none;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        margin-bottom: 20px;
    }

    /* Horizontal Filter Layout */
    .filter-row { 
        display: flex; 
        flex-wrap: nowrap; 
        align-items: flex-end; 
        gap: 12px; 
        padding: 20px; 
        overflow-x: auto; /* Antisipasi jika layar kecil */
    }

    .fitem-horizontal { 
        flex: 1; 
        min-width: 150px; 
    }

    .fitem-horizontal span { 
        display: block !important; 
        font-size: 11px; 
        font-weight: 700; 
        color: #64748b; 
        margin-bottom: 6px;
        text-transform: uppercase;
    }

    /* KPI Single Style */
    .kpi-single {
        background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
        padding: 20px 30px;
        border-radius: 12px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
    }
</style>



<div class="dashboard-wrapper">

    <div class="dashboard-card">
        <div class="filter-row">
            <div class="fitem-horizontal">
                <span>Display</span>
                <select id="filter_display" class="easyui-combobox" style="width:100%; height:38px;" panelHeight="auto">
                    <option value="DAILY">DAILY</option>
                    <option value="WEEKLY">WEEKLY</option>
                    <option value="MONTHLY">MONTHLY</option>
                    <option value="YEARLY">YEARLY</option>
                </select>
            </div>
            
            <div class="fitem-horizontal">
                <span>From</span>
                <input id="filter_from" class="easyui-datebox" style="width:100%; height:38px;" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>

            <div class="fitem-horizontal">
                <span>To</span>
                <input id="filter_to" class="easyui-datebox" style="width:100%; height:38px;" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>

            <div class="fitem-horizontal">
                <span>Supplier</span>
                <input id="filter_supplier_id" class="easyui-combobox" style="width:100%; height:38px;">
            </div>

            <div class="fitem-horizontal">
                <span>Division</span>
                <input id="filter_division" class="easyui-combobox" style="width:100%; height:38px;">
            </div>

            <div style="flex: 0 0 auto; margin-bottom: 5px;">
                <a href="javascript:;" class="easyui-linkbutton" onclick="loadDashboard()"><i class="fa fa-search"></i> Filter Data</a>
                
                <?= $button; ?>
            </div>
        </div>
    </div>
    
    <div class="kpi-single">
        <div>
            <div style="font-size: 14px; opacity: 0.9; font-weight: 500;">Total Purchase Amount</div>
            <div id="kpi_total_amt" style="font-size: 32px; font-weight: 800; margin-top: 4px;">Rp 0</div>
        </div>
        <div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 50%;">
            <i class="fa fa-money-bill-wave fa-2x"></i>
        </div>
    </div>

    <div style="display: flex; gap: 20px;">
        <div class="dashboard-card" style="flex: 1;">
            <div class="easyui-panel" title="Purchase Trend (Bar)" data-options="border:false" style="width:100%; height:450px; padding:20px;">
                <canvas id="purchaseChart"></canvas>
            </div>
        </div>
        <div class="dashboard-card" style="flex: 1;">
            <div class="easyui-panel" title="Top Suppliers" data-options="border:false" style="width:100%; height:450px; padding:20px;">
                <canvas id="supplierChart"></canvas>
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
        prompt: "Select Categories"
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
    };

    $.post('<?= base_url("purchase/purchase_dashboard/get_dashboard_data") ?>', params, function(res) {
        const data = JSON.parse(res);

        // Update KPI
        $('#kpi_total_amt').html(data.total_amount_formatted);
        $('#kpi_total_po').html(data.total_po);

        // Render/Update Chart Purchase
        updateTrendChart(data.trend_labels, data.trend_values);
        
        // Render/Update Chart Supplier
        updateSupplierChart(data.supp_labels, data.supp_values);
    });
}

// Chart Purchase Stacked
function updateTrendChart(labels, values) {
    const ctx = document.getElementById('purchaseChart').getContext('2d');
    
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

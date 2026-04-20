<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="toolbar" style="padding:10px; background:#f4f4f4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">

        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 100%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Display Period</span>
                    <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">
                        <option value="DAILY">DAILY</option>
                        <option value="WEEKLY">WEEKLY</option>
                        <option value="MONTHLY">MONTHLY</option>
                        <option value="YEARLY">YEARLY</option>
                    </select>
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Date</span>
                    <input style="width:29%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:29%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Name</span>
                    <input style="width:60%;" id="filter_supplier_id" class="easyui-combobox">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" id="filter_division" class="easyui-combobox">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="loadDashboard()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>

        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Summary</b></legend>

            <div style="width: 100%; float:left;">
                <div class="card-kpi" style="flex:1; background:#3498db; color:white; padding:20px; margin-bottom:10px; border-radius:8px;">
                    <div style="font-size:14px;">Total Purchase Amount</div>
                    <div id="kpi_total_amt" style="font-size:24px; font-weight:bold;">Rp 0</div>
                </div>

                <div class="card-kpi" style="flex:1; background:#e67e22; color:white; padding:20px; margin-bottom:10px; border-radius:8px;">
                    <div style="font-size:14px;">Total PO Issued</div>
                    <div id="kpi_total_po" style="font-size:24px; font-weight:bold;">0</div>
                </div>
            </div>
        </fieldset>

    </div>

    <?= $button ?>
</div>

<div class="easyui-panel" style="width:100%; padding:15px;">
    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
        <div class="easyui-panel" title="Purchase by Amount" style="width:50%; height:700px; padding:10px;">
            <canvas id="purchaseChart"></canvas>
        </div>
        <div class="easyui-panel" title="Purchase per Suppliers" style="width:50%; height:700px; padding:10px;">
            <canvas id="supplierChart"></canvas>
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

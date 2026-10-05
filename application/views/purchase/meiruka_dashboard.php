<div class="dashboard-wrapper">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        .dashboard-wrapper { background-color: #f4f6f9; padding: 20px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; color: #1e293b; }
        
        .header-title-bar {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
        }
        .main-title {
            font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; margin: 0; text-transform: uppercase;
        }
        .main-subtitle { font-size: 13px; color: #64748b; margin-top: 2px; }

        .filter-container {
            display: flex; gap: 10px; align-items: center; background: white; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;
        }

        /* Top 3 Metric Cards */
        .cards-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px;
        }
        .metric-card {
            background: white; border-radius: 12px; padding: 20px 24px; border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03); position: relative; display: flex; justify-content: space-between; align-items: flex-start;
        }
        .metric-title { font-size: 14px; font-weight: 800; letter-spacing: 0.5px; }
        .metric-val { font-size: 38px; font-weight: 800; line-height: 1.1; margin-top: 8px; }
        .metric-val span { font-size: 14px; font-weight: 600; color: #64748b; margin-left: 4px; }
        .metric-sub { font-size: 12px; font-weight: 600; margin-top: 10px; }
        .metric-icon-circle {
            width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;
        }

        .card-ok { border-top: 4px solid #16a34a; }
        .card-ok .metric-title { color: #15803d; }
        .card-ok .metric-sub { color: #16a34a; }
        .card-ok .metric-icon-circle { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

        .card-under { border-top: 4px solid #dc2626; }
        .card-under .metric-title { color: #991b1b; }
        .card-under .metric-sub { color: #dc2626; }
        .card-under .metric-icon-circle { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        .card-over { border-top: 4px solid #f97316; }
        .card-over .metric-title { color: #c2410c; }
        .card-over .metric-sub { color: #ea580c; }
        .card-over .metric-icon-circle { background: #fff7ed; color: #f97316; border: 1px solid #ffedd5; }

        /* Content Layout */
        .dashboard-grid {
            display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;
        }

        .panel-card {
            background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .panel-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;
        }
        .panel-title { font-size: 14px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Top Supplier List Cards */
        .top-supplier-item {
            display: flex; justify-content: space-between; align-items: center; padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9; font-size: 12px; font-weight: 600;
        }
        .top-supplier-item:last-child { border-bottom: none; }
        .top-supplier-name { display: flex; align-items: center; gap: 8px; color: #334155; }
        .top-supplier-badge {
            font-weight: 800; padding: 2px 8px; border-radius: 4px; font-size: 12px;
        }
        .bg-under-light { background: #fef2f2; color: #dc2626; }
        .bg-over-light { background: #fff7ed; color: #ea580c; }

        .list-total-footer {
            display: flex; justify-content: space-between; align-items: center; font-weight: 800;
            padding-top: 12px; margin-top: 8px; border-top: 2px solid #e2e8f0; font-size: 13px;
        }

        /* Detail Status Table */
        .status-table {
            width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;
        }
        .status-table th {
            background: #0f2b48; color: white; padding: 10px 15px; font-weight: 700; text-transform: uppercase; font-size: 12px;
        }
        .status-table td {
            padding: 10px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #334155;
        }
        .status-table tr.total-row td {
            font-weight: 800; background: #f8fafc; border-top: 2px solid #cbd5e1; color: #0f172a;
        }
        .dot-indicator {
            display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 8px;
        }

        /* Conclusion & Impact Section Style */
        .section-title { 
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; font-weight: 700; color: #475569;
            margin-bottom: 12px; border-left: 4px solid #3b82f6; padding-left: 10px;
        }
        .chart-section {
            background: white; border-radius: 12px; padding: 0; overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;
        }
        .chart-header {
            background: #244b82; color: white; padding: 10px 20px;
            font-size: 13px; font-weight: 600;
        }
    </style>

    <div class="header-title-bar">
        <div>
            <h1 class="main-title">SUPPLIER STATUS DASHBOARD</h1>
            <div class="main-subtitle">Monitoring status material berdasarkan supplier</div>
        </div>
        <div class="filter-container">
            <label style="font-size:12px; font-weight:700; color:#64748b;">DISPLAY:</label>
            <select id="filter_display" class="easyui-combobox" style="width:120px; height:32px;" data-options="panelHeight:'auto', editable:false">
                <option value="WEEKLY">WEEKLY</option>
                <option value="MONTHLY" selected>MONTHLY</option>
                <option value="YEARLY">YEARLY</option>
            </select>
            <label style="font-size:12px; font-weight:700; color:#64748b; margin-left:5px;">DATE:</label>
            <input id="filter_date" class="easyui-datebox" style="width:130px; height:32px;" data-options="formatter:myformatter,parser:myparser" value="<?= date('Y-m-d') ?>">
            <a href="javascript:void(0)" class="easyui-linkbutton" onclick="loadDashboard()" data-options="iconCls:'icon-search'" style="height:32px;">Filter</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" onclick="reload()" data-options="iconCls:'icon-reload'" style="height:32px;">Reload</a>
        </div>
    </div>

    <!-- 3 SUMMARY CARDS -->
    <div class="cards-grid">
        <div class="metric-card card-ok">
            <div>
                <div class="metric-title">OK</div>
                <div class="metric-val"><span id="val_ok">0</span> <span>Item</span></div>
                <div class="metric-sub"><span id="pct_ok">0%</span> dari total item</div>
            </div>
            <div class="metric-icon-circle"><i class="fa-solid fa-check"></i></div>
        </div>

        <div class="metric-card card-under">
            <div>
                <div class="metric-title">UNDER</div>
                <div class="metric-val"><span id="val_under">0</span> <span>Item</span></div>
                <div class="metric-sub"><span id="pct_under">0%</span> dari total item</div>
            </div>
            <div class="metric-icon-circle"><i class="fa-solid fa-triangle-exclamation"></i></div>
        </div>

        <div class="metric-card card-over">
            <div>
                <div class="metric-title">OVER</div>
                <div class="metric-val"><span id="val_over">0</span> <span>Item</span></div>
                <div class="metric-sub"><span id="pct_over">0%</span> dari total item</div>
            </div>
            <div class="metric-icon-circle"><i class="fa-solid fa-angles-up"></i></div>
        </div>
    </div>

    <!-- MAIN MIDDLE GRID -->
    <div class="dashboard-grid">
        <!-- STATUS SUPPLIER BAR CHART -->
        <div class="panel-card">
            <div class="panel-header">
                <div class="panel-title">STATUS SUPPLIER</div>
            </div>
            <div id="supplier_bar_chart" style="min-height: 420px;"></div>
        </div>

        <!-- RIGHT SIDE TOP SUPPLIERS -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- TOP UNDER SUPPLIER -->
            <div class="panel-card" style="border-top: 3px solid #ef4444;">
                <div class="panel-header" style="margin-bottom:10px;">
                    <div class="panel-title" style="color: #b91c1c;">TOP UNDER SUPPLIER</div>
                </div>
                <div id="top_under_list" style="min-height: 140px;"></div>
                <div class="list-total-footer">
                    <span style="color:#64748b;">Total Under</span>
                    <span id="total_under_cnt" style="color:#ef4444;">0</span>
                </div>
            </div>

            <!-- TOP OVER SUPPLIER -->
            <div class="panel-card" style="border-top: 3px solid #f97316;">
                <div class="panel-header" style="margin-bottom:10px;">
                    <div class="panel-title" style="color: #c2410c;">TOP OVER SUPPLIER</div>
                </div>
                <div id="top_over_list" style="min-height: 140px;"></div>
                <div class="list-total-footer">
                    <span style="color:#64748b;">Total Over</span>
                    <span id="total_over_cnt" style="color:#ea580c;">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM GRID: DONUT & TABLE -->
    <div class="dashboard-grid">
        <!-- RINGKASAN STATUS (DONUT CHART) -->
        <div class="panel-card">
            <div class="panel-header">
                <div class="panel-title">RINGKASAN STATUS</div>
            </div>
            <div style="display: flex; align-items: center; justify-content: center; min-height: 240px;">
                <div id="donut_status_chart" style="width: 100%;"></div>
            </div>
        </div>

        <!-- DETAIL STATUS TABLE -->
        <div class="panel-card" style="padding: 0; overflow: hidden;">
            <div class="panel-header" style="padding: 15px 20px 10px 20px; margin-bottom: 0;">
                <div class="panel-title">DETAIL STATUS</div>
            </div>
            <table class="status-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th style="text-align:center;">Jumlah Supplier</th>
                        <th style="text-align:center;">Jumlah Item</th>
                        <th style="text-align:center;">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="dot-indicator" style="background:#16a34a;"></span>OK</td>
                        <td style="text-align:center;" id="tb_sup_ok">0 Supplier</td>
                        <td style="text-align:center;" id="tb_item_ok">0 Item</td>
                        <td style="text-align:center;" id="tb_pct_ok">0%</td>
                    </tr>
                    <tr>
                        <td><span class="dot-indicator" style="background:#f97316;"></span>OVER</td>
                        <td style="text-align:center;" id="tb_sup_over">0 Supplier</td>
                        <td style="text-align:center;" id="tb_item_over">0 Item</td>
                        <td style="text-align:center;" id="tb_pct_over">0%</td>
                    </tr>
                    <tr>
                        <td><span class="dot-indicator" style="background:#dc2626;"></span>UNDER</td>
                        <td style="text-align:center;" id="tb_sup_under">0 Supplier</td>
                        <td style="text-align:center;" id="tb_item_under">0 Item</td>
                        <td style="text-align:center;" id="tb_pct_under">0%</td>
                    </tr>
                    <tr class="total-row">
                        <td>TOTAL</td>
                        <td style="text-align:center;" id="tb_sup_total">0 Supplier</td>
                        <td style="text-align:center;" id="tb_item_total">0 Item</td>
                        <td style="text-align:center;">100%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- CONCLUSION AND IMPACT SECTION -->
    <div class="section-title"><i class="fa fa-files-o"></i> CONCLUSION AND IMPACT</div>

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
    function myformatter(date){
        var y = date.getFullYear();
        var m = date.getMonth()+1;
        var d = date.getDate();
        return y + '-' + (m<10?('0'+m):m) + '-' + (d<10?('0'+d):d);
    }
    function myparser(s){
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0],10);
        var m = parseInt(ss[1],10);
        var d = parseInt(ss[2],10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
            return new Date(y,m-1,d);
        } else {
            return new Date();
        }
    }

    var _supplierBarChart = null;
    var _donutChart = null;

    $(function() {
        $('#filter_date').datebox('setValue', myformatter(new Date()));
        loadDashboard();
    });

    function loadDashboard() {
        var filter_display = $('#filter_display').combobox('getValue');
        var filter_date    = $('#filter_date').datebox('getValue');

        var url = '<?= base_url('purchase/Meiruka_dashboard/get_dashboard_data') ?>'
                + '?filter_display=' + filter_display
                + '&filter_date='    + filter_date;

        $.getJSON(url, function(res) {
            var s = res.summary;
            // Cards
            $('#val_ok').text(s.ok_items);
            $('#pct_ok').text(s.ok_pct + '%');
            $('#val_under').text(s.under_items);
            $('#pct_under').text(s.under_pct + '%');
            $('#val_over').text(s.over_items);
            $('#pct_over').text(s.over_pct + '%');

            // Top Under Supplier
            var htmlUnder = '';
            if (res.top_under) {
                var keys = Object.keys(res.top_under);
                keys.slice(0, 5).forEach(function(sup) {
                    var val = res.top_under[sup];
                    htmlUnder += '<div class="top-supplier-item">'
                        + '<div class="top-supplier-name"><i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;"></i> ' + sup + '</div>'
                        + '<div class="top-supplier-badge bg-under-light">' + val + '</div>'
                        + '</div>';
                });
            }
            $('#top_under_list').html(htmlUnder || '<div style="color:#94a3b8; padding:10px; font-size:12px;">Tidak ada data</div>');
            $('#total_under_cnt').text(s.under_items);

            // Top Over Supplier
            var htmlOver = '';
            if (res.top_over) {
                var keys = Object.keys(res.top_over);
                keys.slice(0, 5).forEach(function(sup) {
                    var val = res.top_over[sup];
                    htmlOver += '<div class="top-supplier-item">'
                        + '<div class="top-supplier-name"><i class="fa-solid fa-angles-up" style="color:#ea580c;"></i> ' + sup + '</div>'
                        + '<div class="top-supplier-badge bg-over-light">' + val + '</div>'
                        + '</div>';
                });
            }
            $('#top_over_list').html(htmlOver || '<div style="color:#94a3b8; padding:10px; font-size:12px;">Tidak ada data</div>');
            $('#total_over_cnt').text(s.over_items);

            // Table Detail Status
            $('#tb_sup_ok').text(s.supplier_ok_count + ' Supplier');
            $('#tb_item_ok').text(s.ok_items + ' Item');
            $('#tb_pct_ok').text(s.ok_pct + '%');

            $('#tb_sup_over').text(s.supplier_over_count + ' Supplier');
            $('#tb_item_over').text(s.over_items + ' Item');
            $('#tb_pct_over').text(s.over_pct + '%');

            $('#tb_sup_under').text(s.supplier_under_count + ' Supplier');
            $('#tb_item_under').text(s.under_items + ' Item');
            $('#tb_pct_under').text(s.under_pct + '%');

            $('#tb_sup_total').text(s.total_suppliers + ' Supplier');
            $('#tb_item_total').text(s.total_items + ' Item');

            // // Dynamic Conclusion & Impact
            // var concHtml = '<ul style="margin:0; padding-left:18px; font-size:13px; color:#334155; line-height:1.6;">'
            //     + '<li>Total item terpantau sebanyak <b>' + s.total_items + ' item</b> dari <b>' + s.total_suppliers + ' supplier</b>.</li>'
            //     + '<li>Status <b>OK</b> mencakup <b>' + s.ok_items + ' item (' + s.ok_pct + '%)</b>.</li>'
            //     + '<li>Status <b>OVER</b> mencakup <b>' + s.over_items + ' item (' + s.over_pct + '%)</b>.</li>'
            //     + '<li>Status <b>UNDER</b> mencakup <b>' + s.under_items + ' item (' + s.under_pct + '%)</b>.</li>'
            //     + '</ul>';

            // var impHtml = '<ul style="margin:0; padding-left:18px; font-size:13px; color:#334155; line-height:1.6;">'
            //     + '<li><b>Potensi Shortage/Stop Line:</b> ' + s.under_items + ' item berkategori UNDER berisiko menyebabkan keterlambatan produksi.</li>'
            //     + '<li><b>Penumpukan Stock & Holding Cost:</b> ' + s.over_items + ' item berkategori OVER berpotensi menaikkan biaya simpan gudang.</li>'
            //     + '<li><b>Stabilitas Supply Chain:</b> Perlu evaluasi schedule pengiriman untuk supplier berkategori Under/Over tinggi.</li>'
            //     + '</ul>';

            // $('#conclusion').html(concHtml);
            // $('#impact').html(impHtml);

            // Render Charts
            renderHorizontalBarChart(res.supplier_chart);
            renderDonutChart(s.ok_items, s.over_items, s.under_items);
        });
    }

    function renderHorizontalBarChart(chartData) {
        if (_supplierBarChart) _supplierBarChart.destroy();

        var categories = [];
        var sOK = [];
        var sOVER = [];
        var sUNDER = [];

        chartData.forEach(function(d) {
            categories.push(d.supplier);
            sOK.push(d.ok);
            sOVER.push(d.over);
            sUNDER.push(d.under);
        });

        var options = {
            series: [{
                name: 'OK',
                data: sOK
            }, {
                name: 'OVER',
                data: sOVER
            }, {
                name: 'UNDER',
                data: sUNDER
            }],
            chart: {
                type: 'bar',
                height: Math.max(350, categories.length * 35),
                stacked: true,
                toolbar: { show: false }
            },
            colors: ['#16a34a', '#f97316', '#dc2626'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '60%',
                    borderRadius: 3
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val > 0 ? val : '';
                },
                style: { colors: ['#fff'], fontSize: '11px', fontWeight: 'bold' }
            },
            xaxis: {
                categories: categories,
                labels: { style: { fontSize: '11px', fontWeight: 600 } }
            },
            yaxis: {
                labels: { style: { fontSize: '11px', fontWeight: 600 } }
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center'
            },
            fill: { opacity: 1 }
        };

        _supplierBarChart = new ApexCharts(document.querySelector("#supplier_bar_chart"), options);
        _supplierBarChart.render();
    }

    function renderDonutChart(ok, over, under) {
        if (_donutChart) _donutChart.destroy();

        var options = {
            series: [ok, over, under],
            labels: ['OK', 'OVER', 'UNDER'],
            chart: {
                type: 'donut',
                height: 240
            },
            colors: ['#16a34a', '#f97316', '#dc2626'],
            legend: {
                show: true,
                position: 'right',
                formatter: function(val, opts) {
                    return val + ' - ' + opts.w.globals.series[opts.seriesIndex] + ' Item';
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return Math.round(val) + "%";
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%'
                    }
                }
            }
        };

        _donutChart = new ApexCharts(document.querySelector("#donut_status_chart"), options);
        _donutChart.render();
    }
</script>

<div class="dashboard-wrapper">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        .dashboard-wrapper { background-color: #f0f2f5; padding: 20px; font-family: 'Inter', sans-serif; }
        
        .header-title-bar {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;
        }
        .main-title {
            font-size: 22px; font-weight: 800; color: #0f172a; tracking: -0.5px; margin: 0;
        }

        .filter-card {
            background: white; border-radius: 10px; padding: 12px 20px;
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 15px;
            margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .card-stat {
            background: white; border-radius: 12px; padding: 15px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px; flex: 1;
        }
        .card-stat-icon {
            width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 20px; background: #eff6ff; color: #3b82f6;
        }
        .card-stat-val { font-size: 26px; font-weight: 800; color: #1e293b; line-height: 1.1; }
        .card-stat-lbl { font-size: 12px; color: #64748b; font-weight: 600; margin-top: 4px; }

        .table-section {
            background: white; border-radius: 12px; padding: 0; overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;
        }
        .table-header {
            background: #244b82; color: white; padding: 12px 20px;
            font-size: 13px; font-weight: 700; letter-spacing: 0.5px;
        }
        
        .cust-table {
            width: 100%; border-collapse: collapse; text-align: center; font-size: 12px;
        }
        .cust-table th {
            background: #f8fafc; color: #334155; font-weight: 700; border-bottom: 2px solid #e2e8f0; padding: 10px 8px;
        }
        .cust-table td {
            padding: 10px 8px; border-bottom: 1px solid #f1f5f9; color: #334155; font-weight: 600;
        }
        .cust-table tr:hover { background-color: #f8fafc; }

        .badge-perf {
            padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 11px; display: inline-block;
        }
        .perf-EXCELLENT { color: #0284c7; background: #e0f2fe; }
        .perf-GOOD { color: #16a34a; background: #dcfce7; }
        .perf-AVERAGE { color: #d97706; background: #fef3c7; }
        .perf-POOR { color: #dc2626; background: #fee2e2; }

        .chart-section {
            background: white; border-radius: 12px; padding: 0; overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;
        }
        .chart-header {
            background: #244b82; color: white; padding: 10px 20px;
            font-size: 13px; font-weight: 600;
        }
        .section-title { 
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; font-weight: 700; color: #475569;
            margin-bottom: 12px; border-left: 4px solid #3b82f6; padding-left: 10px;
        }
    </style>

    <div class="header-title-bar">
        <h1 class="main-title">DELIVERY CUSTOMER</h1>
        <div style="display:flex; gap:10px;">
            <a href="javascript:void(0)" class="easyui-linkbutton" onclick="exportExcel()" data-options="iconCls:'icon-excel'">Download Excel</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" onclick="reload()" data-options="iconCls:'icon-reload'">Reload</a>
        </div>
    </div>


    <div class="filter-card">
        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div>
                <label style="font-size:11px; font-weight:700; color:#64748b; display:block; margin-bottom:3px;">DISPLAY</label>
                <select id="filter_display" class="easyui-combobox" style="width:120px; height:32px;" data-options="panelHeight:'auto', editable:false">
                    <option value="WEEKLY">WEEKLY</option>
                    <option value="MONTHLY" selected>MONTHLY</option>
                    <option value="YEARLY">YEARLY</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px; font-weight:700; color:#64748b; display:block; margin-bottom:3px;">SELECT PERIOD (MONTH)</label>
                <select id="filter_month" class="easyui-combobox" style="width:140px; height:32px;" data-options="panelHeight:'auto', editable:false">
                    <option value="01">JANUARY</option>
                    <option value="02">FEBRUARY</option>
                    <option value="03">MARCH</option>
                    <option value="04">APRIL</option>
                    <option value="05">MAY</option>
                    <option value="06">JUNE</option>
                    <option value="07">JULY</option>
                    <option value="08">AUGUST</option>
                    <option value="09">SEPTEMBER</option>
                    <option value="10">OCTOBER</option>
                    <option value="11">NOVEMBER</option>
                    <option value="12">DECEMBER</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px; font-weight:700; color:#64748b; display:block; margin-bottom:3px;">YEAR</label>
                <input id="filter_year" class="easyui-numberbox" style="width:100px; height:32px;" value="<?= date("Y") ?>">
            </div>
            <div style="margin-top:16px;">
                <a href="javascript:void(0)" class="easyui-linkbutton" onclick="loadDashboard()" data-options="iconCls:'icon-search'" style="height:32px; padding:0 15px;">Filter</a>
            </div>
        </div>

        <div style="display: flex; gap: 15px; width: 420px;">
            <div class="card-stat">
                <div class="card-stat-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div>
                    <div id="stat_ontime" class="card-stat-val">0%</div>
                    <div class="card-stat-lbl">On Time Delivery</div>
                </div>
            </div>
            <div class="card-stat">
                <div class="card-stat-icon" style="background:#f0fdf4; color:#22c55e;"><i class="fa-solid fa-truck-fast"></i></div>
                <div>
                    <div id="stat_leadtime" class="card-stat-val">8.9</div>
                    <div class="card-stat-lbl">Lead Time (Days)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE SECTION -->
    <div class="table-section">
        <div class="table-header">DELIVERY CUSTOMER ANALYSIS</div>
        <table class="cust-table">
            <thead>
                <tr>
                    <th style="width:40px;">No.</th>
                    <th style="text-align:left; padding-left:15px;">Customer</th>
                    <th>TARGET (%)</th>
                    <th>∑ 3 Month (%)</th>
                    <th>% DELIVERY Qty</th>
                    <th>Performance</th>
                </tr>
            </thead>
            <tbody id="tb_cust_body">
                <tr><td colspan="6" style="padding:20px; color:#94a3b8;">Loading data...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- CHART SECTION -->
    <div class="chart-section">
        <div class="chart-header" style="text-align:center;" id="chart_period_title">
            DELIVERY CUSTOMER ANALYSIS
        </div>
        <div id="delivery_bar_chart" style="min-height: 350px; padding: 15px;"></div>
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

    function exportExcel() {
        var display = $('#filter_display').combobox('getValue');
        var month   = $('#filter_month').combobox('getValue');
        var year    = $('#filter_year').numberbox('getValue');
        var url = '<?= base_url('sales/Delivery_customer_dashboard/export_excel') ?>'
                + '?display=' + window.btoa(display)
                + '&month='   + window.btoa(month)
                + '&year='    + window.btoa(year);
        window.location.href = url;
    }

    var _barChart = null;


    $(function() {
        $('#filter_month').combobox('setValue', '<?= date('m') ?>');
        loadDashboard();
    });

    function loadDashboard() {
        var display   = $('#filter_display').combobox('getValue');
        var month     = $('#filter_month').combobox('getValue');
        var year      = $('#filter_year').numberbox('getValue');
        var monthText = $('#filter_month').combobox('getText');

        var titlePeriod = display === 'WEEKLY' ? 'WEEKLY (' + year + ')' : (display === 'YEARLY' ? 'YEARLY (' + year + ')' : monthText + ' ' + year);
        $('#chart_period_title').text('DELIVERY CUSTOMER ANALYSIS - ' + titlePeriod);

        var url = '<?= base_url('sales/Delivery_customer_dashboard/get_dashboard_data') ?>'
                + '?display=' + window.btoa(display)
                + '&month='   + window.btoa(month)
                + '&year='    + window.btoa(year);

        $.getJSON(url, function(res) {
            $('#stat_ontime').text(res.overall_ontime + '%');
            
            var tbody = '';
            var custNames = [];
            var sTarget = [];
            var s3Month = [];
            var sDelQty = [];

            if (res.rows && res.rows.length > 0) {
                res.rows.forEach(function(r, idx) {
                    tbody += '<tr>'
                           + '<td>' + (idx + 1) + '</td>'
                           + '<td style="text-align:left; padding-left:15px;">' + r.customer_name + '</td>'
                           + '<td>' + r.target + '</td>'
                           + '<td>' + r.m3_pct + '</td>'
                           + '<td>' + r.pct_delivery + '</td>'
                           + '<td><span class="badge-perf perf-' + r.performance + '">' + r.performance + '</span></td>'
                           + '</tr>';

                    custNames.push(r.customer_name);
                    sTarget.push(r.target);
                    s3Month.push(r.m3_pct);
                    sDelQty.push(r.pct_delivery);
                });
            } else {
                tbody = '<tr><td colspan="6" style="padding:20px; color:#94a3b8;">No data available for selected period</td></tr>';
            }

            $('#tb_cust_body').html(tbody);
            renderGroupedBarChart(custNames, sTarget, s3Month, sDelQty);
        });
    }

    function renderGroupedBarChart(categories, sTarget, s3Month, sDelQty) {
        if (_barChart) {
            _barChart.destroy();
        }

        var options = {
            series: [{
                name: 'TARGET (%)',
                data: sTarget
            }, {
                name: '3 Month (%)',
                data: s3Month
            }, {
                name: '% DELIVERY Qty',
                data: sDelQty
            }],
            chart: {
                type: 'bar',
                height: 380,
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true
                    }
                }
            },
            colors: ['#475569', '#f59e0b', '#06b6d4'],

            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: true,
                style: { fontSize: '10px' }
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: categories,
                labels: {
                    style: { fontSize: '11px', fontWeight: 600 }
                }
            },
            yaxis: {
                max: 120,
                title: { text: 'Percentage (%)' }
            },
            fill: { opacity: 1 },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + "%";
                    }
                }
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center'
            }
        };

        _barChart = new ApexCharts(document.querySelector("#delivery_bar_chart"), options);
        _barChart.render();
    }
</script>

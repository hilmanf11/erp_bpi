<div class="dashboard-wrapper">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        .dashboard-wrapper { background-color: #f0f2f5; padding: 15px; font-family: 'Inter', sans-serif; }
        .section-title { 
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; font-weight: 700; color: #475569;
            margin-bottom: 12px; border-left: 4px solid #3b82f6; padding-left: 10px;
        }
        .filter-container {
            background: white; border-radius: 10px; padding: 12px 20px;
            display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
            margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .chart-section {
            background: white; border-radius: 12px; padding: 0; overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;
        }
        .chart-header {
            background: #244b82; color: white; padding: 10px 20px;
            font-size: 13px; font-weight: 600;
        }
        .donut-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 12px; padding: 15px;
        }
        .donut-card {
            background: white; border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08); overflow: hidden;
        }
        .donut-card-header {
            background: #f1f5f9; padding: 8px 12px;
            font-size: 11px; font-weight: 700; color: #334155;
            text-align: center; border-bottom: 1px solid #e2e8f0;
        }

        /* --- PRINT STYLE --- */
        @media print {
            body.printing-chart * {
                visibility: hidden;
                margin: 0;
            }
            body.printing-chart .printable-area,
            body.printing-chart .printable-area * {
                visibility: visible;
            }
            body.printing-chart .printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                border: none !important;
            }
            .custom-tools {
                display: none !important;
            }
            .apexcharts-toolbar {
                display: none !important;
            }
        }
    </style>

    <div class="section-title"><i class="fa fa-search"></i> FILTER & SUMMARY</div>
    <div class="filter-container">
        <div style="flex: 1; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <label style="font-size:12px; font-weight:600; color:#475569;">Month:</label>
            <select id="filter_month" class="easyui-combobox" style="width:130px; height:32px;" 
                    data-options="panelHeight:'auto', editable:false">
                <option value="01">JANUARI</option>
                <option value="02">FEBRUARI</option>
                <option value="03">MARET</option>
                <option value="04">APRIL</option>
                <option value="05">MEI</option>
                <option value="06">JUNI</option>
                <option value="07">JULI</option>
                <option value="08">AGUSTUS</option>
                <option value="09">SEPTEMBER</option>
                <option value="10">OKTOBER</option>
                <option value="11">NOVEMBER</option>
                <option value="12">DESEMBER</option>
            </select>
            <label style="font-size:12px; font-weight:600; color:#475569;">Year:</label>
            <input id="filter_year" class="easyui-numberbox" style="width:100px; height:32px;" value="<?= date("Y") ?>">
        </div>
        <a href="javascript:void(0)" class="easyui-linkbutton" onclick="printSpecificChart('allschart')" title="Print Chart" style="color: black;">
            <i class="fa fa-print"></i>
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" onclick="toggleFullScreen('allschart')" title="Full Screen" style="color: black;">
            <i class="fa fa-expand"></i>
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" onclick="loadDashboard()" 
           data-options="iconCls:'icon-search'" style="height:32px; padding:0 15px;">Filter</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" onclick="reload()" 
           data-options="iconCls:'icon-reload'" style="height:32px; padding:0 15px;">Reload</a>
    </div>

    <div id="allschart" class="chart-section" style="flex: 1; background: transparent; box-shadow: none; margin-bottom: 0;">

        <!-- SEGMENT 1: BY AMOUNT -->
        <div class="chart-section">
            <div class="chart-header"><i class="fa fa-dollar-sign"></i> ABC ANALYSIS IN AMOUNT</div>
            <div style="display:flex; gap:0; flex-wrap:wrap;">
                <div style="flex:0 0 280px; padding:15px;">
                    <div id="main-pie-amount" style="height:280px;"></div>
                </div>
                <div style="flex:1;">
                    <div id="donut-amount" class="donut-grid"></div>
                </div>
            </div>
        </div>

        <!-- SEGMENT 2: BY QTY -->
        <div class="chart-section">
            <div class="chart-header"><i class="fa fa-boxes"></i> ABC ANALYSIS IN QTY</div>
            <div style="display:flex; gap:0; flex-wrap:wrap;">
                <div style="flex:0 0 280px; padding:15px;">
                    <div id="main-pie-qty" style="height:280px;"></div>
                </div>
                <div style="flex:1;">
                    <div id="donut-qty" class="donut-grid"></div>
                </div>
            </div>
        </div>

        <!-- SEGMENT 3: BY ITEM COUNT -->
        <div class="chart-section">
            <div class="chart-header"><i class="fa fa-list"></i> ABC ANALYSIS IN ITEM</div>
            <div style="display:flex; gap:0; flex-wrap:wrap;">
                <div style="flex:0 0 280px; padding:15px;">
                    <div id="main-pie-item" style="height:280px;"></div>
                </div>
                <div style="flex:1;">
                    <div id="donut-item" class="donut-grid"></div>
                </div>
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

</div>

<script>
    function reload() {
        window.location.reload();
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

        document.body.classList.remove('printing-chart');
        el.classList.remove('printable-area');
    }

    var ABC_COLORS = ['#4472C4', '#ED7D31', '#A5A5A5']; // A=Blue, B=Orange, C=Grey

    $(function() {
        $('#filter_month').combobox('setValue', '<?= date('m') ?>');
        loadDashboard();
    });

    function loadDashboard() {
        var month = $('#filter_month').combobox('getValue');
        var year  = $('#filter_year').numberbox('getValue');

        var url = '<?= base_url('planning/Abc_dashboard_rm/get_abc_data') ?>'
                + '?month=' + window.btoa(month)
                + '&year='  + window.btoa(year);

        $.getJSON(url, function(data) {
            renderMainPie('main-pie-amount', data.summary_amount, 'RAW MATERIAL IN AMOUNT');
            renderMainPie('main-pie-qty',    data.summary_qty,    'RAW MATERIAL IN QTY');
            renderMainPie('main-pie-item',   data.summary_item,   'RAW MATERIAL IN ITEMS');
            renderDonuts('donut-amount', data.chart_amount, 'amt');
            renderDonuts('donut-qty',    data.chart_qty,    'qty');
            renderDonuts('donut-item',   data.chart_item,   'item');
        });
    }

    var _chartInstances = {};

    function renderMainPie(chartId, summaryData, title) {
        if (_chartInstances[chartId]) { _chartInstances[chartId].destroy(); }

        var month = $('#filter_month').combobox('getText');
        var year  = $('#filter_year').numberbox('getValue');

        var labels = summaryData.map(function(d){ return d.prodfam; });
        var series = summaryData.map(function(d){ return parseFloat(d.value) || 0; });

        var PRODFAM_COLORS = ['#4472C4','#FF69B4','#00B050','#92D050','#A5A5A5','#ED7D31','#FFC000','#FF0000'];

        var options = {
            chart: {
                type: 'pie', height: 280,
                id: chartId,
                toolbar: { show: false }
            },
            series: series,
            labels: labels,
            colors: PRODFAM_COLORS,
            title: {
                text: [title, month.toUpperCase() + ' ' + year],
                align: 'center',
                style: { fontSize: '11px', fontWeight: '700', color: '#334155' }
            },
            legend: { position: 'bottom', fontSize: '10px' },
            dataLabels: {
                enabled: true,
                formatter: function(val, opts) {
                    return opts.w.config.series[opts.seriesIndex].toLocaleString('id-ID');
                }
            },
            tooltip: {
                y: { formatter: function(val){ return val.toLocaleString('id-ID'); } }
            }
        };

        var chart = new ApexCharts(document.querySelector('#' + chartId), options);
        chart.render();
        _chartInstances[chartId] = chart;
    }


    function renderDonuts(containerId, chartData, prefix) {
        var container = document.getElementById(containerId);
        container.innerHTML = '';

        chartData.forEach(function(item) {
            var pf      = item.prodfam || 'Unknown';
            var safe    = pf.replace(/[^a-zA-Z0-9]/g, '_');
            var chartId = prefix + '_' + safe;

            var card = document.createElement('div');
            card.className = 'donut-card';

            var header = document.createElement('div');
            header.className = 'donut-card-header';
            header.textContent = pf;

            var chartDiv = document.createElement('div');
            chartDiv.id = chartId;
            chartDiv.style.height = '200px';

            card.appendChild(header);
            card.appendChild(chartDiv);
            container.appendChild(card);

            if (_chartInstances[chartId]) {
                _chartInstances[chartId].destroy();
            }

            var valA = parseFloat(item.A) || 0;
            var valB = parseFloat(item.B) || 0;
            var valC = parseFloat(item.C) || 0;

            var options = {
                chart: {
                    type: 'donut',
                    height: 200,
                    id: chartId,
                    toolbar: { show: false },
                    animations: { enabled: true }
                },
                series: [valA, valB, valC],
                labels: ['Class A', 'Class B', 'Class C'],
                colors: ABC_COLORS,
                legend: { position: 'bottom', fontSize: '11px' },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        var v = opts.w.config.series[opts.seriesIndex];
                        return v > 0 ? v.toLocaleString('id-ID') : '';
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val.toLocaleString('id-ID');
                        }
                    }
                },
                plotOptions: {
                    pie: { donut: { size: '60%' } }
                }
            };

            var chart = new ApexCharts(document.querySelector('#' + chartId), options);
            chart.render();
            _chartInstances[chartId] = chart;
        });
    }
</script>

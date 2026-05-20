<div class="dashboard-wrapper">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

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

    
    <div class="section-title"><i class="fa fa-search"></i> FILTER & SUMMARY</div>

    <div class="filter-container">
        <div style="flex: 1; display: flex; gap: 10px; align-items: center;">
            <select id="filter_display" class="easyui-combobox" style="width:120px; height:32px;">
                <option value="MONTHLY">MONTHLY</option>
                <option value="YEARLY">YEARLY</option>
            </select>
            
            <select id="filter_month" class="easyui-combobox" 
                    style="width:130px; height:32px;" 
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
            <input id="filter_year" class="easyui-numberbox" style="width:130px; height:32px;" value="<?= date("Y") ?>">

        </div>
        <a href="javascript:void(0)" class="easyui-linkbutton" onclick="printSpecificChart('allschart')" title="Print Chart" style="color: black;">
            <i class="fa fa-print"></i>
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" onclick="toggleFullScreen('allschart')" title="Full Screen" style="color: black;">
            <i class="fa fa-expand"></i>
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" onclick="loadDashboard()" data-options="iconCls:'icon-search'" style="height:32px; padding:0 15px;">Filter</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" onclick="reload()" data-options="iconCls:'icon-reload'" style="height:32px; padding:0 15px;">Reload</a>
    </div>

    <div id="allschart" class="chart-section" style="flex: 1;">

        <div class="kpi-grid">
            <div class="kpi-card bg-green">
                <div class="kpi-label">Total Qty</div>
                <div id="total_in_qty" class="kpi-value">0</div>
                <i class="fa fa-file-invoice"></i>
            </div>
            <div class="kpi-card bg-blue">
                <div class="kpi-label">Total (in Million)</div>
                <div id="total_in_amount" class="kpi-value">Rp 0</div>
                <i class="fa fa-wallet"></i>
            </div>
        </div>


        <div class="section-title"><i class="fa fa-bar-chart"></i> INVENTORY TURN OVER</div>
        <div style="display: flex; gap: 15px;">
            <div class="chart-section" style="flex: 1; width: 99.9%;">
                <table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">  
                </table>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-bottom: 15px;">

            <div id="itodaysChartSection" class="chart-section" style="flex: 1;">

                <div class="chart-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-weight: bold;">
                        <i class="fa fa-chart-bar"></i> ITO Days
                    </div>
                    
                    <div class="custom-tools" style="display: flex; gap: 15px;">
                        <a href="javascript:void(0)" onclick="exportToExcel('itodaysChart')" title="Export Excel" style="color: white;">
                            <i class="fa fa-file-excel"></i>
                        </a>
                        <a href="javascript:void(0)" onclick="printSpecificChart('itodaysChartSection')" title="Print Chart" style="color: white;">
                            <i class="fa fa-print"></i>
                        </a>
                        <a href="javascript:void(0)" onclick="toggleFullScreen('itodaysChartSection')" title="Full Screen" style="color: white;">
                            <i class="fa fa-expand"></i>
                        </a>
                    </div>
                </div>

                <div style="padding: 10px; height: 350px;">
                    <div id="itodaysChart" style="height: 100%; width: 100%;"></div>
                </div>
            </div>
            <div id="rmtoChartSection" class="chart-section" style="flex: 1;">

                <div class="chart-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-weight: bold;">
                        <i class="fa fa-chart-bar"></i> Raw Material Turn Over
                    </div>
                    
                    <div class="custom-tools" style="display: flex; gap: 15px;">
                        <a href="javascript:void(0)" onclick="exportToExcel('rmtoChart')" title="Export Excel" style="color: white;">
                            <i class="fa fa-file-excel"></i>
                        </a>
                        <a href="javascript:void(0)" onclick="printSpecificChart('rmtoChartSection')" title="Print Chart" style="color: white;">
                            <i class="fa fa-print"></i>
                        </a>
                        <a href="javascript:void(0)" onclick="toggleFullScreen('rmtoChartSection')" title="Full Screen" style="color: white;">
                            <i class="fa fa-expand"></i>
                        </a>
                    </div>
                </div>

                <div style="padding: 10px; height: 350px;">
                    <div id="rmtoChart" style="height: 100%; width: 100%;"></div>
                </div>
            </div>
        </div>

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
        console.log(id);
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

        $('#filter_month').combobox('setValue','<?=date('m'); ?>');
        // show on load
        loadDashboard();

    });





    /** ---- CHART ---- */

    var mySalesChart;
    var myCustomerChart;

    let chartInstances = {};

    function loadDashboard() {
            var month = $('#filter_month').combobox('getValue');
            var year = $('#filter_year').numberbox('getValue');
            var display = $('#filter_display').combobox('getValue');

        url = "?&month=" + window.btoa(month) + "&year=" + window.btoa(year) + "&display=" + window.btoa(display);

        console.log('<?= base_url('warehouse/Ito_dashboard_rm/get_dashboard_datatables') ?>' + url);

        $('#dg').datagrid({
            url: '<?= base_url('warehouse/Ito_dashboard_rm/get_dashboard_datatables') ?>' + url,
            pagination: true,
            clientPaging: true, // Mengaktifkan pemotongan data di browser
            singleSelect: true,
            rownumbers: true,
            pageSize: 10,
            pageList: [10, 20, 50,100, 500],
            fitColumns : true,
            
            // Definisi Kolom berdasarkan JSON kamu
            columns: [[
                {
                    field: 'prodfam', 
                    title: 'Product Family', 
                    width: 250,
                    halign: 'center',
                    sortable: false
                },
                {
                    field: 'ending_stock', 
                    title: 'Stock (In Qty)', 
                    width: 250,
                    halign: 'center',
                    align: 'right',
                    sortable: false,
                    formatter: function(value, row, index){
                        if(value){
                            // Menampilkan angka dengan format ribuan
                            return parseFloat(value).toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                        return '0.00';
                    }
                },
                {
                    field: 'qty_in_amount', 
                    title: 'Stock (In Million)', 
                    width: 250,
                    halign: 'center',
                    align: 'right',
                    sortable: false,
                    formatter: function(value, row, index){
                        if(value){
                            // Menampilkan angka dengan format ribuan
                            return parseFloat(value).toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                        return '0.00';
                    }
                },
                {
                    field: 'avg_3month', 
                    title: 'Delivery (3 Month)', 
                    width: 250,
                    halign: 'center',
                    align: 'right',
                    sortable: false,
                    formatter: function(value, row, index){
                        if(value){
                            // Menampilkan angka dengan format ribuan
                            return parseFloat(value).toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                        return '0.00';
                    }
                },
                {
                    field: 'ito_month', 
                    title: 'ITO Month', 
                    width: 250,
                    halign: 'center',
                    align: 'right',
                    sortable: false,
                    formatter: function(value, row, index){
                        if(value){
                            // Menampilkan angka dengan format ribuan
                            return parseFloat(value).toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                        return '0.00';
                    }
                },
                {
                    field: 'ito_days', 
                    title: 'ITO Days', 
                    width: 250,
                    halign: 'center',
                    align: 'right',
                    sortable: false,
                    formatter: function(value, row, index){
                        if(value){
                            // Menampilkan angka dengan format ribuan
                            return parseFloat(value).toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                        return '0.00';
                    }
                },
                {
                    field: 'pembagi', 
                    title: 'pembagi', 
                    width: 100,
                    halign: 'center',
                    align: 'right',
                    sortable: false,
                    hidden: true,
                    formatter: function(value, row, index){
                        if(value){
                            // Menampilkan angka dengan format ribuan
                            return parseFloat(value).toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                        return '0.00';
                    }
                }
            ]],

            // Fungsi khusus agar pagination "Client Side" bisa memotong data dari URL
            loadFilter: function(data) {
                // 1. Ambil Grand Total tepat saat data mentah sampai dari server
                if (data.grandtotal_qty_in_amount !== undefined && !data.originalRows) {
                    var gTotal = data.grandtotal_qty_in_amount;
                    
                    // Format angka (Desimal Indonesia)
                    var formatted_amount = parseFloat(gTotal).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    // Tembak ke div
                    $('#total_in_amount').text(formatted_amount);
                }

                if (data.grandtotal_qty !== undefined && !data.originalRows) {
                    var gTotal_qty = data.grandtotal_qty;
                    
                    // Format angka (Desimal Indonesia)
                    var formatted_qty = parseFloat(gTotal_qty).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    // Tembak ke div
                    $('#total_in_qty').text(formatted_qty);
                }

                // 2. Logika Standar Client-Side Pagination
                if ($.isArray(data)) {
                    data = {
                        total: data.length,
                        rows: data
                    };
                }
                
                var dg = $(this);
                var opts = dg.datagrid('options');
                var pager = dg.datagrid('getPager');

                if (!data.originalRows) {
                    data.originalRows = (data.rows);
                }

                pager.pagination({
                    onSelectPage: function(pageNum, pageSize) {
                        opts.pageNumber = pageNum;
                        opts.pageSize = pageSize;
                        pager.pagination('refresh', {
                            pageNumber: pageNum,
                            pageSize: pageSize
                        });
                        // Me-load ulang data yang tersimpan di memori
                        dg.datagrid('loadData', data);
                    }
                });
                
                var start = (opts.pageNumber - 1) * parseInt(opts.pageSize);
                var end = start + parseInt(opts.pageSize);
                
                // Potong data untuk tampilan paging
                data.rows = (data.originalRows.slice(start, end));

                loadcharts(data.pie_data,data.color,data.bar_data);
                
                return data;
            }
        }).datagrid('enableFilter');

    }

    function loadcharts(data_pie,color,data_bar){
        // Contoh data hasil query SQL kamu tadi
        
        const itoData = data_pie;

        // 1. Sort Descending (Besar ke Kecil) dan ambil 5 teratas
        const top5Data = itoData
            .sort((a, b) => b.ito_days - a.ito_days) // Urutkan
            .slice(0, 5); // Ambil 5 data pertama

        // 2. Pecah data yang sudah di-sort & slice
        const labels = top5Data.map(item => item.prodfam);

        // Gunakan parseFloat().toFixed(2) agar angka tidak terlalu panjang di tooltip
        const series = top5Data.map(item => parseFloat(item.ito_days.toFixed(2)));
        console.log(color);

        const options_pie = {
        chart: {
            type: 'pie',
            height: 300,
            // Tambahkan blok toolbar di sini
            toolbar: {
                show: true,
                tools: {
                    download: true, // Ini yang memunculkan tombol download (ikon garis tiga)
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false
                },
                export: {
                    csv: {
                        filename: 'ITO_Days_Data',
                    },
                    svg: {
                        filename: 'ITO_Days_Chart',
                    },
                    png: {
                        filename: 'ITO_Days_Chart',
                    }
                }
            }
        },
        series: series,
        labels: labels,
        colors: color,
        legend: {
            position: 'bottom'
        },
        title: {
            text: 'ITO Days',
            align: 'center'
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val.toLocaleString(); // Tambahkan satuan biar lebih informatif
                }
            }
        }
    };

        renderchart('itodaysChart',options_pie);

        // Ambil data dari PHP
        const rawData = data_bar;

        // 1. Sort Descending berdasarkan ito_days
        // 2. Ambil Top 5
        const top5Bar = rawData
            .sort((a, b) => b.ito_days - a.ito_days)
            .slice(0, 5);

        // 3. Pecah menjadi format yang dikenali ApexCharts
        const categories = top5Bar.map(item => item.prodfam);
        const stockIn = top5Bar.map(item => parseFloat(item.stock_in).toFixed(2));
        const avg3Month = top5Bar.map(item => parseFloat(item.avg_3month).toFixed(2));
        const itoDays = top5Bar.map(item => parseFloat(item.ito_days).toFixed(2));

        var options_bar = {
        series: [
            // {
            //     name: 'ITO (Days)',
            //     data: itoDays
            // },
            {
                name: 'Avg 3 Month',
                data: avg3Month
            },
            {
                name: 'Stock In',
                data: stockIn
            }
        ],
        chart: {
            type: 'bar',
            height: 300, // Saya naikin tingginya supaya label prodfam tidak terlalu rapat
            toolbar: {
                show: true
            }
        },
        plotOptions: {
            bar: {
                horizontal: true, // Ubah jadi Horizontal
                barHeight: '80%', // Mengatur ketebalan batang bar
                dataLabels: {
                    position: 'top',
                },
            },
        },
        // Sesuaikan warna: Grey untuk ITO, Green untuk Avg, Blue untuk Stock
        colors: ['#A5A5A5', '#92D050', '#4472C4'], 
        dataLabels: {
            enabled: false 
        },
        stroke: {
            show: true,
            width: 1,
            colors: ['#fff']
        },
        xaxis: {
            categories: categories, // Prodfam akan muncul di sumbu Y (kiri)
            labels: {
                formatter: function (val) {
                    return val.toLocaleString();
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    fontWeight: 'bold'
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val.toLocaleString();
                }
            }
        },
        legend: {
            position: 'bottom', // Legend pindah ke bawah agar mirip gambar
            horizontalAlign: 'center'
        }
    };

    renderchart('rmtoChart',options_bar);
    }

    function renderchart(chartid,optionschart){
        $('#'+chartid).empty();
        var chart_bar = new ApexCharts(document.querySelector("#"+chartid), optionschart);
        chart_bar.render();
    }

</script>

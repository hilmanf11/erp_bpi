<div class="supplier-performance-container" style="background: white; padding: 20px; font-family: 'Calibri', 'Segoe UI', sans-serif; font-size: 13px; color: #000; width: 1000px; margin: 0 auto; border: 1px solid #ccc;">

    <!-- Filter Header Form -->
    <div class="no-print" style="margin-bottom: 20px; background: #f5f5f5; padding: 15px; border-radius: 6px; border: 1px solid #e0e0e0; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <label><b>Supplier:</b></label>
            <input id="filter_supplier" class="easyui-combobox" style="width: 250px; height: 30px;">
            
            <label><b>Year:</b></label>
            <input id="filter_year" class="easyui-numberbox" style="width: 80px; height: 30px;" value="<?= date('Y') ?>">

            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" onclick="loadPerformanceData()" >Generate Report</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" onclick="window.location.reload()" >Reload</a>
        </div>
        <div>
            <a href="javascript:void(0)" class="easyui-linkbutton" onclick="window.print()" ><i class="fa fa-print" style="margin-right:5px;"></i>Print Report</a>
        </div>
    </div>

    <!-- Header Sheet -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <tr>
            <td style="font-weight: bold; font-size: 14px; width: 70%;">PT. BANSHU PLASTIC INDONESIA</td>
            <td style="border: 2px solid #000; text-align: center; font-weight: bold; font-size: 12px; padding: 3px 10px;">FM-MCL-025-Rev-00</td>
        </tr>
        <tr>
            <td style="font-size: 11px;">Jl. Jababeka IV Blok J-12B Bekasi</td>
            <td></td>
        </tr>
        <tr>
            <td style="font-size: 11px; font-weight: bold;">Material Control Section</td>
            <td></td>
        </tr>
    </table>

    <div style="text-align: center; font-size: 18px; font-weight: bold; text-decoration: underline; margin: 15px 0 20px 0; letter-spacing: 1px;">
        SUPPLIER PERFORMANCE
    </div>

    <!-- Supplier Profile Section -->
    <div style="font-weight: bold; font-size: 11px; margin-bottom: 3px;">SUPPLIER PROFILE</div>
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; margin-bottom: 15px; font-size: 12px;">
        <tr>
            <td style="width: 15%; padding: 3px 6px; border-right: 1px solid #000; border-bottom: 1px solid #000;">Date</td>
            <td style="width: 35%; padding: 3px 6px; border-right: 2px solid #000; border-bottom: 1px solid #000;">: <span id="info_date"><?= date('d F Y') ?></span></td>
            <td style="width: 15%; padding: 3px 6px; border-right: 1px solid #000; border-bottom: 1px solid #000;">PIC Name</td>
            <td style="width: 35%; padding: 3px 6px; border-bottom: 1px solid #000;">: <span id="info_pic">-</span></td>
        </tr>
        <tr>
            <td style="padding: 3px 6px; border-right: 1px solid #000; border-bottom: 1px solid #000;">Supplier Name</td>
            <td style="padding: 3px 6px; border-right: 2px solid #000; border-bottom: 1px solid #000; font-weight: bold;">: <span id="info_supp_name">-</span></td>
            <td style="padding: 3px 6px; border-right: 1px solid #000; border-bottom: 1px solid #000;">Phone .</td>
            <td style="padding: 3px 6px; border-bottom: 1px solid #000;">: <span id="info_phone">-</span></td>
        </tr>
        <tr>
            <td style="padding: 3px 6px; border-right: 1px solid #000; vertical-align: top;" rowspan="3">Supplier Address</td>
            <td style="padding: 3px 6px; border-right: 2px solid #000; vertical-align: top;" rowspan="3">: <span id="info_address">-</span></td>
            <td style="padding: 3px 6px; border-right: 1px solid #000; border-bottom: 1px solid #000;">Fax</td>
            <td style="padding: 3px 6px; border-bottom: 1px solid #000;">: <span id="info_fax">-</span></td>
        </tr>
        <tr>
            <td style="padding: 3px 6px; border-right: 1px solid #000; border-bottom: 1px solid #000;">Product Type</td>
            <td style="padding: 3px 6px; border-bottom: 1px solid #000;">: <span id="info_product_type">Raw Material Injection</span></td>
        </tr>
        <tr>
            <td style="padding: 3px 6px; border-right: 1px solid #000;"></td>
            <td style="padding: 3px 6px;"></td>
        </tr>
    </table>

    <!-- Intro Text -->
    <div style="margin-bottom: 15px; line-height: 1.5;">
        Dengan hormat<br>
        Dengan ini kami informasikan mengenai hasil evaluasi performance supplier untuk <b id="text_supp_name">PT. -</b> berdasarkan data Total item PO dikurangi Actual kirim dan Achievement Quality pada bulan dikeluarkannya PO yaitu sebagai berikut :
    </div>

    <!-- Chart Container -->
    <div style="border: 2px solid #000; padding: 10px; margin-bottom: 15px; background: #fff;">
        <div id="perfChart" style="height: 260px; width: 100%;"></div>
    </div>

    <!-- Table Performance Grid -->
    <table id="tbl_performance" style="width: 100%; border-collapse: collapse; border: 2px solid #000; text-align: center; font-size: 11px; margin-bottom: 15px;">
        <thead>
            <tr style="background: #f0f0f0;">
                <th style="border: 1px solid #000; padding: 6px; width: 22%;">Bulan</th>
                <th style="border: 1px solid #000; padding: 6px; width: 6.5%;">Jan</th>
                <th style="border: 1px solid #000; padding: 6px; width: 6.5%;">Feb</th>
                <th style="border: 1px solid #000; padding: 6px; width: 6.5%;">Mar</th>
                <th style="border: 1px solid #000; padding: 6px; width: 6.5%;">Apr</th>
                <th style="border: 1px solid #000; padding: 6px; width: 6.5%;">May</th>
                <th style="border: 1px solid #000; padding: 6px; width: 6.5%;">Jun</th>
                <th style="border: 1px solid #000; padding: 6px; width: 6.5%;">Jul</th>
                <th style="border: 1px solid #000; padding: 6px; width: 6.5%;">Aug</th>
                <th style="border: 1px solid #000; padding: 6px; width: 6.5%;">Sept</th>
                <th style="border: 1px solid #000; padding: 6px; width: 6.5%;">Oct</th>
                <th style="border: 1px solid #000; padding: 6px; width: 6.5%;">Nov</th>
                <th style="border: 1px solid #000; padding: 6px; width: 6.5%;">Dec</th>
            </tr>
        </thead>
        <tbody>
            <tr id="row_delivery">
                <td style="border: 1px solid #000; padding: 5px; text-align: left; font-weight: bold;">Nilai Delivery (Qty)</td>
                <!-- 12 months td -->
            </tr>
            <tr id="row_quality">
                <td style="border: 1px solid #000; padding: 5px; text-align: left; font-weight: bold;">Nilai Quality</td>
            </tr>
            <tr id="row_sched_vs_del">
                <td style="border: 1px solid #000; padding: 5px; text-align: left; font-weight: bold;">Nilai Schedul VS Del</td>
            </tr>
            <tr id="row_total" style="font-weight: bold;">
                <td style="border: 1px solid #000; padding: 5px; text-align: left;">Total Nilai</td>
            </tr>
        </tbody>
    </table>

    <!-- Keterangan Grading -->
    <div style="font-size: 11px; margin-bottom: 10px;">
        <b>Keterangan:</b>
    </div>

    <table style="width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 15px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                <b>GRADE NILAI QTY DELIVERY</b>
                <table style="width: 100%; border-collapse: collapse; margin-top: 3px;">
                    <tr><td style="width: 45%;">Jika Qty Kirim 100 %</td><td>= Sangat Memuaskan</td></tr>
                    <tr><td>Jika Qty Kirim Antara 81 - 90 %</td><td>= Memuaskan</td></tr>
                    <tr><td>Jika Qty Kirim Antara 51 - 80 %</td><td>= Kurang Memuaskan</td></tr>
                    <tr><td>Jika Qty Kirim Antara 0 - 50 %</td><td>= Sangat Tidak Memuaskan</td></tr>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top;">
                &nbsp;
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top; padding-right: 10px; padding-top: 10px;">
                <b>GRADE NILAI QUALITY</b>
                <table style="width: 100%; border-collapse: collapse; margin-top: 3px;">
                    <tr><td style="width: 45%;">Jika Quality OK 100 %</td><td>= Sangat Memuaskan</td></tr>
                    <tr><td>Jika Quality OK Antara 81 - 90 %</td><td>= Memuaskan</td></tr>
                    <tr><td>Jika Quality OK Antara 51 - 80 %</td><td>= Kurang Memuaskan</td></tr>
                    <tr><td>Jika Quality OK Antara 0 - 50 %</td><td>= Sangat Tidak Memuaskan</td></tr>
                </table>
            </td>
            <!-- <td style="vertical-align: top; padding-top: 10px;">
                <b>GRADE NILAI SCHEDULE VS DEL</b>
                <table style="width: 100%; border-collapse: collapse; margin-top: 3px;">
                    <tr><td style="width: 55%;">Tepat dengan Schedule 100 %</td><td>= Sangat Memuaskan</td></tr>
                    <tr><td>Terlambat 1 Hari 90 %</td><td>= Memuaskan</td></tr>
                    <tr><td>Terlambat 2 Hari 80 %</td><td>= Kurang Memuaskan</td></tr>
                    <tr><td>Terlambat 3 Hari 50 %</td><td>= Sangat Tidak Memuaskan</td></tr>
                </table>
            </td> -->
        </tr>
    </table>

    <div style="font-size: 11px; margin-bottom: 20px; line-height: 1.4;">
        Kami berterimakasih atas kerjasamanya selama ini dan diharapkan agar di bulan mendatang aspek Delivery dan Quality yang telah baik dapat dipertahankan dan yang masih kurang baik harus lebih ditingkatkan agar mencapai target.
    </div>

    <!-- Signatures -->
    <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 15px;">
        <tr>
            <td style="width: 55%;"></td>
            <td style="width: 45%; text-align: right; padding-right: 20px;">Cikarang, <span id="sign_date"><?= date('d F Y') ?></span></td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px; text-align: center;">
        <tr>
            <td style="width: 20%; border: 1px solid #000; padding: 5px; font-weight: bold;">Menyetujui</td>
            <td style="width: 45%; border: 1px solid #000; padding: 5px; font-weight: bold;">Kolom Catatan Supplier</td>
            <td style="width: 17.5%; border: 1px solid #000; padding: 5px; font-weight: bold;">Diketahui</td>
            <td style="width: 17.5%; border: 1px solid #000; padding: 5px; font-weight: bold;">Dibuat</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 5px;">Supplier</td>
            <td style="border: 1px solid #000; height: 60px;"></td>
            <td style="border: 1px solid #000; vertical-align: bottom; padding-bottom: 5px;"></td>
            <td style="border: 1px solid #000; vertical-align: bottom; padding-bottom: 5px;"></td>
        </tr>
        <tr style="font-weight: bold;">
            <td style="border: 1px solid #000; padding: 4px;"></td>
            <td style="border: 1px solid #000; padding: 4px;"></td>
            <td style="border: 1px solid #000; padding: 4px;">Dicky</td>
            <td style="border: 1px solid #000; padding: 4px;">Indraswari</td>
        </tr>
    </table>

    <div style="font-size: 10px; margin-top: 10px; font-style: italic;">
        <b>Note:</b><br>
        Setelah diterima dan ditandatangani, mohon surat ini di fax kembali ke PT. BPI 021-8983 4491<br>
        atau E-Mail: mcl@banshuplastic.com
    </div>
</div>

<style>
    @media print {
        .no-print, .main-header, .sidebar, #toolbar, .panel-header { display: none !important; }
        body { background: white !important; padding: 0 !important; }
        .supplier-performance-container { border: none !important; width: 100% !important; padding: 0 !important; }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    var chartInstance = null;

    $(document).ready(function() {
        $('#filter_supplier').combobox({
            url: '<?= base_url('warehouse/supplier_performance/readSupplier') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Supplier...',
            onSelect: function(rec) {
                loadSupplierInfo(rec.id);
            }
        });
    });

    function loadSupplierInfo(supp_id) {
        $.getJSON('<?= base_url('warehouse/supplier_performance/get_supplier_info') ?>?supplier_id=' + supp_id, function(data) {
            if (data && data.name) {
                $('#info_supp_name, #text_supp_name').text(data.name);
                $('#info_address').text(data.address || '-');
                $('#info_phone').text(data.phone || '-');
                $('#info_fax').text(data.fax || '-');
                $('#info_pic').text(data.pic_name || '-');
            }
        });
    }

    function loadPerformanceData() {
        var supp_id = $('#filter_supplier').combobox('getValue');
        var year = $('#filter_year').numberbox('getValue');

        if (!supp_id) {
            $.messager.alert('Warning', 'Please select a supplier first!', 'warning');
            return;
        }

        $.getJSON('<?= base_url('warehouse/supplier_performance/get_performance_data') ?>?supplier_id=' + supp_id + '&year=' + year, function(res) {
            renderTable(res);
            renderChart(res);
        });
    }

    function renderTable(res) {
        var rowDel = '<td style="border: 1px solid #000; padding: 5px; text-align: left; font-weight: bold;">Nilai Delivery (Qty)</td>';
        var rowQual = '<td style="border: 1px solid #000; padding: 5px; text-align: left; font-weight: bold;">Nilai Quality</td>';
        var rowSched = '<td style="border: 1px solid #000; padding: 5px; text-align: left; font-weight: bold;">Nilai Schedul VS Del</td>';
        var rowTot = '<td style="border: 1px solid #000; padding: 5px; text-align: left;">Total Nilai</td>';

        for (var i = 0; i < 12; i++) {
            rowDel += '<td style="border: 1px solid #000; padding: 5px;">' + (res.delivery[i] !== null ? res.delivery[i] + '%' : '') + '</td>';
            rowQual += '<td style="border: 1px solid #000; padding: 5px;">' + (res.quality[i] !== null ? res.quality[i] + '%' : '') + '</td>';
            rowSched += '<td style="border: 1px solid #000; padding: 5px;">' + (res.sched_vs_del[i] !== null ? res.sched_vs_del[i] + '%' : '') + '</td>';
            rowTot += '<td style="border: 1px solid #000; padding: 5px;">' + (res.total[i] !== null ? res.total[i] + '%' : '') + '</td>';
        }

        $('#row_delivery').html(rowDel);
        $('#row_quality').html(rowQual);
        $('#row_sched_vs_del').html(rowSched);
        $('#row_total').html(rowTot);
    }

    function renderChart(res) {
        var options = {
            series: [
                { name: 'Nilai Delivery (Qty)', data: res.delivery.map(v => v !== null ? (v/100) : 0) },
                { name: 'Nilai Quality', data: res.quality.map(v => v !== null ? (v/100) : 0) },
                { name: 'Nilai Schedul VS Del', data: res.sched_vs_del.map(v => v !== null ? (v/100) : 0) }
            ],
            chart: {
                type: 'bar',
                height: 240,
                toolbar: { show: false },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 600
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '75%',
                    borderRadius: 3,
                    borderRadiusApplication: 'end'
                },
            },
            dataLabels: { enabled: false },
            colors: ['#a0bce6ff', '#E89090', '#74C686'], // Soft Pastel Colors
            stroke: { show: true, width: 3, colors: ['#ffffff'] },
            xaxis: {
                categories: res.categories,
                labels: { style: { fontSize: '10px', colors: '#333333' } },
                axisBorder: { show: true, color: '#cccccc' }
            },
            yaxis: {
                min: 0,
                max: 1.2,
                tickAmount: 3,
                labels: {
                    style: { fontSize: '10px', colors: '#333333' },
                    formatter: function(val) {
                        return val.toFixed(1).replace('.', ',');
                    }
                }
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '10px',
                markers: { radius: 3 }
            },
            fill: { opacity: 0.95 }
        };

        if (chartInstance) {
            chartInstance.destroy();
        }
        chartInstance = new ApexCharts(document.querySelector("#perfChart"), options);
        chartInstance.render();
    }
</script>

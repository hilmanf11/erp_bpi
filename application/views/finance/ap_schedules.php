<div id="f" class="easyui-panel" style="width:100%; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:30%;" id="filter_from" value="<?= date("Y-01-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier</span>
                <input style="width:60%;" id="filter_supplier" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Currency</span>
                <input style="width:60%;" id="filter_currency" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
    </div>
    <div style="margin-left: 10px; margin-bottom:5px;">
        <?= $button ?>
    </div>
</div>
<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;" data-options="fit: true">
    <iframe id="printout" src="" style="width: 100%; height: 70%; border: 0;"></iframe>
</div>
<script>
    // FILTER SHOW DATA
    function getFilterUrl() {
        const filters = {
            filter_from: $("#filter_from").datebox("getValue"),
            filter_to: $("#filter_to").datebox("getValue"),
            filter_currency: $("#filter_currency").combogrid("getValue"),
            filter_supplier: $("#filter_supplier").combogrid("getValue"),
        };

        const isDateRangeEmpty = filters.filter_from === "" || filters.filter_to === "";
        // const isSupplierEmpty = filters.filter_supplier === ""; // di EBWS per supplier
        if (isDateRangeEmpty) {
            toastr.warning("Please select Trans Date");
            return null; // Gagal validasi
        }

        let url = "?";
        // Gunakan URL encoding (window.btoa) hanya pada tanggal yang mungkin sensitif
        url += "filter_from=" + window.btoa(filters.filter_from) +
            "&filter_to=" + window.btoa(filters.filter_to) +
            "&filter_supplier=" + window.btoa(filters.filter_supplier);

        // Tambahkan filter lainnya
        for (const key in filters) {
            if (filters.hasOwnProperty(key) && key !== 'filter_from' && key !== 'filter_to' && key !== 'filter_supplier') {
                // Hindari pengiriman ulang filter_from dan filter_to
                url += `&${key}=${filters[key]}`;
            }
        }

        return url;
    }

    function validateDateRange() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");

        // Periksa apakah kedua tanggal sudah terisi
        if (filter_from === "" || filter_to === "") {
            return true; 
        }

        if (filter_from > filter_to) {
            toastr.error("Start Date cannot be larger than End Date!");
            return false; // Validasi Gagal
        }

        return true; // Validasi Sukses
    }

    // SHOW DATA
    function filter() {
        if (!validateDateRange()) {
            return; // Hentikan proses jika validasi tanggal gagal
        }

        const urlQuery = getFilterUrl();
        if (urlQuery === null) {
            return; // Hentikan jika validasi gagal
        }
        
        $("#printout").attr('src', '<?= base_url('finance/ap_schedules/print') ?>' + urlQuery);
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
    }

    // EXPORT EXCEL
    function excel() {
        if (!validateDateRange()) {
            return; // Hentikan proses jika validasi tanggal gagal
        }

        const urlQuery = getFilterUrl();
        if (urlQuery === null) {
            return; // Hentikan jika validasi gagal
        }

        window.location.assign('<?= base_url('finance/ap_schedules/print/excel') ?>' + urlQuery);
    }

    function filter_existing() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_supplier = $("#filter_supplier").combobox("getValue");
        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_supplier=" + filter_supplier;
        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Trans Date!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('finance/ap_schedules/print') ?>' + url);
        }
    }

    function excel_existing() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_supplier = $("#filter_supplier").combobox("getValue");
        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_supplier=" + filter_supplier;
        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Trans Date!");
        } else {
            window.location.assign('<?= base_url('finance/ap_schedules/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        $('#filter_supplier').combobox({
            url: '<?php echo base_url('master/suppliers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Supplier Name',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_currency").combogrid({
            url: '<?= base_url('finance/ap_schedules/readCurrencies/') ?>',
            panelWidth: 250,
            idField: 'name',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Currency",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'name',
                    title: 'Currency',
                    width: 100
                }, {
                    field: 'description',
                    title: 'Description',
                    width: 150
                }, ]
            ],
        });

    });

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
</script>

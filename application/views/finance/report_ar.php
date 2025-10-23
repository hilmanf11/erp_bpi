<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
                </div>
                
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" id="filter_customer" class="easyui-combogrid">
                </div>
                
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" id="filter_plant" class="easyui-combogrid">
                </div>
                
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Invoice No</span>
                    <input style="width:60%;" id="filter_sales_invoice" name="filter_sales_invoice" class="easyui-combogrid">
                </div>
                
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:60%;" id="filter_currency" class="easyui-combogrid">
                </div>
                
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Payment</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
                
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Display</span>
                    <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">
                        <option value="Detail">Detail</option>
                        <option value="Summary">Summary</option>
                    </select>
                </div>
            </div>
        </fieldset>
    </div>
    <div style="margin-left: 10px; margin-bottom:5px;">
        <?= $button ?>
    </div>
</div>
<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 450px; border: 0;"></iframe>
</div>
<script>
    // FILTER SHOW DATA
    function getFilterUrl() {
        const filters = {
            filter_from: $("#filter_from").datebox("getValue"),
            filter_to: $("#filter_to").datebox("getValue"),
            filter_customer: $("#filter_customer").combogrid("getValue"),
            filter_sales_invoice: $("#filter_sales_invoice").combogrid("getValue"),
            filter_currency: $("#filter_currency").combogrid("getValue"),
            filter_status: $("#filter_status").combobox("getValue"),
            filter_display: $("#filter_display").combobox("getValue"),
        };

        const isDateRangeEmpty = filters.filter_from === "" || filters.filter_to === "";
        // const isCustomerEmpty = filters.filter_customer === "";
        if (isDateRangeEmpty) {
            toastr.warning("Please select Trans Date");
            return null;
        }

        let url = "?";
        url += "filter_from=" + window.btoa(filters.filter_from) + 
            "&filter_to=" + window.btoa(filters.filter_to) +
            "&filter_sales_invoice=" + window.btoa(filters.filter_sales_invoice);

        // Tambahkan filter lainnya
        for (const key in filters) {
            if (filters.hasOwnProperty(key) && key !== 'filter_from' && key !== 'filter_to' && key !== 'filter_sales_invoice') {
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

    function validasteDisplay() {
        var filter_display = $("#filter_display").combobox("getValue");
        let display;
        if (filter_display == "Summary") {
            display = "print";
        } else {
            display = "print_detail"; // function detail dipisah dengan summary
        }

        return display;
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
        
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/report_ar/print') ?>' + urlQuery);
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

        window.location.assign('<?= base_url('finance/report_ar/print/excel') ?>' + urlQuery);
    }

    // PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    // RELOAD
    function reload() {
        window.location.reload();
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

    // DOCUMENT READY
    $(function() {

        $('#filter_customer').combogrid({
            url: '<?php echo base_url('master/customers/reads'); ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Customer',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combobox('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'id',
                    title: 'Customer ID',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Customer Name',
                    width: 300
                }, ]
            ],
            onSelect: function(index, row) {

                $("#filter_plant").combogrid({
                    url: '<?= base_url('finance/report_ar/readAddress/') ?>' + window.btoa(row.id),
                    panelWidth: 300,
                    idField: 'id',
                    textField: 'plant',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Plant",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'plant',
                            title: 'Plant Name',
                            width: 300
                        }, ]
                    ],
                });

                $("#filter_sales_invoice").combogrid({
                    url: '<?php echo base_url('finance/report_ar/readSi/'); ?>' + window.btoa(row.id),
                    panelWidth: 300,
                    idField: 'number',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Invoice No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'number',
                            title: 'Invoice No',
                            width: 300
                        }, ]
                    ],
                });
            }
        });

        $("#filter_currency").combogrid({
            url: '<?= base_url('master/currencies/reads') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Choose Currencies",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'name',
                    title: 'ID',
                    width: 40,
                }, {
                    field: 'description',
                    title: 'Description',
                    width: 120,
                }, ]
            ],
            onSelect: function (index, row) {
                if (row.name != null) {
                    $("#filter_currency").combogrid('setValue', row.name);
                }
            }
        });

         // -- disable filter lain jika filter_display=Summary
        $('#filter_display').combobox({
            onChange: function(newValue, oldValue) {
                if (newValue === 'Summary') {
                    // Menonaktifkan (disable) elemen saat 'Summary' dipilih
                    $('#filter_sales_invoice').combogrid('disable');
                    $('#filter_sales_invoice').combogrid('setValue', ''); // Kosongkan nilainya
                    $('#filter_plant').combogrid('disable');
                    $('#filter_plant').combogrid('setValue', ''); // Kosongkan nilainya
                } else {
                    // Mengaktifkan kembali elemen saat 'Detail' dipilih
                    $('#filter_sales_invoice').combogrid('enable');
                    $('#filter_plant').combogrid('enable');
                }
            }
        });
    });
</script>
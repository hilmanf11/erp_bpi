<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 40%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date('Y-01-01'); ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" class="easyui-datebox" data-options="prompt:'End Date',formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Posting No</span>
                    <input style="width:60%;" id="filter_posting_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>

            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" id="filter_document_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Invoice No</span>
                    <input style="width:60%;" id="filter_invoice_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Source</span>
                    <input style="width:60%;" id="filter_source" class="easyui-combogrid">
                </div>
            </div>
                
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:60%;" id="filter_currency" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
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
            filter_supplier: $("#filter_supplier").combogrid("getValue"),
            filter_posting_no: $("#filter_posting_no").combogrid("getValue"),
            filter_document_no: $("#filter_document_no").combogrid("getValue"),
            filter_invoice_no: $("#filter_invoice_no").combogrid("getValue"),
            filter_currency: $("#filter_currency").combogrid("getValue"),
            filter_status: $("#filter_status").combobox("getValue"),
            filter_source: $("#filter_source").combobox("getValue"),
            filter_display: $("#filter_display").combobox("getValue")
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
            "&filter_supplier=" + window.btoa(filters.filter_supplier) +
            "&filter_posting_no=" + window.btoa(filters.filter_posting_no) +
            "&filter_invoice_no=" + window.btoa(filters.filter_invoice_no) +
            "&filter_document_no=" + window.btoa(filters.filter_document_no);

        // Tambahkan filter lainnya
        for (const key in filters) {
            if (filters.hasOwnProperty(key) && key !== 'filter_from' && key !== 'filter_to' && key !== 'filter_supplier' 
                && key !== 'filter_posting_no' && key !== 'filter_invoice_no' && key !== 'filter_document_no') {
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

    function validateDisplay() {
        var filter_display = $("#filter_display").combobox("getValue");        
        let display;
        if (filter_display == "Summary") {
            display = "print"; 
        } else {
            // display = "print_detail"; // function detail dipisah dengan summary
            display = "print";           // same function
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

        let displayFunction = validateDisplay();
        if (displayFunction == "print_detail") {
            $("#printout").attr('src', '<?= base_url('finance/report_ap/print_detail') ?>' + urlQuery);
        } else {
            $("#printout").attr('src', '<?= base_url('finance/report_ap/print') ?>' + urlQuery);
        }

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

        let displayFunction = validateDisplay();
        if (displayFunction == "print_detail") {
            window.location.assign('<?= base_url('finance/report_ap/print_detail/excel') ?>' + urlQuery);
        } else {
            window.location.assign('<?= base_url('finance/report_ap/print/excel') ?>' + urlQuery);
        }        
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

        $("#filter_supplier").combogrid({
            url: '<?= base_url('finance/report_ap/readSuppliers/') ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'id',
                    title: 'Supplier ID',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 300
                }, ]
            ],
            onSelect: function(index, supplier) {
                
                $("#filter_posting_no").combogrid({
                    url: '<?= base_url('finance/report_ap/readPostingNo/') ?>' + window.btoa(supplier.name),
                    panelWidth: 300,
                    idField: 'number',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Posting No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'number',
                            title: 'Posting No',
                            width: 150
                        }, {
                            field: 'journal_date',
                            title: 'Posting Date',
                            width: 150
                        }, ]
                    ],
                });

                $("#filter_document_no").combogrid({
                    url: '<?= base_url('finance/report_ap/readDocumentNo/') ?>' + window.btoa(supplier.name),
                    panelWidth: 350,
                    idField: 'document_no',
                    textField: 'document_no',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Document No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'document_no',
                            title: 'Document No',
                            width: 175
                        }, {
                            field: 'journal_date',
                            title: 'Posting Date',
                            width: 175
                        }, ]
                    ],
                });

                $("#filter_invoice_no").combogrid({
                    url: '<?= base_url('finance/report_ap/readInvoiceNo/') ?>' + window.btoa(supplier.name),
                    panelWidth: 400,
                    idField: 'invoice_no',
                    textField: 'invoice_no',
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
                            field: 'invoice_no',
                            title: 'Invoice No',
                            width: 200
                        }, {
                            field: 'modul',
                            title: 'Modul',
                            width: 200
                        }, ]
                    ],
                });

            }
        });

        $("#filter_source").combogrid({
            panelWidth: 200,
            idField: 'id',
            textField: 'name',
            data: [{
                id: 'PI',
                name: 'PURCHASE INVOICING'
            }, {
                id: 'AP',
                name: 'AP PAYMENT'
            }],          
            fitColumns: true,
            prompt: "Choose Source",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'name',
                    title: 'Name',
                    width: 150
                }, ]
            ],
        });

        $("#filter_currency").combogrid({
            url: '<?= base_url('finance/report_ap/readCurrencies/') ?>',
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

        // -- disable filter lain jika filter_display=Summary
        // $('#filter_display').combobox({
        //     onChange: function(newValue, oldValue) {
        //         if (newValue === 'Summary') {
        //             // Menonaktifkan (disable) elemen saat 'Summary' dipilih
        //             $('#filter_posting_no').textbox('disable');
        //             $('#filter_posting_no').textbox('setValue', ''); // Kosongkan nilainya
        //             $('#filter_document_no').textbox('disable');
        //             $('#filter_document_no').textbox('setValue', ''); // Kosongkan nilainya
        //             $('#filter_invoice_no').textbox('disable');
        //             $('#filter_invoice_no').textbox('setValue', ''); // Kosongkan nilainya
        //         } else {
        //             // Mengaktifkan kembali elemen saat 'Detail' dipilih
        //             $('#filter_posting_no').textbox('enable');
        //             $('#filter_document_no').textbox('enable');
        //             $('#filter_invoice_no').textbox('enable');
        //         }
        //     }
        // });

    });
</script>

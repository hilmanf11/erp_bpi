
<!-- EDIT NON MANDATORY DATA -->
<div id="dlg_tax" class="easyui-dialog" style="width:600px;padding:10px 20px" closed="true" buttons="#dlg-buttons-tax" modal="true">
    <form id="fm_tax" method="post" novalidate>
        <div style="width: 100%; overflow: hidden;">
            <div class="fitem" hidden>
                <input id="data_voucher" name="data_voucher" class="easyui-textbox">
                <input id="data_company_id" name="data_company_id" class="easyui-textbox">
                <input id="data_kode_cabang" name="data_kode_cabang" class="easyui-textbox">
                <input id="data_type" name="data_type" class="easyui-textbox">
                <input id="data_bc1" name="data_bc1" class="easyui-textbox">
                <input id="data_bc2" name="data_bc2" class="easyui-textbox">
                <input id="data_bc3" name="data_bc3" class="easyui-textbox">
                <input id="data_bc4" name="data_bc4" class="easyui-textbox">
            </div>

            <div class="fitem" style="margin-bottom: 5px;">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%;" id="data_remarks" name="data_remarks" class="easyui-textbox">
            </div>

            <div class="fitem" style="margin-bottom: 5px;">
                <span style="width:35%; display:inline-block;">Kode Faktur Pajak</span>
                <input style="width:30%;" id="data_faktur_code" name="data_faktur_code" class="easyui-combobox" data-options="
                    valueField: 'id',
                    textField: 'text',
                    panelHeight: 'auto'
                ">
            </div>

            <div class="fitem" style="margin-bottom: 5px;">
                <span style="width:35%; display:inline-block;">FP Pengganti</span>
                <select style="width:30%;" id="data_fp_pengganti" name="data_fp_pengganti" class="easyui-combobox" data-options="panelHeight:'auto'">
                    <option value="00">00</option>
                    <option value="01">01</option>
                </select>
            </div>

            <div class="fitem" style="margin-bottom: 5px;">
                <span style="width:35%; display:inline-block;">Faktur No</span>
                <input style="width:30%;" id="data_faktur_no" name="data_faktur_no" readonly class="easyui-textbox" prompt="Auto">
            </div>

            <div class="fitem" style="margin-bottom: 5px;">
                <span style="width:35%; display:inline-block;">No Seri Faktur Pajak</span>
                <input style="width:10%;" id="data_kode_trans" name="data_kode_trans" readonly class="easyui-textbox">
                <input style="width:8%;" id="data_tahun_pemeriksaan" name="data_tahun_pemeriksaan" readonly class="easyui-textbox">
                <input style="width:25%;" id="data_no_urut" name="data_no_urut" class="easyui-textbox" required="true" prompt="Nomor Urut">
            </div>

            <div class="fitem" style="margin-bottom: 5px;">
                <span style="width:35%; display:inline-block;">BC No</span>
                <input style="width:60%;" id="data_bc_no" name="data_bc_no" class="easyui-textbox" data-options="prompt:'Number Only'">
            </div>

            <div class="fitem" style="margin-bottom: 5px;">
                <span style="width:35%; display:inline-block;">Keterangan Tambahan</span>
                <input style="width:60%;" id="data_keterangan_tambahan" name="data_keterangan_tambahan" required class="easyui-combogrid">
            </div>

            <div class="fitem" style="margin-bottom: 5px;">
                <span style="width:35%; display:inline-block;">Cap Fasilitas</span>
                <input style="width:60%;" id="data_cap_fasilitas" name="data_cap_fasilitas" required class="easyui-combogrid">
            </div>

            <div class="fitem" style="margin-bottom: 5px;">
                <span style="width:35%; display:inline-block;">Payment To</span>
                <input style="width:60%;" id="data_payment_to" name="data_payment_to" class="easyui-combobox">
            </div>
        </div>
    </form>
</div>

<div id="dlg-buttons-tax">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveTaxData()" style="width:90px">Save</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg_tax').dialog('close')" style="width:90px">Cancel</a>
</div>


<script>
    function editTaxData() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.gl_no && row.gl_no.trim() !== "") {
                
                // Buka dialog dan bersihkan form
                $('#dlg_tax').dialog('open').dialog('setTitle', 'Edit Tax Data of ' + (row.number || row.gl_no)).dialog('center');
                $('#fm_tax').form('clear');

                // Load data manual untuk field dengan prefix data_
                $('#data_voucher').textbox('setValue', row.voucher);
                $('#data_company_id').textbox('setValue', row.company_id);
                $('#data_remarks').textbox('setValue', row.remarks);
                $('#data_fp_pengganti').combobox('setValue', row.fp_pengganti || '00');
                $('#data_faktur_no').textbox('setValue', row.faktur_no);
                $('#data_kode_trans').textbox('setValue', row.kode_trans);
                $('#data_tahun_pemeriksaan').textbox('setValue', row.tahun_pemeriksaan);
                $('#data_no_urut').textbox('setValue', row.no_urut);
                $('#data_bc_no').textbox('setValue', row.bc_no);
                
                // Dropdown data with Ajax
                $("#data_faktur_code").combobox({
                    url: '<?= base_url('finance/sales_invoices/readFakturCode?id=') ?>' + row.customer_id,
                    valueField: 'value',
                    textField: 'text',
                    prompt: "Choose Faktur Code",
                    onLoadSuccess: function(data) {
                        if (data && data.length > 0 && data[0].faktur_code) {
                            var fakturCodes = data[0].faktur_code.split(',');
                            var fakturData = fakturCodes.map(function(code) {
                                return { value: code.trim(), text: code.trim() };
                            });
                            $('#data_faktur_code').combobox('loadData', fakturData);
                            $('#data_faktur_code').combobox('setValue', row.faktur_code);
                        }
                    }
                });

                $('#data_keterangan_tambahan').combogrid({
                    panelWidth: 600,
                    idField: 'value',
                    textField: 'description',
                    data: [
                        { value: "Tidak Ada", description: "Tidak Ada" },
                        { value: "TD.00501", description: "1 - Pajak Pertambahan Nilai Tidak Dipungut berdasarkan PP Nomor 10 Tahun 2012" },
                        { value: "TD.00502", description: "2 - Pajak Pertambahan Nilai atau Pajak Pertambahan Nilai dan Pajak Penjualan atas Barang Mewah tidak dipungut" },
                        { value: "TD.00503", description: "3 - Pajak Pertambahan Nilai dan Pajak Penjualan atas Barang Mewah Tidak Dipungut" },
                    ],
                    columns: [[
                        { field: 'value', title: 'Kode', width: 100 },
                        { field: 'description', title: 'Keterangan', width: 480 }
                    ]],
                    fitColumns: true,
                    prompt: "Choose Keterangan Tambahan",
                    editable: false
                });
                if (row.keterangan_tambahan) {
                    $('#data_keterangan_tambahan').combogrid('setValue', row.keterangan_tambahan);
                } else {
                    $('#data_keterangan_tambahan').combogrid('setValue', 'Tidak Ada');
                }


                $('#data_cap_fasilitas').combogrid({
                    panelWidth: 400,
                    idField: 'value',
                    textField: 'description',
                    data: [
                        { value: "Tidak Ada", description: "Tidak Ada" },
                        { value: "TD.01101", description: "1 - Untuk Kawasan Bebas" },
                        { value: "TD.01102", description: "2 - Untuk Tempat Penimbunan Berikat" },
                    ],
                    columns: [[
                        { field: 'value', title: 'Kode', width: 100 },
                        { field: 'description', title: 'Keterangan', width: 300 }
                    ]],
                    fitColumns: true,
                    prompt: "Cap Fasilitas",
                    editable: false,
                });
                if (row.cap_fasilitas) {
                    $('#data_cap_fasilitas').combogrid('setValue', row.cap_fasilitas);
                } else {
                    $('#data_cap_fasilitas').combogrid('setValue', 'Tidak Ada');
                }

                $("#data_payment_to").combogrid({
                    url: '<?= base_url('finance/sales_invoices/readPayment') ?>',
                    panelWidth: 450,
                    idField: 'bank_name',
                    textField: 'bank_name',
                    mode: 'remote',
                    prompt: "Choose Payment",
                    columns: [
                        [{
                            field: 'no',
                            title: 'No',
                            width: 80
                        }, {
                            field: 'bank_name',
                            title: 'Bank Name',
                            width: 200,
                            align: 'left'
                        }, {
                            field: 'bank_account',
                            title: 'Bank Account',
                            width: 150,
                            align: 'left'
                        }]
                    ],
                });
                if (row.payment_to) {
                    $('#data_payment_to').combogrid('setValue', row.payment_to);
                } else {
                    $('#data_payment_to').combogrid('setValue', '');
                }

                // Generate Faktur No (Read Only)
                var updateFullFakturNo = function() {
                    var kodeTrans = $("#data_kode_trans").textbox('getValue');
                    var noUrut = $("#data_no_urut").textbox('getValue');
                    var fullNo = kodeTrans + noUrut; 
                    $("#data_faktur_no").textbox('setValue', fullNo);
                };

                // Generate Kode Trans (Faktur Code + FP Pengganti)
                var updateKodeTrans = function() {
                    var code = $("#data_faktur_code").combobox('getValue');
                    var sub = $("#data_fp_pengganti").combobox('getValue');
                    $("#data_kode_trans").textbox('setValue', code + sub);
                    // Update Nomor Faktur Lengkap
                    updateFullFakturNo();
                };

                // Ubah dengan Trigger saat mengetik
                $("#data_no_urut").textbox({
                    validType: 'length[1,11]',
                    inputEvents: $.extend({}, $.fn.textbox.defaults.inputEvents, {
                        keyup: function(e) {
                            var value = $(this).val();
                            
                            // Limit 11 karakter
                            if (value.length > 11) {
                                value = value.slice(0, 11);
                                $(this).val(value);
                            }
                            $("#data_no_urut").textbox('setValue', value);

                            // Update Nomor Faktur Lengkap
                            updateFullFakturNo();
                        }
                    })
                });

                // Ganti on change di Combobox
                $("#data_faktur_code").combobox({ onChange: updateKodeTrans });
                $("#data_fp_pengganti").combobox({ onChange: updateKodeTrans });

            } else {
                toastr.error("Cannot Update! This Sales Invoice has not been posted to Journal.");
            }
        } else {
            $.messager.alert('Info', 'Please select a Sales Invoice!', 'info');
        }
    }

    function saveTaxData() {
        var row = $('#dg').datagrid('getSelected');
        if (!row) return;

        if (!$('#fm_tax').form('validate')) return;

        // Bandingkan perubahan
        var fieldsMapping = [
            {db: 'remarks', id: 'data_remarks'},
            {db: 'faktur_code', id: 'data_faktur_code'},
            {db: 'fp_pengganti', id: 'data_fp_pengganti'},
            {db: 'no_urut', id: 'data_no_urut'},
            {db: 'bc_no', id: 'data_bc_no'},
            {db: 'keterangan_tambahan', id: 'data_keterangan_tambahan'},
            {db: 'cap_fasilitas', id: 'data_cap_fasilitas'},
            {db: 'payment_to', id: 'data_payment_to'}
        ];
        
        var isChanged = false;
        fieldsMapping.forEach(function(item) {
            var current = $('#' + item.id).textbox('getValue');
            var old = row[item.db] || "";
            if ($.trim(current) !== $.trim(old)) isChanged = true;
        });

        if (!isChanged) {
            $.messager.alert('Info', 'Tidak ada perubahan data.', 'info');
            return;
        }

        $.messager.prompt('Reason Required', 'Alasan perubahan:', function(r){
            if (r) {
                $.ajax({
                    url: '<?= base_url('your_path/update_tax_info') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        voucher: $('#data_voucher').textbox('getValue'),
                        reason: r,
                        remarks: $('#data_remarks').textbox('getValue'),
                        faktur_code: $('#data_faktur_code').combobox('getValue'),
                        fp_pengganti: $('#data_fp_pengganti').combobox('getValue'),
                        no_urut: $('#data_no_urut').textbox('getValue'),
                        bc_no: $('#data_bc_no').textbox('getValue'),
                        keterangan_tambahan: $('#data_keterangan_tambahan').combogrid('getValue'),
                        cap_fasilitas: $('#data_cap_fasilitas').combogrid('getValue'),
                        payment_to: $('#data_payment_to').combobox('getValue')
                    },
                    beforeSend: function() { $.messager.progress({title:'Please wait'}); },
                    success: function(res) {
                        $.messager.progress('close');
                        if(res.success) {
                            $('#dlg_tax').dialog('close');
                            $('#dg').datagrid('reload');
                            toastr.success("Updated!");
                        } else {
                            $.messager.alert('Error', res.errorMsg, 'error');
                        }
                    }
                });
            }
        });
    }
</script>

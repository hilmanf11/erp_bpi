<div id="p" class="easyui-panel" title="New Barcode" style="width:100%;padding:10px;background:#fafafa;" data-options="closable:true,collapsible:true">
    <div style="width: 58%; float: left; margin-right: 10px;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Create New Barcode</b></legend>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Cut Off</span>
                <input style="width:70%;" id="cut_off_date" name="cut_off_date" class="easyui-datebox">
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Category</span>
                <input style="width:70%;" id="item_category_id" name="item_category_id" class="easyui-combobox" required>
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Product Family</span>
                <input style="width:70%;" id="item_family_id" name="item_family_id" class="easyui-combobox" required>
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Sub Product Family</span>
                <input style="width:70%;" id="item_sub_family_id" name="item_sub_family_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Part No</span>
                <input style="width:70%;" id="item_rm_id" name="item_rm_id" class="easyui-combobox" >
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Stock</span>
                <input style="width:50%;" id="historical_stock" name="historical_stock" class="easyui-textbox" readonly>
                <input style="width:20%;" id="historical_uom" name="historical_uom" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">MPQ</span>
                <input style="width:40%;" id="mpq" name="mpq" class="easyui-numberbox" data-options="precision:2" required>
                <input style="width:30%;" id="qty_label" name="qty_label" label="Qty label" class="easyui-numberbox" readonly >
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="saved()"><i class="fa fa-save"></i> Save </a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print()"><i class="fa fa-print"></i> Print Label </a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="reload()"><i class="fa fa-rotate-right"></i> Reload</a>
            </div>
        </fieldset>
    </div>
    <div style="width: 40%; float: right;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Preview Barcode Label</b></legend>
            <iframe id="printout" src="" style="width: 100%; height: 285px; border: 0;"></iframe>
        </fieldset>
    </div>
</div>
<script>
    function reload() {
        window.location.reload();
    }

    function stock(item_rm_id, cut_off_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('warehouse/new_barcode/stock/') ?>" + window.btoa(item_rm_id) + "/" + window.btoa(cut_off_date),
            dataType: "html",
            success: function(result) {
                $("#historical_stock").textbox('setValue', result);
            }
        });
    }

    function mpq(item_rm_id) {
        $.ajax({
            type: "post",
            url: "<?= base_url('warehouse/new_barcode/itemMpq/') ?>" + window.btoa(item_rm_id),
            dataType: "json",
            success: function(result) {
                if(result == null){
                    $("#mpq").numberbox('setValue', 0);
                    $("#qty_label").numberbox('setValue', 0);
                }else{
                    $("#mpq").numberbox('setValue', result.mpq);
                    var historical_stock = $("#historical_stock").textbox('getValue');
                    var mpq = result.mpq; // Gunakan nilai result.mpq dari server, bukan dari textbox

                    $("#qty_label").numberbox('setValue', Math.ceil(historical_stock / mpq));
                }
                
            }
        });
    }

    $(document).ready(function() {
        $("#mpq").numberbox({
            onChange: function(newValue, oldValue) {
                var historical_stock = parseFloat($("#historical_stock").textbox('getValue'));
                var mpq = parseFloat(newValue);

                // Pastikan kedua nilai tidak NaN
                if (!isNaN(historical_stock) && !isNaN(mpq)) {
                    // Cek jika nilai MPQ lebih besar dari stok historis
                    if (mpq > historical_stock) {
                        // Tampilkan pesan error
                        toastr.error("Cannot Process your Request");
                        // Kembalikan nilai MPQ ke nilai sebelumnya
                        $(this).numberbox('setValue', oldValue);
                        return;
                    }
                    
                    // Hitung ulang qty_label
                    $("#qty_label").numberbox('setValue', (historical_stock / mpq));
                }
            }
        });
    });

    function saved() {
        var item_rm_id = $("#item_rm_id").combobox('getValue');
        var stock = $("#historical_stock").textbox('getValue');
        var uom = $("#historical_uom").textbox('getValue');
        var mpq = $("#mpq").numberbox('getValue');
        var qty_label = $("#qty_label").numberbox('getValue');
        var cut_off_date = $("#cut_off_date").datebox('getValue');

        $.ajax({
            type: "POST",
            url: "<?= base_url('warehouse/new_barcode/create') ?>",
            data: "&item_rm_id=" + item_rm_id +
                "&stock=" + stock + 
                "&uom=" + uom + 
                "&mpq=" + mpq +
                "&qty_label=" + qty_label + 
                "&cut_off_date=" + cut_off_date,
            dataType: "json",
            success: function(result) {
                if (result.theme == "success") {
                    toastr.success(result.message, result.title);
                } else {
                    toastr.error(result.message, result.title);
                }

                var url = "?item_rm_id=" + window.btoa(item_rm_id) + "&cut_off_date=" + window.btoa(cut_off_date);
                $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
                $("#printout").attr('src', '<?= base_url('warehouse/new_barcode/print') ?>' + url);
            }
        });
    }

    function print() {
        $("#printout").get(0).contentWindow.print();
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    $(document).ready(function() {
        // Set formatter untuk datebox
        $('#cut_off_date').datebox({
            formatter: function(date){
                var y = date.getFullYear();
                var m = date.getMonth() + 1;
                var d = date.getDate();
                return y + '-' + (m<10?('0'+m):m) + '-' + (d<10?('0'+d):d);
            },
            parser: function(s){
                if (!s) return new Date();
                var ss = s.split('-');
                var y = parseInt(ss[0],10);
                var m = parseInt(ss[1],10);
                var d = parseInt(ss[2],10);
                if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
                    return new Date(y,m-1,d);
                } else {
                    return new Date();
                }
            }
        });

        // Mendapatkan tanggal hari ini
        var today = new Date();
        var formattedDate = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
        
        // Set nilai tanggal ke dalam elemen datebox
        $('#cut_off_date').datebox('setValue', formattedDate);
    });

    $('#item_category_id').combobox({
        url:'<?= base_url('master/item_categories/readsnotfg'); ?>',
        valueField:'id',
        textField:'name',
        prompt: 'Choose Category',
        onSelect: function(category){
            $('#item_family_id').combobox({
                url:'<?= base_url('master/item_familys/reads/'); ?>' + category.id,
                valueField:'id',
                textField:'name',
                prompt: 'Choose Product Family',
                onSelect: function(family) {
                    $('#item_rm_id').combobox({
                        url:'<?= base_url('warehouse/new_barcode/readItemrmnosub/'); ?>' + category.id + "/" + family.id ,
                        valueField:'id',
                        textField:'number',
                        prompt: 'Choose Part No',
                        onSelect: function(item_rm) {
                            var cut_off_date = $("#cut_off_date").datebox('getValue');
                            stock(item_rm.id, cut_off_date);

                            Swal.fire({
                                title: 'Please Wait for Calculating Label',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            setTimeout(function (){
                                mpq(item_rm.id);
                                Swal.close();
                            }, 3000);

                            $("#historical_uom").textbox('setValue', item_rm.uom);
                        }
                    });

                    $('#item_sub_family_id').combobox({
                        url:'<?= base_url('master/item_family_subs/reads/'); ?>' + family.id,
                        valueField:'id',
                        textField:'name',
                        editable: false,
                        prompt: 'Choose Sub Product Family',
                        onSelect: function(subfamily) {
                            $('#item_rm_id').combobox({
                                url:'<?= base_url('warehouse/new_barcode/readItemrm/'); ?>' + category.id + "/" + family.id + "/" + subfamily.id,
                                valueField:'id',
                                textField:'number',
                                prompt: 'Choose Part No',
                                onSelect: function(item_rm) {
                                    var cut_off_date = $("#cut_off_date").datebox('getValue');
                                    stock(item_rm.id, cut_off_date);

                                    Swal.fire({
                                        title: 'Please Wait for Calculating Label',
                                        showConfirmButton: false,
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        },
                                    });

                                    setTimeout(function (){
                                        mpq(item_rm.id);
                                        Swal.close();
                                    }, 3000);

                                    $("#historical_uom").textbox('setValue', item_rm.uom);
                                }
                            });
                        }
                    });
                }
            });
        }
    });

</script>
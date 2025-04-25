<style>
    .scan {
        width: 100%;
        padding: 12px 20px;
        margin: 8px 0;
        display: inline-block;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 40px !important;
    }
</style>
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'label_no',halign:'center',width:200">Serial No</th>
            <th rowspan="2" data-options="field:'item_number',width:140,halign:'center'">Part No</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'">Part Name</th>
            <th rowspan="2" data-options="field:'division',width:100,halign:'center'">Division</th>
            <th rowspan="2" data-options="field:'category',width:100,halign:'center'">Category</th>
            <th rowspan="2" data-options="field:'product_family',width:100,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'uom',width:80,halign:'center'">Uom</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center'">Qty</th>
            <th rowspan="2" data-options="field:'location',width:100,halign:'center'">Location</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> STO</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 400px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; padding: 10px;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Scan Barcode</b></legend>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="display:inline-block;">Trans Date : <b><?= date("d F Y") ?></b> | Receive By : <b><?= $this->session->name ?></b></span>
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">Division</span>
                <input style="width:20%;" id="filter_division" class="easyui-combobox">
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">Category</span>
                <input style="width:20%;" id="filter_category_id" class="easyui-combobox">
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">Product Family</span>
                <input style="width:20%;" id="filter_product_family" class="easyui-combobox">
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <span style="width:20%; display:inline-block;">Part No</span>
                <input style="width:20%;" id="filter_items" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Period</span>
                <input style="width:10%;" id="period_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Select Month'">
                <input style="width:10%;" id="period_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Select Year'" panelHeight="auto">
            </div>
            <div class="fitem" style="padding:10 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="label_no" name="label_no" class="scan" placeholder="SCAN LABEL HERE">
            </div>
            <div class="fitem" style="padding:10 200px 10px 200px;">
                <a href="javascript:;" class="easyui-linkbutton" onclick="reload()"><i class="fa fa-rotate-right"></i> Reload</a>
            </div>
        </fieldset>
    </div>
</div>
<audio id="serialDuplicate">
    <source src="<?= base_url('assets/audio/serial_duplicate.mpeg') ?>" type="audio/mpeg">
</audio>
<audio id="serialSuccess">
    <source src="<?= base_url('assets/audio/serial_success.mpeg') ?>" type="audio/mpeg">
</audio>
<audio id="serialNotFound">
    <source src="<?= base_url('assets/audio/serial_notfound.mpeg') ?>" type="audio/mpeg">
</audio>
<audio id="serialMismatch">
    <source src="<?= base_url('assets/audio/serial_mismatch.mp3') ?>" type="audio/mp3">
</audio>
<script>
    function reload() {
        window.location.reload();
    }
    $(function() {
        //Audio Config
        var serialDuplicate = document.getElementById("serialDuplicate");
        var serialSuccess = document.getElementById("serialSuccess");
        var serialNotFound = document.getElementById("serialNotFound");
        var serialMismatch = document.getElementById("serialMismatch");
        //Scan Label
        $('#label_no').focus();
        $('#label_no').keypress(function(e) {
            if (e.which == 13) {
                var label_no = $("#label_no").val();
                console.log(label_no);

                var category = $("#filter_category_id").combobox('getValue');
                var prod_fam = $("#filter_product_family").combobox('getValue');
                var division = $("#filter_division").combobox('getValue');
                var period_month = $("#period_month").combobox('getValue');
                var period_year = $("#period_year").combobox('getValue');
                
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('warehouse/sto_raw_materials/getPoReceipt') ?>",
                    data: "label_no=" + label_no,
                    dataType: "json",
                    success: function(json) {
                        if (json.total > 0) {
                            var row = json.rows;
                            console.log(row);
                            for (let i = 0; i < json.total; i++) {
                                $.ajax({
                                    type: "POST",
                                    url: "<?= base_url('warehouse/sto_raw_materials/create') ?>",
                                    data: "label_no=" + label_no +
                                        "&category_id=" + category +
                                        "&product_family_id=" + prod_fam +
                                        "&division=" + division +
                                        "&item_rm_id=" + row[i].item_rm_id +
                                        "&period_month=" + period_month +
                                        "&period_year=" + period_year +
                                        "&uom=" + row[i].uom +
                                        "&qty=" + row[i].qty,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            serialSuccess.play();
                                            toastr.success(result.message, result.title);
                                            $("#label_no").val('');
                                            $('#label_no').focus();
                                        } else {
                                            if (result.title == "Available") {
                                                toastr.error(result.message, result.title);
                                                serialDuplicate.play();
                                            } else if (result.title == "Mismatch"){
                                                toastr.error(result.message, result.title);
                                                serialMismatch.play();
                                            }else{
                                                toastr.error(result.message, result.title);
                                            }
                                            $("#label_no").val('');
                                            $('#label_no').focus();
                                        }
                                    }
                                });
                            }
                            $('#dg').datagrid({
                                url: '<?= base_url('warehouse/sto_raw_materials/datatables/') ?>' + window.btoa(label_no),
                                rownumbers: true
                            });
                        } else {
                            serialNotFound.play();
                            toastr.warning("Label not found!");
                            $("#label_no").val('');
                        }
                    }
                });
            }
        });
    });

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    } 

    $('#filter_division').combobox({
        url: '<?= base_url('warehouse/sto_raw_materials/readsDivision/'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: 'Choose Division',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_category_id').combobox({
        url: '<?= base_url('warehouse/sto_raw_materials/readsNotfg'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Category',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onSelect: function(category) {
            $('#filter_product_family').combobox({
                url: '<?= base_url('warehouse/sto_raw_materials/readsCategory/'); ?>' + category.id,
                valueField: 'id',
                textField: 'name',
                prompt: 'Choose Product Family',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
            });
        }
    });

    $(document).ready(function() {
        function reloadData() {
            $('#dg').datagrid('load', {
                period_month: $('#period_month').combobox('getValue'),
                period_year: $('#period_year').combobox('getValue'),
                filter_items: $('#filter_items').combogrid('getValue')
            });
        }

        $('#dg').datagrid({
            url: '<?= base_url('warehouse/sto_raw_materials/datatables/') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            method: 'get',
            fitColumns: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            queryParams: {
                period_month: $('#period_month').combobox('getValue'),
                period_year: $('#period_year').combobox('getValue'),
                filter_items: $('#filter_items').combogrid('getValue')
            }
        });

        // Trigger reload saat filter berubah
        $('#period_month, #period_year').combobox({
            onChange: function() {
                reloadData();
            }
        });

        $('#filter_items').combogrid({
            onChange: function() {
                reloadData();
            }
        });

        $('#filter_items').combogrid({
            url: '<?= base_url('master/item_rm/reads/') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Part No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],

            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }, ]
            ]
        });

        // Tombol untuk apply filter (Opsional jika ingin tombol khusus)
        // $('#apply_filter').click(function() {
        //     reloadData();
        // });
    });

    // $(document).ready(function() {
    //     $('#dg').datagrid({
    //         url: '<?= base_url('warehouse/sto_raw_materials/datatables/') ?>',
    //         rownumbers: true,
    //         method: 'get',
    //         // pagination: true, // Jika ingin menggunakan pagination
    //         fitColumns: true, // Agar kolom otomatis menyesuaikan
    //         // pageList: [20, 50, 100, 500, 1000],
    //         // pageSize: 20,
    //     });
    // });

    $("#period_month").combobox({
        url: '<?= base_url('planning/production_schedules/readMonth') ?>',
        valueField: 'number',
        textField: 'name',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $("#period_year").combobox({
        url: '<?= base_url('planning/production_schedules/readYear') ?>',
        valueField: 'number',
        textField: 'number',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

</script>
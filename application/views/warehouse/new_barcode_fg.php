<div id="p" class="easyui-panel" title="New Barcode FG" style="width:100%;padding:10px;background:#fafafa;" data-options="closable:true,collapsible:true">
    <div style="width: 58%; float: left; margin-right: 10px;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Create New Barcode FG</b></legend>
            <div class="fitem">
            <span style="width:20%; display:inline-block;">Label No</span>
                <select style="width:70%;" name="label_type" id="label_type" required="" panelHeight="auto" class="easyui-combobox">
                    <option value="">Choose Label No</option>
                    <option value="auto">Auto</option>
                    <option value="manual">Manual</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Cut Off</span>
                <input style="width:70%;" id="cut_off_date" name="cut_off_date" required="" class="easyui-datebox">
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Product No</span>
                <input style="width:70%;" id="item_fg_id" name="item_fg_id" required="" class="easyui-combobox" >
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Document No</span>
                <input style="width:70%;" id="number" name="number" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Product Name</span>
                <input style="width:70%;" id="item_fg_name" name="item_fg_name" readonly class="easyui-textbox" >
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Prod Date</span>
                <input style="width:70%;" name="prod_date" id="prod_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Packing Date</span>
                <input style="width:70%;" name="packing_date" id="packing_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Lot No</span>
                <input style="width:70%;" id="lot_no" name="lot_no" required="" class="easyui-textbox" >
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Cust Code</span>
                <input style="width:70%;" id="item_fg_cust_code" name="item_fg_cust_code" readonly class="easyui-textbox" >
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">QC 1</span>
                <input style="width:70%;" name="qc_1" id="qc_1"  required="" class="easyui-combobox">
            </div>
            <div class="fitem" hidden>
                <span style="width:20%; display:inline-block;">QC 1</span>
                <input style="width:70%;" name="qcnumber_1" id="qcnumber_1" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">QC 2</span>
                <input style="width:70%;" name="qc_2" id="qc_2" class="easyui-combobox">
            </div>
            <div class="fitem" hidden>
                <span style="width:20%; display:inline-block;">QC 2</span>
                <input style="width:70%;" name="qcnumber_2" id="qcnumber_2" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Operator 1</span>
                <input style="width:70%;" name="op_1" id="op_1" required="" class="easyui-combobox">
            </div>
            <div class="fitem" hidden>
                <span style="width:20%; display:inline-block;">OP 1</span>
                <input style="width:70%;" name="opnumber_1" id="opnumber_1" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Operator 2</span>
                <input style="width:70%;" name="op_2" id="op_2" class="easyui-combobox">
            </div> 
            <div class="fitem" hidden>
                <span style="width:20%; display:inline-block;">OP 2</span>
                <input style="width:70%;" name="opnumber_2" id="opnumber_2" class="easyui-textbox">
            </div>
            <div class="fitem">
            <span style="width:20%; display:inline-block;">Shift</span>
                <select style="width:70%;" name="shift" id="shift" required="" panelHeight="auto" class="easyui-combobox">
                    <option value="">Choose Shift</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>
            </div>
            <div class="fitem" id="qty_stock_fg_textbox">
                <span style="width:20%; display:inline-block;">Qty Stock FG</span>
                <input style="width:70%;" id="qty_stock_fg" name="qty_stock_fg" readonly= "" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Qty Stock</span>
                <input style="width:70%;" id="qty" name="qty" class="easyui-textbox">
            </div>
            <div class="fitem" id="accumulate_numberbox">
                <span style="width:20%; display:inline-block;">Accumulate</span>
                <input style="width:70%;" id="accumulate" name="accumulate" readonly class="easyui-numberbox">
            </div>
            <div class="fitem" id="balance_numberbox">
                <span style="width:20%; display:inline-block;">Balance Qty</span>
                <input style="width:70%;" name="balance" id="balance" readonly class="easyui-numberbox">
            </div>
            <div class="fitem">
            <span style="width:20%; display:inline-block;">Packing Qty</span>
                <select style="width:50%;" name="packing" id="packing" required="" panelHeight="auto" class="easyui-combobox">
                    <option value="">Choose Packing</option>
                    <option value="1">MPQ</option>
                    <option value="2">Qty per BOX</option>
                    <option value="3">User Entry</option>
                </select>
                <input style="width:20%;" name="packing_qty" id="packing_qty" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:20%; display:inline-block;">Qty Label</span>
                <input style="width:70%;" name="qty_label" id="qty_label" readonly class="easyui-numberbox">
            </div>
            <div class="fitem" id="label_textbox">
                <span style="width:20%; display:inline-block;">Label No</span>
                <input style="width:70%;" name="label_no" id="label_no" class="easyui-textbox">
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
            <iframe id="printout" src="" style="width: 100%; height: 580px; border: 0;"></iframe>
        </fieldset>
    </div>
</div>
<script>
    function reload() {
        window.location.reload();
    }

    function saved() {
        var cut_off_date = $("#cut_off_date").datebox('getValue');
        var item_fg_id = $("#item_fg_id").combobox('getValue');
        var prod_date = $("#prod_date").datebox('getValue');
        var packing_date = $("#packing_date").datebox('getValue');
        var lot_no = $("#lot_no").textbox('getValue');
        var qc_1 = $("#qc_1").combobox('getValue');
        var qc_2 = $("#qc_2").combobox('getValue');
        var op_1 = $("#op_1").combobox('getValue');
        var op_2 = $("#op_2").combobox('getValue');
        var qcnumber_1 = $("#qcnumber_1").textbox('getValue');
        var qcnumber_2 = $("#qcnumber_2").textbox('getValue');
        var opnumber_1 = $("#opnumber_1").textbox('getValue');
        var opnumber_2 = $("#opnumber_2").textbox('getValue');
        var number = $("#number").combogrid('getValue');//berubah
        var shift = $("#shift").combobox('getValue');
        var qty = $("#qty").textbox('getValue');
        var packing = $("#packing").combobox('getValue');
        var packing_qty = $("#packing_qty").textbox('getValue');
        var qty_label = $("#qty_label").numberbox('getValue');
        var label_no = $("#label_no").textbox('getValue');
        var label_type = $("#label_type").combobox('getValue');

        $.ajax({
            type: "POST",
            url: "<?= base_url('warehouse/new_barcode_fg/create') ?>",
            data: "&item_fg_id=" + item_fg_id +
                "&cut_off_date=" + cut_off_date + 
                "&prod_date=" + prod_date + 
                "&packing_date=" + packing_date + 
                "&lot_no=" + lot_no + 
                "&qc_1=" + qc_1 + 
                "&qc_2=" + qc_2 + 
                "&op_1=" + op_1 + 
                "&op_2=" + op_2 + 
                "&qcnumber_1=" + qcnumber_1 + 
                "&qcnumber_2=" + qcnumber_2 + 
                "&opnumber_1=" + opnumber_1 + 
                "&opnumber_2=" + opnumber_2 + 
                "&number=" + number + //berubah
                "&shift=" + shift + 
                "&qty=" + qty + 
                "&packing=" + packing +
                "&packing_qty=" + packing_qty + 
                "&qty_label=" + qty_label + 
                "&label_type=" + label_type + 
                "&label_no=" + label_no,
            dataType: "json",
            success: function(result) {
                if (result.theme == "success") {
                    toastr.success(result.message, result.title);
                } else {
                    toastr.error(result.message, result.title);
                }

                if(label_type == 'manual'){
                    var url = "?item_fg_id=" + window.btoa(item_fg_id) + "&cut_off_date=" + window.btoa(cut_off_date) + "&label_no=" + window.btoa(label_no);
                }else{
                    var url = "?item_fg_id=" + window.btoa(item_fg_id) + "&cut_off_date=" + window.btoa(cut_off_date) + "&number=" + window.btoa(number);
                }

                $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
                $("#printout").attr('src', '<?= base_url('warehouse/new_barcode_fg/print') ?>' + url);
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
        $("#label_textbox").hide();
        $("#qty_stock_fg_textbox").hide();

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

        // // Mendapatkan tanggal hari ini
        // var today = new Date();
        // var formattedDate = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
        
        // // Set nilai tanggal ke dalam elemen datebox
        // $('#cut_off_date').datebox('setValue', formattedDate);


        $('#packing').combobox({
            onSelect: function(record) {
                var item_fg_id = $("#item_fg_id").textbox("getValue");
                $.ajax({
                    url: '<?= base_url("planning/checksheets/readItems/") ?>' + window.btoa(item_fg_id),
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);
                        var packingQty = '';
                        if (record.value == 1) {
                            packingQty = data[0].mpq;
                        } else if (record.value == 2) {
                            packingQty = data[0].qty_box;
                        }
                        $('#packing_qty').textbox('setValue', packingQty);
                    }
                });
            }
        });

        // $("#packing_qty").textbox({
        //     onChange: function(newValue, oldValue) {
        //         var stock_qty = parseFloat($("#qty").textbox('getValue'));
        //         var packing_qty = parseFloat(newValue);

        //         if (!isNaN(stock_qty) && !isNaN(packing_qty)) {
        //             if (packing_qty > stock_qty) {
        //                 toastr.error("Cannot Process your Request");
        //                 $(this).numberbox('setValue', oldValue);
        //                 return;
        //             }
                    
        //             $("#qty_label").numberbox('setValue', (stock_qty / packing_qty));
        //         }
        //     }
        // });

        $("#packing_qty").textbox({
            onChange: function(newValue, oldValue) {
                var stock_qty = parseFloat($("#qty").textbox('getValue'));
                var packing_qty = parseFloat(newValue);

                if (!isNaN(stock_qty) && !isNaN(packing_qty)) {
                    if (packing_qty > stock_qty) {
                        toastr.error("Stock Qty < Packing Qty");
                        $(this).numberbox('setValue', oldValue);

                        $("#qty_label").numberbox('clear');
                        return;
                    }
                    
                    $("#qty_label").numberbox('setValue', Math.ceil(stock_qty / packing_qty));
                }
            }
        });

    });

    // $('#cut_off_date').datebox({
    //     onSelect: function(date){
    //         var y = date.getFullYear();
    //         var m = date.getMonth() + 1;
    //         var d = date.getDate();
    //         var formattedDate =  y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    //         $('#item_fg_id').combobox({
    //             url:'<?= base_url('warehouse/new_barcode_fg/readItem/'); ?>' + btoa(formattedDate),
    //             valueField:'id',
    //             textField:'number',
    //             prompt: 'Choose Product No',
    //             onSelect: function(item_fg) {
    //                 // var cut_off_date = $("#cut_off_date").datebox('getValue');
    //                 // stock(item_rm.id, cut_off_date);

    //                 // Swal.fire({
    //                 //     title: 'Please Wait for Calculating Label',
    //                 //     showConfirmButton: false,
    //                 //     allowOutsideClick: false,
    //                 //     allowEscapeKey: false,
    //                 //     didOpen: () => {
    //                 //         Swal.showLoading();
    //                 //     },
    //                 // });

    //                 // setTimeout(function (){
    //                 //     mpq(item_rm.id);
    //                 //     Swal.close();
    //                 // }, 3000);

    //                 $("#item_fg_name").textbox('setValue', item_fg.name);
    //                 $("#item_fg_cust_code").textbox('setValue', item_fg.number_customer);
    //                 $("#qty").textbox('setValue', item_fg.qty);
    //                 $("#qty_stock_fg").textbox('setValue', item_fg.qty);

    //                 var date = $("#cut_off_date").datebox("getValue");
    //                 var item_fg_id = $("#item_fg_id").combobox("getValue");

    //                 $.ajax({
    //                     url: '<?= base_url("warehouse/new_barcode_fg/checkAcc/") ?>' + window.btoa(date) +"/"+ window.btoa(item_fg.id), 
    //                     method: 'GET',
    //                     dataType: 'json',
    //                     success: function(data) {
    //                         console.log(data);
    //                         accumulateAjax = data[0].qty;
    //                         $("#accumulate").numberbox('setValue', data[0].qty);
    //                     }
    //                 });

    //                 $('#qty').numberbox({
    //                     onChange: function(value) {
    //                         if(value != ""){
    //                             var qty = $("#qty_stock_fg").numberbox("getValue");
    //                             var receipt = $("#qty").numberbox('getValue');

    //                             var calculate = parseInt(receipt) + parseInt(accumulateAjax);
    //                             var result = parseInt(qty) - parseInt(calculate);

    //                             var balance = $("#balance").numberbox('setValue', result);
    //                             var accumulate_total = $("#accumulate").numberbox('setValue', calculate);

    //                             if (result < 0) {
    //                                 toastr.warning("Balance minus, please correct your Qty!");
    //                                 $("#qty").numberbox('setValue', 0);
    //                                 $("#accumulate").numberbox('setValue', accumulate);
    //                             } else {
    //                                 return result;
    //                             }
    //                         }else{
    //                             $("#receipt").numberbox('setValue', 0);
    //                         }
    //                     }
    //                 });
    //             }
    //         });
    //     }
    // });

    $('#cut_off_date').datebox({
        onSelect: function(date){
            var y = date.getFullYear();
            var m = date.getMonth() + 1;
            var d = date.getDate();
            var formattedDate =  y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
            $('#item_fg_id').combobox({
                url:'<?= base_url('warehouse/new_barcode_fg/readItem/'); ?>' + btoa(formattedDate),
                valueField:'id',
                textField:'number',
                prompt: 'Choose Product No',
                onSelect: function(item_fg) {
                    $('#number').combogrid({
                        url: '<?= base_url('warehouse/new_barcode_fg/readDocument/') ?>' + btoa(item_fg.id),
                        panelWidth: 300,
                        idField: 'os_fg_number',
                        textField: 'os_fg_number',
                        mode: 'remote',
                        fitColumns: true,
                        prompt: "Choose Document No",
                        columns: [
                            [{
                                field: 'os_fg_number',
                                title: 'Document',
                                width: 150
                            }, {
                                field: 'qty',
                                title: 'Qty',
                                width: 100,
                                align: 'left'
                            }]
                        ],
                        onSelect: function(val, row) {
                            $("#item_fg_name").textbox('setValue', row.item_name);
                            $("#item_fg_cust_code").textbox('setValue', row.item_number_customer);
                            $("#qty").textbox('setValue', row.qty);
                            $("#qty_stock_fg").textbox('setValue', row.qty);

                            var date = $("#cut_off_date").datebox("getValue");
                            var item_fg_id = $("#item_fg_id").combobox("getValue");

                            $.ajax({
                                url: '<?= base_url("warehouse/new_barcode_fg/checkAcc/") ?>' + window.btoa(date) +"/"+ window.btoa(item_fg_id), 
                                method: 'GET',
                                dataType: 'json',
                                success: function(data) {
                                    console.log(data);
                                    accumulateAjax = data[0].qty;
                                    $("#accumulate").numberbox('setValue', data[0].qty);
                                }
                            });

                            $('#qty').numberbox({
                                onChange: function(value) {
                                    if(value != ""){
                                        var qty = $("#qty_stock_fg").numberbox("getValue");
                                        var receipt = $("#qty").numberbox('getValue');

                                        var calculate = parseInt(receipt) + parseInt(accumulateAjax);
                                        var result = parseInt(qty) - parseInt(calculate);

                                        var balance = $("#balance").numberbox('setValue', result);
                                        var accumulate_total = $("#accumulate").numberbox('setValue', calculate);

                                        if (result < 0) {
                                            toastr.warning("Balance minus, please correct your Qty!");
                                            $("#qty").numberbox('setValue', 0);
                                            $("#accumulate").numberbox('setValue', accumulate);
                                        } else {
                                            return result;
                                        }
                                    }else{
                                        $("#receipt").numberbox('setValue', 0);
                                    }
                                }
                            });
                        }
                    });
                }
            });
        }
    });
    
    $('#qc_1').combobox({
        url: '<?= base_url('warehouse/new_barcode_fg/readEmployesQC'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Employees',
        onSelect: function(qc) {
            $("#qcnumber_1").textbox('setValue', qc.nik);
        }
    });
    $('#qc_2').combobox({
        url: '<?= base_url('warehouse/new_barcode_fg/readEmployesQC'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Employees',
        onSelect: function(qc) {
            $("#qcnumber_2").textbox('setValue', qc.nik);
        }
    });
    $('#op_1').combobox({
        url: '<?= base_url('warehouse/new_barcode_fg/readEmployesOP'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Employees',
        onSelect: function(qc) {
            $("#opnumber_1").textbox('setValue', qc.nik);
        }
    });
    $('#op_2').combobox({
        url: '<?= base_url('warehouse/new_barcode_fg/readEmployesOP'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Employees',
        onSelect: function(qc) {
            $("#opnumber_2").textbox('setValue', qc.nik);
        }
    });

    $("#label_type").combobox({
        onChange: function(val) {
            if (val == "manual") {
                $("#qty").textbox('enable');
                $("#qty").textbox('setValue','0');
                $("#label_textbox").show();
                $("#qty_stock_fg_textbox").show();
                $("#accumulate_numberbox").show();
                $("#balance_numberbox").show();
            } else {
                $("#label_textbox").hide();
                $("#qty_stock_fg_textbox").hide();
                $("#accumulate_numberbox").hide();
                $("#balance_numberbox").hide();
                $("#qty").textbox('disable');
            }
        }
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
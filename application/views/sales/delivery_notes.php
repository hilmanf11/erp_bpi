<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'delivery_note_no',width:150,halign:'center'">Delivery Note No.</th>
            <th rowspan="2" data-options="field:'delivery_note_date',width:100,halign:'center'">Delivery Date</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'shipping_address',width:500,halign:'center'">Shipping Address</th>
            <!-- <th rowspan="2" data-options="field:'trans_type',width:100,halign:'center'">Transaction<br>Type</th> -->
            <th rowspan="2" data-options="field:'note',width:150,halign:'center'">Note</th>
            <!-- <th rowspan="2" data-options="field:'status_delivery',width:100,align:'center', styler:cellStyler, formatter:cellFormatterDeliveryStatus">Delivery Status</th> -->
            <th rowspan="2" data-options="field:'status',width:80,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:120,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:120,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 270px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" id="filter_customer_id" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Note No</span>
                    <input style="width:60%;" id="filter_delivery_note_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order No</span>
                    <input style="width:60%;" id="filter_delivery_order_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Order No</span>
                    <input style="width:60%;" id="filter_sales_order_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Order No</span>
                    <input style="width:60%;" id="filter_customer_order_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Status</span>
                    <select style="width:60%;" id="filter_status_delivery" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose All</option>
                        <option value="0">ON SCHEDULE</option>
                        <option value="1">DELAY</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add Delivery Note" data-options="closed: true,modal:true" style="width: 1200px; height: 600px; padding:10px; top: 20px; left: 50px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
            <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Date</span>
                    <input style="width:60%;" name="delivery_note_date" id="delivery_note_date" required="" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Note No.</span>
                    <input style="width:60%;" name="delivery_note_no" id="delivery_note_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" name="division" id="division" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Shipping Address</span>
                    <input style="width:60%;" name="address_id" id="address_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order No</span>
                    <input style="width:60%;" name="delivery_order_no" id="delivery_order_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Police No.</span>
                    <input style="width:60%;" name="police_no" id="police_no" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Driver Name</span>
                    <input style="width:60%;" name="driver_name" id="driver_name" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" id="btnPreview" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
            <div class="fitem">
                    <span style="width:35%; display:inline-block;">Country of Origin</span>
                    <input style="width:60%;" name="origin" id="origin" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sailing on or about</span>
                    <input style="width:60%;" name="sailing" id="sailing" required="" class="easyui-textbox">
                </div>
            <div class="fitem">
                    <span style="width:35%; display:inline-block;">Ship By</span>
                    <select style="width:60%;" name="ship_by" id="ship_by" required class="easyui-combobox" panelHeight="auto">
                        <option value="SEA">SEA</option>
                        <option value="AIR">AIR</option>
                        <option value="TRUCK">TRUCK</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Incoterm</span>
                    <select style="width:60%;" name="incoterm" id="incoterm" required class="easyui-combobox" panelHeight="auto">
                        <option value="NONE">NONE</option>
                        <option value="CIF">CIF</option>
                        <option value="FOB">FOB</option>
                        <option value="EXW">EXW</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Note</span>
                    <input style="width:60%; height: 100px;" name="note" id="note" class="easyui-textbox" multiline="true">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" name="status" id="status" required class="easyui-combobox" panelHeight="auto">
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <!-- <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Delivery Order Lists" toolbar="#toolbar2"></table> -->
        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Purchase Order List" idField="item_number">
            <thead>
                <tr>
                    <th field="ck" checkbox="true"></th>
                    <th data-options="field:'status_delivery',width:100,halign:'center', styler:cellStyler, formatter:cellFormatterDeliveryStatus">Status</th>
                    <th data-options="field:'delivery_order_no',width:150,halign:'center'">Delivery Order No</th>
                    <th data-options="field:'item_fg_id',width:150,halign:'center'">Product ID</th>
                    <th data-options="field:'item_fg_number',width:150,halign:'center'">Product No</th>
                    <th data-options="field:'item_fg_name',width:150,halign:'center'">Product Name</th>
                    <th data-options="field:'sales_order_no',width:150,halign:'center'">Sales <br>Order No</th>
                    <th data-options="field:'customer_order_no',width:150,halign:'center'">Customer <br>Order No</th>
                    <th data-options="field:'uom',width:80,halign:'center'">Uom</th>
                    <th data-options="field:'qty',width:80,editor:{type:'numberbox'},halign:'center'">Qty</th>
                </tr>
            </thead>
        </table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('sales/delivery_notes/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('sales/delivery_notes/create') ?>';
        $('#frm_insert').form('clear');

        $('#status').combobox('setValue', '0');

        $("#customer_id").combobox('enable');
        $("#delivery_note_date").datebox('enable');
        $("#delivery_note_no").textbox('enable');
        $("#origin").combobox('enable');
        $("#sailing").textbox('enable');
        $("#ship_by").textbox('enable');
        $("#incoterm").textbox('enable');
        $("#status_delivery").textbox('enable');
        $("#delivery_order_no").textbox('enable');
        $("#division").textbox('enable');
        $("#address_id").combobox('enable');
        $("#btnPreview").linkbutton('enable');

    }

    function number(delivery_note_date, divison_number, customer_number) {
        $.ajax({
            type: "post",
            url: "<?= base_url('sales/delivery_notes/number/') ?>" + btoa(delivery_note_date) + "/" + btoa(divison_number),
            data: { customer_number: btoa(customer_number) }, // Kirim customer_number sebagai data POST
            dataType: "html",
            success: function(result) {
                $("#delivery_note_no").textbox('setValue', result);
            }
        });
    }


    // function addTable(customer_id, link = "") {
    //     $('#dg2').datagrid({
    //         url: link,
    //         singleSelect: true,
    //         columns: [
    //             [{
    //                 field: 'delivery_order_no',
    //                 width: 150,
    //                 halign: 'center',
    //                 title: "Delivery Order No.",
    //                 editor: {
    //                     type: 'combogrid',
    //                     options: {
    //                         url: '<?= base_url('sales/delivery_notes/readDo/'); ?>' + customer_id,
    //                         required: true,
    //                         panelWidth: 200,
    //                         idField: 'delivery_order_no',
    //                         textField: 'delivery_order_no',
    //                         mode: 'remote',
    //                         fitColumns: true,
    //                         prompt: 'Choose Delivery Order No.',
    //                         columns: [
    //                             [{
    //                                 field: 'delivery_order_no',
    //                                 title: 'Delivery Order No.',
    //                                 width: 200
    //                             }]
    //                         ],
    //                         onSelect: function(value, rows) {
                               
    //                             var dg = $('#dg2');
    //                             var row = dg.datagrid('getSelected');
    //                             var rowIndex = dg.datagrid('getRowIndex', row);

    //                             var ed = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'delivery_order_no'
    //                             });
    //                             var ed2 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'item_fg_id'
    //                             });
    //                             var ed3 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'item_fg_number'
    //                             });
    //                             var ed4 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'item_fg_name'
    //                             });
    //                             var ed5 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'sales_order_no'
    //                             });
    //                             var ed6 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'customer_order_no'
    //                             });
    //                             var ed7 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'uom'
    //                             });
    //                             var ed8 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'trans_type'
    //                             });
                               

    //                             $(ed.target).textbox('setValue', rows.delivery_order_no);
    //                             $(ed2.target).combogrid({
    //                                 url: '<?= base_url('sales/delivery_notes/readDo/'); ?>' + customer_id,
    //                                 required: true,
    //                                 panelWidth: 200,
    //                                 idField: 'id',
    //                                 textField: 'id',
    //                                 mode: 'remote',
    //                                 fitColumns: true,
    //                                 prompt: 'Choose Product ID',
    //                                 columns: [
    //                                     [{
    //                                         field: 'id',
    //                                         title: 'Product ID',
    //                                         width: 150
    //                                     }]
    //                                 ],
    //                                 onSelect: function(val, id) {
    //                                     $(ed3.target).textbox('setValue', rows.number);
    //                                     $(ed4.target).textbox('setValue', rows.name);
    //                                     $(ed5.target).textbox('setValue', rows.sales_order_no);
    //                                     $(ed6.target).textbox('setValue', rows.customer_order_no);
    //                                     $(ed7.target).textbox('setValue', rows.uom);
    //                                     $(ed8.target).textbox('setValue', rows.trans_type);
    //                                 }
    //                             });

    //                         }
    //                     }
    //                 }
    //             }, {
    //                 field: 'item_fg_id',
    //                 width: 170,
    //                 halign: 'center',
    //                 title: "Product ID",
    //                 editor: {
    //                     type: 'combobox',
    //                     options: {
    //                         readonly: true
    //                     }
    //                 }
    //             }, {
    //                 field: 'item_fg_number',
    //                 width: 150,
    //                 halign: 'center',
    //                 title: "Product No",
    //                 editor: {
    //                     type: 'textbox',
    //                     options: {
    //                         readonly: true
    //                     }
    //                 }
    //             }, {
    //                 field: 'item_fg_name',
    //                 width: 150,
    //                 halign: 'center',
    //                 title: "Product Name",
    //                 editor: {
    //                     type: 'textbox',
    //                     options: {
    //                         readonly: true
    //                     }
    //                 }
    //             }, {
    //                 field: 'sales_order_no',
    //                 width: 150,
    //                 halign: 'center',
    //                 title: "Sales Order No",
    //                 editor: {
    //                     type: 'textbox',
    //                     options: {
    //                         readonly: true
    //                     }
    //                 }
    //             }, {
    //                 field: 'customer_order_no',
    //                 width: 150,
    //                 halign: 'center',
    //                 title: "Customer Order No",
    //                 editor: {
    //                     type: 'textbox',
    //                     options: {
    //                         readonly: true
    //                     }
    //                 }
    //             }, {
    //                 field: 'trans_type',
    //                 width: 80,
    //                 halign: 'center',
    //                 title: "Transaction Type",
    //                 editor: {
    //                     type: 'textbox',
    //                     options: {
    //                         readonly: true
    //                     }
    //                 }
    //             }, {
    //                 field: 'uom',
    //                 width: 80,
    //                 halign: 'center',
    //                 title: "UoM",
    //                 editor: {
    //                     type: 'textbox',
    //                     options: {
    //                         readonly: true
    //                     }
    //                 }
    //             }, {
    //                 field: 'qty',
    //                 width: 80,
    //                 halign: 'center',
    //                 title: "QTY",
    //                 editor: {
    //                     type: 'numberbox',
    //                     options: {
    //                         readonly: true
    //                     }
    //                 }
    //             }]
    //         ],
    //         onClickCell: onClickCell
    //     });
    // }

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg2').datagrid('validateRow', editIndex)) {
            $('#dg2').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function onClickCell(index, field) {
        if (editIndex != index) {
            if (endEditing()) {
                $('#dg2').datagrid('selectRow', index).datagrid('beginEdit', index);
                editIndex = index;
            } else {
                setTimeout(function() {
                    $('#dg2').datagrid('selectRow', editIndex);
                }, 0);
            }
        }
    }

    function append() {
        var customer_id = $("#customer_id").combobox('getValue');
        if (customer_id != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0',
                    delivery: '0',
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Customer Name first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return true;
        }

        var dg = $('#dg2');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: editIndex,
            field: 'item_fg_id'
        });

        var delivery_note_no = $("#delivery_note_no").textbox('getValue');
        var item_fg_id = $(ed.target).combobox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('sales/delivery_notes/delete') ?>',
            data: {
                delivery_note_no: delivery_note_no,
                item_fg_id: item_fg_id
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                toastr.success(result.message);
            },
            complete: function(data) {
                $('#dg').datagrid('reload');
            }
        });

        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            setTimeout(function() { 
                $('#address_id').textbox('setValue', row.address_id);
                $('#delivery_note_no').textbox('setValue', row.delivery_note_no);
            }, 500);
            
            $("#delivery_note_date").datebox('disable');
            $("#delivery_note_no").textbox('disable');
            $("#customer_id").combobox('disable');
            $("#origin").combobox('disable');
            $("#sailing").textbox('disable');
            $("#ship_by").textbox('disable');
            $("#incoterm").textbox('disable');
            $("#status_delivery").textbox('disable');
            $("#delivery_order_no").textbox('disable');
            $("#division").textbox('disable');
            $("#address_id").combobox('disable');
            $("#btnPreview").linkbutton('disable');

            preview("<?= base_url('sales/delivery_notes/datatableUpdates?delivery_note_no=') ?>" + btoa(row.delivery_note_no));
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function preview(url="") {
        var delivery_order_no = $("#delivery_order_no").combobox('getText');
        var delivery_note_date = $("#delivery_note_date").datebox('getValue');
        
        if(url == ""){
            var urlGet = "<?= base_url('sales/delivery_notes/datatablesTemp/') ?>" + btoa(delivery_order_no) + "/" + btoa(delivery_note_date);
        }else{
            var urlGet = url;
        } 

        if (delivery_order_no == "") {
            toastr.warning('Please select Delivery Order No', 'Required');
        } else {
            var lastIndex;
            var dg = $('#dg_request').datagrid({
                url: urlGet,
                fitColumns: true,
                onClickRow: function(rowIndex) {
                    if (lastIndex != rowIndex) {
                        $(this).datagrid('endEdit', lastIndex);
                        $(this).datagrid('beginEdit', rowIndex);
                    }
                    lastIndex = rowIndex;
                },
                onBeginEdit: function(rowIndex, row) {
                    var editors = $('#dg_request').datagrid('getEditors', rowIndex);
                    var qty = $(editors[0].target);
                }
            });
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('sales/delivery_notes/delete') ?>',
                            data: {
                                delivery_note_no: row.delivery_note_no
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                            },
                            complete: function(data) {
                                $('#dg').datagrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_delivery_note_no = $("#filter_delivery_note_no").combobox('getValue');
        var filter_delivery_order_no = $("#filter_delivery_order_no").combobox('getValue');
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_customer_order_no = $("#filter_customer_order_no").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_status_delivery = $("#filter_status_delivery").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_delivery_note_no=" + window.btoa(filter_delivery_note_no) +
            "&filter_delivery_order_no=" + window.btoa(filter_delivery_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_status_delivery=" + window.btoa(filter_status_delivery);
            "&filter_status=" + window.btoa(filter_status);

        $('#dg').datagrid({
            url: '<?= base_url('sales/delivery_notes/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.delivery_note_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('sales/delivery_notes/datatableDetails?delivery_note_no=') ?>' + window.btoa(row.delivery_note_no),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'status_delivery',
                            title: 'Status Delivery',
                            halign: 'center',
                            align: 'center',
                            formatter: cellFormatterDeliveryStatus,
                            styler: cellStyler,
                            width: 100
                        },{
                            field: 'delivery_order_no',
                            title: 'Delivery Order No.',
                            halign: 'center',
                            width: 150
                        },{
                            field: 'item_fg_id',
                            title: 'Product ID',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_number',
                            title: 'Product No.',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'sales_order_no',
                            title: 'Sales Order No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'customer_order_no',
                            title: 'Customer Order No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'qty',
                            title: 'Total',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('sales/delivery_notes/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_delivery_note_no = $("#filter_delivery_note_no").combobox('getValue');
        var filter_delivery_order_no = $("#filter_delivery_order_no").combobox('getValue');
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_customer_order_no = $("#filter_customer_order_no").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_status_delivery = $("#filter_status_delivery").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_delivery_note_no=" + window.btoa(filter_delivery_note_no) +
            "&filter_delivery_order_no=" + window.btoa(filter_delivery_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_status_delivery=" + window.btoa(filter_status_delivery);
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('sales/delivery_notes/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        filter();

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var customer_id = $("#customer_id").combobox('getValue');
                    var delivery_note_date = $("#delivery_note_date").datebox('getValue');
                    var delivery_note_no = $("#delivery_note_no").textbox('getValue');
                    var delivery_order_no = $("#delivery_order_no").textbox('getValue');
                    var division = $("#division").textbox('getValue');
                    var address_id = $("#address_id").combobox('getValue');
                    var police_no = $("#police_no").combobox('getValue');
                    var driver_name = $("#driver_name").textbox('getValue');
                    var origin = $("#origin").textbox('getValue');
                    var sailing = $("#sailing").textbox('getValue');
                    var ship_by = $("#ship_by").combobox('getValue');
                    var incoterm = $("#incoterm").combobox('getValue');
                    var note = $("#note").textbox('getValue');
                    var status = $("#status").combobox('getValue');

                    $('#dg_request').datagrid('acceptChanges');
                    var rows = $('#dg_request').datagrid('getSelections');
                    var totalrows = rows.length;
                    if(totalrows){
                        if (customer_id != "" && delivery_note_date !="" && delivery_note_no !="" && address_id !="" && division !="" &&
                        police_no !="" && origin !="" && sailing !="" && incoterm !="") {
                            for (let i = 0; i < totalrows; i++) {
                                if (rows[i].item_fg_id) {
                                    $.ajax({
                                        type: "post",
                                        url: '<?= base_url('sales/delivery_notes/create') ?>',
                                        data: {
                                            customer_id: customer_id,
                                            delivery_note_date: delivery_note_date,
                                            delivery_note_no: delivery_note_no,
                                            division: division,
                                            address_id: address_id,
                                            police_no: police_no,
                                            driver_name: driver_name,
                                            origin: origin,
                                            sailing: sailing,
                                            ship_by: ship_by,
                                            incoterm: incoterm,
                                            note: note,
                                            status: status,
                                            delivery_order_no: rows[i].delivery_order_no,
                                            item_fg_id: rows[i].item_fg_id,
                                            sales_order_no: rows[i].sales_order_no,
                                            customer_order_no: rows[i].customer_order_no,
                                            trans_type: rows[i].trans_type,
                                            uom: rows[i].uom,
                                            status_delivery: rows[i].status_delivery,
                                            qty: rows[i].qty,
                                        },
                                        dataType: "json",
                                        success: function(result) {
                                            if (i == (totalrows - 1)) {
                                                Swal.fire({
                                                    title: result.message,
                                                    icon: result.theme,
                                                    confirmButtonText: 'Ok',
                                                    allowOutsideClick: false,
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        window.location.reload();
                                                    }
                                                });
                                            }
                                        }
                                    });
                                }
                            }

                            $('#dg').datagrid('reload');
                            $('#dlg_insert').dialog('close');
                        } else {
                            toastr.error("Please Completed your input");
                        }
                    } else{
                        toastr.error("Please Check all Checkbox First!");
                    }
                }
            }]
        });
    });

    $('#filter_customer_id').combobox({
        url: '<?= base_url('master/customers/reads'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose All',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onSelect: function(customer) {
            $('#filter_delivery_note_no').combobox({
                url: '<?= base_url('sales/delivery_notes/readDelivery_note_no/'); ?>' + customer.id,
                valueField: 'delivery_note_no',
                textField: 'delivery_note_no',
                prompt: 'Choose All',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
                onSelect: function(deliver_note) {
                    $('#filter_delivery_order_no').combobox({
                        url: '<?= base_url('sales/delivery_notes/readDelivery_order_no/'); ?>' + customer.id,
                        valueField: 'delivery_order_no',
                        textField: 'delivery_order_no',
                        prompt: 'Choose All',
                        icons: [{
                            iconCls: 'icon-clear',
                            handler: function(e) {
                                $(e.data.target).combobox('clear').combobox('textbox').focus();
                            }
                        }],
                        onSelect: function(deliver_order) {
                            $('#filter_sales_order_no').combobox({
                                url: '<?= base_url('sales/delivery_notes/readSalesOrder/'); ?>' + customer.id,
                                valueField: 'sales_order_no',
                                textField: 'sales_order_no',
                                prompt: 'Choose All',
                                icons: [{
                                    iconCls: 'icon-clear',
                                    handler: function(e) {
                                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                                    }
                                }],
                            });

                            $('#filter_customer_order_no').combobox({
                                url: '<?= base_url('sales/delivery_notes/readCustomerOrder/'); ?>' + customer.id,
                                valueField: 'customer_order_no',
                                textField: 'customer_order_no',
                                prompt: 'Choose All',
                                icons: [{
                                    iconCls: 'icon-clear',
                                    handler: function(e) {
                                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                                    }
                                }],
                            });
                        }
                    });
                }
            });
        }
    });

    $('#filter_item_fg').combogrid({
        url: '<?= base_url("master/item_fg/reads") ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Product No",
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
                width: 200
            }, {
                field: 'name',
                title: 'Product Name',
                width: 200
            }]
        ],
    });

    $('#customer_id').combobox({
        url: '<?= base_url('master/customers/reads'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Customer Name',
        onSelect: function(customer) {
            // addTable(customer.id);

            if(customer.type=="LOCAL"){
                var origin = "INDONESIA";
                var sailing = "-";
                var ship_by = "TRUCK";
                var incoterm = "NONE";
            }else{
                var origin ="";
                var sailing = "";
                var ship_by = "";
                var incoterm = "";
            }

            $("#origin").textbox('setValue', origin);
            $("#sailing").textbox('setValue', sailing);
            $("#ship_by").textbox('setValue', ship_by);
            $("#incoterm").textbox('setValue', incoterm);

            $('#division').combobox({
                url: '<?= base_url('sales/delivery_notes/readDivision/') ?>' + customer.id,
                valueField: 'division',
                textField: 'division',
                panelHeight:'auto',
                prompt: 'Choose Division.',
                onSelect: function(sales_orders) {
                    var delivery_note_date = $("#delivery_note_date").datebox('getValue');
                    number(delivery_note_date, sales_orders.division, customer.number);

                    $('#address_id').combobox({
                        url: '<?= base_url('sales/delivery_notes/readShipping/') ?>' + customer.id + "/" + btoa(sales_orders.division), 
                        valueField: 'id',
                        textField: 'address_name',
                        panelHeight:'auto',
                        prompt: 'Choose Address.',
                        onSelect: function(customer_address){
                            $('#delivery_order_no').combobox({
                                url: '<?= base_url('sales/delivery_notes/readDo/') ?>' + customer.id + "/" + btoa(sales_orders.division) + "/" + btoa(customer_address.id),
                                valueField: 'delivery_order_no',
                                textField: 'delivery_order_no',
                                multiple: true,
                                prompt: 'Choose DO No.',
                            });
                        }
                    });

                }
            });
        }
    });


    $('#police_no').combobox({
        url: '<?= base_url('master/vehicles/reads'); ?>',
        valueField: 'police_no',
        textField: 'police_no',
        prompt: 'Choose Vehicles',
    });    
    

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 0) {
            return 'OPEN';
        } else {
            return 'CLOSE';
        }
    };
    //FORMATTER DELIVERY STATUS 
    function cellFormatterDeliveryStatus(value) {
        if (value == 0) {
            return 'ON SCHEDULE';
        } else {
            return 'DELAY';
        }
    };

    // FORMAT tahun-bulan-tanggal
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

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

    function numberFormat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function btnPrint(val, row) {
        var print = "print_do('" + row.delivery_order_no + "')"; 
        return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
    }

    function print_do(delivery_order_no) {
        window.open("<?= base_url('sales/delivery_notes/print_do/') ?>" + window.btoa(delivery_order_no), "_blank", "width=1200,height=600");
    }
</script>
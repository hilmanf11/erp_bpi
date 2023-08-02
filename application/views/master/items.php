<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'name',width:150,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'description',width:300,halign:'center'">Specification</th>
            <th rowspan="2" data-options="field:'item_family_name',width:150,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'account_number',width:100,halign:'center'">Account No</th>
            <th rowspan="2" data-options="field:'account_name',width:200,halign:'center'">Account Name</th>
            <th rowspan="2" data-options="field:'leadtime',width:100,align:'center'">Leadtime <br> Production</th>
            <th rowspan="2" data-options="field:'box',width:80,align:'center'">Box <br> Delivery</th>
            <th rowspan="2" data-options="field:'box_sub',width:80,align:'center'">Box Sub<br> Delivery</th>
            <th rowspan="2" data-options="field:'lot',width:80,align:'center'">Lot Size</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'supply',width:100,align:'center',formatter:formatSupply,styler:styleSupply">Supply Sheet</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center',formatter:formatStatus,styler:styleStatus">Status</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>
<!-- DIALOG SAVE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; top: 20px;">
    <div class="easyui-tabs" style="width:auto; height:700px;">
        <div title="Wire" style="padding:10px">
            <form id="frm_insert_one" method="post" novalidate>
                <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b>Form Data</b></legend>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Product Family</span>
                        <input style="width:60%;" name="item_family_id" required="true" readonly="true" value="f26818522f0345a2b01aae3087a2d1" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product Family</span>
                        <input style="width:60%;" disabled value="RAW MATERIAL" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Category</span>
                        <input style="width:60%;" name="item_category_id" required="" class="item_category_id">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Maker</span>
                        <input style="width:60%;" id="maker" required="true">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Specification</span>
                        <input style="width:60%;" id="specification" required="true">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Standard</span>
                        <input style="width:60%;" id="standard" required="true">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Type</span>
                        <input style="width:60%;" id="type" required="true">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Size</span>
                        <input style="width:60%;" id="size" required="true">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Color</span>
                        <input style="width:60%;" id="color" required="true">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Option</span>
                        <input style="width:60%;" id="option" required="true">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product No</span>
                        <input style="width:60%;" name="number" readonly="true" required="true" class="number easyui-textbox" data-options="prompt:'Automatic'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product Name</span>
                        <input style="width:60%;" name="name" required="true" class="easyui-textbox">
                    </div>
                    <div class=" fitem">
                        <span style="width:35%; display:inline-block;">Specification</span>
                        <input style="width:60%;" name="description" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Leadtime Production</span>
                        <input style="width:60%;" name="leadtime" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Box Of Delivery</span>
                        <input style="width:60%;" name="box" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Box Sub Of Delivery</span>
                        <input style="width:60%;" name="box_sub" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Lot Size</span>
                        <input style="width:60%;" name="lot" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Unit of Measure</span>
                        <input style="width:60%;" name="uom_id" class="uom_id">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a class="easyui-linkbutton" onclick="saveItemRaw()" data-options="iconCls:'icon-save'">Save Data</a>
                    </div>
                </fieldset>
            </form>
        </div>
        <div title="Finish Good" style="padding:10px">
            <form id="frm_insert_two" method="post" novalidate>
                <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b>Form Data</b></legend>
                    <div class="fitem" hidden>
                        <span style="width:35%; display:inline-block;">Product Family</span>
                        <input style="width:60%;" name="item_family_id" required="true" readonly="true" value="9016430cc47a455ba3a900f9d0b5d8" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product Family</span>
                        <input style="width:60%;" disabled value="FINISH GOOD" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Category</span>
                        <input style="width:60%;" name="item_category_id" required="" class="item_category_id">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Year</span>
                        <input style="width:60%;" id="year" required="true">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Drawing</span>
                        <input style="width:60%;" id="drawing" required="true">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product</span>
                        <input style="width:60%;" id="product" required="true">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Customer</span>
                        <input style="width:60%;" id="customer" required="true">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Plant</span>
                        <input style="width:60%;" id="plant" required="true">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product No</span>
                        <input style="width:60%;" name="number" id="number" readonly="true" required="true" class="number easyui-textbox" data-options="prompt:'Automatic'">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product Name</span>
                        <input style="width:60%;" name="name" required="true" class="easyui-textbox">
                    </div>
                    <div class=" fitem">
                        <span style="width:35%; display:inline-block;">Specification</span>
                        <input style="width:60%;" name="description" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Leadtime Production</span>
                        <input style="width:60%;" name="leadtime" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Box Of Delivery</span>
                        <input style="width:60%;" name="box" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Box Sub Of Delivery</span>
                        <input style="width:60%;" name="box_sub" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Lot Size</span>
                        <input style="width:60%;" name="lot" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Unit of Measure</span>
                        <input style="width:60%;" name="uom_id" class="uom_id">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a class="easyui-linkbutton" onclick="saveItemFg()" data-options="iconCls:'icon-save'">Save Data</a>
                    </div>
                </fieldset>
            </form>
        </div>

        <div title="Others" style="padding:10px">
            <form id="frm_insert_three" method="post" novalidate>
                <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                    <legend><b>Form Data</b></legend>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product Family</span>
                        <input style="width:60%;" name="item_family_id" class="item_family_id" required="true">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Category</span>
                        <input style="width:60%;" name="item_category_id" required="" class="item_category_id">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Product No</span>
                        <input style="width:60%;" name="number" id="number" required="true" class="easyui-textbox">
                    </div>
                    <div class=" fitem">
                        <span style="width:35%; display:inline-block;">Product Name</span>
                        <input style="width:60%;" name="name" required="true" class="easyui-textbox">
                    </div>
                    <div class=" fitem">
                        <span style="width:35%; display:inline-block;">Specification</span>
                        <input style="width:60%;" name="description" class="easyui-textbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Leadtime Production</span>
                        <input style="width:60%;" name="leadtime" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Box Of Delivery</span>
                        <input style="width:60%;" name="box" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Box Sub Of Delivery</span>
                        <input style="width:60%;" name="box_sub" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Lot Size</span>
                        <input style="width:60%;" name="lot" class="easyui-numberbox">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Unit of Measure</span>
                        <input style="width:60%;" name="uom_id" class="uom_id">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Account No</span>
                        <input style="width:60%;" name="account_number" required="" readonly class="easyui-textbox account_number">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Account Name</span>
                        <input style="width:60%;" name="account_name" required="" disabled class="easyui-textbox account_name">
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Supply Sheet</span>
                        <select style="width:60%;" name="supply" class="easyui-combobox" panelHeight="auto">
                            <option value="0">YES</option>
                            <option value="1">NO</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;">Status</span>
                        <select style="width:60%;" name="status" class="easyui-combobox" panelHeight="auto">
                            <option value="0">Active</option>
                            <option value="1">Not Active</option>
                        </select>
                    </div>
                    <div class="fitem">
                        <span style="width:35%; display:inline-block;"></span>
                        <a class="easyui-linkbutton" onclick="saveItemOthers()" data-options="iconCls:'icon-save'">Save Data</a>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>

<!-- DIALOG UPDATE -->
<div id="dlg_update" class="easyui-dialog" title="Update Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" name="item_family_id" required="" class="item_family_id">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Category</span>
                <input style="width:60%;" name="item_category_id" required="" class="item_category_id">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Item ID</span>
                <input style="width:60%;" name="number" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Item Name</span>
                <input style="width:60%;" name="name" required="" class="easyui-textbox">
            </div>
            <div class=" fitem">
                <span style="width:35%; display:inline-block;">Specification</span>
                <input style="width:60%;" name="description" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Account No</span>
                <input style="width:60%;" name="account_number" required="" readonly class="easyui-textbox account_number">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Account Name</span>
                <input style="width:60%;" name="account_name" disabled required="" readonly class="easyui-textbox account_name">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Leadtime Production</span>
                <input style="width:60%;" name="leadtime" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Box Of Delivery</span>
                <input style="width:60%;" name="box" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Box Sub Of Delivery</span>
                <input style="width:60%;" name="box_sub" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Lot Size</span>
                <input style="width:60%;" name="lot" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Unit of Measure</span>
                <input style="width:60%;" name="uom_id" class="uom_id">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supply Sheet</span>
                <select style="width:60%;" name="supply" class="easyui-combobox" panelHeight="auto">
                    <option value="0">YES</option>
                    <option value="1">NO</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:60%;" name="status" class="easyui-combobox" panelHeight="auto">
                    <option value="0">Active</option>
                    <option value="1">Not Active</option>
                </select>
            </div>
        </fieldset>
    </form>
</div>
<!-- DIALOG UPLOAD -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/items/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/items/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_update').dialog('open');
            $('#frm_update').form('load', row);
            url_save = '<?= base_url('master/items/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }
    //GENERATE ITEM RAW
    function generateItemRaw() {
        var maker = $("#maker").combogrid('getValue');
        var specification = $("#specification").combogrid('getValue');
        var standard = $("#standard").combogrid('getValue');
        var type = $("#type").combogrid('getValue');
        var size = $("#size").combogrid('getValue');
        var color = $("#color").combogrid('getValue');
        var option = $("#option").combogrid('getValue');
        return maker + specification + standard + type + size + color + option;
    }
    //GENERATE ITEM FG
    function generateItemFg() {
        var year = $("#year").combobox('getValue');
        var drawing = $("#drawing").combogrid('getValue');
        var product = $("#product").combogrid('getValue');
        var customer = $("#customer").combogrid('getValue');
        var plant = $("#plant").combogrid('getValue');
        var noid = year + drawing + product + customer + plant;
        $.ajax({
            url: '<?= base_url('master/items/itemNumberFg/') ?>' + noid,
            success: function(val) {
                $(".number").textbox('setValue', val);
            }
        });
    }
    //SAVE ITEM RAW
    function saveItemRaw() {
        $('#frm_insert_one').form('submit', {
            url: url_save,
            onSubmit: function() {
                return $(this).form('validate');
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                if (result.theme == "success") {
                    toastr.success(result.message, result.title);
                } else {
                    toastr.error(result.message, result.title);
                }
                // $('#dlg_insert').dialog('close');
                $('#dg').datagrid('reload');
            }
        });
    }

    function saveItemFg() {
        $('#frm_insert_two').form('submit', {
            url: url_save,
            onSubmit: function() {
                return $(this).form('validate');
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                if (result.theme == "success") {
                    toastr.success(result.message, result.title);
                } else {
                    toastr.error(result.message, result.title);
                }
                // $('#dlg_insert').dialog('close');
                $('#dg').datagrid('reload');
            }
        });
    }

    function saveItemOthers() {
        $('#frm_insert_three').form('submit', {
            url: url_save,
            onSubmit: function() {
                return $(this).form('validate');
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                if (result.theme == "success") {
                    toastr.success(result.message, result.title);
                } else {
                    toastr.error(result.message, result.title);
                }
                // $('#dlg_insert').dialog('close');
                $('#frm_insert_three').form('clear');
                $('#dg').datagrid('reload');
            }
        });
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
                            url: '<?= base_url('master/items/delete') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
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
    //UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    //DOWNLOAD TEMPLATE UPLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_items.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/items/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    function styleSupply(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function formatSupply(val) {
        if (val == 0) {
            return 'YES';
        } else {
            return 'NO';
        }
    };

    function styleStatus(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function formatStatus(val) {
        if (val == 0) {
            return 'ACTIVE';
        } else {
            return 'NOT ACTIVE';
        }
    };
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/items/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true
        }).datagrid('enableFilter');
        //UPDATE DATA
        $('#dlg_update').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_update').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }
                            $('#dlg_update').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });
        //UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('master/items/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('master/items/upload') ?>',
                        onSubmit: function() {
                            if ($(this).form('validate') == false) {
                                return $(this).form('validate');
                            } else {
                                $.messager.progress({
                                    title: 'Please Wait',
                                    msg: 'Importing Excel to Database'
                                });
                            }
                        },
                        success: function(result) {
                            $.messager.progress('close');
                            //Clear File
                            $.ajax({
                                url: "<?= base_url('master/items/uploadclearFailed') ?>"
                            });
                            var json = eval('(' + result + ')');
                            requestData(json.total, json);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);
                                    $.ajax({
                                        type: "POST",
                                        async: true,
                                        url: "<?= base_url('master/items/uploadCreate') ?>",
                                        data: {
                                            "data": json[number - 1]
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                //Json Failed
                                                $.ajax({
                                                    type: "POST",
                                                    async: true,
                                                    url: "<?= base_url('master/items/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });
                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }
                                            $("#p_remarks").append(title + "<br>");
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

        $('.item_family_id').combogrid({
            url: '<?= base_url('master/item_familys/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product Family",
            columns: [
                [{
                    field: 'number',
                    title: 'Product Family ID',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Product Family Name',
                    width: 250
                }, ]
            ],
            onSelect: function(val, row){
                $(".account_number").textbox('setValue', row.account_number);
                $(".account_name").textbox('setValue', row.account_name);
            }
        });

        $('#standard').combogrid({
            data: [{
                "number": "O",
                "name": "UL"
            }, {
                "number": "N",
                "name": "NON UL"
            }, {
                "number": "J",
                "name": "JIS"
            }],
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            fitColumns: true,
            prompt: "Choose Standard",
            columns: [
                [{
                    field: 'number',
                    title: 'Standard ID',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Standard Name',
                    width: 250
                }, ]
            ],
            onSelect: function() {
                $(".number").textbox('setValue', generateItemRaw());
            }
        });
        $('#maker').combogrid({
            url: '<?= base_url('master/item_makers/reads') ?>',
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Maker",
            columns: [
                [{
                    field: 'number',
                    title: 'Maker ID',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Maker Name',
                    width: 250
                }, ]
            ],
            onSelect: function() {
                $(".number").textbox('setValue', generateItemRaw());
            }
        });
        $('#specification').combogrid({
            url: '<?= base_url('master/item_specifications/reads') ?>',
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Specification",
            columns: [
                [{
                    field: 'number',
                    title: 'Specification ID',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Specification Name',
                    width: 250
                }, ]
            ],
            onSelect: function() {
                $(".number").textbox('setValue', generateItemRaw());
            }
        });
        $('#type').combogrid({
            url: '<?= base_url('master/item_types/reads') ?>',
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Type",
            columns: [
                [{
                    field: 'number',
                    title: 'Type Id',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Type Name',
                    width: 250
                }, ]
            ],
            onSelect: function() {
                $(".number").textbox('setValue', generateItemRaw());
            }
        });
        $('#size').combogrid({
            url: '<?= base_url('master/item_sizes/reads') ?>',
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Size",
            columns: [
                [{
                    field: 'number',
                    title: 'Size Id',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Size Name',
                    width: 250
                }, ]
            ],
            onSelect: function() {
                $(".number").textbox('setValue', generateItemRaw());
            }
        });
        $('#color').combogrid({
            url: '<?= base_url('master/item_colors/reads') ?>',
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Color",
            columns: [
                [{
                    field: 'number',
                    title: 'Color Id',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Color Name',
                    width: 250
                }, ]
            ],
            onSelect: function() {
                $(".number").textbox('setValue', generateItemRaw());
            }
        });
        $('#option').combogrid({
            url: '<?= base_url('master/item_options/reads') ?>',
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Option",
            columns: [
                [{
                    field: 'number',
                    title: 'Option Id',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Option Name',
                    width: 250
                }, ]
            ],
            onSelect: function() {
                $(".number").textbox('setValue', generateItemRaw());
            }
        });
        $("#year").combobox({
            url: '<?= base_url('master/items/years') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Choose Year",
            onSelect: function() {
                generateItemFg()
            }
        });
        $('#drawing').combogrid({
            data: [{
                "number": "1",
                "name": "ORIGINAL DRAWING"
            }, {
                "number": "2",
                "name": "REVISION DRAWING"
            }],
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            fitColumns: true,
            prompt: "Choose Drawing",
            columns: [
                [{
                    field: 'number',
                    title: 'Drawing ID',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Drawing Name',
                    width: 250
                }, ]
            ],
            onSelect: function() {
                generateItemFg()
            }
        });
        $('#product').combogrid({
            url: '<?= base_url('master/item_products/reads') ?>',
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product",
            columns: [
                [{
                    field: 'number',
                    title: 'Product Id',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 250
                }, ]
            ],
            onSelect: function() {
                generateItemFg()
            }
        });
        $('#customer').combogrid({
            url: '<?= base_url('master/customers/reads') ?>',
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Customer",
            columns: [
                [{
                    field: 'number',
                    title: 'Customer Id',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Customer Name',
                    width: 250
                }, ]
            ],
            onSelect: function() {
                generateItemFg()
            }
        });
        $('#plant').combogrid({
            url: '<?= base_url('master/plants/reads') ?>',
            panelWidth: 420,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Plant",
            columns: [
                [{
                    field: 'number',
                    title: 'Plant Id',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Plant Name',
                    width: 250
                }, ]
            ],
            onSelect: function() {
                generateItemFg()
            }
        });
        $('.item_category_id').combogrid({
            url: '<?= base_url('master/item_categories/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Category",
            columns: [
                [{
                    field: 'number',
                    title: 'Category ID',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Category Name',
                    width: 250
                }, ]
            ]
        });
        $('.uom_id').combogrid({
            url: '<?= base_url('master/uom/reads') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Unit of Measure",
            columns: [
                [{
                    field: 'number',
                    title: 'UoM ID',
                    width: 100
                }, {
                    field: 'name',
                    title: 'UoM Name',
                    width: 200
                }, ]
            ]
        });
    });
</script>
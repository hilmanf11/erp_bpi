<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar" data-options="fit: true">
    <thead data-options="frozen:true">
        <tr>
            <th data-options="field:'item_number',width:150,halign:'center'">Part No</th>
            <th data-options="field:'item_name',width:180,halign:'center'">Part Name</th>
            <th data-options="field:'uom',width:80,halign:'center',align:'center'">UoM</th>
            <th data-options="field:'division',width:100,halign:'center'">Division</th>
            <th data-options="field:'supplier_name',width:250,halign:'center'">Supplier</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'qty_memo',width:80,halign:'center',align:'right',formatter:numberformat">Qty PO</th>
            <th rowspan="2" data-options="field:'qty_por',width:80,halign:'center',align:'right',formatter:numberformat">Receive</th>
            <th rowspan="2" data-options="field:'ost_memo',width:80,halign:'center',align:'right',formatter:numberformat">OS PO</th>
            <th rowspan="2" data-options="field:'type',width:80,halign:'center'">Type</th>
            <?php 
                if ($this->input->get('filter_from') && $this->input->get('filter_to')) {
                    $filter_from = base64_decode($this->input->get('filter_from'));
                    $filter_to = base64_decode($this->input->get('filter_to'));
                    $filter_supplier = base64_decode($this->input->get('filter_supplier'));
                    $filter_item_rm = base64_decode($this->input->get('filter_item_rm'));
                } else {
                    $filter_from = date("Y-m-01");
                    $filter_to = date("Y-m-t");
                    $filter_supplier = "";
                    $filter_item_rm = "";
                }
            
                $currentDate = $filter_from;
                $tgl = 1;
                while (strtotime($currentDate) <= strtotime($filter_to)) {
                    $working_date = date('d M', strtotime($currentDate));
            
                    if (date('w', strtotime($currentDate)) !== '0') {
                        $hstyle = "";
                    } else {
                        $hstyle = "background-color:#FFB0B0";
                    }
            ?>
            <th rowspan="2" data-options="field:'qty_<?= $tgl ?>',width:80,halign:'center', align:'right',hstyler: function(value, row, index) { return '<?= $hstyle ?>'; }"><?= $working_date ?></th>
            <?php
                    $tgl++;
                    $currentDate = date("Y-m-d", strtotime("+1 day", strtotime($currentDate)));
                }
            ?>
        </tr>
        <tr>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 160px; padding: 10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:29%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier" value="<?= $filter_supplier ?>" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 10%; float: left;">
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Material No</span>
                    <input style="width:60%;" id="filter_item_rm" value="<?= $filter_item_rm ?>" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<iframe id="printout" src="" style="width: 100%; height:90%; border: 0;" hidden></iframe>

<script>
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_item_rm=" + window.btoa(filter_item_rm);

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            window.location.assign(url);
        }
    }

    function getQueryParams() {
        var params = {};
        var url = window.location.href;
        var queryString = url.substring(url.indexOf('?') + 1);
        var urlParams = new URLSearchParams(queryString);

        urlParams.forEach(function(value, key) {
            params[key] = window.atob(value);
        });

        return params;
    }

    $(document).ready(function() {
        var params = getQueryParams();
        if (params.filter_from && params.filter_to) {
            $('#filter_from').datebox('setValue', params.filter_from);
            $('#filter_to').datebox('setValue', params.filter_to);
        }
    });

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_item_rm=" + window.btoa(filter_item_rm);

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            $.messager.alert('Info','Please Wait to Export to Excel');
            window.location.assign('<?= base_url('warehouse/report_achievment_daily_supp/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_item_rm=" + window.btoa(filter_item_rm);

        if (filter_from != "" && filter_to != "") {
            $('#dg').datagrid({
                url: '<?= base_url('warehouse/report_achievment_daily_supp/datatables') ?>' + url,
                pagination: true,
                rownumbers: true,
                singleSelect: true,
                fit: true,
                pageList: [20, 50, 100, 500, 1000],
                pageSize: 20,
                onLoadSuccess: function(data){
                    var z = 0;
                    for(var i=0; i < (data.total); i++){
                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'item_number',
                            rowspan: 6,
                        });
                        
                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'item_name',
                            rowspan: 6,
                        });

                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'uom',
                            rowspan: 6,
                        });

                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'division',
                            rowspan: 6,
                        });

                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'supplier_name',
                            rowspan: 6,
                        });

                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'qty_memo',
                            rowspan: 6,
                        });

                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'qty_por',
                            rowspan: 6,
                        });

                        $('#dg').datagrid('mergeCells', {
                            index: z,
                            field: 'ost_memo',
                            rowspan: 6,
                        });

                        z += 6;
                    }
                }
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('warehouse/report_achievment_daily_supp/print') ?>' + url);
        }

        $('#filter_supplier').combobox({
            url: '<?php echo base_url('master/suppliers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Supplier',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_item_rm').combogrid({
            url: '<?= base_url("master/item_rm/reads") ?>',
            panelWidth: 400,
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
                    title: 'Part No',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Part Name',
                    width: 200
                }]
            ],
        });
    });

    function numberformat(value, row) {
        if (value === null || value === undefined || value === '') return '<b>0.00</b>';
        const num = parseFloat(value);
        if (isNaN(num)) return '<b>0.00</b>';
        const formatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        return "<b>" + formatter.format(num) + "</b>";
    }

    function cellStyler(value, row, index) {
        if (value >= 0) {
            return 'font-weight:bold;';
        } else {
            return 'color:#FF5F5F; font-weight:bold;';
        }
    }

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
</script>
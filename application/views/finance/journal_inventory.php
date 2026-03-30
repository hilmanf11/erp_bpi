<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'details',width:90,align:'center', formatter:btnDetails">Detail</th>
            <th rowspan="2" data-options="field:'journal_date',width:100,align:'center'">Journal Date</th>
            <th rowspan="2" data-options="field:'number',width:100,align:'center'">GL No</th>
            <th rowspan="2" data-options="field:'journal_type_name',width:200,halign:'center'">Journal Type</th>
            <th rowspan="2" data-options="field:'modul',width:150,halign:'center'">Modul</th>
            <th colspan="3" data-options="field:'',width:200,halign:'center'">Original Currency</th>
            <th colspan="3" data-options="field:'',width:200,halign:'center'">Local Currency</th>
            <th rowspan="2" data-options="field:'remarks',width:100,halign:'center'">Remarks</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center'"> Approval</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th data-options="field:'original_debit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Debit</th>
            <th data-options="field:'original_credit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Credit</th>
            <th data-options="field:'rates',width:80,halign:'center',align:'right',formatter:numberformatDefault">Rates</th>
            <th data-options="field:'local_debit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Debit</th>
            <th data-options="field:'local_credit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Credit</th>
            <th data-options="field:'approved_by',width:100,align:'center'"> By</th>
            <th data-options="field:'approved',width:100,align:'center', styler:approvedStyle, formatter:approvedFormat"> Status</th>
            <th data-options="field:'approved_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 200px; padding: 10px;">
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width:50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Journal Date</span>
                    <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Journal Type</span>
                    <input style="width:60%;" id="filter_journal_type" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width:50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Type</span>
                    <select style="width:60%;" id="filter_type" class="easyui-combobox" data-options="editable:false, panelHeight:'auto'">
                        <option value="">Choose All</option>
                        <option value="IN">IN</option>
                        <option value="OUT">OUT</option>
                    </select>
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Modul</span>
                    <select style="width:60%;" id="filter_modul" class="easyui-combobox" 
                        data-options="editable:false, valueField:'id', textField:'text', 
                            groupField:'group',panelHeight:'auto'">
                    </select>
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Voucher No</span>
                    <input style="width:60%;" id="filter_voucher" class="easyui-combobox" />
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div id="dlg_detail" class="easyui-window" title="Journal Detail" data-options="closed: true,modal:true" style="width: 800px; height: 500px; top: 20px; left:10px;">
    <table id="dg3" class="easyui-datagrid" style="width:100%;" showFooter="true">
        <thead>
            <tr>
                <th rowspan="2" data-options="field:'trans_date',width:100,halign:'center'">Trans Date</th>
                <th rowspan="2" data-options="field:'document_no',width:150,halign:'center'">Document No</th>
                <th rowspan="2" data-options="field:'invoice_no',width:150,halign:'center'">Invoice No</th>
                <th rowspan="2" data-options="field:'company_name',width:200,halign:'center'">Company Name</th>
                <th rowspan="2" data-options="field:'account_number',width:100,halign:'center'">Account No</th>
                <th rowspan="2" data-options="field:'account_name',width:200,halign:'center'">Account Name</th>
                <th rowspan="2" data-options="field:'description',width:600,halign:'center'">Description</th>
                <th colspan="3" data-options="field:'',width:100">Original Currency</th>
                <th colspan="3" data-options="field:'',width:100">Local Currency</th>
            </tr>
            <tr>
                <th data-options="field:'currency',width:80,align:'center'">Currency</th>
                <th data-options="field:'original_debit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Debit</th>
                <th data-options="field:'original_credit',width:100,halign:'center',align:'right',formatter:numberformatDefault">Credit</th>
                <th data-options="field:'rates',width:100,halign:'center',align:'right',formatter:numberformatDefault">Rates</th>
                <th data-options="field:'local_debit',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Debit</th>
                <th data-options="field:'local_credit',width:100,halign:'center',align:'right',formatter:numberformatDefaultIdr">Credit</th>
            </tr>
        </thead>
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('finance/journal_inventory/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    function getFilterParams() {
        var params = {
            filter_from: $("#filter_from").datebox('getValue'),
            filter_to: $("#filter_to").datebox('getValue'),
            filter_journal_type: $("#filter_journal_type").combogrid('getValue'),
            filter_type: $("#filter_type").combobox('getValue'),
            filter_modul: $("#filter_modul").combobox('getValue'),
            filter_voucher: $("#filter_voucher").textbox('getValue')
        };

        var queryString = "";
        for (var key in params) {
            // Pastikan nilai adalah string dan tidak null/undefined sebelum btoa
            var val = (params[key] || "").toString(); 
            queryString += "&" + key + "=" + window.btoa(val);
        }
        return queryString;
    }

    function filter() {
        var urlParams = getFilterParams();

        $('#dg').datagrid({
            url: '<?= base_url('finance/journal_inventory/datatables') ?>?' + urlParams,
            pagination: true,
            rownumbers: true,
            fit:true,
        });

        // Update preview printout
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Loading Preview...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/journal_inventory/print') ?>?' + urlParams);
    }

    function pdf() {
        // Pastikan iframe sudah ter-load dengan filter terakhir
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var urlParams = getFilterParams();
        window.location.assign('<?= base_url('finance/journal_inventory/print/excel') ?>?' + urlParams);
    }

    //NOMOR AUTOMATIC
    function number(journal_date) {
        var dateValue = journal_date ? journal_date : "<?= date('Y-m-d') ?>";
        
        $.ajax({
            type: "post",
            url: "<?= base_url('finance/journal_inventory/number/') ?>" + window.btoa(dateValue),
            dataType: "html",
            success: function(result) {
                if ($("#number").length > 0) {
                    $("#number").textbox('setValue', result.trim());
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching number: ", error);
            }
        });
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }



    $(function() {
        let masterModules = [];

        // Load JSON Module
        $.getJSON('<?= base_url("json/journal_inventory_module.json"); ?>', function(data) {
            masterModules = data;
            refreshModuleCombo(''); 
        });

        function refreshModuleCombo(selectedGroup) {
            const filtered = !selectedGroup 
                ? masterModules 
                : masterModules.filter(item => item.group === selectedGroup);

            const finalData = [{ id: '', text: 'Choose All', group: '' }, ...filtered];
            
            // Update kedua id (filter_modul dan modul) sekaligus
            $('#filter_modul, #modul').each(function() {
                if ($(this).length) {
                    $(this).combobox('loadData', finalData).combobox('setValue', '');
                }
            });
        }

        // Listener untuk Type (Grouping IN/OUT)
        $('#filter_type, #type').combobox({
            onSelect: (rec) => refreshModuleCombo(rec.value),
            onChange: (val) => { if(!val) refreshModuleCombo(''); }
        });

        // Inisialisasi awal
        filter();
        
        // Listener Tanggal Jurnal
        $("#journal_date").datebox({
            onChange: (val) => number(val)
        });

        $("#filter_journal_type").combogrid({
            url: '<?= base_url('finance/journal_inventory/readJournalType') ?>',
            panelWidth: 450,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            delay: 300,
            fitColumns: true,
            prompt: "Choose Journal Type",
            onBeforeLoad: function(param) {
                var selectedModul = $("#filter_modul").combobox('getValue');
                if (selectedModul) {
                    param.modul = window.btoa(selectedModul);
                }
            },
            columns: [[
                { field: 'number', title: 'Code', width: 90, halign: 'center', align: 'center' },
                { field: 'name', title: 'Journal Type Name', width: 250, halign: 'center' },
                { field: 'status', title: 'D/C', width: 60, halign: 'center', align: 'center' }
            ]],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });
    });

    // DETAILS
    function btnDetails(val, row) {
        var details = "viewDetails('" + row.number + "')";
        return '<a class="btn btn-primary w-100" onClick="' + details + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewDetails(number) {
        $("#dlg_detail").window('open');
        $("#dlg_detail").window('setTitle', "Detail of " + number);

        $('#dg3').datagrid({
            url: '<?= base_url('finance/journal_inventory/datatableDetails?number=') ?>' + btoa(number),
            pagination: false,
            rownumbers: true,
            remoteFilter: true,
        }).datagrid('enableFilter');
    }


    // FORMAT DATE PERIOD
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

    // FORMAT COLUMNS MAIN DATATABLE
    function numberformatDefault(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformatDefaultIdr(value, row){
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function approvedFormat(value, row) {
        if (row.approved_to == "" || row.approved_to == null) {
            return "<b style='color:green;'>APPROVED</b>";
        } else {
            return "<b style='color:red;'>CHECKING</b>";
        }
    }

    function approvedStyle(value, row, index) {
        if (row.approved_to == "" || row.approved_to == null) {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }
</script>

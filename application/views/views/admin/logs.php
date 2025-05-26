<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'created_by',width:180">Created By</th>
            <th data-options="field:'created_date',width:180">Created Date</th>
            <th data-options="field:'ip_address',width:100">Ip Address</th>
            <th data-options="field:'action',width:100">Action</th>
            <th data-options="field:'menu',width:180">Module</th>
            <th data-options="field:'description',width:300">Description</th>
            <th data-options="field:'detail',width:100,formatter:BtnDetail">Detail</th>
        </tr>
    </thead>
</table>
<div id="dlg_detail" class="easyui-dialog" title="Detail Description" data-options="closed: true,modal:true" style="width: 1000px; padding:10px; top: 20px;">
    <div id="remarks">

    </div>
</div>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('admin/logs/print') ?>" style="width: 100%;" hidden></iframe>
<script>
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
                            url: '<?= base_url('admin/logs/delete') ?>',
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
            toastr.info("Please select one of the data in the table first");
        }
    }
    $(function() {
        $('#dg').datagrid({
            url: '<?= base_url('admin/logs/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');
    });
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //EXPORT EXCEL
    function excel() {
        window.location.assign('<?= base_url('admin/logs/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    function details(id) {
        $("#dlg_detail").dialog('open');
        $.ajax({
            type: "post",
            url: "<?= base_url('admin/logs/details') ?>",
            data: "id=" + id,
            dataType: "html",
            success: function(remarks) {
                $("#remarks").html(remarks);
            }
        });
    }

    function BtnDetail(val, row) {
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onclick="details(' + row.id + ')"><i class="fa fa-eye"></i> Details</a>';
    }
</script>
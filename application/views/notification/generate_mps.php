<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'periode',width:150,align:'center'">Periode MPS</th>
            <th data-options="field:'revisi',width:100,align:'center'">Revisi</th>
            <th data-options="field:'pesan',width:450">Pesan / Instruksi</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
     <!-- Kita gunakan Confirm Notif agar seragam dengan modul lain -->
     <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="confirmNotif()"><i class="fa fa-check-square"></i> Confirm / Mark as Read</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel()"><i class="fa fa-file"></i> Export Excel</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>
</div>

<script>
    // RELOAD
    function reload() {
        window.location.reload();
    }

    // EXPORT EXCEL
    function export_excel() {
        $('#dg').datagrid('toExcel', "notification_<?= $table ?>.xls");
    }

    // CONFIRM NOTIFICATION
    function confirmNotif() {
        var rows = $('#dg').datagrid('getSelections');

        if (rows.length > 0) {
            $.messager.confirm('Confirmation', 'Are you sure you want to confirm these notifications?', function(r) {
                if (r) {
                    var selectedData = [];
                    for (var i = 0; i < rows.length; i++) {
                        selectedData.push({
                            id_notif: rows[i].id_notification,
                            id_memo: 0 // Diset 0 karena tidak ada tabel master yang perlu di-update statusnya
                        });
                    }

                    // Kirim ke backend
                    $.ajax({
                        method: 'post',
                        url: '<?= base_url('notifications/confirm') ?>',
                        data: {
                            data: selectedData,
                            table: '<?= $table ?>' 
                        },
                        dataType: 'json',
                        success: function(result) {
                            if(result.status) {
                                toastr.success(result.message);
                            } else {
                                toastr.error(result.message);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            toastr.error(jqXHR.statusText);
                            $.messager.alert("Error", jqXHR.statusText, 'error');
                        },
                        complete: function() {
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            });
        } else {
            $.messager.alert('Information', 'Please select at least one data to confirm!', 'info');
        }
    }

    // FORMATTER NUMBER (Jika kedepannya ada angka yang perlu diformat)
    function numberformat(value, row) {
        if (!value) return "";
        const formatter = new Intl.NumberFormat('id-ID');
        return "<b>" + formatter.format(value) + "</b>";
    }

    $(function() {
        // SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            // URL sudah diubah mengarah ke generate_mps
            url: '<?= base_url('notifications/notificationData/generate_mps/') ?>' + "<?= base64_encode($user) ?>" + "/" + "<?= base64_encode($name) ?>",
            pagination: false,
            singleSelect: false,
            clientPaging: false,
            rownumbers: true,
            fit: true,
        }).datagrid('enableFilter');
    });
</script>
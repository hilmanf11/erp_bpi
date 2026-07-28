<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'memo_no',width:150">Memo No</th>
            <th data-options="field:'request_date',width:100">Request Date</th>
            <th data-options="field:'supplier_name',width:200">Supplier</th>
            <th data-options="field:'maker',width:200">Maker</th>
            <th data-options="field:'item_number',width:200">Part No</th>
            <th data-options="field:'item_name',width:200">Part Name</th>
            <th data-options="field:'qty',width:80">Qty</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <!-- <a href="javascript:void(0)" id="approveall" class="easyui-linkbutton" data-options="plain:true" onclick="deleteAll()"><i class="fa fa-check"></i> Delete</a> -->
     <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="confirmNotif()"><i class="fa fa-check-square"></i> Confirm</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel()"><i class="fa fa-file"></i> Export Excel</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>
</div>

<script>
    //RELOAD
    function reload() {
        window.location.reload();
    }

    function export_excel() {
		$('#dg').datagrid('toExcel', "notification_<?= $table ?>.xls");
	}

    function confirmNotif() {
        var rows = $('#dg').datagrid('getSelections');

        if (rows.length > 0) {
            $.messager.confirm('Confirmation', 'Are you sure you want to confirm these notifications?', function(r) {
                if (r) {
                    // Kumpulkan ID Memo dan ID Notifikasi
                    var selectedData = [];
                    for (var i = 0; i < rows.length; i++) {
                        selectedData.push({
                            id_notif: rows[i].id_notification,
                            id_memo: rows[i].id // ID asli dari request_materials yang ada di log JSON
                        });
                    }

                    // Kirim ke backend
                    $.ajax({
                        method: 'post',
                        url: '<?= base_url('notifications/confirm') ?>',
                        data: {
                            data: selectedData,
                            table: '<?= $table ?>' // Pass nama tabel dari variabel CodeIgniter
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

	function deleteAll() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to Delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('notifications/delete') ?>',
                            data: {
                                id: row.id_notification
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
            $.messager.alert('Information', 'Please select one of the data in the table first!', 'info');
        }
    }

    function numberformat(value, row) {
		const formatter = new Intl.NumberFormat('id-ID');
		return "<b>" + formatter.format(value) + "</b>";
	}

    function Itemid(value, row) {
		return "'" + value;
	}

	function numberformatPrice(value, row){
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('notifications/notificationData/request_materials/') ?>' + "<?= base64_encode($user) ?>" + "/" + "<?= base64_encode($name) ?>",
            pagination: false,
            singleSelect: false,
            clientPaging: false,
            rownumbers: true,
            fit: true,
        }).datagrid('enableFilter');
    });
</script>
<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'number',width:150">Number ID</th>
            <th data-options="field:'name',width:100">Name</th>
            <th data-options="field:'username',width:100">Username</th>
            <th data-options="field:'email',width:150">Email</th>
            <th data-options="field:'phone',width:100">Phone</th>
            <th data-options="field:'position',width:100">Position</th>
            <th data-options="field:'division',width:100">Division</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <!-- <a href="javascript:void(0)" id="approveall" class="easyui-linkbutton" data-options="plain:true" onclick="deleteAll()"><i class="fa fa-check"></i> Delete</a> -->
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

	function deleteAll() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('notification/delete') ?>',
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
            url: '<?= base_url('notifications/notificationData/users/') ?>' + "<?= base64_encode($user) ?>" + "/" + "<?= base64_encode($name) ?>",
            pagination: false,
            singleSelect: false,
            clientPaging: false,
            rownumbers: true,
            fit: true,
        }).datagrid('enableFilter');
    });
</script>
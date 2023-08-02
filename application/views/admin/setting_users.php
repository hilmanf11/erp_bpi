<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%; height: 600px;" data-options="rownumbers:true,singleSelect:true,url:'<?= base_url('admin/user_setting/datatables') ?>'" toolbar="#toolbar"></table>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 75px;">
    <fieldset style="margin-bottom: 10px;">
        <legend>Choose User</legend>
        <form id="frm_search" method="post" enctype="multipart/form-data" novalidate>
            <div class="row">
                <div class="col-lg-5">
                    <div class="fitem">
                        <span style="width:150px; display:inline-block;">Name</span>
                        <input style="width:300px;" id="users_id" class="easyui-textbox">
                    </div>
                </div>
            </div>
        </form>
    </fieldset>
</div>
<br>
<script>
    $(function() {
        $('#users_id').combogrid({
            onSelect: function(value, row) {
                var users_id = row.username;
                $.ajax({
                    url: '<?= base_url('admin/setting_users/create') ?>',
                    type: 'post',
                    data: 'users_id=' + users_id,
                    success: function(msg) {
                        $('#dg').treegrid({
                            url: '<?= base_url('admin/setting_users/datatables') ?>?users_id=' + users_id,
                            rownumbers: true,
                            singleSelect: true,
                            idField: 'id',
                            treeField: 'name',
                            onBeforeLoad: function(row, param) {
                                if (!row) {
                                    param.id = 0;
                                }
                            },
                            columns: [
                                [{
                                    field: 'name',
                                    title: 'Menu Name',
                                    width: 250,
                                    align: 'left'
                                }, {
                                    field: 'v_view',
                                    title: 'View',
                                    width: 80,
                                    align: 'center',
                                    formatter: function(hasil, row) {
                                        var action = "save_data('" + row.id_user_set + "')";
                                        if (row.v_view == "1") {
                                            return '<input type="checkbox" id="v_view' + row.id_user_set + '" value="v_view" onclick="' + action + '" checked>';
                                        } else if (row.m_view == "on") {
                                            return '<input type="checkbox" id="v_view' + row.id_user_set + '" value="v_view" onclick="' + action + '">';
                                        } else {}
                                    }
                                }, {
                                    field: 'v_add',
                                    title: 'Add',
                                    width: 80,
                                    align: 'center',
                                    formatter: function(hasil, row) {
                                        var action = "save_data('" + row.id_user_set + "')";
                                        if (row.v_add == "1") {
                                            return '<input type="checkbox" id="v_add' + row.id_user_set + '" value="v_add" onclick="' + action + '" checked>';
                                        } else if (row.m_add == "on") {
                                            return '<input type="checkbox" id="v_add' + row.id_user_set + '" value="v_add" onclick="' + action + '">';
                                        } else {}
                                    }
                                }, {
                                    field: 'v_edit',
                                    title: 'Edit',
                                    width: 80,
                                    align: 'center',
                                    formatter: function(hasil, row) {
                                        var action = "save_data('" + row.id_user_set + "')";
                                        if (row.v_edit == "1") {
                                            return '<input type="checkbox" id="v_edit' + row.id_user_set + '" value="v_edit" onclick="' + action + '" checked>';
                                        } else if (row.m_edit == "on") {
                                            return '<input type="checkbox" id="v_edit' + row.id_user_set + '" value="v_edit" onclick="' + action + '">';
                                        } else {}
                                    }
                                }, {
                                    field: 'v_delete',
                                    title: 'Delete',
                                    width: 80,
                                    align: 'center',
                                    formatter: function(hasil, row) {
                                        var action = "save_data('" + row.id_user_set + "')";
                                        if (row.v_delete == "1") {
                                            return '<input type="checkbox" id="v_delete' + row.id_user_set + '" value="v_delete" onclick="' + action + '" checked>';
                                        } else if (row.m_delete == "on") {
                                            return '<input type="checkbox" id="v_delete' + row.id_user_set + '" value="v_delete" onclick="' + action + '">';
                                        } else {}
                                    }
                                }, {
                                    field: 'v_upload',
                                    title: 'Upload',
                                    width: 80,
                                    align: 'center',
                                    formatter: function(hasil, row) {
                                        var action = "save_data('" + row.id_user_set + "')";
                                        if (row.v_upload == "1") {
                                            return '<input type="checkbox" id="v_upload' + row.id_user_set + '" value="v_upload" onclick="' + action + '" checked>';
                                        } else if (row.m_upload == "on") {
                                            return '<input type="checkbox" id="v_upload' + row.id_user_set + '" value="v_upload" onclick="' + action + '">';
                                        } else {}
                                    }
                                }, {
                                    field: 'v_download',
                                    title: 'Download',
                                    width: 80,
                                    align: 'center',
                                    formatter: function(hasil, row) {
                                        var action = "save_data('" + row.id_user_set + "')";
                                        if (row.v_download == "1") {
                                            return '<input type="checkbox" id="v_download' + row.id_user_set + '" value="v_download" onclick="' + action + '" checked>';
                                        } else if (row.m_download == "on") {
                                            return '<input type="checkbox" id="v_download' + row.id_user_set + '" value="v_download" onclick="' + action + '">';
                                        } else {}
                                    }
                                }, {
                                    field: 'v_print',
                                    title: 'Print',
                                    width: 80,
                                    align: 'center',
                                    formatter: function(hasil, row) {
                                        var action = "save_data('" + row.id_user_set + "')";
                                        if (row.v_print == "1") {
                                            return '<input type="checkbox" id="v_print' + row.id_user_set + '" value="v_print" onclick="' + action + '" checked>';
                                        } else if (row.m_print == "on") {
                                            return '<input type="checkbox" id="v_print' + row.id_user_set + '" value="v_print" onclick="' + action + '">';
                                        } else {}
                                    }
                                }, {
                                    field: 'v_excel',
                                    title: 'Excel',
                                    width: 80,
                                    align: 'center',
                                    formatter: function(hasil, row) {
                                        var action = "save_data('" + row.id_user_set + "')";
                                        if (row.v_excel == "1") {
                                            return '<input type="checkbox" id="v_excel' + row.id_user_set + '" value="v_excel" onclick="' + action + '" checked>';
                                        } else if (row.m_excel == "on") {
                                            return '<input type="checkbox" id="v_excel' + row.id_user_set + '" value="v_excel" onclick="' + action + '">';
                                        } else {}
                                    }
                                }, ]
                            ],
                            onCheck: function(index, row) {
                                //$(this).datagrid('refreshRow', index);
                            },
                            onUncheck: function(index, row) {
                                //$(this).datagrid('refreshRow', index);
                            }
                        });
                    }
                });
            }
        });
        //Get Username
        $('#users_id').combogrid({
            url: '<?= base_url('admin/setting_users/getUsers') ?>',
            panelWidth: 400,
            idField: 'username',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            columns: [
                [{
                    field: 'name',
                    title: 'Name',
                    width: 200
                }, {
                    field: 'username',
                    title: 'Username',
                    width: 200
                }, ]
            ]
        });
    });

    function save_data(data) {
        var users_id = $("#users_id").val();
        var id = data;
        if ($("#v_view" + data).is(':checked')) {
            var h_v_view = "1";
        } else {
            var h_v_view = "0";
        }
        if ($("#v_add" + data).is(':checked')) {
            var h_v_add = "1";
        } else {
            var h_v_add = "0";
        }
        if ($("#v_edit" + data).is(':checked')) {
            var h_v_edit = "1";
        } else {
            var h_v_edit = "0";
        }
        if ($("#v_delete" + data).is(':checked')) {
            var h_v_delete = "1";
        } else {
            var h_v_delete = "0";
        }
        if ($("#v_upload" + data).is(':checked')) {
            var h_v_upload = "1";
        } else {
            var h_v_upload = "0";
        }
        if ($("#v_download" + data).is(':checked')) {
            var h_v_download = "1";
        } else {
            var h_v_download = "0";
        }
        if ($("#v_print" + data).is(':checked')) {
            var h_v_print = "1";
        } else {
            var h_v_print = "0";
        }
        if ($("#v_excel" + data).is(':checked')) {
            var h_v_excel = "1";
        } else {
            var h_v_excel = "0";
        }
        $.ajax({
            url: '<?= base_url('admin/setting_users/update/') ?>?id=' + window.btoa(id),
            type: 'post',
            data: '&v_view=' + h_v_view + '&v_add=' + h_v_add + '&v_edit=' + h_v_edit + '&v_delete=' + h_v_delete +
                '&v_upload=' + h_v_upload + '&v_download=' + h_v_download + '&v_print=' + h_v_print + '&v_excel=' + h_v_excel,
            success: function(msg) {
                var result = eval('(' + msg + ')');
                if (result.theme == "success") {
                    toastr.success(result.message, result.title);
                } else {
                    toastr.error(result.message, result.title);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                toastr.error(jqXHR.statusText);
            }
        });
    }
</script>
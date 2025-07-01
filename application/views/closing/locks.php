<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%; height: 600px;" data-options="rownumbers:true, singleSelect:true" toolbar="#toolbar"></table>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 75px;">
    <fieldset style="margin-bottom: 10px;">
        <legend>Choose Period</legend>
        <form id="frm_search" method="post" enctype="multipart/form-data" novalidate>
            <div class="row">
                <div class="col-lg-5">
                    <div class="fitem">
                        <span style="width:150px; display:inline-block;">Period</span>
                        <input style="width:300px;" id="period" class="easyui-combobox">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="fitem">
                        <span style="width:100px; display:inline-block;">Lock/Unlock</span>
                        <input id="lockall" class="easyui-checkbox"> &nbsp;&nbsp; ALL
                    </div>
                </div>
            </div>
        </form>
    </fieldset>
</div>
<br>
<script>
    $(function() {
        $('#period').combobox({
            onSelect: function(row) {
                var period = row.period;

                Swal.fire({
                    title: 'Please Wait for Get Data Menu',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    url: '<?= base_url('closing/locks/create') ?>',
                    type: 'post',
                    data: 'period=' + period,
                    success: function(msg) {
                        Swal.close();
                        
                        $('#dg').datagrid({
                            url: '<?= base_url('closing/locks/datatables') ?>?period=' + period,
                            rownumbers: true,
                            singleSelect: true,
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
                                    field: 'lock',
                                    title: 'Lock',
                                    width: 80,
                                    align: 'center',
                                    formatter: function(hasil, row) {
                                        var action = "save_data('" + row.period_id + "')";
                                        if (row.lock == "1") {
                                            return '<input type="checkbox" id="lock' + row.period_id + '" value="lock" onclick="' + action + '" checked>';
                                        } else if (row.lock == "0") {
                                            return '<input type="checkbox" id="lock' + row.period_id + '" value="lock" onclick="' + action + '">';
                                        } else {}
                                    }
                                }, {
                                    field: 'updated_by',
                                    title: 'Updated By',
                                    width: 100,
                                    align: 'center'
                                }, {
                                    field: 'updated_date',
                                    title: 'Updated Date',
                                    width: 150,
                                    align: 'center'
                                }]
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
        $("#period").combobox({
            url: '<?= base_url('closing/locks/getPeriod') ?>',
            valueField: 'period',
            textField: 'period',
            prompt: "Choose Period",
        });

        $("#lockall").checkbox({
            onChange: function(val){
                var period = $("#period").combobox('getValue');

                if(period != ""){
                    $.ajax({
                        url: '<?= base_url('closing/locks/updateAll') ?>',
                        type: 'post',
                        data: '&period=' + period + "&check=" + val,
                        success: function(msg) {
                            var result = eval('(' + msg + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                                $('#dg').datagrid('reload');
                            } else {
                                toastr.error(result.message, result.title);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            toastr.error(jqXHR.statusText);
                        }
                    });
                }else{
                    toastr.info("Please Choose Period");
                }
            }
        })
    });

    function save_data(data) {
        var id = data;

        if ($("#lock" + data).is(':checked')) {
            var lock = "1";
        } else {
            var lock = "0";
        }
        
        $.ajax({
            url: '<?= base_url('closing/locks/update/') ?>?id=' + window.btoa(id),
            type: 'post',
            data: '&lock=' + lock,
            success: function(msg) {
                var result = eval('(' + msg + ')');
                if (result.theme == "success") {
                    toastr.success(result.message, result.title);
                    $('#dg').datagrid('reload');
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
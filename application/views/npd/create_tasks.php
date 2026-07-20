<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<style>
    /* Memaksa semua gambar di dalam deskripsi agar tidak melebihi lebar kotak dialog */
    #content_description img {
        max-width: 100% !important;
        height: auto !important;
    }
</style>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'project_number',width:150,halign:'center'">Project No</th>
            <th rowspan="2" data-options="field:'project_name',width:150,halign:'center'">Project Name</th>
            <th rowspan="2" data-options="field:'project_start_date',width:100,halign:'center'">Start Date</th>
            <th rowspan="2" data-options="field:'project_end_date',width:100,halign:'center'">End Date</th>
            <th rowspan="2" data-options="field:'project_duration',width:100,halign:'center',align:'center'">Duration</th>
            <th rowspan="2" data-options="field:'phase_name',width:200,halign:'center',align:'center'">Phase Name</th>
            <th rowspan="2" data-options="field:'event',width:100,halign:'center',align:'center'">Event</th>
            <th rowspan="2" data-options="field:'btn',width:80,halign:'center',align:'right',formatter:btnDescription">Description</th>
            <th colspan="5" data-options="field:'',width:100,halign:'center'"> Attachment</th>
            <th rowspan="2" data-options="field:'project_level',width:100,halign:'center',align:'center', styler:cellStyler">Project Level</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'attachment1',width:80,align:'center',formatter: btnDetails">1</th>            
            <th data-options="field:'attachment2',width:80,align:'center',formatter: btnDetails">2</th>            
            <th data-options="field:'attachment3',width:80,align:'center',formatter: btnDetails">3</th>            
            <th data-options="field:'attachment4',width:80,align:'center',formatter: btnDetails">4</th>            
            <th data-options="field:'attachment5',width:80,align:'center',formatter: btnDetails">5</th>            

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
<div id="toolbar2">
    <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a> -->
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New Project" data-options="closed: true,modal:true" style="width: 1200px; height: 550px; padding:10px; top: 20px;">    
    <form id="frm_insert" method="post"  novalidate enctype="multipart/form-data">
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Project Data</b></legend>
            <div style="float:left; width:33%;">
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Project Number</span>
                    <input style="width:60%;" name="project_number" id="project_number" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Project Name</span>
                    <input style="width:60%;" name="project_name" id="project_name" required="true" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Project Level</span>
                    <input style="width:60%;" name="project_level" id="project_level" readonly class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Project Category ID</span>
                    <input style="width:60%;" name="project_category_id" id="project_category_id" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Project Category</span>
                    <input style="width:60%;" name="project_category" id="project_category" readonly class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Phase Id</span>
                    <input style="width:60%;" name="phase_id" id="phase_id" required="" class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <input style="width:60%;" name="phase_name" id="phase_name_string" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Phase</span>
                    <input style="width:60%;" name="phase_combo" id="phase_name" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Event</span>
                    <input style="width:60%;" name="event" id="event" class="easyui-textbox">
                </div>
            </div>
            <div style="float:left; width:33%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment 1</span>
                    <input style="width:60%;" name="attachment_upload1" id="attachment_upload1" class="easyui-filebox upload-trigger">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment 2</span>
                    <input style="width:60%;" name="attachment_upload2" id="attachment_upload2" class="easyui-filebox upload-trigger">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment 3</span>
                    <input style="width:60%;" name="attachment_upload3" id="attachment_upload3" class="easyui-filebox upload-trigger">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment 4</span>
                    <input style="width:60%;" name="attachment_upload4" id="attachment_upload4" class="easyui-filebox upload-trigger">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Attachment 1</span>
                    <input style="width:60%;" name="attachment1" id="attachment1" class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Attachment 2</span>
                    <input style="width:60%;" name="attachment2" id="attachment2" class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Attachment 3</span>
                    <input style="width:60%;" name="attachment3" id="attachment3" class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Attachment 4</span>
                    <input style="width:60%;" name="attachment4" id="attachment4" class="easyui-textbox">
                </div>
            </div>
            <div style="float:left; width:33%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment 5</span>
                    <input style="width:60%;" name="attachment_upload5" id="attachment_upload5" class="easyui-filebox upload-trigger">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Attachment 5</span>
                    <input style="width:60%;" name="attachment5" id="attachment5" class="easyui-textbox">
                </div>
                <div style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #17a2b8; border-radius: 3px; width: 85%;">
                    <b style="color: #17a2b8;"><i class="fa fa-info-circle"></i> Upload Rules:</b>
                    <ul style="margin: 5px 0 0 0; padding-left: 20px; font-size: 11px; color: #555; line-height: 1.5;">
                        <li>Max file size: <b>5 MB</b> per file.</li>
                        <li>Allowed formats: <b>.PDF, .JPG, .PNG</b></li>
                        <li>Ensure documents are clearly legible.</li>
                    </ul>
                </div>
            </div>
        </fieldset>
        <div style="clear:both;"></div>
        <table id="dg2" class="easyui-datagrid" style="width:100%; height: 300px;" title="Project Detail Lists" toolbar="#toolbar2"></table>
        <div style="margin-top: 15px; width: 100%;">
            <textarea name="description" id="description" style="width:100%; height:150px;"></textarea>
        </div>
    </form>
</div>
<div id="dlg_description" class="easyui-dialog" title="Project Description" data-options="closed: true, modal:true" style="width: 700px; height: 450px; padding:20px; top: 50px;">
    <div id="content_description" style="font-size: 14px; line-height: 1.6;">
        </div>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('npd/create_tasks/print') ?>" style="width: 100%;" hidden></iframe>
<script>

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open').dialog('center');
        $('#dg2').datagrid('loadData', {"total":0,"rows":[]});         
        url_save = '<?= base_url('npd/create_tasks/create') ?>';
        
        $('#frm_insert').form('clear');
        $("#start_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#end_date").datebox('setValue', "<?= date("Y-m-t") ?>");
        
        // $.ajax({
        //     type : "post",
        //     url : "<?= base_url('npd/create_tasks/autoid')?>",
        //     dataType : "html",
        //     success : function(response){
        //         $('#number').textbox('setValue', response);             
        //     }
        // });

        $('#description').summernote('code', '');
    }

    function addTable(link = "") {
        $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'phase_name_sub',
                    width: 200,
                    halign: 'center',
                    title: "Phase Sub.",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'phase_sub_id',
                    width: 150,
                    hidden: true,
                    halign: 'center',
                    title: "Phase Sub ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'module',
                    width: 150,
                    halign: 'center',
                    title: "Module",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                 }, {
                    field: 'link',
                    width: 150,
                    halign: 'center',
                    title: "Link",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'menus_id',
                    width: 150,
                    hidden: true,
                    halign: 'center',
                    title: "Menu Id",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'department_id',
                    hidden: true,
                    editor: { 
                        type: 'textbox' 
                    }
                }, {
                    field: 'department', 
                    width: 150,
                    align: 'center',
                    title: "Department",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'sub_department',
                    width: 150,
                    halign: 'center',
                    title: "Sub Department",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'level',
                    hidden: true,
                    width: 80,
                    halign: 'center',
                    align: 'center', 
                    title: "Level",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'start_date',
                    width: 100,
                    halign: 'center',
                    title: "Start Date",
                    editor: {
                        type: 'datebox'
                    }
                }, {
                    field: 'end_date',
                    width: 100,
                    halign: 'center',
                    title: "End Date",
                    editor: {
                        type: 'datebox'
                    }
                }, {
                    field: 'remark',
                    width: 200,
                    halign: 'center',
                    title: "Remarks",
                    editor: {
                        type: 'textbox'
                    }
                }]
            ],
            onClickCell: onClickCell,
            onBeginEdit: function(rowIndex, rowData) {
                if (rowData.department_id) {
                    var ed_sub = $(this).datagrid('getEditor', { 
                        index: rowIndex, 
                        field: 'sub_department' 
                    });
                    
                    if (ed_sub) {
                        var url_sub = '<?= base_url('master/sub_departments/reads') ?>?department_id=' + rowData.department_id;
                        
                        $(ed_sub.target).combobox('reload', url_sub);

                        if (rowData.sub_department) {
                            setTimeout(function() {
                                $(ed_sub.target).combobox('setValue', rowData.sub_department);
                            }, 100);
                        }
                    }
                }
            }
        });
    }

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

    // function append() {
    //     var project_name = $("#project_name").combogrid('getValue');
    //     if (project_name != "") {
    //         if (endEditing()) {
    //             var firstRowIndex = $('#dg2').datagrid('getRows').length > 0 ? 0 : undefined;
    //             $('#dg2').datagrid('insertRow', {
    //                 index: firstRowIndex,
    //                 row: {
    //                     qty: '0'
    //                 }
    //             });
    //             $('#dg2').datagrid('selectRow', firstRowIndex).datagrid('beginEdit', firstRowIndex);
    //         }
    //     } else {
    //         toastr.error("Please Choose Project Name first");
    //     }
    // }

    function removeit() {
        var row = $('#dg2').datagrid('getSelected');
        if (row) {
            var rowIndex = $('#dg2').datagrid('getRowIndex', row);
            $('#dg2').datagrid('deleteRow', rowIndex);
            if (editIndex == rowIndex) {
                editIndex = undefined;
            }
        } else {
            toastr.warning("Please select a product item to remove!", "Information");
        }
    }

    // EDIT DATA
    // function update() {
    //     var row = $('#dg').datagrid('getSelected');
    //     if (row) {
    //         $('#dlg_insert').dialog('open').dialog('center').dialog('setTitle', 'Edit Task');
            
    //         $('#frm_insert').form('load', row);
            
    //         for (var i = 1; i <= 5; i++) {
    //             var fileName = row['attachment' + i];
    //             if (fileName) {
    //                 $('#attachment_upload' + i).filebox('setText', fileName);
    //             } else {
    //                 $('#attachment_upload' + i).filebox('clear');
    //             }
    //         }
           
    //         if (row.description) {
    //             $('#description').summernote('code', row.description);
    //         } else {
    //             $('#description').summernote('code', '');
    //         }

    //         // Kosongkan datagrid terlebih dahulu
    //         $('#dg2').datagrid('loadData', []); 
            
    //         // Ambil data dari tabel detail menggunakan AJAX
    //         $.ajax({
    //             url: '<?= base_url('npd/create_tasks/get_details') ?>',
    //             type: 'GET',
    //             data: { task_id: row.id },
    //             dataType: 'json',
    //             success: function(response) {
    //                 // Masukkan data ke dalam datagrid
    //                 $('#dg2').datagrid('loadData', response);
    //             },
    //             error: function() {
    //                 toastr.error("Failed to load task details.");
    //             }
    //         });

    //         url_save = '<?= base_url('npd/create_tasks/update') ?>?id=' + row.id; 
            
    //     } else {
    //         toastr.warning("Please select one of the data in the table first!", "Information");
    //     }
    // }

    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open').dialog('center').dialog('setTitle', 'Edit Task');
            
            // 1. Load data standard ke form
            $('#frm_insert').form('load', row);
            
            // 2. TANGANI COMBOBOX MULTIPLE
            // Asumsi 'phase_name' adalah string "Phase A,Phase B" dari database
            if (row.phase_name) {
                var phaseArray = row.phase_name.split(','); 
                $('#phase_name').combobox('setValues', phaseArray); // 'setValues' (pakai 's') untuk array
            }
            
            // 3. Load Filebox & Summernote (kode Anda sebelumnya sudah benar)
            for (var i = 1; i <= 5; i++) {
                var fileName = row['attachment' + i];
                if (fileName) {
                    $('#attachment_upload' + i).filebox('setText', fileName);
                } else {
                    $('#attachment_upload' + i).filebox('clear');
                }
            }
            
            if (row.description) {
                $('#description').summernote('code', row.description);
            } else {
                $('#description').summernote('code', '');
            }

            // 4. Load Datagrid Detail
            $('#dg2').datagrid('loadData', []); 
            $.ajax({
                url: '<?= base_url('npd/create_tasks/get_details') ?>',
                type: 'GET',
                data: { task_id: row.id },
                dataType: 'json',
                success: function(response) {
                    $('#dg2').datagrid('loadData', response);
                }
            });

            url_save = '<?= base_url('npd/create_tasks/update') ?>?id=' + row.id; 
        } else {
            toastr.warning("Please select one of the data in the table first!");
        }
    }

    // DELETE DATA PROJECT
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete ' + rows.length + ' selected project(s)?', function(r) {
                if (r) {
                    var promises = []; 
                    
                    for (var i = 0; i < rows.length; i++) {
                        var request = $.ajax({
                            method: 'post',
                            url: '<?= base_url('npd/create_tasks/delete') ?>',
                            data: {
                                id: rows[i].id
                            },
                            dataType: 'json' 
                        });
                        
                        promises.push(request);
                    }
                    
                    $.when.apply($, promises).done(function() {
                        $('#dg').datagrid('reload');
                        $('#dg').datagrid('clearSelections'); 
                        toastr.success("Data successfully deleted!");
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        toastr.error("Error occurred while deleting some data.");
                        $('#dg').datagrid('reload'); 
                    });
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('npd/create_tasks/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //ADD DATA
        addTable();

        //SETTING DATAGRID EASYUI
        // $('#dg').datagrid({
        //     url: '<?= base_url('npd/create_tasks/datatables') ?>',
        //     pagination: true,
        //     clientPaging: false,
        //     remoteFilter: true,
        //     rownumbers: true,
        //     fit: true,
        //     pageList: [20, 50, 100, 500, 1000],
        //     pageSize: 20,
        // }).datagrid('enableFilter');

        $('#dg').datagrid({
            url: '<?= base_url('npd/create_tasks/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.project_number + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('npd/create_tasks/datatableDetails?project_number=') ?>' + window.btoa(row.project_number),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'phase_name_sub',
                            title: 'Phase Sub Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'project_category',
                            title: 'Project Category',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'start_date',
                            title: 'Start Date',
                            halign: 'center',
                            width: 100
                        }, {
                            field: 'end_date',
                            title: 'End Date',
                            halign: 'center',
                            width: 100
                         }, {
                            field: 'duration',
                            title: 'Duration',
                            halign: 'center',
                            width: 100
                        }, {
                            field: 'level',
                            title: 'Level',
                            align: 'center',
                            width: 100,
                            styler: cellStyler
                        }, {
                            field: 'department',
                            title: 'Department',
                            halign: 'center',
                            width: 150,
                        }, {
                            field: 'sub_department',
                            title: 'Department Sub',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'remark',
                            title: 'Remarks',
                            width: 150,
                            halign: 'center',
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
        }).datagrid('enableFilter');

        // SAVE DATA 
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    if (!$('#frm_insert').form('validate')) {
                        toastr.warning("Please fill all required fields in the form!");
                        return false;
                    }

                    endEditing();
                    $("#dg2").datagrid('acceptChanges');
                    var rows = $('#dg2').datagrid('getRows');

                    if (rows.length === 0) {
                        toastr.warning("Please add at least one product detail!");
                        return false;
                    }

                    var formData = new FormData($('#frm_insert')[0]);

                    var desc_value = $('#description').summernote('code');
                    formData.set('description', desc_value);

                    formData.append('details', JSON.stringify(rows));

                    $.ajax({
                        type: "POST",
                        url: url_save,
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: "json",
                        success: function(result) {
                            if (result.theme === "success" || result.status === "success") {
                                
                                $('#dlg_insert').dialog('close');
                                $('#dg').datagrid('reload'); 

                                Swal.fire({
                                    title: 'Success',
                                    text: result.message,
                                    icon: 'success',
                                    confirmButtonText: 'Ok',
                                    allowOutsideClick: false,
                                });

                            } else {
                                toastr.error(result.message);
                               
                                $('#dlg_insert').dialog('close');
                                $('#dg').datagrid('reload'); 
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            toastr.error("Server Error: " + textStatus);
                        }
                    });
                }
            }]
        });
    });

    $('#project_name').combogrid({
        url: '<?= base_url('npd/create_tasks/readProjects'); ?>',
        panelWidth: 400,
        idField: 'name',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Project Name",
        columns: [
            [{
                field: 'number',
                title: 'Project Number',
                width: 150
            }, {
                field: 'name',
                title: 'Project Name',
                width: 250
            }, ]
        ],
        onSelect: function (index, row) {
            $('#project_number').textbox('setValue',row.number);
            $('#project_level').textbox('setValue',row.level);
            $('#project_category_id').textbox('setValue',row.project_category_id);
            $('#project_category').textbox('setValue',row.project_category_name);
        }
    });

    $('#phase_name').combobox({
        url: '<?= base_url('npd/create_tasks/readPhases'); ?>',
        valueField: 'name', 
        textField: 'name',
        multiple: true,
        prompt: 'Choose Phase Name(s)',
        onChange: function(newValues, oldValues) {
            
            var phaseNamesStr = newValues.join(',');
            $('#phase_name_string').textbox('setValue', phaseNamesStr);


            var allData = $(this).combobox('getData'); 
            var selectedIds = [];
            
            for (var i = 0; i < newValues.length; i++) {
                for (var j = 0; j < allData.length; j++) {
                    if (allData[j].name === newValues[i]) {
                        selectedIds.push(allData[j].id);
                        break;
                    }
                }
            }
            
            var phaseIdsStr = selectedIds.join(',');
            $('#phase_id').textbox('setValue', phaseIdsStr);

            if (phaseIdsStr !== "") {
                $.ajax({
                    url: '<?= base_url('npd/create_tasks/read_by_phase_ids'); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: { phase_ids: phaseIdsStr },
                    success: function(response) {
                        var pLevel = $('#project_level').textbox('getValue');
                        for (var k = 0; k < response.length; k++) {
                            response[k].level = pLevel; 
                        }
                        $('#dg2').datagrid('loadData', response);
                    },
                    error: function() {
                        toastr.error("Failed to load Sub Phases.");
                    }
                });
            } else {
                $('#dg2').datagrid('loadData', []);
            }
        }
    });

    $('#description').summernote({
        height: 150,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture']]
        ],
        callbacks: {
            onImageUpload: function(image) {
                for (var i = 0; i < image.length; i++) {
                    uploadImageDesc(image[i], this);
                }
            }
        }
    });

    function uploadImageDesc(image, editor) {
        var data = new FormData();
        data.append("file", image);
        
        $.ajax({
            url: '<?= base_url('npd/create_tasks/upload_image_summernote') ?>', 
            cache: false,
            contentType: false,
            processData: false,
            data: data,
            type: "POST",
            success: function(url) {
                var cleanUrl = url.trim();

                if (cleanUrl.toLowerCase().endsWith('.pdf')) {
                    var pdfLink = '<br><a href="' + cleanUrl + '" target="_blank" style="color: red; font-weight: bold; text-decoration: underline;">Open / Download Dokumen PDF</a><br>';
                    
                    $(editor).summernote('pasteHTML', pdfLink);
                } else {
                    $(editor).summernote('insertImage', cleanUrl);
                }
            },
            error: function(data) {
                toastr.error("Gagal meng-upload file ke editor.");
            }
        });
    }

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

    function statusformat(value, row) {
        // active=0 / inactive=1
        if (value == '1') {
            return "<b style='color:red;'>INACTIVE</b>";
        } else if (value == 'footer') {
            return "";
        } else {
            return "<b style='color:green;'>ACTIVE</b>";
        }
    }
    function statusStyle(value, row, index) {
        if (value == '1') {
            return 'background-color:#FFC8C8;';
        } else if (value == 'footer') {
            return "";
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function cellStyler(value, row, index) {
        if (value == 'LOW') {
            return 'background: #53D636; color:white;';
        } else if (value == 'MEDIUM'){
            return 'background: #FFFF00; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    // Formatter Button
    function btnDescription(val, row) {
        var desc = "viewDescription('" + row.project_number + "')";
        return '<a href="javascript:void(0)" class="btn btn-primary w-100" onClick="' + desc + '" style="pointer-events: visible; opacity:1; padding: 2px 5px;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewDescription(project_number) {
        var rows = $('#dg').datagrid('getRows');
        var dataRow = null;

        for (var i = 0; i < rows.length; i++) {
            if (rows[i].project_number === project_number) {
                dataRow = rows[i];
                break; 
            }
        }
        if (dataRow) {
            var descHtml = dataRow.description ? dataRow.description : '<p class="text-muted"><i>No description available for this project.</i></p>';
            $('#content_description').html(descHtml);
            
            $('#dlg_description').dialog('setTitle', 'Project Description : ' + dataRow.project_number).dialog('open').dialog('center');
        } else {
            toastr.error("Project data not found!");
        }
    }

    function btnDetails(val, row, index) {
        if (val != null && val !== "") {
            return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('assets/image/create_tasks/') ?>' + val + '" onclick="event.stopPropagation();" style="pointer-events: visible; opacity:1; padding: 2px 5px;"><i class="fa fa-eye"></i> View</a>';
        } else {
            return '-';
        }
    }

    $('.upload-trigger').filebox({
        buttonText: 'Browse File',
        accept: '.jpg, .png, .pdf',
        onChange: function () {
            // 1. Tangkap ID dari filebox yang sedang diklik/dipilih
            var currentId = $(this).attr('id'); 
            
            // 2. Ambil angkanya saja untuk menentukan targetnya
            var indexNumber = currentId.replace('attachment_upload', ''); 
            
            // 3. Tentukan ID elemen target
            var targetId = '#attachment' + indexNumber; 

            // --- PROSES UPLOAD AJAX ---
            var files = $(this).filebox('files');
            if (files.length === 0) return; // Jika batal pilih file, hentikan

            var formData = new FormData();
            formData.append('file', files[0], files[0].name);

            // Munculkan indikator loading (opsional tapi disarankan)
            $.messager.progress({ title: 'Please waiting', msg: 'Uploading data...' });

            $.ajax({
                url: '<?= base_url('npd/create_tasks/uploadatt') ?>',
                type: 'post',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (data) {
                    $.messager.progress('close'); // Tutup loading

                    if (data.success == true) {
                        toastr.success(data.message);
                        
                        // 4. Set Value ke textbox HANYA pada target yang sesuai
                        $(targetId).textbox('setValue', data.filename); 
                        
                    } else {
                        toastr.error(data.message);
                        // Jika gagal, kosongkan kembali fileboxnya
                        $('#' + currentId).filebox('clear'); 
                    }
                },
                error: function() {
                    $.messager.progress('close');
                    toastr.error("Server error while uploading.");
                    $('#' + currentId).filebox('clear');
                }
            });
        }
    });

</script>
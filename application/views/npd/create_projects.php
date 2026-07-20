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
            <th rowspan="2" data-options="field:'status_time',width:100,align:'center',styler:stylerStatusTime,formatter:formatStatusTime">Status</th>            
            <th rowspan="2" data-options="field:'status_project',width:100,align:'center', styler:cellStylerStatus, formatter:cellFormatter">Project</th>
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Project No</th>
            <th rowspan="2" data-options="field:'name',width:150,halign:'center'">Project Name</th>
            <th rowspan="2" data-options="field:'division',width:100,halign:'center'">Division</th>
            <th rowspan="2" data-options="field:'btn',width:80,halign:'center',align:'right',formatter:btnDescription">Description</th>
            <!-- <th rowspan="2" data-options="field:'owner',width:100,halign:'center'">Owner</th> -->
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer</th>
            <th rowspan="2" data-options="field:'model',width:150,halign:'center'">Model</th>
            <th rowspan="2" data-options="field:'start_date',width:100,halign:'center'">Start Date</th>
            <th rowspan="2" data-options="field:'end_date',width:100,halign:'center'">End Date</th>
            <th rowspan="2" data-options="field:'duration',width:150,halign:'center',align:'center'">Duration</th>
            <th rowspan="2" data-options="field:'progress',width:150,halign:'center',align:'center'">% Progress</th>
            <th rowspan="2" data-options="field:'level',width:80,halign:'center',align:'center', styler:cellStyler">Level</th>
            <th rowspan="2" data-options="field:'project_category_name',width:150,halign:'center'">Category</th>
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
<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New Project" data-options="closed: true,modal:true" style="width: 900px; height: 550px; padding:10px; top: 20px;">    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Project Data</b></legend>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Project No</span>
                    <input style="width:60%;" name="number" id="number" class="easyui-textbox" readonly prompt="Auto">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Project Name</span>
                    <input style="width:60%;" name="name" id="name" required="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division</span>
                    <input style="width:60%;" name="division_id" id="division_id" class="easyui-combobox" required="true">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required="true" class="easyui-combogrid">
                </div>
            </div>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Model</span>
                    <input style="width:60%;" name="model" id="model" required="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Start/End Date</span>
                    <input style="width:29%;" name="start_date" id="start_date" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox" required="true">
                    -
                    <input style="width:29%;" name="end_date" id="end_date" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox" required="true">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Level</span>
                    <select style="width:60%;" name="level" id="level" required="true" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose Level</option>
                        <option value="LOW">LOW</option>
                        <option value="MEDIUM">MEDIUM</option>
                        <option value="HIGH">HIGH</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" name="project_category_id" id="project_category_id" required="true" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <div style="clear:both;"></div>
        <table id="dg2" class="easyui-datagrid" style="width:100%; height: 200px;" title="Project Detail Lists" toolbar="#toolbar2"></table>
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
<iframe id="printout" src="<?= base_url('npd/create_projects/print') ?>" style="width: 100%;" hidden></iframe>
<script>

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open').dialog('center');
        $('#dg2').datagrid('loadData', {"total":0,"rows":[]});         
        url_save = '<?= base_url('npd/create_projects/create') ?>';
        
        $('#frm_insert').form('clear');
        $("#start_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        $("#end_date").datebox('setValue', "<?= date("Y-m-t") ?>");
        
        $.ajax({
            type : "post",
            url : "<?= base_url('npd/create_projects/autoid')?>",
            dataType : "html",
            success : function(response){
                $('#number').textbox('setValue', response);             }
        });

        $('#description').summernote('code', '');
    }

    function addTable(link = "") {
        $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'item_fg_number',
                    width: 200,
                    halign: 'center',
                    title: "Product Number",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'item_fg_name',
                    width: 200,
                    halign: 'center',
                    title: "Product Customer",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'volume',
                    width: 100,
                    align: 'center',
                    title: "Volume",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2
                        }
                    }
                  }, {
                    field: 'volume_unit',
                    width: 150,
                    align: 'center',
                    title: "Volume Unit",
                    editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('npd/volume_units/reads') ?>',
                            editable:false,
                            valueField: 'name',
                            textField: 'name',
                            prompt: 'Choose Currencies'
                        }
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
            onClickCell: onClickCell
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

    function append() {
        var customer_id = $("#customer_id").combogrid('getValue');
        if (customer_id != "") {
            if (endEditing()) {
                var firstRowIndex = $('#dg2').datagrid('getRows').length > 0 ? 0 : undefined;
                $('#dg2').datagrid('insertRow', {
                    index: firstRowIndex,
                    row: {
                        qty: '0'
                    }
                });
                $('#dg2').datagrid('selectRow', firstRowIndex).datagrid('beginEdit', firstRowIndex);
            }
        } else {
            toastr.error("Please Choose Customer first");
        }
    }

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
    //         $('#dlg_insert').dialog('open').dialog('center').dialog('setTitle', 'Edit Project');
            
    //         $('#frm_insert').form('load', row);
            
    //         if (row.description) {
    //             $('#description').summernote('code', row.description);
    //         } else {
    //             $('#description').summernote('code', '');
    //         }

    //         var detailData = [];
    //         if (row.details) {
    //             try {
    //                 detailData = typeof row.details === 'string' ? JSON.parse(row.details) : row.details;
    //             } catch (e) {
    //                 console.error("Format JSON detail tidak valid", e);
    //             }
    //         }
    //         $('#dg2').datagrid('loadData', detailData);

    //         url_save = '<?= base_url('npd/create_projects/update') ?>?id=' + row.id; 
            
    //     } else {
    //         toastr.warning("Please select one of the data in the table first!", "Information");
    //     }
    // }

    // EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        
        if (row) {
            $('#dlg_insert').dialog('open').dialog('center').dialog('setTitle', 'Edit Project');
            
            $('#frm_insert').form('load', row);
            
            if (row.description) {
                $('#description').summernote('code', row.description);
            } else {
                $('#description').summernote('code', '');
            }

            // --- BAGIAN YANG DIRUBAH ---
            // Karena data tidak ada di row, kita ambil dari fungsi datatableDetails
            var url_details = '<?= base_url('npd/create_projects/datatableDetails?number=') ?>' + window.btoa(row.number);
            
            // Lakukan AJAX Get untuk mengambil data detailnya lalu load ke datagrid dg2
            $.get(url_details, function(data) {
                var detailData = typeof data === 'string' ? JSON.parse(data) : data;
                $('#dg2').datagrid('loadData', detailData);
            });
            // ---------------------------

            url_save = '<?= base_url('npd/create_projects/update') ?>?id=' + row.id; 
            
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
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
                            url: '<?= base_url('npd/create_projects/delete') ?>',
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
        window.location.assign('<?= base_url('npd/create_projects/print/excel') ?>');
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
        //     url: '<?= base_url('npd/create_projects/datatables') ?>',
        //     pagination: true,
        //     clientPaging: false,
        //     remoteFilter: true,
        //     rownumbers: true,
        //     fit: true,
        //     pageList: [20, 50, 100, 500, 1000],
        //     pageSize: 20,
        // }).datagrid('enableFilter');

        $('#dg').datagrid({
            url: '<?= base_url('npd/create_projects/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.number + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('npd/create_projects/datatableDetails?number=') ?>' + window.btoa(row.number),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'item_fg_number',
                            title: 'Product No.',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'volume',
                            title: 'Volume',
                            halign: 'center',
                            align: 'right',
                            width: 100,
                        }, {
                            field: 'volume_unit',
                            title: 'Volume Unit',
                            halign: 'center',
                            width: 100
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

                    for (var i = rows.length - 1; i >= 0; i--) {
                        var val = rows[i].item_fg_number; 
                        
                        if (val === undefined || val === null || val === "") {
                            var rowIndex = $('#dg2').datagrid('getRowIndex', rows[i]);
                            $('#dg2').datagrid('deleteRow', rowIndex);
                        }
                    }

                    var finalRows = $('#dg2').datagrid('getRows');

                    if (finalRows.length === 0) {
                        toastr.warning("Please add and fill at least one valid product detail!");
                        return false; 
                    }

                    var formData = new FormData($('#frm_insert')[0]);
                    var desc_value = $('#description').summernote('code');
                    formData.set('description', desc_value);

                    formData.append('details', JSON.stringify(finalRows));

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
                                Swal.fire('Error', result.message, 'error');
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            toastr.error("Server Error: " + textStatus);
                            console.log(jqXHR.responseText);
                        }
                    });
                }
            }]
        });
    });

    $('#phase_name').combobox({
        url:'<?= base_url('npd/project_phases/reads'); ?>',
        valueField:'name',
        textField:'name',
        prompt: 'Choose Phase Name',
        onSelect: function(phase_name){
            $('#phase_id').textbox('setValue',phase_name.id);
        }
    });
    
    $('#division_id').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'id',
        textField: 'number',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
    });

    $('#customer_id').combogrid({
        url: '<?= base_url('master/customers/reads/'); ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Customer",
        columns: [
            [{
                field: 'number',
                title: 'Customer No',
                width: 150
            }, {
                field: 'name',
                title: 'Customer Name',
                width: 250
            }, ]
        ],
    });

    $('#project_category_id').combogrid({
        url: '<?= base_url('npd/project_categorys/reads/'); ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Project Category",
        columns: [
            [{
                field: 'code',
                title: 'Code',
                width: 150
            }, {
                field: 'name',
                title: 'Category Name',
                width: 250
            }, ]
        ],
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
            url: '<?= base_url('npd/create_projects/upload_image_summernote') ?>', 
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

    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 1) {
            return 'COMPLETE';
        } else {
            return 'UNCOMPLETE';
        }
    };

    function cellStylerStatus(value, row, index) {
        if (value == 1) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
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

    function stylerStatusTime(value, row, index) {
        if (value === 'Completed') {
            // Hijau sesuai Chart (#28a745), Teks Putih
            return 'background-color: #28a745; color: #ffffff; border-radius: 3px;'; 
            
        } else if (value === 'Overdue') {
            // Merah sesuai Chart (#dc3545), Teks Putih
            return 'background-color: #dc3545; color: #ffffff; border-radius: 3px;';
            
        } else if (value === 'On Progress') {
            // Kuning sesuai Chart (#ffc107), Teks Gelap (#212529) agar kontras/terbaca
            return 'background-color: #ffc107; color: #212529; border-radius: 3px;';
        }
        return '';
    }

    function formatStatusTime(value, row, index) {
        if (value) {
            return '<span style="font-size: 11px; letter-spacing: 0.5px;">' + value.toUpperCase() + '</span>';
        }
        return '-';
    }

    // Formatter Button
    function btnDescription(val, row) {
        var desc = "viewDescription('" + row.number + "')";
        return '<a href="javascript:void(0)" class="btn btn-primary w-100" onClick="' + desc + '" style="pointer-events: visible; opacity:1; padding: 2px 5px;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewDescription(number) {
        var rows = $('#dg').datagrid('getRows');
        var dataRow = null;

        for (var i = 0; i < rows.length; i++) {
            if (rows[i].number === number) {
                dataRow = rows[i];
                break; 
            }
        }
        if (dataRow) {
            var descHtml = dataRow.description ? dataRow.description : '<p class="text-muted"><i>No description available for this project.</i></p>';
            $('#content_description').html(descHtml);
            
            $('#dlg_description').dialog('setTitle', 'Project Description : ' + dataRow.number).dialog('open').dialog('center');
        } else {
            toastr.error("Project data not found!");
        }
    }
</script>
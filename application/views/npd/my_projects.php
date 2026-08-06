<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    /* Memaksa semua gambar di dalam deskripsi agar tidak melebihi lebar kotak dialog */
    #content_description img {
        max-width: 100% !important;
        height: auto !important;
    }
</style>
<!-- TABLE DATAGRID -->
<div style="padding: 15px;">
    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
        
        <div style="flex: 1; background-color: #d1ecf1; border-radius: 6px; padding: 12px 18px !important; display: flex; align-items: center; gap: 15px; border: 1px solid #bee5eb;">
            <i class="fa fa-file-text" style="color: #0c5460; font-size: 32px !important; width: 35px; text-align: center;"></i> 
            <div style="line-height: 1.2;">
                <div style="font-size: 13px !important; color: #0c5460; font-weight: bold; text-transform: uppercase;">Total Tasks</div>
                <div style="font-size: 24px !important; font-weight: 700; color: #0c5460;" id="count_total">0</div>
            </div>
        </div>

        <div style="flex: 1; background-color: #d4edda; border-radius: 6px; padding: 12px 18px !important; display: flex; align-items: center; gap: 15px; border: 1px solid #c3e6cb;">
            <i class="fa fa-check-circle" style="color: #155724; font-size: 32px !important; width: 35px; text-align: center;"></i>
            <div style="line-height: 1.2;">
                <div style="font-size: 13px !important; color: #155724; font-weight: bold; text-transform: uppercase;">Complete Tasks</div>
                <div style="font-size: 24px !important; font-weight: 700; color: #155724;" id="count_complete">0</div>
            </div>
        </div>

        <div style="flex: 1; background-color: #fff3cd; border-radius: 6px; padding: 12px 18px !important; display: flex; align-items: center; gap: 15px; border: 1px solid #ffeeba;">
            <i class="fa fa-edit" style="color: #856404; font-size: 32px !important; width: 35px; text-align: center;"></i>
            <div style="line-height: 1.2;">
                <div style="font-size: 13px !important; color: #856404; font-weight: bold; text-transform: uppercase;">Uncomplete Tasks</div>
                <div style="font-size: 24px !important; font-weight: 700; color: #856404;" id="count_uncomplete">0</div>
            </div>
        </div>

        <div style="flex: 1; background-color: #f8d7da; border-radius: 6px; padding: 12px 18px !important; display: flex; align-items: center; gap: 15px; border: 1px solid #f5c6cb;">
            <i class="fa fa-clock-o" style="color: #721c24; font-size: 32px !important; width: 35px; text-align: center;"></i>
            <div style="line-height: 1.2;">
                <div style="font-size: 13px !important; color: #721c24; font-weight: bold; text-transform: uppercase;">Overdue Tasks</div>
                <div style="font-size: 24px !important; font-weight: 700; color: #721c24;" id="count_overdue">0</div>
            </div>
        </div>

    </div>

    <div style="display: flex; gap: 15px; width: 100%;">
        
        <div style="flex: 8; width: 100%;">
            
            <div id="tb_my_projects" style="padding: 6px 10px; display: flex; align-items: center; gap: 5px; background-color: #f4f4f4; border: 1px solid #ddd; border-bottom: none;">
                <span style="font-weight: bold; font-size: 12px; margin-right: 5px;">Status</span>
                <select id="filter_status" class="easyui-combobox" panelHeight="auto" style="width:120px">
                    <option value="ALL">ALL</option>
                    <option value="COMPLETE">COMPLETE</option>
                    <option value="UN COMPLETE">UN COMPLETE</option>
                </select>

                <span style="font-weight: bold; font-size: 12px; margin-left: 10px; margin-right: 5px;">Year</span>
                <select id="filter_year" class="easyui-combobox" panelHeight="auto" style="width:100px">
                    <option value="ALL">ALL</option>
                    <?php 
                        $current_year = date('Y');
                        for($y = $current_year; $y >= $current_year - 3; $y--) {
                            echo "<option value='$y'>$y</option>";
                        }
                    ?>
                </select>
                
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="fa fa-refresh" plain="true" title="Reload" onclick="reload()"></a>
                
                <a href="javascript:void(0)" class="easyui-linkbutton" style="background: #dc3545; color: white; border-radius: 4px; padding: 0 5px; margin-left: 5px;" iconCls="fa fa-times" onclick="cancelProject()">Cancel Project</a>
            </div>

            <table id="dg" class="easyui-datagrid" style="width:100%; height:430px; !important;" data-options="singleSelect:false, rownumbers:true">
                <thead>
                    <tr>
                        <th field="ck" checkbox="true"></th>
                        <th data-options="field:'project_number',width:110,halign:'center'">Project No</th>
                        <th data-options="field:'project_name',width:160,halign:'center'">Project Name</th>
                        <th data-options="field:'start_date',width:100,halign:'center',align:'center'">Start Date</th>
                        <th data-options="field:'end_date',width:100,halign:'center',align:'center'">End Date</th>
                        <th data-options="field:'duration',width:100,halign:'center',align:'center'">Duration</th>
                        <th data-options="field:'btn_detail',width:70,halign:'center',align:'center',formatter:btnViewDetail">Detail</th>
                        <th data-options="field:'btn_task',width:70,halign:'center',align:'center',formatter:btnViewTask">Task</th>
                        <th data-options="field:'total_task',width:80,halign:'center',align:'center'">Total Task</th>
                        <th data-options="field:'overdue',width:80,halign:'center',align:'center'">Overdue</th>
                        <th data-options="field:'progress',width:80,halign:'center',align:'center',formatter:formatProgress">% Progres</th>
                        <th data-options="field:'status_project',width:100,align:'center', styler:cellStylerStatus, formatter:cellFormatter">Status</th>
                    </tr>
                </thead>
            </table>
        </div>

        <div style="flex: 2; display: flex; flex-direction: column; gap: 15px;">
            <div id="panel_chart_project" class="easyui-panel" title="Chart By Project" style="width:100%; height: 232px; padding: 10px; display: flex; justify-content: center; align-items: center;">
                <canvas id="chartProject" style="max-height: 100%; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div id="dlg_detail" class="easyui-dialog" title="Information Project Detail" data-options="closed: true, modal:true" style="width: 650px; height: auto; max-height: 550px; padding: 0; top: 20px;"> 
    <div style="width: 100%; height: 100%; box-sizing: border-box; padding: 20px; overflow-y: auto; background-color: #ffffff;">
        <div style="margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
            <div style="font-size: 20px !important; color: #555;" id="number">-</div>
            <div style="font-size: 30px !important; font-weight: bold; color: #333;" id="name">-</div>
        </div>
        <table style="width: 100%; font-size: 12px; margin-bottom: 15px; line-height: 1.8;">
            <tr>
                <td style="width: 80px;">Customer</td>
                <td style="width: 15px;">:</td>
                <td id="customer_name" style="font-weight: bold;">-</td>
            </tr>
            <tr>
                <td>Division</td>
                <td>:</td>
                <td id="division" style="font-weight: bold;">-</td>
            </tr>
            <tr>
                <td>Model</td>
                <td>:</td>
                <td id="model_number" style="font-weight: bold;">-</td>
            </tr>
            <tr>
                <td>Start Date</td>
                <td>:</td>
                <td id="start_date" style="font-weight: bold;">-</td>
            </tr>
            <tr>
                <td>End Date</td>
                <td>:</td>
                <td id="end_date" style="font-weight: bold;">-</td>
            </tr>
        </table>
        <table id="dg_detail" class="easyui-datagrid" style="width:100%; height: 180px;" data-options="singleSelect:true, rownumbers:false">
            <thead>
                <tr>
                    <th data-options="field:'item_fg_number',width:150,halign:'center'">Product No</th>
                    <th data-options="field:'item_fg_name',width:200,halign:'center'">Product Name</th>
                    <th data-options="field:'volume',width:100,halign:'center',align:'center'">Volume</th>
                    <th data-options="field:'volume_unit',width:100,halign:'center',align:'center'">Volume Unit</th>
                </tr>
            </thead>
        </table>
        <div style="margin-top: 15px; font-size: 12px; color: #555; line-height: 1.5;" id="det_description">
            -
        </div>

    </div>
</div>

<div id="dlg_tasks" class="easyui-dialog" title="Project" data-options="closed: true,modal:true" style="width: 1050px; height: 500px; top: 20px;">
    <table id="dg_tasks" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th rowspan="2" data-options="field:'phase_name_sub',width:200,halign:'center'">Task Name</th>
                <th rowspan="2" data-options="field:'level',width:80,halign:'center'">Level</th>
                <th rowspan="2" data-options="field:'phase_name',width:200,halign:'center'">Phase Name</th>
                <th rowspan="2" data-options="field:'phase_name_sub',width:200,halign:'center'">Phase Name</th>
                <th rowspan="2" data-options="field:'btn',width:80,halign:'center',align:'right',formatter:btnDescription">Description</th>
                <th rowspan="2" data-options="field:'start_date',width:100,halign:'center'">Start Date</th>
                <th rowspan="2" data-options="field:'end_date',width:100,halign:'center'">End Date</th>
                <th rowspan="2" data-options="field:'module',width:150,halign:'center'">Modul</th>
                <th rowspan="2" data-options="field:'btn_modul',width:80,halign:'center',align:'center',formatter:btnCrossModule">Task</th>
                <th colspan="2" data-options="field:'',width:100,halign:'center'"> Upload</th>
                <th colspan="5" data-options="field:'',width:100,halign:'center'"> File</th>
                <th rowspan="2" data-options="field:'status_time',width:115,halign:'center',align:'center',formatter:formatTaskStatusTime">Status Time</th>
                <th rowspan="2" data-options="field:'status',width:100,halign:'center',align:'center',formatter:formatStatusTask">Status</th>
            </tr>
            <tr>
                <th data-options="field:'has_template',width:100,halign:'center',align:'center',formatter:formatTemplateBtn">Template</th>
                <th data-options="field:'has_upload',width:100,halign:'center',align:'center',formatter:formatUploadBtn">Upload</th>

                <th data-options="field:'attachment1',width:80,align:'center',formatter: btnAtt">1</th>            
                <th data-options="field:'attachment2',width:80,align:'center',formatter: btnAtt">2</th>            
                <th data-options="field:'attachment3',width:80,align:'center',formatter: btnAtt">3</th>            
                <th data-options="field:'attachment4',width:80,align:'center',formatter: btnAtt">4</th>            
                <th data-options="field:'attachment5',width:80,align:'center',formatter: btnAtt">5</th>
            </tr>
        </thead>
    </table>
</div>

<div id="dlg_description" class="easyui-dialog" title="Project Description" data-options="closed: true, modal:true" style="width: 700px; height: 450px; padding:0; top: 50px;">
    <div id="content_description" style="width: 100%; height: 100%; box-sizing: border-box; padding: 20px; font-size: 14px; line-height: 1.6; overflow-y: auto; background-color: #ffffff;">
    </div>
</div>

<div id="dlg_external_module" class="easyui-dialog" title="External Module" data-options="closed:true,modal:true" style="width:800px;height:600px;padding:15px;">
    <div id="external_content">
        </div>
</div>

<div id="dlg_upload_wrapper" class="easyui-dialog" style="width:100%; height:100%; padding:0; overflow:hidden;" data-options="closed:true, modal:true, maximized:true, noheader:true, border:false">
    <iframe id="iframe_upload" style="width:100%; height:100%; border:none; display:block;"></iframe>
</div>

<script>

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('npd/my_projects/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });
    });

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function btnViewDetail(val, row) {
        var detail = "viewDetail('" + row.project_number + "')";
        return '<a class="btn btn-primary w-100" onClick="' + detail + '" style="pointer-events: visible; opacity:1; padding: 2px 5px;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewDetail(project_number) {
        $("#dlg_detail").dialog('open');
        $('#dg_detail').datagrid({
            url: '<?= base_url('npd/my_projects/datatableDetails?project_number=') ?>' + btoa(project_number),
            pagination: false,
            rownumbers: true,
            onLoadSuccess: function(data) {
                if (data.rows && data.rows.length > 0) {
                    var firstRow = data.rows[0];
                    $('#number').text(firstRow.number);
                    $('#name').text(firstRow.name);
                    $('#customer_name').text(firstRow.customer_name);
                    $('#model_number').text(firstRow.model_number);
                    $('#start_date').text(firstRow.start_date);
                    $('#end_date').text(firstRow.end_date);
                    $('#division').text(firstRow.division);
                } else {
                    $('#number').text('-');
                    $('#name').text('-');
                    $('#customer_name').text('-');
                    $('#model_number').text('-');
                    $('#start_date').text('-');
                    $('#end_date').text('-');
                    $('#division').text('-');
                }
            }
        });
    }

    //Tombol View Task (Hijau)
    function btnViewTask(val, row) {
        var task = "viewTask('" + row.project_number + "')";
        return '<a class="btn btn-success w-100" onClick="' + task + '" style="pointer-events: visible; opacity:1; padding: 2px 5px;"><i class="fa fa-eye"></i> View</a>';
    }

    // Fungsi untuk View Task
    function viewTask(project_number) {
        $("#dlg_tasks").dialog('setTitle', 'Loading Project...'); 
        $("#dlg_tasks").dialog('open');

        $('#dg_tasks').datagrid({
            url: '<?= base_url('npd/my_projects/datatabletasks?project_number=') ?>' + btoa(project_number),
            pagination: false,
            rownumbers: true,
            onLoadSuccess: function(data) {
                if (data.rows && data.rows.length > 0) {
                    var firstRow = data.rows[0];
                    var titleText = firstRow.model_number ? firstRow.model_number : project_number;
                    $('#dlg_tasks').dialog('setTitle', 'Project ' + titleText);

                } else {
                    $('#dlg_tasks').dialog('setTitle', 'Project - ' + project_number);
                    
                }
            }
        });
    }

    function btnDescription(val, row, index) {
        var desc = "viewDescription(" + index + ")";
        return '<a href="javascript:void(0)" class="btn btn-primary w-100" onClick="' + desc + '" style="pointer-events: visible; opacity:1; padding: 2px 5px;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewDescription(index) {
        var rows = $('#dg_tasks').datagrid('getRows');
        var dataRow = rows[index];
        if (dataRow) {
            var descHtml = dataRow.description ? dataRow.description : '<p class="text-muted"><i>No description available for this task.</i></p>';
            $('#content_description').html(descHtml);
            var taskName = dataRow.phase_name_sub ? dataRow.phase_name_sub : 'Task';
            $('#dlg_description').dialog('setTitle', 'Description : ' + taskName).dialog('open').dialog('center');
        } else {
            toastr.error("Task data not found!");
        }
    }

    function btnAtt(val, row, index) {
        if (val != null && val !== "") {
            return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('assets/image/create_tasks/') ?>' + val + '" onclick="event.stopPropagation();" style="pointer-events: visible; opacity:1; padding: 2px 5px;"><i class="fa fa-eye"></i> View</a>';
        } else {
            return '-';
        }
    }

    function btnCrossModule(val, row, index) {
        if (row.link && row.link.trim() !== "") {
            var action = "goToModule(" + index + ")";
            return '<a class="btn btn-success w-100" onClick="' + action + '" style="pointer-events: visible; opacity:1; padding: 2px 5px;"><i class="fa fa-file"></i> Open</a>';
        } else {
            return '<span class="text-muted">-</span>';
        }
    }

    function goToModule(index) {
        var row = $('#dg_tasks').datagrid('getRows')[index]; 
        
        if (row && row.link && row.menus_id) {
            localStorage.setItem('trigger_add', 'yes');

            // 1. MUNCULKAN OVERLAY LOADING
            var loader = '<div id="smooth_loader" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999; display:flex; justify-content:center; align-items:center; backdrop-filter:blur(3px);">' +
                        '<div style="text-align:center; color:#fff;">' +
                        '<i class="fa fa-circle-o-notch fa-spin fa-4x fa-fw"></i>' +
                        '<h4 style="margin-top:15px; font-weight:normal; letter-spacing:1px;">Menyiapkan Form...</h4>' +
                        '</div></div>';
            $('body').append(loader);

            var targetUrl = '<?= site_url() ?>/' + row.link + '?menu_id=' + row.menus_id;
            
            // 2. IFRAME DIBUAT TRANSPARAN
            var content = '<iframe id="frame_modul" src="'+targetUrl+'" frameborder="0" style="width:100%;height:100%;display:block; opacity:0; transition: opacity 0.5s ease-in-out;"></iframe>';
            
            // 3. BUKA DIALOG PEMBUNGKUS
            var $dlg = $('<div id="dlg_outer_wrapper"></div>').dialog({
                noheader: true, 
                border: false, 
                content: content,
                modal: true,
                maximized: true,
                closed: false,
                onOpen: function() {
                    $(this).dialog('dialog').css('background', 'transparent');
                },
                onClose: function() {
                    // Cek apakah ada tanda sukses dari Child
                    if (localStorage.getItem('task_saved') === 'yes') {
                        localStorage.removeItem('task_saved');
                        
                        // Reload Datagrid Parent
                        $('#dg_tasks').datagrid('reload');
                        
                        toastr.success("Data save Sucess!");
                    }
                    
                    // Hancurkan dialog
                    $(this).dialog('destroy'); 
                }
            });

            // 4. DETEKSI KETIKA IFRAME SELESAI DIMUAT
            $('#frame_modul').on('load', function() {
                var $iframe = $(this);
                
              
                setTimeout(function() {
                    
                    // Hapus layar loading hitam secara perlahan
                    $('#smooth_loader').fadeOut(500, function() {
                        $(this).remove();
                    });

                    // Kembalikan background dialog menjadi putih
                    $dlg.dialog('dialog').css('background', '#ffffff');
                    
                    // Munculkan isi form yang SUDAH MEKAR secara perlahan
                    $iframe.css('opacity', '1');
                    
                }, 1500); // Sesuaikan angka ini jika dirasa masih kurang pas (misal: 1800 atau 2000)
            });
        }
    }

    // Formatter Tombol Download Template
    function formatTemplateBtn(value, row, index) {
        return '<a href="javascript:void(0)" onclick="cekFiturModul('+index+', \'download\')" style="display:inline-block; background-color:#dc3545; color:#ffffff; padding:2px 5px; border-radius:3px; text-decoration:none; font-size:12px; border:1px solid #c82333;">' +
            '<i class="fa fa-download"></i> Template</a>';
    }

    // Formatter Tombol Upload
    function formatUploadBtn(value, row, index) {
        return '<a href="javascript:void(0)" onclick="cekFiturModul('+index+', \'upload\')" style="display:inline-block; background-color:#ffc107; color:#212529; padding:2px 5px; border-radius:3px; text-decoration:none; font-size:12px; border:1px solid #e0a800;">' +
            '<i class="fa fa-upload"></i> Upload</a>';
    }

    function cekFiturModul(index, action) {
        var row = $('#dg_tasks').datagrid('getRows')[index];
        
        if (row && row.link && row.menus_id) {
            
            // 1. LOADING MODERN
            var modernLoader = '<div id="modern_loader" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.85); z-index:9999; display:flex; justify-content:center; align-items:center; flex-direction:column; backdrop-filter:blur(3px);">' +
                            '<i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw" style="color:#007bff;"></i>' +
                            '<span style="margin-top:15px; font-family:sans-serif; color:#333; font-weight:bold; font-size:16px;">Mengecek ketersediaan fitur...</span>' +
                            '</div>';
            $('body').append(modernLoader);

            var targetUrl = '<?= site_url() ?>/' + row.link + '?bypass=true&menu_id=' + row.menus_id;

            var $iframe = $('<iframe src="'+targetUrl+'" style="display:none; width:100%; height:100%; border:none;"></iframe>').appendTo('body');

            $iframe.on('load', function() {
                $('#modern_loader').remove(); 
                
                var win = this.contentWindow; 

                if (action === 'download') {
                    toastr.info('Checking template file...', 'Please Wait', { timeOut: 1500 });
                    var downloadUrl = '<?= site_url() ?>/' + row.link + '?menu_id=' + row.menus_id;
                    var $hiddenIframe = $('<iframe style="display:none;"></iframe>').appendTo('body');
                    $hiddenIframe.on('load', function() {
                        try {
                            var win = this.contentWindow;

                            if (win && typeof win.download_excel === 'function') {
                                win.download_excel(); // Picu download
                                toastr.success('The template file is being downloaded.');
                            } else {
                                toastr.warning('This module does not provide Template files for download.');
                            }
                        } catch (e) {
                            toastr.error('Failed to access the module.');
                            console.error('Download Error: ', e);
                        }

                        setTimeout(function() {
                            $hiddenIframe.remove();
                        }, 2000);
                    });

                    $hiddenIframe.attr('src', downloadUrl);
                }

                else if (action === 'upload') {
                    var $dialog = $('#dlg_upload_wrapper');
                    var $iframe = $('#iframe_upload');
                    
                    var uploadUrl = '<?= site_url() ?>/' + row.link + '?menu_id=' + row.menus_id + '&action=upload';

                    var loader = '<div id="smooth_loader_upload" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999; display:flex; justify-content:center; align-items:center; backdrop-filter:blur(3px);">' +
                                '<div style="text-align:center; color:#fff;">' +
                                '<i class="fa fa-circle-o-notch fa-spin fa-4x fa-fw"></i>' +
                                '<h4 style="margin-top:15px; font-weight:normal; letter-spacing:1px;">Menyiapkan Form Upload...</h4>' +
                                '</div></div>';
                    $('body').append(loader);

                    $iframe.css({
                        'opacity': '0',
                        'transition': 'opacity 0.5s ease-in-out'
                    });

                    $dialog.dialog({
                        onOpen: function() {
                            $(this).dialog('dialog').css('background', 'transparent');
                        }
                    }).dialog('open');
                    
                    $iframe.attr('src', uploadUrl);

                    $iframe.off('load').on('load', function() {
                        var win = this.contentWindow;
                        
                        if (typeof win.upload !== 'function') {
                            $('#smooth_loader_upload').remove();
                            $dialog.dialog('close');
                            toastr.warning('This module does not have Upload Data feature.');
                            return;
                        }

                        setTimeout(function() {
                            $('#smooth_loader_upload').fadeOut(500, function() {
                                $(this).remove();
                            });

                            $dialog.dialog('dialog').css('background', '#ffffff');
                            $iframe.css('opacity', '1');
                            
                        }, 1000); 
                    });
                }
            });

        } else {
            // Error menggunakan Toastr
            toastr.error('Link modul tidak valid.');
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

    function formatStatusTask(value, row, index) {
        var baseStyle = 'padding: 4px 0px; border-radius: 4px; font-size: 11px; display:inline-block; width: 100px; text-align: center; text-transform: uppercase;';

        if (value === 'COMPLETE') {
            // Warna asli dari Anda
            return '<span style="' + baseStyle + ' color: white; background-color: #53D636; border: 1px solid #53D636;">COMPLETE</span>';
        } else if (value === 'UNCOMPLETE') {
            // Warna asli dari Anda
            return '<span style="' + baseStyle + ' color: white; background-color: #FF5F5F; border: 1px solid #FF5F5F;">UNCOMPLETE</span>';
        } 
        return '-';
    }

    function formatTaskStatusTime(value, row, index) {
        var baseStyle = 'padding: 4px 0px; border-radius: 4px; font-size: 11px; display:inline-block; width: 100px; text-align: center; text-transform: uppercase;';

        if (value === 'Complete') {
            // Hijau Chart (#28a745)
            return '<span style="' + baseStyle + ' background-color: #28a745; color: #ffffff; border: 1px solid #28a745;">COMPLETE</span>';
        } 
        else if (value === 'Complete (Late)') {
            // Oranye (#fd7e14)
            return '<span style="' + baseStyle + ' background-color: #fd7e14; color: #ffffff; border: 1px solid #fd7e14;">COMP. (LATE)</span>'; // Disingkat sedikit agar muat rapi di 100px
        } 
        else if (value === 'Overdue') {
            // Merah Chart (#dc3545)
            return '<span style="' + baseStyle + ' background-color: #dc3545; color: #ffffff; border: 1px solid #dc3545;">OVERDUE</span>';
        } 
        else if (value === 'On Progress') {
            // Kuning Chart (#ffc107)
            return '<span style="' + baseStyle + ' background-color: #ffc107; color: #212529; border: 1px solid #ffc107;">ON PROGRESS</span>';
        }
        
        return '-';
    }

    function formatProgress(val, row) {
        if (val == null || val == "") val = 0;
        
        // Tentukan warna berdasarkan persentase
        var barColor = '#dc3545'; // Merah (0-30%)
        if (val > 30 && val <= 70) barColor = '#ffc107'; // Kuning (31-70%)
        if (val > 70) barColor = '#28a745'; // Hijau (>70%)

        // HTML untuk Progress Bar
        var html = '<div style="width:100%; border:1px solid #ccc; height:18px; border-radius:10px; overflow:hidden; background:#e9ecef; position:relative;">' +
                '<div style="width:' + val + '%; background:' + barColor + '; height:100%; transition: width 0.5s ease-in-out;"></div>' +
                '<div style="position:absolute; width:100%; top:0; left:0; text-align:center; font-size:10px; line-height:18px; font-weight:bold; color:#333;">' + 
                val + '%' + 
                '</div>' +
                '</div>';
        return html;
    }

    function loadChartProject() {
        if (typeof Chart === 'undefined') {
            console.error('Library Chart.js belum dimuat!');
            return;
        }

        // Ambil nilai filter tahun yang dipilih
        var selectedYear = $('#filter_year').combobox('getValue');

        // --- 1. UPDATE JUDUL PANEL BERDASARKAN TAHUN ---
        var titleText = (selectedYear === 'ALL' || selectedYear === '') 
                        ? 'Chart By Project - All Years' 
                        : 'Chart By Project - ' + selectedYear;
        
        $('#panel_chart_project').panel('setTitle', titleText);

        $.ajax({
            url: '<?= base_url("npd/my_projects/getChartProjectData") ?>',
            type: 'GET',
            dataType: 'json',
            data: { year: selectedYear }, // Kirim tahun ke controller
            success: function(response) {
                var ctx = document.getElementById('chartProject').getContext('2d');
                
                if (window.myChartProject) {
                    window.myChartProject.destroy();
                }

                // --- 2. PLUGIN CUSTOM UNTUK MENAMPILKAN ANGKA DI DONAT ---
                const drawDataLabels = {
                    id: 'drawDataLabels',
                    afterDatasetsDraw(chart, args, pluginOptions) {
                        const { ctx, data } = chart;
                        ctx.save();
                        
                        chart.getDatasetMeta(0).data.forEach((datapoint, index) => {
                            const value = data.datasets[0].data[index];
                            
                            // Hanya tampilkan angka jika nilainya lebih dari 0 (mencegah teks numpuk)
                            if (value > 0) { 
                                // Dapatkan titik tengah dari setiap potongan donat
                                const { x, y } = datapoint.tooltipPosition();
                                
                                // Pengaturan Font dan Style Teks
                                ctx.font = 'bold 12px Arial, sans-serif';
                                ctx.fillStyle = 'white'; // Warna angka
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
                                
                                // Tambahkan efek bayangan hitam agar angka tetap terbaca di warna terang
                                ctx.shadowColor = 'rgba(0, 0, 0, 0.7)';
                                ctx.shadowBlur = 3;
                                
                                // Gambar teks angka di kanvas
                                ctx.fillText(value, x, y);
                            }
                        });
                        
                        ctx.restore();
                    }
                };

                window.myChartProject = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: response.labels,
                        datasets: [{
                            data: response.data,
                            backgroundColor: response.colors,
                            borderWidth: 1
                        }]
                    },
                    plugins: [drawDataLabels], // DAFTARKAN PLUGIN DI SINI
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                            duration: 2000,
                            easing: 'easeOutBounce'
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { 
                                    boxWidth: 12, 
                                    font: { size: 10 } 
                                }
                            }
                        },
                        cutout: '70%' // Mengatur ketebalan donat (semakin kecil, semakin tebal)
                    }
                });
            }
        });
    }

    function refreshSummary() {
        var selectedYear = $('#filter_year').combobox('getValue');

        $.ajax({
            url: '<?= base_url("npd/my_projects/getSummaryStats") ?>',
            type: 'GET',
            dataType: 'json',
            data: { year: selectedYear }, // Kirim tahun ke controller
            success: function(res) {
                $('#count_total').text(res.total);
                $('#count_complete').text(res.complete);
                $('#count_uncomplete').text(res.incomplete);
                $('#count_overdue').text(res.overdue);
            }
        });
    }

    // Fungsi gabungan untuk merefresh semua data
    function reloadAllData() {
        var selectedStatus = $('#filter_status').combobox('getValue');
        var selectedYear   = $('#filter_year').combobox('getValue');

        // 1. Refresh Summary
        refreshSummary();
        
        // 2. Refresh Chart
        loadChartProject();
        
        // 3. Refresh Datagrid dengan membawa parameter filter
        $('#dg').datagrid('load', {
            status: selectedStatus,
            year: selectedYear
        });
    }

    // Trigger otomatis saat tahun atau status diubah
    $('#filter_year, #filter_status').combobox({
        onChange: function(newValue, oldValue) {
            reloadAllData();
        }
    });

    // Panggil saat page load dan berikan event onChange pada filter
    $(document).ready(function() {
        refreshSummary();
        loadChartProject();

        // Trigger otomatis saat tahun diubah
        $('#filter_year').combobox({
            onChange: function(newValue, oldValue) {
                reloadAllData();
            }
        });
    });
</script>
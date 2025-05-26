<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <th field="ck" checkbox="true"></th>
        <th data-options="field:'item_fg_number',width:150,halign:'center'">Product EBWS</th>
        <th data-options="field:'number_customer',width:200,halign:'center'">Product Customer</th>
    </thead>
    <thead>
        <!-- <tr>   
            <th colspan="34" data-options="field:'',width:500,halign:'center',align:'right'">Date Prodplan</th>
        </tr> -->
        <tr>
            <th data-options="field:'item_fg_name',width:200,halign:'center'">Description</th>
            <th data-options="field:'customer_name',width:200,halign:'center'">Customer</th>
            <th data-options="field:'mpsprod',width:80,halign:'center',align:'right'">Prod Plan</th>
            <th data-options="field:'floating',width:80,halign:'center',align:'right',styler:floating">Plotting</th>
            <th data-options="field:'circuit_no',width:80,align:'center'">CCT</th>
            <th data-options="field:'lot',width:80,align:'center'">Lot</th>
            <!-- <th data-options="field:'capacity',width:80,align:'center'">Capacity</th> -->
            <th data-options="field:'customer_name',width:250,halign:'center'">Customer</th>
            <?php 
                if($this->input->get('filter_month')){
                    $filter_month = base64_decode($this->input->get('filter_month'));
                    $filter_year = base64_decode($this->input->get('filter_year'));
                    $filter_revision = base64_decode($this->input->get('filter_revision'));
                    $filter_customer = base64_decode($this->input->get('filter_customer'));
                    $filter_item_fg = base64_decode($this->input->get('filter_item_fg'));
                }else{
                    $filter_month = date("m");
                    $filter_year = date("Y");
                    $filter_revision = "0";
                    $filter_customer = "";
                    $filter_item_fg = "";
                }

                $firstDate = date("Y-m-01", strtotime(date("$filter_year-$filter_month-01")));
                $endDate = date("Y-m-t", strtotime(date("$filter_year-$filter_month")));

                $wp = 0;
                $tgl = 1;
                $alfabet = "z";
                $form_input = "";
                while (strtotime($firstDate) <= strtotime($endDate)) {
                    $working_date = date('Y-m-d', strtotime($firstDate));

                    // $this->db->select('remarks');
                    // $this->db->from('calendars');
                    // $this->db->where('working_date', $working_date);
                    // $holiday = $this->db->get()->row();

                    // if (@$holiday->remarks != null or @$holiday->remarks != "") {
                    //     if($alfabet == "z"){
                    //         $alfabets = "A";
                    //     }elseif($alfabet == "A"){
                    //         $alfabets = "B";
                    //     }elseif($alfabet == "B"){
                    //         $alfabets = "C";
                    //     }elseif($alfabet == "C"){
                    //         $alfabets = "D";
                    //     }elseif($alfabet == "D"){
                    //         $alfabets = "E";
                    //     }elseif($alfabet == "E"){
                    //         $alfabets = "F";
                    //     }elseif($alfabet == "F"){
                    //         $alfabets = "G";
                    //     }elseif($alfabet == "G"){
                    //         $alfabets = "H";
                    //     }elseif($alfabet == "H"){
                    //         $alfabets = "I";
                    //     }elseif($alfabet == "I"){
                    //         $alfabets = "J";
                    //     }elseif($alfabet == "J"){
                    //         $alfabets = "K";
                    //     }elseif($alfabet == "K"){
                    //         $alfabets = "L";
                    //     }elseif($alfabet == "L"){
                    //         $alfabets = "M";
                    //     }elseif($alfabet == "M"){
                    //         $alfabets = "N";
                    //     }elseif($alfabet == "N"){
                    //         $alfabets = "O";
                    //     }else{  
                    //         $alfabets = "";
                    //     }

                    //     $wpp = "WP ".$wp.$alfabets;
                    //     $alfabet = $alfabets;
                    //     $firstDate_check = date("d M", strtotime("+1 day", strtotime($firstDate)));
                    //     $working_date_check = date('Y-m-d', strtotime($firstDate_check));
                    //     $this->db->select('remarks');
                    //     $this->db->from('calendars');
                    //     $this->db->where('working_date', $working_date_check);
                    //     $holiday_check = $this->db->get()->row();

                    //     // if (date('w', strtotime($firstDate_check)) !== '0' && date('w', strtotime($firstDate_check)) !== '6') {
                    //         if (@$holiday_check->remarks == null or @$holiday_check->remarks == "") {
                    //             $wp++;
                    //         }
                    //     // }
                    // }else{
                        if($wp == 0){
                            $wp = 1;
                        }

                        $wpp = "WP ".$wp;
                        $alfabet = "z";
                        $firstDate_check = date("d M", strtotime("+1 day", strtotime($firstDate)));
                        $working_date_check = date('Y-m-d', strtotime($firstDate_check));
                        $this->db->select('remarks');
                        $this->db->from('calendars');
                        $this->db->where('working_date', $working_date_check);
                        $holiday_check = $this->db->get()->row();

                        // if (date('w', strtotime($firstDate_check)) !== '0' && date('w', strtotime($firstDate_check)) !== '6') {
                            // if (@$holiday_check->remarks == null or @$holiday_check->remarks == "") {
                                $wp++;
                            // }
                        // }
                    // }
                  
            ?>
            <th data-options="field:'wds_<?= $tgl ?>',width:60,halign:'center',align:'right',styler:dates,formatter:datef"><?= $wpp ?></th>
            <?php
                    $form_input .= ' <div style="float: left; width: 14%;">
                                        <div class="fitem">
                                            <span style="width:40%; display:inline-block;">'.$wpp.'</span>
                                            <input style="width:50%;" name="date_'.$tgl.'" id="date_'.$tgl.'" class="easyui-textbox" data-options="onChange: function(){ hitung(); }">
                                        </div>
                                    </div>';
                    $tgl++;
                    $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                }

                $last_date = ' <div class="fitem" hidden>
                                    <span style="width:40%; display:inline-block;">Last Date</span>
                                    <input style="width:50%;" id="last_date" class="easyui-textbox" value="'.($tgl-1).'">
                                </div>';
            ?>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 280px; padding: 10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 35%; border:2px solid #d0d0d0; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" name="filter_month" id="filter_month" value="<?= $filter_month ?>" class="easyui-combobox" data-options="prompt:'Month'">
                <input style="width:30%;" name="filter_year" id="filter_year" value="<?= $filter_year ?>" class="easyui-combobox" data-options="prompt:'Year'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <input style="width:60%;" name="filter_revision" id="filter_revision" value="<?= $filter_revision ?>" readonly class="easyui-textbox" data-options="prompt:'Revision'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer Name</span>
                <input style="width:60%;" id="filter_customer" value="<?= $filter_customer ?>" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item_fg" value="<?= $filter_item_fg ?>" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <!-- <a href="javascript:;" class="easyui-linkbutton" id="push_data" onclick="push_data()"><i class="fa fa-database"></i> Push Data</a> -->
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; border-radius:4px;">
            <legend><b>Process Generate Data</b></legend>
            <div style="width: 100%; float: left;">
                <b>WIP TRX MPP</b>
                <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>

                <b>MPP GENERATE</b>
                <div id="p_upload_mpp_generate" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start_mpp_generate">0</b> Of <b id="p_finish_mpp_generate">0</b></center>

                <div style="margin:12px;">
                    <input class="easyui-checkbox" id="check_calendar" value="on" readonly="true"> &nbsp; Working Calendars
                </div>
            </div>
            <div style="width: 50%; float: left;" hidden="">
                <b>PLAN SCHEDULE</b>
                <div id="p_upload_plan" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start_plan">0</b> Of <b id="p_finish_plan">0</b></center>

                <b>PLAN SCHEDULE DETAIL</b>
                <div id="p_upload_plan_detail" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start_plan_detail">0</b> Of <b id="p_finish_plan_detail">0</b></center>
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; border-radius:4px;">
            <legend><b>Result Generate</b></legend>
            <div id="p_remarks" class="easyui-panel" style="width:100%; height:160px; padding:10px; margin-top: 10px; overflow: auto;">
                <ul id="remarks">

                </ul>
            </div>
            <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
        </fieldset>
    </div>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="addProductionSchedules()"><i class="fa fa-plus"></i> Production Schedule</a>
    <?= $button ?>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="data_mps()"><i class="fa fa-database"></i> MPS Data</a>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1300px; padding:10px; top: 10px; left: 10px;">
    <form id="frm_insert" method="post" novalidate>
        <center>
            <button class="easyui-linkbutton" type="button" onclick="savedata()" style="width:100px;">Save</button>
            <button class="easyui-linkbutton" type="button" onclick="nextdata()" style="width:100px;">Next</button>
            <button class="easyui-linkbutton" type="button" onclick="finishdata()" style="width:100px;">Finish</button>
        </center>

        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Index</span>
                    <input style="width:60%;" id="row_index" disabled="" required="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_fg_number" id="item_fg_number" disabled="" required="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Name</span>
                    <input style="width:60%;" name="item_fg_name" id="item_fg_name" disabled="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Prodplan</span>
                    <input style="width:60%;" name="mpsprod" id="mpsprod" disabled="" class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer_name" id="customer_name" disabled="" required="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Circuit</span>
                    <input style="width:60%;" name="circuit_no" id="circuit_no" disabled="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plotting</span>
                    <input style="width:60%;" name="floating" id="floating" disabled="" class="easyui-textbox">
                </div>
                <?= 
                    $last_date
                ?>
            </div>
        </fieldset>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Production Planning Day</b></legend>
            <?= $form_input ?>
        </fieldset>
    </form>
</div>

<div id="dlg_mps" class="easyui-dialog" title="List Data MPS" data-options="closed: true,modal:true" style="width: 780px; height: 500px; top: 10px;">
    <table id="dg_mps" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'item_fg_number',width:150,halign:'center'">Product No</th>
                <th data-options="field:'item_fg_name',width:200,halign:'center'">Product Name</th>
                <th data-options="field:'customer_name',width:250,halign:'center'">Customer</th>
                <th data-options="field:'prod_plan',width:80,halign:'center',align:'right'">Prod Plan</th>
            </tr>
        </thead>
    </table>
</div>

<iframe id="printout" src="" style="width: 100%; height: 450px; border: 0;" hidden=""></iframe>
<script>
    function data_mps(){
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").textbox('getValue');

        $("#dlg_mps").dialog('open');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision);

        if (filter_month != "" && filter_year != "" && filter_revision != "") {
            $('#dg_mps').datagrid({
                url: '<?= base_url('planning/generate_mpp/datatableNotMps') ?>' + url,
                rownumbers: true
            }).datagrid('enableFilter');
        }
    }

    // function addProductionSchedules(){
    //     var rows = $('#dg').datagrid('getSelections');
    //     if (rows.length > 0) {
    //         Swal.fire({
    //             title: 'Create Data',
    //             text: "Are you sure? You want to create production scheudle!",
    //             icon: 'warning',
    //             showCancelButton: true,
    //             allowOutsideClick: false,
    //             allowEscapeKey: false,
    //             confirmButtonText: 'Yes!'
    //         }).then((result) => {
    //             if (result.isConfirmed) {
    //                 Swal.fire({
    //                     title: 'Please Wait...',
    //                     showConfirmButton: false,
    //                     allowOutsideClick: false,
    //                     allowEscapeKey: false,
    //                     didOpen: () => {
    //                         Swal.showLoading();
    //                     },
    //                 });

    //                 requestCreate(rows.length, rows);
    //                 function requestCreate(total, json, number = 1, value = 0) {
    //                     if (value < 100) {
    //                         var row = json[number-1];
    //                         value = Math.floor((number / total) * 100);

    //                         $.ajax({
    //                             method: 'post',
    //                             url: '<?= base_url('planning/generate_mpp/createProductionSchedules') ?>',
    //                             data: {
    //                                 data: row,
    //                             },
    //                             success: function(result) {
    //                                 var result = eval('(' + result + ')');
    //                                 requestCreate(total, json, number + 1, value);

    //                                 if (number == total) {
    //                                     // $('#dg').datagrid('reload');
    //                                     Swal.close();
    //                                     Swal.fire(
    //                                         'Create Completed',
    //                                         'Create Data has been completed, Please Check in Production Schedule Module',
    //                                         'success'
    //                                     );
    //                                 }
    //                             },
    //                             error: function(jqXHR, textStatus, errorThrown) {
    //                                 toastr.error("Data Already in Production Schedule, You can delete first in Production Schedule");
    //                                 Swal.close();
    //                             },
    //                         });
    //                     }
    //                 }
    //             }
    //         });
    //     } else {
    //         toastr.info("Please select one of the data in the table first");
    //     }
    // }

    //Edit Data
    function update(index = 0) {
        var rows = $('#dg').datagrid('getSelections');
        if (rows) {
            var row = rows[index];
            if(row){
                $("#row_index").textbox('setValue', index);
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                url_update = '<?= base_url('planning/generate_mpp/update'); ?>?id=' + btoa(row.id);

                if(row.wds_1 < 0){ $("#date_1").textbox('disable'); }
                if(row.wds_2 < 0){ $("#date_2").textbox('disable'); }
                if(row.wds_3 < 0){ $("#date_3").textbox('disable'); }
                if(row.wds_4 < 0){ $("#date_4").textbox('disable'); }
                if(row.wds_5 < 0){ $("#date_5").textbox('disable'); }
                if(row.wds_6 < 0){ $("#date_6").textbox('disable'); }
                if(row.wds_7 < 0){ $("#date_7").textbox('disable'); }
                if(row.wds_8 < 0){ $("#date_8").textbox('disable'); }
                if(row.wds_9 < 0){ $("#date_9").textbox('disable'); }
                if(row.wds_10 < 0){ $("#date_10").textbox('disable'); }
                if(row.wds_11 < 0){ $("#date_11").textbox('disable'); }
                if(row.wds_12 < 0){ $("#date_12").textbox('disable'); }
                if(row.wds_13 < 0){ $("#date_13").textbox('disable'); }
                if(row.wds_14 < 0){ $("#date_14").textbox('disable'); }
                if(row.wds_15 < 0){ $("#date_15").textbox('disable'); }
                if(row.wds_16 < 0){ $("#date_16").textbox('disable'); }
                if(row.wds_17 < 0){ $("#date_17").textbox('disable'); }
                if(row.wds_18 < 0){ $("#date_18").textbox('disable'); }
                if(row.wds_19 < 0){ $("#date_19").textbox('disable'); }
                if(row.wds_20 < 0){ $("#date_20").textbox('disable'); }
                if(row.wds_21 < 0){ $("#date_21").textbox('disable'); }
                if(row.wds_22 < 0){ $("#date_22").textbox('disable'); }
                if(row.wds_23 < 0){ $("#date_23").textbox('disable'); }
                if(row.wds_24 < 0){ $("#date_24").textbox('disable'); }
                if(row.wds_25 < 0){ $("#date_25").textbox('disable'); }
                if(row.wds_26 < 0){ $("#date_26").textbox('disable'); }
                if(row.wds_27 < 0){ $("#date_27").textbox('disable'); }
                if(row.wds_28 < 0){ $("#date_28").textbox('disable'); }
                if(row.wds_29 < 0){ $("#date_29").textbox('disable'); }
                if(row.wds_30 < 0){ $("#date_30").textbox('disable'); }
                if(row.wds_31 < 0){ $("#date_31").textbox('disable'); }
            }else{
                toastr.warning("Next Product No cannot Found");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Add Data
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").textbox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var check_calendar  = $('#check_calendar').checkbox('options');

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please select filter month, year and revision", "Information");
        }else{
            if (check_calendar.checked == true){
                $.messager.prompt('Generate MPP', 'Please input Password Generate', function(r){
                    if (r == "GENERATEMPP"){
                        Swal.fire({
                            title: 'Please Wait for Generating Data',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            },
                        });

                        $.ajax({
                            type: "get",
                            url: "<?= base_url('planning/generate_mpp/getdata') ?>",
                            data: "filter_month=" + window.btoa(filter_month) +
                                "&filter_year=" + window.btoa(filter_year) +
                                "&filter_revision=" + window.btoa(filter_revision) +
                                "&filter_customer=" + window.btoa(filter_customer) +
                                "&filter_item_fg=" + window.btoa(filter_item_fg),
                            dataType: "json",
                            success: function(rows) {
                                Swal.close();

                                if(rows.length > 0){
                                    requestData(rows.length, rows);
                                }else{
                                    Swal.fire('Not Found!', 'Data MPS not found!', 'error');
                                }

                                function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                    if (value < 100) {
                                        value = Math.floor((number / total) * 100);
                                        $('#p_upload').progressbar('setValue', value);
                                        $('#p_start').html(number);
                                        $('#p_finish').html(total);

                                        $.post('<?= base_url('planning/generate_mpp/create') ?>', {
                                            data: json[number - 1]
                                        }, function(note) {
                                            var result = eval('(' + note + ')');
                                            if (result.theme == "success") {
                                                Swal.close();
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                                //Json Failed
                                                $.ajax({
                                                    type: "POST",
                                                    async: true,
                                                    url: "<?= base_url('planning/generate_mpp/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });

                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            if (value == 100) {
                                                Swal.fire('Good job!', 'Process Save Data Completed!', 'success');
                                            }

                                            $("#p_remarks").append(title + "<br>");
                                        }).fail(function(jqXHR, textStatus) {
                                            if (textStatus == "error") {
                                                Swal.fire({
                                                    title: 'Connection Time Out, Check Your Connection',
                                                    showConfirmButton: false,
                                                    allowOutsideClick: false,
                                                    allowEscapeKey: false,
                                                    didOpen: () => {
                                                        Swal.showLoading();
                                                    },
                                                });

                                                requestData(total, json, number, value, success + 0, failed + 0);
                                            }
                                        });
                                    }
                                }
                            }
                        });
                    }
                });
            }else{
                toastr.warning("Working Calendar Check Not Complete ", "Information");
            }
        }
    }

    function downloadFailed() {
        window.open('<?= base_url('planning/generate_mpp/uploadDownloadFailed') ?>', '_blank');
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").textbox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            window.location.assign(url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").textbox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        if (filter_month == "" || filter_year == "" || filter_revision == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/generate_mpp/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    function hitung(){
        var last_date = $("#last_date").textbox('getValue');

        var date_1 = $("#date_1").textbox('getValue');
        var date_2 = $("#date_2").textbox('getValue');
        var date_3 = $("#date_3").textbox('getValue');
        var date_4 = $("#date_4").textbox('getValue');
        var date_5 = $("#date_5").textbox('getValue');
        var date_6 = $("#date_6").textbox('getValue');
        var date_7 = $("#date_7").textbox('getValue');
        var date_8 = $("#date_8").textbox('getValue');
        var date_9 = $("#date_9").textbox('getValue');
        var date_10 = $("#date_10").textbox('getValue');
        var date_11 = $("#date_11").textbox('getValue');
        var date_12 = $("#date_12").textbox('getValue');
        var date_13 = $("#date_13").textbox('getValue');
        var date_14 = $("#date_14").textbox('getValue');
        var date_15 = $("#date_15").textbox('getValue');
        var date_16 = $("#date_16").textbox('getValue');
        var date_17 = $("#date_17").textbox('getValue');
        var date_18 = $("#date_18").textbox('getValue');
        var date_19 = $("#date_19").textbox('getValue');
        var date_20 = $("#date_20").textbox('getValue');
        var date_21 = $("#date_21").textbox('getValue');
        var date_22 = $("#date_22").textbox('getValue');
        var date_23 = $("#date_23").textbox('getValue');
        var date_24 = $("#date_24").textbox('getValue');
        var date_25 = $("#date_25").textbox('getValue');
        var date_26 = $("#date_26").textbox('getValue');
        var date_27 = $("#date_27").textbox('getValue');
        var date_28 = $("#date_28").textbox('getValue');
        
        if(last_date == "28"){
            var date_28 = $("#date_28").textbox('getValue');
            var date_29 = 0;
            var date_30 = 0;
            var date_31 = 0;
        }else if(last_date == "29"){
            var date_28 = $("#date_28").textbox('getValue');
            var date_29 = $("#date_29").textbox('getValue');
            var date_30 = 0;
            var date_31 = 0;
        }else if(last_date == "30"){
            var date_28 = $("#date_28").textbox('getValue');
            var date_29 = $("#date_29").textbox('getValue');
            var date_30 = $("#date_30").textbox('getValue');
            var date_31 = 0;
        }else{
            var date_28 = $("#date_28").textbox('getValue');
            var date_29 = $("#date_29").textbox('getValue');
            var date_30 = $("#date_30").textbox('getValue');
            var date_31 = $("#date_31").textbox('getValue');
        }

        if($.isNumeric(date_1)){ var date_1 = date_1; }else{ var date_1 = 0; }
        if($.isNumeric(date_2)){ var date_2 = date_2; }else{ var date_2 = 0; }
        if($.isNumeric(date_3)){ var date_3 = date_3; }else{ var date_3 = 0; }
        if($.isNumeric(date_4)){ var date_4 = date_4; }else{ var date_4 = 0; }
        if($.isNumeric(date_5)){ var date_5 = date_5; }else{ var date_5 = 0; }
        if($.isNumeric(date_6)){ var date_6 = date_6; }else{ var date_6 = 0; }
        if($.isNumeric(date_7)){ var date_7 = date_7; }else{ var date_7 = 0; }
        if($.isNumeric(date_8)){ var date_8 = date_8; }else{ var date_8 = 0; }
        if($.isNumeric(date_9)){ var date_9 = date_9; }else{ var date_9 = 0; }
        if($.isNumeric(date_10)){ var date_10 = date_10; }else{ var date_10 = 0; }
        if($.isNumeric(date_11)){ var date_11 = date_11; }else{ var date_11 = 0; }
        if($.isNumeric(date_12)){ var date_12 = date_12; }else{ var date_12 = 0; }
        if($.isNumeric(date_13)){ var date_13 = date_13; }else{ var date_13 = 0; }
        if($.isNumeric(date_14)){ var date_14 = date_14; }else{ var date_14 = 0; }
        if($.isNumeric(date_15)){ var date_15 = date_15; }else{ var date_15 = 0; }
        if($.isNumeric(date_16)){ var date_16 = date_16; }else{ var date_16 = 0; }
        if($.isNumeric(date_17)){ var date_17 = date_17; }else{ var date_17 = 0; }
        if($.isNumeric(date_18)){ var date_18 = date_18; }else{ var date_18 = 0; }
        if($.isNumeric(date_19)){ var date_19 = date_19; }else{ var date_19 = 0; }
        if($.isNumeric(date_20)){ var date_20 = date_20; }else{ var date_20 = 0; }
        if($.isNumeric(date_21)){ var date_21 = date_21; }else{ var date_21 = 0; }
        if($.isNumeric(date_22)){ var date_22 = date_22; }else{ var date_22 = 0; }
        if($.isNumeric(date_23)){ var date_23 = date_23; }else{ var date_23 = 0; }
        if($.isNumeric(date_24)){ var date_24 = date_24; }else{ var date_24 = 0; }
        if($.isNumeric(date_25)){ var date_25 = date_25; }else{ var date_25 = 0; }
        if($.isNumeric(date_26)){ var date_26 = date_26; }else{ var date_26 = 0; }
        if($.isNumeric(date_27)){ var date_27 = date_27; }else{ var date_27 = 0; }
        if($.isNumeric(date_28)){ var date_28 = date_28; }else{ var date_28 = 0; }
        if($.isNumeric(date_29)){ var date_29 = date_29; }else{ var date_29 = 0; }
        if($.isNumeric(date_30)){ var date_30 = date_30; }else{ var date_30 = 0; }
        if($.isNumeric(date_31)){ var date_31 = date_31; }else{ var date_31 = 0; }

        var total = (parseInt(date_1) + parseInt(date_2) + parseInt(date_3) + parseInt(date_4) + parseInt(date_5) + parseInt(date_6) + parseInt(date_7) + parseInt(date_8)
            + parseInt(date_9) + parseInt(date_10) + parseInt(date_11) + parseInt(date_12) + parseInt(date_13) + parseInt(date_14) + parseInt(date_15) + parseInt(date_16)
            + parseInt(date_17) + parseInt(date_18) + parseInt(date_19) + parseInt(date_20) + parseInt(date_21) + parseInt(date_22) + parseInt(date_23) + parseInt(date_24) + parseInt(date_25) + parseInt(date_26) + parseInt(date_27) + parseInt(date_28) + parseInt(date_29) + parseInt(date_30));

        $("#floating").textbox('setValue', total);

        // var mpsprod = $("#mpsprod").textbox('getValue');

        // if(parseInt(mpsprod) < parseInt(total)){
        //     toastr.error("Plotting > Prodplan");
        //     return false;
        // }
    }

    function nextdata(){
        var index = $("#row_index").textbox('getValue');
        update(parseInt(index) + 1);
    }

    function savedata(){
        var mpsprod = $("#mpsprod").textbox('getValue');
        var floating = $("#floating").textbox('getValue');

        if(parseInt(mpsprod) < parseInt(floating)){
            toastr.error("Plotting > Prodplan");
        }else{
            $('#frm_insert').form('submit', {
                url: url_update,
                onSubmit: function() {
                    return $(this).form('validate');
                },
                success: function(result) {
                    var result = eval('(' + result + ')');

                    if (result.theme == "success") {
                        toastr.success(result.message, result.title);
                    } else {
                        toastr.error(result.message, result.title);
                    }

                    //$('#dlg_insert').dialog('close');
                    // $('#dg').datagrid('reload');
                }
            });
        }
    }

    function finishdata(){
        $('#dlg_insert').dialog('close');
        $('#dg').datagrid('reload');
    }

    $(function() {
        $("#add").html('Generate');

        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_revision = $("#filter_revision").textbox('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_customer=" + window.btoa(filter_customer) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        if (filter_month != "" && filter_year != "") {
            $('#dg').datagrid({
                url: '<?= base_url('planning/generate_mpp/datatables') ?>' + url,
                pagination: true,
                rownumbers: true,
                singleSelect: false,
                fit: true,
                pageList: [10,50,100,1000]
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/generate_mpp/print') ?>' + url);
        }

        $('#filter_month').combobox({
            url: '<?php echo base_url('planning/generate_mps/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Month',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row){
                var filter_year = $('#filter_year').combobox('getValue');

                $.ajax({
                    type: "get",
                    url: "<?= base_url('planning/generate_mpp/check_calendar') ?>",
                    data: "filter_month=" + window.btoa(row.id) +
                        "&filter_year=" + window.btoa(filter_year),
                    dataType: "json",
                    success: function(calendar) {
                        if (calendar.theme == "success") {
                            $('#check_calendar').checkbox({
                                checked: true
                            });
                        } else {
                            $('#check_calendar').checkbox({
                                checked: false
                            });
                        }
                    }
                });

                $.ajax({
                    type: "get",
                    url: '<?php echo base_url('planning/generate_mpp/readRevisions/'); ?>' + row.id + '/' + filter_year,
                    dataType: "json",
                    success: function(rev) {
                        $('#filter_revision').textbox('setValue', rev.revision);
                    }
                });
            }
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('planning/generate_mps/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Year',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row){
                var filter_month = $('#filter_month').combobox('getValue');

                $.ajax({
                    type: "get",
                    url: "<?= base_url('planning/generate_mpp/check_calendar') ?>",
                    data: "filter_month=" + window.btoa(filter_month) +
                        "&filter_year=" + window.btoa(row.id),
                    dataType: "json",
                    success: function(calendar) {
                        if (calendar.theme == "success") {
                            $('#check_calendar').checkbox({
                                checked: true
                            });
                        } else {
                            $('#check_calendar').checkbox({
                                checked: false
                            });
                        }
                    }
                });

                $.ajax({
                    type: "get",
                    url: '<?php echo base_url('planning/generate_mpp/readRevisions/'); ?>' + filter_month + '/' + row.id,
                    dataType: "json",
                    success: function(rev) {
                        $('#filter_revision').textbox('setValue', rev.revision);
                    }
                });
            }
        });

        $('#filter_customer').combobox({
            url: '<?php echo base_url('master/customers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Customer',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(customer) {
                $('#filter_item_fg').combogrid({
                    url: '<?= base_url('master/customer_items/reads/') ?>' + btoa(customer.id),
                    panelWidth: 600,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Product No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'number',
                            title: 'Product EBWS',
                            width: 150
                        },{
                            field: 'number_customer',
                            title: 'Product Customer',
                            width: 200
                        },{
                            field: 'name',
                            title: 'Description',
                            width: 200
                        }]
                    ]
                });
            }
        });

        $('#filter_item_fg').combogrid({
            url: '<?= base_url('master/item_fg/reads') ?>',
            panelWidth: 600,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Product EBWS',
                    width: 150
                },{
                    field: 'number_customer',
                    title: 'Product Customer',
                    width: 200
                },{
                    field: 'name',
                    title: 'Description',
                    width: 200
                }]
            ]
        });
    });

    //Format Datepicker
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

    //Format Datepicker
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

    function dates(value, row, index) {
        if (value == "W") {
            return 'background: #FFB73F; color:white;';
        }else if(value < 0){
            return 'background: #FF1D1D; color:white;';
        }
    }

    function datef(value, row, index) {
        if (value == "W") {
            return "OFF";
        }else if(value < 0){
            return Math.abs(value);
        }else{
            return value;
        }
    }

    function floating(value, row, index) {
        if (row.mpsprod < value) {
            return 'background: #FFA5A5; color:white;';
        }else if(row.mpsprod > value){
            return 'background: #FF9F00; color:white;';
        }else{
            return 'background: #22CC00; color:white;';
        }
    }
</script>
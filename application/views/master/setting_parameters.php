<!-- TABLE DATAGRID -->
<table id="dg"class="easyui-datagrid"style="width:99.5%;"toolbar="#toolbar"rownumbers="true"singleSelect="true"fitColumns="false">
    <thead>
        <tr>
            <th rowspan="3" field="ck" checkbox="true"></th>
            <th rowspan="3"data-options="field:'print',width:80,align:'center', formatter:btnPrint">Print</th>
            <th rowspan="3"data-options="field:'item_fg_id',width:150,halign:'center'">Product Id</th>
            <th rowspan="3"data-options="field:'item_fg_number',width:120,halign:'center'">Product No</th>
            <th rowspan="3"data-options="field:'item_fg_name',width:250,halign:'center'">Product Name</th>
            <th rowspan="3"data-options="field:'machine_number',width:80,halign:'center'">Machine No</th>
            <th rowspan="3"data-options="field:'toonage',width:80,halign:'center'">Tonage</th>
            <th rowspan="3"data-options="field:'maker',width:100,halign:'center'">Maker</th>
            <th colspan="6"data-options="halign:'center'">BARREL TEMPERATURE</th>
            <th colspan="15"data-options="halign:'center'">INJECTION</th>
            <th colspan="9"data-options="halign:'center'">HOLDING</th>
            <th colspan="17"data-options="halign:'center'">CHARGING</th>
            <th colspan="4"data-options="halign:'center'">SUCK BACK</th>
            <th rowspan="3"data-options="field:'injection_time',width:100,halign:'center'">Injection Time</th>
            <th rowspan="3"data-options="field:'delay_sb1',width:100,halign:'center'">Delay for SB 1</th>
            <th rowspan="3"data-options="field:'delay_sb2',width:100,halign:'center'">Delay for SB 2</th>
            <th rowspan="3"data-options="field:'delay_charge',width:100,halign:'center'">Delay of Charge</th>
            <th rowspan="3"data-options="field:'inj_monitoring_time',width:100,halign:'center'">Injection <br>Monitoring Time</th>
            <th rowspan="3"data-options="field:'charge_monitoring_time',width:100,halign:'center'">Charge <br>Monitoring Time</th>
            <th rowspan="3"data-options="field:'cooling_time',width:100,halign:'center'">Cooling Time</th>
            <th rowspan="3"data-options="field:'min_cushion_check',width:100,halign:'center'">Minimum Cushion<br> Check</th>
            <th rowspan="3"data-options="field:'min_cushion_low_limit',width:100,halign:'center'">Minimum Cushion<br> Low Limit</th>
            <th rowspan="3"data-options="field:'min_cushion_upper_limit',width:100,halign:'center'">Minimum Cushion<br> Upper Limit</th>
            <th rowspan="3"data-options="field:'charge_after_cooling',width:100,halign:'center'">Charge <br>After Cooling</th>
            <th rowspan="3"data-options="field:'use_manual_back_pressure',width:100,halign:'center'">Use of manual <br>back pressure</th>
            <th rowspan="3"data-options="field:'actual_cushion',width:100,halign:'center'">Actual Cushion</th>
            <th rowspan="3"data-options="field:'switch_over_position',width:100,halign:'center'">Switch Over <br>Position</th>
            <th rowspan="3"data-options="field:'switch_over_time',width:100,halign:'center'">Switch Over <br>Time</th>
            <th colspan="15" data-options="halign:'center'">MOLD CLOSING</th>
            <th colspan="15" data-options="halign:'center'">MOLD OPENING</th>
            <th rowspan="3"data-options="field:'delay_mold_closing',width:100,halign:'center'">Delay for <br>Mold Closing</th>
            <th rowspan="3"data-options="field:'mq_delay_time',width:100,halign:'center'">MQ Delay Time</th>
            <th rowspan="3"data-options="field:'mold_closing_time',width:100,halign:'center'">Mold Closing <br>Time</th>
            <th rowspan="3"data-options="field:'mold_opening_time',width:100,halign:'center'">Mold Opening <br>Time</th>
            <th rowspan="3"data-options="field:'process_time',width:100,halign:'center'">Process Time</th>
            <th rowspan="3"data-options="field:'mold_protection_time_1',width:100,halign:'center'">Mold Protection <br>Time 1</th>
            <th rowspan="3"data-options="field:'mold_protection_time_2',width:100,halign:'center'">Mold Protection <br>Time 2</th>
            <th colspan="12" data-options="halign:'center'">EJECTION FORWARD</th>
            <th colspan="12" data-options="halign:'center'">EJECTION BACKWARD</th>
            <th colspan="10" data-options="halign:'center'">EJECTION</th>
            <th rowspan="3"data-options="field:'cycle_time_actual',width:80,halign:'center'">Cycle Time</th>
            <th colspan="6" data-options="halign:'center'">Mold Cooling</th>
            <th colspan="14" data-options="halign:'center'">HOT RUNNER TEMPERATURE</th>
            <th rowspan="3"data-options="field:'dryer_temperature',width:100,halign:'center'">Dryer Temperature</th>
            <th rowspan="3"data-options="field:'dryer_time',width:100,halign:'center'">Dryer Time</th>
            <th rowspan="3"data-options="field:'weight',width:100,halign:'center'">Part Weight</th>
            <th rowspan="3"data-options="field:'runner',width:100,halign:'center'">Runner Weight</th>
            <th colspan="2"data-options="halign:'center'">MATERIAL</th>
            <th rowspan="3" data-options="field:'approved_to',width:100,halign:'center',formatter:formatApproved,styler:styleApproved">Status <br>Approve</th>
            <th rowspan="3" data-options="field:'approved_by',width:100,halign:'center'">Approve By</th>
            <th rowspan="3" data-options="field:'approved_date',width:150,halign:'center'">Approve Date</th>
            <th colspan="2"data-options="halign:'center'">Created</th>
            <th colspan="2"data-options="halign:'center'">Updated</th>
        </tr>

        <tr>
            <th rowspan="2"data-options="field:'nozzle',width:80">Nozle</th>
            <th rowspan="2"data-options="field:'front',width:80">Front</th>
            <th rowspan="2"data-options="field:'middle_3',width:80">Middle 3</th>
            <th rowspan="2"data-options="field:'middle_2',width:80">Middle 2</th>
            <th rowspan="2"data-options="field:'middle_1',width:80">Middle 1</th>
            <th rowspan="2"data-options="field:'rear',width:80">Rear</th>

            <th colspan="5"data-options="halign:'center'">INJECTION POSITION</th>
            <th colspan="5"data-options="halign:'center'">INJECTION SPEED</th>
            <th colspan="5"data-options="halign:'center'">INJECTION PRESSURE</th>

            <th colspan="3"data-options="halign:'center'">HOLDING PRESSURE</th>
            <th colspan="3"data-options="halign:'center'">HOLDING SPEED</th>
            <th colspan="3"data-options="halign:'center'">HOLDING TIME</th>

            <th colspan="4"data-options="halign:'center'">SPEED</th>
            <th colspan="4"data-options="halign:'center'">PRESSURE</th>
            <th colspan="4"data-options="halign:'center'">BACK PRESSURE</th>
            <th colspan="5"data-options="halign:'center'">POSITION</th>

            <th rowspan="2"data-options="field:'suck_back_1_pressure',width:100">Suck Back 1 <br>Pressure</th>
            <th rowspan="2"data-options="field:'suck_back_2_pressure',width:100">Suck Back 2 <br>Pressure</th>
            <th rowspan="2"data-options="field:'suck_back_1',width:80">Suck Back 1</th>
            <th rowspan="2"data-options="field:'suck_back_2',width:80">Suck Back 2</th>

            <th colspan="5" data-options="halign:'center'">MOLD CLOSING SPEED</th>
            <th colspan="5" data-options="halign:'center'">MOLD CLOSING PRESSURE</th>
            <th colspan="5" data-options="halign:'center'">MOLD CLOSING POSITION</th>
            <th colspan="5" data-options="halign:'center'">MOLD OPENING SPEED</th>
            <th colspan="5" data-options="halign:'center'">MOLD OPENING PRESSURE</th>
            <th colspan="5" data-options="halign:'center'">MOLD OPENING POSITION</th>

            <th colspan="4" data-options="halign:'center'">EJECTION SPEED</th>
            <th colspan="4" data-options="halign:'center'">EJECTION PRESSURE</th>
            <th colspan="4" data-options="halign:'center'">EJECTION POSITION</th>
            <th colspan="4" data-options="halign:'center'">EJECTION SPEED</th>
            <th colspan="4" data-options="halign:'center'">EJECTION PRESSURE</th>
            <th colspan="4" data-options="halign:'center'">EJECTION POSITION</th>

            <th rowspan="2"data-options="field:'ejecting_time',width:80">Ejecting <br>Time</th>
            <th rowspan="2"data-options="field:'delay_forward',width:80">Delay <br>Forward</th>
            <th rowspan="2"data-options="field:'delay_backward',width:80">Delay <br>Backward</th>
            <th rowspan="2"data-options="field:'forward_time',width:80">Forward <br>Time</th>
            <th rowspan="2"data-options="field:'backward_time',width:80">Backward <br>Time</th>
            <th rowspan="2"data-options="field:'every_time_delays',width:80">Every TIme <br>Delays</th>
            <th rowspan="2"data-options="field:'ejector_forward_maintain',width:80">Ejector Forward <br>Maintain</th>
            <th rowspan="2"data-options="field:'semi_automatic_ej_switch',width:80">Semi Auto <br>EJ f/b Switch</th>
            <th rowspan="2"data-options="field:'ejector_bw_com_signal',width:80">Ejector BW <br>COM Signal</th>
            <th rowspan="2"data-options="field:'semi_automatic_safety_door_start',width:90">Semi-Automatic <br>Safety Door</th>

            <th colspan="2" data-options="halign:'center'">Core</th>
            <th colspan="2" data-options="halign:'center'">Cavity</th>
            <th colspan="2" data-options="halign:'center'">Slider</th>

            <th rowspan="2"data-options="field:'hr_zone_1',width:80">Zone 1</th>
            <th rowspan="2"data-options="field:'hr_zone_2',width:80">Zone 2</th>
            <th rowspan="2"data-options="field:'hr_zone_3',width:80">Zone 3</th>
            <th rowspan="2"data-options="field:'hr_zone_4',width:80">Zone 4</th>
            <th rowspan="2"data-options="field:'hr_zone_5',width:80">Zone 5</th>
            <th rowspan="2"data-options="field:'hr_zone_6',width:80">Zone 6</th>
            <th rowspan="2"data-options="field:'hr_zone_7',width:80">Zone 7</th>
            <th rowspan="2"data-options="field:'hr_zone_8',width:80">Zone 8</th>
            <th rowspan="2"data-options="field:'hr_zone_9',width:80">Zone 9</th>
            <th rowspan="2"data-options="field:'hr_zone_10',width:80">Zone 10</th>
            <th rowspan="2"data-options="field:'hr_zone_11',width:80">Zone 11</th>
            <th rowspan="2"data-options="field:'hr_zone_12',width:80">Zone 12</th>
            <th rowspan="2"data-options="field:'hr_zone_13',width:80">Zone 13</th>
            <th rowspan="2"data-options="field:'hr_zone_14',width:80">Zone 14</th>

            <th rowspan="2"data-options="field:'item_rm_number',width:200">Part No</th>
            <th rowspan="2"data-options="field:'item_rm_name',width:200">Part Name</th>

            <th rowspan="2"data-options="field:'created_by',width:100">By</th>
            <th rowspan="2"data-options="field:'created_date',width:120">Date</th>

            <th rowspan="2"data-options="field:'updated_by',width:100">By</th>
            <th rowspan="2"data-options="field:'updated_date',width:120">Date</th>
        </tr>

        <tr>
            <th data-options="field:'s1_injection',width:50">S1</th>
            <th data-options="field:'s2_injection',width:50">S2</th>
            <th data-options="field:'s3_injection',width:50">S3</th>
            <th data-options="field:'s4_injection',width:50">S4</th>
            <th data-options="field:'s5_injection',width:50">S5</th>
            <th data-options="field:'v1_injection',width:50">V1</th>
            <th data-options="field:'v2_injection',width:50">V2</th>
            <th data-options="field:'v3_injection',width:50">V3</th>
            <th data-options="field:'v4_injection',width:50">V4</th>
            <th data-options="field:'v5_injection',width:50">V5</th>
            <th data-options="field:'p1_injection',width:50">P1</th>
            <th data-options="field:'p2_injection',width:50">P2</th>
            <th data-options="field:'p3_injection',width:50">P3</th>
            <th data-options="field:'p4_injection',width:50">P4</th>
            <th data-options="field:'p5_injection',width:50">P5</th>

            <th data-options="field:'p1_holding',width:50">P1</th>
            <th data-options="field:'p2_holding',width:50">P2</th>
            <th data-options="field:'p3_holding',width:50">P3</th>
            <th data-options="field:'v1_holding',width:50">V1</th>
            <th data-options="field:'v2_holding',width:50">V2</th>
            <th data-options="field:'v3_holding',width:50">V3</th>
            <th data-options="field:'t1_holding',width:50">T1</th>
            <th data-options="field:'t2_holding',width:50">T2</th>
            <th data-options="field:'t3_holding',width:50">T3</th>

            <th data-options="field:'1_charging_speed',width:50">1</th>
            <th data-options="field:'2_charging_speed',width:50">2</th>
            <th data-options="field:'3_charging_speed',width:50">3</th>
            <th data-options="field:'4_charging_speed',width:50">4</th>
            <th data-options="field:'1_charging_pressure',width:50">1</th>
            <th data-options="field:'2_charging_pressure',width:50">2</th>
            <th data-options="field:'3_charging_pressure',width:50">3</th>
            <th data-options="field:'4_charging_pressure',width:50">4</th>
            <th data-options="field:'1_charging_back_pressure',width:50">1</th>
            <th data-options="field:'2_charging_back_pressure',width:50">2</th>
            <th data-options="field:'3_charging_back_pressure',width:50">3</th>
            <th data-options="field:'4_charging_back_pressure',width:50">4</th>
            <th data-options="field:'1_charging_position',width:50">1</th>
            <th data-options="field:'2_charging_position',width:50">2</th>
            <th data-options="field:'3_charging_position',width:50">3</th>
            <th data-options="field:'4_charging_position',width:50">4</th>
            <th data-options="field:'end_charging_position',width:50">End</th>

            <th data-options="field:'v1_mold_closing',width:45">V1</th>
            <th data-options="field:'v2_mold_closing',width:45">V2</th>
            <th data-options="field:'v3_mold_closing',width:45">V3</th>
            <th data-options="field:'v4_mold_closing',width:45">V4</th>
            <th data-options="field:'v5_mold_closing',width:45">V5</th>
            <th data-options="field:'p1_mold_closing',width:45">P1</th>
            <th data-options="field:'p2_mold_closing',width:45">P2</th>
            <th data-options="field:'p3_mold_closing',width:45">P3</th>
            <th data-options="field:'p4_mold_closing',width:45">P4</th>
            <th data-options="field:'p5_mold_closing',width:45">P5</th>
            <th data-options="field:'s1_mold_closing',width:45">S1</th>
            <th data-options="field:'s2_mold_closing',width:45">S2</th>
            <th data-options="field:'s3_mold_closing',width:45">S3</th>
            <th data-options="field:'s4_mold_closing',width:45">S4</th>
            <th data-options="field:'s5_mold_closing',width:45">S5</th>

            <th data-options="field:'v1_mold_opening',width:45">V1</th>
            <th data-options="field:'v2_mold_opening',width:45">V2</th>
            <th data-options="field:'v3_mold_opening',width:45">V3</th>
            <th data-options="field:'v4_mold_opening',width:45">V4</th>
            <th data-options="field:'v5_mold_opening',width:45">V5</th>
            <th data-options="field:'p1_mold_opening',width:45">P1</th>
            <th data-options="field:'p2_mold_opening',width:45">P2</th>
            <th data-options="field:'p3_mold_opening',width:45">P3</th>
            <th data-options="field:'p4_mold_opening',width:45">P4</th>
            <th data-options="field:'p5_mold_opening',width:45">P5</th>
            <th data-options="field:'s1_mold_opening',width:45">S1</th>
            <th data-options="field:'s2_mold_opening',width:45">S2</th>
            <th data-options="field:'s3_mold_opening',width:45">S3</th>
            <th data-options="field:'s4_mold_opening',width:45">S4</th>
            <th data-options="field:'s5_mold_opening',width:45">S5</th>

            <th data-options="field:'v1_ej_forward',width:45">V1</th>
            <th data-options="field:'v2_ej_forward',width:45">V2</th>
            <th data-options="field:'v3_ej_forward',width:45">V3</th>
            <th data-options="field:'v4_ej_forward',width:45">V4</th>
            <th data-options="field:'p1_ej_forward',width:45">P1</th>
            <th data-options="field:'p2_ej_forward',width:45">P2</th>
            <th data-options="field:'p3_ej_forward',width:45">P3</th>
            <th data-options="field:'p4_ej_forward',width:45">P4</th>
            <th data-options="field:'s1_ej_forward',width:45">S1</th>
            <th data-options="field:'s2_ej_forward',width:45">S2</th>
            <th data-options="field:'s3_ej_forward',width:45">S3</th>
            <th data-options="field:'s4_ej_forward',width:45">S4</th>

            <th data-options="field:'v1_ej_backward',width:45">V1</th>
            <th data-options="field:'v2_ej_backward',width:45">V2</th>
            <th data-options="field:'v3_ej_backward',width:45">V3</th>
            <th data-options="field:'v4_ej_backward',width:45">V4</th>
            <th data-options="field:'p1_ej_backward',width:45">P1</th>
            <th data-options="field:'p2_ej_backward',width:45">P2</th>
            <th data-options="field:'p3_ej_backward',width:45">P3</th>
            <th data-options="field:'p4_ej_backward',width:45">P4</th>
            <th data-options="field:'s1_ej_backward',width:45">S1</th>
            <th data-options="field:'s2_ej_backward',width:45">S2</th>
            <th data-options="field:'s3_ej_backward',width:45">S3</th>
            <th data-options="field:'s4_ej_backward',width:45">S4</th>

            <th data-options="field:'core_temp',width:45">Temp</th>
            <th data-options="field:'core_use',width:45">Use</th>
            <th data-options="field:'slider_temp',width:45">Temp</th>
            <th data-options="field:'slider_use',width:45">Use</th>
            <th data-options="field:'cavity_temp',width:45">Temp</th>
            <th data-options="field:'cavity_use',width:45">Use</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Machine No</span>
                    <input style="width:60%;" id="filter_machine_id" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed:true,modal:true" style="width:60%;height:650px;padding:10px;">
    <form id="frm_insert" method="post" novalidate enctype="multipart/form-data">
        <div class="easyui-tabs" style="width:100%;height:570px;">
            <div title="Parameter Setting 1" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem" hidden>
                            <span style="width:35%; display:inline-block;">Product Id</span>
                            <input style="width:60%;" name="item_fg_id" id="item_fg_id" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Product No</span>
                            <input style="width:60%;" name="item_fg_number" id="item_fg_number" required="" class="easyui-combogrid">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Product Name</span>
                            <input style="width:60%;" name="item_fg_name" id="item_fg_name" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Customer Id</span>
                            <input style="width:60%;" name="machine_id" id="machine_id" class="easyui-combobox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Tonage</span>
                            <input style="width:60%;" name="toonage" id="toonage" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Maker</span>
                            <input style="width:60%;" name="maker" id="maker" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Cycle Time Actual</span>
                            <input style="width:60%;" name="cycle_time_actual" id="cycle_time_actual" class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Dryer Temperature</span>
                            <input style="width:60%;" name="dryer_temperature" id="dryer_temperature" class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Dryer Time</span>
                            <input style="width:60%;" name="dryer_time" id="dryer_time" class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Part Weight</span>
                            <input style="width:60%;" name="weight" id="weight" readonly class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Running Cavity</span>
                            <input style="width:60%;" name="cavity_actual" id="cavity_actual" class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Runner weight/Shoot</span>
                            <input style="width:60%;" name="runner" id="runner" readonly class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem" hidden>
                            <span style="width:35%; display:inline-block;">Part Id</span>
                            <input style="width:60%;" name="item_rm_id" id="item_rm_id" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Part No</span>
                            <input style="width:60%;" name="item_rm_number" id="item_rm_number" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Part Name</span>
                            <input style="width:60%;" name="item_rm_name" id="item_rm_name" readonly class="easyui-textbox">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Barrel Temperature" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Nozzle</span>
                            <input style="width:60%;" name="nozzle" id="nozzle" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Front</span>
                            <input style="width:60%;" name="front" id="front" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Middle 3</span>
                            <input style="width:60%;" name="middle_3" id="middle_3" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Middle 2</span>
                            <input style="width:60%;" name="middle_2" id="middle_2" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Middle 1</span>
                            <input style="width:60%;" name="middle_1" id="middle_1" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Rear</span>
                            <input style="width:60%;" name="rear" id="rear" class="easyui-numberbox">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Injection" style="padding:15px">
                <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between;">
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>INJECTION POSITION</b></legend>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">S1</span>
                            <input style="width:65%;" name="s1_injection" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">S2</span>
                            <input style="width:65%;" name="s2_injection" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">S3</span>
                            <input style="width:65%;" name="s3_injection" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">S4</span>
                            <input style="width:65%;" name="s4_injection" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">S5</span>
                            <input style="width:65%;" name="s5_injection" class="easyui-numberbox">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>INJECTION SPEED</b></legend>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">V1</span>
                            <input style="width:65%;" name="v1_injection" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">V2</span>
                            <input style="width:65%;" name="v2_injection" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">V3</span>
                            <input style="width:65%;" name="v3_injection" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">V4</span>
                            <input style="width:65%;" name="v4_injection" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">V5</span>
                            <input style="width:65%;" name="v5_injection" class="easyui-numberbox">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>INJECTION PRESSURE</b></legend>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">P1</span>
                            <input style="width:65%;" name="p1_injection" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">P2</span>
                            <input style="width:65%;" name="p2_injection" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">P3</span>
                            <input style="width:65%;" name="p3_injection" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">P4</span>
                            <input style="width:65%;" name="p4_injection" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">P5</span>
                            <input style="width:65%;" name="p5_injection" class="easyui-numberbox">
                        </div>
                    </fieldset>

                </div>
            </div>
            <div title="Holding" style="padding:15px">
                <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between;">
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Holding Pressure</b></legend>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">P1</span>
                            <input style="width:60%;" name="p1_holding" id="p1_holding" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">P2</span>
                            <input style="width:60%;" name="p2_holding" id="p2_holding" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">P3</span>
                            <input style="width:60%;" name="p3_holding" id="p3_holding" class="easyui-numberbox">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Holding Speed</b></legend>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">V1</span>
                            <input style="width:60%;" name="v1_holding" id="v1_holding" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">V2</span>
                            <input style="width:60%;" name="v2_holding" id="v2_holding" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">V3</span>
                            <input style="width:60%;" name="v3_holding" id="v3_holding" class="easyui-numberbox">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Holding Time</b></legend>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">T1</span>
                            <input style="width:60%;" name="t1_holding" id="t1_holding" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">T2</span>
                            <input style="width:60%;" name="t2_holding" id="t2_holding" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">T3</span>
                            <input style="width:60%;" name="t3_holding" id="t3_holding" class="easyui-numberbox">
                        </div>
                    </fieldset>
                </div>
            </div>
            <div title="Charging" style="padding:15px">
                <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between;">
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Speed</b></legend>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">1</span>
                            <input style="width:60%;" name="1_charging_speed" id="1_charging_speed" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">2</span>
                            <input style="width:60%;" name="2_charging_speed" id="2_charging_speed" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">3</span>
                            <input style="width:60%;" name="3_charging_speed" id="3_charging_speed" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">4</span>
                            <input style="width:60%;" name="4_charging_speed" id="4_charging_speed" class="easyui-numberbox">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Pressure</b></legend>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">1</span>
                            <input style="width:60%;" name="1_charging_pressure" id="1_charging_pressure" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">2</span>
                            <input style="width:60%;" name="2_charging_pressure" id="2_charging_pressure" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">3</span>
                            <input style="width:60%;" name="3_charging_pressure" id="3_charging_pressure" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">4</span>
                            <input style="width:60%;" name="4_charging_pressure" id="4_charging_pressure" class="easyui-numberbox">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Back Pressure</b></legend>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">1</span>
                            <input style="width:60%;" name="1_charging_back_pressure" id="1_charging_back_pressure" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">2</span>
                            <input style="width:60%;" name="2_charging_back_pressure" id="2_charging_back_pressure" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">3</span>
                            <input style="width:60%;" name="3_charging_back_pressure" id="3_charging_back_pressure" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">4</span>
                            <input style="width:60%;" name="4_charging_back_pressure" id="4_charging_back_pressure" class="easyui-numberbox">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Position</b></legend>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">1</span>
                            <input style="width:60%;" name="1_charging_position" id="1_charging_position" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">2</span>
                            <input style="width:60%;" name="2_charging_position" id="2_charging_position" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">3</span>
                            <input style="width:60%;" name="3_charging_position" id="3_charging_position" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">4</span>
                            <input style="width:60%;" name="4_charging_position" id="4_charging_position" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">END</span>
                            <input style="width:60%;" name="end_charging_position" id="end_charging_position" class="easyui-numberbox">
                        </div>
                    </fieldset>
                </div>
            </div>
            <div title="Suck Back" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Suck Back 1 Pressure</span>
                            <input style="width:60%;" name="suck_back_1_pressure" id="suck_back_1_pressure" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Suck Back 2 Pressure</span>
                            <input style="width:60%;" name="suck_back_2_pressure" id="suck_back_2_pressure" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Suck Back 1</span>
                            <input style="width:60%;" name="suck_back_1" id="suck_back_1" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Suck Back 2</span>
                            <input style="width:60%;" name="suck_back_2" id="suck_back_2" class="easyui-numberbox">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Parameter Setting 2" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Injection Time</span>
                            <input style="width:60%;" name="injection_time" id="injection_time" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Delay for SB 1</span>
                            <input style="width:60%;" name="delay_sb1" id="delay_sb1" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Delay for SB 2</span>
                            <input style="width:60%;" name="delay_sb2" id="delay_sb2" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Delay of Charge</span>
                            <input style="width:60%;" name="delay_charge" id="delay_charge" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Inj. Monitoring Time</span>
                            <input style="width:60%;" name="inj_monitoring_time" id="inj_monitoring_time" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Charge Monitoring Time</span>
                            <input style="width:60%;" name="charge_monitoring_time" id="charge_monitoring_time" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Cooling Time</span>
                            <input style="width:60%;" name="cooling_time" id="cooling_time" class="easyui-numberbox" data-options="precision:2">
                        </div>

                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Min. Cushion Check</span>
                            <input style="width:60%;" name="min_cushion_check" id="min_cushion_check" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Min. Cushion Low Limit</span>
                            <input style="width:60%;" name="min_cushion_low_limit" id="min_cushion_low_limit" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Min. Cushion Upper Limit</span>
                            <input style="width:60%;" name="min_cushion_upper_limit" id="min_cushion_upper_limit" class="easyui-numberbox" data-options="precision:2">
                        </div>

                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Charge After Cooling</span>
                            <input style="width:60%;" name="charge_after_cooling" id="charge_after_cooling" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Use of Manual Back Pressure</span>
                            <input style="width:60%;" name="use_manual_back_pressure" id="use_manual_back_pressure" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Actual Cushion</span>
                            <input style="width:60%;" name="actual_cushion" id="actual_cushion" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Switch Over Position</span>
                            <input style="width:60%;" name="switch_over_position" id="switch_over_position" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Switch Over Time</span>
                            <input style="width:60%;" name="switch_over_time" id="switch_over_time" class="easyui-numberbox" data-options="precision:2">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Mold Closing" style="padding:15px">
                <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between;">
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Mold Closing Speed</b></legend>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">V1</span>
                            <input style="width:60%;" name="v1_mold_closing" id="v1_mold_closing" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">V2</span>
                            <input style="width:60%;" name="v2_mold_closing" id="v2_mold_closing" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">V3</span>
                            <input style="width:60%;" name="v3_mold_closing" id="v3_mold_closing" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">V4</span>
                            <input style="width:60%;" name="v4_mold_closing" id="v4_mold_closing" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">V5</span>
                            <input style="width:60%;" name="v5_mold_closing" id="v5_mold_closing" class="easyui-numberbox">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Mold Closing Pressure</b></legend>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">P1</span>
                            <input style="width:60%;" name="p1_mold_closing" id="p1_mold_closing" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">P2</span>
                            <input style="width:60%;" name="p2_mold_closing" id="p2_mold_closing" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">P3</span>
                            <input style="width:60%;" name="p3_mold_closing" id="p3_mold_closing" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">P4</span>
                            <input style="width:60%;" name="p4_mold_closing" id="p4_mold_closing" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">P5</span>
                            <input style="width:60%;" name="p5_mold_closing" id="p5_mold_closing" class="easyui-numberbox">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Mold Closing Position</b></legend>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">S1</span>
                            <input style="width:60%;" name="s1_mold_closing" id="s1_mold_closing" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">S2</span>
                            <input style="width:60%;" name="s2_mold_closing" id="s2_mold_closing" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">S3</span>
                            <input style="width:60%;" name="s3_mold_closing" id="s3_mold_closing" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">S4</span>
                            <input style="width:60%;" name="s4_mold_closing" id="s4_mold_closing" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">S5</span>
                            <input style="width:60%;" name="s5_mold_closing" id="s5_mold_closing" class="easyui-numberbox">
                        </div>
                    </fieldset>
                </div>
            </div>
            <div title="Mold Opening" style="padding:15px">
                <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between;">
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Mold Opening Speed</b></legend>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">V1</span>
                            <input style="width:60%;" name="v1_mold_opening" id="v1_mold_opening" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">V2</span>
                            <input style="width:60%;" name="v2_mold_opening" id="v2_mold_opening" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">V3</span>
                            <input style="width:60%;" name="v3_mold_opening" id="v3_mold_opening" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">V4</span>
                            <input style="width:60%;" name="v4_mold_opening" id="v4_mold_opening" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">V5</span>
                            <input style="width:60%;" name="v5_mold_opening" id="v5_mold_opening" class="easyui-numberbox">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Mold Opening Pressure</b></legend>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">P1</span>
                            <input style="width:60%;" name="p1_mold_opening" id="p1_mold_opening" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">P2</span>
                            <input style="width:60%;" name="p2_mold_opening" id="p2_mold_opening" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">P3</span>
                            <input style="width:60%;" name="p3_mold_opening" id="p3_mold_opening" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">P4</span>
                            <input style="width:60%;" name="p4_mold_opening" id="p4_mold_opening" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">P5</span>
                            <input style="width:60%;" name="p5_mold_opening" id="p5_mold_opening" class="easyui-numberbox">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Mold Opening Position</b></legend>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">S1</span>
                            <input style="width:60%;" name="s1_mold_opening" id="s1_mold_opening" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">S2</span>
                            <input style="width:60%;" name="s2_mold_opening" id="s2_mold_opening" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">S3</span>
                            <input style="width:60%;" name="s3_mold_opening" id="s3_mold_opening" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">S4</span>
                            <input style="width:60%;" name="s4_mold_opening" id="s4_mos4_mold_openingld" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">S5</span>
                            <input style="width:60%;" name="s5_mold_opening" id="s5_mold_opening" class="easyui-numberbox">
                        </div>
                    </fieldset>
                </div>
            </div>
            <div title="Mold Time" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Delay for Mold Closing</span>
                            <input style="width:60%;" name="delay_mold_closing" id="delay_mold_closing" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">M.Q Delay Time</span>
                            <input style="width:60%;" name="mq_delay_time" id="mq_delay_time" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mold Closing Time</span>
                            <input style="width:60%;" name="mold_closing_time" id="mold_closing_time" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mold Opening Time</span>
                            <input style="width:60%;" name="mold_opening_time" id="mold_opening_time" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Process Time</span>
                            <input style="width:60%;" name="process_time" id="process_time" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mold Protection Time 1</span>
                            <input style="width:60%;" name="mold_protection_time_1" id="mold_protection_time_1" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mold Protection Time 2</span>
                            <input style="width:60%;" name="mold_protection_time_2" id="mold_protection_time_2" class="easyui-numberbox" data-options="precision:2">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Ejection Forward" style="padding:15px">
                <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between;">
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Ejection Speed</b></legend>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">V1</span>
                            <input style="width:65%;" name="v1_ej_forward" id="v1_ej_forward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">V2</span>
                            <input style="width:65%;" name="v2_ej_forward" id="v2_ej_forward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">V3</span>
                            <input style="width:65%;" name="v3_ej_forward" id="v3_ej_forward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">V4</span>
                            <input style="width:65%;" name="v4_ej_forward" id="v4_ej_forward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Ejection Pressure</b></legend>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">P1</span>
                            <input style="width:65%;" name="p1_ej_forward" id="p1_ej_forward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">P2</span>
                            <input style="width:65%;" name="p2_ej_forward" id="p2_ej_forward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">P3</span>
                            <input style="width:65%;" name="p3_ej_forward" id="p3_ej_forward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">P4</span>
                            <input style="width:65%;" name="p4_ej_forward" id="p4_ej_forward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Ejection Position</b></legend>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">S1</span>
                            <input style="width:65%;" name="s1_ej_forward" id="s1_ej_forward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">S2</span>
                            <input style="width:65%;" name="s2_ej_forward" id="s2_ej_forward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">S3</span>
                            <input style="width:65%;" name="s3_ej_forward" id="s3_ej_forward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">S4</span>
                            <input style="width:65%;" name="s4_ej_forward" id="s4_ej_forward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                    </fieldset>
                </div>
            </div>
            <div title="Ejection Backward" style="padding:15px">
                <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between;">
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Ejection Speed</b></legend>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">V1</span>
                            <input style="width:65%;" name="v1_ej_backward" id="v1_ej_backward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">V2</span>
                            <input style="width:65%;" name="v2_ej_backward" id="v2_ej_backward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">V3</span>
                            <input style="width:65%;" name="v3_ej_backward" id="v3_ej_backward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">V4</span>
                            <input style="width:65%;" name="v4_ej_backward" id="v4_ej_backward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Ejection Pressure</b></legend>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">P1</span>
                            <input style="width:65%;" name="p1_ej_backward" id="p1_ej_backward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">P2</span>
                            <input style="width:65%;" name="p2_ej_backward" id="p2_ej_backward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">P3</span>
                            <input style="width:65%;" name="p3_ej_backward" id="p3_ej_backward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">P4</span>
                            <input style="width:65%;" name="p4_ej_backward" id="p4_ej_backward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:4px; padding: 10px; box-sizing: border-box;">
                        <legend><b>Ejection Position</b></legend>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">S1</span>
                            <input style="width:65%;" name="s1_ej_backward" id="s1_ej_backward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">S2</span>
                            <input style="width:65%;" name="s2_ej_backward" id="s2_ej_backward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">S3</span>
                            <input style="width:65%;" name="s3_ej_backward" id="s3_ej_backward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:30%; display:inline-block;">S4</span>
                            <input style="width:65%;" name="s4_ej_backward" id="s4_ej_backward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                    </fieldset>
                </div>
            </div>
            <div title="Ejection" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Ejecting Time</span>
                            <input style="width:60%;" name="ejecting_time" id="ejecting_time" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Delay For Forward</span>
                            <input style="width:60%;" name="delay_forward" id="delay_forward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Delay For Backward</span>
                            <input style="width:60%;" name="delay_backward" id="delay_backward" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Forward Time</span>
                            <input style="width:60%;" name="forward_time" id="forward_time" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Backward Time</span>
                            <input style="width:60%;" name="backward_time" id="backward_time" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Every Time Delays</span>
                            <input style="width:60%;" name="every_time_delays" id="every_time_delays" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Ejector Forward Maintain</span>
                            <input style="width:60%;" name="ejector_forward_maintain" id="ejector_forward_maintain" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Semi Automatic EJ f/b Switch</span>
                            <input style="width:60%;" name="semi_automatic_ej_switch" id="semi_automatic_ej_switch" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Ejector BW COM Signal</span>
                            <input style="width:60%;" name="ejector_bw_com_signal" id="ejector_bw_com_signal" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Semi Automatic Safety Door Start</span>
                            <input style="width:60%;" name="semi_automatic_safety_door_start" id="semi_automatic_safety_door_start" class="easyui-numberbox" data-options="precision:2">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Mold Cooling" style="padding:15px">
                <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: flex-start;">
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:8px; padding: 15px; box-sizing: border-box; background-color: #f9f9f9;">
                        <legend><b>Core</b></legend>
                        <div class="fitem" style="margin-bottom: 10px;">
                            <span style="width:40%; display:inline-block;">Temperature</span>
                            <input style="width:55%;" name="core_temp" id="core_temp" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:40%; display:inline-block;">Use</span>
                            <input style="width:55%;" name="core_use" id="core_use" class="easyui-textbox">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:8px; padding: 15px; box-sizing: border-box; background-color: #f9f9f9;">
                        <legend><b>Slider</b></legend>
                        <div class="fitem" style="margin-bottom: 10px;">
                            <span style="width:40%; display:inline-block;">Temperature</span>
                            <input style="width:55%;" name="slider_temp" id="slider_temp" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:40%; display:inline-block;">Use</span>
                            <input style="width:55%;" name="slider_use" id="slider_use" class="easyui-textbox">
                        </div>
                    </fieldset>
                    <fieldset style="width: 48%; border:1px solid #d0d0d0; border-radius:8px; padding: 15px; box-sizing: border-box; background-color: #f9f9f9;">
                        <legend><b>Cavity</b></legend>
                        <div class="fitem" style="margin-bottom: 10px;">
                            <span style="width:40%; display:inline-block;">Temperature</span>
                            <input style="width:55%;" name="cavity_temp" id="cavity_temp" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:40%; display:inline-block;">Use</span>
                            <input style="width:55%;" name="cavity_use" id="cavity_use" class="easyui-textbox">
                        </div>
                    </fieldset>
                </div>
            </div>
            <div title="Hot Runner Temperature" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">ZONE 1</span>
                            <input style="width:60%;" name="hr_zone_1" id="hr_zone_1" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">ZONE 2</span>
                            <input style="width:60%;" name="hr_zone_2" id="hr_zone_2" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">ZONE 3</span>
                            <input style="width:60%;" name="hr_zone_3" id="hr_zone_3" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">ZONE 4</span>
                            <input style="width:60%;" name="hr_zone_4" id="hr_zone_4" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">ZONE 5</span>
                            <input style="width:60%;" name="hr_zone_5" id="hr_zone_5" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">ZONE 6</span>
                            <input style="width:60%;" name="hr_zone_6" id="hr_zone_6" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">ZONE 7</span>
                            <input style="width:60%;" name="hr_zone_7" id="hr_zone_7" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">ZONE 8</span>
                            <input style="width:60%;" name="hr_zone_8" id="hr_zone_8" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">ZONE 9</span>
                            <input style="width:60%;" name="hr_zone_9" id="hr_zone_9" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">ZONE 10</span>
                            <input style="width:60%;" name="hr_zone_10" id="hr_zone_10" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">ZONE 11</span>
                            <input style="width:60%;" name="hr_zone_11" id="hr_zone_11" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">ZONE 12</span>
                            <input style="width:60%;" name="hr_zone_12" id="hr_zone_12" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">ZONE 13</span>
                            <input style="width:60%;" name="hr_zone_13" id="hr_zone_13" class="easyui-numberbox" data-options="precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">ZONE 14</span>
                            <input style="width:60%;" name="hr_zone_14" id="hr_zone_14" class="easyui-numberbox" data-options="precision:2">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Update -->
<div id="dlg_insert2" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 40%; padding:10px; top: 10px;">
    <form id="frm_insert2" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">ID</span>
                    <input style="width:30%;" name="id" id="id" class="easyui-textbox" readonly>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Customer ID</span>
                    <input style="width:30%;" name="customer_id" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer_name" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Division ID</span>
                    <input style="width:60%;" name="division_id"  class="easyui-textbox" readonly>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Plant ID</span>
                    <input style="width:30%;" name="customer_address_id" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" name="plant" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part Id</span>
                    <input style="width:60%;" name="item_fg_id" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part Number</span>
                    <input style="width:60%;" name="item_fg_number" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Price</span>
                    <input style="width:60%;" name="price" class="easyui-numberbox" precision='4'>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:60%;" name="currency" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Valid Date</span>
                    <input style="width:60%;" name="valid_date" required="" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remark</span>
                    <input style="width:60%;" name="remark"  class="easyui-textbox">
                </div>
        </fieldset>
    </form>
</div>

<!-- Detail Histories -->
<div id="dlg_history" class="easyui-dialog" title="Price Histories" data-options="closed: true,modal:true" style="width: 600px; height: 300px; top: 20px;">
    <table id="dg_history" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'price',width:100,halign:'center',formatter: priceformat">Price</th>
                <th data-options="field:'valid_date',width:100,halign:'center'">Valid Date</th>
                <th data-options="field:'created_by',width:120,align:'center'"> Created By</th>
                <th data-options="field:'created_date',width:150,align:'center'"> Created Date</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:15px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #ddd; margin-bottom: 15px; border-radius:4px; padding: 10px;">
            <legend style="padding: 0 5px;"><b>Form Data</b></legend>
            <div class="fitem" style="margin-bottom: 5px;">
                <label style="width:30%; display:inline-block;">File Upload</label>
                <input name="file_upload" style="width: 65%;" required="true" accept=".xls" id="file_excel" class="easyui-filebox" data-options="prompt:'Choose file...'">
            </div>
        </fieldset>
    </form>

    <div style="overflow: hidden; margin-bottom: 5px; font-weight: bold;">
        <span style="float: left; color:#27ae60;">SUCCESS : <span id="p_success">0</span></span>
        <span style="float: right; color:#e74c3c;">FAILED : <span id="p_failed">0</span></span>
    </div>

    <div id="p_upload" class="easyui-progressbar" style="width:100%; height: 20px;"></div>
    
    <div style="margin: 5px 0 15px 0; text-align: center; font-weight: bold;">
        <span id="p_start">0</span> Of <span id="p_finish">0</span>
    </div>

    <div id="p_remarks" title="Upload Logs" class="easyui-panel" style="width:100%; height:180px; background-color: #f9f9f9;">
        <ul id="remarks" style="list-style-type: none; padding: 5px; margin: 0; font-family: monospace; font-size: 11px;">
            </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/setting_parameters/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/setting_parameters/create') ?>';
        $('#frm_insert').form('clear');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        console.log(row);

        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/setting_parameters/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        console.log(rows);
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('master/setting_parameters/delete') ?>',
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
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }
    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_setting_parameters.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_machine_id = $("#filter_machine_id").combobox('getValue');

        var url = "?filter_item_fg_id=" + window.btoa(filter_item_fg_id) + "&filter_machine_id=" + window.btoa(filter_machine_id);


        $('#dg').datagrid({
            url: '<?= base_url('master/setting_parameters/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('master/setting_parameters/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_machine_id = $("#filter_machine_id").combobox('getValue');

        var url = "?filter_item_fg_id=" + window.btoa(filter_item_fg_id) + "&filter_machine_id=" + window.btoa(filter_machine_id);

        window.location.assign('<?= base_url('master/setting_parameters/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //ADD DATA
        $('#dg').datagrid({
            url: '<?= base_url('master/setting_parameters/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        })

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
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
                            $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#dlg_insert').dialog({
            onOpen: function () {
                $.parser.parse('#dlg_insert');
            }
        });

        $('#adjustment, #price_layer, #qty_packing_standart').numberbox({
            onChange: function () {
                calculateAdjustmentAndPart();
            }
        });

        $('#qty_usage').numberbox({
            onChange: function () {
                calculatePartOnly();
            }
        });

        $('#adjustment_foam, #price_foam').numberbox({
            onChange: function () {
                calculateAdjustmentAndPartFoam();
            }
        });

        $('#qty_foam').numberbox({
            onChange: function () {
                calculatePartOnlyFoam();
            }
        });

        $('#adjustment_tape, #price_tape, #length').numberbox({
            onChange: function () {
                calculateAdjustmentAndPartTape();
            }
        });

        $('#qty_tape').numberbox({
            onChange: function () {
                calculatePartOnlyTape();
            }
        });

        $('#price_pcs_1, #qty_polybag_1').numberbox({
            onChange: function () {
                calculatePolybag(1);
            }
        });

        $('#price_pcs_2, #qty_polybag_2').numberbox({
            onChange: function () {
                calculatePolybag(2);
            }
        });

        $('#volume').numberbox({
            onChange: function () {
                calculateNeedPartDay();
            }
        });

        $('#storage_pos, #storage_duration, #box_price, #month').numberbox({
            onChange: function () {
                calculateBoxCalculation();
            }
        });

        $('#price_part, #price_part_1, #price_part_2, #price_part_foam, #price_part_tape, #price_part_box').numberbox({
            onChange: function () {
                calculateTotalPackingCost();
            }
        });

        $('#box_length, #box_width, #box_height, #vehicle_length, #vehicle_width, #vehicle_height').textbox({
                onChange: function () {
                    calculateArmadaCapacity();
                }
            });

        $('#qty_packing_standart').numberbox({
            onChange: function () {
                calculateNeedPartDay();
                calculateArmadaCapacity();
            }
        });

        $('#distance_astimation, #tol_price').numberbox({
            onChange: function () {
                calculateBbmAndOperation();
            }
        });

        $('#fuel_consumption_per_km, #bbm_price, #rent_daily, #mp_cost_daily').numberbox({
            onChange: function () {
                calculateBbmAndOperation();
            }
        });

        $('#operation, #armada_cap_pcs').numberbox({
            onChange: function () {
                calculateTransportationCostPcs();
            }
        });

        $('#packing_box').combobox({
            onChange: function (newValue) {
                // 1. Reset input fisik (yang bisa diisi user) berdasarkan tipe
                if (newValue === "Returnable Box") {
                    $('#palet_price').numberbox('setValue', 0);
                    $('#mpq_price').numberbox('setValue', 0);
                } 
                else if (newValue === "Carton Box") {
                    $('#palet_price').numberbox('setValue', 0);
                    // Reset input yang hanya milik Returnable
                    $('#storage_pos').numberbox('setValue', 0);
                    $('#storage_duration').numberbox('setValue', 0);
                    $('#volume').numberbox('setValue', 0);
                    $('#month').numberbox('setValue', 0);
                } 
                else if (newValue === "Palet") {
                    $('#box_price').numberbox('setValue', 0);
                    // Reset input yang hanya milik Returnable
                    $('#storage_pos').numberbox('setValue', 0);
                    $('#storage_duration').numberbox('setValue', 0);
                    $('#volume').numberbox('setValue', 0);
                    $('#month').numberbox('setValue', 0);
                }

                // 2. Jalankan kalkulasi untuk mereset field readonly (total, planning, dll)
                calculateBoxCalculation();
            }
        });

        $('#palet_price, #mpq_price').numberbox({
            onChange: function () {
                calculateBoxCalculation();
            }
        });
    });

    function calculateAdjustmentAndPart() {
        var qtyUsage     = parseFloat($('#qty_usage').numberbox('getValue')) || 0;
        var priceLayer   = parseFloat($('#price_layer').numberbox('getValue')) || 0;
        var adjustment   = parseFloat($('#adjustment').numberbox('getValue')) || 0;
        var qtyPacking   = parseFloat($('#qty_packing_standart').numberbox('getValue')) || 0;

        // reset
        $('#price_adjustment').numberbox('setValue', 0);
        $('#price_part').numberbox('setValue', 0);

        if (adjustment <= 0 || priceLayer <= 0) {
            return;
        }

        var priceAdjustment = priceLayer + (priceLayer * (adjustment / 100));
        $('#price_adjustment').numberbox('setValue', priceAdjustment.toFixed(2));

        if (qtyUsage > 0 && qtyPacking > 0) {
            var pricePart = (qtyUsage * priceAdjustment) / qtyPacking;
            $('#price_part').numberbox('setValue', pricePart.toFixed(2));
        }
    }

    function calculateAdjustmentAndPartFoam() {
        var qtyUsage    = parseFloat($('#qty_foam').numberbox('getValue')) || 0;
        var priceLayer  = parseFloat($('#price_foam').numberbox('getValue')) || 0;
        var adjustment  = parseFloat($('#adjustment_foam').numberbox('getValue')) || 0;

        // reset
        $('#price_adjustment_foam').numberbox('setValue', 0);
        $('#price_part_foam').numberbox('setValue', 0);

        if (adjustment <= 0 || priceLayer <= 0) {
            return;
        }

        var priceAdjustment = priceLayer + (priceLayer * (adjustment / 100));
        $('#price_adjustment_foam').numberbox('setValue', priceAdjustment.toFixed(2));

        if (qtyUsage > 0) {
            var pricePart = qtyUsage * priceAdjustment;
            $('#price_part_foam').numberbox('setValue', pricePart.toFixed(2));
        }
    }

    function calculateAdjustmentAndPartTape() {
        var priceLayer = parseFloat($('#price_tape').numberbox('getValue')) || 0;
        var adjustment = parseFloat($('#adjustment_tape').numberbox('getValue')) || 0;
        var length     = parseFloat($('#length').numberbox('getValue')) || 0;
        var qtyUsage   = parseFloat($('#qty_tape').numberbox('getValue')) || 0;

        // reset output
        $('#price_adjustment_tape').numberbox('setValue', 0);
        $('#price_mm_tape').numberbox('setValue', 0);
        $('#price_part_tape').numberbox('setValue', 0);

        if (priceLayer <= 0 || adjustment <= 0) {
            return;
        }

        // price adjustment
        var priceAdjustment = priceLayer + (priceLayer * (adjustment / 100));
        $('#price_adjustment_tape').numberbox('setValue', priceAdjustment.toFixed(2));

        if (length <= 0) {
            return;
        }

        // price per mm
        var priceMm = priceAdjustment / (length * 1000);
        $('#price_mm_tape').numberbox('setValue', priceMm.toFixed(6));

        // price per part
        if (qtyUsage > 0) {
            var pricePart = qtyUsage * priceMm;
            $('#price_part_tape').numberbox('setValue', pricePart.toFixed(2));
        }
        
    }

    function calculatePartOnly() {
        var qtyUsage        = parseFloat($('#qty_usage').numberbox('getValue')) || 0;
        var priceAdjustment = parseFloat($('#price_adjustment').numberbox('getValue')) || 0;
        var qtyPacking      = parseFloat($('#qty_packing_standart').numberbox('getValue')) || 0;

        if (priceAdjustment <= 0 || qtyUsage <= 0 || qtyPacking <= 0) {
            $('#price_part').numberbox('setValue', 0);
            return;
        }

        var pricePart = (qtyUsage * priceAdjustment) / qtyPacking;
        $('#price_part').numberbox('setValue', pricePart.toFixed(2));
    }

    function calculatePartOnlyFoam() {
        var qtyUsage         = parseFloat($('#qty_foam').numberbox('getValue')) || 0;
        var priceAdjustment  = parseFloat($('#price_adjustment_foam').numberbox('getValue')) || 0;

        if (priceAdjustment <= 0 || qtyUsage <= 0) {
            $('#price_part_foam').numberbox('setValue', 0);
            return;
        }

        var pricePart = qtyUsage * priceAdjustment;
        $('#price_part_foam').numberbox('setValue', pricePart.toFixed(2));
    }

    function calculatePartOnlyTape() {
        var qtyUsage = parseFloat($('#qty_tape').numberbox('getValue')) || 0;
        var priceMm  = parseFloat($('#price_mm_tape').numberbox('getValue')) || 0;

        if (qtyUsage <= 0 || priceMm <= 0) {
            $('#price_part_tape').numberbox('setValue', 0);
            return;
        }

        var pricePart = qtyUsage * priceMm;
        $('#price_part_tape').numberbox('setValue', pricePart.toFixed(2));
    }

    function calculatePolybag(index) {
        var pricePcs = parseFloat($('#price_pcs_' + index).numberbox('getValue')) || 0;
        var qty      = parseFloat($('#qty_polybag_' + index).numberbox('getValue')) || 0;

        if (pricePcs > 0 && qty > 0) {
            var pricePart = pricePcs / qty;
            $('#price_part_' + index).numberbox('setValue', pricePart.toFixed(2));
        } else {
            $('#price_part_' + index).numberbox('setValue', 0);
        }
    }

    function calculateNeedPartDay() {
        var volume      = parseFloat($('#volume').numberbox('getValue')) || 0;
        var qtyPacking  = parseFloat($('#qty_packing_standart').numberbox('getValue')) || 0;

        // reset
        $('#need_part_day').numberbox('setValue', 0);
        $('#need_box_day').numberbox('setValue', 0);

        if (volume <= 0) {
            return;
        }

        var needPartDay = volume / 21;
        $('#need_part_day').numberbox('setValue', needPartDay.toFixed(2));

        if (qtyPacking > 0) {
            var needBoxDay = needPartDay / qtyPacking;
            $('#need_box_day').numberbox('setValue', needBoxDay.toFixed(2));
        }
    }

    function calculateBoxCalculation() {
        var packingType     = $('#packing_box').combobox('getValue');
        var needBoxDay      = parseFloat($('#need_box_day').numberbox('getValue')) || 0;
        var storagePos      = parseFloat($('#storage_pos').numberbox('getValue')) || 0;
        var storageDuration = parseFloat($('#storage_duration').numberbox('getValue')) || 0;
        var boxPrice        = parseFloat($('#box_price').numberbox('getValue')) || 0;
        var paletPrice      = parseFloat($('#palet_price').numberbox('getValue')) || 0;
        var mpqPrice        = parseFloat($('#mpq_price').numberbox('getValue')) || 0;
        var volume          = parseFloat($('#volume').numberbox('getValue')) || 0;
        var month           = parseFloat($('#month').numberbox('getValue')) || 0;

        // Selalu reset output readonly ke 0 setiap kali fungsi dipanggil
        $('#need_pos_day').numberbox('setValue', 0);
        $('#storage_bpi_day').numberbox('setValue', 0);
        $('#total_need_box').numberbox('setValue', 0);
        $('#total_box_price').numberbox('setValue', 0);
        $('#planning').numberbox('setValue', 0);
        $('#price_part_box').numberbox('setValue', 0);

        if (packingType === "Returnable Box") {
            if (needBoxDay > 0) {
                var needPosDay = needBoxDay * storagePos;
                $('#need_pos_day').numberbox('setValue', needPosDay.toFixed(2));

                var storageBpiDay = needBoxDay * storageDuration;
                $('#storage_bpi_day').numberbox('setValue', storageBpiDay.toFixed(2));

                var totalNeedBox = needPosDay + storageBpiDay;
                $('#total_need_box').numberbox('setValue', totalNeedBox.toFixed(2));

                var totalBoxPrice = totalNeedBox * boxPrice;
                $('#total_box_price').numberbox('setValue', totalBoxPrice.toFixed(3));

                var planning = volume * month;
                $('#planning').numberbox('setValue', planning.toFixed(3));

                if (planning > 0) {
                    var pricePartBox = totalBoxPrice / planning;
                    $('#price_part_box').numberbox('setValue', pricePartBox.toFixed(2));
                }
            }
        } 
        else if (packingType === "Carton Box") {
            if (mpqPrice > 0) {
                var pricePartBox = boxPrice / mpqPrice;
                $('#price_part_box').numberbox('setValue', pricePartBox.toFixed(2));
            }
        } 
        else if (packingType === "Palet") {
            if (mpqPrice > 0) {
                var pricePartBox = paletPrice / mpqPrice;
                $('#price_part_box').numberbox('setValue', pricePartBox.toFixed(2));
            }
        }
    }

    function calculateTotalPackingCost() {
        var layer      = parseFloat($('#price_part').numberbox('getValue')) || 0;
        var polybag1   = parseFloat($('#price_part_1').numberbox('getValue')) || 0;
        var polybag2   = parseFloat($('#price_part_2').numberbox('getValue')) || 0;
        var foam       = parseFloat($('#price_part_foam').numberbox('getValue')) || 0;
        var tape       = parseFloat($('#price_part_tape').numberbox('getValue')) || 0;
        var box        = parseFloat($('#price_part_box').numberbox('getValue')) || 0;

        var totalPackingCost = layer + polybag1 + polybag2 + foam + tape + box;

        $('#total_packing_cost').numberbox(
            'setValue',
            totalPackingCost.toFixed(2)
        );
    }

    function calculateArmadaCapacity() {
        var vLength = parseFloat($('#vehicle_length').textbox('getValue')) || 0;
        var vWidth  = parseFloat($('#vehicle_width').textbox('getValue')) || 0;
        var vHeight = parseFloat($('#vehicle_height').textbox('getValue')) || 0;

        var bLength = parseFloat($('#box_length').textbox('getValue')) || 0;
        var bWidth  = parseFloat($('#box_width').textbox('getValue')) || 0;
        var bHeight = parseFloat($('#box_height').textbox('getValue')) || 0;

        var qtyPacking = parseFloat($('#qty_packing_standart').numberbox('getValue')) || 0;

        // reset
        $('#armada_cap_box').numberbox('setValue', 0);
        $('#armada_cap_pcs').numberbox('setValue', 0);

        // validasi
        if (
            vLength <= 0 || vWidth <= 0 || vHeight <= 0 ||
            bLength <= 0 || bWidth <= 0 || bHeight <= 0
        ) {
            return;
        }

        var armadaCapBoxRaw =
            0.85 *
            (vLength / bLength) *
            (vWidth  / bWidth) *
            (vHeight / bHeight);

        var armadaCapBox = Math.round(armadaCapBoxRaw);

        $('#armada_cap_box').numberbox('setValue', armadaCapBox);

        if (qtyPacking > 0) {
            var armadaCapPcs = armadaCapBox * qtyPacking;
            $('#armada_cap_pcs').numberbox('setValue', armadaCapPcs);
        }
    }

    function calculateBbmAndOperation() {
        var distance    = parseFloat($('#distance_astimation').numberbox('getValue')) || 0;
        var fuelCons    = parseFloat($('#fuel_consumption_per_km').numberbox('getValue')) || 0;
        var bbmPrice    = parseFloat($('#bbm_price').numberbox('getValue')) || 0;

        var rentDaily   = parseFloat($('#rent_daily').numberbox('getValue')) || 0;
        var tolPrice    = parseFloat($('#tol_price').numberbox('getValue')) || 0;
        var mpCostDaily = parseFloat($('#mp_cost_daily').numberbox('getValue')) || 0;

        // reset
        $('#bbm_cost').numberbox('setValue', 0);
        $('#operation').numberbox('setValue', 0);

        // hitung BBM cost
        if (distance > 0 && fuelCons > 0 && bbmPrice > 0) {
            var bbmCost = ((distance / fuelCons) * bbmPrice) * 2;
            $('#bbm_cost').numberbox('setValue', bbmCost.toFixed(2));
        }

        // bbm_cost 
        var bbmCostVal = parseFloat($('#bbm_cost').numberbox('getValue')) || 0;

        // operation
        var operation = rentDaily + bbmCostVal + tolPrice + mpCostDaily;

        $('#operation').numberbox('setValue', operation.toFixed(2));
    }

    function calculateTransportationCostPcs() {
        var operation      = parseFloat($('#operation').numberbox('getValue')) || 0;
        var armadaCapPcs   = parseFloat($('#armada_cap_pcs').numberbox('getValue')) || 0;

        // reset
        $('#transportasion_cost_pcs').numberbox('setValue', 0);

        if (operation <= 0 || armadaCapPcs <= 0) {
            return;
        }

        var transportCostPcs = operation / armadaCapPcs;
        $('#transportasion_cost_pcs').numberbox('setValue', transportCostPcs.toFixed(2));
    }

    //SAVE DATA2
    $('#dlg_insert2').dialog({
        buttons: [{
            text: 'Save',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_insert2').form('submit', {
                    url: url_save,
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
                        $('#dlg_insert2').dialog('close');
                        $('#dg').datagrid('reload');
                    }
                });
            }
        }]
    });

    $('#customer_name').combogrid({
        url: '<?= base_url('master/customers/reads/'); ?>',
        panelWidth: 370,
        idField: 'name',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Customer",
        columns: [
            [{
                field: 'number',
                title: 'Customer Code',
                width: 120
            }, {
                field: 'name',
                title: 'Customer Name',
                width: 250
            }, ]
        ],
        onSelect: function(value, rows) {
            $('#customer_id').textbox('setValue', rows.id);
        }
    });

    $('#item_fg_number').combogrid({
        url: '<?= base_url('master/setting_parameters/readItems'); ?>',
        panelWidth: 500,
        idField: 'number',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product Number.",
        columns: [
            [{
                field: 'id',
                title: 'Product ID',
                width: 200
            }, {
                field: 'number',
                title: 'Product No.',
                width: 150
            }, {
                field: 'name',
                title: 'Product Name',
                width: 150
            }]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        onSelect: function(value, rows) {
            $('#item_fg_id').textbox('setValue', rows.id);
            $('#item_fg_name').textbox('setValue', rows.name);
            $('#weight').numberbox('setValue', rows.weight);
            $('#item_rm_id').textbox('setValue', rows.item_rm_id);
            $('#item_rm_number').textbox('setValue', rows.item_rm_number);
            $('#item_rm_name').textbox('setValue', rows.item_rm_name);

            var currentMachineId = $('#machine_id').combobox('getValue');
            getRunnerData(rows.id, currentMachineId);
        }
    });

    $('#machine_id').combobox({
        url:'<?= base_url('master/machines/reads/'); ?>',
        valueField:'id',
        textField:'number',
        prompt: 'Choose Machine No.',
        onSelect: function(machine){
            $('#toonage').textbox('setValue', machine.toonage);
            $('#maker').textbox('setValue', machine.maker);

            var currentProductId = $('#item_fg_id').textbox('getValue');
            getRunnerData(currentProductId, machine.id);
        }
    });

    $('#filter_item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/reads'); ?>',
        panelWidth: 500,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No.",
        columns: [
            [{
                field: 'id',
                title: 'Product ID',
                width: 200
            }, {
                field: 'number',
                title: 'Product No.',
                width: 150
            }, {
                field: 'name',
                title: 'Product Name',
                width: 150
            }]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $('#filter_machine_id').combobox({
        url:'<?= base_url('master/machines/reads/'); ?>',
        valueField:'id',
        textField:'number',
        prompt: 'Choose Machine No.',
    });

    function getRunnerData(productId, machineId) {
        if (!productId || !machineId) return;

        $.ajax({
            url: '<?= base_url('master/setting_parameters/get_runner_weight'); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                item_fg_id: productId,
                machine_id: machineId
            },
            success: function(result) {
                var runnerVal = (result && result.runner) ? result.runner : 0;
                $('#runner').numberbox('setValue', runnerVal);
            }
        });
    }

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 0) {
            return 'Active';
        } else {
            return 'Not Active';
        }
    };

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

    function priceformat(value, row) {
        var digits, currency, format;

        if (row.currency === "USD") {
            digits = 4;
            currency = 'USD';
            format = "en-US";
        } else if (row.currency === "JPY") {
            digits = 2;
            currency = 'JPY';
            format = "ja-JP";
        } else if (row.currency === "EUR") {
            digits = 2;
            currency = 'EUR';
            format = "de-DE";
        } else {
            digits = 2;
            currency = 'IDR';
            format = "id-ID";
        }

        if (value != null) {
            const formatter = new Intl.NumberFormat(format, {
                style: 'decimal',
                minimumFractionDigits: digits
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function btnHistories(val, row) {
        var history = "viewHistories('" + row.customer_id + "','" + row.item_fg_id + "','" + row.division_id + "','" + row.customer_address_id + "')";
        return '<a class="btn btn-primary w-100" onClick="' + history + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
    }

    function viewHistories(customer_id, item_fg_id, division_id, customer_address_id) {
        $("#dlg_history").dialog('open');
        $('#dg_history').datagrid({
            url: '<?= base_url('master/setting_parameters/datatableHistories?customer_id=') ?>' + btoa(customer_id) + "&item_fg_id=" + btoa(item_fg_id) + "&division_id=" + btoa(division_id) + "&customer_address_id=" + btoa(customer_address_id),
            pagination: false,
            rownumbers: true,
        });
    }

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/setting_parameters/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/setting_parameters/upload') ?>',
                    onSubmit: function() {
                        if ($(this).form('validate') == false) {
                            return $(this).form('validate');
                        } else {
                            $.messager.progress({
                                title: 'Please Wait',
                                msg: 'Importing Excel to Database'
                            });
                        }
                    },
                    success: function(result) {
                        $.messager.progress('close');
                        //Clear File
                        $.ajax({
                            url: "<?= base_url('master/setting_parameters/uploadclearFailed') ?>"
                        });
                        var json = eval('(' + result + ')');
                        requestData(json.total, json);

                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                            if (value < 100) {
                                value = Math.floor((number / total) * 100);
                                $('#p_upload').progressbar('setValue', value);
                                $('#p_start').html(number);
                                $('#p_finish').html(total);

                                $.ajax({
                                    type: "POST",
                                    async: true,
                                    url: "<?= base_url('master/setting_parameters/uploadCreate') ?>",
                                    data: {
                                        "data": json[number - 1]
                                    },
                                    cache: false,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
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
                                                url: "<?= base_url('master/setting_parameters/uploadcreateFailed') ?>",
                                                data: {
                                                    data: json[number - 1],
                                                    message: result.message
                                                },
                                                cache: false
                                            });
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }
                                        $("#p_remarks").append(title + "<br>");
                                    }
                                });
                            }
                        }
                    }
                });
            }
        }]
    });

    function btnPrint(val, row) {
        var print = "print_setting_parameters('" + row.id + "')"; 
        return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
    }

    function print_setting_parameters(id) {
        if (!id) {
            alert("Data not Found!");
            return;
        }
        var url = "<?= base_url('master/setting_parameters/print_mps/') ?>" + window.btoa(id);
        window.open(url, "_blank", "width=1200,height=600");
    }

    //CELLSTYLE APPROVE
     function styleApproved(value, row, index) {
        if (value == "" || value === null ) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    //FORMATTER APPROVE
    function formatApproved(value) {
        if (value == "" || value === null ) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };
</script>
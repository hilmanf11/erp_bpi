<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar" pagination="true" rownumbers="true" fitColumns="false" singleSelect="false">

    <!-- FROZEN -->
    <thead frozen="true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'action_print',width:80,align:'center',formatter:formatPrint">Print</th>
            <th data-options="field:'attachment',width:80,align:'center',formatter:formatAttachment">Part Picture</th>
            <th data-options="field:'item_fg_number',width:120,halign:'center'">Product No</th>
            <th data-options="field:'item_fg_name',width:250">Product Name</th>
            <th data-options="field:'customer_name',width:250">Customer</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th data-options="field:'part_size_mm',width:120,align:'right'">Part Size (mm)</th>
            <th data-options="field:'est_part_weight_per_pcs',width:150,align:'right'">Est. Part Weight (g)</th>
            <th data-options="field:'item_rm_number',width:150">Resin Material</th>
            <th data-options="field:'mould_cav_no',width:120,align:'right'">Cavity No</th>
            <th data-options="field:'mould_base_steel',width:150">Base Steel</th>
            <th data-options="field:'core_cav_steel',width:160">Core & Cavity Steel</th>
            <th data-options="field:'mould_cav_core_steel',width:160">Cavity & Core Steel</th>
            <th data-options="field:'mold_base_accessories',width:160">Mold Accessories</th>
            <th data-options="field:'mould_frame_note',width:180">Mould Frame Note</th>
            <th data-options="field:'mould_life_estimation',width:140,align:'right'">Life Est (Shots)</th>
            <th data-options="field:'mould_finish_surface',width:150">Finish Surface</th>
            <th data-options="field:'mould_building_standard',width:160">Building Standard</th>
            <th data-options="field:'estimation_mould_length',width:120,align:'right'">Mould Length</th>
            <th data-options="field:'estimation_mould_width',width:120,align:'right'">Mould Width</th>
            <th data-options="field:'estimation_mould_height',width:120,align:'right'">Mould Height</th>
            <th data-options="field:'estimation_mould_weight',width:140,align:'right'">Mould Weight (kg)</th>

            <th data-options="field:'injection_system',width:150">Injection System</th>
            <th data-options="field:'ejection_system',width:150">Ejection System</th>
            <th data-options="field:'no_of_sliders',width:120">No of Sliders</th>
            <th data-options="field:'side_action_operated_by',width:160">Side Action Op</th>
            <th data-options="field:'no_of_lifter',width:120">No of Lifter</th>
            <th data-options="field:'hot_runner_details',width:180">Hot Runner Details</th>

            <th data-options="field:'est_runner_weight_per_pcs',width:160,align:'right'">Runner Weight (g)</th>
            <th data-options="field:'est_cycle_time',width:120,align:'right'">Cycle Time (s)</th>
            <th data-options="field:'est_machine_size',width:130,align:'right'">Machine Size (T)</th>
            <th data-options="field:'lead_time_1st_off_sample',width:160,align:'right'">Lead Time (Weeks)</th>
            <th data-options="field:'target_productivity',width:150,align:'right'">Target Prod (pcs/h)</th>
            <th data-options="field:'mold_setting_time',width:140">Mold Setting Time</th>
            <th data-options="field:'quantity_year',width:120,align:'right'">Qty / Year</th>
            <th data-options="field:'quantity_month',width:120,align:'right'">Qty / Month</th>
            <th data-options="field:'quantity_wp_month',width:140,align:'right'">Qty W/P / Month</th>
            <th data-options="field:'avg_load_cap_machine',width:130,align:'right'">Avg Load (%)</th>
            <th data-options="field:'space_needed_for_wp_fg',width:120,align:'right'">Space (m2)</th>
            <th data-options="field:'packaging',width:120,align:'right'">Packaging</th>

            <th data-options="field:'estimation_hot_runner_fob_price',width:180">Hot Runner FOB Price</th>
            <th data-options="field:'mold_flow_analysis_cost',width:160">Mold Flow Cost</th>
            <th data-options="field:'other_cost',width:140">Other Cost</th>
            <th data-options="field:'target_capability',width:150">Target Capability</th>
            <th data-options="field:'mfg_tech_alternative',width:160">Mfg Tech Alt</th>
            <th data-options="field:'customer_req',width:250">Customer Req</th>
            <th data-options="field:'experience_from_previous_dev',width:250">Prev Experience</th>
            
            <th data-options="field:'man_power_std',width:120">Man Power (Std)</th>
            <th data-options="field:'man_power_act',width:120">Man Power (Act)</th>
            <th data-options="field:'machine_std',width:120">Machine (Std)</th>
            <th data-options="field:'machine_act',width:120">Machine (Act)</th>
            <th data-options="field:'method_std',width:120">Method (Std)</th>
            <th data-options="field:'method_act',width:120">Method (Act)</th>
            <th data-options="field:'material_std',width:120">Material (Std)</th>
            <th data-options="field:'material_act',width:120">Material (Act)</th>
            <th data-options="field:'other',width:150">Other 4M</th>
            
            <th data-options="field:'rejection_ppm_copq',width:180">Rejection PPM/COPQ</th>
            <th data-options="field:'error_proofing',width:180">Error Proofing</th>

            <th data-options="field:'created_by',width:120">Created By</th>
            <th data-options="field:'created_date',width:150">Created Date</th>
            <th data-options="field:'updated_by',width:120">Updated By</th>
            <th data-options="field:'updated_date',width:150">Updated Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <!-- <div style="width: 50%; float: left;"> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" id="filter_customer_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg_number" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            <!-- </div> -->
            <!-- <div style="width: 50%; float: left;" hidden>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Id</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
                </div>
                
            </div> -->
            
        </fieldset>
        <?= $button ?>
    </div>
</div>

<style>
    .tab-wrap {
        width: 100%;
    }

    /*flex 1 kolom */
    .form-row {
        display: flex;
        flex-direction: column; /* VERTIKAL */
    }

    /* tiap fitem full width */
    .form-row .fitem {
        width: 100%;
        box-sizing: border-box;
        padding: 4px 10px;
    }

    /* input tidak terlalu panjang */
    .form-row .fitem input {
        max-width: 200px;
    }
</style>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed:true,modal:true" style="width:60%;height:650px;padding:10px;">
    <form id="frm_insert" method="post" novalidate enctype="multipart/form-data">
        <div class="easyui-tabs" style="width:100%;height:570px;">
            <div title="Part Information" style="padding:10px">
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
                        <div class="fitem" hidden>
                            <span style="width:35%; display:inline-block;">Project Number</span>
                            <input style="width:60%;" name="project_number" id="project_number" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem" hidden>
                            <span style="width:35%; display:inline-block;">Customer Id</span>
                            <input style="width:60%;" name="customer_id" id="customer_id" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Customer</span>
                            <input style="width:60%;" name="customer_name" id="customer_name" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Part Picture</span>
                            <input style="width:60%;" name="attachment" id="attachment" class="easyui-filebox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Part size/mm</span>
                            <input style="width:60%;" name="part_size_mm" id="part_size_mm" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Est. part weight/g per pcs</span>
                            <input style="width:60%;" name="est_part_weight_per_pcs" id="est_part_weight_per_pcs" class="easyui-numberbox">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Mould & Tooling Specs" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem" hidden>
                            <span style="width:35%; display:inline-block;">Moulding Resin Material</span>
                            <input style="width:60%;" name="item_rm_id" id="item_rm_id" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Moulding Resin Material</span>
                            <input style="width:60%;" name="item_rm_number" id="item_rm_number" class="easyui-combobox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mould Cavity Number</span>
                            <input style="width:60%;" name="mould_cav_no" id="mould_cav_no" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mould base Steel</span>
                            <input style="width:60%;" name="mould_base_steel" id="mould_base_steel" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Core & Cavity plate Steel</span>
                            <input style="width:60%;" name="core_cav_steel" id="core_cav_steel" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mould Cavity & Core Steel</span>
                            <input style="width:60%;" name="mould_cav_core_steel" id="mould_cav_core_steel" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mold base Accessories</span>
                            <input style="width:60%;" name="mold_base_accessories" id="mold_base_accessories" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mould Frame Note</span>
                            <input style="width:60%;" name="mould_frame_note" id="mould_frame_note" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mould life estimation </span>
                            <input style="width:60%;" name="mould_life_estimation" id="mould_life_estimation" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mould Finish Surface</span>
                            <input style="width:60%;" name="mould_finish_surface" id="mould_finish_surface" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mould Building Standard</span>
                            <input style="width:60%;" name="mould_building_standard" id="mould_building_standard" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Estimation Mould Size</span>
                            <input style="width:20%;" name="estimation_mould_length" id="estimation_mould_length" class="easyui-numberbox" data-options="buttonText:'mm',prompt:'length'">
                            <input style="width:20%;" name="estimation_mould_width" id="estimation_mould_width" class="easyui-numberbox" data-options="buttonText:'mm',prompt:'width'">
                            <input style="width:20%;" name="estimation_mould_height" id="estimation_mould_height" class="easyui-numberbox" data-options="buttonText:'mm',prompt:'height'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Estimation Mould Weigh</span>
                            <input style="width:60%;" name="estimation_mould_weight" id="estimation_mould_weight" class="easyui-numberbox" data-options="buttonText:'kg'">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Injection & Mechanism" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Injection System</span>
                            <input style="width:60%;" name="injection_system" id="injection_system" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Ejection System</span>
                            <input style="width:60%;" name="ejection_system" id="ejection_system" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">No. of sliders</span>
                            <input style="width:60%;" name="no_of_sliders" id="no_of_sliders" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Side actions operated by</span>
                            <input style="width:60%;" name="side_action_operated_by" id="side_action_operated_by" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">No. of lifter</span>
                            <input style="width:60%;" name="no_of_lifter" id="no_of_lifter" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Hot Runner Details</span>
                            <input style="width:60%;" name="hot_runner_details" id="hot_runner_details" class="easyui-textbox" data-options="prompt:'Default: Not Required'">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Production Planning" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Est. runner weight/g per pcs</span>
                            <input style="width:60%;" name="est_runner_weight_per_pcs" id="est_runner_weight_per_pcs" class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Est. cycle time</span>
                            <input style="width:60%;" name="est_cycle_time" id="est_cycle_time" class="easyui-numberbox" data-options="buttonText:'sec'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Est. Machine size</span>
                            <input style="width:60%;" name="est_machine_size" id="est_machine_size" class="easyui-numberbox" data-options="buttonText:'ton'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Lead time to 1st off samples</span>
                            <input style="width:60%;" name="lead_time_1st_off_sample" id="lead_time_1st_off_sample" class="easyui-numberbox" data-options="buttonText:'weeks'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Target Productivity</span>
                            <input style="width:60%;" name="target_productivity" id="target_productivity" class="easyui-numberbox" data-options="buttonText:'pcs/hour', onChange: calculateAvgLoad">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mold setting time</span>
                            <input style="width:60%;" name="mold_setting_time" id="mold_setting_time" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Quantity / Year</span>
                            <input style="width:60%;" name="quantity_year" id="quantity_year" class="easyui-numberbox" readonly="" data-options="buttonText:'pcs'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Quantity / Month</span>
                            <input style="width:60%;" name="quantity_month" id="quantity_month" class="easyui-numberbox" readonly="" data-options="buttonText:'pcs'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Quantity W/P / Month</span>
                            <input style="width:60%;" name="quantity_wp_month" id="quantity_wp_month" class="easyui-numberbox" readonly="" data-options="buttonText:'pcs', onChange: calculateAvgLoad">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Avg Load Capacity per machine</span>
                            <input style="width:60%;" name="avg_load_cap_machine" id="avg_load_cap_machine" class="easyui-numberbox" readonly="" data-options="buttonText:'%'" precision='2'>
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Space needed for W/P & FG</span>
                            <input style="width:60%;" name="space_needed_for_wp_fg" id="space_needed_for_wp_fg" class="easyui-numberbox" readonly="" data-options="buttonText:'m2', precision:2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Packaging</span>
                            <input style="width:60%;" name="packaging" id="packaging" class="easyui-numberbox">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Cost, Quality & Analysis" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Estimation Hot Runner FOB Price</span> 
                            <input style="width:60%;" name="estimation_hot_runner_fob_price" id="estimation_hot_runner_fob_price" class="easyui-textbox" data-options="prompt:'Default: Not Required'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mold Flow Analysis cost</span> 
                            <input style="width:60%;" name="mold_flow_analysis_cost" id="mold_flow_analysis_cost" class="easyui-textbox" data-options="prompt:'Default: Not Required'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Other cost</span> 
                            <input style="width:60%;" name="other_cost" id="other_cost" class="easyui-textbox" data-options="prompt:'Default: Not Required'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Target Capability</span> 
                            <input style="width:60%;" name="target_capability" id="target_capability" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mfg Tech Alternative</span> 
                            <input style="width:60%;" name="mfg_tech_alternative" id="mfg_tech_alternative" class="easyui-textbox" data-options="prompt:'Default: Not Required'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Customer Requirement</span> 
                            <input style="width:60%;" name="customer_req" id="customer_req" class="easyui-textbox" data-options="prompt:'Default: Not Required'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Experience From Previous Developments</span> 
                            <input style="width:60%;" name="experience_from_previous_dev" id="experience_from_previous_dev" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Man Power</span> 
                            <input style="width:30%;" name="man_power_std" id="man_power_std" class="easyui-textbox" data-options="prompt:'std'">
                            <input style="width:30%;" name="man_power_act" id="man_power_act" class="easyui-textbox" data-options="prompt:'act'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Machine</span> 
                            <input style="width:30%;" name="machine_std" id="machine_std" class="easyui-combogrid" data-options="prompt:'std'">
                            <input style="width:30%;" name="machine_act" id="machine_act" class="easyui-textbox" data-options="prompt:'act'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Method</span> 
                            <input style="width:30%;" name="method_std" id="method_std" class="easyui-textbox" data-options="prompt:'std'">
                            <input style="width:30%;" name="method_act" id="method_act" class="easyui-textbox" data-options="prompt:'act'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Material</span> 
                            <input style="width:30%;" name="material_std" id="material_std" class="easyui-textbox" data-options="prompt:'std'">
                            <input style="width:30%;" name="material_act" id="material_act" class="easyui-textbox" data-options="prompt:'act'">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Other</span> 
                            <input style="width:60%;" name="other" id="other" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Rejection PPM or COPQ</span> 
                            <input style="width:60%;" name="rejection_ppm_copq" id="other" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Error proofing</span> 
                            <input style="width:60%;" name="error_proofing" id="error_proofing" class="easyui-textbox">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Update -->
<!-- <div id="dlg_insert2" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 40%; padding:10px; top: 10px;">
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
</div> -->

<!-- Detail Histories -->
<!-- <div id="dlg_history" class="easyui-dialog" title="Price Histories" data-options="closed: true,modal:true" style="width: 600px; height: 300px; top: 20px;">
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
</div> -->

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('npd/feasibilitys/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('npd/feasibilitys/create') ?>';
        $('#frm_insert').form('clear');

    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        console.log(row);

        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('npd/feasibilitys/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('npd/feasibilitys/delete') ?>',
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
        window.location.assign('<?= base_url('template/tmp_customer_items.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_customer_id = $("#filter_customer_id").combogrid('getValue');
        var filter_item_fg_number = $("#filter_item_fg_number").combogrid('getValue');

        var url = "?filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_item_fg_number=" + window.btoa(filter_item_fg_number);

        $('#dg').datagrid({
            url: '<?= base_url('npd/feasibilitys/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('npd/feasibilitys/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_customer_id = $("#filter_customer_id").combogrid('getValue');
        var filter_item_fg_number = $("#filter_item_fg_number").combogrid('getValue');

        var url = "?filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_item_fg_number=" + window.btoa(filter_item_fg_number);

        window.location.assign('<?= base_url('npd/feasibilitys/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //ADD DATA
        $('#dg').datagrid({
            url: '<?= base_url('npd/feasibilitys/datatables') ?>',
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
    });

    function calculateAvgLoad() {
        var qty_wp = parseFloat($('#quantity_wp_month').numberbox('getValue')) || 0;
        var target_prod = parseFloat($('#target_productivity').numberbox('getValue')) || 0;
        
        if (target_prod > 0) {
            var avg_load = (qty_wp / (target_prod * 7.5 * 3) / 30) * 100;
            
            $('#avg_load_cap_machine').numberbox('setValue', avg_load.toFixed(2));
        } else {
            $('#avg_load_cap_machine').numberbox('setValue', 0);
        }
    }

    function calculateSpaceNeeded() {
        var qty_month = parseFloat($('#quantity_month').numberbox('getValue')) || 0;
        var box_space_mm2 = 590 * 390; // = 230100
        var box_stacking_qty = 6;
        var box_packing_qty = 80; 
        //    var box_packing_qty = parseFloat($('#packaging').numberbox('getValue')) || 1;

        if (qty_month > 0) {
            var total_boxes_needed = Math.ceil(qty_month / box_packing_qty);

            var space_needed = (total_boxes_needed / box_stacking_qty) * (box_space_mm2 / 1000000);

            $('#space_needed_for_wp_fg').numberbox('setValue', space_needed.toFixed(2));
        } else {
            $('#space_needed_for_wp_fg').numberbox('setValue', 0);
        }
    }

    //SAVE DATA2
    // $('#dlg_insert2').dialog({
    //     buttons: [{
    //         text: 'Save',
    //         iconCls: 'icon-ok',
    //         handler: function() {
    //             $('#frm_insert2').form('submit', {
    //                 url: url_save,
    //                 onSubmit: function() {
    //                     return $(this).form('validate');
    //                 },
    //                 success: function(result) {
    //                     var result = eval('(' + result + ')');
    //                     if (result.theme == "success") {
    //                         toastr.success(result.message, result.title);
    //                     } else {
    //                         toastr.error(result.message, result.title);
    //                     }
    //                     $('#dlg_insert2').dialog('close');
    //                     $('#dg').datagrid('reload');
    //                 }
    //             });
    //         }
    //     }]
    // });

    $('#item_fg_number').combogrid({
        url: '<?= base_url('npd/feasibilitys/readFG'); ?>',
        panelWidth: 500,
        idField: 'item_fg_number',
        textField: 'item_fg_number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product Number.",
        columns: [
            [{
                field: 'item_fg_number',
                title: 'Product No.',
                width: 150
            }, {
                field: 'item_fg_name',
                title: 'Product Name',
                width: 150
            }, {
                field: 'project_number',
                title: 'Project No',
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
            $('#item_fg_id').textbox('setValue', rows.item_fg_id);
            $('#item_fg_name').textbox('setValue', rows.item_fg_name);
            $('#customer_name').textbox('setValue', rows.customer_name);
            $('#customer_id').textbox('setValue', rows.customer_id);
            $('#project_number').textbox('setValue', rows.project_number);

            var volume = parseFloat(rows.volume) || 0;
            var qty_month = 0;

            if (rows.volume_unit === "PCS/YEAR") {
                $('#quantity_year').numberbox('setValue', volume);
                qty_month = volume / 12;
                $('#quantity_month').numberbox('setValue', qty_month);
            } 
            else if (rows.volume_unit === "PCS/MONTH") {
                $('#quantity_year').numberbox('setValue', volume * 12);
                qty_month = volume;
                $('#quantity_month').numberbox('setValue', qty_month);
            }
            
            var qty_wp_month = qty_month + (qty_month * 0.10);
            $('#quantity_wp_month').numberbox('setValue', qty_wp_month);

            calculateAvgLoad();
            calculateSpaceNeeded();
        }
    });

    $('#item_rm_number').combobox({
        url: '<?= base_url('npd/feasibilitys/readItems/'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: 'Choose Part No',
        onSelect: function(supp) {
            $('#item_rm_id').textbox('setValue', supp.id);
        }
    });

    $('#machine_std').combogrid({
        url: '<?= base_url('npd/feasibilitys/readsMachines'); ?>',
        panelWidth: 200,
        idField: 'machine_std',
        textField: 'machine_std',
        mode: 'remote',
        fitColumns: true,
        prompt: "std",
        columns: [
            [{
                field: 'toonage',
                title: 'Tonage',
                width: 100
            }, {
                field: 'maker',
                title: 'Maker',
                width: 100
            }]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }]
    });


    $('#filter_customer_id').combogrid({
        url: '<?= base_url('master/customers/reads'); ?>',
        panelWidth: 750,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Customer",
        columns: [
            [{
                field: 'id',
                title: 'Customer ID',
                width: 150
            }, {
                field: 'number',
                title: 'Customer Code',
                width: 150
            }, {
                field: 'name',
                title: 'Customer Name',
                width: 200
            }, {
                field: 'type',
                title: 'Type',
                width: 100
            }, {
                field: 'currency',
                title: 'Currency',
                width: 100
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }]
    });

    $('#filter_item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/reads'); ?>',
        panelWidth: 500,
        idField: 'id',
        textField: 'id',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product Id.",
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

    $('#filter_item_fg_number').combogrid({
        url: '<?= base_url('master/item_fg/reads'); ?>',
        panelWidth: 500,
        idField: 'number',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No.",
        columns: [
            [{
                field: 'number',
                title: 'Product No.',
                width: 150
            }, {
                field: 'name',
                title: 'Product Name',
                width: 150
            }, {
                field: 'number_customer',
                title: 'Product Customer',
                width: 200
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    // $('#division_id').combobox({
    //     url: '<?= base_url('master/divisions/reads/'); ?>',
    //     valueField: 'id',
    //     textField: 'name',
    //     prompt: 'Choose Division'
    // });


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

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('npd/feasibilitys/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('npd/feasibilitys/upload') ?>',
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
                            url: "<?= base_url('npd/feasibilitys/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('npd/feasibilitys/uploadCreate') ?>",
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
                                                url: "<?= base_url('npd/feasibilitys/uploadcreateFailed') ?>",
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

    function formatAttachment(value, row, index) {
        if (value && value !== "") {
            return '<a href="' + value + '" target="_blank" class="w-100" style="background-color: #2b91d8; color: white; padding: 5px 0; text-decoration: none; border-radius: 3px; font-size: 12px; display: inline-block; text-align: center;" ><i class="fa fa-eye"></i></a>';
        } else {
            return '<span style="color:#aaa;">-</span>';
        }
    }

    function formatPrint(value, row, index) {
        if (row.id) {
            var id_b64 = window.btoa(row.id);
            var url_print = '<?= base_url('npd/feasibilitys/print_feasibility') ?>?id=' + id_b64;
            return '<a href="' + url_print + '" target="_blank" class="w-100" style="background-color: #2b91d8; color: white; padding: 5px 0; text-decoration: none; border-radius: 3px; font-size: 12px; display: inline-block; text-align: center;" title="Print Feasibility Study"><i class="fa fa-print"></i></a>';
        } else {
            return '-';
        }
    }
</script>
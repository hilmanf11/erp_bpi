<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar" pagination="true" rownumbers="true" fitColumns="false" singleSelect="false">

    <!-- FROZEN -->
    <thead frozen="true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'item_fg_number',width:120,halign:'center'">Product No</th>
            <th data-options="field:'item_fg_name',width:250">Product Name</th>
            <th data-options="field:'customer_name',width:250">Customer</th>
            <th data-options="field:'p_month',width:60,align:'center'">Month</th>
            <th data-options="field:'p_year',width:70,align:'center'">Year</th>
            <th data-options="field:'revision',width:70,align:'center'">Rev</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <!-- LAYER -->
            <th data-options="field:'dimension_part',width:120,align:'right'">Dimension</th>
            <th data-options="field:'item_rm_number_layer',width:150">Part No Layer</th>
            <th data-options="field:'qty_usage',width:120,align:'right'">Qty Usage</th>
            <th data-options="field:'qty_packing_standart',width:150,align:'right'">Qty Packing Std</th>
            <th data-options="field:'price_layer',width:120,align:'right'">Price Layer</th>
            <th data-options="field:'adjustment',width:120,align:'right'">Adj %</th>
            <th data-options="field:'price_adjustment',width:140,align:'right'">Adj Price</th>
            <th data-options="field:'price_part',width:140,align:'right'">Price/Part</th>

            <!-- POLYBAG -->
            <th data-options="field:'polybag_size_1',width:140">Polybag 1</th>
            <th data-options="field:'qty_polybag_1',width:140,align:'right'">Qty PB 1</th>
            <th data-options="field:'price_pcs_1',width:140,align:'right'">Price PCS 1</th>
            <th data-options="field:'price_part_1',width:140,align:'right'">Price/Part 1</th>

            <th data-options="field:'polybag_size_2',width:140">Polybag 2</th>
            <th data-options="field:'qty_polybag_2',width:140,align:'right'">Qty PB 2</th>
            <th data-options="field:'price_pcs_2',width:140,align:'right'">Price PCS 2</th>
            <th data-options="field:'price_part_2',width:140,align:'right'">Price/Part 2</th>

            <!-- FOAM -->
            <th data-options="field:'item_rm_number_foam',width:150">Part No Foam</th>
            <th data-options="field:'qty_foam',width:120,align:'right'">Qty Foam</th>
            <th data-options="field:'price_foam',width:120,align:'right'">Price Foam</th>
            <th data-options="field:'adjustment_foam',width:120,align:'right'">Adj %</th>
            <th data-options="field:'price_adjustment_foam',width:150,align:'right'">Adj Price</th>
            <th data-options="field:'price_part_foam',width:140,align:'right'">Price/Part</th>

            <!-- TAPE -->
            <th data-options="field:'item_rm_number_tape',width:150">Part No Tape</th>
            <th data-options="field:'length',width:120,align:'right'">Length</th>
            <th data-options="field:'qty_tape',width:120,align:'right'">Qty Tape</th>
            <th data-options="field:'price_tape',width:120,align:'right'">Price Tape</th>
            <th data-options="field:'adjustment_tape',width:120,align:'right'">Adj %</th>
            <th data-options="field:'price_adjustment_tape',width:150,align:'right'">Adj Price</th>
            <th data-options="field:'price_mm_tape',width:150,align:'right'">Price/MM</th>
            <th data-options="field:'price_part_tape',width:150,align:'right'">Price/Part</th>

            <!-- PACKING BOX -->
            <th data-options="field:'volume',width:120,align:'right'">Vol/M</th>
            <th data-options="field:'need_part_day',width:140,align:'right'">Need Part/Day</th>
            <th data-options="field:'need_box_day',width:140,align:'right'">Need Box/Day</th>
            <th data-options="field:'storage_pos',width:120,align:'right'">Storage Pos</th>
            <th data-options="field:'need_pos_day',width:140,align:'right'">Need Pos</th>
            <th data-options="field:'storage_duration',width:140,align:'right'">Storage Dur</th>
            <th data-options="field:'storage_bpi_day',width:140,align:'right'">BPI/Day</th>
            <th data-options="field:'total_need_box',width:150,align:'right'">Total Box</th>
            <th data-options="field:'box_price',width:120,align:'right'">Price Box</th>
            <th data-options="field:'total_box_price',width:150,align:'right'">Total Box Price</th>
            <th data-options="field:'month',width:120,align:'right'">Month</th>
            <th data-options="field:'planning',width:150,align:'right'">Planning</th>
            <th data-options="field:'price_part_box',width:150,align:'right'">Price/Part</th>

            <!-- TOTAL -->
            <th data-options="field:'total_packing_cost',width:160,align:'right'">Total Packing Cost</th>

            <!-- BOX -->
            <th data-options="field:'box_name',width:180">Box Name</th>
            <th data-options="field:'color',width:180">Box Color</th>

            <!-- ARMADA -->
            <th data-options="field:'vehicle_name',width:180">Vehicle</th>
            <th data-options="field:'armada_cap_box',width:150,align:'right'">Cap Box</th>
            <th data-options="field:'armada_cap_pcs',width:150,align:'right'">Cap PCS</th>

            <!-- OPERATION -->
            <th data-options="field:'distance_astimation',width:150,align:'right'">Distance Astimation</th>
            <th data-options="field:'bbm_cost',width:150,align:'right'">BBM Cost</th>
            <th data-options="field:'tol_price',width:120,align:'right'">Tol Price</th>
            <th data-options="field:'operation',width:150,align:'right'">Operational Cost</th>
            <th data-options="field:'transportasion_cost_pcs',width:180,align:'right'"> Transportasion Cost/PCS</th>

            <!-- AUDIT -->
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
            <div title="Period" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Period</span>
                            <input style="width:30%;" name="p_month" id="p_month" required="" class="easyui-combobox">
                            <input style="width:30%;" name="p_year" id="p_year" required="" class="easyui-combobox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Revision</span>
                            <select style="width:60%;" name="revision" id="revision" required="" class="easyui-combobox" panelHeight="auto">
                                <option value="" selected disabled>Choose Revision</option>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div title="Product & Layer" style="padding:10px">
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
                            <span style="width:35%; display:inline-block;">Customer Id</span>
                            <input style="width:60%;" name="customer_id" id="customer_id" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Customer</span>
                            <input style="width:60%;" name="customer_name" id="customer_name" required="" class="easyui-combogrid">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Dimension Part</span>
                            <input style="width:60%;" name="dimension_part" id="dimension_part" class="easyui-numberbox">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="fitem" hidden>
                            <span style="width:35%; display:inline-block;">Part Id Layer</span>
                            <input style="width:60%;" name="item_rm_id_layer" id="item_rm_id_layer" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Part No Layer</span>
                            <input style="width:60%;" name="item_rm_number_layer" id="item_rm_number_layer" class="easyui-combobox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Qty Usage</span>
                            <input style="width:60%;" name="qty_usage" id="qty_usage" required="" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Packing Quantity Standart</span>
                            <input style="width:60%;" name="qty_packing_standart" id="qty_packing_standart" required="" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price</span>
                            <input style="width:60%;" name="price_layer" id="price_layer" class="easyui-numberbox" readonly>
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Adjustment %</span>
                            <input style="width:60%;" name="adjustment" id="adjustment" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price Adjustment</span>
                            <input style="width:60%;" name="price_adjustment" id="price_adjustment" readonly class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price/Part Layer</span>
                            <input style="width:60%;" name="price_part" id="price_part" readonly class="easyui-numberbox" precision="2">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Polybag" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Polybag Size 1</span>
                            <input style="width:60%;" name="polybag_size_1" id="polybag_size_1" class="easyui-combobox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Qty Part/Polybag 1</span>
                            <input style="width:60%;" name="qty_polybag_1" id="qty_polybag_1" required="" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price/Pcs Polybag 1</span>
                            <input style="width:60%;" name="price_pcs_1" id="price_pcs_1" readonly class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price/Part Polybag 1</span>
                            <input style="width:60%;" name="price_part_1" id="price_part_1" readonly class="easyui-numberbox" precision = "2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Polybag Size 2</span>
                            <input style="width:60%;" name="polybag_size_2" id="polybag_size_2" class="easyui-combobox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Qty Part/Polybag 2</span>
                            <input style="width:60%;" name="qty_polybag_2" id="qty_polybag_2" required="" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price/Pcs Polybag 2</span>
                            <input style="width:60%;" name="price_pcs_2" id="price_pcs_2" readonly class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price/Part Polybag 2</span>
                            <input style="width:60%;" name="price_part_2" id="price_part_2" readonly class="easyui-numberbox" precision = "2">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Foam" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem" hidden>
                            <span style="width:35%; display:inline-block;">Part Id Foam</span>
                            <input style="width:60%;" name="item_rm_id_foam" id="item_rm_id_foam" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Part No Foam</span>
                            <input style="width:60%;" name="item_rm_number_foam" id="item_rm_number_foam" class="easyui-combobox">
                        </div>
                            <div class="fitem">
                            <span style="width:35%; display:inline-block;">Qty Part/FoamBag</span>
                            <input style="width:60%;" name="qty_foam" id="qty_foam" required="" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price</span>
                            <input style="width:60%;" name="price_foam" id="price_foam" readonly class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Adjustment %</span>
                            <input style="width:60%;" name="adjustment_foam" id="adjustment_foam" required="" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price Adjustment Foam</span>
                            <input style="width:60%;" name="price_adjustment_foam" id="price_adjustment_foam" readonly class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price/Part Foam</span>
                            <input style="width:60%;" name="price_part_foam" id="price_part_foam" readonly class="easyui-numberbox" precision="2">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Tape" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem" hidden>
                            <span style="width:35%; display:inline-block;">Part Id Tape</span>
                            <input style="width:60%;" name="item_rm_id_tape" id="item_rm_id_tape" class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Part No Tape</span>
                            <input style="width:60%;" name="item_rm_number_tape" id="item_rm_number_tape" class="easyui-combobox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Length</span>
                            <input style="width:60%;" name="length" id="length" required="" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Qty Usage Tape</span>
                            <input style="width:60%;" name="qty_tape" id="qty_tape" required="" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price</span>
                            <input style="width:60%;" name="price_tape" id="price_tape" readonly class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Adjustment %</span>
                            <input style="width:60%;" name="adjustment_tape" id="adjustment_tape" required="" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price Adjustment Tape</span>
                            <input style="width:60%;" name="price_adjustment_tape" id="price_adjustment_tape" readonly class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price/mm Tape</span>
                            <input style="width:60%;" name="price_mm_tape" id="price_mm_tape" readonly class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price/Part Tape</span>
                            <input style="width:60%;" name="price_part_tape" id="price_part_tape" readonly class="easyui-numberbox" precision="2">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Packing Box" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Packing Box</span> 
                            <select style="width:60%;" name="packing_box" id="packing_box" required="" class="easyui-combobox" panelHeight="auto">
                                <option value="" selected disabled>Choose Packing Box</option>
                                <option value="Returnable Box">Returnable Box</option>
                                <option value="Carton Box">Carton Box</option>
                                <option value="Palet">Palet</option>
                            </select>
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Vol/M</span>
                            <input style="width:60%;" name="volume" id="volume" required="" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Need Part/Day</span>
                            <input style="width:60%;" name="need_part_day" id="need_part_day" readonly class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Need Box/Day</span>
                            <input style="width:60%;" name="need_box_day" id="need_box_day" readonly class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Storage Pos</span>
                            <input style="width:60%;" name="storage_pos" id="storage_pos" required="" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Need Pos/Day</span>
                            <input style="width:60%;" name="need_pos_day" id="need_pos_day" readonly class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Storage Duration</span>
                            <input style="width:60%;" name="storage_duration" id="storage_duration" required="" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Storage BPI/Day</span>
                            <input style="width:60%;" name="storage_bpi_day" id="storage_bpi_day" readonly class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Total Need Box</span>
                            <input style="width:60%;" name="total_need_box" id="total_need_box" readonly class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Box Price</span>
                            <input style="width:60%;" name="box_price" id="box_price" required="" class="easyui-numberbox" precision="3">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Palet Price</span>
                            <input style="width:60%;" name="palet_price" id="palet_price" class="easyui-numberbox" precision="3">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Mpq</span>
                            <input style="width:60%;" name="mpq_price" id="mpq_price" class="easyui-numberbox" precision="3">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Total Box Price</span>
                            <input style="width:60%;" name="total_box_price" id="total_box_price" readonly class="easyui-numberbox" precision="3">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Month</span>
                            <input style="width:60%;" name="month" id="month" required="" class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Planning 1,5 Year</span>
                            <input style="width:60%;" name="planning" id="planning" readonly class="easyui-numberbox" precision="3">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Price/Part</span>
                            <input style="width:60%;" name="price_part_box" id="price_part_box" readonly class="easyui-numberbox" precision="2">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Total Packing Cost" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Total Packing Cost</span>
                            <input style="width:60%;" name="total_packing_cost" id="total_packing_cost" readonly="" class="easyui-numberbox" precision="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Adjust Total Packing Cost</span>
                            <input style="width:60%;" name="adj_total_packing_cost" id="adj_total_packing_cost" class="easyui-numberbox" precision="2">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Box" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem" hidden>
                            <span style="width:35%; display:inline-block;">Box Id</span>
                            <input style="width:60%;" name="box_id" id="box_id" required class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Box Name</span>
                            <input style="width:60%;" name="box_name" id="box_name" required class="easyui-combogrid">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Code</span>
                            <input style="width:60%;" name="box_code" id="box_code" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Length</span>
                            <input style="width:60%;" name="box_length" id="box_length" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Width</span>
                            <input style="width:60%;" name="box_width" id="box_width" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Height</span>
                            <input style="width:60%;" name="box_height" id="box_height" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Color</span>
                            <input style="width:60%;" name="color" id="color" readonly class="easyui-textbox">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Armada" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem" hidden>
                            <span style="width:35%; display:inline-block;">Vehicle Id</span>
                            <input style="width:60%;" name="vehicle_id" id="vehicle_id" required class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Vehicle Name</span>
                            <input style="width:60%;" name="vehicle_name" id="vehicle_name" required class="easyui-combogrid">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Police No</span>
                            <input style="width:60%;" name="police_no" id="police_no" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Length</span>
                            <input style="width:60%;" name="vehicle_length" id="vehicle_length" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Width</span>
                            <input style="width:60%;" name="vehicle_width" id="vehicle_width" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Height</span>
                            <input style="width:60%;" name="vehicle_height" id="vehicle_height" readonly class="easyui-textbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Armada Cap (Box)</span>
                            <input style="width:60%;" name="armada_cap_box" id="armada_cap_box" readonly class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Armada Cap (Pcs)</span>
                            <input style="width:60%;" name="armada_cap_pcs" id="armada_cap_pcs" readonly class="easyui-numberbox">
                        </div>
                    </div>
                </div>
            </div>
            <div title="Operational cost" style="padding:10px">
                <div class="tab-wrap">
                    <div class="form-row">
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Distance Astimation</span>
                            <input style="width:60%;" name="distance_astimation" id="distance_astimation" required class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Operational & Transportation Cost Year</span>
                            <input style="width:60%;" name="op_trans_cost_year" id="op_trans_cost_year" required class="easyui-combogrid">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Fuel Consumption</span>
                            <input style="width:60%;" name="fuel_consumption_per_km" id="fuel_consumption_per_km" readonly class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">BBM Price</span>
                            <input style="width:60%;" name="bbm_price" id="bbm_price" readonly class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">BBM Cost</span>
                            <input style="width:60%;" name="bbm_cost" id="bbm_cost" readonly class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Rent Box Daily</span>
                            <input style="width:60%;" name="rent_daily" id="rent_daily" readonly class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">MP Cost Daily</span>
                            <input style="width:60%;" name="mp_cost_daily" id="mp_cost_daily" readonly class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Tol Price</span>
                            <input style="width:60%;" name="tol_price" id="tol_price" required="" class="easyui-numberbox" precision ="2">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Operational Cost</span>
                            <input style="width:60%;" name="operation" id="operation" readonly class="easyui-numberbox">
                        </div>
                        <div class="fitem">
                            <span style="width:35%; display:inline-block;">Transportasion Cost/Pcs</span>
                            <input style="width:60%;" name="transportasion_cost_pcs" id="transportasion_cost_pcs" readonly class="easyui-numberbox" precision="2">
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
<iframe id="printout" src="<?= base_url('pricing/packaging_transportation_costs/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('pricing/packaging_transportation_costs/create') ?>';
        $('#frm_insert').form('clear');

        $("#dimension_part").numberbox("setValue", 0);
        $("#qty_usage").numberbox("setValue", 0);
        $("#qty_packing_standart").numberbox("setValue", 0);
        $("#price_layer").numberbox("setValue", 0);
        $("#adjustment").numberbox("setValue", 0);
        $("#price_adjustment").numberbox("setValue", 0);
        $("#price_part").numberbox("setValue", 0);
        $("#qty_polybag_1").numberbox("setValue", 0);
        $("#price_pcs_1").numberbox("setValue", 0);
        $("#price_part_1").numberbox("setValue", 0);
        $("#qty_polybag_2").numberbox("setValue", 0);
        $("#price_pcs_2").numberbox("setValue", 0);
        $("#price_part_2").numberbox("setValue", 0);
        $("#qty_foam").numberbox("setValue", 0);
        $("#price_foam").numberbox("setValue", 0);
        $("#adjustment_foam").numberbox("setValue", 0);
        $("#price_adjustment_foam").numberbox("setValue", 0);
        $("#price_part_foam").numberbox("setValue", 0);
        $("#length").numberbox("setValue", 0);
        $("#qty_tape").numberbox("setValue", 0);
        $("#price_tape").numberbox("setValue", 0);
        $("#adjustment_tape").numberbox("setValue", 0);
        $("#price_adjustment_tape").numberbox("setValue", 0);
        $("#price_mm_tape").numberbox("setValue", 0);
        $("#price_part_tape").numberbox("setValue", 0);
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        console.log(row);

        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('pricing/packaging_transportation_costs/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('pricing/packaging_transportation_costs/delete') ?>',
                            data: {
                                customer_id: row.customer_id,
                                division_id: row.division_id,
                                customer_address_id: row.customer_address_id
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
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_item_fg_number = $("#filter_item_fg_number").combogrid('getValue');

        var url = "?filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_item_fg_id=" + window.btoa(filter_item_fg_id) + "&filter_item_fg_number=" + window.btoa(filter_item_fg_number);

        $('#dg').datagrid({
            url: '<?= base_url('pricing/packaging_transportation_costs/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('pricing/packaging_transportation_costs/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_customer_id = $("#filter_customer_id").combogrid('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_item_fg_number = $("#filter_item_fg_number").combogrid('getValue');

        var url = "?filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_item_fg_id=" + window.btoa(filter_item_fg_id) + "&filter_item_fg_number=" + window.btoa(filter_item_fg_number);

        window.location.assign('<?= base_url('pricing/packaging_transportation_costs/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //ADD DATA
        $('#dg').datagrid({
            url: '<?= base_url('pricing/packaging_transportation_costs/datatables') ?>',
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
        url: '<?= base_url('npd/item_fg_npd/reads'); ?>',
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
        }
    });

    $('#item_rm_number_layer').combobox({
        url: '<?= base_url('pricing/packaging_transportation_costs/readItems/'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: 'Choose Part No',
        onSelect: function(supp) {
            $('#item_rm_id_layer').textbox('setValue', supp.id);
            $('#price_layer').numberbox('setValue', supp.price);
            $('#price_adjustment').numberbox('setValue', supp.price);
        }
    });

    $('#item_rm_number_foam').combobox({
        url: '<?= base_url('pricing/packaging_transportation_costs/readItems/'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: 'Choose Part No',
        onSelect: function(supp) {
            $('#item_rm_id_foam').textbox('setValue', supp.id);
            $('#price_foam').numberbox('setValue', supp.price);
            $('#price_adjustment_foam').numberbox('setValue', supp.price);
        }
    });

    $('#item_rm_number_tape').combobox({
        url: '<?= base_url('pricing/packaging_transportation_costs/readItems/'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: 'Choose Part No',
        onSelect: function(supp) {
            $('#item_rm_id_tape').textbox('setValue', supp.id);
            $('#price_tape').numberbox('setValue', supp.price);
            $('#price_adjustment_tape').numberbox('setValue', supp.price);
        }
    });

    $('#polybag_size_1').combobox({
        url: '<?= base_url('master/polybag_prices/reads/'); ?>',
        valueField: 'size',
        textField: 'size',
        prompt: 'Choose Size',
        onSelect: function(row) {
            $('#price_pcs_1').numberbox('setValue', row.price_pcs);
            calculatePolybag(1);
        }
    });

    $('#polybag_size_2').combobox({
        url: '<?= base_url('master/polybag_prices/reads/'); ?>',
        valueField: 'size',
        textField: 'size',
        prompt: 'Choose Size',
        onSelect: function(row) {
            $('#price_pcs_2').numberbox('setValue', row.price_pcs);
            calculatePolybag(2);
        }
    });

    $('#vehicle_name').combogrid({
        url: '<?= base_url('master/vehicles/reads'); ?>',
        panelWidth: 600,
        idField: 'name',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: 'Choose Vehicles',
        columns: [
            [{
                field: 'name',
                title: 'Vehicle Name',
                width: 100
            }, {
                field: 'police_no',
                title: 'Police No',
                width: 80
            }, {
                field: 'dimension_p',
                title: 'Length',
                width: 100
            }, {
                field: 'dimension_l',
                title: 'Width',
                width: 100
            }, {
                field: 'dimension_t',
                title: 'Height',
                width: 100
            }]
        ],
        onSelect: function(value, rows) {
            $('#vehicle_id').textbox('setValue', rows.id);
            $('#police_no').textbox('setValue', rows.police_no);
            $('#vehicle_length').textbox('setValue', rows.dimension_p);
            $('#vehicle_width').textbox('setValue', rows.dimension_l);
            $('#vehicle_height').textbox('setValue', rows.dimension_t);

        }
    }); 

    $('#box_name').combogrid({
        url: '<?= base_url('master/item_boxs/reads'); ?>',
        panelWidth: 600,
        idField: 'name',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: 'Choose Box',
        columns: [
            [{
                field: 'name',
                title: 'Box Name',
                width: 120
            }, {
                field: 'code',
                title: 'Box Code',
                width: 80
            }, {
                field: 'length',
                title: 'Length',
                width: 100
            }, {
                field: 'width',
                title: 'Width',
                width: 100
            }, {
                field: 'height',
                title: 'Height',
                width: 100
             }, {
                field: 'color',
                title: 'Color',
                width: 100
            }]
        ],
        onSelect: function(value, rows) {
            $('#box_id').textbox('setValue', rows.id);
            $('#box_code').textbox('setValue', rows.code);
            $('#box_length').textbox('setValue', rows.length);
            $('#box_width').textbox('setValue', rows.width);
            $('#box_height').textbox('setValue', rows.height);
            $('#color').textbox('setValue', rows.color);
        }
    }); 

    $('#op_trans_cost_year').combogrid({
        url: '<?= base_url('master/op_trans_costs/reads'); ?>',
        panelWidth: 300,
        idField: 'year',
        textField: 'year',
        mode: 'remote',
        fitColumns: true,
        prompt: 'Choose Year',
        columns: [
            [{
                field: 'year',
                title: 'Year',
                width: 80
            }, {
                field: 'fuel_consumption_per_km',
                title: 'Fuel Consumption',
                width: 100
            }, {
                field: 'bbm_price',
                title: 'BBM Price',
                width: 100
            }]
        ],
        onSelect: function(value, rows) {
            $('#fuel_consumption_per_km').textbox('setValue', rows.fuel_consumption_per_km);
            $('#bbm_price').textbox('setValue', rows.bbm_price);
            $('#rent_daily').textbox('setValue', rows.rent_daily);
            $('#mp_cost_daily').textbox('setValue', rows.mp_cost_daily);
        }
    }); 

    $('#p_month').combobox({
        url: '<?= base_url('pricing/process_costs/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
    });

    $('#p_year').combobox({
        url: '<?= base_url('pricing/process_costs/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
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
            url: '<?= base_url('pricing/packaging_transportation_costs/datatableHistories?customer_id=') ?>' + btoa(customer_id) + "&item_fg_id=" + btoa(item_fg_id) + "&division_id=" + btoa(division_id) + "&customer_address_id=" + btoa(customer_address_id),
            pagination: false,
            rownumbers: true,
        });
    }

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('pricing/packaging_transportation_costs/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('pricing/packaging_transportation_costs/upload') ?>',
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
                            url: "<?= base_url('pricing/packaging_transportation_costs/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('pricing/packaging_transportation_costs/uploadCreate') ?>",
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
                                                url: "<?= base_url('pricing/packaging_transportation_costs/uploadcreateFailed') ?>",
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
</script>
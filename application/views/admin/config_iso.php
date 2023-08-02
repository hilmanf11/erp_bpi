<!-- UPDATE DATA -->
<div id="p" class="easyui-panel" title="Configuration" style="width:100%; padding:10px; background:#fafafa;" data-options="collapsible:false, maximizable:false">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <div style="width: 100%; float: left;">
            <fieldset style="width:30%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Document ISO</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request</span>
                    <input style="width:60%;" name="doc_purchase_request" value="<?= $config->doc_purchase_request ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Order</span>
                    <input style="width:60%;" name="doc_purchase_order" value="<?= $config->doc_purchase_order ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receiving Note</span>
                    <input style="width:60%;" name="doc_receiving_note" value="<?= $config->doc_receiving_note ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Job Order</span>
                    <input style="width:60%;" name="doc_job_order" value="<?= $config->doc_job_order ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supply Sheet</span>
                    <input style="width:60%;" name="doc_supply_sheet" value="<?= $config->doc_supply_sheet ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Check Sheet</span>
                    <input style="width:60%;" name="doc_checksheet" value="<?= $config->doc_checksheet ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order</span>
                    <input style="width:60%;" name="doc_delivery_order" value="<?= $config->doc_delivery_order ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Note</span>
                    <input style="width:60%;" name="doc_delivery_note" value="<?= $config->doc_delivery_note ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Invoice</span>
                    <input style="width:60%;" name="doc_sales_invoice" value="<?= $config->doc_sales_invoice ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Packing List</span>
                    <input style="width:60%;" name="doc_packing_list" value="<?= $config->doc_packing_list ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="doc_customer" value="<?= $config->doc_customer ?>" class="easyui-textbox">
                </div>
            </fieldset>
            <fieldset style="width:30%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Form ISO</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request</span>
                    <input style="width:60%;" name="form_purchase_request" value="<?= $config->form_purchase_request ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Order</span>
                    <input style="width:60%;" name="form_purchase_order" value="<?= $config->form_purchase_order ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receiving Note</span>
                    <input style="width:60%;" name="form_receiving_note" value="<?= $config->form_receiving_note ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Job Order</span>
                    <input style="width:60%;" name="form_job_order" value="<?= $config->form_job_order ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supply Sheet</span>
                    <input style="width:60%;" name="form_supply_sheet" value="<?= $config->form_supply_sheet ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Check Sheet</span>
                    <input style="width:60%;" name="form_checksheet" value="<?= $config->form_checksheet ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Order</span>
                    <input style="width:60%;" name="form_delivery_order" value="<?= $config->form_delivery_order ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Note</span>
                    <input style="width:60%;" name="form_delivery_note" value="<?= $config->form_delivery_note ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Invoice</span>
                    <input style="width:60%;" name="form_sales_invoice" value="<?= $config->form_sales_invoice ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Packing List</span>
                    <input style="width:60%;" name="form_packing_list" value="<?= $config->form_packing_list ?>" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="form_customer" value="<?= $config->form_customer ?>" class="easyui-textbox">
                </div>
            </fieldset>
        </div>
        <div style="width: 100%; float: left;">
            <a class="easyui-linkbutton c6" onclick="saved()" data-options="iconCls:'icon-save'">Save Changes</a>
        </div>
    </form>
</div>
<script>
    //Add Data
    function saved() {
        $('#frm_insert').form('submit', {
            url: '<?= base_url('admin/config_iso/update') ?>',
            method: 'POST',
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
            }
        });
    }
</script>
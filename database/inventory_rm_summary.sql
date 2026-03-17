CREATE TABLE `inventory_rm_summary` (
  `id` varchar(30) NOT NULL PRIMARY KEY,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,

  `number` VARCHAR(50) NOT NULL, -- Unik ID untuk satu kali proses simpan
  `cutoff_date` DATE NOT NULL,

  -- Filter & Meta Data
  `filter_from` DATE NOT NULL,
  `filter_to` DATE NOT NULL,
  `filter_items` VARCHAR(100) DEFAULT NULL,
  `filter_display` VARCHAR(20) DEFAULT NULL,
  `filter_division` VARCHAR(50) DEFAULT NULL,
  `filter_item_family` VARCHAR(50) DEFAULT NULL,
  `filter_item_category` VARCHAR(50) DEFAULT NULL,
  
  -- Summary Stock (QTY & Amount)
  `total_b_qty` DECIMAL(20,4) DEFAULT 0,
  `total_std_b` DECIMAL(20,4) DEFAULT 0,
  `total_act_b` DECIMAL(20,4) DEFAULT 0,
  
  `total_i_qty` DECIMAL(20,4) DEFAULT 0,
  `total_std_i` DECIMAL(20,4) DEFAULT 0,
  `total_act_i` DECIMAL(20,4) DEFAULT 0,
  
  `total_o_qty` DECIMAL(20,4) DEFAULT 0,
  `total_std_o` DECIMAL(20,4) DEFAULT 0,
  `total_act_o` DECIMAL(20,4) DEFAULT 0,
  
  `total_e_qty` DECIMAL(20,4) DEFAULT 0,
  `total_std_e` DECIMAL(20,4) DEFAULT 0,
  `total_act_e` DECIMAL(20,4) DEFAULT 0,

  -- Detail IN (Actual Amount)
  `total_receipt_qty` DECIMAL(20,4) DEFAULT 0,
  `total_std_purchase` DECIMAL(20,4) DEFAULT 0,
  `total_act_purchase` DECIMAL(20,4) DEFAULT 0,
  
  `total_bpm_qty` DECIMAL(20,4) DEFAULT 0,
  `total_std_bpm` DECIMAL(20,4) DEFAULT 0,
  `total_act_bpm` DECIMAL(20,4) DEFAULT 0,
  
  `total_adj_in_qty` DECIMAL(20,4) DEFAULT 0,
  `total_std_adjin` DECIMAL(20,4) DEFAULT 0,
  `total_act_adjin` DECIMAL(20,4) DEFAULT 0,

  -- Detail OUT (Actual Amount)
  `total_qty_supply_sheet` DECIMAL(20,4) DEFAULT 0,
  `total_std_supply` DECIMAL(20,4) DEFAULT 0,
  `total_act_supply` DECIMAL(20,4) DEFAULT 0,
  
  `total_qty_mat_request` DECIMAL(20,4) DEFAULT 0,
  `total_std_req` DECIMAL(20,4) DEFAULT 0,
  `total_act_req` DECIMAL(20,4) DEFAULT 0,
  
  `total_qty_kanban` DECIMAL(20,4) DEFAULT 0,
  `total_std_kanban` DECIMAL(20,4) DEFAULT 0,
  `total_act_kanban` DECIMAL(20,4) DEFAULT 0,
  
  `total_qty_kanban_sj` DECIMAL(20,4) DEFAULT 0,
  `total_std_kanban_sj` DECIMAL(20,4) DEFAULT 0,
  `total_act_kanban_sj` DECIMAL(20,4) DEFAULT 0,
  
  `total_qty_kanban_sp` DECIMAL(20,4) DEFAULT 0,
  `total_std_kanban_sp` DECIMAL(20,4) DEFAULT 0,
  `total_act_kanban_sp` DECIMAL(20,4) DEFAULT 0,
  
  `total_bpb_qty` DECIMAL(20,4) DEFAULT 0,
  `total_std_bpb` DECIMAL(20,4) DEFAULT 0,
  `total_act_bpb` DECIMAL(20,4) DEFAULT 0,
  
  `total_adj_out_qty` DECIMAL(20,4) DEFAULT 0,
  `total_std_adjout` DECIMAL(20,4) DEFAULT 0,
  `total_act_adjout` DECIMAL(20,4) DEFAULT 0,

  -- Posting Info
  `status` TINYINT(1) DEFAULT 0,
  
  INDEX (`number`, `cutoff_date`),
  INDEX (`filter_from`, `filter_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

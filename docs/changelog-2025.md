# 📂 Archive Update History - 2025

Halaman ini berisi rangkuman seluruh pembaruan sistem ERP selama tahun 2025.

---

## December 2025
* [2025-12-31] Feat: fixing query datatables ASSET Depreciation insert to Posting journal
* [2025-12-31] Fixing null query ASSET Posting Journal and dropdown Asset No Fixed Asset
* [2025-12-31] Fixing: fix dropdown get company name module ASSET on Posting Journal
* [2025-12-30] Fixing: fix dropdown get journal_type_id module ASSET on Posting Journal
* [2025-12-29] Fixing: optimasi query Fixed Asset filter date default periode yang sedang berjalan
* [2025-12-29] Fixing: add column Account Number on upload Fixed Asset and table
* [2025-12-29] Fixing: modul Fixed Asset get account_number item from PI
* [2025-12-26] Update Report_history_transactions.php
* [2025-12-18] Update issued_materials.php
* [2025-12-10] Update POR
* [2025-12-05] fixing: fix null account_name by division on Preview() Sales Invoices
* [2025-12-04] Update Generate_mps.php
* [2025-12-04] Update Sum Forecast
* [2025-12-04] Update Forecast
* [2025-12-04] fixing: fix division onChange get account_number in preview Sales Invoice
* [2025-12-04] fixing: fix total_vat get from table in print commercial Sales Invoices
* [2025-12-03] Update Purchase_report.php
* [2025-12-02] Update PI req Bu Nina atas Perubahan input PR , PO dan POR

## November 2025
* [2025-11-26] Update Purchase_report.php
* [2025-11-24] Update Item_fg.php
* [2025-11-20] fixing: fix bug value dropdown Account Statement
* [2025-11-20] fixing: fix bug value dropdown other liabilities Account Statement
* [2025-11-19] Update Receipt_crusher.php
* [2025-11-19] Update Customer item
* [2025-11-19] Update Warehouse_transferred.php
* [2025-11-19] New Module Upload stock whs trans
* [2025-11-19] New Module Trans other component
* [2025-11-19] New Module Trans Boxs
* [2025-11-19] New Module Scan DN Crusher
* [2025-11-19] New Module Report history trans other component
* [2025-11-19] New Module Report Historical Trans Boxs
* [2025-11-19] New Module Receipt Crusher
* [2025-11-19] New Module DN Scrap
* [2025-11-19] New Module DN Crusher
* [2025-11-19] New Modul DN Boxa
* [2025-11-19] Update Scan_rm_transfer.php
* [2025-11-19] Update Report_history_transactions_fg.php
* [2025-11-19] Update Report_history_transactions.php
* [2025-11-19] Update Sales_report.php
* [2025-11-14] fixing: fix query and get supplier balance AP Aging
* [2025-11-14] fixing: fix calculate debit/credit accumulation Report AP (seperti zoom Bu Nina)
* [2025-11-14] feat: fixing query get AP Aging Report
* [2025-11-13] chore: match fixed script Multi Account on Customers and Sales Invoices
* [2025-11-13] fixing: remove validation checkAccountBalance Sales Invoices
* [2025-11-13] fixing: filter pi_summary invoices and filter status Report AP
* [2025-11-13] fixing: fix validasi debit value on status closed and rate Report AP
* [2025-11-13] fixing: fix query filter_invoice_no and add column payment_no Report AP
* [2025-11-12] fixing: fix validation diff balance Sales Invoices
* [2025-11-12] fixing: fix calculateJournal Sales Invoices
* [2025-11-12] fixing: fix validasi result jika gagal create Sales Invoices
* [2025-11-12] fixing: get preview data account number customer by division Sales Invoices
* [2025-11-11] feat: modif format Product Name eCoretax in Sales Invoices
* [2025-11-10] fixing: fix query PI has paid in AP, debit nominal, and status closed Report AP
* [2025-11-10] fixing: autofill Journal Type by Division and Customer Name in Sales Invoices
* [2025-11-07] fixing: fix excel bug huruf hijaiyah Master export Item RM (Bu Septi)
* [2025-11-07] fixing: fix separator format nominal Report Trial Balance
* [2025-11-07] fixing: fix bug journal_type_id on update AP Payments
* [2025-11-07] fixing: fix bug GL No auto posting journal via AP Payments
* [2025-11-05] fixing: fix query balance and filter currency Report AP
* [2025-11-05] fixing: fix bug null data Upload Item RM and folder uploads live (Request Bu Septi)
* [2025-11-05] fixing: fix division default INJ Multi Account Numbers in Customers
* [2025-11-05] fixing: fix flag create Multi Account Numbers in Customers
* [2025-11-05] fixing: fix get and create Multi Account Numbers in Customers
* [2025-11-05] fixing: fix query filter_status open/closed Report AR
* [2025-11-03] feat: add multiple Account Numbers in Customers and fix delete
* [2025-11-03] fixing: fix calculations on print summary and details Sales Invoices

## October 2025
* [2025-10-31] fixing: takeout validation cannot update if Account COA is used in other tables
* [2025-10-31] fixing: fix update AP Payment without PI and hide deleteOnUncheck PI
* [2025-10-31] fixing: fix multiple PI in update AP Payment (uploaded to live)
* [2025-10-31] fixing: fix bug logic status payment and filter_status Report AR
* [2025-10-31] fixing: fix bug filter currency and amount Report AR
* [2025-10-30] chore: fixing local credit debit get datatablesTemp AR Receipt in Journal Posting
* [2025-10-29] Update Customers.php
* [2025-10-29] chore: match script Report AP add print_existing
* [2025-10-29] Update Os_mpp.php
* [2025-10-29] Update Output_productions.php
* [2025-10-28] Update Os_mpp.php
* [2025-10-28] Update Boxs
* [2025-10-28] Update repair_of_goods.php
* [2025-10-28] New Module Inventory Report
* [2025-10-27] fixing: fix bug upload Sales Invoices get journals
* [2025-10-27] Checksheet Update
* [2025-10-27] Update Report_meiruka.php
* [2025-10-27] New Modul Scan item Receipt Crusher
* [2025-10-27] fixing: fix bug upload Sales Invoices create account journal diff with setup
* [2025-10-24] fixing: fix filter status null, open, closed in Report AP
* [2025-10-24] fixing: fix bug Report AP transactions not show case Woojin (Bu Nina)
* [2025-10-23] fixing: fix format nominal print and export excel Report AR
* [2025-10-23] fixing: fix query Report AR and filters
* [2025-10-23] fixing: fix bug e-Coretax htmlspecialchars npwp and item_name
* [2025-10-22] fixing: special chars and symbol upload Master Item RM dengan PhpSpreadsheet
* [2025-10-22] fixing: fix remove special character upload Master Item RM
* [2025-10-22] fixing: fix bug ketika tanggal journal pada posting_no, document_no, invoice_no berbeda dengan periode Report AP
* [2025-10-22] fixing: fix dropdown filter document_no, posting_no, invoice_no in Report AP
* [2025-10-21] fixing: fix filter currency get Summary and Detail Report AP
* [2025-10-21] Update delivery_orders.php
* [2025-10-21] fixing: fix query get data tampil PI AP di Report AP
* [2025-10-20] fixing: fix number_format export excel without separator in Posting Journals
* [2025-10-20] fixing: fix Accumulation Depreciation Fixed Asset and Generate Asset Depreciation
* [2025-10-20] fixing: fix Generate Asset Depreciation by category Fixed Assets
* [2025-10-20] fixing: fix dropdown hasil review Asset Category
* [2025-10-17] fixing: fix calculation accumulation depreciation with asset journals in Fixed Asset
* [2025-10-17] feat: fix Asset Categories by Product Family and Journal Types
* [2025-10-16] fixing: fix remove null document_no and balance=0 in Report AP Report AR
* [2025-10-16] feat: fix update status report AP AR in Account CoA
* [2025-10-16] fixing: fix Report AP Report AR status account_number in Account CoA
* [2025-10-16] fixing: fix decimal point and separator numeric Report AP Report AR
* [2025-10-15] feat: fixing query Report AR Receipt
* [2025-10-15] feat: fix combogrid currency in Report AP Payment
* [2025-10-15] feat: fixing query Report AP Payment
* [2025-10-15] feat: fixing query Report COGS (Cost of Good Sold)
* [2025-10-15] chore: fix dropdown filter category Fixed Asset and progress bar Asset Depreciation
* [2025-10-14] feat: modul Asset Depreciation and fixing calc Accumulation Depreciation
* [2025-10-14] chore: match script upload AP Payments and PI with live server
* [2025-10-14] chore: match script upload Purchase Invoices with live server
* [2025-10-14] fixing: fix validasi dan variable Upload Sales Invoices
* [2025-10-13] feat: add Upload template Sales Invoices
* [2025-10-13] fixing: action New/Update Upload Purchase Invoices, AP Payments, AR Receipts
* [2025-10-13] fixing: fix Upload Purchase Invoices and fix btn Details
* [2025-10-13] fixing: fix Upload AP Payments and AR Receipts
* [2025-10-10] fixing: validate Payment via Form and Action Upload AP Payments
* [2025-10-10] chore: fix match script PI, AP Payments with dummy server
* [2025-10-10] fixing: export excel and filter financial period print Fixed Assets
* [2025-10-10] fixing: fix javascript Upload PI, AP Payments, SI, AR Receipts
* [2025-10-09] feat: fix upload calculate journal flag AP Payment dan flag AR Receipt
* [2025-10-09] feat: fix upload get journal AR Receipts
* [2025-10-09] fixing: btn calculate Depreciation and fix filter Fixed Asset
* [2025-10-09] Update Checksheet
* [2025-10-09] Update Supplier Items
* [2025-10-09] Update Transaction RM
* [2025-10-09] Update Report_history_transactions.php
* [2025-10-09] Update Report_outstanding_po.php
* [2025-10-09] Update item_rm.php
* [2025-10-08] feat: validasi Uom MTR create dan fixing field table Fixed Assets
* [2025-10-07] feat: fix template excel upload AP Payments
* [2025-10-07] feat: fix template excel upload Purchase Invoice, AP Payments, AR Receipts
* [2025-10-07] fixing: fix Upload Fixed Asset bug character UTF-8
* [2025-10-07] Update Inventory_wip.php
* [2025-10-07] fixing: fix dropdown read product hanya tampil Fixed Asset
* [2025-10-07] fixing: fix function and javascript Add new Fixed Asset
* [2025-10-06] fixing: fix javascript and prepare data Upload Fixed Assets
* [2025-10-06] fixing: fix validasi type, account_type, etc in Upload Purchase Invoice
* [2025-10-06] chore: fix whitespace every detail in export excel AP Payments
* [2025-10-06] feat: modifikasi Upload AP Payments multiple PI
* [2025-10-03] fixing: fix bug debit, credit, begin balance in report Trial Balance
* [2025-10-02] fixing: filter get datatables left join journal_types in Journal Posting
* [2025-10-02] fixing: fix bug nominal in export Excel Journal Posting
* [2025-10-02] fixing: fix bug Hyperlink GL Journal Posting
* [2025-10-01] fixing: fix update Account CoA account_group_detail_id and name
* [2025-10-01] Update PR & POR
* [2025-10-01] Update Supplier_items
* [2025-10-01] Update Item Rm
* [2025-10-01] chore: fixing failed txt name Purchase Invoices
* [2025-10-01] fixing: fix update Account CoA different id between datagrid and form

## September 2025
* [2025-09-30] Update delivery_orders.php
* [2025-09-30] Update delivery_notes.php
* [2025-09-30] create Upload Purchase Invoice with get POR
* [2025-09-29] Update Delivery_notes.php
* [2025-09-29] Update Item_fg.php
* [2025-09-29] Update Trans FG
* [2025-09-29] Update Item_fg_subs.php
* [2025-09-29] Update Progress_wip.php
* [2025-09-29] Update Report_history_transactions.php
* [2025-09-29] Update supply_materials.php
* [2025-09-26] feat: fix Asset No in auto-insert Fixed Asset via create Purchase Invoices
* [2025-09-26] feat: fix update data Fixed Asset error estimate_year
* [2025-09-26] feat: fix auto-increment Asset No in Fixed Asset input manual
* [2025-09-25] fixing: rename asset category to asset family in Fixed Assets
* [2025-09-25] Update POR
* [2025-09-25] fixing: validasi input DN manual in Create Sales Invoices
* [2025-09-25] fixing: fix export eCoretax mapping item_name in Sales Invoices
* [2025-09-25] chore: fix view sales invoices to match with live server
* [2025-09-25] Update Item_equivalent.php
* [2025-09-25] feat: add filter faktur_no and fixing exports excel in Sales Invoices
* [2025-09-25] Update Bom.php
* [2025-09-25] Update Checksheets.php
* [2025-09-25] feat: show column faktur_no on datagrid Sales Invoices
* [2025-09-24] fixing: fix eCoretax format HS Code get first 4 digit for Code in Sales Invoices
* [2025-09-24] fixing: fix eCoretax format HS Code in Sales Invoices
* [2025-09-24] fixing: fix eCoretax format HS Code inside tag Code in Sales Invoices
* [2025-09-24] Update Checksheets.php
* [2025-09-23] Update Checksheets.php
* [2025-09-23] fixing: fix eCoretax format NPWP dan IDTKU 0 and item_name with HS Code in Sales Invoices
* [2025-09-23] fixing: fix export Excel parse data url-encoded and Clear Failed Upload in Master Item RM
* [2025-09-23] fixing: fix query get print export excel (same as datatables and reads) in Purchase Invoices
* [2025-09-23] fixing: fixing parse json javascript and function prepare data json upload in Master Item RM
* [2025-09-22] fixing: fixing validate update when isset special chars and symbol in uploadCreate modul Master Item RM
* [2025-09-22] fixing: fixing uploadCreate Master Item RM and output excel special chars and symbol
* [2025-09-22] Req Bu Nina
* [2025-09-20] Update Item_receipts_fg.php
* [2025-09-20] Update Issued_materials.php
* [2025-09-20] Update supplier_items.php
* [2025-09-20] Update Material Requestion
* [2025-09-16] Update Purchase_orders.php
* [2025-09-15] fixing: bug account subtotal and grand total in output to excel Purchase Invoices
* [2025-09-15] feat: fix bug float decimal length etc uploadCreate in modul Master Item (RM)
* [2025-09-15] feat: enhancement add column ACTION on template to update or create via Upload Master Item (RM)
* [2025-09-15] feat: add status active/inactive on Module Category dan Product Family
* [2025-09-15] Update PR
* [2025-09-15] fixing: fix get npwp config export xml eCoretax in Sales Invoices
* [2025-09-15] fixing: include account VAT IN 170.170.00 dan 210.120.00 ke Journal List Purchase Invoices
* [2025-09-15] Update Output_productions.php
* [2025-09-12] Update Customer_items.php
* [2025-09-11] Update Delivery_notes.php
* [2025-09-10] fixing: fix format XML tag export eCoretax in Sales Invoices and match script Item FG with live server
* [2025-09-10] feat: fixing update Fixed Assets via trigger update() Purchase Invoices
* [2025-09-10] feat: add validation auto-create or update Fixed Assets via create Purchase Invoices
* [2025-09-10] chore: fixing function Update duplicate data in modul Exchange Rate
* [2025-09-10] feat: fixing flow auto-insert Fixed Asset via Purchase Invoices
* [2025-09-09] feat: add column Useful Life of Asset (Years) to Product Family and validate Currency Fixed Asset in PI
* [2025-09-08] feat: auto-create Fixed Assets via create Purchase Invoices if category=fixed asset
* [2025-09-08] fixing: print_summary format decimal number after point based on currency in modul Sales Invoices
* [2025-09-08] feat: perbaikan colspan print invoice Summary jika faktur_code=07 modul Sales Invoices
* [2025-09-08] feat: tampil column "HS Code" dan print invoice jika faktur_code=07 modul Sales Invoices
* [2025-09-08] Update os_mpp
* [2025-09-08] Update Bom
* [2025-09-08] New Module Warehouse Transfer
* [2025-09-08] New Module Scan RM Transfer
* [2025-09-08] New Module Report Menu Loadings
* [2025-09-08] New Module Report Bom
* [2025-09-08] New Module Report Master Data Engineering
* [2025-09-08] New Module Makers
* [2025-09-04] chore: fixing filter search datatables Journal Types by account_number
* [2025-09-04] feat: fixing create new and get department in modul Fixed Assets
* [2025-09-04] feat: fixing print and datatables same get data list in modul Fixed Assets
* [2025-09-04] Update Supply Sheet - Merge branch 'main'
* [2025-09-04] feat: fixing filter get data list modul Fixed Assets
* [2025-09-04] Update Supply Sheet
* [2025-09-04] feat: fixing create and get list Fixed Assets
* [2025-09-03] fixing: fix bug validasi ales_order_no or sales_order_no_rm datatablesTemp in Sales Invoices live (Bu Nina)
* [2025-09-03] fixing: fix bug get price and amount by sales_order_no or sales_order_no_rm in Sales Invoices live (Bu Septi)
* [2025-09-03] feat: fixing Export Coretax to XML (existing using excel/csv) in modul Sales Invoices
* [2025-09-02] feat: enhancement export eCoretax tampil kolom "Period Dok Pendukung" dan "HS Code" di Sales Invoices
* [2025-09-02] feat: tampil column "HS Code" item_fg pada modul Delivery Orders and fix on print DN
* [2025-09-02] feat: rename controller and view Fixeds to Fixed_assets modul Fixed Assets
* [2025-09-02] fixing: fix calculate Gain-Loss 810.150.00 AP Payments and 810.140.00 AR Receipts
* [2025-09-02] Merge branch 'main' of https://github.com/hilmanf11/erp_bpi
* [2025-09-02] Update PO
* [2025-09-02] feat: tampil column "HS Code" item_fg pada modul Delivery Notes
* [2025-09-02] update sales order
* [2025-09-02] chore: match script controller and view modul Delivery Notes with Live server
* [2025-09-01] feat: bisa edit tanggal PI, SI, AP, AR ketika update jangan di lock / readonly (Bu Nina)

## August 2025
* [2025-08-29] feat: enhancement calculate Gain-Loss journal 810.190.00 Rate USD etc in modul AR Receipts
* [2025-08-29] fixing: perbaikan validasi tidak balance Save All data AP Payments
* [2025-08-28] fixing: perbaikan Journal Posting calculate Gain-Loss dan save Exchange Rate table AP Payment Journals
* [2025-08-28] Update item_ng
* [2025-08-28] Update POR
* [2025-08-28] fixing: perbaikan exchange_now=1 jika currency=IDR Gain-Loss Rate in modul AP Payment
* [2025-08-28] fixing: perbaikan Otomatis Update calculate Gain-Loss tanpa btn save 810.190.00 Rate in modul AP Payment
* [2025-08-27] fixing: perbaikan hasil review calculate Gain-Loss journal 810.190.00 Rate in modul AP Payment
* [2025-08-27] feat: fix PI reads json total_idr undefined and Journal Posting validate account 810.190.00 Gain-Loss
* [2025-08-27] feat: enhancement calculate Gain-Loss journal 810.190.00 Rate USD etc in modul AP Payment
* [2025-08-26] chore: match script controller Trial Balance with dummy and prod (feat hyperlink detail transactions)
* [2025-08-20] fixing: fix export excel gran total calculation on modul Purchase Invoices
* [2025-08-20] fixing: fix export excel calculate sub_total - discount on Purchase Invoices
* [2025-08-19] feat: fixing hyperlink function not duplicate on modul PI and SI, also fixing AP, AR, Journal Posting
* [2025-08-19] feat: add multiple hyperlink detail transaction GL on modul Trial Balance
* [2025-08-19] Update Purchase Order
* [2025-08-19] fixing: fix duplicate GL transaction and trans_date on Bank Statements
* [2025-08-19] fixing: fix replace/delete data when re-upload per period on New Module Bank Reconciliation
* [2025-08-18] feat: fix multiple hyperlink detail transaction GL on modul Sales Invoices
* [2025-08-18] feat: fix multiple hyperlink detail transaction GL on modul AR Receipts
* [2025-08-18] feat: add multiple hyperlink detail transaction GL on modul PI, AP, SI, AR, and fix Journal Posting
* [2025-08-18] fixing: fix hasil review modul Bank Reconciliation and General Ledgers based on Zoom Bu Nina
* [2025-08-18] feat: add hyperlink detail transaction GL posting journal on modul General Ledger
* [2025-08-18] Update Barcode_divides.php
* [2025-08-18] Update Output Productions
* [2025-08-18] Update Progress Wip
* [2025-08-18] Update Repair of goods
* [2025-08-15] fixing: fix dropdown POR and DN null on update Purchase Invoices and SI
* [2025-08-15] fixing: fix dropdown Delivery Notes null on update Sales Invoices
* [2025-08-15] fixing: fix create SI Input DN manual perbaikan 3.4 proses saat create SI tanpa tarik DN proses save data loading terus
* [2025-08-15] feat: mapping biaya admin in unmatched transaction bank on New Module Bank Reconciliation
* [2025-08-15] fixing: Bank Account on print etc based on review gmeet on New Module Bank Reconciliation
* [2025-08-15] New Report NG
* [2025-08-15] New Meiruka Board
* [2025-08-15] New Report Historical Wip RM
* [2025-08-15] Update Historical RM
* [2025-08-15] New Modul
* [2025-08-14] fixing: fix template and dropdown bank account hasil testing on New Module Bank Reconciliation #2
* [2025-08-14] fixing: fix template and dropdown bank account hasil testing on New Module Bank Reconciliation
* [2025-08-14] feat: validasi posting date different with period date on New Module Bank Reconciliation
* [2025-08-13] feat: fix format IDR balance transactions on New Module Bank Reconciliation
* [2025-08-13] feat: fix difference balance summary of bank and journal on New Module Bank Reconciliation
* [2025-08-13] feat: status recheck identical transaction and fix get data on New Module Bank Reconciliation
* [2025-08-12] feat: reconciliation process match bank mutation with journal on New Module Bank Reconciliation
* [2025-08-12] feat: fix library excel uploadCreate process New Module Bank Reconciliation
* [2025-08-11] feat: uploadCreate process and fixing javascript dlg_upload on New Module Bank Reconciliation
* [2025-08-11] fixing: fix create() Purchase Invoices tanpa POR error replaced PI number
* [2025-08-07] feat: resources controller view and template New Module Bank Reconciliation
* [2025-08-07] Update Checksheet
* [2025-08-07] Update Sales Invoice
* [2025-08-06] ConvertCurrency
* [2025-08-06] fixing: fix bug error convertCurrencyToWords di print Invoice (01 Agustus)
* [2025-08-05] Update PI export accurate
* [2025-08-04] WIP balance FG
* [2025-08-04] Modif SI req Bu Nina
* [2025-08-01] fixing: add validation if exist in another table do not delete or update Account CoA change ajax not using eval()
* [2025-08-01] feat: add fitur Fasilitas Edit untuk tambah/kurangi di modul AP Payments dan AR Receipts (Done 30 Juli)
* [2025-08-01] fixing: fix bug error convertCurrencyToWords di print Invoice modul Sales Invoices
* [2025-08-01] receipt fg
* [2025-08-01] Update Report_history_transactions_fg.php

## July 2025
* [2025-07-30] fixing: exit process delete on uncheck on modul Purchase and Sales Invoices
* [2025-07-30] fixing: fix dlg_detail value type modul Purchase Invoice
* [2025-07-30] fixing: fix bug create() when POR multi item and error on deleterow() modul Purchase Invoice
* [2025-07-29] feat: validasi tidak boleh Save All jika account_number null di modul PI, SI, AP, dan AR
* [2025-07-28] feat: custom List Journal dropdown currency otomatis terisi IDR dan masih bisa di edit pada modul PI, SI, AP, dan AR
* [2025-07-28] feat: rename modul Foreign Currencies to Currency Revaluation and fixing Modul Bank Statement
* [2025-07-28] chore: match script controller with live server on modul Purchase Invoices
* [2025-07-28] fixing: add setting on-off fitur Auto Posting Journal on modul Sales Invoices
* [2025-07-28] fixing: fix message delete on uncheck and merge script live on modul Sales Invoices
* [2025-07-28] fixing: fix redundant delete on uncheck and merge script live on modul Purchase Invoices
* [2025-07-25] Merge Update Repair of Goods to 'main'
* [2025-07-25] fixing: fix redundant delete on uncheck, merge script live, add setting on-off fitur Auto Posting Journal on modul Purchase Invoices
* [2025-07-25] Update Repair of Goods
* [2025-07-25] Merge script live server to 'main' modul AR Receipt
* [2025-07-25] fixing: upload merged script dgn live modul AR Receipts
* [2025-07-25] Modif Output production Req Bu Septi
* [2025-07-25] fixing: merge script dgn live dan add setting on-off fitur Auto Posting Journal on modul AR Receipts
* [2025-07-24] Update Config ISO
* [2025-07-24] Update Generate mps
* [2025-07-24] update pengambilan tmp views
* [2025-07-24] feat: add viewDetails Fasilitas View Jurnal modul AP Payments
* [2025-07-24] ganti nama tmp
* [2025-07-23] fixing: add setting on-off fitur Auto Posting Journal on modul AP Payments
* [2025-07-23] Update fitur Upload output production
* [2025-07-23] chore: fixing sort account_number not in account_coa and null account group in Trial Balances
* [2025-07-23] chore: enable dropdown Category on Update Account CoA
* [2025-07-23] Update Generate Mrp
* [2025-07-23] chore: match script controller and view with live server modul AP Payments (feature Pak Kurniawan)
* [2025-07-23] feat: add viewDetails Fasilitas View Jurnal modul AR Receipts
* [2025-07-22] fix: fixing calculate dan getData Begin Balance in modul Trial Balances
* [2025-07-22] feat: add dropdown Fasilitas Edit untuk tambah/kurangi POR on Purchase Invoices
* [2025-07-21] fix: bug not show discount dlg_insert in Sales Invoices
* [2025-07-21] feat: add viewDetails Fasilitas View Jurnal modul Purchase Invoices
* [2025-07-19] Update Journal Posting AP dan AR rate
* [2025-07-18] feat: fixing on checklist dropdown Fasilitas Edit Delivery Note on Sales Invoices
* [2025-07-18] feat: add onUncheck dropdown Fasilitas Edit untuk tambah/kurangi Delivery Note on Sales Invoices
* [2025-07-18] fix: fixing get generateData2 per account_number on Income Statements
* [2025-07-18] fix: fixing validate get generate data per account_number on Income Statements
* [2025-07-18] style: fixing form input style disabled on view details Fasilitas View Jurnal modul Sales Invoices
* [2025-07-17] feat: add button view details on datagrid Fasilitas View Jurnal modul Sales Invoices
* [2025-07-17] Item Receipt dan historical RM
* [2025-07-15] feat: filter display Detail and Yearly also add generate per account on report Balance Sheets
* [2025-07-15] PI req Bu Nina
* [2025-07-15] chore: match script controller and view with live server on modul Non Supply Sheets
* [2025-07-14] feat: add Sales Repair on Details and fixing generateData on Income Statements
* [2025-07-14] feat: add generate account also filter display Detail and Yearly on report Income Statement
* [2025-07-14] feat: add section Sales Repair on Income Statement report (Bu Nina)
* [2025-07-14] item _rm
* [2025-07-11] SI
* [2025-07-11] Update Report_history_transactions.php
* [2025-07-11] chore: match script local with live server modul Purchase Order Receipts
* [2025-07-11] fix: fixing filter dan sort status Outstanding PO on Purchase Requests
* [2025-07-11] fix: fixing foreach get data Outstanding PO on Purchase Requests
* [2025-07-11] fix: fixing add new Output Production dropdown items subcont type jasa
* [2025-07-11] chore: match script controller and view with live server modul Output Production
* [2025-07-10] Update Exchange_rates.php
* [2025-07-10] item ng
* [2025-07-10] fix: fixing os_qty on check status open Outstanding PO di Purchase Requests
* [2025-07-10] fix: check status open Outstanding PO dan qty item di Purchase Requests
* [2025-07-10] Modifikasi SO
* [2025-07-10] DO perubahan datatables temp
* [2025-07-09] feat: add field division datatables, create, update, print on Product Family
* [2025-07-09] feat: fixing order by supplier name on Purchase and Sales Report Summary
* [2025-07-09] feat: enhancement calculate amount per-divisions on Purchase Report Summary
* [2025-07-08] Modifikasi Modul Delivery Schedule FG dan PO
* [2025-07-08] feat: fixing query get item_fg and filter item_fg on Consumable Part
* [2025-07-08] feat: validation insert item status active and type indirect composition on Upload Consumable Part
* [2025-07-08] feat: fixing readItems dropdown and report name in Consumable Part
* [2025-07-08] feat: add category on print, export excel, and add Consumable Part
* [2025-07-08] feat: get FG-SA dan validasi tidak boleh ada Part No. yang sama pada add Consumable Part
* [2025-07-08] Update by Req
* [2025-07-08] feat: create new modul Consumable Part fitur create, update, dan upload
* [2025-07-07] fix: fixing query dan mapping get data Summary of Sales Report
* [2025-07-04] Penambahan filter status
* [2025-07-04] Update Report_history_transactions.php
* [2025-07-04] Update Progress_wip.php
* [2025-07-04] feat: create new modul Consumable Part fitur view, print, export excel, delete dan download template
* [2025-07-04] chore: match script controller and view with live production modul Bill of Materials
* [2025-07-04] feat: fixing nomor 0 menghilang saat export excel Item NG to format mso
* [2025-07-03] fix: bug reads data on update Purchase Invoices (Bu Nina)
* [2025-07-03] feat: mapping account_number on add to journal Purchase Invoice (Bu Nina)
* [2025-07-03] Merge branch 'main' of https://github.com/hilmanf11/erp_bpi
* [2025-07-03] Update Report_history_transactions.php
* [2025-07-03] feat: add column created_date and created_by on export excel Item NG
* [2025-07-03] chore: match script controller and view with live production modul Item NG
* [2025-07-03] Inventories
* [2025-07-02] Modifikasi report history RM dan FG
* [2025-07-02] chore: rollback match script with live to add feature auto generate journal posting Purchase Invoicing
* [2025-07-02] chore: match script view with live production modul Purchase Invoices
* [2025-07-02] chore: match script view with live production modul Config ISO
* [2025-07-02] chore: match script function lsb() with live production History Transaction RM
* [2025-07-02] Merge branch 'main' of https://github.com/hilmanf11/erp_bpi
* [2025-07-02] Perubahan Progress WIP Req Bu Septi
* [2025-07-02] feat: add nomor form historical RM di report History Transaction RM (Request Bu Septi)
* [2025-07-02] Merge branch 'main' of https://github.com/hilmanf11/erp_bpi
* [2025-07-02] Revisi Sales Invoice
* [2025-07-02] chore: match script with live production History Transaction RM

## June 2025
* [2025-06-30] feat: fix calculate percent on report Balance Sheets
* [2025-06-30] feat: fix number_format for excel and null validation on report Income Statement and Balance Sheets
* [2025-06-26] chore: add column VAT and PPH on Journal Setups for setting account_number
* [2025-06-25] feat: add total per segment RM, INJ, MTS, ADM on Sales Report
* [2025-06-25] feat: fixing loop export Excel and print multiple account_number on report General Ledger
* [2025-06-25] chore: fix number_format hapus separator (kecuali decimal) export Posting Journal
* [2025-06-24] fix: fixing grand total, optimasi query dan html report modul Trial Balances
* [2025-06-20] feat: add dropdown category and fix get account multiple on General Ledger
* [2025-06-19] feat: fix validation duplicate account number and name update Account CoA #2
* [2025-06-19] feat: fix validation duplicate account number and name update Account CoA
* [2025-06-19] feat: add validation duplicate account number and name on create and update Account CoA
* [2025-06-19] feat: fixing amount with exchange rate on Sales Report
* [2025-06-19] feat: add footer total debit-kredit, column status closing_journal, starting date, and fix excel on Account Coa (request Bu Nina)
* [2025-06-18] feat: fixing column type per division RM, INJ, MTS, and ADM amount on Sales Report
* [2025-06-18] fix: dropdown product family hanya muncul category finished good di Forecast Analysis
* [2025-06-18] feat: column type RM, INJ, MTS, and ADM on Sales Report
* [2025-06-17] fix: amount with exchange rate, revision latest ver, dan bgcolor qty 0 on Forecast Analysis
* [2025-06-16] feat: forecast analysis and forecast moving average reports (request Bu Septi)
* [2025-06-16] lib: match template from live server features
* [2025-06-13] fix: get item_rm and item_fg status active on add Bill of Material Lists (request Bu Septi)
* [2025-06-13] fix: validasi journals null, column account number dan name di Report General Ledgers
* [2025-06-13] fix: filter each column datatables di Account_coa dan Account_group_details
* [2025-06-13] fix: column account number dan name di Report General Ledgers
* [2025-06-12] chore: exclude CR on dropdown Product No modul Outstanding PO
* [2025-06-12] chore: query select case address_plant in controller Sales Orders
* [2025-06-10] feat: new modul Closing - Accounting Period and Lock Finance
* [2025-06-10] feat: auto generate Posting Journal on transaction SI and AR Receipts
* [2025-06-10] fix: validate VAT addToJournal and isSubmitting on PI (compared with live)
* [2025-06-09] feat: auto generate Posting Journal on transaction PI and AP Payments
* [2025-06-09] feat: create menu Amount Forecast Customer on Forecasting
* [2025-06-05] Penambahan Status dan Kondisi Price
* [2025-06-03] Merge branch 'main' of https://github.com/hilmanf11/erp_bpi
* [2025-06-03] Modifikasi SI
* [2025-06-02] feat: add column 'Qty WIP' on modal Add new Output_productions
* [2025-06-02] feat: create menu Return Report on Accounting and Finance Report
* [2025-06-02] Merge branch 'main' of https://github.com/hilmanf11/erp_bpi
* [2025-06-02] Perubahan 02/06/25 request Implementator

## May 2025
* [2025-05-30] Merge branch 'main' of https://github.com/hilmanf11/erp_bpi
* [2025-05-30] Delete duplicate Folder Controllers dan views
* [2025-05-28] Hapus controoler dan views
* [2025-05-26] update Local
* [2025-05-09] New Update

## April 2025
* [2025-04-30] New Update
* [2025-04-28] New Update
* [2025-04-25] New Update
* [2025-04-25] Refresh
* [2025-04-25] refresh
* [2025-04-25] copy dari Server ke Local


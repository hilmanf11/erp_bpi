<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevLog - ERP</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <style>
        /* Menyesuaikan TomSelect dengan style Tailwind */
        .ts-control {
            border-radius: 0.75rem !important; /* rounded-xl */
            padding: 0.625rem 1rem !important; /* py-2.5 px-4 */
            border-color: #e5e7eb !important; /* border-gray-200 */
        }
        .ts-wrapper.focus .ts-control {
            border-color: #4A5568 !important; /* ganti dengan warna docsify */
            box-shadow: none !important;
        }
    </style>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        docsify: '#42b983',
                        dark: '#34495e',
                        sidebar: '#fcfcfc',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        /* Smooth transition for sidebar and content */
        #sidebar-wrapper, #page-content-wrapper, #sidebar-show-desktop {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Custom scrollbar for sidebar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        /* DESKTOP LOGIC */
        @media (min-width: 768px) {
            #page-content-wrapper {
                margin-left: 280px;
            }
            
            /* Saat sidebar disembunyikan */
            body.sidebar-hidden #page-content-wrapper {
                margin-left: 0 !important;
            }
            body.sidebar-hidden #sidebar-wrapper {
                transform: translateX(-100%);
            }
            body.sidebar-hidden #sidebar-show-desktop {
                opacity: 1;
                pointer-events: auto;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gray-50 text-dark antialiased overflow-x-hidden">
    <?php 
        // Ambil ID module dari URL (?module=ID)
        $current_module = $this->input->get('module'); 
    ?>

    <header class="md:hidden flex items-center justify-between bg-white border-b px-4 py-3 fixed top-0 w-full z-50">
        <div class="flex items-center">
            <button id="sidebar-toggle" class="p-2 hover:bg-gray-100 rounded-lg mr-2 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>
            <span class="font-bold text-gray-800 tracking-tight text-sm">ERP <span class="text-docsify">DevLog</span></span>
        </div>
    </header>

    <div class="flex" id="wrapper">
        <aside id="sidebar-wrapper" class="fixed inset-y-0 left-0 z-40 w-[280px] bg-sidebar border-r transform -translate-x-full md:translate-x-0 overflow-y-auto custom-scrollbar">
            <div class="p-6 h-20 flex justify-between items-center border-b bg-white sticky top-0 z-10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-docsify rounded-lg flex items-center justify-center text-white font-bold">D</div>
                    <span class="text-xl font-bold tracking-tight text-gray-800">Developer <span class="text-docsify">Logs</span></span>
                </div>
                <button id="sidebar-toggle-desktop" class="hidden md:block text-gray-400 hover:text-docsify transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                </button>
            </div>

            <div class="p-4 mt-2">
                <button class="w-full flex items-center justify-center gap-2 py-2.5 bg-docsify text-white rounded-xl font-semibold shadow-sm shadow-green-100 hover:shadow-xl hover:-translate-y-0.5 transition active:scale-95" onclick="openModal()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Update
                </button>
            </div>

            <nav class="mt-2 px-3 space-y-1">
                <p class="px-4 py-2 text-[10px] font-bold uppercase text-gray-400 tracking-widest">Modules List</p>
                
                <div class="px-3 mb-4">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                        </span>
                        <input type="text" id="searchModule" class="w-full pl-9 pr-4 py-2 bg-gray-100 border-none rounded-xl text-xs focus:ring-2 focus:ring-docsify/20 outline-none transition" placeholder="Search module...">
                    </div>
                </div>

                <div id="moduleList">
                    <?php if (empty($current_module)): ?>
                        <a href="<?= base_url('developerlog'); ?>" class="flex items-center px-4 py-2.5 text-docsify bg-green-50 rounded-xl font-semibold border-l-4 border-docsify mb-1">
                            All Modules 
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('developerlog'); ?>" class="flex items-center px-4 py-2.5 text-docsify bg-green-50 rounded-xl font-semibold border-l-4 border-docsify mb-1">
                            Back to Home
                        </a>
                    <?php endif; ?>

                    <?php foreach($modules as $m): ?>
                        <?php 
                            // Cek apakah ID module ini sedang aktif
                            $is_active = ($current_module == $m->id);
                            $class = $is_active 
                                ? 'text-docsify bg-green-50 font-semibold border-l-4 border-docsify' 
                                : 'text-gray-500 hover:text-docsify hover:bg-gray-100';
                        ?>
                        <a href="<?= base_url('developerlog?module='.$m->id); ?>" 
                        class="module-item flex items-center px-4 py-2.5 rounded-xl transition group <?= $class ?>"
                        data-name="<?= strtolower($m->name) ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?= $is_active ? 'bg-docsify' : 'bg-gray-300' ?> mr-3 group-hover:bg-docsify"></span>
                            <span class="module-name"><?= $m->name ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div id="noModuleFound" class="hidden px-4 py-10 text-center">
                    <p class="text-xs text-gray-400 italic">Module not found.</p>
                </div>
            </nav>
        </aside>

        <main id="page-content-wrapper" class="w-full min-h-screen pt-16 md:pt-0 relative">
            
            <button id="sidebar-show-desktop" class="hidden md:flex fixed top-6 left-6 z-30 p-2.5 bg-white border border-gray-200 rounded-xl shadow-md text-docsify hover:bg-gray-50 opacity-0 pointer-events-none transform -translate-x-4 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>

            <div class="p-6 md:p-10 max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight md:text-4xl">
                            <?php if (empty($current_module)): ?>
                                ALL UPDATE LOGS
                            <?php else: ?>
                                UPDATE LOGS
                            <?php endif; ?>
                        </h1>
                        <p class="text-gray-500 mt-2">Dokumentasi teknis perubahan fitur ERP.</p>
                    </div>
                    <div class="flex items-center gap-3">

                        <?php if (!empty($current_module)): 
                            // Cari index berdasarkan ID, lalu ambil namanya
                            $key = array_search($current_module, array_column($modules, 'id'));
                            $active_name = ($key !== false) ? $modules[$key]->name : 'Unknown';
                        ?>
                        <span class="flex items-center gap-2 px-3 py-1 bg-red-50 text-red-600 rounded-full text-xs font-semibold border border-red-100">
                            <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                            Module : <?= $active_name ?>
                        </span>
                        <?php else: ?>
                            <span class="flex items-center gap-2 px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-semibold border border-blue-100">
                                <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                                All Modules
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100 text-[11px] font-bold uppercase text-gray-400 tracking-wider">
                                    <th class="px-6 py-4">Module</th>
                                    <th class="px-6 py-4">Technical Update</th>
                                    <th class="px-6 py-4 text-center">Developer</th>
                                    <th class="px-6 py-4 text-center">Date</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <?php if(!empty($feature_updates)): foreach($feature_updates as $log): ?>
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 uppercase">
                                            <?= $log->module_name ?> 
                                        </span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="text-gray-900 font-bold leading-tight"><?= $log->feature_name ?></div>
                                        <div class="text-gray-500 py-3">Changes: <?= $log->feature_detail ?></div>
                                        <div class="flex gap-2">
                                            <?php if($log->change_controller): ?>
                                                <span class="text-[10px] text-gray-400 border border-gray-100 px-1 py-0.5 rounded">Controller</span>
                                            <?php endif; ?>
                                            <?php if($log->change_view): ?>
                                                <span class="text-[10px] text-gray-400 border border-gray-100 px-1 py-0.5 rounded">View</span>
                                            <?php endif; ?>
                                            <?php if($log->change_db): ?>
                                                <span class="text-[10px] text-gray-400 border border-gray-100 px-1 py-0.5 rounded">DB Update</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center justify-center">
                                            <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-[10px] font-bold border border-white shadow-sm">
                                                <?= strtoupper(substr($log->feature_developer, 0, 2)) ?>
                                            </div>
                                            <span class="ml-2 text-xs font-medium text-gray-600"><?= $log->feature_developer ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <span class="font-mono text-gray-400 text-xs"><?= date('Y-m-d H:i', strtotime($log->created_at)) ?></span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <?php 
                                            $statusColor = [
                                                'local' => 'bg-blue-50 border-blue-500 text-blue-600',
                                                'dummy' => 'bg-yellow-50 border-yellow-500 text-yellow-600',
                                                'live'  => 'bg-green-50 border-green-500 text-green-600'
                                            ];
                                            $color = $statusColor[$log->status] ?? 'bg-gray-50 border-gray-500 text-gray-600';
                                        ?>
                                        <div class="text-center py-1 border rounded-xl text-[10px] font-bold uppercase <?= $color ?>">
                                            <?= $log->status ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <button type="button" 
                                                class="view-detail-btn w-full flex py-2 items-center justify-center bg-docsify text-white rounded-xl font-semibold hover:bg-green-600 transition" 
                                                data-id="<?= $log->id ?>">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-400 italic">No logs found.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 bg-gray-50/30 border-t border-gray-100 text-center">
                        <p class="text-xs text-gray-400 italic">Showing <?= count($feature_updates) ?> entries. Keep it up, Devs! 🚀</p>
                    </div>

                </div>
            </div>
        </main>
    </div>


    <!-- MODAL CREATE -->
    <div id="modal-create" class="fixed inset-0 z-[100] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300">
        <div class="absolute inset-0 bg-dark/40 backdrop-blur-sm"></div>
        
        <div class="relative bg-white w-full max-w-5xl mx-4 rounded-3xl shadow-2xl overflow-hidden transform scale-95 transition-all duration-300" id="modal-container">
            <div class="px-8 py-4 border-b flex justify-between items-center bg-white sticky top-0 z-10">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Add New Update</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Dokumentasikan perubahan fitur ERP.</p>
                </div>
                <button class="close-modal p-2 hover:bg-gray-100 rounded-full transition">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="<?= base_url('developerlog/save'); ?>" method="POST">
                <div class="flex flex-col md:flex-row divide-y md:divide-y-0 md:divide-x divide-gray-100">
                    
                    <div class="w-full md:w-5/12 p-8 space-y-6">
                        <h4 class="text-[11px] font-bold text-docsify uppercase tracking-widest">General Information</h4>
                        
                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Modul</label>
                                <select id="select-module" name="module_id" placeholder="Search Module..." autocomplete="off">
                                    <option value="">Search Module...</option>
                                    <?php foreach($modules as $m): ?>
                                        <option value="<?= $m->id ?>"> <?= $m->name ?> </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="block text-gray-400 mt-2">Module = Submenu on ERP</small>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Deployment Status</label>
                                <div class="flex gap-2">
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="status" value="local" class="peer hidden" checked>
                                        <div class="text-center py-2 border border-gray-100 rounded-xl text-[10px] font-bold uppercase peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:text-blue-600 hover:bg-gray-50 transition">Local</div>
                                    </label>
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="status" value="dummy" class="peer hidden">
                                        <div class="text-center py-2 border border-gray-100 rounded-xl text-[10px] font-bold uppercase peer-checked:bg-yellow-50 peer-checked:border-yellow-500 peer-checked:text-yellow-600 hover:bg-gray-50 transition">Dummy</div>
                                    </label>
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="status" value="live" class="peer hidden">
                                        <div class="text-center py-2 border border-gray-100 rounded-xl text-[10px] font-bold uppercase peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-600 hover:bg-gray-50 transition">Live</div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Feature or Task Name</label>
                                <input type="text" name="feature_name" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:border-docsify outline-none transition text-sm font-semibold text-gray-700">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Detail Feature or Task</label>
                                <textarea name="feature_detail" rows="5" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:border-docsify outline-none transition text-sm"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="w-full md:w-7/12 p-8 bg-gray-50/50">
                        <h4 class="text-[11px] font-bold text-docsify uppercase tracking-widest mb-6">Technical Changes</h4>
                        
                        <div class="space-y-5">                                
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">File Controller *</label>
                                <input type="text" name="change_controller" rows="2" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:border-docsify outline-none transition text-xs font-mono"></textarea>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">File View *</label>
                                <input type="text" name="change_view" rows="2" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:border-docsify outline-none transition text-xs font-mono"></textarea>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2 font-bold">Database Update (SQL/Migration)</label>
                                <textarea name="change_db" rows="4" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:border-docsify outline-none transition text-xs font-mono text-blue-600 shadow-inner" placeholder="Example: ALTER TABLE p_invoices ADD COLUMN is_closed.."></textarea>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Menu Structure Update</label>
                                <input type="text" name="change_menu" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:border-docsify outline-none transition text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Developer Name *</label>
                                <input type="text" name="feature_developer" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:border-docsify outline-none transition text-sm font-semibold text-gray-700">
                            </div>

                            <small class="block text-gray-400 mt-2">*&#41; Separate with comma for multiple value</small>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-4 border-t bg-white flex flex-col md:flex-row justify-end gap-3">
                    <button type="button" class="close-modal px-6 py-2.5 text-sm text-gray-500 font-semibold hover:bg-gray-100 rounded-xl transition">Cancel</button>
                    <button type="submit" class="px-8 py-2.5 bg-docsify text-white rounded-xl font-bold shadow-lg shadow-green-100 hover:shadow-xl hover:-translate-y-1 transition active:scale-95">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL SHOW -->
    <div id="modal-detail" class="fixed inset-0 z-[100] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300">
        <div class="absolute inset-0 bg-dark/40 backdrop-blur-sm"></div>
            <div class="relative bg-white w-full max-w-4xl mx-4 rounded-3xl shadow-2xl overflow-hidden transform scale-95 transition-all duration-300" id="detail-container">
                <div class="px-8 py-5 border-b flex justify-between items-center bg-gray-50">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Feature Detail</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Technical documentation overview.</p>
                    </div>
                    <button onclick="closeDetail()" class="p-2 hover:bg-gray-200 rounded-full transition">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-bold text-docsify uppercase tracking-widest mb-1">Feature Name</label>
                                <p id="det-feature-name" class="text-sm font-semibold text-gray-700 italic"></p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Description</label>
                                <p id="det-description" class="text-sm text-gray-600 leading-relaxed"></p>
                            </div>
                            <div class="flex gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Developer</label>
                                    <span id="det-developer" class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold"></span>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Status Server</label>
                                    <span id="det-status" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase"></span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 bg-gray-50 p-6 rounded-2xl border border-gray-100">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 text-xs">Module Path</label>
                                <p id="det-menu-path" class="text-sm font-semibold text-gray-700 italic"></p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 text-xs">Controller</label>
                                <code id="det-controller" class="block p-2 bg-white border rounded text-xs font-mono text-pink-600"></code>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 text-xs">View</label>
                                <code id="det-view" class="block p-2 bg-white border rounded text-xs font-mono text-blue-600"></code>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 text-xs">SQL / DB Changes</label>
                                <pre id="det-sql" class="p-3 bg-white rounded text-[11px] font-mono overflow-x-auto text-gray-900"></pre>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="px-8 py-4 border-t bg-gray-50 flex justify-end">
                    <button onclick="closeDetail()" class="px-8 py-2.5 bg-gray-800 text-white rounded-xl font-bold hover:bg-black transition">Close Window</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            const $body = $('body');
            const $sidebar = $('#sidebar-wrapper');

            function toggleSidebar(e) {
                if(e) e.preventDefault();
                
                if ($(window).width() < 768) {
                    // Mobile Toggle
                    $sidebar.toggleClass('-translate-x-full');
                } else {
                    // Desktop Toggle menggunakan class body
                    $body.toggleClass('sidebar-hidden');
                }
            }

            // Gabungkan semua tombol toggle
            $('#sidebar-toggle, #sidebar-toggle-desktop, #sidebar-show-desktop').on('click', toggleSidebar);

            // Auto-close di mobile saat link diklik
            $('nav a').on('click', function() {
                if ($(window).width() < 768) {
                    $sidebar.addClass('-translate-x-full');
                }
            });

            // Sinkronisasi saat resize
            $(window).resize(function() {
                if ($(window).width() >= 768) {
                    $sidebar.removeClass('-translate-x-full');
                } else {
                    $body.removeClass('sidebar-hidden');
                }
            });

            $('#searchModule').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                let hasResult = false;

                $('.module-item').each(function() {
                    const name = $(this).data('name');
                    if (name.includes(value)) {
                        $(this).show();
                        hasResult = true;
                    } else {
                        $(this).hide();
                    }
                });

                // Menangani tampilan jika hasil kosong
                if (hasResult) {
                    $('#noModuleFound').addClass('hidden');
                } else {
                    $('#noModuleFound').removeClass('hidden');
                }

                // "All Modules" selalu muncul jika input kosong
                if (value === "") {
                    $('#noModuleFound').addClass('hidden');
                }
            });
            
            // Fungsi Buka Modal
            window.openModal = function() {
                $('#modal-create').removeClass('opacity-0 pointer-events-none');
                $('#modal-container').removeClass('scale-95').addClass('scale-100');
                $('body').addClass('overflow-hidden'); // Disable scroll saat modal buka
            };

            // Fungsi Tutup Modal
            window.closeModal = function() {
                $('#modal-create').addClass('opacity-0 pointer-events-none');
                $('#modal-container').removeClass('scale-100').addClass('scale-95');
                $('body').removeClass('overflow-hidden');
            };

            // Event Listeners
            $('.close-modal').on('click', closeModal);
            
            // Tutup jika klik di luar modal container
            $('#modal-create').on('click', function(e) {
                if (e.target === this) closeModal();
            });
            
        });

        $(document).on('click', '.view-detail-btn', function() {
            const id = $(this).data('id');
            const $btn = $(this);
            const originalText = $btn.html();
            
            // Loading state di tombol
            $btn.html('...').prop('disabled', true);

            $.ajax({
                url: "<?= base_url('developerlog/get_detail/'); ?>" + id,
                type: "GET",
                dataType: "JSON",
                success: function(response) {
                    $btn.html(originalText).prop('disabled', false);

                    if(response.status === 'success') {
                        const d = response.data;
                        
                        // Masukkan data ke elemen modal detail
                        $('#det-feature-name').text(d.feature_name);
                        $('#det-menu-path').text(d.change_menu || d.module_name);
                        $('#det-description').text(d.feature_detail);
                        $('#det-developer').text(d.feature_developer);
                        $('#det-controller').text(d.change_controller || '-');
                        $('#det-view').text(d.change_view || '-');
                        $('#det-sql').text(d.change_db || '-- No database changes');
                        
                        // Styling Status Badge
                        const statusClasses = {
                            'local': 'bg-blue-100 text-blue-700',
                            'dummy': 'bg-yellow-100 text-yellow-700',
                            'live': 'bg-green-100 text-green-700'
                        };
                        $('#det-status').text(d.status).removeClass().addClass('inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase ' + (statusClasses[d.status] || 'bg-gray-100'));

                        openDetail();
                    }
                },
                error: function() {
                    $btn.html(originalText).prop('disabled', false);
                    alert('Gagal mengambil data.');
                }
            });
        });

        // Fungsi Toggle Modal Detail
        window.openDetail = function() {
            $('#modal-detail').removeClass('opacity-0 pointer-events-none');
            $('#detail-container').removeClass('scale-95').addClass('scale-100');
            $('body').addClass('overflow-hidden');
        };

        window.closeDetail = function() {
            $('#modal-detail').addClass('opacity-0 pointer-events-none');
            $('#detail-container').removeClass('scale-100').addClass('scale-95');
            $('body').removeClass('overflow-hidden');
        };
        
        new TomSelect("#select-module", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    </script>
</body>
</html>

<x-admin-layout>
    <x-slot name="header">Roles & Permissions</x-slot>

    @php
        $permissionIcons = [
            'admin.panel.dashboard' => 'layout-dashboard',
            'admin.panel.analytics' => 'bar-chart-3',
            'admin.panel.search' => 'search',
            'admin.panel.restaurants' => 'store',
            'admin.panel.restaurant_requests' => 'clipboard-check',
            'admin.panel.plans' => 'tags',
            'admin.panel.waiters' => 'users',
            'admin.panel.live_orders' => 'shopping-bag',
            'admin.panel.orders' => 'history',
            'admin.panel.customer_requests' => 'message-square',
            'admin.panel.payments' => 'credit-card',
            'admin.panel.withdrawals' => 'wallet',
            'admin.panel.tips' => 'coins',
            'admin.panel.payroll' => 'banknote',
            'admin.panel.reports' => 'line-chart',
            'admin.panel.landing_page' => 'file-text',
            'admin.panel.feedback' => 'star',
            'admin.panel.menus' => 'book-open',
            'admin.panel.notifications' => 'send',
            'admin.panel.impersonate' => 'user-cog',
            'admin.technical.docker' => 'container',
            'admin.technical.bots' => 'bot',
            'admin.technical.activity_log' => 'clock',
            'admin.technical.settings' => 'settings',
            'admin.technical.payment_integration' => 'smartphone',
            'admin.technical.fix_storage' => 'hard-drive',
        ];
    @endphp

    @push('styles')
    <style>
        .roles-shell .role-segment { transition: all 0.2s ease; }
        .roles-shell .role-segment[aria-selected="true"] { color: #fff; }
        .roles-shell .role-segment-admin[aria-selected="true"] {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.35), rgba(139, 92, 246, 0.2));
            box-shadow: inset 0 0 0 1px rgba(139, 92, 246, 0.35);
        }
        .roles-shell .role-segment-tech[aria-selected="true"] {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.3), rgba(56, 189, 248, 0.15));
            box-shadow: inset 0 0 0 1px rgba(56, 189, 248, 0.35);
        }
        .roles-shell .perm-row { transition: background 0.15s, border-color 0.15s; }
        .roles-shell .perm-row.is-on {
            background: rgba(139, 92, 246, 0.07);
            border-color: rgba(139, 92, 246, 0.25);
        }
        .roles-shell [data-role-panel="technical"] .perm-row.is-on {
            background: rgba(56, 189, 248, 0.07);
            border-color: rgba(56, 189, 248, 0.25);
        }
        .roles-shell .perm-row.is-hidden { display: none; }
        .roles-shell .perm-toggle { position: relative; width: 42px; height: 24px; flex-shrink: 0; display: block; }
        .roles-shell .perm-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
        .roles-shell .perm-toggle span {
            position: absolute; inset: 0; cursor: pointer; border-radius: 999px;
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1);
            transition: 0.2s ease;
        }
        .roles-shell .perm-toggle span:before {
            content: ""; position: absolute; height: 18px; width: 18px; left: 2px; bottom: 2px;
            background: #fff; border-radius: 50%; transition: 0.2s ease;
        }
        .roles-shell .perm-toggle input:checked + span { background: #8C71F6; border-color: transparent; }
        .roles-shell [data-role-panel="technical"] .perm-toggle input:checked + span { background: #0ea5e9; }
        .roles-shell .perm-toggle input:checked + span:before { transform: translateX(18px); }
        .roles-shell .role-panel { display: none; }
        .roles-shell .role-panel.is-active { display: block; }
        .roles-shell .progress-fill { transition: width 0.35s ease; }
        .roles-shell .save-bar { transform: translateY(calc(100% + 1.5rem)); transition: transform 0.3s ease; }
        .roles-shell .save-bar.is-visible { transform: translateY(0); }
    </style>
    @endpush

    {{-- Page intro (same pattern as Plans / Users) --}}
    <div class="roles-shell">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-6">
            <div class="min-w-0">
                <h2 class="text-2xl font-black text-white tracking-tight">Roles & Permissions</h2>
                <p class="text-sm text-white/45 mt-1 max-w-2xl">Choose which admin pages each role can open. Super admin always has full access.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                @foreach($roles as $role)
                    @php $summary = $roleSummaries[$role->name]; @endphp
                    <div class="glass rounded-xl px-4 py-2.5 border border-white/10 flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full {{ $role->name === 'technical' ? 'bg-sky-400' : 'bg-indigo-400' }}"></span>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-white/35">{{ $summary['label'] }}</p>
                            <p class="text-sm font-black text-white tabular-nums">{{ $userCountsByRole[$role->name] ?? 0 }} <span class="text-[10px] font-semibold text-white/40">users</span></p>
                        </div>
                    </div>
                @endforeach
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-cyan-600 text-white text-sm font-bold hover:shadow-lg hover:shadow-violet-500/20 transition-all">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    Manage users
                </a>
            </div>
        </div>

        <div id="roles-editor"
             data-update-url="{{ url('/admin/api/roles') }}"
             data-reset-url="{{ url('/admin/api/roles') }}"
             data-csrf="{{ csrf_token() }}">

            <div class="glass-card rounded-2xl border border-white/10 overflow-hidden">
                {{-- Tabs + primary actions --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 border-b border-white/5 bg-white/[0.02]">
                    <div class="flex p-1 rounded-xl bg-black/20 border border-white/10 w-full sm:w-auto" role="tablist">
                        @foreach($roles as $index => $role)
                            @php
                                $summary = $roleSummaries[$role->name];
                                $isTech = $role->name === 'technical';
                            @endphp
                            <button type="button"
                                    role="tab"
                                    data-role-tab="{{ $role->name }}"
                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                    class="role-segment {{ $isTech ? 'role-segment-tech' : 'role-segment-admin' }} flex-1 sm:flex-none px-5 py-2.5 rounded-lg text-sm font-bold text-white/50 whitespace-nowrap">
                                {{ $summary['label'] }}
                            </button>
                        @endforeach
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button type="button" data-reset-active
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl glass border border-white/10 text-xs font-bold text-white/70 hover:bg-white/10">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                            Reset
                        </button>
                        <button type="button" data-save-active
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-cyan-600 text-xs font-bold text-white hover:shadow-lg hover:shadow-violet-500/20">
                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                            Save changes
                        </button>
                    </div>
                </div>

                @foreach($roles as $index => $role)
                    @php
                        $summary = $roleSummaries[$role->name];
                        $assigned = $role->permissions->pluck('name')->all();
                        $groups = $role->name === 'technical' ? $technicalGroups : $panelGroups;
                        $pct = $summary['total'] > 0 ? round(($summary['assigned'] / $summary['total']) * 100) : 0;
                        $isTech = $role->name === 'technical';
                    @endphp

                    <div class="role-panel {{ $index === 0 ? 'is-active' : '' }}"
                         data-role-panel="{{ $role->name }}"
                         role="tabpanel">

                        {{-- Role summary strip --}}
                        <div class="px-5 py-5 lg:px-6 border-b border-white/5 space-y-4">
                            <div class="flex flex-col lg:flex-row lg:items-center gap-4 lg:gap-8">
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-white/35 mb-1">{{ $isTech ? 'System tools' : 'Operations' }}</p>
                                    <p class="text-sm text-white/55 leading-relaxed">{{ $summary['description'] }}</p>
                                </div>
                                <div class="flex items-center gap-4 shrink-0">
                                    <div class="text-right">
                                        <p class="text-2xl font-black text-white tabular-nums" data-enabled-count="{{ $role->name }}">{{ $summary['assigned'] }}</p>
                                        <p class="text-[10px] font-bold text-white/35 uppercase tracking-wider">of {{ $summary['total'] }} pages</p>
                                    </div>
                                    <div class="w-px h-10 bg-white/10 hidden sm:block"></div>
                                    <p class="text-sm font-black tabular-nums {{ $isTech ? 'text-sky-300' : 'text-violet-300' }}" data-progress-label="{{ $role->name }}">{{ $pct }}%</p>
                                </div>
                            </div>

                            <div class="h-1.5 rounded-full bg-white/5 overflow-hidden">
                                <div class="progress-fill h-full rounded-full bg-gradient-to-r {{ $isTech ? 'from-sky-500 to-cyan-400' : 'from-violet-600 to-indigo-400' }}"
                                     style="width: {{ $pct }}%"
                                     data-progress-bar="{{ $role->name }}"></div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3">
                                <div class="relative flex-1">
                                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-white/30 pointer-events-none"></i>
                                    <input type="search"
                                           placeholder="Search page name…"
                                           data-perm-search="{{ $role->name }}"
                                           class="w-full pl-10 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-white/30 focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                                </div>
                                <div class="flex gap-2 shrink-0">
                                    <button type="button" data-select-all="{{ $role->name }}"
                                            class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-xs font-bold text-white/70 bg-white/5 border border-white/10 hover:bg-white/10">
                                        Enable all
                                    </button>
                                    <button type="button" data-select-none="{{ $role->name }}"
                                            class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-xs font-bold text-white/70 bg-white/5 border border-white/10 hover:bg-white/10">
                                        Disable all
                                    </button>
                                </div>
                            </div>

                            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-400/90 hidden" data-dirty-label="{{ $role->name }}">
                                • Unsaved changes
                            </p>
                        </div>

                        {{-- Permission groups --}}
                        <div class="divide-y divide-white/5">
                            @foreach($groups as $groupName => $permissions)
                                <section class="p-5 lg:p-6" data-perm-group="{{ $role->name }}-{{ Str::slug($groupName) }}">
                                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                                        <div>
                                            <h3 class="text-sm font-black text-white">{{ $groupName }}</h3>
                                            <p class="text-[10px] text-white/35 mt-0.5">
                                                <span data-group-count="{{ $role->name }}-{{ Str::slug($groupName) }}">0</span> / {{ $permissions->count() }} enabled
                                            </p>
                                        </div>
                                        <button type="button"
                                                data-group-all="{{ $role->name }}"
                                                data-group-slug="{{ Str::slug($groupName) }}"
                                                class="text-[10px] font-bold uppercase tracking-wider text-violet-300 hover:text-violet-200 px-3 py-1.5 rounded-lg hover:bg-violet-500/10 border border-transparent hover:border-violet-500/20">
                                            Enable group
                                        </button>
                                    </div>

                                    <div class="space-y-2">
                                        @foreach($permissions as $permission)
                                            @php
                                                $isOn = in_array($permission['name'], $assigned, true);
                                                $icon = $permissionIcons[$permission['name']] ?? 'circle';
                                            @endphp
                                            <div class="perm-row flex items-center gap-3 px-4 py-3 rounded-xl border border-white/8 bg-white/[0.02] {{ $isOn ? 'is-on' : '' }}"
                                                 data-perm-card
                                                 data-perm-label="{{ strtolower($permission['label']) }}"
                                                 data-role="{{ $role->name }}"
                                                 data-group-slug="{{ Str::slug($groupName) }}">
                                                <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-white/45 shrink-0">
                                                    <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                                                </div>
                                                <p class="flex-1 min-w-0 text-sm font-semibold text-white truncate">{{ $permission['label'] }}</p>
                                                <label class="perm-toggle shrink-0" aria-label="Toggle {{ $permission['label'] }}">
                                                    <input type="checkbox"
                                                           name="permissions[{{ $role->name }}][]"
                                                           value="{{ $permission['name'] }}"
                                                           {{ $isOn ? 'checked' : '' }}
                                                           data-perm-input
                                                           data-role="{{ $role->name }}">
                                                    <span></span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach

                            <p class="hidden text-center text-sm text-white/40 py-12" data-no-results="{{ $role->name }}">
                                No pages match your search.
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Unsaved bar --}}
            <div class="fixed bottom-0 inset-x-0 z-40 p-4 pointer-events-none">
                <div class="save-bar max-w-lg mx-auto pointer-events-auto glass-card rounded-2xl border border-amber-500/30 px-5 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-2xl" id="roles-sticky-bar">
                    <p class="text-sm font-semibold text-white">You have unsaved permission changes</p>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button type="button" id="sticky-discard" class="flex-1 sm:flex-none px-4 py-2 rounded-xl text-xs font-bold text-white/70 bg-white/5 border border-white/10">Discard</button>
                        <button type="button" id="sticky-save" class="flex-1 sm:flex-none px-4 py-2 rounded-xl text-xs font-bold text-white bg-violet-600 hover:bg-violet-500">Save</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reset modal --}}
        <div id="roles-reset-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/70 backdrop-blur-sm" aria-hidden="true">
            <div class="glass-card rounded-2xl border border-white/10 p-6 max-w-sm w-full shadow-2xl" role="dialog">
                <h3 class="text-lg font-black text-white">Reset permissions?</h3>
                <p class="text-sm text-white/50 mt-2">Restore default pages for <strong class="text-white" id="reset-modal-role-label">this role</strong>.</p>
                <div class="flex gap-3 mt-6">
                    <button type="button" id="reset-modal-cancel" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white/70 bg-white/5 border border-white/10">Cancel</button>
                    <button type="button" id="reset-modal-confirm" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-black bg-amber-400 hover:bg-amber-300">Reset</button>
                </div>
            </div>
        </div>

        <div id="roles-toast-host" class="fixed top-24 right-6 z-[200] space-y-2 pointer-events-none max-w-sm"></div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.getElementById('roles-editor');
            if (!root) return;

            const csrf = root.dataset.csrf;
            const updateBase = root.dataset.updateUrl;
            const resetBase = root.dataset.resetUrl;

            let activeRole = root.querySelector('[data-role-tab]')?.dataset.roleTab || 'admin';
            const snapshots = {};
            let pendingResetRole = null;

            const showToast = (message, type = 'success') => {
                const host = document.getElementById('roles-toast-host');
                const styles = {
                    success: 'border-emerald-500/30 text-emerald-300',
                    error: 'border-rose-500/30 text-rose-300',
                    info: 'border-cyan-500/30 text-cyan-300',
                };
                const el = document.createElement('div');
                el.className = `pointer-events-auto glass-card px-4 py-3 rounded-xl border text-sm font-medium text-white/90 ${styles[type] || styles.info}`;
                el.textContent = message;
                host.appendChild(el);
                setTimeout(() => el.remove(), 4000);
            };

            const collectPermissions = (role) =>
                Array.from(root.querySelectorAll(`input[data-perm-input][data-role="${role}"]:checked`)).map(el => el.value);

            const snapshotRole = (role) => {
                snapshots[role] = JSON.stringify(collectPermissions(role).sort());
            };

            const isDirty = (role) => snapshots[role] && snapshots[role] !== JSON.stringify(collectPermissions(role).sort());

            const syncCardStates = (role) => {
                root.querySelectorAll(`[data-perm-card][data-role="${role}"]`).forEach(card => {
                    const input = card.querySelector('input[data-perm-input]');
                    card.classList.toggle('is-on', !!input?.checked);
                });
            };

            const updateProgress = (role) => {
                const inputs = root.querySelectorAll(`input[data-perm-input][data-role="${role}"]`);
                const enabled = root.querySelectorAll(`input[data-perm-input][data-role="${role}"]:checked`).length;
                const total = inputs.length;
                const pct = total ? Math.round((enabled / total) * 100) : 0;

                const bar = root.querySelector(`[data-progress-bar="${role}"]`);
                const label = root.querySelector(`[data-progress-label="${role}"]`);
                const countEl = root.querySelector(`[data-enabled-count="${role}"]`);
                const tabBtn = root.querySelector(`[data-role-tab="${role}"]`);

                if (bar) bar.style.width = `${pct}%`;
                if (label) label.textContent = `${pct}%`;
                if (countEl) countEl.textContent = enabled;

                root.querySelectorAll(`[data-group-count^="${role}-"]`).forEach(el => {
                    const slug = el.dataset.groupCount.replace(`${role}-`, '');
                    el.textContent = root.querySelectorAll(`[data-perm-card][data-role="${role}"][data-group-slug="${slug}"] input:checked`).length;
                });

                root.querySelector(`[data-dirty-label="${role}"]`)?.classList.toggle('hidden', !isDirty(role));
                document.getElementById('roles-sticky-bar')?.classList.toggle('is-visible', Object.keys(snapshots).some(isDirty));
            };

            const setActiveRole = (role) => {
                activeRole = role;
                root.querySelectorAll('[data-role-tab]').forEach(tab => {
                    tab.setAttribute('aria-selected', tab.dataset.roleTab === role ? 'true' : 'false');
                });
                root.querySelectorAll('[data-role-panel]').forEach(panel => {
                    panel.classList.toggle('is-active', panel.dataset.rolePanel === role);
                });
                if (typeof lucide !== 'undefined') lucide.createIcons();
            };

            const filterPermissions = (role, query) => {
                const q = query.trim().toLowerCase();
                let visible = 0;
                root.querySelectorAll(`[data-perm-card][data-role="${role}"]`).forEach(card => {
                    const match = !q || card.dataset.permLabel.includes(q);
                    card.classList.toggle('is-hidden', !match);
                    if (match) visible++;
                });
                root.querySelectorAll(`[data-perm-group^="${role}-"]`).forEach(group => {
                    group.classList.toggle('hidden', q.length > 0 && !group.querySelector('[data-perm-card]:not(.is-hidden)'));
                });
                root.querySelector(`[data-no-results="${role}"]`)?.classList.toggle('hidden', visible > 0 || !q);
            };

            const saveRole = async (role) => {
                try {
                    const res = await fetch(`${updateBase}/${role}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ permissions: collectPermissions(role) }),
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Save failed');
                    snapshotRole(role);
                    updateProgress(role);
                    showToast('Permissions saved.');
                } catch (e) {
                    showToast(e.message || 'Save failed.', 'error');
                }
            };

            const resetRole = async (role) => {
                try {
                    const res = await fetch(`${resetBase}/${role}/reset`, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Reset failed');
                    const assigned = data.data?.permissions || [];
                    root.querySelectorAll(`input[data-perm-input][data-role="${role}"]`).forEach(input => {
                        input.checked = assigned.includes(input.value);
                    });
                    syncCardStates(role);
                    snapshotRole(role);
                    updateProgress(role);
                    showToast('Reset to defaults.', 'info');
                } catch (e) {
                    showToast(e.message || 'Reset failed.', 'error');
                }
            };

            const discardRole = (role) => {
                const saved = JSON.parse(snapshots[role] || '[]');
                root.querySelectorAll(`input[data-perm-input][data-role="${role}"]`).forEach(input => {
                    input.checked = saved.includes(input.value);
                });
                syncCardStates(role);
                updateProgress(role);
            };

            root.querySelectorAll('[data-role-tab]').forEach(tab => {
                tab.addEventListener('click', () => setActiveRole(tab.dataset.roleTab));
            });

            root.querySelectorAll('[data-perm-card]').forEach(card => {
                card.addEventListener('click', (e) => {
                    if (e.target.closest('.perm-toggle')) return;
                    const input = card.querySelector('input[data-perm-input]');
                    if (input) {
                        input.checked = !input.checked;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            });

            root.querySelectorAll('input[data-perm-input]').forEach(input => {
                input.addEventListener('change', () => {
                    syncCardStates(input.dataset.role);
                    updateProgress(input.dataset.role);
                });
            });

            root.querySelectorAll('[data-perm-search]').forEach(input => {
                input.addEventListener('input', () => filterPermissions(input.dataset.permSearch, input.value));
            });

            root.querySelectorAll('[data-select-all]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const role = btn.dataset.selectAll;
                    root.querySelectorAll(`input[data-perm-input][data-role="${role}"]`).forEach(i => {
                        const card = i.closest('[data-perm-card]');
                        if (card && !card.classList.contains('is-hidden')) i.checked = true;
                    });
                    syncCardStates(role);
                    updateProgress(role);
                });
            });

            root.querySelectorAll('[data-select-none]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const role = btn.dataset.selectNone;
                    root.querySelectorAll(`input[data-perm-input][data-role="${role}"]`).forEach(i => {
                        const card = i.closest('[data-perm-card]');
                        if (card && !card.classList.contains('is-hidden')) i.checked = false;
                    });
                    syncCardStates(role);
                    updateProgress(role);
                });
            });

            root.querySelectorAll('[data-group-all]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const { groupAll: role, groupSlug } = btn.dataset;
                    root.querySelectorAll(`[data-perm-card][data-role="${role}"][data-group-slug="${groupSlug}"] input`).forEach(i => { i.checked = true; });
                    syncCardStates(role);
                    updateProgress(role);
                });
            });

            const openResetModal = (role) => {
                pendingResetRole = role;
                document.getElementById('reset-modal-role-label').textContent = role;
                const modal = document.getElementById('roles-reset-modal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            root.querySelector('[data-reset-active]')?.addEventListener('click', () => openResetModal(activeRole));
            document.getElementById('reset-modal-cancel')?.addEventListener('click', () => {
                document.getElementById('roles-reset-modal').classList.add('hidden');
                document.getElementById('roles-reset-modal').classList.remove('flex');
                pendingResetRole = null;
            });
            document.getElementById('reset-modal-confirm')?.addEventListener('click', async () => {
                if (pendingResetRole) await resetRole(pendingResetRole);
                document.getElementById('roles-reset-modal').classList.add('hidden');
                document.getElementById('roles-reset-modal').classList.remove('flex');
                pendingResetRole = null;
            });

            root.querySelector('[data-save-active]')?.addEventListener('click', () => saveRole(activeRole));
            document.getElementById('sticky-save')?.addEventListener('click', () => {
                Object.keys(snapshots).filter(isDirty).forEach(saveRole);
            });
            document.getElementById('sticky-discard')?.addEventListener('click', () => {
                Object.keys(snapshots).filter(isDirty).forEach(discardRole);
            });

            root.querySelectorAll('[data-role-panel]').forEach(panel => {
                snapshotRole(panel.dataset.rolePanel);
                syncCardStates(panel.dataset.rolePanel);
                updateProgress(panel.dataset.rolePanel);
            });
            setActiveRole(activeRole);

            window.addEventListener('beforeunload', (e) => {
                if (Object.keys(snapshots).some(isDirty)) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
    @endpush
</x-admin-layout>

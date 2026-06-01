<x-admin-layout>
    <x-slot name="header">
        Docker Infrastructure
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-8" id="docker-control-root"
         data-status-url="{{ route('admin.docker.status') }}"
         data-action-url="{{ route('admin.docker.action') }}"
         data-csrf="{{ csrf_token() }}"
         data-poll="{{ $pollSeconds }}"
         data-enabled="{{ $enabled ? '1' : '0' }}">

        <div class="glass-card rounded-2xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/40">Market · {{ strtoupper($market) }}</p>
                <h2 class="text-xl font-black text-white tracking-tight mt-1">Container status &amp; control</h2>
                <p class="text-sm text-white/50 mt-2 max-w-xl">Each admin panel only manages this region’s servers. Laravel stack and WhatsApp bot may run on separate VPS hosts.</p>
            </div>
            <div class="flex items-center gap-3">
                <span id="docker-last-refresh" class="text-[10px] font-bold uppercase tracking-widest text-white/30">—</span>
                <button type="button" id="docker-refresh-btn" class="px-4 py-2.5 glass rounded-xl text-xs font-bold text-white hover:bg-white/10 transition-all border border-white/10">
                    Refresh now
                </button>
            </div>
        </div>

        @unless($enabled)
            <div class="glass-card rounded-2xl p-8 border border-amber-500/30">
                <h3 class="text-lg font-black text-amber-300 mb-2">Docker control is disabled</h3>
                <p class="text-sm text-white/60 mb-4">Enable in <code class="text-amber-200/90">.env</code> on the server, then configure SSH or local Docker access.</p>
                <ul class="text-xs text-white/50 space-y-2 font-mono">
                    <li>DOCKER_CONTROL_ENABLED=true</li>
                    <li>DOCKER_LARAVEL_SSH_HOST / DOCKER_LARAVEL_SSH_KEY (or local DOCKER_LARAVEL_WORK_DIR)</li>
                    <li>DOCKER_BOT_SSH_HOST / DOCKER_BOT_SSH_KEY (bot VPS)</li>
                </ul>
            </div>
        @endunless

        <div id="docker-stacks" class="space-y-8">
            @foreach($stacks as $stack)
                <section class="glass-card rounded-2xl p-8" data-stack-id="{{ $stack['id'] }}">
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                        <div>
                            <h3 class="text-lg font-black text-white">{{ $stack['label'] }}</h3>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-white/40 mt-1">{{ $stack['host_label'] }}</p>
                        </div>
                        <span class="docker-stack-badge px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border border-white/10 text-white/40">Loading…</span>
                    </div>
                    @if(!$stack['configured'])
                        <p class="text-sm text-amber-400/90 docker-stack-hint">{{ $stack['config_hint'] }}</p>
                    @else
                        <p class="text-sm text-white/40 docker-stack-hint hidden"></p>
                    @endif
                    <div class="docker-stack-containers grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                        <p class="text-sm text-white/30 col-span-full">Waiting for status…</p>
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    <script>
        (function () {
            const root = document.getElementById('docker-control-root');
            if (!root) return;

            const statusUrl = root.dataset.statusUrl;
            const actionUrl = root.dataset.actionUrl;
            const csrf = root.dataset.csrf;
            const pollSeconds = parseInt(root.dataset.poll || '15', 10) * 1000;
            const enabled = root.dataset.enabled === '1';
            let pollTimer = null;

            function stateColor(state) {
                if (state === 'running') return 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30';
                if (state === 'exited' || state === 'dead') return 'bg-red-500/20 text-red-400 border-red-500/30';
                if (state === 'paused') return 'bg-amber-500/20 text-amber-400 border-amber-500/30';
                return 'bg-white/10 text-white/50 border-white/10';
            }

            function renderContainer(stackId, c) {
                const actions = (c.actions || []).map(function (action) {
                    const labels = { start: 'Start', stop: 'Stop', restart: 'Restart' };
                    const colors = {
                        start: 'from-emerald-600 to-teal-600',
                        stop: 'from-red-600 to-rose-600',
                        restart: 'from-violet-600 to-cyan-600',
                    };
                    return '<button type="button" class="docker-action-btn px-3 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest text-white bg-gradient-to-r ' + (colors[action] || colors.restart) + ' hover:opacity-90 transition-opacity" data-stack="' + stackId + '" data-container="' + c.name + '" data-action="' + action + '">' + (labels[action] || action) + '</button>';
                }).join('');

                return '<div class="rounded-xl border border-white/10 bg-white/5 p-5 flex flex-col gap-4">' +
                    '<div class="flex items-start justify-between gap-3">' +
                    '<div class="min-w-0"><p class="font-mono text-sm font-bold text-white truncate">' + c.name + '</p>' +
                    '<p class="text-[10px] text-white/40 mt-1 truncate">' + c.image + '</p></div>' +
                    '<span class="shrink-0 px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border ' + stateColor(c.state) + '">' + c.state + '</span></div>' +
                    '<p class="text-xs text-white/50">' + c.status + '</p>' +
                    '<div class="flex flex-wrap gap-2">' + actions + '</div></div>';
            }

            function updateStacks(stacks) {
                stacks.forEach(function (stack) {
                    const section = document.querySelector('[data-stack-id="' + stack.id + '"]');
                    if (!section) return;

                    const badge = section.querySelector('.docker-stack-badge');
                    const hint = section.querySelector('.docker-stack-hint');
                    const grid = section.querySelector('.docker-stack-containers');

                    if (!stack.configured) {
                        badge.textContent = 'Not configured';
                        badge.className = 'docker-stack-badge px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border border-amber-500/30 text-amber-400';
                        if (stack.error && hint) { hint.textContent = stack.error; hint.classList.remove('hidden'); }
                        grid.innerHTML = '';
                        return;
                    }

                    if (stack.reachable) {
                        badge.textContent = 'Online';
                        badge.className = 'docker-stack-badge px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border border-emerald-500/30 text-emerald-400';
                        if (hint) hint.classList.add('hidden');
                    } else {
                        badge.textContent = 'Unreachable';
                        badge.className = 'docker-stack-badge px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border border-red-500/30 text-red-400';
                        if (stack.error && hint) {
                            hint.textContent = stack.error;
                            hint.classList.remove('hidden');
                        }
                    }

                    if (!stack.containers || stack.containers.length === 0) {
                        grid.innerHTML = '<p class="text-sm text-white/40 col-span-full">No matching containers found.</p>';
                        return;
                    }

                    grid.innerHTML = stack.containers.map(function (c) {
                        return renderContainer(stack.id, c);
                    }).join('');
                });

                document.getElementById('docker-last-refresh').textContent = 'Updated ' + new Date().toLocaleTimeString();
            }

            async function fetchStatus() {
                if (!enabled) return;
                try {
                    const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await res.json();
                    if (data.stacks) updateStacks(data.stacks);
                } catch (e) {
                    console.error('Docker status fetch failed', e);
                }
            }

            async function runAction(btn) {
                if (!confirm('Run ' + btn.dataset.action + ' on ' + btn.dataset.container + '?')) return;
                btn.disabled = true;
                try {
                    const body = new FormData();
                    body.append('_token', csrf);
                    body.append('stack_id', btn.dataset.stack);
                    body.append('container', btn.dataset.container);
                    body.append('action', btn.dataset.action);
                    const res = await fetch(actionUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: body,
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        alert(data.message || 'Action failed');
                        return;
                    }
                    if (data.stacks) updateStacks(data.stacks);
                } catch (e) {
                    alert('Request failed');
                } finally {
                    btn.disabled = false;
                }
            }

            root.addEventListener('click', function (e) {
                const btn = e.target.closest('.docker-action-btn');
                if (btn) runAction(btn);
            });

            document.getElementById('docker-refresh-btn')?.addEventListener('click', fetchStatus);

            if (enabled) {
                fetchStatus();
                pollTimer = setInterval(fetchStatus, pollSeconds);
            }
        })();
    </script>
</x-admin-layout>

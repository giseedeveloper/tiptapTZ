<div class="mb-3 px-4 sidebar-label"><p class="text-[9px] font-bold text-white/25 uppercase tracking-[0.25em]">Main</p></div>
<a href="{{ route('admin.dashboard') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-violet-500/20 to-cyan-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.dashboard') ? 'text-violet-400' : 'text-white/50' }}"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
    </div>
    <span class="font-medium text-xs">Dashboard</span>
</a>
<a href="{{ route('admin.search.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.search.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-sky-500/20 to-blue-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.search.*') ? 'text-sky-400' : 'text-white/50' }}"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
    </div>
    <span class="font-medium text-xs">Global Search</span>
</a>

<div class="mt-5 mb-3 px-4 sidebar-label"><p class="text-[9px] font-bold text-white/25 uppercase tracking-[0.25em]">People</p></div>
<a href="{{ route('admin.restaurants.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.restaurants.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-emerald-500/20 to-teal-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.restaurants.*') ? 'text-emerald-400' : 'text-white/50' }}"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>
    </div>
    <span class="font-medium text-xs">Restaurants</span>
</a>
<a href="{{ route('admin.users.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-blue-500/20 to-indigo-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.users.*') ? 'text-blue-400' : 'text-white/50' }}"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <span class="font-medium text-xs">Users &amp; Roles</span>
</a>
<a href="{{ route('admin.waiters.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.waiters.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-amber-500/20 to-orange-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.waiters.*') ? 'text-amber-400' : 'text-white/50' }}"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <span class="font-medium text-xs">Waiters</span>
</a>

<div class="mt-5 mb-3 px-4 sidebar-label"><p class="text-[9px] font-bold text-white/25 uppercase tracking-[0.25em]">Operations</p></div>
<a href="{{ route('admin.live-orders.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.live-orders.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-emerald-500/20 to-green-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.live-orders.*') ? 'text-emerald-400' : 'text-white/50' }}"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/></svg>
    </div>
    <span class="font-medium text-xs">Live Orders</span>
</a>
<a href="{{ route('admin.orders.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.orders.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-amber-500/20 to-orange-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.orders.*') ? 'text-amber-400' : 'text-white/50' }}"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
    </div>
    <span class="font-medium text-xs">Orders History</span>
</a>
<a href="{{ route('admin.customer-requests.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.customer-requests.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-rose-500/20 to-pink-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.customer-requests.*') ? 'text-rose-400' : 'text-white/50' }}"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    </div>
    <span class="font-medium text-xs">Customer Requests</span>
</a>

<div class="mt-5 mb-3 px-4 sidebar-label"><p class="text-[9px] font-bold text-white/25 uppercase tracking-[0.25em]">Finance</p></div>
<a href="{{ route('admin.payments.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.payments.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-pink-500/20 to-rose-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.payments.*') ? 'text-pink-400' : 'text-white/50' }}"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
    </div>
    <span class="font-medium text-xs">Payments</span>
</a>
<a href="{{ route('admin.payment-integration.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.payment-integration.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-cyan-500/20 to-blue-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.payment-integration.*') ? 'text-cyan-400' : 'text-white/50' }}"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
    </div>
    <span class="font-medium text-xs">Payment Integration</span>
</a>
<a href="{{ route('admin.withdrawals.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.withdrawals.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-teal-500/20 to-cyan-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.withdrawals.*') ? 'text-teal-400' : 'text-white/50' }}"><path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/></svg>
    </div>
    <span class="font-medium text-xs">Withdrawals</span>
</a>
<a href="{{ route('admin.tips.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.tips.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-yellow-500/20 to-amber-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.tips.*') ? 'text-yellow-400' : 'text-white/50' }}"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
    </div>
    <span class="font-medium text-xs">Tips</span>
</a>
<a href="{{ route('admin.payroll.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.payroll.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-indigo-500/20 to-violet-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.payroll.*') ? 'text-indigo-400' : 'text-white/50' }}"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <span class="font-medium text-xs">Payroll</span>
</a>
<a href="{{ route('admin.reports.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.reports.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-cyan-500/20 to-blue-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.reports.*') ? 'text-cyan-400' : 'text-white/50' }}"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
    </div>
    <span class="font-medium text-xs">Reports</span>
</a>

<div class="mt-5 mb-3 px-4 sidebar-label"><p class="text-[9px] font-bold text-white/25 uppercase tracking-[0.25em]">Content</p></div>
<a href="{{ route('admin.landing-page.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.landing-page.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-fuchsia-500/20 to-violet-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.landing-page.*') ? 'text-fuchsia-400' : 'text-white/50' }}"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 13H8"/><path d="M16 13h-2"/><path d="M10 17H8"/><path d="M16 17h-2"/></svg>
    </div>
    <span class="font-medium text-xs">Landing Page</span>
</a>
<a href="{{ route('admin.feedback.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.feedback.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-orange-500/20 to-red-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.feedback.*') ? 'text-orange-400' : 'text-white/50' }}"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
    </div>
    <span class="font-medium text-xs">Feedback</span>
</a>
<a href="{{ route('admin.menus.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.menus.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-lime-500/20 to-green-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.menus.*') ? 'text-lime-400' : 'text-white/50' }}"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/></svg>
    </div>
    <span class="font-medium text-xs">Menus</span>
</a>

<div class="mt-5 mb-3 px-4 sidebar-label"><p class="text-[9px] font-bold text-white/25 uppercase tracking-[0.25em]">System</p></div>
<a href="{{ route('admin.docker.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.docker.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-sky-500/20 to-indigo-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.docker.*') ? 'text-sky-400' : 'text-white/50' }}"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
    </div>
    <span class="font-medium text-xs">Docker</span>
</a>
<a href="{{ route('admin.bots.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.bots.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-cyan-500/20 to-blue-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.bots.*') ? 'text-cyan-400' : 'text-white/50' }}"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/></svg>
    </div>
    <span class="font-medium text-xs">Bots &amp; WhatsApp</span>
</a>
<a href="{{ route('admin.notifications.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.notifications.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-violet-500/20 to-purple-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.notifications.*') ? 'text-violet-400' : 'text-white/50' }}"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
    </div>
    <span class="font-medium text-xs">Notifications</span>
</a>
<a href="{{ route('admin.activity-log.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.activity-log.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-slate-500/20 to-gray-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.activity-log.*') ? 'text-slate-400' : 'text-white/50' }}"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
    </div>
    <span class="font-medium text-xs">Activity Log</span>
</a>
<a href="{{ route('admin.settings.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2 px-3 py-1.5 mx-2 rounded-lg {{ request()->routeIs('admin.settings.*') ? 'sidebar-link-active' : 'text-white/55' }}">
    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-zinc-500/20 to-slate-500/20 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ request()->routeIs('admin.settings.*') ? 'text-slate-400' : 'text-white/50' }}"><circle cx="12" cy="12" r="3"/><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.47a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.39a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/></svg>
    </div>
    <span class="font-medium text-xs">Settings</span>
</a>

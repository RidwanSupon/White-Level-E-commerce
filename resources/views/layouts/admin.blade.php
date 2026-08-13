<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Dashboard - {{ $site_name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        {!! $white_label_css !!}
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full bg-slate-900 text-slate-100 antialiased flex" x-data="{ sidebarOpen: false }">

    <!-- Admin Sidebar Nav -->
    <aside class="hidden md:flex flex-col w-64 bg-slate-950 border-r border-slate-800 shrink-0">
        <div class="p-6 border-b border-slate-800/80 flex items-center gap-3">
            @if(!empty(setting('site_logo')) && file_exists(public_path(setting('site_logo'))))
                <img src="{{ setting('site_logo') }}" alt="{{ $site_name }}" class="h-9 max-w-[120px] object-contain">
            @else
                <div class="w-9 h-9 rounded-xl bg-brand-500 text-white font-bold flex items-center justify-center text-lg shadow-md shadow-brand-500/20">
                    A
                </div>
            @endif
            <div>
                <h1 class="font-display font-bold text-lg text-white leading-none">{{ $site_name }}</h1>
                <span class="text-[10px] font-semibold text-brand-500 uppercase tracking-wider">Enterprise Admin</span>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-brand-500 text-white font-semibold shadow-md shadow-brand-500/20' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Dashboard
            </a>

            <!-- Catalog Section -->
            <div class="pt-3 pb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider px-3">Catalog</div>
            <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.products.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Products
            </a>
            <a href="{{ route('admin.attributes.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.attributes.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Attributes & Swatches
            </a>
            <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.categories.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Categories
            </a>
            <a href="{{ route('admin.brands.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.brands.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Brands
            </a>
            <a href="{{ route('admin.shipping_zones.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.shipping_zones.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Shipping Zone & Rates
            </a>

            <!-- Sales Section -->
            <div class="pt-3 pb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider px-3">Sales & Marketing</div>
            <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.orders.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Orders & Invoices
            </a>
            <a href="{{ route('admin.payments.index') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.payments.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                <span>bKash / Nagad Payments</span>
                @php $pendingPaymentsCount = \App\Models\ManualPayment::where('status', 'verification_pending')->count(); @endphp
                @if($pendingPaymentsCount > 0)
                    <span class="px-2 py-0.5 bg-amber-500 text-slate-950 font-extrabold text-[10px] rounded-full animate-pulse">{{ $pendingPaymentsCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.coupons.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.coupons.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Discount Coupons
            </a>
            <a href="{{ route('admin.banners.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.banners.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Hero Promo Banners
            </a>
            <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.reports.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Analytics & Reports
            </a>
            <a href="{{ route('admin.notifications.index') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.notifications.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                <span>Notifications Center</span>
                @php $unreadCount = auth()->user()?->unreadNotifications->count() ?? 0; @endphp
                @if($unreadCount > 0)
                    <span class="px-2 py-0.5 bg-rose-500 text-white font-extrabold text-[10px] rounded-full animate-pulse">{{ $unreadCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.pages.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.pages.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                CMS Custom Pages
            </a>

            <!-- Operations Section -->
            <div class="pt-3 pb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider px-3">Operations & Setup</div>
            <a href="{{ route('admin.inventory.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.inventory.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Inventory Audit
            </a>
            <a href="{{ route('admin.payment_methods.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.payment_methods.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Payment Gateways
            </a>
            <a href="{{ route('admin.taxes.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.taxes.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Tax Rates & Settings
            </a>

            <!-- Access & Governance Section -->
            <div class="pt-3 pb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider px-3">Governance</div>
            <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.roles.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Roles & Permissions
            </a>
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.users.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Staff Accounts
            </a>
            <a href="{{ route('admin.audit_logs.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.audit_logs.*') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                Audit Logs
            </a>

            <!-- Settings -->
            <div class="pt-3 pb-1 text-[11px] font-bold text-slate-500 uppercase tracking-wider px-3">System</div>
            <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.settings.index') ? 'bg-brand-500 text-white font-semibold shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
                White-Label Settings
            </a>

            <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm text-slate-400 hover:text-white hover:bg-slate-900 transition-all mt-4 border-t border-slate-800/80 pt-4">
                <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 01-2 2v10a2 2 0 012 2h10a2 2 0 012-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Live Storefront ↗
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800/80">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 rounded-xl transition-all">
                    Sign Out Administrator
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Workspace -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top App Bar -->
        <header class="bg-slate-950 border-b border-slate-800 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h2 class="text-lg font-bold text-white font-display">Admin Portal</h2>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400 bg-slate-900 px-3 py-1.5 rounded-full border border-slate-800">
                    Logged in as: <strong class="text-white">{{ auth()->user()?->name }}</strong>
                </span>
            </div>
        </header>

        <!-- Main Dashboard View Body -->
        <main class="flex-1 overflow-y-auto p-6 bg-slate-900/50">
            @if(session('success'))
                <div class="mb-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-2xl text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-rose-500/10 border border-rose-500/20 text-rose-400 px-4 py-3 rounded-2xl text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</body>
</html>

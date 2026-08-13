<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign In - {{ $site_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        {!! $white_label_css !!}
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-slate-950 text-white">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-brand-500 text-white font-bold text-2xl flex items-center justify-center mx-auto shadow-lg shadow-brand-500/20">
                A
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Administrator Portal</h1>
            <p class="text-xs text-slate-400">Enterprise White-Label Management Suite</p>
        </div>

        @if($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs rounded-xl p-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Admin Email</label>
                <input type="email" name="email" required value="admin@example.com" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Password</label>
                <input type="password" name="password" required value="password" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
            </div>

            <button type="submit" class="w-full py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/20 transition-all">
                Authenticate as Admin
            </button>
        </form>
    </div>
</body>
</html>

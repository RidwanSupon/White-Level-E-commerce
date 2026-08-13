@extends('layouts.admin')

@section('content')
<div class="max-w-4xl space-y-8" x-data="whiteLabelSettings({
    primaryHsl: '{{ $settings['color_primary_hsl'] }}',
    secondaryHsl: '{{ $settings['color_secondary_hsl'] }}',
    accentHsl: '{{ $settings['color_accent_hsl'] }}'
})">
    <div>
        <h1 class="text-2xl font-bold text-white font-display">White-Label System Settings</h1>
        <p class="text-slate-400 text-xs mt-1">Configure store logo, company identity, HSL color palette, currency, and branding preview</p>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs font-semibold flex items-center gap-2">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- 1. Brand Identity & Logo Upload -->
        <div class="bg-slate-950 rounded-3xl p-6 sm:p-8 border border-slate-800 space-y-6">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3 flex items-center justify-between">
                <span>1. Brand Identity & Store Logo</span>
                <span class="text-xs text-slate-500 font-normal">PNG, JPG, SVG, WebP up to 2MB</span>
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Company / Store Name</label>
                    <input type="text" name="site_name" value="{{ $settings['site_name'] }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tagline</label>
                    <input type="text" name="site_tagline" value="{{ $settings['site_tagline'] }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                </div>
            </div>

            <!-- Store Logo Upload & Live Preview -->
            <div class="pt-2">
                <label class="block text-xs font-semibold text-slate-300 mb-2">Store Logo Image</label>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 p-4 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="w-32 h-16 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-center p-2 overflow-hidden">
                        @if(!empty($settings['site_logo']))
                            <img src="{{ $settings['site_logo'] }}" alt="Store Logo" class="max-h-full max-w-full object-contain">
                        @else
                            <span class="text-slate-500 text-xs font-bold font-display">LuxeCart</span>
                        @endif
                    </div>
                    <div class="space-y-2 flex-1">
                        <input type="file" name="site_logo_file" accept="image/*" class="text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-500 file:text-white hover:file:bg-brand-600 cursor-pointer">
                        <p class="text-[11px] text-slate-500">Upload custom logo image. Current logo: <span class="font-mono text-slate-400">{{ $settings['site_logo'] }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. White-Label HSL Color Palette with Live Visual Color Picker -->
        <div class="bg-slate-950 rounded-3xl p-6 sm:p-8 border border-slate-800 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base">2. White-Label HSL Color Palette</h3>
                <span class="text-xs text-brand-400 font-semibold">Live Real-time Color Sync</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Primary Color -->
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-white">Primary Color</label>
                        <div class="w-8 h-8 rounded-lg border border-white/20 shadow-md transition-all" :style="'background-color: hsl(' + primaryHsl + ')'"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="color" x-model="primaryHex" @input="updateHslFromHex('primary')" class="w-10 h-10 rounded-lg bg-transparent border-0 cursor-pointer">
                        <input type="text" name="color_primary_hsl" x-model="primaryHsl" @input="updateHexFromHsl('primary')" required placeholder="221 83% 53%" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs font-mono text-white outline-none focus:border-brand-500">
                    </div>
                    <span class="text-[10px] text-slate-400 block">Controls main CTA buttons, badges, active states</span>
                </div>

                <!-- Secondary Color -->
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-white">Secondary Color</label>
                        <div class="w-8 h-8 rounded-lg border border-white/20 shadow-md transition-all" :style="'background-color: hsl(' + secondaryHsl + ')'"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="color" x-model="secondaryHex" @input="updateHslFromHex('secondary')" class="w-10 h-10 rounded-lg bg-transparent border-0 cursor-pointer">
                        <input type="text" name="color_secondary_hsl" x-model="secondaryHsl" @input="updateHexFromHsl('secondary')" required placeholder="215 28% 17%" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs font-mono text-white outline-none focus:border-brand-500">
                    </div>
                    <span class="text-[10px] text-slate-400 block">Controls dark panels, footers & surfaces</span>
                </div>

                <!-- Accent Color -->
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-white">Accent Color</label>
                        <div class="w-8 h-8 rounded-lg border border-white/20 shadow-md transition-all" :style="'background-color: hsl(' + accentHsl + ')'"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="color" x-model="accentHex" @input="updateHslFromHex('accent')" class="w-10 h-10 rounded-lg bg-transparent border-0 cursor-pointer">
                        <input type="text" name="color_accent_hsl" x-model="accentHsl" @input="updateHexFromHsl('accent')" required placeholder="142 71% 45%" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs font-mono text-white outline-none focus:border-brand-500">
                    </div>
                    <span class="text-[10px] text-slate-400 block">Controls success alerts & positive indicators</span>
                </div>
            </div>

            <!-- Live Interactive Theme Component Preview -->
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Live Storefront Theme Component Preview</span>
                <div class="flex flex-wrap items-center gap-4 p-4 rounded-xl bg-slate-950 border border-slate-800">
                    <button type="button" class="px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-lg transition-all" :style="'background-color: hsl(' + primaryHsl + ')'">
                        Primary Button Sample
                    </button>
                    <span class="px-3 py-1 rounded-full font-bold text-[10px] text-white" :style="'background-color: hsl(' + accentHsl + ')'">
                        ★ Accent Badge
                    </span>
                    <div class="px-4 py-2 rounded-xl text-xs font-semibold text-white border" :style="'background-color: hsl(' + secondaryHsl + '); border-color: hsl(' + primaryHsl + ')'">
                        Secondary Card Surface
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Currency & Localization -->
        <div class="bg-slate-950 rounded-3xl p-6 sm:p-8 border border-slate-800 space-y-6">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">3. Currency & Localization</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Currency Code</label>
                    <input type="text" name="currency_code" value="{{ $settings['currency_code'] }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                </div>
            </div>
        </div>

        <!-- 4. Manual bKash & Nagad Payment Settings -->
        <div class="bg-slate-950 rounded-3xl p-6 sm:p-8 border border-slate-800 space-y-6">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3 flex items-center justify-between">
                <span>4. Manual bKash & Nagad Payment Settings</span>
                <span class="text-xs text-pink-400 font-normal">White-Label Mobile Payment Configuration</span>
            </h3>

            <!-- Global Verification Rules -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Payment Proof Screenshot Required?</label>
                    <select name="payment_proof_required" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                        <option value="1" {{ ($settings['payment_proof_required'] ?? '0') == '1' ? 'selected' : '' }}>Yes — Screenshot Required (ON)</option>
                        <option value="0" {{ ($settings['payment_proof_required'] ?? '0') == '0' ? 'selected' : '' }}>No — Optional Proof (OFF)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Payment Expiry Time (Hours)</label>
                    <input type="number" min="1" max="168" name="payment_expiry_hours" value="{{ $settings['payment_expiry_hours'] ?? '24' }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                </div>
            </div>

            <!-- bKash Configuration -->
            <div class="p-6 rounded-2xl bg-pink-950/20 border border-pink-900/30 space-y-4">
                <div class="flex items-center justify-between border-b border-pink-900/30 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-pink-500 inline-block"></span>
                        <h4 class="font-extrabold text-pink-400 text-sm">bKash Payment Settings</h4>
                    </div>
                    <div>
                        <select name="bkash_enabled" class="bg-slate-900 border border-pink-800/50 rounded-xl px-3 py-1.5 text-xs font-bold text-pink-300 outline-none">
                            <option value="1" {{ ($settings['bkash_enabled'] ?? '1') == '1' ? 'selected' : '' }}>Enabled</option>
                            <option value="0" {{ ($settings['bkash_enabled'] ?? '1') == '0' ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">bKash Merchant/Personal Number *</label>
                        <input type="text" name="bkash_number" value="{{ $settings['bkash_number'] ?? '01700000000' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white outline-none focus:border-pink-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Account Type</label>
                        <select name="bkash_account_type" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white outline-none focus:border-pink-500">
                            <option value="Personal" {{ ($settings['bkash_account_type'] ?? 'Personal') == 'Personal' ? 'selected' : '' }}>Personal (Send Money)</option>
                            <option value="Merchant" {{ ($settings['bkash_account_type'] ?? 'Personal') == 'Merchant' ? 'selected' : '' }}>Merchant (Make Payment)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Account Name</label>
                        <input type="text" name="bkash_account_name" value="{{ $settings['bkash_account_name'] ?? 'LuxeCart Store' }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white outline-none focus:border-pink-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">bKash Customer Instructions</label>
                    <textarea name="bkash_instructions" rows="4" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 outline-none focus:border-pink-500 font-mono">{{ $settings['bkash_instructions'] ?? "1. Open bKash App.\n2. Select Send Money.\n3. Enter the merchant number above.\n4. Enter the exact order amount.\n5. Complete the transaction and copy your Transaction ID.\n6. Paste your Transaction ID below and submit." }}</textarea>
                </div>
            </div>

            <!-- Nagad Configuration -->
            <div class="p-6 rounded-2xl bg-orange-950/20 border border-orange-900/30 space-y-4">
                <div class="flex items-center justify-between border-b border-orange-900/30 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-orange-500 inline-block"></span>
                        <h4 class="font-extrabold text-orange-400 text-sm">Nagad Payment Settings</h4>
                    </div>
                    <div>
                        <select name="nagad_enabled" class="bg-slate-900 border border-orange-800/50 rounded-xl px-3 py-1.5 text-xs font-bold text-orange-300 outline-none">
                            <option value="1" {{ ($settings['nagad_enabled'] ?? '1') == '1' ? 'selected' : '' }}>Enabled</option>
                            <option value="0" {{ ($settings['nagad_enabled'] ?? '1') == '0' ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nagad Merchant/Personal Number *</label>
                        <input type="text" name="nagad_number" value="{{ $settings['nagad_number'] ?? '01800000000' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white outline-none focus:border-orange-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Account Type</label>
                        <select name="nagad_account_type" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white outline-none focus:border-orange-500">
                            <option value="Personal" {{ ($settings['nagad_account_type'] ?? 'Personal') == 'Personal' ? 'selected' : '' }}>Personal (Send Money)</option>
                            <option value="Merchant" {{ ($settings['nagad_account_type'] ?? 'Personal') == 'Merchant' ? 'selected' : '' }}>Merchant (Make Payment)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Account Name</label>
                        <input type="text" name="nagad_account_name" value="{{ $settings['nagad_account_name'] ?? 'LuxeCart Store' }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white outline-none focus:border-orange-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nagad Customer Instructions</label>
                    <textarea name="nagad_instructions" rows="4" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 outline-none focus:border-orange-500 font-mono">{{ $settings['nagad_instructions'] ?? "1. Open Nagad App.\n2. Select Send Money.\n3. Enter the merchant number above.\n4. Enter the exact order amount.\n5. Complete the transaction and copy your Transaction ID.\n6. Paste your Transaction ID below and submit." }}</textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="px-8 py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/20 transition-all">
            Save & Publish White-Label Settings
        </button>
    </form>
</div>

<script>
function whiteLabelSettings(config) {
    return {
        primaryHsl: config.primaryHsl || '221 83% 53%',
        secondaryHsl: config.secondaryHsl || '215 28% 17%',
        accentHsl: config.accentHsl || '142 71% 45%',
        primaryHex: '#2563eb',
        secondaryHex: '#1e293b',
        accentHex: '#16a34a',

        init() {
            this.primaryHex = this.hslToHex(this.primaryHsl);
            this.secondaryHex = this.hslToHex(this.secondaryHsl);
            this.accentHex = this.hslToHex(this.accentHsl);
        },

        updateHslFromHex(type) {
            let hex = this[type + 'Hex'];
            let hsl = this.hexToHsl(hex);
            this[type + 'Hsl'] = hsl;
        },

        updateHexFromHsl(type) {
            let hsl = this[type + 'Hsl'];
            let hex = this.hslToHex(hsl);
            if (hex) {
                this[type + 'Hex'] = hex;
            }
        },

        hexToHsl(hex) {
            hex = hex.replace('#', '');
            if (hex.length === 3) {
                hex = hex.split('').map(c => c + c).join('');
            }
            let r = parseInt(hex.substring(0, 2), 16) / 255;
            let g = parseInt(hex.substring(2, 4), 16) / 255;
            let b = parseInt(hex.substring(4, 6), 16) / 255;

            let max = Math.max(r, g, b), min = Math.min(r, g, b);
            let h, s, l = (max + min) / 2;

            if (max === min) {
                h = s = 0;
            } else {
                let d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                switch (max) {
                    case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                    case g: h = (b - r) / d + 2; break;
                    case b: h = (r - g) / d + 4; break;
                }
                h /= 6;
            }
            return Math.round(h * 360) + ' ' + Math.round(s * 100) + '% ' + Math.round(l * 100) + '%';
        },

        hslToHex(hslStr) {
            if (!hslStr) return '#2563eb';
            let parts = hslStr.replace(/%/g, '').split(' ');
            if (parts.length < 3) return '#2563eb';

            let h = parseFloat(parts[0]) / 360;
            let s = parseFloat(parts[1]) / 100;
            let l = parseFloat(parts[2]) / 100;

            if (isNaN(h) || isNaN(s) || isNaN(l)) return '#2563eb';

            let r, g, b;
            if (s === 0) {
                r = g = b = l;
            } else {
                const hue2rgb = (p, q, t) => {
                    if (t < 0) t += 1;
                    if (t > 1) t -= 1;
                    if (t < 1/6) return p + (q - p) * 6 * t;
                    if (t < 1/2) return q;
                    if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                    return p;
                };
                let q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                let p = 2 * l - q;
                r = hue2rgb(p, q, h + 1/3);
                g = hue2rgb(p, q, h);
                b = hue2rgb(p, q, h - 1/3);
            }

            const toHex = x => {
                const hex = Math.round(x * 255).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            };
            return '#' + toHex(r) + toHex(g) + toHex(b);
        }
    }
}
</script>
@endsection

@php($fontOptions = array_keys(\App\Services\Branding\AppearanceService::FONTS))
<section id="settings-appearance" class="tab-panel">
    <div class="card settings-card">
        <div class="settings-section-head"><div><h2><i class="fas fa-swatchbook"></i> Appearance</h2><p>Change how the whole platform looks: the public website, the youth portal and this admin area. Changes apply to everyone as soon as you save.</p></div></div>

        <h3 class="appearance-sub">Logo and favicon</h3>
        <div class="form-grid">
            <label>Logo<input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml"><small>Transparent PNG, WebP or SVG works best.</small></label>
            <label>Favicon<input type="file" name="favicon" accept="image/png,image/x-icon,image/svg+xml,image/webp"><small>A square image, at least 32 by 32 pixels. Shown in the browser tab.</small></label>
            <div class="brand-preview span-2">
                <span>Current logo and favicon</span>
                <div class="preview-row">
                    @if(!empty($settings['logo']))<div class="preview-box preview-logo"><img src="{{ asset('storage/'.$settings['logo']) }}" alt="Current logo"></div>@else<div class="preview-box preview-logo preview-empty"><i class="fas fa-image"></i><small>No logo</small></div>@endif
                    @if(!empty($settings['favicon']))<div class="preview-box preview-favicon"><img src="{{ route('pwa.favicon', ['v' => substr(sha1($settings['favicon']), 0, 10)]) }}" alt="Current favicon"></div>@else<div class="preview-box preview-favicon preview-empty"><i class="fas fa-star"></i></div>@endif
                </div>
            </div>
        </div>

        <div class="appearance-layout">
            <div>
                <h3 class="appearance-sub">Colours</h3>
                <div class="color-grid">
                    @foreach([
                        'primary_color' => ['Main brand colour', 'Buttons, links and highlights.'],
                        'secondary_color' => ['Second brand colour', 'Hover states and some accents.'],
                        'accent_color' => ['Accent colour', 'Badges, streaks and the highlighter under headings.'],
                        'background_color' => ['Page background', 'Behind every page. Keep it light.'],
                    ] as $field => [$label, $help])
                        <label class="color-field">
                            <input type="color" name="{{ $field }}" value="{{ old($field, $appearance[$field]) }}" data-preview>
                            <span><strong>{{ $label }}</strong><small>{{ $help }}</small></span>
                        </label>
                    @endforeach
                    <div class="color-field sidebar-field">
                        <input type="color" name="sidebar_color" value="{{ old('sidebar_color', $appearance['sidebar_color'] ?? '#F3EBDD') }}" data-preview aria-label="Sidebar background">
                        <span><strong>Sidebar background</strong><small>Used by the youth portal and admin sidebars. The text colour adjusts automatically so it stays readable.</small>
                            <label class="inline-check"><input type="checkbox" name="sidebar_default" value="1" data-preview @checked(old('sidebar_default', empty($appearance['sidebar_color'])))> Keep the default sidebars</label>
                        </span>
                    </div>
                </div>

                <h3 class="appearance-sub">Fonts</h3>
                <div class="form-grid">
                    <label>Headings
                        <select name="heading_font" data-preview>
                            @foreach($fontOptions as $font)<option value="{{ $font }}" @selected(old('heading_font', $appearance['heading_font']) === $font)>{{ $font }}</option>@endforeach
                        </select>
                    </label>
                    <label>Body text
                        <select name="body_font" data-preview>
                            @foreach($fontOptions as $font)<option value="{{ $font }}" @selected(old('body_font', $appearance['body_font']) === $font)>{{ $font }}</option>@endforeach
                        </select>
                    </label>
                </div>

                <h3 class="appearance-sub">Style</h3>
                <div class="style-options" role="radiogroup" aria-label="Style">
                    @foreach(\App\Services\Branding\AppearanceService::STYLES as $key => [$label, $help])
                        <label class="style-option">
                            <input type="radio" name="theme_style" value="{{ $key }}" data-preview @checked(old('theme_style', $appearance['theme_style']) === $key)>
                            <span class="style-swatch style-{{ $key }}" aria-hidden="true"></span>
                            <span><strong>{{ $label }}</strong><small>{{ $help }}</small></span>
                        </label>
                    @endforeach
                </div>

                <label class="inline-check reset-check"><input type="checkbox" name="reset_appearance" value="1"> Restore the default look when I save (colours, fonts, style and sidebar)</label>
            </div>

            <aside class="appearance-preview" aria-label="Live preview">
                <span class="preview-label">Live preview</span>
                <div class="ap-frame" id="appearancePreview">
                    <div class="ap-side"><i class="fas fa-house"></i><i class="fas fa-book-open"></i><i class="fas fa-ticket"></i><i class="fas fa-people-group"></i></div>
                    <div class="ap-main">
                        <h4>Good morning, <span class="ap-mark">Amina</span></h4>
                        <p>Here's what's good today.</p>
                        <div class="ap-card"><span class="ap-badge">3 day streak</span><strong>Walking with Jesus</strong><span class="ap-meter"><span></span></span></div>
                        <div class="ap-buttons"><span class="ap-btn ap-primary">Continue</span><span class="ap-btn">Later</span></div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
.appearance-sub{font-size:1rem;margin:22px 0 10px}
.appearance-layout{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:24px;align-items:start}
.color-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.color-field{display:flex;gap:12px;align-items:flex-start;padding:12px;border:1px solid var(--border);border-radius:12px;background:#fff;font-weight:400}
.color-field input[type=color]{width:44px!important;height:44px!important;min-height:0!important;padding:2px!important;border:1px solid var(--border);border-radius:10px;cursor:pointer;flex:0 0 44px}
.color-field input[type=color]:disabled{opacity:.35;cursor:not-allowed}
.color-field strong{display:block;font-size:.9rem}
.color-field small{display:block;color:var(--muted);margin-top:2px;line-height:1.35}
.sidebar-field{grid-column:span 2}
.inline-check{display:flex!important;align-items:center;gap:8px;margin-top:8px;font-weight:600;font-size:.85rem}
.inline-check input{width:18px!important;height:18px!important;min-height:0!important;margin:0}
.reset-check{margin-top:22px;padding:12px;border:1px dashed var(--border);border-radius:12px;color:var(--danger)}
.style-options{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
.style-option{position:relative;display:flex;flex-direction:column;gap:8px;padding:12px;border:1px solid var(--border);border-radius:12px;background:#fff;cursor:pointer;font-weight:400}
.style-option:has(input:checked){border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-light)}
.style-option:has(input:focus-visible){outline:3px solid var(--primary);outline-offset:2px}
.style-option input{position:absolute;opacity:0;pointer-events:none}
.style-option strong{display:block}
.style-option small{color:var(--muted);line-height:1.35}
.style-swatch{height:42px;background:#fff}
.style-playful{border:2px solid #1d1530;box-shadow:4px 4px 0 #1d1530;border-radius:14px}
.style-soft{border-radius:18px;box-shadow:0 8px 20px rgba(29,21,48,.14)}
.style-clean{border:1px solid #d9d4e3;border-radius:6px}
.appearance-preview{position:sticky;top:calc(var(--top-h) + 60px)}
.preview-label{display:block;font-size:.75rem;font-weight:800;color:#4b5563;margin-bottom:8px}
.ap-frame{--ap-primary:#4B2E83;--ap-accent:#FFC23D;--ap-bg:#FBF6EE;--ap-side:#F3EBDD;--ap-side-ink:#1D1530;--ap-head:'Bricolage Grotesque';--ap-body:'Figtree';--ap-radius:16px;--ap-edge:1.5px solid #1D1530;--ap-shadow:3px 3px 0 #1D1530;display:grid;grid-template-columns:44px 1fr;border:1px solid var(--border);border-radius:14px;overflow:hidden;background:var(--ap-bg);font-family:var(--ap-body),sans-serif;color:#1d1530;min-height:260px}
.ap-side{display:flex;flex-direction:column;align-items:center;gap:16px;padding:16px 0;background:var(--ap-side);color:var(--ap-side-ink)}
.ap-main{padding:16px}
.ap-main h4{font-family:var(--ap-head),sans-serif;font-size:1.25rem;margin:0 0 2px;letter-spacing:-.02em}
.ap-mark{background:linear-gradient(var(--ap-accent),var(--ap-accent)) 0 88%/100% .4em no-repeat}
.ap-main p{margin:0 0 12px;font-size:.85rem;color:#4a4160}
.ap-card{display:flex;flex-direction:column;gap:6px;padding:12px;background:#fff;border:var(--ap-edge);border-radius:var(--ap-radius);box-shadow:var(--ap-shadow)}
.ap-card strong{font-family:var(--ap-head),sans-serif}
.ap-badge{align-self:flex-start;padding:2px 8px;border-radius:999px;background:var(--ap-accent);font-size:.72rem;font-weight:800}
.ap-meter{height:7px;border-radius:9px;background:#eee}
.ap-meter span{display:block;width:45%;height:100%;border-radius:inherit;background:var(--ap-primary)}
.ap-buttons{display:flex;gap:8px;margin-top:12px}
.ap-btn{padding:7px 14px;border-radius:999px;border:var(--ap-edge);font-size:.8rem;font-weight:700;background:#fff}
.ap-primary{background:var(--ap-primary);border-color:var(--ap-primary);color:#fff}
@media(max-width:1100px){.appearance-layout{grid-template-columns:1fr}.appearance-preview{position:static;max-width:360px}}
@media(max-width:700px){.color-grid,.style-options{grid-template-columns:1fr}.sidebar-field{grid-column:auto}}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('siteSettingsForm');
    const frame = document.getElementById('appearancePreview');
    if (!form || !frame) return;
    const fonts = @json(\App\Services\Branding\AppearanceService::FONTS);
    const loaded = new Set();
    const loadFont = name => {
        if (!fonts[name] || loaded.has(name)) return;
        loaded.add(name);
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://fonts.googleapis.com/css2?family=' + fonts[name] + '&display=swap';
        document.head.appendChild(link);
    };
    // Same rule as AppearanceService::readableTextOn().
    const readable = hex => {
        const c = [1, 3, 5].map(i => {
            const v = parseInt(hex.substr(i, 2), 16) / 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
        });
        return (0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2]) > 0.4 ? '#1D1530' : '#FFFFFF';
    };
    const styles = {
        playful: ['16px', '1.5px solid #1D1530', '3px 3px 0 #1D1530'],
        soft: ['20px', '1px solid transparent', '0 8px 20px rgba(29,21,48,.12)'],
        clean: ['8px', '1px solid #E2DCEB', 'none'],
    };
    const field = name => form.elements[name];
    const set = (name, value) => frame.style.setProperty(name, value);
    const update = () => {
        set('--ap-primary', field('primary_color').value);
        set('--ap-accent', field('accent_color').value);
        set('--ap-bg', field('background_color').value);
        const keepDefault = field('sidebar_default').checked;
        field('sidebar_color').disabled = keepDefault;
        const side = keepDefault ? '#F3EBDD' : field('sidebar_color').value;
        set('--ap-side', side);
        set('--ap-side-ink', readable(side));
        const head = field('heading_font').value;
        const body = field('body_font').value;
        loadFont(head);
        loadFont(body);
        set('--ap-head', "'" + head + "'");
        set('--ap-body', "'" + body + "'");
        const checked = form.querySelector('input[name="theme_style"]:checked');
        const style = styles[checked ? checked.value : 'playful'];
        set('--ap-radius', style[0]);
        set('--ap-edge', style[1]);
        set('--ap-shadow', style[2]);
    };
    form.querySelectorAll('[data-preview]').forEach(el => {
        el.addEventListener('input', update);
        el.addEventListener('change', update);
    });
    update();
});
</script>

@php($brand = app(\App\Services\Branding\BrandingService::class)->data())
<div style="text-align:center;margin-bottom:22px">
    @if(!empty($brand['logo_url']))
        <img src="{{ $brand['logo_url'] }}" alt="{{ $brand['short_name'] }} logo" style="display:block;max-width:180px;max-height:88px;object-fit:contain;margin:0 auto 12px">
    @else
        <div style="width:68px;height:68px;border-radius:18px;background:#ede9fe;color:#4b2e83;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:30px">
            <i class="fas fa-church" aria-hidden="true"></i>
        </div>
    @endif
    <div style="font-size:24px;font-weight:800;line-height:1.25">{{ $brand['short_name'] }}</div>
    <div class="muted" style="margin-top:6px;line-height:1.5">{{ $brand['tagline'] }}</div>
</div>

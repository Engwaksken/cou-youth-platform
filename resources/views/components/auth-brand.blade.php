@php try { $authLogo = \App\Models\SiteSetting::get('logo'); $authName = \App\Models\SiteSetting::get('system_name','Church of Uganda Youth Platform'); } catch (\Throwable $e) { $authLogo=null; $authName='Church of Uganda Youth Platform'; } @endphp
<div style="text-align:center;margin-bottom:14px">
@if(!empty($authLogo))<img src="{{ asset('storage/'.$authLogo) }}" alt="{{ $authName }} logo" style="max-height:56px;max-width:220px;object-fit:contain">@else<p style="font-weight:900;color:#4b2e83;margin:0"><i class="fas fa-church" aria-hidden="true"></i> {{ $authName }}</p>@endif
</div>

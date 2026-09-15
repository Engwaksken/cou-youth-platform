<?php
namespace App\Http\Controllers\Api\V1; use App\Http\Controllers\Controller; use App\Models\PaymentGateway; use App\Services\Payments\PaymentWebhookService; use Illuminate\Http\Request;
class PaymentWebhookController extends Controller { public function handle(Request $r,string $gatewaySlug,PaymentWebhookService $service){$gateway=PaymentGateway::where('slug',$gatewaySlug)->where('is_enabled',true)->firstOrFail();$result=$service->process($gateway,$r->all(),$r->header('X-COU-Signature'));return response()->json(['success'=>$result['ok']],$result['status']);} }

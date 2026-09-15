<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PaymentWebhookLog extends Model { protected $guarded=[]; protected $casts=['signature_valid'=>'boolean','payload'=>'array']; }

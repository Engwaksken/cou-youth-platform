<?php
namespace App\Services\Audit;
use App\Models\AuditLog;
class AuditService { public function log(string $action,string $module,$record=null,?array $before=null,?array $after=null): void {
 AuditLog::create(['user_id'=>auth()->id(),'action'=>$action,'module'=>$module,'record_type'=>$record?get_class($record):null,'record_id'=>$record?->id,'before'=>$before,'after'=>$after,'ip_address'=>request()->ip(),'user_agent'=>request()->userAgent()]);
 } }

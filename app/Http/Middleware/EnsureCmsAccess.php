<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;
class EnsureCmsAccess { public function handle(Request $request, Closure $next){
 if(!$request->user()) return redirect()->route('login');
 $allowed=['super_admin','provincial_admin','diocesan_admin','archdeaconry_admin','parish_admin','local_church_admin','content_manager','events_manager','safeguarding_officer','finance_admin','donations_manager'];
 if(method_exists($request->user(),'hasAnyRole') && !$request->user()->hasAnyRole($allowed)) abort(403);
 return $next($request);
 } }

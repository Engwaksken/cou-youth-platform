<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller; use App\Models\OrganisationUnit; use Illuminate\Http\Request;
class OrganisationController extends Controller { public function index(Request $r){ $q=OrganisationUnit::query()->where('is_active',true)->orderBy('name'); if($r->filled('type'))$q->where('type',$r->string('type')); if($r->has('parent_id'))$q->where('parent_id',$r->input('parent_id')); return response()->json(['data'=>$q->get()]); } }

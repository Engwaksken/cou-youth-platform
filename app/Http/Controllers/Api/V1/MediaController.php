<?php
namespace App\Http\Controllers\Api\V1; use App\Http\Controllers\Controller; use App\Models\MediaAsset; use Illuminate\Http\Request;
class MediaController extends Controller {public function index(Request $r){$q=MediaAsset::where('is_published',true); if($r->filled('type')) $q->where('type',$r->string('type')); return response()->json($q->latest()->paginate(20));}}
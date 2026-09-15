<?php
namespace App\Http\Controllers\Api\V1; use App\Http\Controllers\Controller; use App\Models\CourseCertificate; use Illuminate\Http\Request;
class CertificateController extends Controller { public function index(Request $r){return response()->json(CourseCertificate::with('course:id,title')->where('user_id',$r->user()->id)->latest('issued_at')->paginate(20));} public function show(Request $r,CourseCertificate $certificate){abort_unless($certificate->user_id===$r->user()->id,403);return response()->json($certificate->load('course:id,title'));} }

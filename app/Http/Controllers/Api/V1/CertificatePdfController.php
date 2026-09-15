<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller; use App\Models\CourseCertificate; use Illuminate\Http\Request;
class CertificatePdfController extends Controller {
 public function show(Request $r,CourseCertificate $certificate){abort_unless($certificate->user_id===$r->user()->id,403);abort_unless(class_exists(\Barryvdh\DomPDF\Facade\Pdf::class),501,'PDF certificate dependency is not installed.');$certificate->load(['course:id,title','user:id,name']);$pdf=\Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.course',['certificate'=>$certificate])->setPaper('a4','landscape');return $pdf->download($certificate->certificate_number.'.pdf');}
}

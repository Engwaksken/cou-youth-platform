<?php
namespace App\Services\Certificates; use App\Models\{Course,CourseCertificate,User}; use Illuminate\Support\Str;
class CertificateService { public function issue(Course $course,User $user): CourseCertificate { return CourseCertificate::firstOrCreate(['course_id'=>$course->id,'user_id'=>$user->id],['uuid'=>(string)Str::uuid(),'certificate_number'=>'COU-'.now()->format('Y').'-'.str_pad((string)$course->id,4,'0',STR_PAD_LEFT).'-'.str_pad((string)$user->id,6,'0',STR_PAD_LEFT),'issued_at'=>now()]); } }

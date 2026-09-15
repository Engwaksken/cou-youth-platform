<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class CourseCertificate extends Model { protected $guarded=[]; protected $casts=['issued_at'=>'datetime']; public function course(){return $this->belongsTo(Course::class);} public function user(){return $this->belongsTo(User::class);} }

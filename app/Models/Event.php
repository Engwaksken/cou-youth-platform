<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Support\Str;
class Event extends Model {
    public function organisationUnit(){ return $this->belongsTo(OrganisationUnit::class); }
    protected $fillable=['organisation_unit_id','title','category','description','starts_at','ends_at','venue','theme','speaker','organizer','target_age_categories','registration_required','fee','currency','capacity','registration_deadline','poster_path','status','created_by','qr_token','allow_external_registration','external_registration_url'];
    protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime','registration_deadline'=>'datetime','target_age_categories'=>'array','registration_required'=>'boolean','allow_external_registration'=>'boolean','fee'=>'decimal:2'];
    protected static function boot(){ parent::boot(); static::creating(function($m){ if(empty($m->qr_token)){ $m->qr_token = Str::random(32); } }); }
    public function registrationUrl(){ return route('public.event.register', $this->qr_token); }
    public function registrations(){return $this->hasMany(EventRegistration::class);}
}

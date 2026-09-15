<?php
namespace App\Services\Safeguarding;
use Carbon\Carbon;
use InvalidArgumentException;
class AgeCategoryService { public function resolve(string $dateOfBirth): string { $age=Carbon::parse($dateOfBirth)->age; return match(true){$age>=12 && $age<=17=>'teen',$age>=18 && $age<=23=>'youth',$age>=24 && $age<=35=>'young_adult',default=>throw new InvalidArgumentException('Registration is currently available to ages 12–35.')}; } public function requiresGuardianConsent(string $category): bool { return $category==='teen'; } }

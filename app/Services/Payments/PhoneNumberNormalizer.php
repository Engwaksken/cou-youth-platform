<?php

declare(strict_types=1);

namespace App\Services\Payments;

use InvalidArgumentException;

final class PhoneNumberNormalizer
{
    public function normalise(string $phone, string $country = 'UG'): string
    {
        $digits = preg_replace('/\D+/', '', trim($phone)) ?? '';

        if ($digits === '') {
            throw new InvalidArgumentException('A valid mobile money phone number is required.');
        }

        $country = strtoupper(trim($country));

        if ($country === 'UG') {
            if (str_starts_with($digits, '256')) {
                $normalised = $digits;
            } elseif (str_starts_with($digits, '0') && strlen($digits) === 10) {
                $normalised = '256'.substr($digits, 1);
            } elseif (strlen($digits) === 9) {
                $normalised = '256'.$digits;
            } else {
                $normalised = $digits;
            }
        } else {
            $normalised = $digits;
        }

        if (strlen($normalised) < 8 || strlen($normalised) > 15) {
            throw new InvalidArgumentException('The mobile money phone number format is invalid.');
        }

        return $normalised;
    }
}

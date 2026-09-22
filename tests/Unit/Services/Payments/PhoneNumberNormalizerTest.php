<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Payments;

use App\Services\Payments\PhoneNumberNormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PhoneNumberNormalizerTest extends TestCase
{
    #[DataProvider('ugandaNumbers')]
    public function test_it_normalises_uganda_mobile_numbers(string $input, string $expected): void
    {
        $normalizer = new PhoneNumberNormalizer();

        $this->assertSame($expected, $normalizer->normalise($input, 'UG'));
    }

    public static function ugandaNumbers(): array
    {
        return [
            'local leading zero' => ['0772123456', '256772123456'],
            'local without zero' => ['772123456', '256772123456'],
            'international digits' => ['256772123456', '256772123456'],
            'international formatted' => ['+256 772 123 456', '256772123456'],
        ];
    }

    public function test_it_rejects_empty_or_unreasonably_short_numbers(): void
    {
        $normalizer = new PhoneNumberNormalizer();

        $this->expectException(InvalidArgumentException::class);
        $normalizer->normalise('123', 'UG');
    }
}

<?php

declare(strict_types=1);

namespace Hyra\IeCompaniesRegistrationOfficeLookup\Stubs;

final class BusinessNumberFaker
{
    public static function validBusinessNumber(): string
    {
        // @phpstan-ignore-next-line it can find an appropriate source of randomness
        return (string) \random_int(101, 99_999_998);
    }

    public static function invalidBusinessNumber(): string
    {
        return \sprintf('ABC%s', self::validBusinessNumber());
    }
}

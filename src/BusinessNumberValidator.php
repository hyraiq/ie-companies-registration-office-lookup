<?php

declare(strict_types=1);

namespace Hyra\IeCompaniesRegistrationOfficeLookup;

final class BusinessNumberValidator
{
    public static function isValidNumber(string $businessNumber): bool
    {
        // Replace whitespace
        $businessNumber = \preg_replace('/\s+/', '', $businessNumber);
        if (null === $businessNumber) {
            return false;
        }

        $isNumeric = \is_numeric($businessNumber);

        if (false === $isNumeric) {
            return false;
        }

        $businessNumberAsInt = (int) $businessNumber;

        return $businessNumberAsInt > 100 && $businessNumberAsInt < 99999999;
    }
}

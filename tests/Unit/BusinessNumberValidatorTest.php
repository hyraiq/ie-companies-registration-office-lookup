<?php

declare(strict_types=1);

namespace Unit;

use Hyra\IeCompaniesRegistrationOfficeLookup\BusinessNumberValidator;
use Hyra\IeCompaniesRegistrationOfficeLookup\Stubs\BusinessNumberFaker;
use PHPUnit\Framework\TestCase;

class BusinessNumberValidatorTest extends TestCase
{
    /**
     * @dataProvider getValidTests
     */
    public function testValidNumber(string $businessNumber): void
    {
        $result = BusinessNumberValidator::isValidNumber($businessNumber);

        static::assertTrue($result);
    }

    /**
     * @return mixed[]
     */
    public function getValidTests(): array
    {
        return [
            'less than 99999999' => ['99999998'],
            'more than 100'      => ['101'],
            'with spaces'        => ['83 740 '],
            'random 1'           => ['83740'],
            'random 2'           => ['520644'],
        ];
    }

    /**
     * @dataProvider getInvalidTests
     */
    public function testInvalidNumber(string $businessNumber): void
    {
        $result = BusinessNumberValidator::isValidNumber($businessNumber);

        static::assertFalse($result);
    }

    /**
     * @return mixed[]
     */
    public function getInvalidTests(): array
    {
        return [
            'more than 99999999'   => ['999999999'],
            'equal to 99999999'    => ['99999999'],
            'less than 100'        => ['99'],
            'equal to 100'         => ['100'],
            'invalid with letters' => ['ABC520644'],
            'not a number'         => ['invalid'],
            'not an integer'       => ['100.450'],
        ];
    }

    public function testRandomValidNumbers(): void
    {
        for ($i = 0; $i < 100; ++$i) {
            $businessNumber = BusinessNumberFaker::validBusinessNumber();
            $result         = BusinessNumberValidator::isValidNumber($businessNumber);

            static::assertTrue($result, \sprintf('The number %s is not valid', $businessNumber));
        }
    }

    public function testRandomInvalidNumbers(): void
    {
        for ($i = 0; $i < 100; ++$i) {
            $businessNumber = BusinessNumberFaker::invalidBusinessNumber();
            $result         = BusinessNumberValidator::isValidNumber($businessNumber);

            static::assertFalse($result, \sprintf('The number %s is not invalid', $businessNumber));
        }
    }
}

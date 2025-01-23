<?php

declare(strict_types=1);

namespace Hyra\Tests\IeCompaniesRegistrationOfficeLookup\Model;

use Hyra\IeCompaniesRegistrationOfficeLookup\Model\IeCompanyResponse;

final class IeCompanyResponseTest extends BaseModelTest
{
    public function testValidModel(): void
    {
        $data = $this->getValidResponse();

        $parsed = $this->valid($data, IeCompanyResponse::class);

        static::assertSame(83740, $parsed->companyNumber);
        static::assertSame('RYANAIR', $parsed->companyName);
        static::assertSame(1132, $parsed->typeCode);
        static::assertSame('Business name - Body Corporate', $parsed->typeDescription);
        static::assertSame('Normal', $parsed->getStatus());
        static::assertSame(1101, $parsed->statusCode);
        static::assertSame('1985-02-12', $parsed->statusDate->format('Y-m-d'));
        static::assertSame('1985-02-12', $parsed->registrationDate->format('Y-m-d'));

        // Additional dates
        static::assertSame('0001-01-01', $parsed->lastAnnualReturnDate?->format('Y-m-d'));
        static::assertSame('0001-01-01', $parsed->nextAnnualReturnDate?->format('Y-m-d'));
        static::assertSame('0001-01-01', $parsed->lastAccountingYearDate?->format('Y-m-d'));

        // Address fileds
        static::assertSame('SHANNON AIRPORT', $parsed->addressLine1);
        static::assertSame('SHANNON, CLARE, V14VY39', $parsed->addressLine2);
        static::assertSame('', $parsed->addressLine3);
        static::assertSame('', $parsed->addressLine4);
        static::assertSame('V14VY39', $parsed->eirCode);
        static::assertSame('', $parsed->placeOfBusiness);
    }

    /**
     * @dataProvider getInValidTests
     *
     * @param string[] $keys
     */
    public function testInvalidModel(array $keys): void
    {
        $data = $this->getValidResponse();

        foreach ($keys as $key) {
            $data = $this->removeProperty($data, $key);
        }

        $this->invalid($data, IeCompanyResponse::class);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getInValidTests(): array
    {
        return [
            'missing name'             => [['company_name']],
            'missing companyNumber'    => [['company_num']],
            'missing indicator'        => [['company_bus_ind']],
            'missing typeDescription'  => [['comp_type_desc']],
            'missing typeCode'         => [['company_type_code']],
            'missing status'           => [['company_status_desc']],
            'missing statusCode'       => [['company_status_code']],
            'missing statusDate'       => [['company_status_date']],
            'missing registrationDate' => [['company_reg_date']],
            'missing addressLine1'     => [['company_addr_1']],
            'missing addressLine2'     => [['company_addr_2']],
            'missing addressLine3'     => [['company_addr_3']],
            'missing addressLine4'     => [['company_addr_4']],
            'missing placeOfBusiness'  => [['place_of_business']],
            'missing eirCode'          => [['eircode']],
        ];
    }

    private function removeProperty(string $data, string $key): string
    {
        /** @var mixed[] $decoded */
        $decoded = \json_decode($data, true);

        unset($decoded[$key]);

        return \json_encode($decoded, \JSON_THROW_ON_ERROR);
    }

    private function getValidResponse(): string
    {
        return <<<JSON
        {
          "company_num": 83740,
          "company_bus_ind": "b",
          "company_name": "RYANAIR",
          "company_addr_1": "SHANNON AIRPORT",
          "company_addr_2": "SHANNON, CLARE, V14VY39",
          "company_addr_3": "",
          "company_addr_4": "",
          "company_reg_date": "1985-02-12T00:00:00Z",
          "company_status_desc": "Normal ",
          "company_status_date": "1985-02-12T00:00:00Z",
          "last_ar_date": "0001-01-01T00:00:00Z",
          "next_ar_date": "0001-01-01T00:00:00Z",
          "last_acc_date": "0001-01-01T00:00:00Z",
          "comp_type_desc": "Business name - Body Corporate",
          "company_type_code": 1132,
          "company_status_code": 1101,
          "place_of_business": "",
          "eircode": "V14VY39"
        }
        JSON;
    }
}

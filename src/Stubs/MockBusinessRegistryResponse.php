<?php

declare(strict_types=1);

namespace Hyra\IeCompaniesRegistrationOfficeLookup\Stubs;

final class MockBusinessRegistryResponse
{
    /**
     * @return array<string, mixed>
     */
    public static function valid(): array
    {
        return [
            'company_num'         => 83740,
            'company_bus_ind'     => 'b',
            'company_name'        => 'RYANAIR',
            'company_addr_1'      => 'SHANNON AIRPORT',
            'company_addr_2'      => 'SHANNON, CLARE, V14VY39',
            'company_addr_3'      => '',
            'company_addr_4'      => '',
            'company_reg_date'    => '1985-02-12T00:00:00Z',
            'company_status_desc' => 'Normal ',
            'company_status_date' => '1985-02-12T00:00:00Z',
            'last_ar_date'        => '0001-01-01T00:00:00Z',
            'next_ar_date'        => '0001-01-01T00:00:00Z',
            'last_acc_date'       => '0001-01-01T00:00:00Z',
            'comp_type_desc'      => 'Business name - Body Corporate',
            'company_type_code'   => 1132,
            'company_status_code' => 1101,
            'place_of_business'   => '',
            'eircode'             => 'V14VY39',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function noBusinessNumberFound(): array
    {
        return [];
    }
}

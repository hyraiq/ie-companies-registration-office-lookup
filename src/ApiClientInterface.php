<?php

declare(strict_types=1);

namespace Hyra\IeCompaniesRegistrationOfficeLookup;

use Hyra\IeCompaniesRegistrationOfficeLookup\Enum\CompanyBusinessIndicator;
use Hyra\IeCompaniesRegistrationOfficeLookup\Exception\ConnectionException;
use Hyra\IeCompaniesRegistrationOfficeLookup\Exception\NumberInvalidException;
use Hyra\IeCompaniesRegistrationOfficeLookup\Exception\NumberNotFoundException;
use Hyra\IeCompaniesRegistrationOfficeLookup\Model\IeCompanyResponse;

interface ApiClientInterface
{
    /**
     * @throws NumberInvalidException
     * @throws ConnectionException
     * @throws NumberNotFoundException
     */
    public function lookupNumber(string $businessNumber, CompanyBusinessIndicator $indicator): IeCompanyResponse;
}

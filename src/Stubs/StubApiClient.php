<?php

declare(strict_types=1);

namespace Hyra\IeCompaniesRegistrationOfficeLookup\Stubs;

use Hyra\IeCompaniesRegistrationOfficeLookup\ApiClientInterface;
use Hyra\IeCompaniesRegistrationOfficeLookup\BusinessNumberValidator;
use Hyra\IeCompaniesRegistrationOfficeLookup\Enum\CompanyBusinessIndicator;
use Hyra\IeCompaniesRegistrationOfficeLookup\Exception\NumberInvalidException;
use Hyra\IeCompaniesRegistrationOfficeLookup\Exception\NumberNotFoundException;
use Hyra\IeCompaniesRegistrationOfficeLookup\Model\IeCompanyResponse;

final class StubApiClient implements ApiClientInterface
{
    /** @var array<int, IeCompanyResponse> */
    private array $companyResponses = [];

    /** @var string[] */
    private array $notFoundBusinessNumbers = [];

    public function lookupNumber(string $businessNumber, CompanyBusinessIndicator $indicator): IeCompanyResponse
    {
        if (false === BusinessNumberValidator::isValidNumber($businessNumber)) {
            throw new NumberInvalidException();
        }

        if (\array_key_exists((int) $businessNumber, $this->companyResponses)) {
            return $this->companyResponses[(int) $businessNumber];
        }

        if (\in_array($businessNumber, $this->notFoundBusinessNumbers, true)) {
            throw new NumberNotFoundException();
        }

        throw new \LogicException(
            'Make sure you set a stub response for the business number before calling the ApiClient'
        );
    }

    public function addMockResponse(IeCompanyResponse ...$companyResponse): void
    {
        foreach ($companyResponse as $response) {
            $this->companyResponses[$response->companyNumber] = $response;
        }
    }

    public function addNotFoundBusinessNumbers(string ...$businessNumbers): void
    {
        $this->notFoundBusinessNumbers = \array_merge(
            $this->notFoundBusinessNumbers,
            $businessNumbers,
        );
    }
}

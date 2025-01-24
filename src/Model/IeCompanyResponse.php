<?php

declare(strict_types=1);

namespace Hyra\IeCompaniesRegistrationOfficeLookup\Model;

use Hyra\IeCompaniesRegistrationOfficeLookup\Enum\CompanyBusinessIndicator;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

final class IeCompanyResponse extends AbstractResponse
{
    #[SerializedName('company_name')]
    #[NotBlank]
    public string $companyName;

    #[SerializedName('company_num')]
    #[NotBlank]
    public int $companyNumber;

    #[SerializedName('company_bus_ind')]
    #[NotBlank]
    public CompanyBusinessIndicator $indicator;

    #[SerializedName('company_reg_date')]
    #[NotBlank]
    public \DateTimeImmutable $registrationDate;

    #[SerializedName('company_status_desc')]
    #[NotBlank]
    public string $status;

    #[SerializedName('company_status_code')]
    #[NotBlank]
    public int $statusCode;

    #[SerializedName('company_status_date')]
    #[NotBlank]
    public \DateTimeImmutable $statusDate;

    #[SerializedName('comp_type_desc')]
    #[NotBlank]
    public string $typeDescription;

    #[SerializedName('company_type_code')]
    #[NotBlank]
    public int $typeCode;

    #[SerializedName('company_addr_1')]
    #[NotNull]
    public string $addressLine1;

    #[SerializedName('company_addr_2')]
    #[NotNull]
    public string $addressLine2;

    #[SerializedName('company_addr_3')]
    #[NotNull]
    public string $addressLine3;

    #[SerializedName('company_addr_4')]
    #[NotNull]
    public string $addressLine4;

    #[SerializedName('place_of_business')]
    #[NotNull]
    public string $placeOfBusiness;

    #[SerializedName('eircode')]
    #[NotNull]
    public string $eirCode;

    // These dates may not be returned when calling the /companies endpoint

    #[SerializedName('last_ar_date')]
    public ?\DateTimeImmutable $lastAnnualReturnDate = null;

    #[SerializedName('next_ar_date')]
    public ?\DateTimeImmutable $nextAnnualReturnDate = null;

    #[SerializedName('last_acc_date')]
    public ?\DateTimeImmutable $lastAccountingYearDate = null;

    public function getStatus(): string
    {
        return mb_trim($this->status);
    }

    public function isActive(): bool
    {
        return 'normal' === \mb_strtolower($this->getStatus());
    }
}

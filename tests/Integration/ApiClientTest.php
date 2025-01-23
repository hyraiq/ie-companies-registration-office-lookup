<?php

declare(strict_types=1);

namespace Integration;

use Faker\Factory;
use Faker\Generator;
use Hyra\IeCompaniesRegistrationOfficeLookup\ApiClient;
use Hyra\IeCompaniesRegistrationOfficeLookup\Dependencies;
use Hyra\IeCompaniesRegistrationOfficeLookup\Enum\CompanyBusinessIndicator;
use Hyra\IeCompaniesRegistrationOfficeLookup\Exception\ConnectionException;
use Hyra\IeCompaniesRegistrationOfficeLookup\Exception\NumberInvalidException;
use Hyra\IeCompaniesRegistrationOfficeLookup\Exception\NumberNotFoundException;
use Hyra\IeCompaniesRegistrationOfficeLookup\Exception\UnexpectedResponseException;
use Hyra\IeCompaniesRegistrationOfficeLookup\Stubs\MockBusinessRegistryResponse;
use Hyra\IeCompaniesRegistrationOfficeLookup\Stubs\StubHttpClient;
use PHPUnit\Framework\TestCase;

final class ApiClientTest extends TestCase
{
    private const BusinessNumber = '52064';

    private const Indicator = CompanyBusinessIndicator::Company;

    private Generator $faker;

    private ApiClient $client;

    private StubHttpClient $stubHttpClient;

    private string $email;

    private string $apiKey;

    protected function setUp(): void
    {
        $this->faker          = Factory::create();
        $denormalizer         = Dependencies::serializer();
        $validator            = Dependencies::validator();
        $this->stubHttpClient = new StubHttpClient();

        $this->email  = 'test@test.com';
        $this->apiKey = $this->faker->uuid;

        $this->client = new ApiClient($denormalizer, $validator, $this->stubHttpClient, $this->email, $this->apiKey);
    }

    /**
     * Yes, this is a bad test. It just reimplements logic in ApiClient. However, we want to ensure the defaults
     * don't change.
     */
    public function testClientInitialisedCorrectly(): void
    {
        $authKey = \base64_encode(\sprintf('%s:%s', $this->email, $this->apiKey));

        $this->stubHttpClient->assertDefaultOptions([
            'base_uri' => 'https://services.cro.ie/cws/',
            'headers'  => [
                'Content-type: application/json',
                'Accept: application/json',
                \sprintf('Authorization: Basic %s', $authKey),
            ],
        ]);
    }

    public function testLookupNumberInvalidNumberDoesNotUseApi(): void
    {
        $this->expectException(NumberInvalidException::class);

        $this->client->lookupNumber($this->faker->numerify('ABC###'), self::Indicator);

        $this->stubHttpClient->assertCompanyEndpointNotCalled();
    }

    public function testLookupNumberConnectionExceptionOnServerErrorResponse(): void
    {
        $this->stubHttpClient->setStubResponse([], 500);

        $this->expectException(ConnectionException::class);

        $this->client->lookupNumber(self::BusinessNumber, self::Indicator);
    }

    public function testLookupNumberWhenNumberNotFound(): void
    {
        // The API seems to return a 520 status code from Cloudflare when no result is found
        $this->stubHttpClient->setStubResponse(MockBusinessRegistryResponse::noBusinessNumberFound(), 520);

        $this->expectException(NumberNotFoundException::class);

        $this->client->lookupNumber(self::BusinessNumber, self::Indicator);
    }

    public function testLookupNumberWithInvalidBusinessNumber(): void
    {
        $this->expectException(NumberInvalidException::class);

        $this->client->lookupNumber('invalid', self::Indicator);
    }

    public function testLookupNumberHandlesUnexpectedResponse(): void
    {
        $response                = MockBusinessRegistryResponse::valid();
        $response['company_num'] = null;
        $this->stubHttpClient->setStubResponse($response);

        $this->expectException(UnexpectedResponseException::class);

        $this->client->lookupNumber(self::BusinessNumber, self::Indicator);
    }

    public function testLookupNumberSuccess(): void
    {
        /** @var array{addresses: array{addressList: mixed[]}} $mockResponse */
        $mockResponse = MockBusinessRegistryResponse::valid();

        $this->stubHttpClient->setStubResponse($mockResponse);

        $response = $this->client->lookupNumber(self::BusinessNumber, self::Indicator);

        $normalizedResponse = [
            'companyName'            => $response->companyName,
            'companyNumber'          => $response->companyNumber,
            'indicator'              => $response->indicator->value,
            'registrationDate'       => $response->registrationDate->format('Y-m-d\TH:i:s\Z'),
            'status'                 => $response->status,
            'statusCode'             => $response->statusCode,
            'statusDate'             => $response->statusDate->format('Y-m-d\TH:i:s\Z'),
            'typeDescription'        => $response->typeDescription,
            'typeCode'               => $response->typeCode,
            'addressLine1'           => $response->addressLine1,
            'addressLine2'           => $response->addressLine2,
            'addressLine3'           => $response->addressLine3,
            'addressLine4'           => $response->addressLine4,
            'placeOfBusiness'        => $response->placeOfBusiness,
            'eircode'                => $response->eirCode,
            'lastAnnualReturnDate'   => $response->lastAnnualReturnDate?->format('Y-m-d\TH:i:s\Z'),
            'nextAnnualReturnDate'   => $response->nextAnnualReturnDate?->format('Y-m-d\TH:i:s\Z'),
            'lastAccountingYearDate' => $response->lastAccountingYearDate?->format('Y-m-d\TH:i:s\Z'),
        ];

        $this->stubHttpClient->assertCompanyEndpointCalled([]);

        static::assertEqualsCanonicalizing($mockResponse, $normalizedResponse);
        static::assertTrue($response->isActive());
    }
}

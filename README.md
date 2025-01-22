hyraiq/ie-companies-registration-office-lookup
================

A PHP SDK to validate Irish Business Numbers and verify them with the
[Irish Companies Registration Office (CRO) Public Data API](https://services.cro.ie/index.aspx).

The difference between validation and verification can be outlined as follows:

- Validation uses a regular expression to check that a given number is a valid Irish business number. This _does not_ contact the API to
  ensure that the given number is assigned to a business
- Verification contacts the Companies Registration Office through their API to retrieve information registered against the entity number. It
  will tell you if the number actually belongs to a business.

In order to use the API (only necessary for verification), you'll need to
[register an account](https://services.cro.ie/overview.aspx) to receive an API key.


## Type safety

The SDK utilises the [Symfony Serializer](https://symfony.com/doc/current/components/serializer.html) and the
[Symfony Validator](https://symfony.com/doc/current/components/validator.html) to deserialize and validate data returned
from the API in order to provide a valid [IeCompanyResponse](./src/Model/IeCompanyResponse.php) model.
This means that if you receive a response from the SDK, it is guaranteed to be valid.

Invalid responses from the API fall into three categories, which are handled with exceptions:

- `ConnectionException.php`: Unable to connect to the API, or the API returned an unexpected response
- `NumberInvalidException.php`: The entity number is invalid (i.e. validation failed)
- `NumberNotFoundException.php`: The entity number is valid, however it is not assigned to a business (i.e. verification failed)


## Usage

### Installation

```shell
$ composer require hyraiq/ie-companies-registration-office-lookup
```

### Configuration with Symfony

In `services.yaml`, you need to pass your CRO API key and associated email address to the `ApiClient` and register the `ApiClient` with the
`ApiClientInterface`:

```yaml
Hyra\IeCompaniesRegistrationOfficeLookup\ApiClientInterface: '@Hyra\IeCompaniesRegistrationOfficeLookup\ApiClient'
Hyra\IeCompaniesRegistrationOfficeLookup\ApiClient:
    arguments:
        $email: "%env(IE_COMPANIES_REGISTRATION_OFFICE_API_KEY)%"
        $apiKey: "%env(IE_COMPANIES_REGISTRATION_OFFICE_EMAIL)%"
```

You can then inject the `ApiClientInterface` directly into your controllers/services.

```php
class VerifyController extends AbtractController
{
    public function __construct(
        private ApiClientInterface $apiClient,
    ) {
    }
    
    // ...  
}
```

You also need to add the custom address denormalizer to the `services.yaml`:

```yaml
Hyra\IeCompaniesRegistrationOfficeLookup\Model\AddressDenormalizer: ~
```

### Configuration outside Symfony

If you're not using Symfony, you'll need to instantiate the API client yourself, which can be registered in your service
container or just used directly. We have provided some helpers in the `Dependencies` class in order to create the
Symfony Serializer and Validator with minimal options.

```php
use Hyra\IeCompaniesRegistrationOfficeLookup\Dependencies;
use Hyra\IeCompaniesRegistrationOfficeLookup\ApiClient;

$email = '<insert your email address here>'
$apiKey = '<insert your API key here>'

// Whichever http client you choose
$httpClient = new HttpClient();

$denormalizer = Dependencies::serializer();
$validator = Dependencies::validator();

$apiClient = new ApiClient($denormalizer, $validator, $httpClient, $apiKey, $email);
```

### Looking up a business number

Once you have configured your `ApiClient` you can look up an individual business numbers. Note, this will validate the number before
calling the API in order to prevent unnecessary API requests.

```php
$number = '9429032389470';

try {
    $response = $apiClient->lookupNumber($number);
} catch (ConnectionException $e) {
    die($e->getMessage())
} catch (NumberInvalidException) {
    die('Invalid business number');
} catch (NumberNotFoundException) {
    die('Business number not found');
}

echo $response->companyNumber; // 9429032389470
echo $response->entityName; // BURGER FUEL LIMITED
echo $response->status; // Registered
```


## Testing

In automated tests, you can replace the `ApiClient` with the `StubApiClient` in order to mock responses from the API.
There is also the `BusinessNumberFaker` which you can use during tests to get both valid and invalid business numbers.

```php
use Hyra\IeCompaniesRegistrationOfficeLookup\Stubs\BusinessNumberFaker;
use Hyra\IeCompaniesRegistrationOfficeLookup\Stubs\StubApiClient;
use Hyra\IeCompaniesRegistrationOfficeLookup\Stubs\MockBusinessRegistryResponse;

$stubClient = new StubApiClient();

$stubClient->lookupNumber(BusinessNumberFaker::invalidBusinessNumber()); // NumberInvalidException - Note, the stub still uses the validator

$stubClient->lookupNumber(BusinessNumberFaker::validBusinessNumber()); // LogicException - You need to tell the stub how to respond to specific queries

$businessNumber = BusinessNumberFaker::validBusinessNumber();
$stubClient->addNotFoundBusinessNumbers($businessNumber);
$stubClient->lookupNumber($businessNumber); // NumberNotFoundException

$businessNumber = BusinessNumberFaker::validBusinessNumber();
$mockResponse = MockBusinessRegistryResponse::valid();
$mockResponse->businessNumber = $businessNumber;

$stubClient->addMockResponse($mockResponse);
$response = $stubClient->lookupNumber($businessNumber); // $response === $mockResponse
```


## Contributing

All contributions are welcome! You'll need [docker](https://docs.docker.com/engine/install/) installed in order to
run tests and CI processes locally. These will also be run against your pull request with any failures added as
GitHub annotations in the Files view.

```shell
# First build the required docker container
$ docker compose build

# Then you can install composer dependencies
$ docker compose run php ./composer.phar install

# Now you can run tests and other tools
$ docker compose run php make (fix|psalm|phpstan|phpunit)
```

In order for you PR to be accepted, it will need to be covered by tests and be accepted by:

- [php-cs-fixer](https://github.com/FriendsOfPhp/PHP-CS-Fixer)
- [psalm](https://github.com/vimeo/psalm/)
- [phpstan](https://github.com/phpstan/phpstan)

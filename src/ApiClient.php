<?php

declare(strict_types=1);

namespace Hyra\IeCompaniesRegistrationOfficeLookup;

use Hyra\IeCompaniesRegistrationOfficeLookup\Enum\CompanyBusinessIndicator;
use Hyra\IeCompaniesRegistrationOfficeLookup\Exception\ConnectionException;
use Hyra\IeCompaniesRegistrationOfficeLookup\Exception\NumberInvalidException;
use Hyra\IeCompaniesRegistrationOfficeLookup\Exception\NumberNotFoundException;
use Hyra\IeCompaniesRegistrationOfficeLookup\Exception\UnexpectedResponseException;
use Hyra\IeCompaniesRegistrationOfficeLookup\Model\AbstractResponse;
use Hyra\IeCompaniesRegistrationOfficeLookup\Model\IeCompanyResponse;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ApiClient implements ApiClientInterface
{
    private HttpClientInterface $client;

    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly ValidatorInterface $validator,
        HttpClientInterface $client,
        string $email,
        string $apiKey,
    ) {
        $authKey = \base64_encode(\sprintf('%s:%s', $email, $apiKey));

        $this->client = $client->withOptions([
            'base_uri' => 'https://services.cro.ie/cws/',
            'headers'  => [
                'Content-type: application/json',
                'Accept: application/json',
                \sprintf('Authorization: Basic %s', $authKey),
            ],
        ]);
    }

    public function lookupNumber(string $businessNumber, CompanyBusinessIndicator $indicator): IeCompanyResponse
    {
        if (false === BusinessNumberValidator::isValidNumber($businessNumber)) {
            throw new NumberInvalidException();
        }

        try {
            $url      = \sprintf('company/%s/%s', $businessNumber, $indicator->value);
            $response = $this->client->request('GET', $url)->getContent();
        } catch (HttpExceptionInterface | TransportExceptionInterface $e) {
            if (520 === $e->getCode()) {
                throw new NumberNotFoundException();
            }

            throw new ConnectionException(
                \sprintf('Unable to connect to the CRO API: %s', $e->getMessage()),
                $e
            );
        }

        /** @var IeCompanyResponse $model */
        $model = $this->decodeResponse($response, IeCompanyResponse::class);

        return $model;
    }

    /**
     * @template T of AbstractResponse
     *
     * @psalm-param    class-string<T> $type
     *
     * @psalm-return   T
     *
     * @throws UnexpectedResponseException
     */
    private function decodeResponse(string $response, string $type): object
    {
        try {
            /** @psalm-var T $model */
            $model = $this->denormalizer->denormalize(\json_decode($response, true), $type, 'json');
        } catch (SerializerExceptionInterface $e) {
            throw new UnexpectedResponseException(
                \sprintf('Unable to deserialize response "%s": %s', $response, $e->getMessage()),
                $e
            );
        }

        $violations = $this->validator->validate($model);

        if (0 < \count($violations)) {
            $errors = \array_map(
                static fn (ConstraintViolationInterface $violation) => $violation->getPropertyPath(),
                \iterator_to_array($violations)
            );

            throw new UnexpectedResponseException(
                \sprintf('Response contains errors "%s": %s', $response, \json_encode($errors))
            );
        }

        return $model;
    }
}

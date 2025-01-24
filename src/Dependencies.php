<?php

declare(strict_types=1);

namespace Hyra\IeCompaniesRegistrationOfficeLookup;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Dependencies
{
    public static function serializer(): Serializer
    {
        $classMetadataFactory       = new ClassMetadataFactory(new AttributeLoader());
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $propertyAccessor           = new PropertyAccessor();

        $propertyExtractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);

        $objectNormalizer = new ObjectNormalizer(
            $classMetadataFactory,
            $metadataAwareNameConverter,
            $propertyAccessor,
            $propertyExtractor,
        );

        $dateTimeDenormalizer = new DateTimeNormalizer();
        $arrayDenormalizer    = new ArrayDenormalizer();
        $enumDenormalizer     = new BackedEnumNormalizer();

        return new Serializer(
            [
                $enumDenormalizer,
                $dateTimeDenormalizer,
                $objectNormalizer,
                $arrayDenormalizer,
            ],
            [
                'json' => new JsonEncoder(),
            ]
        );
    }

    public static function validator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
        ;
    }
}

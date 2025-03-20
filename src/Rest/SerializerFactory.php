<?php

namespace AiBundle\Rest;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerFactory {

  public function __invoke(): SerializerInterface {
    $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
    return new Serializer(
      [
        new ObjectNormalizer(
          nameConverter: new MetadataAwareNameConverter($classMetadataFactory, null),
          propertyTypeExtractor: new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()])
        ),
        new ArrayDenormalizer()
      ],
      [
        new JsonEncoder()
      ]
    );
  }

}

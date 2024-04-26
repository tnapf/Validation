<?php

namespace Tnapf\Validation;

use Tnapf\JsonMapper\Attributes\SnakeToCamelCase;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * Converts a string from camelCase to slug format (e.g. "helloWorld" -> "hello_world")
 */
function camelCaseToSlugFormat(string $name): string
{
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
}

function getValidators(string $class): array
{
    $reflection = (new ReflectionClass($class));
    $properties = $reflection->getProperties();

    $validators = [];
    $transferKey = (count($reflection->getAttributes(SnakeToCamelCase::class, ReflectionAttribute::IS_INSTANCEOF)) > 0);

    foreach ($properties as $property) {
        $key = $transferKey ?
            camelCaseToSlugFormat($property->getName()) :
            $property->getName();

        $validators[$key] = array_map(
            static fn (ReflectionAttribute $attribute) => $attribute->newInstance(),
            $property->getAttributes(Validator::class, ReflectionAttribute::IS_INSTANCEOF)
        );
    }

    return $validators;
}

/**
 * @throws ReflectionException
 */
function validateModel(object|string $model, array $data): array
{
    if (is_string($model)) {
        $model = new $model();
    }

    $reflection = (new ReflectionClass($model));
    $properties = $reflection->getProperties();

    $errors = [];
    $transferKey = (count($reflection->getAttributes(SnakeToCamelCase::class, ReflectionAttribute::IS_INSTANCEOF)) > 0);

    foreach ($properties as $property) {
        $validators = $property->getAttributes(Validator::class, ReflectionAttribute::IS_INSTANCEOF);

        foreach ($validators as $validator) {
            $validator = $validator->newInstance();
            /** @var Validator $validator */
            $key = $transferKey ?
                camelCaseToSlugFormat($property->getName()) :
                $property->getName();
            $value = is_object($model) && isset($model->{$property->getName()}) ? $model->{$property->getName()} : $data[$key] ?? null;

            try {
                $validator->validate($value, static function (string $message) use (&$errors, $key) {
                    $errors[$key] ??= [];
                    $errors[$key][] = $message;
                });
            } catch (Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }
    }

    return $errors;
}

function validateArray(array $body, array $data): array
{
    $errors = [];

    foreach ($body as $property => $validators) {
        /** @var Validator $validator */
        foreach ($validators as $validator) {
            if (!isset($data[$property])) {
                $data[$property] = null;
            }

            try {
                $validator->validate($data[$property], static function (string $message) use (&$errors, $property) {
                    $errors[$property] ??= [];
                    $errors[$property][] = $message;
                });
            } catch (Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }
    }

    return $errors;
}

<?php

declare(strict_types=1);

namespace App\DBAL\TypedFieldMapper;

use App\DBAL\Types\SQLEnumTypeInterface;
use Doctrine\ORM\Mapping\TypedFieldMapper;
use ReflectionNamedType;
use ReflectionProperty;

class AbstractEnumMapper implements TypedFieldMapper
{
    /**
     * @param array<string, mixed> $mapping
     */
    public function validateAndComplete(array $mapping, ReflectionProperty $field): array
    {
        $type = $field->getType();
        if (
            ! isset($mapping['type'])
            && ($type instanceof ReflectionNamedType)
        ) {
            if (!$type->isBuiltin() && enum_exists($type->getName()) && is_subclass_of($type->getName(), SQLEnumTypeInterface::class)) {
                $mapping['type'] = $type->getName();
            }
        }

        return $mapping;
    }
}

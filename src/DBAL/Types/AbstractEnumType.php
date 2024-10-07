<?php

declare(strict_types=1);

namespace App\DBAL\Types;

use BackedEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Types\Type;
use LogicException;

abstract class AbstractEnumType extends Type
{
    /** @return class-string<SQLEnumTypeInterface> */
    abstract public static function getEnumClass(): string;

    public function getName(): string // the name of the type.
    {
        return static::getEnumClass();
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $class = static::getEnumClass();

        if (!is_a($class, SQLEnumTypeInterface::class, true)) {
            throw new LogicException(sprintf('%s must be of %s type', $class, SQLEnumTypeInterface::class));
        }

        $values = [];

        foreach ($class::cases() as $val) {
            if (!$val->getExcludeFromSQLEnumDeclaration()) {
                $values[] = "'{$val->value}'";
            }
        }

        if ($platform instanceof SqlitePlatform) {
            return 'TEXT CHECK( ' . $column['name'] . ' IN (' . implode(', ', $values) . ') )';
        }

        return 'enum(' . implode(', ', $values) . ')';
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if ($value !== null && !($value instanceof BackedEnum)) {
            /** @var SQLEnumTypeInterface $class */
            $class = static::getEnumClass();
            $value = $class::tryFrom($value);
        }

        if ($value instanceof SQLEnumTypeDefaultForNullInterface && $value === $value::getDefaultForNull()) {
            return null;
        }

        if ($value instanceof SQLEnumTypeInterface) {
            if ($value->getExcludeFromSQLEnumDeclaration()) {
                throw new LogicException(sprintf('%s is not a valid value for %s in database', $value->value, get_debug_type($value)));
            }

            return $value->value;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?BackedEnum
    {
        if (false === enum_exists(static::getEnumClass(), true)) {
            throw new LogicException('This class should be an enum');
        }

        /** @var SQLEnumTypeInterface $class */
        $class = static::getEnumClass();

        if (is_a($class, SQLEnumTypeDefaultForNullInterface::class, true) && $value == null) {
            /** @var SQLEnumTypeDefaultForNullInterface $class */
            return $class::getDefaultForNull();
        }

        if ((!is_int($value)) && !is_string($value)) {
            return null;
        }

        return $class::tryFrom($value);
    }

    /**
     * @codeCoverageIgnore
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}

<?php

declare(strict_types=1);

namespace App\DBAL\Types;

interface SQLEnumTypeDefaultForNullInterface extends SQLEnumTypeInterface
{
    public static function getDefaultForNull(): self;
}

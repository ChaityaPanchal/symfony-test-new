<?php

declare(strict_types=1);

namespace App\DBAL\Types;

use Elao\Enum\ExtrasTrait;
use Elao\Enum\ReadableEnumTrait;

trait SQLEnumTypeTrait
{
    use ReadableEnumTrait;
    use ExtrasTrait;

    public function getExcludeFromSQLEnumDeclaration(): bool
    {
        return !!$this->getExtra('excludeFromSQLEnumDeclaration', false);
    }
}

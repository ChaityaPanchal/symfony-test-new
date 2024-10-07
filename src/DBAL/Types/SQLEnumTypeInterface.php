<?php

declare(strict_types=1);

namespace App\DBAL\Types;

use BackedEnum;
use Elao\Enum\ReadableEnumInterface;

interface SQLEnumTypeInterface extends ReadableEnumInterface, BackedEnum
{
    public function getExcludeFromSQLEnumDeclaration(): bool;
}

<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\DBAL\Types\SQLEnumTypeInterface;
use App\DBAL\Types\SQLEnumTypeTrait;
use Elao\Enum\Attribute\EnumCase;

enum UserRole: string implements SQLEnumTypeInterface
{
    use SQLEnumTypeTrait;

    #[EnumCase('Admin')]
    case ADMIN = 'ROLE_ADMIN';

    #[EnumCase('User')]
    case USER = 'ROLE_USER';
}

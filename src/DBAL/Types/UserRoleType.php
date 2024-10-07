<?php

declare(strict_types=1);

namespace App\DBAL\Types;

use App\Entity\Enum\UserRole;

class UserRoleType extends AbstractEnumType
{
    public static function getEnumClass(): string
    {
        return UserRole::class;
    }
}

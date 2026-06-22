<?php

namespace App\Enums;

enum UserRole: string
{
    case Tenant = 'tenant';
    case Owner = 'owner';
    case Admin = 'admin';
}

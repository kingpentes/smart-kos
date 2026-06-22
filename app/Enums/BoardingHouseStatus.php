<?php

namespace App\Enums;

enum BoardingHouseStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
    case Rejected = 'rejected';
    case Inactive = 'inactive';
}

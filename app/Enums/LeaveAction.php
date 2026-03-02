<?php

namespace App\Enums;

enum LeaveAction
{
    case Created;
    case Updated;
    case Deleted;
    case Approved;
    case Rejected;
}
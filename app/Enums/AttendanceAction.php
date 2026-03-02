<?php

namespace App\Enums;

enum AttendanceAction
{
    case Created;
    case Updated;
    case Deleted;
    case Restored;
}

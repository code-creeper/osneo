<?php

namespace App\Enums;

enum InsuranceClaimStatus: string
{
    case OPEN = 'Open';
    case WAITING = 'Waiting';
    case CONFIRMED = 'Confirmed';
    case UNCONFIRMED = 'Unconfirmed';
    case DONE = 'Done';
    case REJECTED = 'Rejected';
}

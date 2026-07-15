<?php

namespace App\Enums;

// DELTA #3
enum LockStatus: string
{
    case TemporaryHold = 'temporary_hold';
    case PendingApproval = 'pending_approval';
    case Confirmed = 'confirmed';
    case Released = 'released';
}

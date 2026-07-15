<?php

namespace App\Enums;

enum StatusVerifikasi: string
{
    case Menunggu = 'Menunggu';
    case Valid = 'Valid';
    case TidakValid = 'Tidak Valid';
}

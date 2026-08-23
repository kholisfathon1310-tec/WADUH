<?php

namespace App\Enums;

enum StatusReservasi: string
{
    case Menunggu = 'Menunggu';
    case Disetujui = 'Disetujui';
    case Ditolak = 'Ditolak';
    case Selesai = 'Selesai';
    case Dibatalkan = 'Dibatalkan';
    case Kadaluarsa = 'Kadaluarsa';
}

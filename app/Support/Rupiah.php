<?php

namespace App\Support;

class Rupiah
{
    /**
     * Format integer ke format mata uang Rupiah tanpa spasi dengan pemisah ribuan titik.
     * Contoh: 6982000 -> "Rp6.982.000"
     */
    public static function format(int $n): string
    {
        if ($n < 0) {
            return '-Rp' . number_format(abs($n), 0, ',', '.');
        }

        return 'Rp' . number_format($n, 0, ',', '.');
    }
}

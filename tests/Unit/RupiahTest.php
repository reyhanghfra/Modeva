<?php

namespace Tests\Unit;

use App\Support\Rupiah;
use PHPUnit\Framework\TestCase;

class RupiahTest extends TestCase
{
    public function test_format_formats_positive_integers_correctly(): void
    {
        $this->assertSame('Rp6.982.000', Rupiah::format(6982000));
        $this->assertSame('Rp3.462.000', Rupiah::format(3462000));
        $this->assertSame('Rp1.450.000', Rupiah::format(1450000));
        $this->assertSame('Rp50.000', Rupiah::format(50000));
        $this->assertSame('Rp500', Rupiah::format(500));
    }

    public function test_format_formats_zero(): void
    {
        $this->assertSame('Rp0', Rupiah::format(0));
    }

    public function test_format_formats_negative_integers(): void
    {
        $this->assertSame('-Rp50.000', Rupiah::format(-50000));
    }
}

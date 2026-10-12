<?php
declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\InvoiceLine;
use App\Tests\Mother\InvoiceLineMother;
use PHPUnit\Framework\TestCase;

class InvoiceLineTest extends TestCase
{
    public function testUnitPriceHasToBeGreaterThanZero()
    {
        $this->expectException(\Exception::class);
        $invalidLine = new InvoiceLine('name', 1, 0);
    }

    public function testQuantityHasToBeGreaterThanZero()
    {
        $this->expectException(\Exception::class);
        $invalidLine = new InvoiceLine('name', 0, 1);
    }

    public function testLinePriceReturnsQuantityMultipliedByUnitPrice()
    {
        $invoiceLine = InvoiceLineMother::aLine()
            ->withQuantity(2)
            ->withUnitPrice(10)
            ->build();
        $this->assertEquals(20, $invoiceLine->getLinePrice());
    }
}

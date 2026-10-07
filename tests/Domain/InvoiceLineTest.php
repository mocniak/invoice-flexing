<?php
declare(strict_types=1);

namespace App\Tests\Domain;

use App\Tests\Mother\InvoiceLineMother;
use PHPUnit\Framework\TestCase;

class InvoiceLineTest extends TestCase
{
    public function testIntLinePrice()
    {
        $invoiceLine = InvoiceLineMother::aLine()
            ->withQuantity(2)
            ->withUnitPrice(10)
            ->build();
        $this->assertEquals(20, $invoiceLine->getLinePrice());
    }
}

<?php
declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\Invoice;
use App\Tests\Mother\InvoiceLineMother;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    public function testTotalPrice()
    {
        $invoice = new Invoice('Customer Name', 'customer@example.com', [
            InvoiceLineMother::aLine()->withQuantity(3)->withUnitPrice(10)->build(),
            InvoiceLineMother::aLine()->withQuantity(7)->withUnitPrice(3)->build(),
        ]);
        $this->assertEquals(51, $invoice->getTotalPrice());
    }
}

<?php
declare(strict_types=1);

namespace App\Tests\Invoice\Domain;

use App\Invoice\Domain\Invoice;
use App\Invoice\Domain\InvoiceStatus;
use App\Tests\Invoice\Mother\InvoiceLineMother;
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

    public function testMarkAsSent(): void
    {
        $invoice = new Invoice('Customer Name', 'customer@example.com', [
            InvoiceLineMother::any(),
        ]);
        $invoice->markAsSent();
        $this->assertEquals(InvoiceStatus::SentToClient, $invoice->status());
    }
}

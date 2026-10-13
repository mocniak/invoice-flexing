<?php
declare(strict_types=1);

namespace App\Tests\Invoice\Domain;

use App\Invoice\Domain\Exception\InvalidInvoiceTransition;
use App\Invoice\Domain\Invoice;
use App\Invoice\Domain\InvoiceStatus;
use App\Tests\Invoice\Mother\InvoiceLineMother;
use App\Tests\Invoice\Mother\InvoiceMother;
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

    public function testDraftInvoiceCanBeMarkedAsInSending(): void
    {
        $invoice = InvoiceMother::anInvoice()->withNonEmptyLines()->build();
        $invoice->markAsInSending();
        $this->assertEquals(InvoiceStatus::InSending, $invoice->getStatus());
    }

    public function testDraftInvoiceCannotBeMarkedAsSent(): void
    {
        $invoice = InvoiceMother::anInvoice()->withNonEmptyLines()->build();
        $this->expectException(InvalidInvoiceTransition::class);
        $invoice->markAsSent();
    }

    public function testInSendingInvoiceCannotBeMarkedAsInSendingAgain(): void
    {
        $invoice = InvoiceMother::anInvoice()->withNonEmptyLines()->build();
        $invoice->markAsInSending();
        $this->expectException(InvalidInvoiceTransition::class);
        $invoice->markAsInSending();
    }

    public function testInSendingInvoiceCanBeMarkedAsSent(): void
    {
        $invoice = InvoiceMother::anInvoice()->withNonEmptyLines()->build();
        $invoice->markAsInSending();
        $invoice->markAsSent();
        $this->assertEquals(InvoiceStatus::SentToClient, $invoice->getStatus());
    }

    public function testSentInvoiceCannotBeMarkedAsSentAgain(): void
    {
        $invoice = InvoiceMother::anInvoice()->withNonEmptyLines()->build();
        $invoice->markAsInSending();
        $invoice->markAsSent();

        $this->expectException(InvalidInvoiceTransition::class);
        $invoice->markAsSent();
    }

    public function testSentInvoiceCannotBeMarkedAsInSending(): void
    {
        $invoice = InvoiceMother::anInvoice()->withNonEmptyLines()->build();
        $invoice->markAsInSending();
        $invoice->markAsSent();

        $this->expectException(InvalidInvoiceTransition::class);
        $invoice->markAsInSending();
    }


}

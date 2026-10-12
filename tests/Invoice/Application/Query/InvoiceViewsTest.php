<?php

declare(strict_types=1);

namespace App\Tests\Invoice\Application\Query;

use App\Invoice\Application\Query\InvoiceLineView;
use App\Invoice\Application\Query\InvoiceView;
use App\Invoice\Application\Query\InvoiceViews;
use App\Invoice\Application\Repository\Exception\NotFound;
use App\Invoice\Application\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\Invoice;
use App\Invoice\Domain\InvoiceLine;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class InvoiceViewsTest extends TestCase
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private InvoiceViews $invoiceViews;

    protected function setUp(): void
    {
        $this->invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class);
        $this->invoiceViews = new InvoiceViews($this->invoiceRepository);
    }

    public function testGetReturnsMappedInvoiceView(): void
    {
        $line1 = new InvoiceLine('Domain Registration', 2, 1200);
        $line2 = new InvoiceLine('Web Hosting', 1, 5000);
        $invoice = new Invoice('Acme Corp', 'billing@acme.com', [$line1, $line2]);

        $this->invoiceRepository
            ->expects($this->once())
            ->method('get')
            ->with($invoice->id)
            ->willReturn($invoice);

        $view = $this->invoiceViews->get($invoice->id);

        $this->assertSame($invoice->id, $view->id);
        $this->assertSame('Acme Corp', $view->customerName);
        $this->assertSame('billing@acme.com', $view->customerEmail);
        $this->assertSame(7400, $view->totalPrice);
        $this->assertCount(2, $view->lines);

        $this->assertInstanceOf(InvoiceLineView::class, $view->lines[0]);
        $this->assertSame('Domain Registration', $view->lines[0]->productName);
        $this->assertSame(2, $view->lines[0]->quantity);
        $this->assertSame(1200, $view->lines[0]->unitPrice);
        $this->assertSame(2400, $view->lines[0]->totalPrice);

        $this->assertInstanceOf(InvoiceLineView::class, $view->lines[1]);
        $this->assertSame('Web Hosting', $view->lines[1]->productName);
        $this->assertSame(1, $view->lines[1]->quantity);
        $this->assertSame(5000, $view->lines[1]->unitPrice);
        $this->assertSame(5000, $view->lines[1]->totalPrice);
    }

    public function testGetReturnsMappedInvoiceViewWithEmptyLines(): void
    {
        $invoice = new Invoice('John Doe', 'john@example.com', []);

        $this->invoiceRepository
            ->expects($this->once())
            ->method('get')
            ->with($invoice->id)
            ->willReturn($invoice);

        $view = $this->invoiceViews->get($invoice->id);

        $this->assertSame($invoice->id, $view->id);
        $this->assertSame('John Doe', $view->customerName);
        $this->assertSame('john@example.com', $view->customerEmail);
        $this->assertSame(0, $view->totalPrice);
        $this->assertSame([], $view->lines);
    }

    public function testGetThrowsExceptionWhenInvoiceNotFound(): void
    {
        $id = Uuid::v7();

        $this->invoiceRepository
            ->expects($this->once())
            ->method('get')
            ->with($id)
            ->willThrowException(new NotFound());

        $this->expectException(NotFound::class);
        $this->invoiceViews->get($id);
    }
}

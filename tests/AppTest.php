<?php
declare(strict_types=1);

namespace App\Tests;

use App\Application\App;
use App\Application\Repository\InvoiceRepositoryInterface;
use App\Domain\Invoice;
use App\Tests\Mother\InvoiceLineMother;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;

class AppTest extends TestCase
{
    private $mailer;
    private $invoiceRepository;
    private $app;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class);
        $this->app = new App($this->invoiceRepository, $this->mailer);
    }

    public function testTryingToSendInvoiceWithStatusOtherThatDraftThrowsAnException()
    {
        $invoice = new Invoice('customer name', 'customer@example.com', [InvoiceLineMother::any()]);
        $invoice->markAsSending();
        $this->invoiceRepository->expects($this->once())->method('get')->with($invoice->id)->willReturn($invoice);
        $this->mailer->expects($this->never())->method('send');
        $this->expectException(\Exception::class);
        $this->app->sendInvoice($invoice->id);
    }

    public function testTryingToSendInvoiceWithNoLinesThrowsAnException()
    {
        $invoice = new Invoice('customer name', 'customer@example.com', []);
        $this->invoiceRepository->expects($this->once())->method('get')->with($invoice->id)->willReturn($invoice);
        $this->mailer->expects($this->never())->method('send');
        $this->expectException(\Exception::class);
        $this->app->sendInvoice($invoice->id);
    }
}

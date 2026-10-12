<?php
declare(strict_types=1);

namespace App\Tests\Invoice;

use App\Invoice\Application\InvoiceApp;
use App\Invoice\Application\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\Invoice;
use App\Notification\Api\NotificationApi;
use App\Tests\Invoice\Mother\InvoiceLineMother;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;

class InvoiceAppTest extends TestCase
{
    private $notificationApi;
    private $invoiceRepository;
    private $app;

    protected function setUp(): void
    {
        $this->notificationApi = $this->createMock(NotificationApi::class);
        $this->invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class);
        $this->app = new InvoiceApp($this->invoiceRepository, $this->notificationApi);
    }

    public function testTryingToSendInvoiceWithStatusOtherThatDraftThrowsAnException()
    {
        $invoice = new Invoice('customer name', 'customer@example.com', [InvoiceLineMother::any()]);
        $invoice->markAsInSending();
        $this->invoiceRepository->expects($this->once())->method('get')->with($invoice->id)->willReturn($invoice);
        $this->notificationApi->expects($this->never())->method('notify');
        $this->expectException(\Exception::class);
        $this->app->sendInvoice($invoice->id);
    }

    public function testTryingToSendInvoiceWithNoLinesThrowsAnException()
    {
        $invoice = new Invoice('customer name', 'customer@example.com', []);
        $this->invoiceRepository->expects($this->once())->method('get')->with($invoice->id)->willReturn($invoice);
        $this->notificationApi->expects($this->never())->method('notify');
        $this->expectException(\Exception::class);
        $this->app->sendInvoice($invoice->id);
    }
}

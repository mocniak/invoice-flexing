<?php
declare(strict_types=1);

namespace App\Tests\Invoice\Application;

use App\Invoice\Application\Handler\NotificationSentHandler;
use App\Invoice\Application\Repository\Exception\NotFound;
use App\Invoice\Application\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\Invoice;
use App\Invoice\Domain\InvoiceStatus;
use App\Notification\Api\Events\NotificationSentEvent;
use App\Tests\Invoice\Mother\InvoiceLineMother;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class NotificationSentHandlerTest extends TestCase
{
    public function testHandlesNotificationSentEventAndMarksInvoiceAsSent(): void
    {
        $invoice = new Invoice('Customer Name', 'customer@example.com', [InvoiceLineMother::any()]);
        $invoice->markAsInSending();

        $repository = $this->createMock(InvoiceRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('get')
            ->with($invoice->id)
            ->willReturn($invoice);

        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Invoice $savedInvoice) {
                return $savedInvoice->getStatus() === InvoiceStatus::SentToClient;
            }));

        $handler = new NotificationSentHandler($repository);
        $handler(new NotificationSentEvent($invoice->id));

        $this->assertSame(InvoiceStatus::SentToClient, $invoice->getStatus());
    }

    public function testNotificationWithResourceIdNotFoundAsInvoiceIdAreIgnored(): void
    {
        $someRandomId = Uuid::v4();
        $repository = $this->createMock(InvoiceRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('get')
            ->with($someRandomId)
            ->willThrowException(new NotFound());

        $repository->expects($this->never())->method('save');

        $handler = new NotificationSentHandler($repository);
        $handler(new NotificationSentEvent($someRandomId));
    }}

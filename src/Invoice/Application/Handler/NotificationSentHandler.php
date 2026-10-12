<?php
declare(strict_types=1);

namespace App\Invoice\Application\Handler;

use App\Invoice\Application\Repository\Exception\NotFound;
use App\Invoice\Application\Repository\InvoiceRepositoryInterface;
use App\Notification\Api\Events\NotificationSentEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final readonly class NotificationSentHandler
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
    ) {
    }

    public function __invoke(NotificationSentEvent $event): void
    {
        try {
            $invoice = $this->invoiceRepository->get($event->resourceId);
        } catch (NotFound $e) {
            // something was sent, but it was not the invoice from this module - do nothing
            return;
        }
        $invoice->markAsSent();
        $this->invoiceRepository->save($invoice);
    }
}

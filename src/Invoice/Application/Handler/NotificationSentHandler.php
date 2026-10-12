<?php
declare(strict_types=1);

namespace App\Invoice\Application\Handler;

use App\Invoice\Application\Repository\Exception\NotFound;
use App\Invoice\Application\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\Exception\InvalidInvoiceTransition;
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
        } catch (NotFound) {
            // something was sent, but it was not the invoice from this module - do nothing
            return;
        }

        try {
            $invoice->markAsSent();
        } catch (InvalidInvoiceTransition) {
            // it is worth logging the invalid transition somewhere
            return;
        }
        $this->invoiceRepository->save($invoice);
    }
}

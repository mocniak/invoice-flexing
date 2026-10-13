<?php
declare(strict_types=1);

namespace App\Invoice\Application;

use App\Invoice\Application\Dto\CreateInvoiceDto;
use App\Invoice\Application\Dto\CreateInvoiceLineDto;
use App\Invoice\Application\Exception\InvoiceCannotBeSent;
use App\Invoice\Application\Exception\InvoiceNotFound;
use App\Invoice\Application\Repository\Exception\NotFound;
use App\Invoice\Application\Repository\Exception\SavingStaleInvoice;
use App\Invoice\Application\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\Exception\InvalidInvoiceTransition;
use App\Invoice\Domain\Invoice;
use App\Invoice\Domain\InvoiceLine;
use App\Notification\Api\Data\NotifyData;
use App\Notification\Api\NotificationApi;
use Symfony\Component\Uid\Uuid;

readonly class InvoiceApp
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
        private NotificationApi $notificationApi,
    )
    {
    }

    public function createInvoice(CreateInvoiceDto $dto): Uuid
    {
        $lines = array_map(
            fn(CreateInvoiceLineDto $lineDto) => new InvoiceLine(
                $lineDto->productName,
                $lineDto->quantity,
                $lineDto->unitPrice,
            ),
            $dto->lines,
        );

        $newInvoice = new Invoice($dto->customerName, $dto->customerEmail, $lines);
        $this->invoiceRepository->save($newInvoice);

        return $newInvoice->id;
    }

    /**
     * @throws InvoiceCannotBeSent
     * @throws InvoiceNotFound
     */
    public function sendInvoice(Uuid $invoiceId): void
    {
        try {
            $invoiceToSend = $this->invoiceRepository->get($invoiceId);
        } catch (NotFound) {
            throw new InvoiceNotFound('Invoice not found');
        }

        try {
            $invoiceToSend->markAsInSending();
        } catch (InvalidInvoiceTransition $e) {
            throw new InvoiceCannotBeSent($e->getMessage());
        }

        try {
            $this->invoiceRepository->save($invoiceToSend);
        } catch (SavingStaleInvoice $e) {
            throw new InvoiceCannotBeSent($e->getMessage());
        }
        $this->notificationApi->notify(new NotifyData(
            resourceId: $invoiceId,
            toEmail: $invoiceToSend->customerEmail,
            subject: 'Here is your invoice.',
            message: 'Lorem ipsum dolor sit amet.'
        ));
    }
}

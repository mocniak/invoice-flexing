<?php
declare(strict_types=1);

namespace App\Invoice\Application;

use App\Invoice\Application\Exception\InvoiceCannotBeSent;
use App\Invoice\Application\Exception\InvoiceNotFound;
use App\Invoice\Application\Repository\Exception\NotFound;
use App\Invoice\Application\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\Invoice;
use App\Invoice\Domain\InvoiceLine;
use App\Invoice\Domain\InvoiceStatus;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;

readonly class InvoiceApp
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
        private MailerInterface $mailer,
    )
    {
    }

    public function createInvoice(): Uuid
    {
        $newInvoice = new Invoice('Customer Name', 'customer@example.com', [new InvoiceLine('Product Name', 1, 100)]);
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

        if ($invoiceToSend->status !== InvoiceStatus::Draft) {
            throw new InvoiceCannotBeSent('Invoice must be in draft status');
        }
        if (count($invoiceToSend->lines) === 0) {
            throw new InvoiceCannotBeSent('Invoice has to contain at least one line');
        }

        $email = (new Email())
            ->from('invoices@example.com')
            ->to($invoiceToSend->customerEmail)
            ->subject('Your invoice')
            ->text('Here is your invoice.');

        $this->mailer->send($email);
        $invoiceToSend->markAsSending();
        $this->invoiceRepository->save($invoiceToSend);
    }
}

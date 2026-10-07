<?php
declare(strict_types=1);

namespace App;

use App\Domain\Invoice;
use App\Repository\InvoiceRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;

readonly class App
{
    public function __construct(
        private InvoiceRepository $invoiceRepository,
        private MailerInterface $mailer,
    )
    {
    }

    public function createInvoice(): Uuid
    {
        $newInvoice = new Invoice('Customer Name', 'customer@example.com', []);
        $this->invoiceRepository->save($newInvoice);

        return $newInvoice->id;
    }

    public function sendInvoice(Uuid $invoiceId): void
    {
        $invoiceToSend = $this->invoiceRepository->get($invoiceId);
        $email = (new Email())
            ->from('invoices@example.com')
            ->to($invoiceToSend->customerEmail)
            ->subject('Your invoice')
            ->text('Here is your invoice.');

        $this->mailer->send($email);
        $invoiceToSend->markAsSending();
    }

}

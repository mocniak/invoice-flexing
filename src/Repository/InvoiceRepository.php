<?php
declare(strict_types=1);

namespace App\Repository;

use App\Domain\Invoice;
use Symfony\Component\Uid\Uuid;

class InvoiceRepository
{
    private array $invoices = [];

    public function save(Invoice $invoice): void
    {
        $this->invoices[$invoice->id->toRfc4122()] = $invoice;
    }

    /**
     * @throws InvoiceNotFoundException
     */
    public function get(?Uuid $createdInvoiceId): Invoice
    {
        return $this->invoices[$createdInvoiceId->toRfc4122()] ?? throw new InvoiceNotFoundException();
    }
}

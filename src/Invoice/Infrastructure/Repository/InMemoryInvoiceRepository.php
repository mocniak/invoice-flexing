<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Repository;

use App\Invoice\Application\Repository\Exception\NotFound;
use App\Invoice\Application\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\Invoice;
use Symfony\Component\Uid\Uuid;

class InMemoryInvoiceRepository implements InvoiceRepositoryInterface
{
    private array $invoices = [];

    public function save(Invoice $invoice): void
    {
        $this->invoices[$invoice->id->toRfc4122()] = $invoice;
    }

    /**
     * @throws NotFound
     */
    public function get(Uuid $createdInvoiceId): Invoice
    {
        return $this->invoices[$createdInvoiceId->toRfc4122()] ?? throw new NotFound();
    }
}

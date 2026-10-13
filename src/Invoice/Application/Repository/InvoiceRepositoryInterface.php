<?php

declare(strict_types=1);

namespace App\Invoice\Application\Repository;

use App\Invoice\Application\Repository\Exception\NotFound;
use App\Invoice\Application\Repository\Exception\SavingStaleInvoice;
use App\Invoice\Domain\Invoice;
use Symfony\Component\Uid\Uuid;

interface InvoiceRepositoryInterface
{
    /**
     * @throws SavingStaleInvoice
     */
    public function save(Invoice $invoice): void;

    /**
     * @throws NotFound
     */
    public function get(Uuid $createdInvoiceId): Invoice;
}

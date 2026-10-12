<?php

declare(strict_types=1);

namespace App\Application\Repository;

use App\Application\Repository\Exception\NotFound;
use App\Domain\Invoice;
use Symfony\Component\Uid\Uuid;

interface InvoiceRepositoryInterface
{
    public function save(Invoice $invoice): void;

    /**
     * @throws NotFound
     */
    public function get(Uuid $createdInvoiceId): Invoice;
}

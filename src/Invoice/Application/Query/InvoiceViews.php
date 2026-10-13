<?php
declare(strict_types=1);

namespace App\Invoice\Application\Query;

use App\Invoice\Application\Repository\Exception\NotFound;
use App\Invoice\Application\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\InvoiceLine;
use Symfony\Component\Uid\Uuid;

readonly class InvoiceViews
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
    ) {
    }

    /**
     * @throws NotFound
     */
    public function get(Uuid $uuid): InvoiceView
    {
        $invoice = $this->invoiceRepository->get($uuid);

        $lineViews = array_map(
            fn (InvoiceLine $line) => new InvoiceLineView(
                $line->productName,
                $line->quantity,
                $line->unitPrice,
                $line->getLinePrice(),
            ),
            $invoice->lines
        );

        return new InvoiceView(
            $invoice->id,
            $invoice->getStatus(),
            $invoice->customerName,
            $invoice->customerEmail,
            $lineViews,
            $invoice->getTotalPrice(),
        );
    }
}

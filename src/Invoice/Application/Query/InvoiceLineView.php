<?php
declare(strict_types=1);

namespace App\Invoice\Application\Query;

final readonly class InvoiceLineView
{
    public function __construct(
        public string $productName,
        public int $quantity,
        public int $unitPrice,
        public int $totalPrice,
    ) {
    }
}

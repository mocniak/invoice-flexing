<?php
declare(strict_types=1);

namespace App\Domain;

class InvoiceLine
{
    public function __construct(
        public readonly string $productName,
        public readonly int $quantity,
        public readonly int $unitPrice
    )
    {
    }
    public function getLinePrice(): int
    {
        return $this->quantity * $this->unitPrice;
    }
}

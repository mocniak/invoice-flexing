<?php
declare(strict_types=1);

namespace App\Domain;

readonly class InvoiceLine
{
    public function __construct(
        public string $productName,
        public int $quantity,
        public int $unitPrice
    )
    {
    }
    public function getLinePrice(): int
    {
        return $this->quantity * $this->unitPrice;
    }
}

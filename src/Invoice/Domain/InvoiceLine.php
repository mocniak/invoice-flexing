<?php
declare(strict_types=1);

namespace App\Invoice\Domain;

readonly class InvoiceLine
{
    public function __construct(
        public string $productName,
        public int $quantity,
        public int $unitPrice
    )
    {
        if ($unitPrice <= 0) {
            throw new \Exception('Unit price has to be greater than zero');
        }
        if ($this->quantity <= 0) {
            throw new \Exception('Quantity has to be greater than zero');
        }
    }
    public function getLinePrice(): int
    {
        return $this->quantity * $this->unitPrice;
    }
}

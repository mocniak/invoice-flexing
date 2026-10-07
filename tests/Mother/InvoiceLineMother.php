<?php
declare(strict_types=1);

namespace App\Tests\Mother;

use App\Domain\InvoiceLine;

class InvoiceLineMother
{
    private int $quantity = 1;
    private int $unitPrice;

    public static function aLine(): self
    {
        return new self();
    }
    public function withQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }
    public function withUnitPrice(int $unitPrice): self
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }
    public function build(): InvoiceLine
    {
        return new InvoiceLine('Product Name', $this->quantity, $this->unitPrice);
    }
}

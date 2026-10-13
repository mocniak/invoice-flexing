<?php
declare(strict_types=1);

namespace App\Tests\Invoice\Mother;

use App\Invoice\Domain\InvoiceLine;

class InvoiceLineMother
{
    private int $quantity;
    private int $unitPrice;

    public function __construct()
    {
        $this->quantity = random_int(1, 1000);
        $this->unitPrice = random_int(1, 100000);
    }

    public static function aLine(): self
    {
        return new self();
    }

    public static function any(): InvoiceLine
    {
        return self::aLine()->build();
    }

    public static function nonEmpty(): InvoiceLine
    {
        return new InvoiceLine('Non Empty Line', 2, 100);
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

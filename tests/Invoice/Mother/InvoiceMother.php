<?php
declare(strict_types=1);

namespace App\Tests\Invoice\Mother;

use App\Invoice\Domain\Invoice;

class InvoiceMother
{
    private array $lines = [];

    public static function anInvoice(): self
    {
        return new self();
    }

    public static function any(): Invoice
    {
        return self::anInvoice()->build();
    }

    public function build(): Invoice
    {
        return new Invoice('Product Name', 'customer@example.com', $this->lines);
    }

    public function withNonEmptyLines():self
    {
        $this->lines[] = InvoiceLineMother::nonEmpty();
        return $this;
    }

    public function withLines(array $lines):self
    {
        $this->lines = $lines;
        return $this;
    }
}

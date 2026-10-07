<?php
declare(strict_types=1);

namespace App\Domain;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

class Invoice
{
    public readonly UuidV7 $id;

    public function __construct(public readonly string $customerName, public readonly string $customerEmail, public array $lines)
    {
        $this->id = Uuid::v7();
    }

    public function totalPrice(): int
    {
        return array_reduce(
            $this->lines,
            fn (int $sum, InvoiceLine $line) => $sum + $line->getLinePrice(),
            0
        );
    }
}

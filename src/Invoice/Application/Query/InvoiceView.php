<?php
declare(strict_types=1);

namespace App\Invoice\Application\Query;

use App\Invoice\Domain\InvoiceStatus;
use Symfony\Component\Uid\Uuid;

final readonly class InvoiceView
{
    /**
     * @param InvoiceLineView[] $lines
     */
    public function __construct(
        public Uuid $id,
        public InvoiceStatus $status,
        public string $customerName,
        public string $customerEmail,
        public array $lines,
        public int $totalPrice,
    ) {
    }
}

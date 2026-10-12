<?php
declare(strict_types=1);

namespace App\Infrastructure\Http\Dto;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateInvoiceLineDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $productName,

        #[Assert\NotNull]
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $quantity,

        #[Assert\NotNull]
        #[Assert\Type('integer')]
        #[Assert\Positive]
        public int $unitPrice,
    ) {
    }
}

<?php
declare(strict_types=1);

namespace App\Infrastructure\Http\Dto;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateInvoiceDto
{
    /**
     * @param CreateInvoiceLineDto[] $lines
     */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $customerName,

        #[Assert\NotBlank]
        #[Assert\Email]
        public string $customerEmail,

        #[Assert\Valid]
        public array $lines = [],
    ) {
    }
}

<?php
declare(strict_types=1);

namespace App\Domain;

use App\Infrastructure\Repository\DoctrineInvoiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[ORM\Entity(repositoryClass: DoctrineInvoiceRepository::class)]
#[ORM\Table(name: 'invoices')]
#[ORM\HasLifecycleCallbacks]
class Invoice
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    public readonly UuidV7 $id;

    #[ORM\Column(type: 'string', enumType: InvoiceStatus::class)]
    public InvoiceStatus $status;

    #[ORM\Column(type: 'string', length: 255)]
    public readonly string $customerName;

    #[ORM\Column(type: 'string', length: 255)]
    public readonly string $customerEmail;

    /**
     * @var InvoiceLine[]
     */
    #[ORM\Column(type: 'json')]
    public array $lines;

    public function __construct(
        string $customerName,
        string $customerEmail,
        array $lines
    )
    {
        $this->id = Uuid::v7();
        $this->status = InvoiceStatus::Draft;
        $this->customerName = $customerName;
        $this->customerEmail = $customerEmail;
        $this->lines = $lines;
    }

    #[ORM\PostLoad]
    public function postLoad(): void
    {
        $this->lines = array_map(
            fn ($line) => $line instanceof InvoiceLine ? $line : new InvoiceLine(
                $line['productName'],
                (int) $line['quantity'],
                (int) $line['unitPrice']
            ),
            $this->lines
        );
    }

    public function status(): InvoiceStatus
    {
        return $this->status;
    }

    public function getTotalPrice(): int
    {
        return array_reduce(
            $this->lines,
            fn (int $sum, InvoiceLine $line) => $sum + $line->getLinePrice(),
            0
        );
    }

    public function markAsSending(): void
    {
        $this->status = InvoiceStatus::Sending;
    }
}

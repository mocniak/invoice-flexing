<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Repository;

use App\Invoice\Application\Repository\Exception\NotFound;
use App\Invoice\Application\Repository\Exception\SavingStaleInvoice;
use App\Invoice\Application\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
#[AsAlias(InvoiceRepositoryInterface::class)]
class DoctrineInvoiceRepository extends ServiceEntityRepository implements InvoiceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * @throws SavingStaleInvoice
     */
    public function save(Invoice $invoice): void
    {
        try {
            $this->getEntityManager()->persist($invoice);
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            throw new SavingStaleInvoice(
                'Invoice was concurrently modified.',
                previous: $e,
            );
        }
    }

    /**
     * @throws NotFound
     */
    public function get(Uuid $createdInvoiceId): Invoice
    {
        $invoice = $this->find($createdInvoiceId);

        if ($invoice === null) {
            throw new NotFound();
        }

        return $invoice;
    }
}

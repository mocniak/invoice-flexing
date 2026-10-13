<?php
declare(strict_types=1);

namespace App\Tests\Integration;

use App\Invoice\Application\Repository\Exception\SavingStaleInvoice;
use App\Invoice\Domain\Invoice;
use App\Invoice\Infrastructure\Repository\DoctrineInvoiceRepository;
use App\Tests\Invoice\Mother\InvoiceLineMother;
use App\Tests\Invoice\Mother\InvoiceMother;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineInvoiceRepositoryTest extends KernelTestCase
{

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->entityManager = self::getContainer()->get(
            EntityManagerInterface::class,
        );
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();

        parent::tearDown();
    }

    public function testStaleInvoiceCannotOverwriteNewerInvoice(): void
    {
        $invoice = InvoiceMother::anInvoice()->withLines([InvoiceLineMother::any()])->build();

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        $invoiceId = $invoice->id;

        // two independent UOW
        $entityManagerA = $this->createEntityManager();
        $entityManagerB = $this->createEntityManager();

        $repositoryA = $this->createRepository($entityManagerA);
        $repositoryB = $this->createRepository($entityManagerB);

        // Both load version 1 independently.
        $invoiceA = $repositoryA->get($invoiceId);
        $invoiceB = $repositoryB->get($invoiceId);

        self::assertNotSame($invoiceA, $invoiceB);

        // A wins.
        $invoiceA->markAsInSending();
        $repositoryA->save($invoiceA);

        // B still has stale version 1.
        $invoiceB->markAsInSending();

        $this->expectException(SavingStaleInvoice::class);
        $repositoryB->save($invoiceB);
    }

    private function createRepository(
        EntityManagerInterface $entityManager,
    ): DoctrineInvoiceRepository
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $registry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with(Invoice::class)
            ->willReturn($entityManager);

        return new DoctrineInvoiceRepository($registry);
    }

    private function createEntityManager(): EntityManagerInterface
    {
        return new EntityManager(
            $this->entityManager->getConnection(),
            $this->entityManager->getConfiguration(),
            $this->entityManager->getEventManager(),
        );
    }
}

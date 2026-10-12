<?php
declare(strict_types=1);

namespace App\Tests\Notification\Controller;

use App\Invoice\Application\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\Invoice;
use App\Invoice\Domain\InvoiceLine;
use App\Invoice\Domain\InvoiceStatus;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class NotificationControllerTest extends WebTestCase
{
    public function testMarkAsSentSuccessfully(): void
    {
        $client = static::createClient();

        /** @var InvoiceRepositoryInterface $repository */
        $repository = static::getContainer()->get(InvoiceRepositoryInterface::class);

        $line = new InvoiceLine('Product A', 1, 100);
        $invoice = new Invoice('Customer A', 'customer@example.com', [$line]);
        $invoice->markAsSending();
        $repository->save($invoice);

        $client->request('POST', '/api/notification/' . $invoice->id->toRfc4122() . '/mark-as-sent');

        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);

        $updatedInvoice = $repository->get($invoice->id);
        $this->assertSame(InvoiceStatus::SentToClient, $updatedInvoice->status());
    }
}

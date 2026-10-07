<?php
declare(strict_types=1);

namespace App;

use App\Domain\Invoice;
use App\Repository\InvoiceRepository;
use Symfony\Component\Uid\Uuid;

readonly class App
{
    public function __construct(private InvoiceRepository $invoiceRepository)
    {
    }

    public function createInvoice(): Uuid
    {
        $newInvoice = new Invoice('Customer Name', 'customer@example.com', []);
        $this->invoiceRepository->save($newInvoice);

        return $newInvoice->id;
    }

}

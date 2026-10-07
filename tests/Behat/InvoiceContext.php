<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\App;
use App\Domain\Invoice;
use App\Domain\InvoiceStatus;
use App\Repository\InvoiceRepository;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Uid\Uuid;

/**
 * This context class contains the definitions of the steps used by the demo
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 *
 * @see http://behat.org/en/latest/quick_start.html
 */
final class InvoiceContext implements Context
{

    /** @var Response|null */
    private $response;
    private ?Uuid $createdInvoiceId;
    private ?Invoice $retrievedInvoice;

    public function __construct(
        private readonly KernelInterface $kernel,
    private readonly App $app,
        private readonly InvoiceRepository $invoiceView,
    )
    {
    }

    #[When('a demo scenario sends a request to :path')]
    public function aDemoScenarioSendsARequestTo(string $path): void
    {
        $this->response = $this->kernel->handle(Request::create($path, 'GET'));
    }

    #[Then('the response should be received')]
    public function theResponseShouldBeReceived(): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('No response received');
        }
    }

    #[Given('/^there is an Invoice created$/')]
    public function thereIsAnInvoiceCreated(): void
    {
        $this->createdInvoiceId = $this->app->createInvoice();
    }

    #[When('/^I retrieve created invoice$/')]
    public function iRetrieveCreatedInvoice() : void
    {
        $this->retrievedInvoice = $this->invoiceView->get($this->createdInvoiceId);
    }

    #[Then('/^retrieved Invoice status is "([^"]*)"$/')]
    public function retrievedInvoiceStatusIs(string $status): void
    {
        Assert::assertSame(InvoiceStatus::from($status), $this->retrievedInvoice->status);
    }
}

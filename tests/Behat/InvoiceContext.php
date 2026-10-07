<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\App;
use App\Domain\Invoice;
use App\Domain\InvoiceStatus;
use App\Repository\InvoiceRepository;
use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
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
        private readonly MessageLoggerListener $mailerLogger,
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
    public function iRetrieveCreatedInvoice(): void
    {
        $this->retrievedInvoice = $this->invoiceView->get($this->createdInvoiceId);
    }

    #[Then('/^retrieved Invoice status is "([^"]*)"$/')]
    public function retrievedInvoiceStatusIs(string $status): void
    {
        Assert::assertSame(InvoiceStatus::from($status), $this->retrievedInvoice->status);
    }

    #[When('/^I send the created invoice to the customer$/')]
    public function iSendTheCreatedInvoiceToTheCustomer(): void
    {
        $this->app->sendInvoice($this->createdInvoiceId);
    }

    #[Then('/^the message is sent with created invoice$/')]
    public function theMessageIsSentWithCreatedInvoice(): void
    {
        // leave only those mails which reached the mail transport
        $sentEventsInMailer = array_filter(
            $this->mailerLogger->getEvents()->getEvents(),
            static fn (MessageEvent $event) => !$event->isQueued(),
        );

        Assert::assertCount(1, $sentEventsInMailer);

        $mailerEvent = array_values($sentEventsInMailer)[0];

        // chech if the found email is the one we're looking for
        Assert::assertSame(
            $this->invoiceView->get($this->createdInvoiceId)->customerEmail,
            $mailerEvent->getEnvelope()->getRecipients()[0]->getAddress(),
        );
    }

    #[Given('/^the created invoice status is "([^"]*)"$/')]
    public function theCreatedInvoiceStatusIs(string $status): void
    {
        $createdInvoice = $this->invoiceView->get($this->createdInvoiceId);
        Assert::assertEquals(InvoiceStatus::from($status), $createdInvoice->status);
    }
}

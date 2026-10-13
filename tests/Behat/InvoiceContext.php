<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Invoice\Application\InvoiceApp;
use App\Invoice\Application\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\Invoice;
use App\Invoice\Domain\InvoiceLine;
use App\Invoice\Domain\InvoiceStatus;
use App\Tests\Invoice\Mother\InvoiceMother;
use App\Tests\Invoice\Stub\NotificationApiStub;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Uid\Uuid;

final class InvoiceContext implements Context
{

    /** @var Response|null */
    private $response;
    private ?Uuid $createdInvoiceId = null;
    private ?Invoice $createdInvoice = null;
    private ?Invoice $retrievedInvoice = null;

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly InvoiceApp $app,
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly NotificationApiStub $notificationApiStub,
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
        $createdInvoice = InvoiceMother::any();
        $this->invoiceRepository->save($createdInvoice);
        $this->createdInvoiceId = $createdInvoice->id;
    }

    #[When('/^I retrieve created invoice$/')]
    public function iRetrieveCreatedInvoice(): void
    {
        $this->retrievedInvoice = $this->invoiceRepository->get($this->createdInvoiceId);
    }

    #[Then('/^retrieved Invoice status is "([^"]*)"$/')]
    public function retrievedInvoiceStatusIs(string $status): void
    {
        Assert::assertSame(InvoiceStatus::from($status), $this->retrievedInvoice->getStatus());
    }

    #[When('/^I send the created invoice to the customer$/')]
    public function iSendTheCreatedInvoiceToTheCustomer(): void
    {
        $this->app->sendInvoice($this->createdInvoiceId);
    }

    #[Given('there is an invoice for :customerName with email :customerEmail and lines:')]
    public function thereIsAnInvoiceWithLines(string $customerName, string $customerEmail, TableNode $table): void
    {
        $lines = [];
        foreach ($table->getColumnsHash() as $row) {
            $lines[] = new InvoiceLine(
                (string)$row['product'],
                (int)$row['quantity'],
                (int)$row['unitPrice'],
            );
        }

        $invoice = new Invoice($customerName, $customerEmail, $lines);
        $this->invoiceRepository->save($invoice);
        $this->createdInvoiceId = $invoice->id;
        $this->createdInvoice = $invoice;
    }

    #[Given('there is an invoice for :customerName with email :customerEmail')]
    public function thereIsAnInvoiceForCustomer(string $customerName, string $customerEmail): void
    {
        $invoice = new Invoice($customerName, $customerEmail, []);
        $this->invoiceRepository->save($invoice);
        $this->createdInvoiceId = $invoice->id;
        $this->createdInvoice = $invoice;
    }

    #[Given('the invoice is marked as sending')]
    public function theInvoiceIsMarkedAsSending(): void
    {
        $invoice = $this->invoiceRepository->get($this->createdInvoiceId);
        $invoice->markAsInSending();
        $this->invoiceRepository->save($invoice);
    }

    #[When('I send a GET request to :path')]
    public function iSendAGetRequestTo(string $path): void
    {
        if (str_contains($path, '{id}') && $this->createdInvoiceId !== null) {
            $path = str_replace('{id}', $this->createdInvoiceId->toRfc4122(), $path);
        }

        $this->response = $this->kernel->handle(Request::create($path, 'GET'));
    }

    #[When('I send a POST request to :path')]
    public function iSendAPostRequestTo(string $path): void
    {
        if (str_contains($path, '{id}') && $this->createdInvoiceId !== null) {
            $path = str_replace('{id}', $this->createdInvoiceId->toRfc4122(), $path);
        }

        $request = Request::create(
            $path,
            'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
        );

        $this->response = $this->kernel->handle($request);
    }

    #[When('I send a POST request to :path with JSON:')]
    public function iSendAPostRequestToWithJson(string $path, PyStringNode $json): void
    {
        $request = Request::create(
            $path,
            'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: $json->getRaw(),
        );

        $this->response = $this->kernel->handle($request);
    }

    #[Then('the message is sent with created invoice')]
    public function theMessageIsSentWithCreatedInvoice(): void
    {
        Assert::assertTrue($this->notificationApiStub->didReceiveNotification($this->createdInvoiceId));
    }

    #[Then('the response status code should be :statusCode')]
    public function theResponseStatusCodeShouldBe(int $statusCode): void
    {
        Assert::assertNotNull($this->response, 'No response received');
        Assert::assertSame($statusCode, $this->response->getStatusCode());
    }

    #[Then('the response content type should be :contentType')]
    public function theResponseContentTypeShouldBe(string $contentType): void
    {
        Assert::assertNotNull($this->response, 'No response received');
        $header = $this->response->headers->get('content-type');
        Assert::assertNotNull($header);
        Assert::assertStringContainsString($contentType, $header);
    }

    #[Then('the response JSON should contain a valid UUID id')]
    public function theResponseJsonShouldContainAValidUuidId(): void
    {
        Assert::assertNotNull($this->response, 'No response received');
        $data = json_decode((string)$this->response->getContent(), true);
        Assert::assertIsArray($data);
        Assert::assertArrayHasKey('id', $data);
        Assert::assertTrue(Uuid::isValid($data['id']));
    }

    #[Then('the response JSON should contain error :error')]
    public function theResponseJsonShouldContainError(string $error): void
    {
        Assert::assertNotNull($this->response, 'No response received');
        $data = json_decode((string)$this->response->getContent(), true);
        Assert::assertIsArray($data);
        Assert::assertSame($error, $data['error'] ?? null);
    }

    #[Then('the response JSON should contain customer :name and email :email and status :status')]
    public function theResponseJsonShouldContainCustomerAndEmailAndStatus(string $name, string $email, string $status): void
    {
        Assert::assertNotNull($this->response, 'No response received');
        $data = json_decode((string)$this->response->getContent(), true);
        Assert::assertIsArray($data);
        Assert::assertSame($name, $data['customerName'] ?? null);
        Assert::assertSame($email, $data['customerEmail'] ?? null);
        Assert::assertSame($status, $data['status'] ?? null);
    }

    #[Then('the response JSON should contain a violation on :propertyPath')]
    public function theResponseJsonShouldContainAViolationOn(string $propertyPath): void
    {
        Assert::assertNotNull($this->response, 'No response received');
        $data = json_decode((string)$this->response->getContent(), true);
        Assert::assertIsArray($data);
        Assert::assertArrayHasKey('violations', $data);

        $violationPaths = array_map(static fn(array $v) => $v['propertyPath'] ?? '', $data['violations']);
        Assert::assertContains(
            $propertyPath,
            $violationPaths,
            sprintf('Violation for "%s" not found in %s', $propertyPath, json_encode($violationPaths))
        );
    }

    #[Then('the response JSON should match the created invoice')]
    public function theResponseJsonShouldMatchTheCreatedInvoice(): void
    {
        Assert::assertNotNull($this->response, 'No response received');
        $data = json_decode((string)$this->response->getContent(), true);
        Assert::assertIsArray($data);

        $invoice = $this->createdInvoice ?? ($this->createdInvoiceId !== null ? $this->invoiceRepository->get($this->createdInvoiceId) : null);
        Assert::assertNotNull($invoice, 'No invoice found to match');

        Assert::assertSame($invoice->id->toRfc4122(), $data['id']);

        if (isset($data['customerName'])) {
            Assert::assertSame($invoice->customerName, $data['customerName']);
            Assert::assertSame($invoice->customerEmail, $data['customerEmail']);
            Assert::assertSame($invoice->getStatus()->value, $data['status']);
            Assert::assertCount(count($invoice->lines), $data['lines']);

            foreach ($invoice->lines as $index => $line) {
                Assert::assertSame($line->productName, $data['lines'][$index]['productName']);
                Assert::assertSame($line->quantity, $data['lines'][$index]['quantity']);
                Assert::assertSame($line->unitPrice, $data['lines'][$index]['unitPrice']);
                if (isset($data['lines'][$index]['linePrice'])) {
                    Assert::assertSame($line->getLinePrice(), $data['lines'][$index]['linePrice']);
                } else {
                    Assert::assertSame($line->getLinePrice(), $data['lines'][$index]['totalPrice']);
                }
            }
        } else {
            Assert::assertSame($invoice->customerName, $data['customerId']);
            Assert::assertSame($invoice->getTotalPrice(), $data['totalPrice']);
            Assert::assertCount(count($invoice->lines), $data['lines']);

            foreach ($invoice->lines as $index => $line) {
                Assert::assertSame($line->productName, $data['lines'][$index]['productName']);
                Assert::assertSame($line->quantity, $data['lines'][$index]['quantity']);
                Assert::assertSame($line->unitPrice, $data['lines'][$index]['unitPrice']);
                Assert::assertSame($line->getLinePrice(), $data['lines'][$index]['totalPrice']);
            }
        }
    }

    #[Given('/^the created invoice status is "([^"]*)"$/')]
    public function theCreatedInvoiceStatusIs(string $status): void
    {
        $createdInvoice = $this->invoiceRepository->get($this->createdInvoiceId);
        Assert::assertEquals(InvoiceStatus::from($status), $createdInvoice->getStatus());
    }
}

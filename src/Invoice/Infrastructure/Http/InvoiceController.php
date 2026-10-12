<?php
declare(strict_types=1);

namespace App\Invoice\Infrastructure\Http;

use App\Invoice\Application\InvoiceApp;
use App\Invoice\Application\Exception\InvoiceCannotBeSent;
use App\Invoice\Application\Exception\InvoiceNotFound;
use App\Invoice\Application\Query\InvoiceView;
use App\Invoice\Application\Query\InvoiceViews;
use App\Invoice\Application\Repository\Exception\NotFound;
use App\Invoice\Application\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\Invoice;
use App\Invoice\Domain\InvoiceLine;
use App\Invoice\Infrastructure\Http\Dto\CreateInvoiceDto;
use App\Invoice\Infrastructure\Http\Dto\CreateInvoiceLineDto;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Uid\Uuid;

#[OA\Tag(name: 'Invoices')]
class InvoiceController extends AbstractController
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly InvoiceViews $invoiceViews,
        private readonly InvoiceApp $app,
    )
    {
    }

    #[Route('/api/invoices', name: 'api_invoice_create', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Invoice creation payload',
        required: true,
        content: new OA\JsonContent(ref: new Model(type: CreateInvoiceDto::class))
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Invoice successfully created',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '018f0a00-0000-7000-8000-000000000000'),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation failed'
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Invalid input'
    )]
    public function create(
        #[MapRequestPayload(
            serializationContext: [
                AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
                DenormalizerInterface::COLLECT_EXTRA_ATTRIBUTES_ERRORS => true,
            ]
        )] CreateInvoiceDto $dto,
    ): JsonResponse
    {
        $lines = array_map(
            fn(CreateInvoiceLineDto $lineDto) => new InvoiceLine(
                $lineDto->productName,
                $lineDto->quantity,
                $lineDto->unitPrice,
            ),
            $dto->lines,
        );

        $invoice = new Invoice($dto->customerName, $dto->customerEmail, $lines);
        $this->invoiceRepository->save($invoice);

        return $this->json(['id' => $invoice->id], Response::HTTP_CREATED);
    }

    #[Route('/api/invoices/{id}', name: 'api_invoice_get', methods: ['GET'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'UUID of the invoice',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Invoice found and retrieved successfully',
        content: new OA\JsonContent(ref: new Model(type: InvoiceView::class))
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Invoice not found'
    )]
    public function get(Uuid $id): JsonResponse
    {
        try {
            $invoice = $this->invoiceViews->get($id);
        } catch (NotFound) {
            throw $this->createNotFoundException('Invoice not found');
        }

        return $this->json($invoice);
    }

    #[Route('/api/invoices/{id}/send', name: 'api_invoice_send', methods: ['POST'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'UUID of the invoice',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Response(
        response: Response::HTTP_ACCEPTED,
        description: 'Invoice dispatched to be sent',
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Invoice not found'
    )]
    #[OA\Response(
        response: 409,
        description: 'Invoice cannot be sent in its current state',
    )]
    public function send(Uuid $id): Response
    {
        try {
            $this->app->sendInvoice($id);
        } catch (InvoiceCannotBeSent $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (InvoiceNotFound) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return new Response('', Response::HTTP_ACCEPTED);
    }
}

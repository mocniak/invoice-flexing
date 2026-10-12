<?php
declare(strict_types=1);

namespace App\Notification\Infrastructure\Http;

use App\Notification\Application\NotificationApp;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[OA\Tag(name: 'Notification')]
class NotificationController extends AbstractController
{
    public function __construct(
        private readonly NotificationApp $notificationApp,
    ) {
    }

    #[Route('/api/notification/{id}/mark-as-sent', name: 'api_notification_mark_as_sent', methods: ['POST'])]
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
    public function markAsSent(Uuid $id): Response
    {
        $this->notificationApp->markAsDelivered($id);

        return new Response('', Response::HTTP_ACCEPTED);
    }
}

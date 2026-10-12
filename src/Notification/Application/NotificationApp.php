<?php
declare(strict_types=1);

namespace App\Notification\Application;

use App\Notification\Api\Data\NotifyData;
use App\Notification\Api\Events\NotificationSentEvent;
use App\Notification\Api\NotificationApi;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class NotificationApp implements NotificationApi
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function notify(NotifyData $data): void
    {
        // well, do nothing
    }

    public function markAsDelivered(Uuid $resourceId): void
    {
        $this->eventDispatcher->dispatch(new NotificationSentEvent($resourceId));
    }
}

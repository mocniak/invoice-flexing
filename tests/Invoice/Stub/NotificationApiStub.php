<?php
declare(strict_types=1);

namespace App\Tests\Invoice\Stub;

use App\Notification\Api\Data\NotifyData;
use App\Notification\Api\NotificationApi;
use Symfony\Component\Uid\Uuid;

class NotificationApiStub implements NotificationApi
{
    private array $receivedNotificationRequests = [];
    public function notify(NotifyData $data): void
    {
        $this->receivedNotificationRequests[] = $data->resourceId->toRfc4122();
    }

    public function didReceiveNotification(Uuid $uuid): bool
    {
        return in_array($uuid->toRfc4122(), $this->receivedNotificationRequests);
    }
}

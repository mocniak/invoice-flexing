<?php
declare(strict_types=1);

namespace App\Tests\Application;

use App\Notification\Api\Events\NotificationSentEvent;
use App\Notification\Application\NotificationApp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NotificationAppTest extends TestCase
{
    public function testMarkAsDeliveredDispatchesNotificationSentEvent(): void
    {
        $invoiceId = Uuid::v7();

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (object $event) use ($invoiceId) {
                return $event instanceof NotificationSentEvent && $event->resourceId->equals($invoiceId);
            }))
            ->willReturnArgument(0);

        $notificationApp = new NotificationApp($dispatcher);
        $notificationApp->markAsDelivered($invoiceId);
    }
}

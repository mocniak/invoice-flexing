<?php

declare(strict_types=1);

namespace App\Notification\Api\Events;

use Symfony\Component\Uid\Uuid;

final readonly class NotificationSentEvent
{
    public function __construct(
        public Uuid $resourceId,
    ) {}
}

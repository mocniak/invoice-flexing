<?php

declare(strict_types=1);

namespace App\Notification\Api\Data;

use Symfony\Component\Uid\Uuid;

final readonly class NotifyData
{
    public function __construct(
        public Uuid $resourceId,
        public string $toEmail,
        public string $subject,
        public string $message,
    ) {}
}

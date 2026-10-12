<?php

declare(strict_types=1);

namespace App\Notification\Api;

use App\Notification\Api\Data\NotifyData;

interface NotificationApi
{
    public function notify(NotifyData $data): void;
}

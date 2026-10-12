<?php

declare(strict_types=1);

namespace App\Invoice\Domain;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case InSending = 'sending';
    case SentToClient = 'sent-to-client';
}

<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ErpNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ErpNotificationHandler
{
    public function __invoke(ErpNotification $message)
    {
        //some logic communication with ERP
    }
}

<?php

declare(strict_types=1);

namespace Damax\User\Application\Command;

class RecordLogin
{
    /**
     * @var string
     */
    public $userId;

    /**
     * @var string
     */
    public $clientIp;

    /**
     * @var string
     */
    public $serverIp;

    /**
     * @var string
     */
    public $userAgent;
}

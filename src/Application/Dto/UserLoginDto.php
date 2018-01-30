<?php

declare(strict_types=1);

namespace Damax\User\Application\Dto;

use DateTimeInterface;

class UserLoginDto
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $username;

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

    /**
     * @var DateTimeInterface
     */
    public $createdAt;
}
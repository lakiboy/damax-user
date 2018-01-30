<?php

declare(strict_types=1);

namespace Damax\User\Application\Command;

class ChangePassword extends UserCommand
{
    /**
     * @var string
     */
    public $newPassword;
}
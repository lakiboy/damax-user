<?php

declare(strict_types=1);

namespace Damax\User\Application\Exception;

use RuntimeException;

class PermissionAlreadyExists extends RuntimeException
{
    public static function withCode(string $code): self
    {
        return new self(sprintf('Permission with code "%s" already exists.', $code));
    }
}

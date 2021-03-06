<?php

declare(strict_types=1);

namespace Damax\User\Domain\Model;

interface UserFactory
{
    public function create($data, User $creator = null): User;
}

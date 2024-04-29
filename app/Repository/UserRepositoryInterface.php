<?php

namespace App\Repository;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function updatePassword(int $id, array $content);
}
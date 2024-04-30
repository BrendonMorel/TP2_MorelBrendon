<?php

namespace App\Repository\Eloquent;

use App\Models\User;
use App\Repository\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{

    /**
     * UserRepository constructor.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function updatePassword(int $id, array $content)
    {
        $user = parent::getById($id);

        $user->password = bcrypt($content['new_password']);
        $user->save();

        return $user;
    }
}

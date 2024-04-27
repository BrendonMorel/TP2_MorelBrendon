<?php

namespace App\Repository\Eloquent;

use App\Models\Actor;
use App\Repository\ActorRepositoryInterface;

class ActorRepository extends BaseRepository implements ActorRepositoryInterface
{

    /**
    * ActorRepository constructor.
    *
    * @param Actor $model
    */
   public function __construct(Actor $model)
   {
       parent::__construct($model);
   }
}
?>
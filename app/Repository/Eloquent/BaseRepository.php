<?php

namespace App\Repository\Eloquent;

use App\Repository\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    /**
     * @param int $perPage
     * @return Collection
     */
    public function getAll(int $perPage = 0): Collection
    {
        if ($perPage > 0) {
            return $this->model->paginate($perPage);
        }
        
        return $this->model->all();
    }

    /**
     * @param int $id
     * @return Model
     */
    public function getById(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @param int $id
     * @param array $content
     * @return Model
     */
    public function update(int $id, array $content): Model
    {
        return $this->model->findOrFail($id)->update($content);
    }

    /**
     * @param int $id
     * @return Model
     */
    public function delete(int $id): Model
    {
        return $this->model->findOrFail($id)->delete();
    }
}

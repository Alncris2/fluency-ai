<?php

namespace App\Core\Architecture\Abstracts;

use App\Core\Architecture\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AbstractRepository implements RepositoryInterface
{
    public Model $model;

    public function getModel(): Model
    {
        return $this->model;
    }

    public function all($params = null, $with = [])
    {
        $query = $this->getModel()->with($with);

        if (is_array($params)) {
            $query->where($params);
        }

        return $query->paginate(20)->withQueryString();
    }

    public function find(mixed $id, array $with = [], array $withCount = []): Model|Collection|Builder|array|null
    {
        if (is_numeric($id)) {
            return $this->getModel()->with($with)->withCount($withCount)->find($id);
        }

        return $this->findOneWhere(['code' => $id], $with, $withCount);
    }

    public function findOneWhere(array $where, array $with = [], array $withCount = [])
    {
        $query = $this->where($where, $with, $withCount);

        if (method_exists($this->getModel(), 'withTrashed')) {
            $query = $query->withTrashed();
        }

        return $query->first();
    }

    public function create(array $params): Model
    {
        return $this->getModel()->create($params);
    }

    public function update(Model $entity, $data): bool
    {
        return $entity->fill($data)->save();
    }

    public function delete($id): void
    {
        $model = $this->find($id);
        $model->delete();
    }

    public function restore($id): Model
    {
        $model = $this->getModel()->where(is_numeric($id) ? ['id' => $id] : ['code' => $id])
            ->withTrashed()
            ->first();

        $model->restore();

        return $model;
    }

    public function where(array $where, array $with = [], array $withCount = [])
    {
        return $this->getModel()->where($where)->with($with)->withCount($withCount)->get();
    }

    public function createOrUpdate($paramsValidator, $params)
    {
        return $this->getModel()->updateOrCreate($paramsValidator, $params);
    }
}
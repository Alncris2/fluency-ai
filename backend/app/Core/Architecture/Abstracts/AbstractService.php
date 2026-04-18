<?php

namespace App\Core\Architecture\Abstracts;

use App\Core\Architecture\Interfaces\ServiceInterface;
use App\Exceptions\ModelNotFoundException;
use App\Traits\Common;
use Exception;

class AbstractService implements ServiceInterface
{
    use Common;

    protected $with = [];

    public $repository;

    public function getAll(array $params = [], $with = [])
    {
        return $this->repository->where($params, $with);
    }

    public function all(array $params = [], $with = [])
    {
        return $this->repository->all($params, $with);
    }

    /**
     * @throws Exception
     */
    public function find($id, array $with = [], array $withCount = [])
    {
        $result = $this->repository->find($id, $with, $withCount);

        if ($result == null) {
            throw new ModelNotFoundException(get_class($this->repository->getModel()));
        }

        return $result;
    }

    public function validateOnInsert(array $params): bool
    {
        return true;
    }

    public function validateOnUpdate($id, array $params): bool
    {
        return true;
    }

    public function validateOnDelete($id): bool
    {
        return true;
    }

    public function validateOnRestore($id): bool
    {
        return true;
    }

    public function afterSave($entity, array $params)
    {
        return $entity;
    }

    public function afterUpdate($entity, array $params)
    {
        return $entity;
    }

    public function afterDelete($entity)
    {
        return $entity;
    }

    public function afterRestore($entity)
    {
        return $entity;
    }

    public function beforeSave(array $params): array
    {
        return $params;
    }

    public function beforeUpdate($id, array $params)
    {
        return $params;
    }

    public function beforeDelete($entity)
    {
        return $entity;
    }

    public function beforeRestore($entity)
    {
        return $entity;
    }

    public function create(array $params)
    {
        $entity = $this->repository->create($params);
        $this->afterSave($entity, $params);

        return $entity;
    }

    public function update($id, array $params)
    {
        $params = $this->beforeUpdate($id, $params);
        $this->validateOnUpdate($id, $params);
        $entity = $this->find($id);
        $updated = $this->repository->update($entity, $params);

        if ($updated) {
            $this->afterUpdate($entity, $params);
        }

        return $entity->refresh();
    }

    public function delete($id): int|string
    {
        $this->validateOnDelete($id);
        $this->beforeDelete($id);
        $this->repository->delete($id);
        $this->afterDelete($id);

        return $id;
    }

    public function restore($id): int|string
    {
        if (! method_exists($this->repository->getModel(), 'restore')) {
            throw new Exception('Esse método não pode ser utilizado, pois o model não possui o método restore.');
        }
        $this->validateOnRestore($id);
        $this->beforeRestore($id);
        $this->repository->restore($id);
        $this->afterRestore($id);

        return $id;
    }

    public function save(array $params)
    {
        $params = $this->beforeSave($params);
        if ($this->validateOnInsert($params) !== false) {
            $entity = $this->repository->create($params);
            $this->afterSave($entity, $params);

            return $entity;
        }

        return false;
    }

    public function where(array $where, array $with = [], array $withCount = [])
    {
        return $this->repository->where($where, $with, $withCount);
    }

    public function findOneWhere(array $where, array $with = [], array $withCount = [])
    {
        return $this->repository->findOneWhere($where, $with, $withCount);
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function createOrUpdate($params, $paramsValidator)
    {
        $params = $this->beforeCreateOrUpdate($params);
        if ($this->validateOnInsert($params) !== false) {
            $entity = $this->repository->createOrUpdate($paramsValidator, $params);
            $this->afterCreateOrUpdate($entity, $params);

            return $entity;
        }

        return false;
    }

    public function afterCreateOrUpdate($entity, array $params)
    {
        return $entity;
    }

    public function beforeCreateOrUpdate(array $params): array
    {
        return $params;
    }
}
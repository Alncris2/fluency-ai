<?php

namespace App\Core\Architecture\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    public function getModel(): Model;

    public function all(?array $params = null, $with = []);

    public function find(mixed $id, array $with = [], array $withCount = []);

    public function findOneWhere(array $where, array $with = [], array $withCount = []);

    public function create(array $params);

    public function update(Model $entity, $data);

    public function delete(int $id);

    public function restore(int $id);

    public function where(array $where, array $with = [], array $withCount = []);
}
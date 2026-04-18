<?php

namespace App\Core\User\Repositories;

use App\Core\Architecture\Abstracts\AbstractRepository;
use App\Models\User;

class UserRepository extends AbstractRepository
{
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Search users for filters with pagination
     */
    public function searchForFilters(string $search = '', int $page = 1, int $perPage = 15)
    {
        $query = $this->model->query()
            ->when($search, function ($q) use ($search) {
                return $q->where('name', 'LIKE', '%'.$search.'%')
                    ->orWhere('email', 'LIKE', '%'.$search.'%');
            })
            ->orderBy('name');

        return $query->paginate($perPage, ['id', 'name', 'email'], 'page', $page);
    }

    /**
     * Get users by IDs
     */
    public function getByIds(array $ids)
    {
        return $this->model->whereIn('id', $ids)
            ->get(['id', 'code', 'name', 'email', 'picture']);
    }
}

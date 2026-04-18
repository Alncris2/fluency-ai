<?php

namespace App\Core\User\Services;

use App\Core\Architecture\Abstracts\AbstractService;
use App\Core\User\Repositories\UserRepository;
use App\Core\User\Support\RolePermissionDefaults;
use App\Core\User\Validators\DataUserValidator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserService extends AbstractService
{
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function beforeSave(array $params): array
    {
        $dataUserValidator = $this->preparePayloadToSave($params);

        $payload = $dataUserValidator->toArray();

        return $payload;
    }

    public function beforeUpdate($id, $params): array
    {
        return $this->preparePayloadToUpdate($params);
    }

    private function preparePayloadToSave(array $params): array
    {
        $dataInvitationValidator = new DataUserValidator($params);

        $payload = $dataInvitationValidator->toArray();

        return array_merge($params, $payload);
    }

    private function preparePayloadToUpdate(array $params): array
    {
        $mappings = [
            'name' => 'name',
            'email' => 'email',
            'password' => 'password',
            'document' => 'document',
        ];

        foreach ($mappings as $key => $mapping) {
            $altKey = is_array($mapping) ? $mapping[0] : $mapping;
            $callback = is_array($mapping) ? $mapping[1] : null;

            if (isset($params[$key]) || ($altKey && isset($params[$altKey]))) {
                $value = $params[$key] ?? $params[$altKey];
                $params[$key] = $callback ? $callback($value) : $value;
            }
        }

        return $params;
    }

    /**
     * Search users for filters
     */
    public function searchForFilters(string $search = '', int $page = 1, int $perPage = 15)
    {
        return $this->repository->searchForFilters($search, $page, $perPage);
    }

    /**
     * Get users by IDs
     */
    public function getByIds(array $ids)
    {
        return $this->repository->getByIds($ids);
    }
}

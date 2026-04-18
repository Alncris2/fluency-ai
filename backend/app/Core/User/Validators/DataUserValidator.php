<?php

namespace App\Core\User\Validators;

use App\Traits\Common;
use Illuminate\Database\Eloquent\Model;

class DataUserValidator
{
    use Common;

    private array $params;

    private Model $entity;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->params['name'] ?? '',
            'email' => $this->params['email'],
            'password' => $this->params['password'],
            'picture' => $this->params['picture'] ?? null,
            'document' => $this->params['document'] ?? '',
        ];
    }
}

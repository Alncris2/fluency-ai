<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected string $titleSuccessDefault = 'Success';

    protected string $messageSuccessDefault = ':)';

    protected string $messageErrorDefault = 'Ops';

    public const TYPE_SUCCESS = 'success';

    public const TYPE_ERROR = 'error';

    public function ok($items = [], int $status = Response::HTTP_OK): JsonResponse
    {
        $data = [
            'success' => true,
            'type' => self::TYPE_SUCCESS,
            'status' => $status,
            'data' => $items,
            'show' => false,
        ];

        return response()->json($data, $status);
    }

    public function error(
        string $message = '',
        array $items = [],
        int $status = Response::HTTP_UNPROCESSABLE_ENTITY
    ): JsonResponse {
        if (is_null($message)) {
            $message = $this->messageErrorDefault;
        }

        $data = [
            'error' => true,
            'type' => self::TYPE_ERROR,
            'status' => $status,
            'message' => $message,
            'show' => true,
        ];

        if ($items) {
            foreach ($items as $key => $item) {
                $data['errors'][$key] = $item;
            }
        }

        return response()->json($data, $status);
    }

    public function success(
        string $message,
        array $items = [],
        int $status = Response::HTTP_OK
    ): JsonResponse {
        if (is_null($message)) {
            $message = $this->messageSuccessDefault;
        }

        $data = [
            'success' => true,
            'type' => self::TYPE_SUCCESS,
            'status' => $status,
            'title' => $this->titleSuccessDefault,
            'msg' => $message,
            'show' => true,
        ];

        if ($items instanceof Arrayable) {
            $items = $items->toArray();
        }

        if ($items) {
            foreach ($items as $key => $item) {
                $data[$key] = $item;
            }
        }

        return response()->json($data, $status);
    }

    public function getUserAuth(): ?Authenticatable
    {
        return Auth::user();
    }
}
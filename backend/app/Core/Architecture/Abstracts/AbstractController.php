<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

abstract class AbstractController extends Controller
{
    protected $with = [];

    protected $withCount = [];

    protected $service;

    protected $requestValidate;

    protected $requestValidateUpdate;

    public function index(Request $request)
    {
        if (! isset($request->with)) {
            $request->with = $this->with;
        }

        $items = $this
            ->service
            ->getAll($request->all(), $request->with)
            ->toArray();

        return $this->ok($items);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            if ($this->requestValidate) {
                $requestValidate = app($this->requestValidate);
            }
        } catch (ValidationException $e) {
            return $this->error($this->messageErrorDefault, $e->errors());
        }

        try {
            DB::beginTransaction();
            $response = $this->service->save($request->toArray());
            DB::commit();

            return $this->success($this->messageSuccessDefault, ['response' => $response]);
        } catch (\Exception|ValidationException $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                Log::info($e->getMessage());

                return $this->error($this->messageErrorDefault, $e->errors());
            }
            if ($e instanceof \Exception) {
                Log::error($e->getLine(), (array) $e->getFile());

                return $this->error($e->getMessage());
            }
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            if (! empty($this->requestValidateUpdate)) {
                $requestValidateUpdate = app($this->requestValidateUpdate);
            }
        } catch (ValidationException $e) {
            return $this->error($this->messageErrorDefault, $e->errors());
        }

        try {
            DB::beginTransaction();
            $response = $this->service->update($id, $request->toArray());
            DB::commit();

            return $this->success($this->messageSuccessDefault, ['response' => $response]);
        } catch (\Exception|ValidationException $e) {
            DB::rollBack();
            if ($e instanceof \Exception) {
                return $this->error($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->error($this->messageErrorDefault, $e->errors());
            }
        }
    }

    public function show($id, Request $request): JsonResponse
    {
        if (! isset($request->with)) {
            $request->with = $this->with;
        }

        if (! isset($request->withCount)) {
            $request->withCount = $this->withCount;
        }

        try {
            return $this->ok($this->service->find($id, $request->with, $request->withCount)->toArray());
        } catch (\Exception|ValidationException $e) {
            DB::rollBack();
            if ($e instanceof \Exception) {
                return $this->error($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->error($this->messageErrorDefault, $e->errors());
            }
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return $this->success($this->messageSuccessDefault);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @param null $id
     */
    public function preRequisite($id = null): JsonResponse
    {
        $preRequisite = $this->service->preRequisite($id);

        return $this->ok(compact('preRequisite'));
    }

    public function toSelect(): JsonResponse
    {
        return $this->ok($this->service->toSelect(request()->all()));
    }
}